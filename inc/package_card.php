<?php
const PACKAGE_TYPE_LABELS = [
    'circuito' => 'Circuito',
    'todo_incluido' => 'Todo Incluido',
    'combinado' => 'Combinado',
    'promocion_2x1' => 'Promoción 2x1',
];

function render_package_card(array $pkg): void {
    $cover = $pkg['heroGallery'][0] ?? null;
    $typeLabel = PACKAGE_TYPE_LABELS[$pkg['packageType']] ?? $pkg['packageType'];
    ?>
    <a href="/paquetes/<?= htmlspecialchars($pkg['slug']) ?>" class="shine-card group block overflow-hidden rounded-xl border border-border bg-background">
      <div class="relative aspect-[4/3] overflow-hidden bg-linear-to-br from-brand-dark to-brand">
        <?php if ($cover): ?>
          <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($pkg['title']) ?>" loading="lazy"
               class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        <?php else: ?>
          <svg viewBox="0 0 24 24" aria-hidden="true" class="absolute left-1/2 top-1/2 h-16 w-16 -translate-x-1/2 -translate-y-1/2 fill-white/15">
            <path d="M2.5 19.5 5 12l6-1.5V4a1.5 1.5 0 0 1 3 0v6.5L20 12l2.5 7.5-8.5-2.5-3.5 2-3.5-2Z" />
          </svg>
        <?php endif; ?>
        <?php if ($pkg['packageType']): ?>
          <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-brand-dark">
            <?= htmlspecialchars($typeLabel) ?>
          </span>
        <?php endif; ?>
      </div>
      <div class="p-4">
        <h3 class="font-display font-semibold text-brand-dark"><?= htmlspecialchars($pkg['title']) ?></h3>
        <?php if (!empty($pkg['subtitle'])): ?>
          <p class="mt-1 text-sm text-foreground/70"><?= htmlspecialchars($pkg['subtitle']) ?></p>
        <?php endif; ?>
      </div>
    </a>
    <?php
}
