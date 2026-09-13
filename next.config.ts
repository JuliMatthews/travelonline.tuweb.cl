import type { NextConfig } from "next";
import createNextIntlPlugin from "next-intl/plugin";

const withNextIntl = createNextIntlPlugin("./src/i18n/request.ts");

const nextConfig: NextConfig = {
  images: {
    // Necesario porque WordPress headless corre en localhost:8090 (misma
    // máquina) — Next.js 16 bloquea por defecto optimizar imágenes desde IPs
    // privadas para prevenir SSRF. Sin riesgo real aquí: todo es local.
    // En producción, WordPress vivirá en un dominio real y esto no aplicará.
    dangerouslyAllowLocalIP: true,
    remotePatterns: [
      {
        // WordPress headless local (ver cms/docker-compose.yml, puerto 8090)
        protocol: "http",
        hostname: "localhost",
        port: "8090",
        pathname: "/wp-content/uploads/**",
      },
      {
        protocol: "https",
        hostname: "**.travelonline.cl",
      },
    ],
  },
};

export default withNextIntl(nextConfig);
