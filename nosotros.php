<?php
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/static_page.php';

$page = get_page_by_slug('nosotros');
if (!$page) {
    http_response_code(404);
    $pageTitle = 'Página no encontrada — Travel Online';
    require __DIR__ . '/inc/head.php';
    require __DIR__ . '/inc/header.php';
    echo '<div class="mx-auto max-w-4xl px-4 py-16 sm:px-6"><p class="text-foreground/60">Página no encontrada.</p></div>';
    require __DIR__ . '/inc/footer.php';
    exit;
}

$pageTitle = $page['title'] . ' — Travel Online';
$activeNav = 'nosotros';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
render_static_page($page);
require __DIR__ . '/inc/footer.php';
