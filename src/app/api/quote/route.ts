import { NextResponse, type NextRequest } from "next/server";
import { z } from "zod";
import { getPackageBySlug } from "@/lib/wp";
import { calculateQuote } from "@/lib/pricing";
import { pool } from "@/lib/db";

const QuoteRequestSchema = z.object({
  packageSlug: z.string().min(1),
  adults: z.number().int().min(1),
  children: z.number().int().min(0),
  roomOptionId: z.string().nullable(),
  selectedAddonIds: z.array(z.string()),
  preferredDateFrom: z.string().optional().default(""),
  preferredDateTo: z.string().optional().default(""),
  passengerName: z.string().min(1),
  passengerEmail: z.string().email(),
  passengerPhone: z.string().min(1),
  comments: z.string().optional().default(""),
});

export async function POST(request: NextRequest) {
  const json = await request.json().catch(() => null);
  const parsed = QuoteRequestSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json(
      { message: "Datos inválidos", issues: parsed.error.issues },
      { status: 400 }
    );
  }

  const input = parsed.data;

  // Autoritativo: se vuelve a pedir el paquete a WordPress y se recalcula
  // TODO desde cero acá. Cualquier precio/total que el navegador haya
  // mandado (no debería mandar ninguno, pero por si acaso) se ignora por
  // completo — ver plan, "nunca confiar en un total calculado por el
  // navegador".
  const pkg = await getPackageBySlug(input.packageSlug);
  if (!pkg) {
    return NextResponse.json({ message: "Paquete no encontrado" }, { status: 404 });
  }

  const breakdown = calculateQuote({
    basePriceClp: pkg.priceFromClp,
    priceUnit: pkg.priceUnit,
    adults: input.adults,
    children: input.children,
    selectedAddonIds: input.selectedAddonIds,
    addons: pkg.addons.map((a) => ({ id: a.id, name: a.name, priceClp: a.priceClp })),
    roomOptionId: input.roomOptionId,
    roomOptions: pkg.roomOptions.map((r) => ({
      id: r.id,
      label: r.label,
      priceAdjustmentClp: r.priceAdjustmentClp,
    })),
  });

  const selectedRoom = input.roomOptionId
    ? pkg.roomOptions.find((r) => r.id === input.roomOptionId) ?? null
    : null;

  const { rows } = await pool.query(
    `INSERT INTO quote_requests (
      package_slug, package_title, adults, children,
      room_option_id, room_option_label, selected_addon_ids, selected_addons_json,
      per_person_base_clp, passengers_subtotal_clp, addons_total_clp, room_adjustment_clp,
      total_clp, deposit_suggested_clp,
      preferred_date_from, preferred_date_to, passenger_name, passenger_email, passenger_phone, comments
    ) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20)
    RETURNING id`,
    [
      pkg.slug,
      pkg.title,
      input.adults,
      input.children,
      selectedRoom?.id ?? null,
      selectedRoom?.label ?? null,
      breakdown.selectedAddons.map((a) => a.id),
      JSON.stringify(breakdown.selectedAddons),
      breakdown.perPersonBase,
      breakdown.passengersSubtotal,
      breakdown.addonsTotal,
      breakdown.roomAdjustment,
      breakdown.total,
      breakdown.depositSuggested,
      input.preferredDateFrom || null,
      input.preferredDateTo || null,
      input.passengerName,
      input.passengerEmail,
      input.passengerPhone,
      input.comments,
    ]
  );

  const quoteId = rows[0].id as number;

  // TODO (Fase 3, pendiente de infraestructura de correo — Resend/SMTP no
  // configurado aún en este proyecto Next.js, ver plan §3): por ahora se dejan
  // constancia en el log del servidor. El destinatario real ya está definido
  // vía variable de entorno (QUOTE_NOTIFICATION_EMAILS) para poder conectar
  // el envío real sin tocar esta lógica — esto es justo lo que corrige la
  // clase de incidente de "s.valdes" (destinatario hardcodeado).
  const recipients = process.env.QUOTE_NOTIFICATION_EMAILS ?? "(sin configurar)";
  console.log(
    `[quote #${quoteId}] Nueva cotización de ${input.passengerName} (${input.passengerEmail}) ` +
      `para "${pkg.title}" — total: $${breakdown.total ?? "bajo consulta"} CLP. ` +
      `Notificar a: ${recipients}`
  );

  return NextResponse.json({
    id: quoteId,
    breakdown,
  });
}
