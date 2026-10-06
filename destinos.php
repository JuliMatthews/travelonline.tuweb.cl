<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/regions.php';

$regions = get_regions_overview();

$pageTitle = t('destinos.title') . ' — Travel Online';
$activeNav = 'destinos';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
  <h1 class="font-display text-3xl font-bold text-brand-dark"><?= htmlspecialchars(t('destinos.title')) ?></h1>
  <p class="mt-3 max-w-2xl text-foreground/70">
    <?= htmlspecialchars(t('destinos.subtitle')) ?>
  </p>

  <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($regions as $region): ?>
      <a href="<?= htmlspecialchars(locale_url(current_locale(), '/destinos/' . $region['slug'])) ?>" class="shine-card group relative flex aspect-4/3 flex-col justify-between overflow-hidden rounded-2xl p-6 text-white">
        <img src="<?= htmlspecialchars(REGION_IMAGES[$region['slug']]) ?>" alt="" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        <div class="absolute inset-0 bg-linear-to-t from-brand-dark/90 via-brand-dark/25 to-brand-dark/10"></div>
        <span class="relative font-display text-2xl font-bold leading-tight drop-shadow-sm"><?= htmlspecialchars(region_name($region['slug'])) ?></span>
        <span class="relative mt-6 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
          <?= $region['count'] ?> <?= htmlspecialchars(t($region['count'] === 1 ? 'home.package_singular' : 'home.package_plural')) ?>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
