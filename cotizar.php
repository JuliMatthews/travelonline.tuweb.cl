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
        <p class="mb-2 text-xs text-foreground/60">Inicia sesión con Google para autocompletar tu nombre y correo (opcional).</p>
        <div id="google-signin-button" data-client-id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"></div>
        <div id="google-signin-done" class="hidden items-center gap-2 rounded-lg border-2 border-brand bg-brand-light/60 px-3 py-2.5 text-sm font-semibold text-brand-dark">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span id="google-signin-name"></span>
          <button type="button" id="google-signin-reset" class="ml-auto text-xs font-normal underline">Usar otro correo</button>
        </div>
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
<script src="https://accounts.google.com/gsi/client" defer></script>
<script src="/js/google-signin.js" defer></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
