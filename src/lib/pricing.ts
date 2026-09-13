// Motor de cálculo de cotización — función pura, sin React ni red, para que
// se pueda usar TAL CUAL tanto en el cliente (recalculo en vivo mientras el
// usuario cambia el formulario) como en el servidor (/api/quote, que vuelve
// a calcular todo desde los datos frescos de la base de datos y NUNCA confía en un
// total que mande el navegador). Ver plan: "nunca confiar en un total
// calculado por el navegador".

export type PriceUnit = "per_person" | "per_couple";

export type AddonInput = { id: string; name: string; priceClp: number };
export type RoomOptionInput = { id: string; label: string; priceAdjustmentClp: number };

export type CalculateQuoteInput = {
  basePriceClp: number | null; // null si el paquete está "bajo consulta"
  priceUnit: PriceUnit | null; // null → se asume "per_person" por defecto
  adults: number;
  children: number;
  selectedAddonIds: string[];
  addons: AddonInput[];
  roomOptionId: string | null;
  roomOptions: RoomOptionInput[];
};

export type QuoteBreakdown = {
  passengers: number;
  perPersonBase: number | null;
  passengersSubtotal: number | null;
  addonsTotal: number;
  selectedAddons: AddonInput[];
  roomAdjustment: number;
  selectedRoomLabel: string | null;
  total: number | null; // null si no hay precio base (bajo consulta) — igual se informan addons/room por separado
  depositSuggested: number | null;
};

// Constante única y fácil de ajustar — decisión confirmada con el cliente:
// depósito = 30% del total. Cambiar solo acá si la agencia define otro %.
export const DEPOSIT_RATE = 0.3;

export function calculateQuote(input: CalculateQuoteInput): QuoteBreakdown {
  const adults = Math.max(0, Math.floor(input.adults) || 0);
  const children = Math.max(0, Math.floor(input.children) || 0);
  const passengers = adults + children;

  const unit = input.priceUnit ?? "per_person";

  // Niños pagan igual que un adulto por ahora — no hay tarifa de niño
  // confirmada con el cliente (ver plan, "supuestos abiertos"). Cambiar acá
  // el día que se defina un % de descuento real.
  let perPersonBase: number | null = null;
  let passengersSubtotal: number | null = null;

  if (input.basePriceClp != null) {
    perPersonBase = unit === "per_couple" ? input.basePriceClp / 2 : input.basePriceClp;
    passengersSubtotal = passengers > 0 ? perPersonBase * passengers : 0;
  }

  const selectedAddons = input.addons.filter((a) => input.selectedAddonIds.includes(a.id));
  const addonsTotal = selectedAddons.reduce((sum, a) => sum + a.priceClp, 0);

  const selectedRoom = input.roomOptionId
    ? input.roomOptions.find((r) => r.id === input.roomOptionId) ?? null
    : null;
  const roomAdjustment = selectedRoom?.priceAdjustmentClp ?? 0;

  const total =
    passengersSubtotal != null ? Math.max(0, passengersSubtotal + addonsTotal + roomAdjustment) : null;

  return {
    passengers,
    perPersonBase,
    passengersSubtotal,
    addonsTotal,
    selectedAddons,
    roomAdjustment,
    selectedRoomLabel: selectedRoom?.label ?? null,
    total,
    depositSuggested: total != null ? Math.round(total * DEPOSIT_RATE) : null,
  };
}
