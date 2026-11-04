<?php
// sitemap.xml dinámico: portada, destinos, programas publicados, blog y páginas fijas,
// con las variantes en español, inglés (/en) y portugués (/pt).
require_once __DIR__ . '/inc/content.php';

const SITEMAP_BASE = 'https://travelonline.cl';

$paths = ['/', '/destinos', '/promociones', '/programas/todo-incluido', '/viajes-a-medida/', '/cotizar', '/blog', '/nosotros', '/contacto',
    '/legal/condiciones-de-reserva', '/legal/politica-de-cookies', '/legal/politicas-de-privacidad'];
foreach (get_regions_overview() as $r) { $paths[] = '/destinos/' . $r['slug']; }
foreach (get_all_packages() as $p)     { $paths[] = '/paquetes/' . $p['slug']; }
foreach (get_all_blog_posts() as $b)   { $paths[] = '/blog/' . $b['slug']; }

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach (array_unique($paths) as $path) {
    $variants = ['es' => $path, 'en' => '/en' . ($path === '/' ? '' : $path), 'pt' => '/pt' . ($path === '/' ? '' : $path)];
    foreach ($variants as $loc => $v) {
        echo "  <url>\n    <loc>" . htmlspecialchars(SITEMAP_BASE . $v) . "</loc>\n";
        foreach ($variants as $hl => $hv) {
            echo '    <xhtml:link rel="alternate" hreflang="' . $hl . '" href="' . htmlspecialchars(SITEMAP_BASE . $hv) . '"/>' . "\n";
        }
        echo "  </url>\n";
    }
}
echo "</urlset>\n";
