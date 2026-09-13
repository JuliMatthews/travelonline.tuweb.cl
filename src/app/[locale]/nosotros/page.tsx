import { notFound } from "next/navigation";
import { getPageBySlug } from "@/lib/content";
import { StaticPageContent } from "@/components/StaticPageContent";

export default async function NosotrosPage() {
  const page = await getPageBySlug("nosotros");
  if (!page) notFound();

  return <StaticPageContent {...page} />;
}
