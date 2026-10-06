<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/package_card.php';

$packages = get_all_packages();
$todoIncluido = array_values(array_filter($packages, fn($p) => $p['packageType'] === 'todo_incluido'));

$pageTitle = t('todo_incluido.title') . ' — Travel Online';
$activeNav = 'todo-incluido';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
  <h1 class="font-display text-3xl font-bold text-brand-dark"><?= htmlspecialchars(t('todo_incluido.title')) ?></h1>
  <p class="mt-3 max-w-2xl text-foreground/70">
    <?= htmlspecialchars(t('todo_incluido.subtitle')) ?>
  </p>

  <?php if (count($todoIncluido) === 0): ?>
    <p class="mt-10 text-foreground/60"><?= htmlspecialchars(t('todo_incluido.empty')) ?></p>
  <?php else: ?>
    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($todoIncluido as $pkg): render_package_card($pkg); endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
