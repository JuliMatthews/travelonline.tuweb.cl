<?php
// Reemplaza a content.ts (que a su vez reemplazó a wp.ts) — mismo rol:
// única fuente de contenido para el sitio público, ahora en PHP puro
// consultando MySQL directo (mysqli, sin ORM). Sin cacheo — cada request
// consulta fresco, igual que la versión Node.
require_once __DIR__ . '/db.php';

function build_image_url(string $id, string $extension): string {
    return ADMIN_PUBLIC_URL . '/uploads/' . $id . '.' . $extension;
}

// Portada de un paquete (primera imagen por sort_order) — es lo único que
// usan las tarjetas de listado (home, destinos, promociones, todo-incluido).
function package_cover_subquery(string $alias = 'p'): string {
    return "(SELECT CONCAT(i.id, '|', i.file_extension) FROM package_images pi
             JOIN images i ON i.id = pi.image_id
             WHERE pi.package_id = $alias.id ORDER BY pi.sort_order LIMIT 1)";
}

function row_to_summary(array $row): array {
    $cover = null;
    if (!empty($row['cover'])) {
        [$id, $ext] = explode('|', $row['cover'], 2);
        $cover = build_image_url($id, $ext);
    }
    return [
        'id' => $row['id'],
        'slug' => $row['slug'],
        'title' => $row['title'],
        'subtitle' => $row['subtitle'],
        'packageType' => $row['package_type'],
        'heroGallery' => $cover ? [$cover] : [],
        'showInPromociones' => (bool) $row['show_in_promociones'],
    ];
}

function get_region_with_packages(string $regionSlug): ?array {
    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT id, name FROM regions WHERE slug = ?');
    $stmt->bind_param('s', $regionSlug);
    $stmt->execute();
    $region = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$region) return null;

    $cover = package_cover_subquery();
    $stmt = $mysqli->prepare(
        "SELECT p.id, p.slug, p.title, p.subtitle, p.package_type, p.show_in_promociones, $cover AS cover
         FROM packages p WHERE p.region_id = ? AND p.status = 'published' ORDER BY p.title"
    );
    $stmt->bind_param('s', $region['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $packages = [];
    while ($row = $res->fetch_assoc()) $packages[] = row_to_summary($row);
    $stmt->close();

    return ['name' => $region['name'], 'description' => null, 'packages' => $packages];
}

function get_regions_overview(): array {
    $mysqli = db();
    $res = $mysqli->query(
        "SELECT r.slug, r.name, count(p.id) AS count
         FROM regions r
         LEFT JOIN packages p ON p.region_id = r.id AND p.status = 'published'
         GROUP BY r.id, r.slug, r.name
         ORDER BY r.sort_order"
    );
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = ['slug' => $r['slug'], 'name' => $r['name'], 'count' => (int) $r['count']];
    }
    return $rows;
}

function get_featured_packages(): array {
    $mysqli = db();
    $cover = package_cover_subquery();
    $res = $mysqli->query(
        "SELECT slug, title, subtitle, package_type, show_in_promociones, $cover AS cover
         FROM packages p
         WHERE is_featured = 1 AND status = 'published'
         ORDER BY featured_sort_order"
    );
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = row_to_summary($r);
    return $rows;
}

function get_all_packages(): array {
    $mysqli = db();
    $cover = package_cover_subquery();
    $res = $mysqli->query(
        "SELECT id, slug, title, subtitle, package_type, show_in_promociones, $cover AS cover
         FROM packages p WHERE status = 'published' ORDER BY title"
    );
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = row_to_summary($r);
    return $rows;
}

function get_package_by_slug(string $slug): ?array {
    $mysqli = db();
    $cover = package_cover_subquery();
    $stmt = $mysqli->prepare(
        "SELECT p.id, p.slug, p.title, p.subtitle, p.package_type, p.show_in_promociones,
                p.content, p.duration_days, p.duration_nights, p.price_display_mode,
                p.price_from_clp, p.price_unit, p.included, p.not_included,
                $cover AS cover, r.name AS region_name, r.slug AS region_slug
         FROM packages p
         LEFT JOIN regions r ON r.id = p.region_id
         WHERE p.slug = ? AND p.status = 'published'"
    );
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) return null;

    $id = $row['id'];

    $images = [];
    $stmt = $mysqli->prepare(
        'SELECT i.id, i.file_extension FROM package_images pi
         JOIN images i ON i.id = pi.image_id WHERE pi.package_id = ? ORDER BY pi.sort_order'
    );
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $images[] = build_image_url($r['id'], $r['file_extension']);
    $stmt->close();

    $addons = [];
    $stmt = $mysqli->prepare('SELECT id, name, price_clp FROM package_addons WHERE package_id = ? ORDER BY sort_order');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $addons[] = ['id' => $r['id'], 'name' => $r['name'], 'priceClp' => (int) $r['price_clp']];
    $stmt->close();

    $roomOptions = [];
    $stmt = $mysqli->prepare('SELECT id, label, price_adjustment_clp FROM package_room_options WHERE package_id = ? ORDER BY sort_order');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $roomOptions[] = ['id' => $r['id'], 'label' => $r['label'], 'priceAdjustmentClp' => (int) $r['price_adjustment_clp']];
    $stmt->close();

    $itinerary = [];
    $stmt = $mysqli->prepare('SELECT day_number, title, description FROM package_itinerary_days WHERE package_id = ? ORDER BY sort_order');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $itinerary[] = ['dayNumber' => $r['day_number'] !== null ? (int) $r['day_number'] : null, 'title' => $r['title'], 'description' => $r['description']];
    }
    $stmt->close();

    $summary = row_to_summary($row);
    $summary['heroGallery'] = $images;

    return array_merge($summary, [
        'content' => $row['content'] ?? '',
        'durationDays' => $row['duration_days'] !== null ? (int) $row['duration_days'] : null,
        'durationNights' => $row['duration_nights'] !== null ? (int) $row['duration_nights'] : null,
        'priceDisplayMode' => $row['price_display_mode'],
        'priceFromClp' => $row['price_from_clp'] !== null ? (int) $row['price_from_clp'] : null,
        'priceUnit' => $row['price_unit'],
        'included' => $row['included'],
        'notIncluded' => $row['not_included'],
        'region' => $row['region_slug'] ? ['name' => $row['region_name'], 'slug' => $row['region_slug']] : null,
        'addons' => $addons,
        'roomOptions' => $roomOptions,
        'itinerary' => $itinerary,
    ]);
}

function row_to_blog_summary(array $row): array {
    $featuredImage = null;
    if (!empty($row['image_id'])) {
        $featuredImage = build_image_url($row['image_id'], $row['image_ext']);
    }
    return [
        'slug' => $row['slug'],
        'title' => $row['title'],
        'excerpt' => $row['excerpt'],
        'date' => $row['published_at'],
        'featuredImage' => $featuredImage,
    ];
}

function get_all_blog_posts(): array {
    $mysqli = db();
    $res = $mysqli->query(
        "SELECT b.slug, b.title, b.excerpt, b.published_at, i.id AS image_id, i.file_extension AS image_ext
         FROM blog_posts b LEFT JOIN images i ON i.id = b.featured_image_id
         WHERE b.status = 'published' ORDER BY b.published_at DESC"
    );
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = row_to_blog_summary($r);
    return $rows;
}

function get_blog_post_by_slug(string $slug): ?array {
    $mysqli = db();
    $stmt = $mysqli->prepare(
        "SELECT b.slug, b.title, b.excerpt, b.content, b.published_at, i.id AS image_id, i.file_extension AS image_ext
         FROM blog_posts b LEFT JOIN images i ON i.id = b.featured_image_id
         WHERE b.slug = ? AND b.status = 'published'"
    );
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) return null;
    return array_merge(row_to_blog_summary($row), ['content' => $row['content']]);
}

function get_page_by_slug(string $slug): ?array {
    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT id, title, content FROM static_pages WHERE slug = ?');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) return null;

    $images = [];
    $stmt = $mysqli->prepare(
        'SELECT i.id, i.file_extension FROM static_page_images spi
         JOIN images i ON i.id = spi.image_id WHERE spi.page_id = ? ORDER BY spi.sort_order'
    );
    $stmt->bind_param('s', $row['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $images[] = build_image_url($r['id'], $r['file_extension']);
    $stmt->close();

    return ['title' => $row['title'], 'content' => $row['content'], 'heroGallery' => $images];
}
