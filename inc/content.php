<?php
// Reemplaza a content.ts (que a su vez reemplazó a wp.ts) — mismo rol:
// única fuente de contenido para el sitio público, ahora en PHP puro
// consultando MySQL directo (mysqli, sin ORM). Sin cacheo — cada request
// consulta fresco, igual que la versión Node.
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/regions.php';

function build_image_url(string $id, string $extension): string {
    return ADMIN_PUBLIC_URL . '/uploads/' . $id . '.' . $extension;
}

// COALESCE que trata '' como NULL también — un campo traducido vacío (aún
// sin traducir a mano, la API no tenía nada que mandar) debe caer al
// español igual que si la fila ni existiera.
function locale_coalesce($translated, $original) {
    return ($translated !== null && $translated !== '') ? $translated : $original;
}

// 'es' nunca tiene tabla de traducción propia (es la canónica) — este helper
// evita repetir el if en cada función de abajo.
function is_translatable_locale(): bool {
    return current_locale() !== 'es';
}

// Arma el JOIN + las expresiones SELECT para traer un campo en el idioma
// activo con respaldo automático a español, sin tocar nada cuando el idioma
// es español (que no tiene fila de traducción propia). $fields es
// [columna_base => alias_en_resultado]. $locale ya viene validado contra
// una whitelist fija (current_locale()), así que es seguro interpolarlo.
function translated_select(string $table, string $fkColumn, string $baseAlias, array $fields): array {
    $locale = current_locale();
    if ($locale === 'es') {
        $join = '';
        $select = array_map(fn($col) => "$baseAlias.$col", array_combine($fields, $fields));
    } else {
        $joinAlias = 'i18n_' . $table;
        $join = "LEFT JOIN $table $joinAlias ON $joinAlias.$fkColumn = $baseAlias.id AND $joinAlias.locale = '$locale'";
        $select = [];
        foreach ($fields as $col) {
            $select[$col] = "COALESCE(NULLIF($joinAlias.$col,''), $baseAlias.$col)";
        }
    }
    return ['join' => $join, 'select' => $select];
}

function select_sql(array $select): string {
    $parts = [];
    foreach ($select as $alias => $expr) $parts[] = "$expr AS $alias";
    return implode(', ', $parts);
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
    $t = translated_select('package_translations', 'package_id', 'p', ['title', 'subtitle']);
    $stmt = $mysqli->prepare(
        "SELECT p.id, p.slug, {$t['select']['title']} AS title, {$t['select']['subtitle']} AS subtitle,
                p.package_type, p.show_in_promociones, $cover AS cover
         FROM packages p {$t['join']} WHERE p.region_id = ? AND p.status = 'published' ORDER BY p.title"
    );
    $stmt->bind_param('s', $region['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $packages = [];
    while ($row = $res->fetch_assoc()) $packages[] = row_to_summary($row);
    $stmt->close();

    return ['name' => region_name($regionSlug), 'description' => null, 'packages' => $packages];
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
    $t = translated_select('package_translations', 'package_id', 'p', ['title', 'subtitle']);
    $res = $mysqli->query(
        "SELECT p.slug, {$t['select']['title']} AS title, {$t['select']['subtitle']} AS subtitle,
                p.package_type, p.show_in_promociones, $cover AS cover
         FROM packages p {$t['join']}
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
    $t = translated_select('package_translations', 'package_id', 'p', ['title', 'subtitle']);
    $res = $mysqli->query(
        "SELECT p.id, p.slug, {$t['select']['title']} AS title, {$t['select']['subtitle']} AS subtitle,
                p.package_type, p.show_in_promociones, $cover AS cover
         FROM packages p {$t['join']} WHERE status = 'published' ORDER BY p.title"
    );
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = row_to_summary($r);
    return $rows;
}

function get_package_by_slug(string $slug): ?array {
    $mysqli = db();
    $cover = package_cover_subquery();
    $t = translated_select('package_translations', 'package_id', 'p', ['title', 'subtitle', 'content', 'included', 'not_included']);
    $stmt = $mysqli->prepare(
        "SELECT p.id, p.slug, {$t['select']['title']} AS title, {$t['select']['subtitle']} AS subtitle,
                p.package_type, p.show_in_promociones,
                {$t['select']['content']} AS content, p.duration_days, p.duration_nights, p.price_display_mode,
                p.price_from_clp, p.price_unit, {$t['select']['included']} AS included, {$t['select']['not_included']} AS not_included,
                $cover AS cover, r.slug AS region_slug
         FROM packages p {$t['join']}
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
    $ta = translated_select('package_addon_translations', 'addon_id', 'pa', ['name']);
    $stmt = $mysqli->prepare("SELECT pa.id, {$ta['select']['name']} AS name, pa.price_clp FROM package_addons pa {$ta['join']} WHERE pa.package_id = ? ORDER BY pa.sort_order");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $addons[] = ['id' => $r['id'], 'name' => $r['name'], 'priceClp' => (int) $r['price_clp']];
    $stmt->close();

    $roomOptions = [];
    $tr = translated_select('package_room_option_translations', 'room_option_id', 'pr', ['label']);
    $stmt = $mysqli->prepare("SELECT pr.id, {$tr['select']['label']} AS label, pr.price_adjustment_clp FROM package_room_options pr {$tr['join']} WHERE pr.package_id = ? ORDER BY pr.sort_order");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $roomOptions[] = ['id' => $r['id'], 'label' => $r['label'], 'priceAdjustmentClp' => (int) $r['price_adjustment_clp']];
    $stmt->close();

    $itinerary = [];
    $td = translated_select('package_itinerary_day_translations', 'day_id', 'd', ['title', 'description']);
    $stmt = $mysqli->prepare("SELECT d.day_number, {$td['select']['title']} AS title, {$td['select']['description']} AS description FROM package_itinerary_days d {$td['join']} WHERE d.package_id = ? ORDER BY d.sort_order");
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
        'region' => $row['region_slug'] ? ['name' => region_name($row['region_slug']), 'slug' => $row['region_slug']] : null,
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
    $t = translated_select('blog_post_translations', 'post_id', 'b', ['title', 'excerpt']);
    $res = $mysqli->query(
        "SELECT b.slug, {$t['select']['title']} AS title, {$t['select']['excerpt']} AS excerpt,
                b.published_at, i.id AS image_id, i.file_extension AS image_ext
         FROM blog_posts b {$t['join']} LEFT JOIN images i ON i.id = b.featured_image_id
         WHERE b.status = 'published' ORDER BY b.published_at DESC"
    );
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = row_to_blog_summary($r);
    return $rows;
}

function get_blog_post_by_slug(string $slug): ?array {
    $mysqli = db();
    $t = translated_select('blog_post_translations', 'post_id', 'b', ['title', 'excerpt', 'content']);
    $stmt = $mysqli->prepare(
        "SELECT b.slug, {$t['select']['title']} AS title, {$t['select']['excerpt']} AS excerpt,
                {$t['select']['content']} AS content, b.published_at, i.id AS image_id, i.file_extension AS image_ext
         FROM blog_posts b {$t['join']} LEFT JOIN images i ON i.id = b.featured_image_id
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
    $t = translated_select('static_page_translations', 'page_id', 'sp', ['title', 'content']);
    $stmt = $mysqli->prepare("SELECT sp.id, {$t['select']['title']} AS title, {$t['select']['content']} AS content FROM static_pages sp {$t['join']} WHERE sp.slug = ?");
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
