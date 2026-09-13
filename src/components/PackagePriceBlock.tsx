"use client";

import { useState } from "react";
import { Link } from "@/i18n/navigation";
import { formatPrice, type Currency } from "@/lib/currency";

export function PackagePriceBlock({
  slug,
  priceDisplayMode,
  priceFromClp,
  hasAddons,
  hasRoomOptions,
}: {
  slug: string;
  priceDisplayMode: string | null;
  priceFromClp: number | null;
  hasAddons: boolean;
  hasRoomOptions: boolean;
}) {
  const [currency, setCurrency] = useState<Currency>("CLP");
  const hasPrice = priceDisplayMode === "desde" && priceFromClp != null;

  return (
    <div>
      <div className="flex items-center gap-2">
        <p className="text-sm text-foreground/60">Precio</p>
        {hasPrice && (
          <div className="flex rounded-full border border-black/10 text-[10px] font-semibold">
            {(["CLP", "USD", "EUR"] as const).map((c) => (
              <button
                key={c}
                type="button"
                onClick={() => setCurrency(c)}
                className={`px-2 py-0.5 first:rounded-l-full last:rounded-r-full ${
                  currency === c ? "bg-brand text-white" : "text-brand-dark"
                }`}
              >
                {c}
              </button>
            ))}
          </div>
        )}
      </div>
      <p className="font-semibold text-brand-dark">
        {hasPrice ? `Desde ${formatPrice(priceFromClp!, currency)}` : "Bajo consulta"}
      </p>
      {(hasAddons || hasRoomOptions) && (
        <p className="mt-1 text-xs text-foreground/60">
          {hasAddons && hasRoomOptions
            ? "Excursiones opcionales y tipos de habitación disponibles"
            : hasAddons
              ? "Excursiones opcionales disponibles"
              : "Distintos tipos de habitación disponibles"}
        </p>
      )}
      <Link
        href={`/cotizar?paquete=${slug}`}
        className="mt-3 inline-block rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark"
      >
        Cotizar este paquete
      </Link>
    </div>
  );
}
