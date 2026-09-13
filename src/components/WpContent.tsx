import Image from "next/image";
import type { WpPage } from "@/lib/wp";

// Renderiza el HTML que viene del editor de WordPress (bloques/clásico).
// El contenido lo escribe el propio staff en wp-admin, no un usuario público,
// así que renderizarlo tal cual es aceptable acá (no es una entrada no confiable).
export function WpContent({ title, content, heroGallery }: WpPage) {
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
