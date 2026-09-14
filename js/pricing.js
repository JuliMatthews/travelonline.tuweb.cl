// Traducción 1:1 de inc/pricing.php (calculate_quote) — usada para el
// recálculo instantáneo en el navegador mientras el usuario cambia pasajeros,
// habitación o excursiones. El total real y autoritativo SIEMPRE se calcula
// de nuevo en el servidor (api-quote-submit.php) — esto es solo UX.
const DEPOSIT_RATE = 0.3;

function calculateQuote(input) {
  const adults = Math.max(0, parseInt(input.adults, 10) || 0);
  const children = Math.max(0, parseInt(input.children, 10) || 0);
  const passengers = adults + children;

  const unit = input.priceUnit || "per_person";
  const basePriceClp = input.basePriceClp ?? null;

  let perPersonBase = null;
  let passengersSubtotal = null;
  if (basePriceClp !== null) {
    perPersonBase = unit === "per_couple" ? basePriceClp / 2 : basePriceClp;
    passengersSubtotal = passengers > 0 ? perPersonBase * passengers : 0;
  }

  const selectedAddonIds = input.selectedAddonIds || [];
  const selectedAddons = (input.addons || []).filter((a) => selectedAddonIds.includes(a.id));
  const addonsTotal = selectedAddons.reduce((sum, a) => sum + a.priceClp, 0);

  let selectedRoom = null;
  if (input.roomOptionId) {
    selectedRoom = (input.roomOptions || []).find((r) => r.id === input.roomOptionId) || null;
  }
  const roomAdjustment = selectedRoom ? selectedRoom.priceAdjustmentClp : 0;

  const total = passengersSubtotal !== null ? Math.max(0, passengersSubtotal + addonsTotal + roomAdjustment) : null;

  return {
    passengers,
    perPersonBase,
    passengersSubtotal,
    addonsTotal,
    selectedAddons,
    roomAdjustment,
    selectedRoomLabel: selectedRoom ? selectedRoom.label : null,
    total,
    depositSuggested: total !== null ? Math.round(total * DEPOSIT_RATE) : null,
  };
}

const CLP_PER_UNIT = { CLP: 1, USD: 950, EUR: 1020 };
const CURRENCY_SYMBOL = { CLP: "$", USD: "US$", EUR: "€" };

function formatPrice(clp, currency) {
  const amount = clp / CLP_PER_UNIT[currency];
  return CURRENCY_SYMBOL[currency] + Math.round(amount).toLocaleString("es-CL");
}
