// Lógica interactiva de /cotizar: selector de paquete (con familias),
// habitación/excursiones, recálculo en vivo con pricing.js, y envío del
// formulario a api-quote-submit.php. El servidor SIEMPRE recalcula el total
// de nuevo — este JS es solo para que la persona vea el precio al instante.
(function () {
  const dataEl = document.getElementById("cotizar-data");
  const { options, initialPackage } = JSON.parse(dataEl.textContent);

  const topSelect = document.getElementById("top-select");
  const familyMembersBlock = document.getElementById("family-members");
  const familyMembersGrid = document.getElementById("family-members-grid");
  const standaloneImageBlock = document.getElementById("standalone-image");
  const standaloneImageImg = document.getElementById("standalone-image-img");
  const adultsInput = document.getElementById("adults");
  const childrenInput = document.getElementById("children");
  const roomBlock = document.getElementById("room-options-block");
  const roomList = document.getElementById("room-options-list");
  const addonsBlock = document.getElementById("addons-block");
  const addonsList = document.getElementById("addons-list");
  const summaryContent = document.getElementById("summary-content");
  const currencyToggle = document.getElementById("currency-toggle");
  const submitBtn = document.getElementById("submit-btn");
  const submitError = document.getElementById("submit-error");
  const form = document.getElementById("quote-form");

  let currentPackage = null;
  let currency = "CLP";

  function findFamilyContaining(slug) {
    return options.find((o) => o.kind === "family" && o.members.some((m) => m.slug === slug)) || null;
  }

  // Poblar el selector principal: familias primero (si hay), luego paquetes sueltos.
  for (const opt of options) {
    const el = document.createElement("option");
    if (opt.kind === "family") {
      el.value = "family:" + opt.id;
      el.textContent = opt.label;
    } else {
      el.value = "pkg:" + opt.slug;
      el.textContent = opt.title;
    }
    topSelect.appendChild(el);
  }

  function renderFamilyMembers(family, selectedSlug) {
    familyMembersGrid.innerHTML = "";
    for (const m of family.members) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.dataset.slug = m.slug;
      btn.className =
        "group flex flex-col items-center gap-1 rounded-lg p-1 text-center " +
        (m.slug === selectedSlug ? "ring-2 ring-brand" : "ring-1 ring-black/10");
      btn.innerHTML =
        '<span class="relative block aspect-square w-full overflow-hidden rounded-md">' +
        '<img src="' + (m.imageUrl || "") + '" alt="" class="h-full w-full object-cover">' +
        "</span>" +
        '<span class="text-[11px] leading-tight text-foreground/80">' + m.title + "</span>";
      btn.addEventListener("click", () => {
        familyMembersGrid.querySelectorAll("button").forEach((b) => {
          b.classList.toggle("ring-2", b === btn);
          b.classList.toggle("ring-brand", b === btn);
          b.classList.toggle("ring-1", b !== btn);
          b.classList.toggle("ring-black/10", b !== btn);
        });
        loadPackage(m.slug);
      });
      familyMembersGrid.appendChild(btn);
    }
    familyMembersBlock.classList.remove("hidden");
  }

  async function loadPackage(slug) {
    currentPackage = null;
    renderSummary();
    const res = await fetch("/api-package-lookup.php?slug=" + encodeURIComponent(slug));
    const data = await res.json();
    if (!data.ok) return;
    currentPackage = data.package;
    renderRoomOptions();
    renderAddons();
    renderSummary();
  }

  function renderRoomOptions() {
    roomList.innerHTML = "";
    const opts = currentPackage.roomOptions || [];
    if (opts.length === 0) {
      roomBlock.classList.add("hidden");
      return;
    }
    roomBlock.classList.remove("hidden");
    opts.forEach((r, i) => {
      const label = document.createElement("label");
      label.className = "flex items-center gap-2 text-sm";
      label.innerHTML =
        '<input type="radio" name="room-option" value="' + r.id + '" ' + (i === 0 ? "checked" : "") + '> ' +
        r.label + (r.priceAdjustmentClp ? " (+" + formatPrice(r.priceAdjustmentClp, currency) + ")" : "");
      roomList.appendChild(label);
    });
    roomList.querySelectorAll('input[name="room-option"]').forEach((el) => el.addEventListener("change", renderSummary));
  }

  function renderAddons() {
    addonsList.innerHTML = "";
    const opts = currentPackage.addons || [];
    if (opts.length === 0) {
      addonsBlock.classList.add("hidden");
      return;
    }
    addonsBlock.classList.remove("hidden");
    opts.forEach((a) => {
      const label = document.createElement("label");
      label.className = "flex items-center gap-2 text-sm";
      label.innerHTML =
        '<input type="checkbox" name="addon" value="' + a.id + '"> ' + a.name + " (+" + formatPrice(a.priceClp, currency) + ")";
      addonsList.appendChild(label);
    });
    addonsList.querySelectorAll('input[name="addon"]').forEach((el) => el.addEventListener("change", renderSummary));
  }

  function getSelectedRoomOptionId() {
    const el = roomList.querySelector('input[name="room-option"]:checked');
    return el ? el.value : null;
  }

  function getSelectedAddonIds() {
    return Array.from(addonsList.querySelectorAll('input[name="addon"]:checked')).map((el) => el.value);
  }

  function renderSummary() {
    if (!currentPackage) {
      summaryContent.innerHTML = '<p class="text-sm text-foreground/60">Elige un paquete para ver el resumen.</p>';
      submitBtn.disabled = true;
      return;
    }
    const quote = calculateQuote({
      basePriceClp: currentPackage.priceFromClp,
      priceUnit: currentPackage.priceUnit,
      adults: adultsInput.value,
      children: childrenInput.value,
      selectedAddonIds: getSelectedAddonIds(),
      addons: currentPackage.addons || [],
      roomOptionId: getSelectedRoomOptionId(),
      roomOptions: currentPackage.roomOptions || [],
    });

    let html = '<p class="font-semibold text-brand-dark">' + currentPackage.title + "</p>";
    html += '<dl class="mt-4 space-y-2 text-sm">';
    html += row("Pasajeros", quote.passengers + "");
    if (quote.passengersSubtotal !== null) {
      html += row("Subtotal pasajeros", formatPrice(quote.passengersSubtotal, currency));
    }
    if (quote.selectedRoomLabel) {
      html += row(quote.selectedRoomLabel, formatPrice(quote.roomAdjustment, currency));
    }
    for (const a of quote.selectedAddons) {
      html += row(a.name, formatPrice(a.priceClp, currency));
    }
    html += "</dl>";
    if (quote.total !== null) {
      html += '<div class="mt-4 border-t border-black/10 pt-3">';
      html += row("Total estimado", formatPrice(quote.total, currency), true);
      html += row("Abono sugerido (30%)", formatPrice(quote.depositSuggested, currency));
      html += "</div>";
    } else {
      html += '<p class="mt-4 text-xs text-foreground/60">Este paquete no tiene precio publicado — te contactaremos con una cotización a medida.</p>';
    }
    summaryContent.innerHTML = html;
    submitBtn.disabled = false;
  }

  function row(label, value, strong) {
    return (
      '<div class="flex items-center justify-between' + (strong ? " font-semibold text-brand-dark" : " text-foreground/70") + '">' +
      "<span>" + label + "</span><span>" + value + "</span></div>"
    );
  }

  topSelect.addEventListener("change", () => {
    const [kind, value] = topSelect.value.split(":");
    if (kind === "family") {
      const family = options.find((o) => o.kind === "family" && o.id === value);
      standaloneImageBlock.classList.add("hidden");
      currentPackage = null;
      renderSummary();
      if (family && family.members.length > 0) {
        renderFamilyMembers(family, null);
      }
    } else {
      familyMembersBlock.classList.add("hidden");
      const opt = options.find((o) => o.kind === "package" && o.slug === value);
      if (opt) {
        standaloneImageImg.src = opt.imageUrl || "";
        standaloneImageImg.alt = opt.title;
        standaloneImageBlock.classList.remove("hidden");
      }
      loadPackage(value);
    }
  });

  adultsInput.addEventListener("input", renderSummary);
  childrenInput.addEventListener("input", renderSummary);

  currencyToggle.querySelectorAll(".currency-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      currency = btn.dataset.currency;
      currencyToggle.querySelectorAll(".currency-btn").forEach((b) => {
        b.classList.toggle("bg-brand", b === btn);
        b.classList.toggle("text-white", b === btn);
        b.classList.toggle("text-brand-dark", b !== btn);
      });
      if (currentPackage) {
        renderRoomOptions();
        renderAddons();
        renderSummary();
      }
    });
  });

  // Preselección desde ?paquete=slug en la URL.
  if (initialPackage) {
    const family = findFamilyContaining(initialPackage.slug);
    if (family) {
      topSelect.value = "family:" + family.id;
      renderFamilyMembers(family, initialPackage.slug);
    } else {
      topSelect.value = "pkg:" + initialPackage.slug;
      standaloneImageImg.src = (initialPackage.heroGallery && initialPackage.heroGallery[0]) || "";
      standaloneImageImg.alt = initialPackage.title;
      standaloneImageBlock.classList.remove("hidden");
    }
    currentPackage = initialPackage;
    renderRoomOptions();
    renderAddons();
    renderSummary();
  } else {
    renderSummary();
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    submitError.classList.add("hidden");
    if (!currentPackage) return;
    submitBtn.disabled = true;
    submitBtn.textContent = "Enviando…";

    const payload = {
      packageSlug: currentPackage.slug,
      adults: parseInt(adultsInput.value, 10) || 0,
      children: parseInt(childrenInput.value, 10) || 0,
      dateFrom: document.getElementById("date-from").value || null,
      dateTo: document.getElementById("date-to").value || null,
      roomOptionId: getSelectedRoomOptionId(),
      selectedAddonIds: getSelectedAddonIds(),
      passengerName: document.getElementById("passenger-name").value,
      passengerEmail: document.getElementById("passenger-email").value,
      passengerPhone: document.getElementById("passenger-phone").value,
      comments: document.getElementById("comments").value,
    };

    try {
      const res = await fetch("/api-quote-submit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!data.ok) {
        submitError.textContent = data.error || "No pudimos enviar tu cotización. Intenta de nuevo.";
        submitError.classList.remove("hidden");
        submitBtn.disabled = false;
        submitBtn.textContent = "Enviar cotización";
        return;
      }
      window.location.href = "/cotizar/gracias";
    } catch (err) {
      submitError.textContent = "No pudimos enviar tu cotización. Revisa tu conexión e intenta de nuevo.";
      submitError.classList.remove("hidden");
      submitBtn.disabled = false;
      submitBtn.textContent = "Enviar cotización";
    }
  });
})();
