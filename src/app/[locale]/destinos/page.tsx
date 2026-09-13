import Image from "next/image";
import { useTranslations } from "next-intl";
import { setRequestLocale } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { REGION_IMAGES, type RegionSlug } from "@/lib/regions";
import { getRegionsOverview } from "@/lib/content";

export default async function DestinosPage({
  params,
}: PageProps<"/[locale]/destinos">) {
  const { locale } = await params;
  setRequestLocale(locale);

  const regions = await getRegionsOverview();

  return <Destinos regions={regions} />;
}

function Destinos({
  regions,
}: {
  regions: Awaited<ReturnType<typeof getRegionsOverview>>;
}) {
  const trRaw = useTranslations("regions");
  const tr = trRaw as unknown as (key: string) => string;

  return (
    <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">Destinos</h1>
      <p className="mt-3 max-w-2xl text-foreground/70">
        Elige una región para ver los circuitos, paquetes todo incluido y
        combinados que armamos ahí.
      </p>

      <div className="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {regions.map((region) => (
          <Link
            key={region.slug}
            href={`/destinos/${region.slug}`}
            className="shine-card group relative flex aspect-4/3 flex-col justify-between overflow-hidden rounded-2xl p-6 text-white"
          >
            <Image
              src={REGION_IMAGES[region.slug as RegionSlug]}
              alt=""
              fill
              sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
              className="object-cover transition duration-500 group-hover:scale-105"
            />
            <div className="absolute inset-0 bg-linear-to-t from-brand-dark/90 via-brand-dark/25 to-brand-dark/10" />
            <span className="relative font-display text-2xl font-bold leading-tight drop-shadow-sm">
              {tr(region.slug) || region.name}
            </span>
            <span className="relative mt-6 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
              {region.count} {region.count === 1 ? "paquete" : "paquetes"}
            </span>
          </Link>
        ))}
      </div>
    </div>
  );
}
