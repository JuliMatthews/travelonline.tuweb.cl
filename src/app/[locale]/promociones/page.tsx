import { PackageCard } from "@/components/PackageCard";
import { getAllPackages, LEGACY_PRODUCT_SLUGS } from "@/lib/wp";

export default async function PromocionesPage() {
  const packages = await getAllPackages();
  // Fiel al sitio original: "Promociones" ahí es el catálogo completo de
  // productos de WooCommerce (29), no solo los marcados 2x1 — ver la nota
  // en LEGACY_PRODUCT_SLUGS.
  const promociones = packages.filter((pkg) => LEGACY_PRODUCT_SLUGS.includes(pkg.slug));

  return (
    <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">
        Promociones
      </h1>
      <p className="mt-3 max-w-2xl text-foreground/70">
        Nuestro catálogo completo de programas — {promociones.length} destinos
        disponibles, incluyendo ofertas 2x1 por tiempo limitado.
      </p>

      {promociones.length === 0 ? (
        <p className="mt-10 text-foreground/60">
          No hay promociones activas por el momento.
        </p>
      ) : (
        <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {promociones.map((pkg) => (
            <PackageCard key={pkg.slug} pkg={pkg} />
          ))}
        </div>
      )}
    </div>
  );
}
