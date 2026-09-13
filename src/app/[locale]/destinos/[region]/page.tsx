import { notFound } from "next/navigation";
import { getRegionWithPackages } from "@/lib/wp";
import { PackageCard } from "@/components/PackageCard";
import { REGION_SLUGS } from "@/lib/regions";

export function generateStaticParams() {
  return REGION_SLUGS.map((region) => ({ region }));
}

export default async function RegionPage({
  params,
}: PageProps<"/[locale]/destinos/[region]">) {
  const { region: regionSlug } = await params;
  const region = await getRegionWithPackages(regionSlug);

  if (!region) notFound();

  const packages = region.destinationPackages.nodes;

  return (
    <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">{region.name}</h1>
      {region.description && (
        <p className="mt-3 max-w-2xl text-foreground/70">{region.description}</p>
      )}

      {packages.length === 0 ? (
        <p className="mt-10 text-foreground/60">
          Todavía no hay paquetes cargados para esta región.
        </p>
      ) : (
        <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {packages.map((pkg) => (
            <PackageCard key={pkg.slug} pkg={pkg} />
          ))}
        </div>
      )}
    </div>
  );
}
