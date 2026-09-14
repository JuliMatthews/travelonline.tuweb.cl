<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/package_card.php';
require_once __DIR__ . '/inc/regions.php';

$regionSlug = $_GET['region'] ?? '';
$region = get_region_with_packages($regionSlug);

if (!$region) {
    http_response_code(404);
    $pageTitle = 'Región no encontrada — Travel Online';
    require __DIR__ . '/inc/head.php';
    require __DIR__ . '/inc/header.php';
    echo '<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6"><p class="text-foreground/60">Región no encontrada.</p></div>';
    require __DIR__ . '/inc/footer.php';
    exit;
}

$pageTitle = $region['name'] . ' — Travel Online';
$activeNav = 'destinos';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
  <h1 class="font-display text-3xl font-bold text-brand-dark"><?= htmlspecialchars($region['name']) ?></h1>

  <?php if (count($region['packages']) === 0): ?>
    <p class="mt-10 text-foreground/60">Todavía no hay paquetes cargados para esta región.</p>
  <?php else: ?>
    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($region['packages'] as $pkg): render_package_card($pkg); endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
