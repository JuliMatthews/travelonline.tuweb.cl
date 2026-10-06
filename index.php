<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/regions.php';
require_once __DIR__ . '/inc/package_card.php';

$regions = get_regions_overview();
$featured = get_featured_packages();

$pageTitle = t('home.title');
$activeNav = '';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<section class="relative overflow-hidden bg-brand-dark px-4 py-20 text-center sm:py-28">
  <video class="hero-video absolute inset-0 h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
    <source src="/video/hero-pexels-beach.mp4" type="video/mp4">
  </video>
  <div aria-hidden class="absolute inset-0 bg-linear-to-b from-brand-dark/55 via-brand-dark/25 to-brand/35"></div>
  <div aria-hidden class="pointer-events-none absolute -top-32 right-[-10%] h-96 w-96 rounded-full opacity-30 blur-3xl" style="background: radial-gradient(circle, var(--accent), transparent 70%)"></div>
  <div class="relative mx-auto max-w-3xl">
    <h1 class="font-display text-4xl font-bold tracking-tight text-white sm:text-6xl"><?= htmlspecialchars(t('home.hero_title')) ?></h1>
    <p class="mx-auto mt-5 max-w-2xl text-lg text-white/80">
      <?= htmlspecialchars(t('home.hero_subtitle')) ?>
    </p>
    <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
      <a href="<?= htmlspecialchars(locale_url(current_locale(), '/cotizar')) ?>" class="rounded-full bg-accent px-7 py-3 font-semibold text-accent-ink transition hover:brightness-105"><?= htmlspecialchars(t('home.cta_quote')) ?></a>
      <a href="<?= htmlspecialchars(locale_url(current_locale(), '/destinos')) ?>" class="rounded-full border border-white/40 px-7 py-3 font-semibold text-white transition hover:bg-white/10"><?= htmlspecialchars(t('home.cta_destinations')) ?></a>
    </div>
  </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
  <div class="mb-8 flex items-end justify-between gap-4">
    <h2 class="font-display text-2xl font-bold text-brand-dark sm:text-3xl"><?= htmlspecialchars(t('home.explore_region')) ?></h2>
    <a href="<?= htmlspecialchars(locale_url(current_locale(), '/destinos')) ?>" class="text-sm font-semibold text-brand hover:text-brand-dark"><?= htmlspecialchars(t('home.view_all')) ?> →</a>
  </div>

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
    <?php foreach ($regions as $region): ?>
      <a href="<?= htmlspecialchars(locale_url(current_locale(), '/destinos/' . $region['slug'])) ?>" class="shine-card group relative flex aspect-4/5 flex-col justify-between overflow-hidden rounded-2xl p-5 text-white sm:aspect-square">
        <img src="<?= htmlspecialchars(REGION_IMAGES[$region['slug']]) ?>" alt="" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        <div class="absolute inset-0 bg-linear-to-t from-brand-dark/90 via-brand-dark/25 to-brand-dark/10"></div>
        <span class="relative font-display text-lg font-bold leading-tight drop-shadow-sm"><?= htmlspecialchars(region_name($region['slug'])) ?></span>
        <span class="relative mt-6 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
          <?= $region['count'] ?> <?= htmlspecialchars(t($region['count'] === 1 ? 'home.package_singular' : 'home.package_plural')) ?>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if (count($featured) > 0): ?>
<section class="border-t border-border bg-brand-light/40 px-4 py-16 sm:px-6 sm:py-20">
  <div class="mx-auto max-w-6xl">
    <div class="mb-8 flex items-end justify-between gap-4">
      <h2 class="font-display text-2xl font-bold text-brand-dark sm:text-3xl"><?= htmlspecialchars(t('home.featured_title')) ?></h2>
    </div>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($featured as $pkg): render_package_card($pkg); endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/inc/footer.php'; ?>
