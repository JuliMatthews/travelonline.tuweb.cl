<?php
require_once __DIR__ . '/inc/content.php';

$posts = get_all_blog_posts();

function format_blog_date(string $iso): string {
    $months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $ts = strtotime($iso);
    return date('j', $ts) . ' de ' . $months[(int)date('n', $ts) - 1] . ' de ' . date('Y', $ts);
}

$pageTitle = 'Blog — Travel Online';
$activeNav = 'blog';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
  <h1 class="font-display text-3xl font-bold text-brand-dark">Blog</h1>
  <p class="mt-3 max-w-2xl text-foreground/70">Consejos, guías rápidas e ideas para tu próximo viaje.</p>

  <?php if (count($posts) === 0): ?>
    <p class="mt-10 text-foreground/60">Todavía no hay artículos publicados.</p>
  <?php else: ?>
    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($posts as $post): ?>
        <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="shine-card group block overflow-hidden rounded-xl border border-border bg-background">
          <div class="relative aspect-[4/3] overflow-hidden bg-linear-to-br from-brand-dark to-brand">
            <?php if ($post['featuredImage']): ?>
              <img src="<?= htmlspecialchars($post['featuredImage']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
            <?php else: ?>
              <svg viewBox="0 0 24 24" aria-hidden="true" class="absolute left-1/2 top-1/2 h-16 w-16 -translate-x-1/2 -translate-y-1/2 fill-white/15">
                <path d="M4 4h16v16H4V4Zm2 2v12h12V6H6Zm2 2h8v2H8V8Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z" />
              </svg>
            <?php endif; ?>
          </div>
          <div class="p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand"><?= format_blog_date($post['date']) ?></p>
            <h2 class="font-display mt-1 font-semibold text-brand-dark"><?= htmlspecialchars($post['title']) ?></h2>
            <div class="mt-2 text-sm text-foreground/70 [&_p]:m-0"><?= $post['excerpt'] ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
