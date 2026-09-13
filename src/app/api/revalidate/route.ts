import { revalidateTag } from "next/cache";
import { NextResponse, type NextRequest } from "next/server";

// Recibe el webhook que dispara cms/wp-content/mu-plugins/travelonline-revalidate-webhook.php
// en cada save_post, y revalida el tag correspondiente (plan §2).
export async function POST(request: NextRequest) {
  const secret = request.headers.get("x-revalidate-secret");
  if (!secret || secret !== process.env.REVALIDATE_SECRET) {
    return NextResponse.json({ message: "Secreto inválido" }, { status: 401 });
  }

  const body = (await request.json().catch(() => null)) as {
    postType?: string;
    slug?: string;
  } | null;

  if (!body?.postType) {
    return NextResponse.json({ message: "postType requerido" }, { status: 400 });
  }

  // Tags amplios por tipo de contenido; a medida que se agreguen páginas se
  // pueden ir sumando tags más específicos (ej: `package:${slug}`).
  // { expire: 0 } fuerza contenido fresco en la próxima visita (en vez de sw-r
  // con "max") porque esto lo llama un webhook externo (WordPress), no una
  // Server Action del propio usuario — así lo documenta Next.js 16 para este caso.
  revalidateTag(body.postType, { expire: 0 });
  if (body.slug) {
    revalidateTag(`${body.postType}:${body.slug}`, { expire: 0 });
  }

  return NextResponse.json({ revalidated: true, postType: body.postType, slug: body.slug ?? null });
}
