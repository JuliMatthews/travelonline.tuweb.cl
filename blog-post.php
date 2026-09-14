<?php
require_once __DIR__ . '/inc/content.php';

function format_blog_date(string $iso): string {
    $months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $ts = strtotime($iso);
    return date('j', $ts) . ' de ' . $months[(int)date('n', $ts) - 1] . ' de ' . date('Y', $ts);
}

$slug = $_GET['slug'] ?? '';
$post = get_blog_post_by_slug($slug);

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Artículo no encontrado — Travel Online';
    require __DIR__ . '/inc/head.php';
    require __DIR__ . '/inc/header.php';
    echo '<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6"><p class="text-foreground/60">Artículo no encontrado.</p></div>';
    require __DIR__ . '/inc/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' — Travel Online';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<article class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
  <p class="text-xs font-semibold uppercase tracking-wide text-brand"><?= format_blog_date($post['date']) ?></p>
  <h1 class="font-display mt-2 text-3xl font-bold text-brand-dark sm:text-4xl"><?= htmlspecialchars($post['title']) ?></h1>

  <?php if ($post['featuredImage']): ?>
    <div class="relative mt-8 aspect-video overflow-hidden rounded-xl">
      <img src="<?= htmlspecialchars($post['featuredImage']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="absolute inset-0 h-full w-full object-cover">
    </div>
  <?php endif; ?>

  <div class="prose prose-neutral mt-8 max-w-none"><?= $post['content'] ?></div>
</article>

<?php require __DIR__ . '/inc/footer.php'; ?>
