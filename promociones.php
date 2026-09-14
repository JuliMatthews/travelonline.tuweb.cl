<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/package_card.php';

$packages = get_all_packages();
$promociones = array_values(array_filter($packages, fn($p) => $p['showInPromociones']));

$pageTitle = 'Promociones — Travel Online';
$activeNav = 'promociones';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
  <h1 class="font-display text-3xl font-bold text-brand-dark">Promociones</h1>
  <p class="mt-3 max-w-2xl text-foreground/70">
    Nuestro catálogo completo de programas — <?= count($promociones) ?> destinos disponibles, incluyendo ofertas 2x1 por tiempo limitado.
  </p>

  <?php if (count($promociones) === 0): ?>
    <p class="mt-10 text-foreground/60">No hay promociones activas por el momento.</p>
  <?php else: ?>
    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($promociones as $pkg): render_package_card($pkg); endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
