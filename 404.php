<?php
// Página 404 propia (ErrorDocument en .htaccess): mantiene el diseño del sitio.
require_once __DIR__ . '/inc/content.php';
http_response_code(404);
$pageTitle = t('page_not_found.title') . ' — Travel Online';
$activeNav = '';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>
<div class="mx-auto max-w-4xl px-4 py-24 text-center sm:px-6">
  <p class="font-display text-6xl font-bold text-brand-dark">404</p>
  <h1 class="mt-4 font-display text-2xl font-bold text-brand-dark"><?= htmlspecialchars(t('page_not_found.title')) ?></h1>
  <p class="mt-3 text-foreground/70">Revisa la dirección o vuelve al inicio para seguir explorando destinos.</p>
  <div class="mt-8 flex flex-wrap justify-center gap-3">
    <a href="<?= htmlspecialchars(locale_url(current_locale(), '/')) ?>" class="rounded-full bg-brand-dark px-5 py-2.5 text-sm font-semibold text-white">Ir al inicio</a>
    <a href="<?= htmlspecialchars(locale_url(current_locale(), '/destinos')) ?>" class="rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-brand-dark">Ver destinos</a>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
