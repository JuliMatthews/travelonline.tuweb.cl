<?php
// Idioma activo del sitio público: español es el canónico (sin prefijo de
// URL), inglés/portugués van con prefijo /en/ y /pt/ (ver .htaccess, que
// pasa el prefijo como env var TO_LANG). ?lang= sirve de respaldo para
// pruebas locales, donde el servidor embebido de PHP no procesa .htaccess.

const SUPPORTED_LOCALES = ['es', 'en', 'pt'];

function current_locale(): string {
    static $locale = null;
    if ($locale !== null) return $locale;

    $candidate = $_GET['lang']
        ?? $_SERVER['REDIRECT_TO_LANG']
        ?? $_SERVER['TO_LANG']
        ?? 'es';

    $locale = in_array($candidate, SUPPORTED_LOCALES, true) ? $candidate : 'es';
    return $locale;
}

// Construye el mismo path actual con otro idioma, para el selector del
// header. $path es el REQUEST_URI ya sin el prefijo de idioma (lo arma cada
// página a partir de la ruta "bonita" conocida, no de REQUEST_URI directo,
// porque este último todavía trae el prefijo viejo).
function locale_url(string $locale, string $path = ''): string {
    $path = '/' . ltrim($path, '/');
    if ($path === '/') $path = '';
    if ($locale === 'es') return $path === '' ? '/' : $path;
    return '/' . $locale . $path;
}

// Path "bonito" actual sin el prefijo de idioma, para armar los links del
// selector de idioma en cualquier página sin tener que pasarlo a mano.
function current_clean_path(): string {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    foreach (['en', 'pt'] as $loc) {
        if ($uri === '/' . $loc || strpos($uri, '/' . $loc . '/') === 0) {
            return substr($uri, strlen('/' . $loc)) ?: '/';
        }
    }
    return $uri;
}

function load_lang(string $locale): array {
    static $cache = [];
    if (isset($cache[$locale])) return $cache[$locale];
    $file = __DIR__ . '/../lang/' . $locale . '.php';
    $cache[$locale] = is_file($file) ? require $file : [];
    return $cache[$locale];
}

// t('nav.destinos') — cae a español si falta la llave en el idioma activo,
// y a la llave misma (visible, fácil de detectar) si falta en todos lados.
// Reemplaza al format_blog_date() que estaba duplicado en blog.php y
// blog-post.php — ahora en un solo lugar y consciente del idioma activo.
function format_blog_date(string $iso): string {
    $ts = strtotime($iso);
    $month = t('month.' . (int) date('n', $ts));
    return sprintf(t('blog.date_format'), (int) date('j', $ts), $month, (int) date('Y', $ts));
}

function t(string $key): string {
    $locale = current_locale();
    $strings = load_lang($locale);
    if (isset($strings[$key])) return $strings[$key];
    if ($locale !== 'es') {
        $es = load_lang('es');
        if (isset($es[$key])) return $es[$key];
    }
    return $key;
}
