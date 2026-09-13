import { NextResponse } from "next/server";
import { getPackageBySlug } from "@/lib/wp";

// Usado por el formulario de cotización (Client Component) para pedir los
// datos de precio/addons/habitaciones frescos cuando el visitante cambia de
// paquete, sin exponer directo el endpoint de WPGraphQL al navegador.
export async function GET(
  _request: Request,
  { params }: RouteContext<"/api/packages/[slug]">
) {
  const { slug } = await params;
  const pkg = await getPackageBySlug(slug);

  if (!pkg) {
    return NextResponse.json({ message: "Paquete no encontrado" }, { status: 404 });
  }

  return NextResponse.json(pkg);
}
