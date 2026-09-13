import Image from "next/image";
import { Link } from "@/i18n/navigation";
import type { PackageSummary } from "@/lib/content";

const PACKAGE_TYPE_LABELS: Record<string, string> = {
  circuito: "Circuito",
  todo_incluido: "Todo Incluido",
  combinado: "Combinado",
  promocion_2x1: "Promoción 2x1",
};

export function PackageCard({ pkg }: { pkg: PackageSummary }) {
  const cover = pkg.heroGallery[0];

  return (
    <Link
      href={`/paquetes/${pkg.slug}`}
      className="shine-card group block overflow-hidden rounded-xl border border-black/5 bg-background"
    >
      <div className="relative aspect-[4/3] overflow-hidden bg-linear-to-br from-brand-dark to-brand">
        {cover ? (
          <Image
            src={cover}
            alt={pkg.title}
            fill
            sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
            className="object-cover transition duration-500 group-hover:scale-105"
          />
        ) : (
          <svg
            viewBox="0 0 24 24"
            aria-hidden="true"
            className="absolute left-1/2 top-1/2 h-16 w-16 -translate-x-1/2 -translate-y-1/2 fill-white/15"
          >
            <path d="M2.5 19.5 5 12l6-1.5V4a1.5 1.5 0 0 1 3 0v6.5L20 12l2.5 7.5-8.5-2.5-3.5 2-3.5-2Z" />
          </svg>
        )}
        {pkg.packageType && (
          <span className="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-brand-dark">
            {PACKAGE_TYPE_LABELS[pkg.packageType] ?? pkg.packageType}
          </span>
        )}
      </div>
      <div className="p-4">
        <h3 className="font-display font-semibold text-brand-dark">{pkg.title}</h3>
        {pkg.subtitle && (
          <p className="mt-1 text-sm text-foreground/70">{pkg.subtitle}</p>
        )}
      </div>
    </Link>
  );
}
