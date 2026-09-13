"use client";

import Image from "next/image";
import { useEffect, useMemo, useState } from "react";
import { useRouter } from "@/i18n/navigation";
import { calculateQuote } from "@/lib/pricing";
import { formatPrice, type Currency } from "@/lib/currency";
import type { PackageDetail } from "@/lib/wp";
import { findFamilyContaining, type PackageSelectorOption } from "@/lib/packageFamilies";

// Valor único para el <select> combinando familias y paquetes sueltos en un
// solo espacio de opciones (ej. "family:caribe-romantico" o
// "pkg:circuito-madrid-paris").
function optionValue(opt: PackageSelectorOption): string {
  return opt.kind === "family" ? `family:${opt.id}` : `pkg:${opt.slug}`;
}

export function QuoteForm({
  options,
  initialPackage,
}: {
  options: PackageSelectorOption[];
  initialPackage: PackageDetail | null;
}) {
  const router = useRouter();

  const initialFamily = initialPackage ? findFamilyContaining(options, initialPackage.slug) : null;
  const [selectedTopValue, setSelectedTopValue] = useState(
    initialFamily ? `family:${initialFamily.id}` : initialPackage ? `pkg:${initialPackage.slug}` : ""
  );
  const [selectedMemberSlug, setSelectedMemberSlug] = useState<string | null>(
    initialPackage?.slug ?? null
  );

  const selectedFamily =
    options.find((o) => o.kind === "family" && `family:${o.id}` === selectedTopValue) ?? null;

  const selectedStandalone =
    options.find((o) => o.kind === "package" && `pkg:${o.slug}` === selectedTopValue) ?? null;

  // El slug que realmente hay que cotizar: si hay una familia elegida, es el
  // miembro específico que se seleccionó dentro de ella (o ninguno todavía).
  const selectedSlug =
    selectedFamily?.kind === "family" ? (selectedMemberSlug ?? "") : selectedTopValue.replace(/^pkg:/, "");

  const [pkg, setPkg] = useState<PackageDetail | null>(initialPackage);
  const [loadingPkg, setLoadingPkg] = useState(false);

  const [adults, setAdults] = useState(2);
  const [children, setChildren] = useState(0);
  const [roomOptionId, setRoomOptionId] = useState<string | null>(null);
  const [selectedAddonIds, setSelectedAddonIds] = useState<string[]>([]);
  const [currency, setCurrency] = useState<Currency>("CLP");
  const [preferredDateFrom, setPreferredDateFrom] = useState("");
  const [preferredDateTo, setPreferredDateTo] = useState("");
  // No dejar elegir fechas pasadas en el calendario.
  const todayStr = useMemo(() => new Date().toISOString().slice(0, 10), []);

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [comments, setComments] = useState("");

  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [submitted, setSubmitted] = useState(false);

  // Al cambiar de paquete, pedimos sus datos frescos (precio, addons,
  // habitaciones) — nunca asumimos que lo que ya tenemos sigue vigente.
  useEffect(() => {
    if (!selectedSlug || selectedSlug === initialPackage?.slug) {
      return;
    }
    let cancelled = false;
    setLoadingPkg(true);
    fetch(`/api/packages/${selectedSlug}`)
      .then((res) => (res.ok ? res.json() : null))
      .then((data: PackageDetail | null) => {
        if (!cancelled) {
          setPkg(data);
          setRoomOptionId(null);
          setSelectedAddonIds([]);
        }
      })
      .finally(() => {
        if (!cancelled) setLoadingPkg(false);
      });
    return () => {
      cancelled = true;
    };
  }, [selectedSlug, initialPackage?.slug]);

  const breakdown = useMemo(() => {
    if (!pkg) return null;
    return calculateQuote({
      basePriceClp: pkg.priceFromClp,
      priceUnit: pkg.priceUnit,
      adults,
      children,
      selectedAddonIds,
      addons: pkg.addons.map((a) => ({ id: a.id, name: a.name, priceClp: a.priceClp })),
      roomOptionId,
      roomOptions: pkg.roomOptions.map((r) => ({
        id: r.id,
        label: r.label,
        priceAdjustmentClp: r.priceAdjustmentClp,
      })),
    });
  }, [pkg, adults, children, selectedAddonIds, roomOptionId]);

  function toggleAddon(id: string) {
    setSelectedAddonIds((prev) =>
      prev.includes(id) ? prev.filter((a) => a !== id) : [...prev, id]
    );
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!pkg) return;
    setSubmitting(true);
    setSubmitError(null);

    try {
      const res = await fetch("/api/quote", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          packageSlug: pkg.slug,
          adults,
          children,
          roomOptionId,
          selectedAddonIds,
          preferredDateFrom,
          preferredDateTo,
          passengerName: name,
          passengerEmail: email,
          passengerPhone: phone,
          comments,
        }),
      });

      if (!res.ok) {
        const data = await res.json().catch(() => null);
        throw new Error(data?.message ?? "No se pudo enviar la cotización.");
      }

      setSubmitted(true);
      router.push("/cotizar/gracias");
    } catch (err) {
      setSubmitError(err instanceof Error ? err.message : "Error inesperado.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-[1.3fr_1fr]">
      <div className="space-y-6">
        <div>
          <label className="block text-sm font-semibold text-brand-dark">Paquete</label>
          <select
            required
            value={selectedTopValue}
            onChange={(e) => {
              setSelectedTopValue(e.target.value);
              setSelectedMemberSlug(null);
              setPkg(null);
            }}
            className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
          >
            <option value="" disabled>
              Selecciona un paquete
            </option>
            {options.map((opt) => (
              <option key={optionValue(opt)} value={optionValue(opt)}>
                {opt.kind === "family" ? opt.label : opt.title}
              </option>
            ))}
          </select>

          {selectedFamily?.kind === "family" && (
            <div className="mt-3">
              <p className="text-xs text-foreground/60">Elige el destino:</p>
              <div className="mt-2 grid grid-cols-3 gap-3">
                {selectedFamily.members.map((member) => (
                  <button
                    key={member.slug}
                    type="button"
                    onClick={() => setSelectedMemberSlug(member.slug)}
                    className="flex flex-col items-center gap-1.5"
                  >
                    <span
                      className={`relative block aspect-square w-full overflow-hidden rounded-lg ring-2 transition ${
                        selectedMemberSlug === member.slug
                          ? "ring-brand"
                          : "ring-transparent hover:ring-black/10"
                      }`}
                    >
                      {member.imageUrl ? (
                        <Image
                          src={member.imageUrl}
                          alt={member.title}
                          fill
                          sizes="120px"
                          className="object-cover"
                        />
                      ) : (
                        <span className="flex h-full w-full items-center justify-center bg-brand-light text-xs text-brand-dark/50">
                          Sin foto
                        </span>
                      )}
                    </span>
                    <span className="flex items-center gap-1 text-xs font-medium text-brand-dark">
                      <input
                        type="radio"
                        name="family-member"
                        readOnly
                        checked={selectedMemberSlug === member.slug}
                      />
                      {member.title}
                    </span>
                  </button>
                ))}
              </div>
            </div>
          )}

          {selectedStandalone?.kind === "package" && (
            <div className="mt-3">
              <span className="relative block aspect-square w-28 overflow-hidden rounded-lg ring-2 ring-brand">
                {selectedStandalone.imageUrl ? (
                  <Image
                    src={selectedStandalone.imageUrl}
                    alt={selectedStandalone.title}
                    fill
                    sizes="112px"
                    className="object-cover"
                  />
                ) : (
                  <span className="flex h-full w-full items-center justify-center bg-brand-light text-xs text-brand-dark/50">
                    Sin foto
                  </span>
                )}
              </span>
            </div>
          )}
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-semibold text-brand-dark">Adultos</label>
            <input
              type="number"
              min={1}
              value={adults}
              onChange={(e) => setAdults(Number(e.target.value))}
              className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold text-brand-dark">Niños</label>
            <input
              type="number"
              min={0}
              value={children}
              onChange={(e) => setChildren(Number(e.target.value))}
              className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-semibold text-brand-dark">
            Fecha preferida de viaje
          </label>
          <div className="mt-1 grid grid-cols-2 gap-3">
            <div>
              <label className="block text-xs text-foreground/60">Desde</label>
              <input
                type="date"
                value={preferredDateFrom}
                min={todayStr}
                onChange={(e) => {
                  setPreferredDateFrom(e.target.value);
                  if (preferredDateTo && e.target.value > preferredDateTo) {
                    setPreferredDateTo(e.target.value);
                  }
                }}
                className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
              />
            </div>
            <div>
              <label className="block text-xs text-foreground/60">Hasta</label>
              <input
                type="date"
                value={preferredDateTo}
                min={preferredDateFrom || todayStr}
                onChange={(e) => setPreferredDateTo(e.target.value)}
                className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
              />
            </div>
          </div>
          <p className="mt-1 text-xs text-foreground/50">
            Aproximada — es solo referencial para armar la cotización.
          </p>
        </div>

        {pkg && pkg.roomOptions.length > 0 && (
          <div>
            <label className="block text-sm font-semibold text-brand-dark">
              Tipo de habitación
            </label>
            <div className="mt-2 space-y-2">
              {pkg.roomOptions.map((room) => (
                <label key={room.id} className="flex items-center gap-2 text-sm">
                  <input
                    type="radio"
                    name="room"
                    checked={roomOptionId === room.id}
                    onChange={() => setRoomOptionId(room.id)}
                  />
                  {room.label}
                  {room.priceAdjustmentClp !== 0 && (
                    <span className="text-foreground/60">
                      ({room.priceAdjustmentClp > 0 ? "+" : ""}
                      {formatPrice(room.priceAdjustmentClp, currency)})
                    </span>
                  )}
                </label>
              ))}
            </div>
          </div>
        )}

        {pkg && pkg.addons.length > 0 && (
          <div>
            <label className="block text-sm font-semibold text-brand-dark">
              Excursiones opcionales
            </label>
            <div className="mt-2 space-y-2">
              {pkg.addons.map((addon) => (
                <label key={addon.id} className="flex items-center gap-2 text-sm">
                  <input
                    type="checkbox"
                    checked={selectedAddonIds.includes(addon.id)}
                    onChange={() => toggleAddon(addon.id)}
                  />
                  {addon.name}{" "}
                  <span className="text-foreground/60">
                    (+{formatPrice(addon.priceClp, currency)})
                  </span>
                </label>
              ))}
            </div>
          </div>
        )}

        <div className="border-t border-black/5 pt-6">
          <button
            type="button"
            disabled
            title="Próximamente"
            className="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-brand bg-brand-light/60 px-3 py-2.5 text-sm font-semibold text-brand-dark disabled:cursor-not-allowed"
          >
            <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
              <path
                fill="#FFC107"
                d="M43.6 20.5H42V20H24v8h11.3C33.7 32.6 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.5 5.3 29.5 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.3-.4-3.5z"
              />
              <path
                fill="#FF3D00"
                d="M6.3 14.7l6.6 4.8C14.6 15.3 18.9 12 24 12c3.1 0 5.8 1.1 8 3l6-6C34.5 5.3 29.5 3 24 3 15.9 3 8.9 7.7 6.3 14.7z"
              />
              <path
                fill="#4CAF50"
                d="M24 45c5.4 0 10.3-2.1 14-5.5l-6.5-5.5C29.4 35.8 26.9 36.7 24 36.7c-5.3 0-9.7-3.4-11.3-8.1l-6.6 5.1C8.9 40.4 15.9 45 24 45z"
              />
              <path
                fill="#1976D2"
                d="M43.6 20.5H42V20H24v8h11.3c-.9 2.7-2.6 5-4.8 6.5l6.5 5.5C40.8 36.9 44 31 44 24c0-1.2-.1-2.3-.4-3.5z"
              />
            </svg>
            Cotiza con tu cuenta Google (más rápido)
          </button>
        </div>

        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label className="block text-sm font-semibold text-brand-dark">Nombre</label>
            <input
              required
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold text-brand-dark">Correo</label>
            <input
              required
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold text-brand-dark">Teléfono</label>
            <input
              required
              type="tel"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold text-brand-dark">
              Comentarios (opcional)
            </label>
            <input
              type="text"
              value={comments}
              onChange={(e) => setComments(e.target.value)}
              className="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2"
            />
          </div>
        </div>

        {submitError && <p className="text-sm text-red-600">{submitError}</p>}

        <button
          type="submit"
          disabled={submitting || !pkg || submitted}
          className="rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark disabled:opacity-50"
        >
          {submitting ? "Enviando..." : "Enviar cotización"}
        </button>
      </div>

      <aside className="h-fit rounded-2xl border border-black/5 bg-brand-light/40 p-6">
        <div className="mb-4 flex items-center justify-between">
          <h2 className="font-display font-bold text-brand-dark">Resumen</h2>
          <div className="flex rounded-full border border-black/10 text-xs font-semibold">
            {(["CLP", "USD", "EUR"] as const).map((c) => (
              <button
                key={c}
                type="button"
                onClick={() => setCurrency(c)}
                className={`px-3 py-1 first:rounded-l-full last:rounded-r-full ${
                  currency === c ? "bg-brand text-white" : "text-brand-dark"
                }`}
              >
                {c}
              </button>
            ))}
          </div>
        </div>

        {loadingPkg && <p className="text-sm text-foreground/60">Cargando paquete…</p>}

        {!pkg && !loadingPkg && (
          <p className="text-sm text-foreground/60">Elige un paquete para ver el resumen.</p>
        )}

        {pkg && breakdown && (
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
              <dt className="text-foreground/70">Pasajeros</dt>
              <dd>{breakdown.passengers}</dd>
            </div>
            {breakdown.perPersonBase != null ? (
              <div className="flex justify-between">
                <dt className="text-foreground/70">Precio por persona</dt>
                <dd>{formatPrice(breakdown.perPersonBase, currency)}</dd>
              </div>
            ) : (
              <p className="text-foreground/70">
                Este paquete está bajo consulta — te contactamos con el valor.
              </p>
            )}
            {breakdown.passengersSubtotal != null && (
              <div className="flex justify-between">
                <dt className="text-foreground/70">Subtotal pasajeros</dt>
                <dd>{formatPrice(breakdown.passengersSubtotal, currency)}</dd>
              </div>
            )}
            {breakdown.selectedRoomLabel && breakdown.roomAdjustment !== 0 && (
              <div className="flex justify-between">
                <dt className="text-foreground/70">{breakdown.selectedRoomLabel}</dt>
                <dd>
                  {breakdown.roomAdjustment > 0 ? "+" : ""}
                  {formatPrice(breakdown.roomAdjustment, currency)}
                </dd>
              </div>
            )}
            {breakdown.selectedAddons.map((a) => (
              <div key={a.id} className="flex justify-between">
                <dt className="text-foreground/70">{a.name}</dt>
                <dd>+{formatPrice(a.priceClp, currency)}</dd>
              </div>
            ))}
            {breakdown.total != null && (
              <>
                <div className="flex justify-between border-t border-black/10 pt-2 font-display text-base font-bold text-brand-dark">
                  <dt>Total</dt>
                  <dd>{formatPrice(breakdown.total, currency)}</dd>
                </div>
                <div className="flex justify-between text-xs text-foreground/60">
                  <dt>Depósito sugerido (30%)</dt>
                  <dd>{formatPrice(breakdown.depositSuggested ?? 0, currency)}</dd>
                </div>
              </>
            )}
          </dl>
        )}
      </aside>
    </form>
  );
}
