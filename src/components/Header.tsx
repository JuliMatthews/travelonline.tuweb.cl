"use client";

import { useTranslations } from "next-intl";
import { Link, usePathname } from "@/i18n/navigation";
import { REGION_SLUGS } from "@/lib/regions";

// Reemplaza "Max Mega Menu" (§2): un menú simple alimentado por REGION_SLUGS.
// El item de la página en la que ya estás se oculta del menú (pedido
// explícito: no mostrar "Nosotros" estando en /nosotros, etc.) — se marca
// "activo" con startsWith para que /destinos/europa también cuente como
// "estamos en Destinos".
type NavItem = {
  href: string;
  label: string;
  active: boolean;
};

export function Header() {
  const t = useTranslations("nav");
  const tr = useTranslations("regions");
  const pathname = usePathname();

  const isActive = (href: string) =>
    href === "/" ? pathname === "/" : pathname === href || pathname.startsWith(`${href}/`);

  const navItems: NavItem[] = [
    { href: "/nosotros", label: t("about"), active: isActive("/nosotros") },
    { href: "/destinos", label: t("destinations"), active: isActive("/destinos") },
    {
      href: "/programas/todo-incluido",
      label: t("allInclusive"),
      active: isActive("/programas/todo-incluido"),
    },
    { href: "/promociones", label: t("promotions"), active: isActive("/promociones") },
    { href: "/blog", label: t("blog"), active: isActive("/blog") },
    { href: "/contacto", label: t("contact"), active: isActive("/contacto") },
  ];

  return (
    <header className="sticky top-0 z-40 border-b border-black/5 bg-background/90 backdrop-blur">
      <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <Link
          href="/"
          className="font-display rounded-full bg-brand-dark px-4 py-2 text-sm font-bold text-white transition hover:bg-brand"
        >
          Travel Online
        </Link>

        <nav className="hidden items-center gap-6 text-sm font-medium md:flex">
          {navItems.map((item) =>
            item.active ? null : item.href === "/destinos" ? (
              <div key={item.href} className="group relative">
                <Link href={item.href}>{item.label}</Link>
                <div className="invisible absolute left-0 top-full grid w-56 grid-cols-1 gap-1 rounded-lg border border-black/5 bg-background p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
                  {REGION_SLUGS.map((slug) => (
                    <Link
                      key={slug}
                      href={`/destinos/${slug}`}
                      className="rounded-md px-3 py-2 hover:bg-brand-light"
                    >
                      {tr(slug)}
                    </Link>
                  ))}
                </div>
              </div>
            ) : (
              <Link key={item.href} href={item.href}>
                {item.label}
              </Link>
            )
          )}
        </nav>

        <Link
          href="/cotizar"
          className="rounded-full bg-[#25D366] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1ebe57]"
        >
          {t("quote")}
        </Link>
      </div>
    </header>
  );
}
