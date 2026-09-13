import { PackageCard } from "@/components/PackageCard";
import { getAllPackages } from "@/lib/wp";

export default async function TodoIncluidoPage() {
  const packages = await getAllPackages();
  const todoIncluido = packages.filter((pkg) => pkg.packageType === "todo_incluido");

  return (
    <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">
        Programas Todo Incluido
      </h1>
      <p className="mt-3 max-w-2xl text-foreground/70">
        Vuelos, alojamiento y traslados en un solo precio — para viajar sin
        preocuparte de organizar cada detalle.
      </p>

      {todoIncluido.length === 0 ? (
        <p className="mt-10 text-foreground/60">
          Todavía no hay programas todo incluido cargados.
        </p>
      ) : (
        <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {todoIncluido.map((pkg) => (
            <PackageCard key={pkg.slug} pkg={pkg} />
          ))}
        </div>
      )}
    </div>
  );
}
