<?php
// Portada — rediseño "Marino Sereno" (08-10-2026), estructura de la maqueta de
// Francisco con datos reales: paquetes destacados y regiones desde la BD.
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/regions.php';
require_once __DIR__ . '/inc/package_card.php';

$regions = get_regions_overview();
$featured = get_featured_packages();
$totalPackages = array_sum(array_map(fn($r) => (int) $r['count'], $regions));

$pageTitle = t('home.title');
$activeNav = '';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';

$loc = current_locale();
$u = fn(string $path = '') => htmlspecialchars(locale_url($loc, $path));
$e = fn(string $key) => htmlspecialchars(t($key));
$ico = [
    'shield' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    'chat' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'card' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>',
    'globe' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>',
];
?>

<section class="ms-hero">
  <div class="ms-wrap ms-hero-grid">
    <div>
      <div class="ms-eyebrow"><?= $e('home.eyebrow') ?></div>
      <h1><?= $e('home.hero_title_1') ?> <em><?= $e('home.hero_title_em') ?></em></h1>
      <p class="lead"><?= $e('home.hero_lead') ?></p>

      <!-- Cotizador conversacional: lleva a Viajes a medida con estos datos ya cargados -->
      <form class="ms-quoter" method="get" action="<?= $u('/viajes-a-medida/') ?>">
        <label class="ms-q-title" for="q-dest"><?= $e('home.q_title') ?></label>
        <div class="ms-q-row">
          <input class="ms-q-field dest" id="q-dest" name="destino" placeholder="<?= $e('home.q_dest_ph') ?>" maxlength="120" required>
          <select class="ms-q-field when" name="cuando" aria-label="<?= $e('vam.when') ?>">
            <option value="1"><?= $e('vam.when_1') ?></option>
            <option value="2"><?= $e('vam.when_2') ?></option>
            <option value="3"><?= $e('vam.when_3') ?></option>
            <option value="4"><?= $e('vam.when_4') ?></option>
          </select>
          <span class="ms-q-join"><?= $e('home.q_people') ?></span>
          <input class="ms-q-field n" name="personas" value="2" inputmode="numeric" pattern="[0-9]*" maxlength="2" aria-label="<?= $e('home.q_people') ?>">
        </div>
        <div class="ms-q-foot">
          <small><?= $e('home.q_note') ?></small>
          <button class="ms-btn ms-btn-p" type="submit"><?= $e('home.q_cta') ?></button>
        </div>
      </form>
    </div>

    <div style="position:relative">
      <div class="ms-photo ms-hero-photo">
        <img src="/img/hero-maldivas.jpg" alt="<?= $e('home.photo_alt') ?>" fetchpriority="high">
      </div>
    </div>
  </div>
</section>

<div class="ms-trust">
  <div class="ms-wrap ms-trust-in">
    <div><span class="ms-dot"><?= $ico['shield'] ?></span><span><b><?= $e('home.trust_1_t') ?></b><span class="ms-muted"><?= $e('home.trust_1_d') ?></span></span></div>
    <div><span class="ms-dot"><?= $ico['chat'] ?></span><span><b><?= $e('home.trust_2_t') ?></b><span class="ms-muted"><?= $e('home.trust_2_d') ?></span></span></div>
    <div><span class="ms-dot"><?= $ico['card'] ?></span><span><b><?= $e('home.trust_3_t') ?></b><span class="ms-muted"><?= $e('home.trust_3_d') ?></span></span></div>
    <div><span class="ms-dot"><?= $ico['globe'] ?></span><span><b><?= $e('home.trust_4_t') ?></b><span class="ms-muted"><?= $e('home.trust_4_d') ?></span></span></div>
  </div>
</div>

<?php if (count($featured) > 0): ?>
<section class="ms-sec">
  <div class="ms-wrap">
    <div class="ms-sec-head">
      <div>
        <div class="ms-eyebrow"><?= $e('home.offers_eyebrow') ?></div>
        <h2><?= $e('home.offers_title') ?></h2>
        <p class="ms-muted"><?= $e('home.offers_sub') ?></p>
      </div>
      <a class="ms-btn ms-btn-s" href="<?= $u('/promociones') ?>"><?= htmlspecialchars(sprintf(t('home.view_all_packages'), $totalPackages)) ?></a>
    </div>
    <div class="ms-offers">
      <?php foreach ($featured as $pkg) render_package_card($pkg); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="ms-sec tight">
  <div class="ms-wrap">
    <div class="ms-sec-head"><div><div class="ms-eyebrow"><?= $e('home.steps_eyebrow') ?></div><h2><?= $e('home.steps_title') ?></h2></div></div>
    <div class="ms-steps">
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <div class="ms-step"><span class="ms-step-n">0<?= $i ?></span><h3><?= $e("home.step{$i}_t") ?></h3><p class="ms-muted"><?= $e("home.step{$i}_d") ?></p></div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="ms-sec tight">
  <div class="ms-wrap">
    <div class="ms-band">
      <div>
        <div class="ms-eyebrow"><?= $e('home.band_eyebrow') ?></div>
        <h2><?= $e('home.band_title') ?></h2>
        <p><?= $e('home.band_text') ?></p>
        <p style="margin-top:20px"><a class="ms-btn ms-btn-p" href="<?= $u('/viajes-a-medida/') ?>"><?= $e('home.band_cta') ?></a></p>
      </div>
      <div class="ms-chips">
        <?php foreach ([2, 3, 4, 5, 6, 7] as $n): ?>
          <a href="<?= $u('/viajes-a-medida/?tipo=' . $n) ?>"><?= $e("vam.type_$n") ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="ms-sec tight">
  <div class="ms-wrap">
    <div class="ms-sec-head">
      <div><div class="ms-eyebrow"><?= $e('home.dest_eyebrow') ?></div><h2><?= $e('home.dest_title') ?></h2></div>
      <a class="ms-btn ms-btn-s" href="<?= $u('/destinos') ?>"><?= $e('home.dest_all') ?></a>
    </div>
    <div class="ms-dests">
      <?php foreach ($regions as $region): if ((int) $region['count'] === 0) continue; ?>
        <a class="ms-dest" href="<?= $u('/destinos/' . $region['slug']) ?>">
          <div class="ms-photo"><img src="<?= htmlspecialchars(REGION_IMAGES[$region['slug']] ?? '') ?>" alt="" loading="lazy"></div>
          <div class="ms-dest-l"><b><?= htmlspecialchars(region_name($region['slug'])) ?></b><span><?= (int) $region['count'] ?> <?= $e((int) $region['count'] === 1 ? 'home.package_singular' : 'home.package_plural') ?></span></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="ms-sec tight">
  <div class="ms-wrap ms-faq">
    <div>
      <div class="ms-eyebrow"><?= $e('home.faq_eyebrow') ?></div>
      <h2 class="ms-h2" style="margin-top:8px"><?= $e('home.faq_title') ?></h2>
      <p class="ms-muted" style="margin-top:10px"><?= $e('home.faq_text') ?></p>
    </div>
    <div>
      <?php for ($i = 1; $i <= 4; $i++): ?>
        <details<?= $i === 1 ? ' open' : '' ?>><summary><?= $e("home.faq_q$i") ?></summary><p><?= $e("home.faq_a$i") ?></p></details>
      <?php endfor; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
