import Image from "next/image";
import type { StaticPageData } from "@/lib/content";

// Renderiza el HTML de una página estática (Nosotros, Contacto) editada
// desde el panel — la escribe el propio staff, no un usuario público, así
// que renderizarla tal cual es aceptable acá (no es una entrada no confiable).
export function StaticPageContent({ title, content, heroGallery }: StaticPageData) {
  return (
    <article className="mx-auto max-w-4xl px-4 py-16 sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">{title}</h1>

      {heroGallery.length > 0 && (
        <div className="mt-8 grid grid-cols-3 gap-3">
          {heroGallery.map((src) => (
            <div key={src} className="relative aspect-video overflow-hidden rounded-lg">
              <Image
                src={src}
                alt=""
                fill
                sizes="(min-width: 640px) 33vw, 100vw"
                className="object-cover"
              />
            </div>
          ))}
        </div>
      )}

      <div
        className="prose prose-neutral mt-8 max-w-none"
        dangerouslySetInnerHTML={{ __html: content }}
      />
    </article>
  );
}
