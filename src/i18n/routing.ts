import { defineRouting } from "next-intl/routing";

// Fase 5 del plan agrega "en" con contenido curado; por ahora todo vive en "es".
export const routing = defineRouting({
  locales: ["es", "en"],
  defaultLocale: "es",
  localePrefix: {
    mode: "as-needed",
  },
});

export type AppLocale = (typeof routing.locales)[number];
