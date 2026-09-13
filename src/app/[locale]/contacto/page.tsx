import { notFound } from "next/navigation";
import { getPageBySlug } from "@/lib/content";
import { StaticPageContent } from "@/components/StaticPageContent";

// TODO (Fase 1, pendiente): agregar <LocationMap> acá una vez definido el
// reemplazo único de Maps Widget + WP Google Map (plan §2).
export default async function ContactoPage() {
  const page = await getPageBySlug("contacto");
  if (!page) notFound();

  return <StaticPageContent {...page} />;
}
