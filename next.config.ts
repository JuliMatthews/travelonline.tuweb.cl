import type { NextConfig } from "next";
import createNextIntlPlugin from "next-intl/plugin";

const withNextIntl = createNextIntlPlugin("./src/i18n/request.ts");

const nextConfig: NextConfig = {
  images: {
    // Necesario porque `admin` (el CMS propio, que ahora sirve las imágenes
    // vía /uploads/[id]) corre en localhost:3001 (misma máquina) — Next.js
    // 16 bloquea por defecto optimizar imágenes desde IPs privadas para
    // prevenir SSRF. Sin riesgo real acá: todo es local. Al desplegar, el
    // panel vivirá en un dominio real (app.travelonline.tuweb.cl) y esto no
    // aplicará — ahí se agrega ese hostname a `remotePatterns` en vez de
    // `dangerouslyAllowLocalIP`.
    dangerouslyAllowLocalIP: true,
    remotePatterns: [
      {
        protocol: "http",
        hostname: "localhost",
        port: "3001",
        pathname: "/uploads/**",
      },
    ],
  },
};

export default withNextIntl(nextConfig);
