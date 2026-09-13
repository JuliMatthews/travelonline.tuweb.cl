import { Link } from "@/i18n/navigation";

export default function CotizarGraciasPage() {
  return (
    <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">
        ¡Recibimos tu cotización!
      </h1>
      <p className="mt-4 text-foreground/70">
        Nuestro equipo va a revisar los detalles y te va a contactar a la
        brevedad para confirmar el valor final y coordinar los siguientes
        pasos.
      </p>
      <Link
        href="/"
        className="mt-8 inline-block rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark"
      >
        Volver al inicio
      </Link>
    </div>
  );
}
