import type { PackageSummary } from "@/lib/wp";

// Agrupa paquetes que son "el mismo producto, distinto destino" bajo una
// sola entrada en el selector de /cotizar (pedido de Julio: menos ruido en
// la lista + más interactivo). Al elegir el grupo, el formulario muestra
// tarjetas pequeñas con foto para elegir el destino específico dentro del
// grupo. Se puede sumar más grupos acá sin tocar el resto del código —no
// todos los paquetes necesitan pertenecer a uno.
export type PackageFamily = {
  id: string;
  label: string;
  memberSlugs: string[];
};

export const PACKAGE_FAMILIES: PackageFamily[] = [
  {
    id: "caribe-romantico",
    label: "Caribe Romántico (Todo Incluido)",
    memberSlugs: [
      "caribe-romantico-cancun-todo-incluido",
      "caribe-romantico-punta-cana-todo-incluido",
      "caribe-romantico-san-andres-todo-incluido",
    ],
  },
  {
    id: "circuito-madrid",
    label: "Circuito Madrid",
    memberSlugs: [
      "circuito-madrid-paris",
      "circuito-madrid-roma",
      "circuito-madrid-portugal-andalucia-y-marruecos",
    ],
  },
  {
    id: "circuito-las-vegas",
    label: "Circuito Las Vegas",
    memberSlugs: ["circuito-las-vegas-gran-canon", "circuito-las-vegas-grandes-parques"],
  },
];

export type FamilyMember = { slug: string; title: string; imageUrl: string | null };

export type PackageSelectorOption =
  | { kind: "package"; slug: string; title: string; imageUrl: string | null }
  | { kind: "family"; id: string; label: string; members: FamilyMember[] };

/** Nombre corto del miembro dentro de su grupo — se muestra bajo la foto,
 * no el título completo (ej. "Cancún" en vez de "Caribe Romántico: Cancún
 * – Todo Incluido"), tomando lo que sigue al primer separador del título. */
function shortMemberLabel(title: string): string {
  const parts = title.split(/[:–-]/);
  return parts.length > 1 ? parts[1].replace(/todo incluido/i, "").trim() : title;
}

export function buildPackageSelectorOptions(packages: PackageSummary[]): PackageSelectorOption[] {
  const bySlug = new Map(packages.map((p) => [p.slug, p]));
  const groupedSlugs = new Set(PACKAGE_FAMILIES.flatMap((f) => f.memberSlugs));

  const familyOptions: PackageSelectorOption[] = PACKAGE_FAMILIES.map((family) => ({
    kind: "family",
    id: family.id,
    label: family.label,
    members: family.memberSlugs
      .map((slug) => bySlug.get(slug))
      .filter((p): p is PackageSummary => Boolean(p))
      .map((p) => ({
        slug: p.slug,
        title: shortMemberLabel(p.title),
        imageUrl: p.heroGallery[0] ?? null,
      })),
  }));

  const standaloneOptions: PackageSelectorOption[] = packages
    .filter((p) => !groupedSlugs.has(p.slug))
    .map((p) => ({
      kind: "package",
      slug: p.slug,
      title: p.title,
      imageUrl: p.heroGallery[0] ?? null,
    }));

  return [...familyOptions, ...standaloneOptions].sort((a, b) => {
    const labelA = a.kind === "family" ? a.label : a.title;
    const labelB = b.kind === "family" ? b.label : b.title;
    return labelA.localeCompare(labelB, "es");
  });
}

/** Dado un slug ya elegido (ej. viene de ?paquete=... en la URL), encuentra
 * a qué grupo pertenece, si pertenece a alguno. */
export function findFamilyContaining(
  options: PackageSelectorOption[],
  slug: string
): Extract<PackageSelectorOption, { kind: "family" }> | null {
  for (const opt of options) {
    if (opt.kind === "family" && opt.members.some((m) => m.slug === slug)) {
      return opt;
    }
  }
  return null;
}
