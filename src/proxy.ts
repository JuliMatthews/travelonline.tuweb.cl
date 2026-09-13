import createMiddleware from "next-intl/middleware";
import { routing } from "./i18n/routing";

const intlProxy = createMiddleware(routing);

// Next.js 16 renombró "middleware.ts" a "proxy.ts" (mismo mecanismo, otro nombre).
export function proxy(request: Parameters<typeof intlProxy>[0]) {
  return intlProxy(request);
}

export const config = {
  // Excluye assets estáticos, imágenes optimizadas y archivos con extensión (favicon, etc.)
  matcher: ["/((?!api|_next|_vercel|.*\\..*).*)"],
};
