<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/package_families.php';

$packages = get_all_packages();
$options = build_package_selector_options($packages);

$initialSlug = $_GET['paquete'] ?? null;
$initialPackage = $initialSlug ? get_package_by_slug($initialSlug) : null;

$pageTitle = 'Cotiza tu viaje — Travel Online';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
  <h1 class="font-display text-3xl font-bold text-brand-dark">Cotiza tu viaje</h1>
  <p class="mt-3 max-w-2xl text-foreground/70">
    Arma tu cotización — el total se actualiza al instante mientras cambias pasajeros, habitación o excursiones. Sin costo ni compromiso.
  </p>

  <form id="quote-form" class="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-[1.3fr_1fr]">
    <div class="space-y-6">
      <div>
        <label class="block text-sm font-semibold text-brand-dark">Paquete</label>
        <select id="top-select" required class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          <option value="" disabled selected>Selecciona un paquete</option>
        </select>
        <div id="family-members" class="mt-3 hidden">
          <p class="text-xs text-foreground/60">Elige el destino:</p>
          <div id="family-members-grid" class="mt-2 grid grid-cols-3 gap-3"></div>
        </div>
        <div id="standalone-image" class="mt-3 hidden">
          <span class="relative block aspect-square w-28 overflow-hidden rounded-lg ring-2 ring-brand">
            <img id="standalone-image-img" src="" alt="" class="h-full w-full object-cover">
          </span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-brand-dark">Adultos</label>
          <input type="number" id="adults" min="1" value="2" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-brand-dark">Niños</label>
          <input type="number" id="children" min="0" value="0" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-brand-dark">Fecha preferida de viaje</label>
        <div class="mt-1 grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-foreground/60">Desde</label>
            <input type="date" id="date-from" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          </div>
          <div>
            <label class="block text-xs text-foreground/60">Hasta</label>
            <input type="date" id="date-to" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          </div>
        </div>
        <p class="mt-1 text-xs text-foreground/50">Aproximada — es solo referencial para armar la cotización.</p>
      </div>

      <div id="room-options-block" class="hidden">
        <label class="block text-sm font-semibold text-brand-dark">Tipo de habitación</label>
        <div id="room-options-list" class="mt-2 space-y-2"></div>
      </div>

      <div id="addons-block" class="hidden">
        <label class="block text-sm font-semibold text-brand-dark">Excursiones opcionales</label>
        <div id="addons-list" class="mt-2 space-y-2"></div>
      </div>

      <div class="border-t border-black/5 pt-6">
        <button type="button" disabled title="Próximamente" class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-brand bg-brand-light/60 px-3 py-2.5 text-sm font-semibold text-brand-dark disabled:cursor-not-allowed">
          <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.6 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.5 5.3 29.5 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.3-.4-3.5z"/>
            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.3 18.9 12 24 12c3.1 0 5.8 1.1 8 3l6-6C34.5 5.3 29.5 3 24 3 15.9 3 8.9 7.7 6.3 14.7z"/>
            <path fill="#4CAF50" d="M24 45c5.4 0 10.3-2.1 14-5.5l-6.5-5.5C29.4 35.8 26.9 36.7 24 36.7c-5.3 0-9.7-3.4-11.3-8.1l-6.6 5.1C8.9 40.4 15.9 45 24 45z"/>
            <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.9 2.7-2.6 5-4.8 6.5l6.5 5.5C40.8 36.9 44 31 44 24c0-1.2-.1-2.3-.4-3.5z"/>
          </svg>
          Cotiza con tu cuenta Google (más rápido)
        </button>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-semibold text-brand-dark">Nombre</label>
          <input required type="text" id="passenger-name" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-brand-dark">Correo</label>
          <input required type="email" id="passenger-email" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-brand-dark">Teléfono</label>
          <input required type="tel" id="passenger-phone" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-brand-dark">Comentarios (opcional)</label>
          <input type="text" id="comments" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        </div>
      </div>

      <p id="submit-error" class="hidden text-sm text-red-600"></p>

      <button type="submit" id="submit-btn" class="rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark disabled:opacity-50">
        Enviar cotización
      </button>
    </div>

    <aside class="h-fit rounded-2xl border border-black/5 bg-brand-light/40 p-6">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="font-display font-bold text-brand-dark">Resumen</h2>
        <div class="flex rounded-full border border-black/10 text-xs font-semibold" id="currency-toggle">
          <?php foreach (['CLP', 'USD', 'EUR'] as $c): ?>
            <button type="button" data-currency="<?= $c ?>" class="currency-btn px-3 py-1 first:rounded-l-full last:rounded-r-full <?= $c === 'CLP' ? 'bg-brand text-white' : 'text-brand-dark' ?>"><?= $c ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div id="summary-content">
        <p class="text-sm text-foreground/60">Elige un paquete para ver el resumen.</p>
      </div>
    </aside>
  </form>
</div>

<script id="cotizar-data" type="application/json"><?= json_encode(['options' => $options, 'initialPackage' => $initialPackage], JSON_UNESCAPED_UNICODE) ?></script>
<script src="/js/pricing.js"></script>
<script src="/js/cotizar.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
