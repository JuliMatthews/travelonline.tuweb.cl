// Toda la lógica de precios (pricing.ts) trabaja siempre en CLP — esto es
// solo para MOSTRAR el número en otra moneda si el visitante lo prefiere.
// Tasa fija, no oficial ni actualizada automáticamente — decisión del
// cliente de partir simple; ajustar acá si la agencia define otra tasa.
export const CLP_PER_USD = 950;
export const CLP_PER_EUR = 1020;

export type Currency = "CLP" | "USD" | "EUR";

const CLP_PER_UNIT: Record<Currency, number> = {
  CLP: 1,
  USD: CLP_PER_USD,
  EUR: CLP_PER_EUR,
};

const LOCALE_BY_CURRENCY: Record<Currency, string> = {
  CLP: "es-CL",
  USD: "en-US",
  EUR: "de-DE",
};

export function convertFromClp(clp: number, currency: Currency): number {
  return clp / CLP_PER_UNIT[currency];
}

export function formatPrice(clp: number, currency: Currency): string {
  const amount = convertFromClp(clp, currency);
  return new Intl.NumberFormat(LOCALE_BY_CURRENCY[currency], {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
  }).format(amount);
}
