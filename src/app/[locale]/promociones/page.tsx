import { PackageCard } from "@/components/PackageCard";
import { getAllPackages } from "@/lib/content";

export default async function PromocionesPage() {
  const packages = await getAllPackages();
  // Fiel al sitio original: "Promociones" ahí era el catálogo completo de
  // productos de WooCommerce (29), no solo los marcados 2x1. Hoy es un
  // campo editable por paquete (`show_in_promociones` en el panel) en vez
  // de una lista fija de slugs en código.
  const promociones = packages.filter((pkg) => pkg.showInPromociones);

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
