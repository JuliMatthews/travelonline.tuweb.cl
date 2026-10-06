<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/currency.php';
require_once __DIR__ . '/inc/package_card.php';

$slug = $_GET['slug'] ?? '';
$pkg = get_package_by_slug($slug);

if (!$pkg) {
    http_response_code(404);
    $pageTitle = 'Paquete no encontrado — Travel Online';
    require __DIR__ . '/inc/head.php';
    require __DIR__ . '/inc/header.php';
    echo '<div class="mx-auto max-w-4xl px-4 py-16 sm:px-6"><p class="text-foreground/60">Paquete no encontrado.</p></div>';
    require __DIR__ . '/inc/footer.php';
    exit;
}

$included = $pkg['included'] ? array_filter(array_map('trim', explode("\n", $pkg['included']))) : [];
$notIncluded = $pkg['notIncluded'] ? array_filter(array_map('trim', explode("\n", $pkg['notIncluded']))) : [];
$hasPrice = $pkg['priceDisplayMode'] === 'desde' && $pkg['priceFromClp'] !== null;

$pageTitle = $pkg['title'] . ' — Travel Online';
$pageDescription = $pkg['subtitle'] ?: $pageDescription ?? null;
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<article class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
  <div class="flex flex-wrap items-center gap-2 text-sm text-foreground/60">
    <?php if ($pkg['region']): ?>
      <a href="/destinos/<?= htmlspecialchars($pkg['region']['slug']) ?>" class="rounded-full bg-brand-light px-3 py-1 text-brand-dark">
        <?= htmlspecialchars($pkg['region']['name']) ?>
      </a>
    <?php endif; ?>
    <?php if ($pkg['packageType']): ?>
      <span class="rounded-full border border-border px-3 py-1"><?= htmlspecialchars(PACKAGE_TYPE_LABELS[$pkg['packageType']] ?? $pkg['packageType']) ?></span>
    <?php endif; ?>
  </div>

  <h1 class="font-display mt-4 text-4xl font-bold text-brand-dark"><?= htmlspecialchars($pkg['title']) ?></h1>
  <?php if ($pkg['subtitle']): ?>
    <p class="mt-2 text-lg text-foreground/70"><?= htmlspecialchars($pkg['subtitle']) ?></p>
  <?php endif; ?>

  <?php if (count($pkg['heroGallery']) > 0): ?>
    <div class="mt-8 grid grid-cols-2 gap-2 sm:grid-cols-3">
      <?php foreach ($pkg['heroGallery'] as $src): ?>
        <div class="relative aspect-square overflow-hidden rounded-lg">
          <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($pkg['title']) ?>" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="mt-8 flex flex-wrap items-center gap-6 rounded-xl bg-brand-light/50 p-6">
    <?php if ($pkg['durationDays'] || $pkg['durationNights']): ?>
      <div>
        <p class="text-sm text-foreground/60">Duración</p>
        <p class="font-semibold text-brand-dark"><?= $pkg['durationDays'] ?? '—' ?> días / <?= $pkg['durationNights'] ?? '—' ?> noches</p>
      </div>
    <?php endif; ?>
    <div class="ml-auto" id="price-block" data-price-clp="<?= (int)($pkg['priceFromClp'] ?? 0) ?>" data-has-price="<?= $hasPrice ? '1' : '0' ?>">
      <div class="flex items-center gap-2">
        <p class="text-sm text-foreground/60">Precio</p>
        <?php if ($hasPrice): ?>
          <div class="flex rounded-full border border-border text-[10px] font-semibold" id="price-currency-toggle">
            <?php foreach (['CLP', 'USD', 'EUR'] as $c): ?>
              <button type="button" data-currency="<?= $c ?>" class="price-currency-btn px-2 py-0.5 first:rounded-l-full last:rounded-r-full <?= $c === 'CLP' ? 'bg-brand text-white' : 'text-brand-dark' ?>"><?= $c ?></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <p class="mt-1 font-display text-2xl font-bold text-brand-dark" id="price-value">
        <?= $hasPrice ? 'Desde ' . htmlspecialchars(format_price($pkg['priceFromClp'], 'CLP')) : 'Bajo consulta' ?>
      </p>
      <a href="/cotizar?paquete=<?= htmlspecialchars($pkg['slug']) ?>" class="mt-3 inline-block rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">
        Cotizar este paquete
      </a>
      <?php if (count($pkg['addons']) > 0 || count($pkg['roomOptions']) > 0): ?>
        <p class="mt-2 text-xs text-foreground/50">Incluye opciones de excursiones/habitación configurables al cotizar.</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if (count($pkg['itinerary']) > 0): ?>
    <section class="mt-10">
      <h2 class="font-display text-2xl font-bold text-brand-dark">Itinerario</h2>
      <ol class="mt-4 space-y-4">
        <?php foreach ($pkg['itinerary'] as $day): ?>
          <li class="rounded-lg border border-border p-4">
            <p class="font-semibold text-brand-dark">Día <?= htmlspecialchars((string)($day['dayNumber'] ?? '')) ?> — <?= htmlspecialchars($day['title']) ?></p>
            <div class="prose prose-neutral prose-sm mt-2 max-w-none"><?= $day['description'] ?></div>
          </li>
        <?php endforeach; ?>
      </ol>
    </section>
  <?php endif; ?>

  <?php if (count($included) > 0 || count($notIncluded) > 0): ?>
    <section class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2">
      <?php if (count($included) > 0): ?>
        <div>
          <h3 class="font-semibold text-brand-dark">Incluye</h3>
          <ul class="mt-2 list-inside list-disc text-foreground/80">
            <?php foreach ($included as $item): ?><li><?= htmlspecialchars($item) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if (count($notIncluded) > 0): ?>
        <div>
          <h3 class="font-semibold text-brand-dark">No incluye</h3>
          <ul class="mt-2 list-inside list-disc text-foreground/80">
            <?php foreach ($notIncluded as $item): ?><li><?= htmlspecialchars($item) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</article>

<script src="/js/price-toggle.js"></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
