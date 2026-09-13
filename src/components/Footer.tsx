import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";

export function Footer() {
  const t = useTranslations("footer");

  return (
    <footer className="mt-auto border-t border-black/5 bg-brand-light/40">
      <div className="mx-auto max-w-6xl px-4 py-8 text-sm text-foreground/70 sm:px-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <p>Travel Online — Chile</p>
          <div className="flex gap-4">
            <Link href="/legal/condiciones-de-reserva">
              Condiciones de reserva
            </Link>
            <Link href="/legal/politica-de-cookies">Cookies</Link>
            <Link href="/legal/politicas-de-privacidad">Privacidad</Link>
          </div>
        </div>
        <p className="mt-4">
          © {new Date().getFullYear()} Travel Online. {t("rights")}
        </p>
      </div>
    </footer>
  );
}
