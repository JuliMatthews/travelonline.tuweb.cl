import { pool } from "@/lib/db";

// Reemplaza a wp.ts — WordPress ya no existe en este proyecto. Mismas
// firmas de función que antes (para no tocar a casi ninguno de sus 16
// consumidores), pero leyendo directo de la Postgres que administra
// `admin/` en vez de hacer fetch a WPGraphQL. Sin cacheo por tags: cada
// función consulta Postgres fresca en cada request — es más rápida que el
// viejo round-trip a WPGraphQL y evita toda una clase de bugs de "caché
// vieja" (ver plan, Fase 6).

function buildImageUrl(id: string, extension: string): string {
  const base = process.env.ADMIN_PUBLIC_URL ?? "http://localhost:3001";
  return `${base}/uploads/${id}.${extension}`;
}

type ImageRow = { id: string; ext: string } | null;

function imagesToGallery(images: ImageRow[] | null): string[] {
  if (!images) return [];
  return images.map((img) => buildImageUrl(img!.id, img!.ext));
}

const PACKAGE_IMAGES_SUBQUERY = /* SQL */ `(
  SELECT json_agg(json_build_object('id', i.id, 'ext', i.file_extension) ORDER BY pi.sort_order)
  FROM package_images pi JOIN images i ON i.id = pi.image_id
  WHERE pi.package_id = p.id
)`;

export type StaticPageData = {
  title: string;
  content: string;
  heroGallery: string[];
};

export type PackageItineraryDay = {
  dayNumber: number | null;
  title: string;
  description: string;
};

export type PackageSummary = {
  slug: string;
  title: string;
  subtitle: string | null;
  packageType: string | null;
  heroGallery: string[];
  showInPromociones: boolean;
};

export type PackageAddon = { id: string; name: string; priceClp: number };
export type PackageRoomOption = { id: string; label: string; priceAdjustmentClp: number };

export type PackageDetail = PackageSummary & {
  content: string;
  durationDays: number | null;
  durationNights: number | null;
  priceDisplayMode: string | null;
  priceFromClp: number | null;
  priceUnit: "per_person" | "per_couple" | null;
  included: string | null;
  notIncluded: string | null;
  itinerary: PackageItineraryDay[];
  addons: PackageAddon[];
  roomOptions: PackageRoomOption[];
  region: { name: string; slug: string } | null;
};

function rowToSummary(row: Record<string, unknown>): PackageSummary {
  return {
    slug: row.slug as string,
    title: row.title as string,
    subtitle: row.subtitle as string | null,
    packageType: row.package_type as string | null,
    heroGallery: imagesToGallery(row.images as ImageRow[] | null),
    showInPromociones: row.show_in_promociones as boolean,
  };
}

export async function getRegionWithPackages(regionSlug: string) {
  const { rows: regionRows } = await pool.query(
    "SELECT id, name FROM regions WHERE slug = $1",
    [regionSlug]
  );
  if (regionRows.length === 0) return null;
  const region = regionRows[0];

  const { rows } = await pool.query(
    `SELECT p.slug, p.title, p.subtitle, p.package_type, p.show_in_promociones,
            ${PACKAGE_IMAGES_SUBQUERY} AS images
     FROM packages p
     WHERE p.region_id = $1 AND p.status = 'published'
     ORDER BY p.title`,
    [region.id]
  );

  return {
    name: region.name as string,
    description: null as string | null, // sin contenido editable de región todavía (fuera de alcance)
    destinationPackages: { nodes: rows.map(rowToSummary) },
  };
}

export type RegionOverview = { slug: string; name: string; count: number };

export async function getRegionsOverview(): Promise<RegionOverview[]> {
  const { rows } = await pool.query(`
    SELECT r.slug, r.name, count(p.id) AS count
    FROM regions r
    LEFT JOIN packages p ON p.region_id = r.id AND p.status = 'published'
    GROUP BY r.id, r.slug, r.name
    ORDER BY r.sort_order
  `);
  return rows.map((r) => ({ slug: r.slug, name: r.name, count: Number(r.count) }));
}

export async function getFeaturedPackages(): Promise<PackageSummary[]> {
  const { rows } = await pool.query(`
    SELECT slug, title, subtitle, package_type, show_in_promociones,
           ${PACKAGE_IMAGES_SUBQUERY} AS images
    FROM packages p
    WHERE is_featured = true AND status = 'published'
    ORDER BY featured_sort_order
  `);
  return rows.map(rowToSummary);
}

export async function getAllPackages(): Promise<PackageSummary[]> {
  const { rows } = await pool.query(`
    SELECT slug, title, subtitle, package_type, show_in_promociones,
           ${PACKAGE_IMAGES_SUBQUERY} AS images
    FROM packages p
    WHERE status = 'published'
    ORDER BY title
  `);
  return rows.map(rowToSummary);
}

export async function getPackageBySlug(slug: string): Promise<PackageDetail | null> {
  const { rows } = await pool.query(
    `SELECT p.id, p.slug, p.title, p.subtitle, p.package_type, p.show_in_promociones,
            p.content, p.duration_days, p.duration_nights, p.price_display_mode,
            p.price_from_clp, p.price_unit, p.included, p.not_included,
            ${PACKAGE_IMAGES_SUBQUERY} AS images,
            r.name AS region_name, r.slug AS region_slug
     FROM packages p
     LEFT JOIN regions r ON r.id = p.region_id
     WHERE p.slug = $1 AND p.status = 'published'`,
    [slug]
  );
  if (rows.length === 0) return null;
  const row = rows[0];
  const packageId = row.id as string;

  const [addons, roomOptions, itinerary] = await Promise.all([
    pool.query(
      "SELECT id, name, price_clp FROM package_addons WHERE package_id = $1 ORDER BY sort_order",
      [packageId]
    ),
    pool.query(
      "SELECT id, label, price_adjustment_clp FROM package_room_options WHERE package_id = $1 ORDER BY sort_order",
      [packageId]
    ),
    pool.query(
      "SELECT day_number, title, description FROM package_itinerary_days WHERE package_id = $1 ORDER BY sort_order",
      [packageId]
    ),
  ]);

  return {
    ...rowToSummary(row),
    content: row.content ?? "",
    durationDays: row.duration_days,
    durationNights: row.duration_nights,
    priceDisplayMode: row.price_display_mode,
    priceFromClp: row.price_from_clp,
    priceUnit: row.price_unit,
    included: row.included,
    notIncluded: row.not_included,
    region: row.region_slug ? { name: row.region_name, slug: row.region_slug } : null,
    addons: addons.rows.map((a) => ({ id: a.id, name: a.name, priceClp: a.price_clp })),
    roomOptions: roomOptions.rows.map((r) => ({
      id: r.id,
      label: r.label,
      priceAdjustmentClp: r.price_adjustment_clp,
    })),
    itinerary: itinerary.rows.map((d) => ({
      dayNumber: d.day_number,
      title: d.title,
      description: d.description,
    })),
  };
}

export type BlogPostSummary = {
  slug: string;
  title: string;
  excerpt: string;
  date: string;
  featuredImage: { node: { sourceUrl: string } } | null;
};

export type BlogPostDetail = BlogPostSummary & { content: string };

function rowToBlogSummary(row: Record<string, unknown>): BlogPostSummary {
  return {
    slug: row.slug as string,
    title: row.title as string,
    excerpt: row.excerpt as string,
    date: (row.published_at as Date).toISOString(),
    featuredImage:
      row.image_id != null
        ? { node: { sourceUrl: buildImageUrl(row.image_id as string, row.image_ext as string) } }
        : null,
  };
}

export async function getAllBlogPosts(): Promise<BlogPostSummary[]> {
  const { rows } = await pool.query(`
    SELECT b.slug, b.title, b.excerpt, b.published_at,
           i.id AS image_id, i.file_extension AS image_ext
    FROM blog_posts b
    LEFT JOIN images i ON i.id = b.featured_image_id
    WHERE b.status = 'published'
    ORDER BY b.published_at DESC
  `);
  return rows.map(rowToBlogSummary);
}

export async function getBlogPostBySlug(slug: string): Promise<BlogPostDetail | null> {
  const { rows } = await pool.query(
    `SELECT b.slug, b.title, b.excerpt, b.content, b.published_at,
            i.id AS image_id, i.file_extension AS image_ext
     FROM blog_posts b
     LEFT JOIN images i ON i.id = b.featured_image_id
     WHERE b.slug = $1 AND b.status = 'published'`,
    [slug]
  );
  if (rows.length === 0) return null;
  return { ...rowToBlogSummary(rows[0]), content: rows[0].content };
}

export async function getPageBySlug(slug: string): Promise<StaticPageData | null> {
  const { rows } = await pool.query(
    `SELECT sp.title, sp.content,
            (SELECT json_agg(json_build_object('id', i.id, 'ext', i.file_extension) ORDER BY spi.sort_order)
             FROM static_page_images spi JOIN images i ON i.id = spi.image_id
             WHERE spi.page_id = sp.id) AS images
     FROM static_pages sp
     WHERE sp.slug = $1`,
    [slug]
  );
  if (rows.length === 0) return null;
  const row = rows[0];
  return {
    title: row.title,
    content: row.content,
    heroGallery: imagesToGallery(row.images),
  };
}
