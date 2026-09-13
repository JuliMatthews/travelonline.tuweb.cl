import Image from "next/image";
import { notFound } from "next/navigation";
import { Link } from "@/i18n/navigation";
import { getPackageBySlug } from "@/lib/content";
import { PackagePriceBlock } from "@/components/PackagePriceBlock";

const PACKAGE_TYPE_LABELS: Record<string, string> = {
  circuito: "Circuito",
  todo_incluido: "Todo Incluido",
  combinado: "Combinado",
  promocion_2x1: "Promoción 2x1",
};

export default async function PackagePage({
  params,
}: PageProps<"/[locale]/paquetes/[slug]">) {
  const { slug } = await params;
  const pkg = await getPackageBySlug(slug);

  if (!pkg) notFound();

  const included = pkg.included?.split("\n").filter(Boolean) ?? [];
  const notIncluded = pkg.notIncluded?.split("\n").filter(Boolean) ?? [];

  return (
    <article className="mx-auto max-w-4xl px-4 py-16 sm:px-6">
      <div className="flex flex-wrap items-center gap-2 text-sm text-foreground/60">
        {pkg.region && (
          <Link
            href={`/destinos/${pkg.region.slug}`}
            className="rounded-full bg-brand-light px-3 py-1 text-brand-dark"
          >
            {pkg.region.name}
          </Link>
        )}
        {pkg.packageType && (
          <span className="rounded-full border border-black/10 px-3 py-1">
            {PACKAGE_TYPE_LABELS[pkg.packageType] ?? pkg.packageType}
          </span>
        )}
      </div>

      <h1 className="font-display mt-4 text-4xl font-bold text-brand-dark">{pkg.title}</h1>
      {pkg.subtitle && (
        <p className="mt-2 text-lg text-foreground/70">{pkg.subtitle}</p>
      )}

      {pkg.heroGallery.length > 0 && (
        <div className="mt-8 grid grid-cols-2 gap-2 sm:grid-cols-3">
          {pkg.heroGallery.map((src) => (
            <div key={src} className="relative aspect-square overflow-hidden rounded-lg">
              <Image
                src={src}
                alt={pkg.title}
                fill
                sizes="(min-width: 640px) 33vw, 50vw"
                className="object-cover"
              />
            </div>
          ))}
        </div>
      )}

      <div className="mt-8 flex flex-wrap items-center gap-6 rounded-xl bg-brand-light/50 p-6">
        {(pkg.durationDays || pkg.durationNights) && (
          <div>
            <p className="text-sm text-foreground/60">Duración</p>
            <p className="font-semibold text-brand-dark">
              {pkg.durationDays ?? "—"} días / {pkg.durationNights ?? "—"} noches
            </p>
          </div>
        )}
        <div className="ml-auto">
          <PackagePriceBlock
            slug={pkg.slug}
            priceDisplayMode={pkg.priceDisplayMode}
            priceFromClp={pkg.priceFromClp}
            hasAddons={pkg.addons.length > 0}
            hasRoomOptions={pkg.roomOptions.length > 0}
          />
        </div>
      </div>

      {pkg.itinerary.length > 0 && (
        <section className="mt-10">
          <h2 className="font-display text-2xl font-bold text-brand-dark">Itinerario</h2>
          <ol className="mt-4 space-y-4">
            {pkg.itinerary.map((day) => (
              <li key={day.dayNumber} className="rounded-lg border border-black/5 p-4">
                <p className="font-semibold text-brand-dark">
                  Día {day.dayNumber} — {day.title}
                </p>
                <div
                  className="prose prose-neutral prose-sm mt-2 max-w-none"
                  dangerouslySetInnerHTML={{ __html: day.description }}
                />
              </li>
            ))}
          </ol>
        </section>
      )}

      {(included.length > 0 || notIncluded.length > 0) && (
        <section className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2">
          {included.length > 0 && (
            <div>
              <h3 className="font-semibold text-brand-dark">Incluye</h3>
              <ul className="mt-2 list-inside list-disc text-foreground/80">
                {included.map((item) => (
                  <li key={item}>{item}</li>
                ))}
              </ul>
            </div>
          )}
          {notIncluded.length > 0 && (
            <div>
              <h3 className="font-semibold text-brand-dark">No incluye</h3>
              <ul className="mt-2 list-inside list-disc text-foreground/80">
                {notIncluded.map((item) => (
                  <li key={item}>{item}</li>
                ))}
              </ul>
            </div>
          )}
        </section>
      )}
    </article>
  );
}
