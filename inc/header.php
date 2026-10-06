<?php
// Cada página define $activeNav antes de incluir este archivo (uno de:
// nosotros, destinos, todo-incluido, promociones, blog, contacto) para
// ocultar su propio item del menú — mismo comportamiento que el Header.tsx
// original (pedido explícito: no mostrar "Nosotros" estando en /nosotros).
require_once __DIR__ . '/regions.php';
$activeNav = $activeNav ?? '';

$navItems = [
    'nosotros' => ['href' => '/nosotros', 'label' => 'Nosotros'],
    'destinos' => ['href' => '/destinos', 'label' => 'Destinos'],
    'todo-incluido' => ['href' => '/programas/todo-incluido', 'label' => 'Todo Incluido'],
    'promociones' => ['href' => '/promociones', 'label' => 'Promociones'],
    'blog' => ['href' => '/blog', 'label' => 'Blog'],
    'contacto' => ['href' => '/contacto', 'label' => 'Contacto'],
];
?>
<header class="sticky top-0 z-40 border-b border-border bg-background/90 backdrop-blur">
  <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
    <a href="/" class="font-display rounded-full bg-[#8D7676] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#7a6363]">
      Travel Online
    </a>

    <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
      <?php foreach ($navItems as $key => $item): if ($key === $activeNav) continue; ?>
        <?php if ($key === 'destinos'): ?>
          <div class="group relative">
            <a href="<?= htmlspecialchars($item['href']) ?>"><?= htmlspecialchars($item['label']) ?></a>
            <div class="invisible absolute left-0 top-full grid w-56 grid-cols-1 gap-1 rounded-lg border border-border bg-background p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
              <?php foreach (REGION_SLUGS as $slug): ?>
                <a href="/destinos/<?= htmlspecialchars($slug) ?>" class="rounded-md px-3 py-2 hover:bg-brand-light">
                  <?= htmlspecialchars(REGION_NAMES[$slug]) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= htmlspecialchars($item['href']) ?>"><?= htmlspecialchars($item['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

    <div class="flex items-center gap-2">
      <button type="button" id="theme-toggle" aria-label="Cambiar a modo oscuro o claro"
        class="rounded-full border border-border p-2 text-foreground/70 transition hover:bg-brand-light/40">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="block dark:hidden">
          <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden dark:block">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
        </svg>
      </button>
      <a href="/area-clientes" class="rounded-full bg-[#0066FF] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0052cc]">
        Área Clientes
      </a>
      <a href="/cotizar" class="rounded-full bg-[#25D366] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1ebe57]">
        Cotizar viaje
      </a>
    </div>
  </div>
</header>
<main class="flex-1">
