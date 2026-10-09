<?php
// Menú del rediseño "Marino Sereno" (08-10-2026). Cada página define
// $activeNav antes de incluir este archivo (nosotros, destinos, todo-incluido,
// promociones, blog, contacto, viajes-medida, area-clientes) para marcar su item.
require_once __DIR__ . '/regions.php';
require_once __DIR__ . '/i18n.php';
$activeNav = $activeNav ?? '';
$loc = current_locale();
$u = fn(string $path = '') => htmlspecialchars(locale_url($loc, $path));

$navItems = [
    'promociones' => ['href' => '/promociones', 'label' => t('nav.paquetes')],
    'viajes-medida' => ['href' => '/viajes-a-medida/', 'label' => t('nav.viajes_medida')],
    'destinos' => ['href' => '/destinos', 'label' => t('nav.destinos')],
    'nosotros' => ['href' => '/nosotros', 'label' => t('nav.nosotros')],
    'contacto' => ['href' => '/contacto', 'label' => t('nav.contacto')],
];
// En el menú de celular también van Todo Incluido y Blog (en escritorio no caben).
$mobileItems = $navItems + [
    'todo-incluido' => ['href' => '/programas/todo-incluido', 'label' => t('nav.todo_incluido')],
    'blog' => ['href' => '/blog', 'label' => t('nav.blog')],
];
$locales = ['es' => 'ES', 'en' => 'EN', 'pt' => 'PT'];
$cleanPath = current_clean_path();
$iconUser = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>';
$iconPlane = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>';
?>
<header class="ms-nav">
  <div class="ms-wrap ms-nav-in">
    <a class="ms-logo" href="<?= $u() ?>"><i></i>Travel Online</a>

    <nav class="ms-links" aria-label="Menú principal">
      <?php foreach ($navItems as $key => $item): ?>
        <?php if ($key === 'destinos'): ?>
          <div class="ms-drop">
            <a href="<?= $u($item['href']) ?>" class="<?= $key === $activeNav ? 'on' : '' ?>"><?= htmlspecialchars($item['label']) ?></a>
            <div class="ms-drop-menu"><div>
              <?php foreach (REGION_SLUGS as $slug): ?>
                <a href="<?= $u('/destinos/' . $slug) ?>"><?= htmlspecialchars(region_name($slug)) ?></a>
              <?php endforeach; ?>
            </div></div>
          </div>
        <?php else: ?>
          <a href="<?= $u($item['href']) ?>" class="<?= $key === $activeNav ? 'on' : '' ?>"><?= htmlspecialchars($item['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

    <div class="ms-nav-r">
      <div class="ms-langs">
        <?php foreach ($locales as $l => $label): ?>
          <a href="<?= htmlspecialchars(locale_url($l, $cleanPath)) ?>" class="<?= $l === $loc ? 'on' : '' ?>" hreflang="<?= $l ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>
      <button type="button" id="theme-toggle" class="ms-icon-btn" aria-label="<?= htmlspecialchars(t('header.theme_toggle_aria')) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden dark:block" aria-hidden="true">
          <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="block dark:hidden" aria-hidden="true">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
        </svg>
      </button>
      <a class="ms-btn ms-btn-area" href="<?= $u('/area-clientes') ?>" aria-label="<?= htmlspecialchars(t('header.area_clientes')) ?>"><?= $iconUser ?><span><?= htmlspecialchars(t('header.area_clientes')) ?></span></a>
      <a class="ms-btn ms-btn-quote" href="<?= $u('/viajes-a-medida/') ?>" aria-label="<?= htmlspecialchars(t('header.cotizar')) ?>"><?= $iconPlane ?><span><?= htmlspecialchars(t('header.cotizar')) ?></span></a>
      <button type="button" class="ms-icon-btn ms-menu-btn" id="ms-menu-btn" aria-label="<?= htmlspecialchars(t('header.menu_open')) ?>" aria-expanded="false" aria-controls="ms-mnav"
        data-open-label="<?= htmlspecialchars(t('header.menu_open')) ?>" data-close-label="<?= htmlspecialchars(t('header.menu_close')) ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>

  <nav class="ms-mnav" id="ms-mnav" hidden aria-label="Menú principal">
    <div class="ms-wrap ms-mnav-in">
      <?php foreach ($mobileItems as $key => $item): ?>
        <a href="<?= $u($item['href']) ?>" class="<?= $key === $activeNav ? 'on' : '' ?>"><?= htmlspecialchars($item['label']) ?></a>
      <?php endforeach; ?>
      <div class="ms-mnav-row">
        <span class="ms-muted"><?= htmlspecialchars(t('header.language')) ?></span>
        <div class="ms-langs" style="display:flex">
          <?php foreach ($locales as $l => $label): ?>
            <a href="<?= htmlspecialchars(locale_url($l, $cleanPath)) ?>" class="<?= $l === $loc ? 'on' : '' ?>" hreflang="<?= $l ?>"><?= $label ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="ms-mnav-btns">
        <a class="ms-btn ms-btn-area" href="<?= $u('/area-clientes') ?>"><?= $iconUser ?> <?= htmlspecialchars(t('header.area_clientes')) ?></a>
        <a class="ms-btn ms-btn-quote" href="<?= $u('/viajes-a-medida/') ?>"><?= $iconPlane ?> <?= htmlspecialchars(t('header.cotizar')) ?></a>
      </div>
    </div>
  </nav>
</header>
<main class="flex-1">
