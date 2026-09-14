<?php
// Renderiza una página estática (Nosotros, Contacto) — mismo rol que tenía
// StaticPageContent.tsx. El HTML se preserva tal cual (lo escribe el propio
// staff desde el panel, no un usuario público).
function render_static_page(array $page): void {
    ?>
    <article class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
      <h1 class="font-display text-3xl font-bold text-brand-dark"><?= htmlspecialchars($page['title']) ?></h1>

      <?php if (count($page['heroGallery']) > 0): ?>
        <div class="mt-8 grid grid-cols-3 gap-3">
          <?php foreach ($page['heroGallery'] as $src): ?>
            <div class="relative aspect-video overflow-hidden rounded-lg">
              <img src="<?= htmlspecialchars($src) ?>" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="prose prose-neutral mt-8 max-w-none"><?= $page['content'] ?></div>
    </article>
    <?php
}
