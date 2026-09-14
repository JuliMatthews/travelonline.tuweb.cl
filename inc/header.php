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
<header class="sticky top-0 z-40 border-b border-black/5 bg-background/90 backdrop-blur">
  <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
    <a href="/" class="font-display rounded-full bg-[#8D7676] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#7a6363]">
      Travel Online
    </a>

    <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
      <?php foreach ($navItems as $key => $item): if ($key === $activeNav) continue; ?>
        <?php if ($key === 'destinos'): ?>
          <div class="group relative">
            <a href="<?= htmlspecialchars($item['href']) ?>"><?= htmlspecialchars($item['label']) ?></a>
            <div class="invisible absolute left-0 top-full grid w-56 grid-cols-1 gap-1 rounded-lg border border-black/5 bg-background p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
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

    <a href="/cotizar" class="rounded-full bg-[#25D366] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1ebe57]">
      Cotizar viaje
    </a>
  </div>
</header>
<main class="flex-1">
