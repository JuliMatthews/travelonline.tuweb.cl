<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/package_card.php';

$packages = get_all_packages();
$todoIncluido = array_values(array_filter($packages, fn($p) => $p['packageType'] === 'todo_incluido'));

$pageTitle = 'Programas Todo Incluido — Travel Online';
$activeNav = 'todo-incluido';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
  <h1 class="font-display text-3xl font-bold text-brand-dark">Programas Todo Incluido</h1>
  <p class="mt-3 max-w-2xl text-foreground/70">
    Vuelos, alojamiento y traslados en un solo precio — para viajar sin preocuparte de organizar cada detalle.
  </p>

  <?php if (count($todoIncluido) === 0): ?>
    <p class="mt-10 text-foreground/60">Todavía no hay programas todo incluido cargados.</p>
  <?php else: ?>
    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($todoIncluido as $pkg): render_package_card($pkg); endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
