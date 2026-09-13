import { notFound } from "next/navigation";
import { getPageBySlug } from "@/lib/wp";
import { WpContent } from "@/components/WpContent";

export default async function NosotrosPage() {
  const page = await getPageBySlug("nosotros");
  if (!page) notFound();

  return <WpContent {...page} />;
}
