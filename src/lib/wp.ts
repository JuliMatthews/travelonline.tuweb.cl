const ENDPOINT = process.env.WPGRAPHQL_ENDPOINT ?? "http://localhost:8090/graphql";

/**
 * Fetch a WPGraphQL vía la API nativa `fetch` (no graphql-request) para poder
 * usar las opciones de caché de Next.js (`next.tags`) y así conectar con la
 * revalidación bajo demanda de /api/revalidate (plan §2).
 */
async function wpFetch<T>(
  query: string,
  variables: Record<string, unknown>,
  tags: string[]
): Promise<T> {
  const res = await fetch(ENDPOINT, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ query, variables }),
    next: {
      tags,
      revalidate: 600, // red de seguridad: 10 min, aunque el webhook falle
    },
  });

  const json = await res.json();
  if (json.errors) {
    throw new Error(`WPGraphQL error: ${JSON.stringify(json.errors)}`);
  }
  return json.data as T;
}

export type WpPage = {
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
  regions: { nodes: { name: string; slug: string }[] };
};

const PACKAGE_SUMMARY_FIELDS = /* GraphQL */ `
  slug
  title
  subtitle
  packageType
  heroGallery
`;

export async function getRegionWithPackages(regionSlug: string) {
  const data = await wpFetch<{
    region: { name: string; description: string | null; destinationPackages: { nodes: PackageSummary[] } } | null;
  }>(
    /* GraphQL */ `
      query RegionWithPackages($slug: ID!) {
        region(id: $slug, idType: SLUG) {
          name
          description
          destinationPackages(first: 50) {
            nodes {
              ${PACKAGE_SUMMARY_FIELDS}
            }
          }
        }
      }
    `,
    { slug: regionSlug },
    ["destination_package", "region", `region:${regionSlug}`]
  );

  return data.region;
}

export type RegionOverview = { slug: string; name: string; count: number };

export async function getRegionsOverview(): Promise<RegionOverview[]> {
  const data = await wpFetch<{
    regions: { nodes: { name: string; slug: string; destinationPackages: { nodes: { slug: string }[] } }[] };
  }>(
    /* GraphQL */ `
      query RegionsOverview {
        regions {
          nodes {
            name
            slug
            destinationPackages {
              nodes {
                slug
              }
            }
          }
        }
      }
    `,
    {},
    ["destination_package", "region"]
  );

  return data.regions.nodes.map((r) => ({
    slug: r.slug,
    name: r.name,
    count: r.destinationPackages.nodes.length,
  }));
}

// Selección curada a mano para la vitrina del home — con "first: N" salían
// puros paquetes de playa (los últimos creados por el script de siembra), lo
// que no representaba bien la variedad real del catálogo. Esto es un
// placeholder de la Fase 2 ("elegir los 5-8 paquetes destacados" es una
// decisión de negocio, ver plan §Decisiones abiertas #7) — reemplazar por la
// selección real que defina el cliente, o por un campo "destacado" editable
// en wp-admin si se vuelve algo que el staff deba poder cambiar solo.
const FEATURED_SLUGS = [
  "super-dubai",
  "venecia-a-roma",
  "circuito-china-osos-pandas",
  "caribe-romantico-punta-cana-todo-incluido",
  "circuito-las-vegas-gran-canon",
  "turquia-admirable",
];

export async function getFeaturedPackages(): Promise<PackageSummary[]> {
  const query = /* GraphQL */ `
    query FeaturedPackages(${FEATURED_SLUGS.map((_, i) => `$slug${i}: ID!`).join(", ")}) {
      ${FEATURED_SLUGS.map(
        (_, i) => `p${i}: destinationPackage(id: $slug${i}, idType: SLUG) { ${PACKAGE_SUMMARY_FIELDS} }`
      ).join("\n")}
    }
  `;
  const variables = Object.fromEntries(FEATURED_SLUGS.map((slug, i) => [`slug${i}`, slug]));

  const data = await wpFetch<Record<string, PackageSummary | null>>(
    query,
    variables,
    ["destination_package"]
  );

  return Object.values(data).filter((pkg): pkg is PackageSummary => pkg !== null);
}

// Los 29 productos reales de WooCommerce del sitio legacy — en el sitio
// original, "Promociones" en el menú apunta literalmente al archivo
// completo de productos de WooCommerce (?post_type=product, "29
// resultados"), no a una lista curada de ofertas. Estos slugs replican
// exactamente ese comportamiento. Ver cms/seed/import-legacy-products.php
// (marca cada uno con el meta `legacy_source = product`).
export const LEGACY_PRODUCT_SLUGS = [
  'british', 'buzios-todo-incluido', 'cancun-todo-incluido',
  'caribe-romantico-cancun-todo-incluido', 'caribe-romantico-punta-cana-todo-incluido',
  'caribe-romantico-san-andres-todo-incluido', 'dubai-maravilloso-2x1',
  'europa-en-17-dias', 'gran-tour-europeo-todo-incluido', 'khalifa', 'nilo',
  'paris-alpes-e-italia', 'polski', 'punta-cana-todo-incluido',
  'rio-de-janeiro-buzios', 'rivera-maya-todo-incluido', 'san-andres-todo-incluido',
  'super-dubai', 'super-egipto-con-crucero', 'super-grecia-esencial',
  'super-jordania', 'super-turquia-con-tren', 'tesoros-balcanicos',
  'triangulo-de-oro', 'turquia-2x1', 'turquia-admirable', 'vaporetto',
  'varadero-todo-incluido', 'venecia-a-roma',
];

export async function getAllPackages(): Promise<PackageSummary[]> {
  const data = await wpFetch<{ destinationPackages: { nodes: PackageSummary[] } }>(
    /* GraphQL */ `
      query AllPackages {
        destinationPackages(first: 100) {
          nodes {
            ${PACKAGE_SUMMARY_FIELDS}
          }
        }
      }
    `,
    {},
    ["destination_package"]
  );

  return data.destinationPackages.nodes;
}

export async function getAllPackageSlugs(): Promise<string[]> {
  const data = await wpFetch<{ destinationPackages: { nodes: { slug: string }[] } }>(
    /* GraphQL */ `
      query AllPackageSlugs {
        destinationPackages(first: 200) {
          nodes {
            slug
          }
        }
      }
    `,
    {},
    ["destination_package"]
  );

  return data.destinationPackages.nodes.map((n) => n.slug);
}

export async function getPackageBySlug(slug: string): Promise<PackageDetail | null> {
  const data = await wpFetch<{ destinationPackage: PackageDetail | null }>(
    /* GraphQL */ `
      query PackageBySlug($slug: ID!) {
        destinationPackage(id: $slug, idType: SLUG) {
          ${PACKAGE_SUMMARY_FIELDS}
          content
          durationDays
          durationNights
          priceDisplayMode
          priceFromClp
          priceUnit
          included
          notIncluded
          itinerary {
            dayNumber
            title
            description
          }
          addons {
            id
            name
            priceClp
          }
          roomOptions {
            id
            label
            priceAdjustmentClp
          }
          regions {
            nodes {
              name
              slug
            }
          }
        }
      }
    `,
    { slug },
    ["destination_package", `destination_package:${slug}`]
  );

  return data.destinationPackage;
}

export type BlogPostSummary = {
  slug: string;
  title: string;
  excerpt: string;
  date: string;
  featuredImage: { node: { sourceUrl: string } } | null;
};

export type BlogPostDetail = BlogPostSummary & { content: string };

export async function getAllBlogPostSlugs(): Promise<string[]> {
  const data = await wpFetch<{ posts: { nodes: { slug: string }[] } }>(
    /* GraphQL */ `
      query AllBlogPostSlugs {
        posts(first: 100) {
          nodes {
            slug
          }
        }
      }
    `,
    {},
    ["post"]
  );

  return data.posts.nodes.map((n) => n.slug);
}

export async function getAllBlogPosts(): Promise<BlogPostSummary[]> {
  const data = await wpFetch<{ posts: { nodes: BlogPostSummary[] } }>(
    /* GraphQL */ `
      query AllBlogPosts {
        posts(first: 50) {
          nodes {
            slug
            title
            excerpt
            date
            featuredImage {
              node {
                sourceUrl(size: LARGE)
              }
            }
          }
        }
      }
    `,
    {},
    ["post"]
  );

  return data.posts.nodes;
}

export async function getBlogPostBySlug(slug: string): Promise<BlogPostDetail | null> {
  const data = await wpFetch<{ post: BlogPostDetail | null }>(
    /* GraphQL */ `
      query BlogPostBySlug($slug: ID!) {
        post(id: $slug, idType: SLUG) {
          slug
          title
          excerpt
          content
          date
          featuredImage {
            node {
              sourceUrl(size: LARGE)
            }
          }
        }
      }
    `,
    { slug },
    ["post", `post:${slug}`]
  );

  return data.post;
}

export async function getPageBySlug(slug: string): Promise<WpPage | null> {
  const uri = `/${slug}/`;
  const data = await wpFetch<{ page: WpPage | null }>(
    /* GraphQL */ `
      query PageBySlug($uri: ID!) {
        page(id: $uri, idType: URI) {
          title
          content
          heroGallery
        }
      }
    `,
    { uri },
    ["page", `page:${slug}`]
  );

  return data.page;
}
