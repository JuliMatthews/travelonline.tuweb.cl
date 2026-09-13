// Fuente única de las 6 regiones/hubs del plan (§1 y §2). Cuando WPGraphQL esté
// conectado, los conteos de paquetes por región se completarán con datos reales;
// por ahora sirve para armar la navegación sin depender de WordPress.
export const REGION_SLUGS = [
  "europa",
  "asia",
  "america",
  "medio-oriente",
  "africa",
  "combinados",
] as const;

export type RegionSlug = (typeof REGION_SLUGS)[number];

// Imagen ilustrativa por región para la tira "Explora por región" del home.
// Vive como asset estático de Next.js (no en WordPress) porque hoy es solo
// una imagen de referencia por región, no contenido editable por el staff —
// si más adelante se puebla el CPT `region_hub` con su propio `hero_image`
// (plan §1), esto se reemplaza por esa fuente.
export const REGION_IMAGES: Record<RegionSlug, string> = {
  europa: "/regions/europa.jpg",
  asia: "/regions/asia.jpg",
  america: "/regions/america.jpg",
  "medio-oriente": "/regions/medio-oriente.jpg",
  africa: "/regions/africa.jpg",
  combinados: "/regions/combinados.jpg",
};
