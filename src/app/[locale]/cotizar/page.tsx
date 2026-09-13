import { getAllPackages, getPackageBySlug } from "@/lib/content";
import { buildPackageSelectorOptions } from "@/lib/packageFamilies";
import { QuoteForm } from "@/components/QuoteForm";

export default async function CotizarPage({
  searchParams,
}: PageProps<"/[locale]/cotizar">) {
  const { paquete } = await searchParams;
  const initialSlug = typeof paquete === "string" ? paquete : null;

  const [packages, initialPackage] = await Promise.all([
    getAllPackages(),
    initialSlug ? getPackageBySlug(initialSlug) : Promise.resolve(null),
  ]);

  const options = buildPackageSelectorOptions(packages);

  return (
    <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">
        Cotiza tu viaje
      </h1>
      <p className="mt-3 max-w-2xl text-foreground/70">
        Arma tu cotización — el total se actualiza al instante mientras
        cambias pasajeros, habitación o excursiones. Sin costo ni compromiso.
      </p>

      <QuoteForm options={options} initialPackage={initialPackage} />
    </div>
  );
}
