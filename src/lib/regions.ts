// Fuente única de las 6 regiones/hubs del plan. Los conteos de paquetes por
// región salen de la base de datos (ver content.ts); estos slugs/nombres son
// fijos y no forman parte del contenido editable del panel (fuera de alcance
// por ahora — ver plan, Fase 6).
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
// Vive como asset estático de Next.js (no en el panel) porque hoy es solo
// una imagen de referencia por región, no contenido editable por el staff —
// si más adelante se quiere hacer editable, es trabajo aparte (fuera de
// alcance del plan actual).
export const REGION_IMAGES: Record<RegionSlug, string> = {
  europa: "/regions/europa.jpg",
  asia: "/regions/asia.jpg",
  america: "/regions/america.jpg",
  "medio-oriente": "/regions/medio-oriente.jpg",
  africa: "/regions/africa.jpg",
  combinados: "/regions/combinados.jpg",
};
