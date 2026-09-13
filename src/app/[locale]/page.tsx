import Image from "next/image";
import { useTranslations } from "next-intl";
import { setRequestLocale } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { PackageCard } from "@/components/PackageCard";
import { getFeaturedPackages, getRegionsOverview } from "@/lib/content";
import { REGION_IMAGES, type RegionSlug } from "@/lib/regions";

export default async function HomePage({
  params,
}: PageProps<"/[locale]">) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [regions, featured] = await Promise.all([
    getRegionsOverview(),
    getFeaturedPackages(),
  ]);

  return <Home regions={regions} featured={featured} />;
}

function Home({
  regions,
  featured,
}: {
  regions: Awaited<ReturnType<typeof getRegionsOverview>>;
  featured: Awaited<ReturnType<typeof getFeaturedPackages>>;
}) {
  const t = useTranslations("home");
  const trRaw = useTranslations("regions");
  const tr = trRaw as unknown as (key: string) => string;

  return (
    <>
      <section className="relative overflow-hidden bg-brand-dark px-4 py-20 text-center sm:py-28">
        <video
          className="hero-video absolute inset-0 h-full w-full object-cover opacity-60"
          autoPlay
          muted
          loop
          playsInline
          preload="auto"
          aria-hidden="true"
        >
          <source src="/video/hero-clouds.mp4" type="video/mp4" />
        </video>
        <div
          aria-hidden
          className="absolute inset-0 bg-linear-to-b from-brand-dark/90 via-brand-dark/70 to-brand/80"
        />
        <div
          aria-hidden
          className="pointer-events-none absolute -top-32 right-[-10%] h-96 w-96 rounded-full opacity-30 blur-3xl"
          style={{ background: "radial-gradient(circle, var(--accent), transparent 70%)" }}
        />
        <div className="relative mx-auto max-w-3xl">
          <h1 className="font-display text-4xl font-bold tracking-tight text-white sm:text-6xl">
            {t("heroTitle")}
          </h1>
          <p className="mx-auto mt-5 max-w-2xl text-lg text-white/80">
            {t("heroSubtitle")}
          </p>
          <div className="mt-9 flex flex-wrap items-center justify-center gap-4">
            <Link
              href="/cotizar"
              className="rounded-full bg-accent px-7 py-3 font-semibold text-accent-ink transition hover:brightness-105"
            >
              {t("ctaQuote")}
            </Link>
            <Link
              href="/destinos"
              className="rounded-full border border-white/40 px-7 py-3 font-semibold text-white transition hover:bg-white/10"
            >
              {t("ctaDestinations")}
            </Link>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
        <div className="mb-8 flex items-end justify-between gap-4">
          <h2 className="font-display text-2xl font-bold text-brand-dark sm:text-3xl">
            Explora por región
          </h2>
          <Link href="/destinos" className="text-sm font-semibold text-brand hover:text-brand-dark">
            Ver todas →
          </Link>
        </div>

        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
          {regions.map((region) => (
            <Link
              key={region.slug}
              href={`/destinos/${region.slug}`}
              className="shine-card group relative flex aspect-4/5 flex-col justify-between overflow-hidden rounded-2xl p-5 text-white sm:aspect-square"
            >
              <Image
                src={REGION_IMAGES[region.slug as RegionSlug]}
                alt=""
                fill
                sizes="(min-width: 1024px) 16vw, (min-width: 640px) 30vw, 45vw"
                className="object-cover transition duration-500 group-hover:scale-105"
              />
              <div className="absolute inset-0 bg-linear-to-t from-brand-dark/90 via-brand-dark/25 to-brand-dark/10" />
              <span className="relative font-display text-lg font-bold leading-tight drop-shadow-sm">
                {tr(region.slug) || region.name}
              </span>
              <span className="relative mt-6 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
                {region.count} {region.count === 1 ? "paquete" : "paquetes"}
              </span>
            </Link>
          ))}
        </div>
      </section>

      {featured.length > 0 && (
        <section className="border-t border-black/5 bg-brand-light/40 px-4 py-16 sm:px-6 sm:py-20">
          <div className="mx-auto max-w-6xl">
            <div className="mb-8 flex items-end justify-between gap-4">
              <h2 className="font-display text-2xl font-bold text-brand-dark sm:text-3xl">
                Paquetes destacados
              </h2>
            </div>
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {featured.map((pkg) => (
                <PackageCard key={pkg.slug} pkg={pkg} />
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  );
}
