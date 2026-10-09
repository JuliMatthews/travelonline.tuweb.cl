<?php
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/regions.php';

const PACKAGE_TYPE_LABELS = [
    'circuito' => 'Circuito',
    'todo_incluido' => 'Todo Incluido',
    'combinado' => 'Combinado',
    'promocion_2x1' => 'Promoción 2x1',
];

// 4 valores fijos (tipo de paquete), igual criterio que REGION_NAMES_I18N en
// inc/regions.php: taxonomía fija, se escribe a mano en vez de pasar por la
// tabla de traducciones.
const PACKAGE_TYPE_LABELS_I18N = [
    'es' => ['circuito' => 'Circuito', 'todo_incluido' => 'Todo Incluido', 'combinado' => 'Combinado', 'promocion_2x1' => 'Promoción 2x1'],
    'en' => ['circuito' => 'Tour', 'todo_incluido' => 'All-inclusive', 'combinado' => 'Combo', 'promocion_2x1' => '2x1 Promo'],
    'pt' => ['circuito' => 'Circuito', 'todo_incluido' => 'Tudo Incluído', 'combinado' => 'Combinado', 'promocion_2x1' => 'Promoção 2x1'],
];

function package_type_label(string $type): string {
    $locale = current_locale();
    return PACKAGE_TYPE_LABELS_I18N[$locale][$type] ?? PACKAGE_TYPE_LABELS[$type] ?? $type;
}

// Títulos cargados TODO EN MAYÚSCULAS en el catálogo ("SUPER DUBAI") se
// muestran con mayúscula inicial para que no desentonen junto a los demás.
function display_title(string $title): string {
    $letters = preg_replace('/[^\p{L}]/u', '', $title);
    if (mb_strlen($letters) > 3 && $letters === mb_strtoupper($letters)) {
        return mb_convert_case(mb_strtolower($title), MB_CASE_TITLE, 'UTF-8');
    }
    return $title;
}

function format_clp(int $n): string {
    return '$' . number_format($n, 0, ',', '.');
}

// Tarjeta de paquete del rediseño "Marino Sereno" (portada, promociones, destinos).
function render_package_card(array $pkg): void {
    $cover = $pkg['heroGallery'][0] ?? null;
    $typeLabel = $pkg['packageType'] ? package_type_label($pkg['packageType']) : null;
    $title = display_title($pkg['title']);
    $meta = [];
    if (!empty($pkg['durationNights'])) $meta[] = $pkg['durationNights'] . ' ' . t('package.nights');
    elseif (!empty($pkg['durationDays'])) $meta[] = $pkg['durationDays'] . ' ' . t('package.days');
    if (!empty($pkg['regionSlug'])) $meta[] = region_name($pkg['regionSlug']);
    $hasPrice = ($pkg['priceDisplayMode'] ?? null) !== 'bajo_consulta' && !empty($pkg['priceFromClp']);
    ?>
    <a href="<?= htmlspecialchars(locale_url(current_locale(), '/paquetes/' . $pkg['slug'])) ?>" class="ms-card">
      <div class="ms-photo">
        <?php if ($cover): ?>
          <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy">
        <?php endif; ?>
      </div>
      <div class="ms-card-b">
        <?php if ($typeLabel): ?><span class="ms-tag"><?= htmlspecialchars($typeLabel) ?></span><?php endif; ?>
        <h3><?= htmlspecialchars($title) ?></h3>
        <?php if ($meta): ?><p class="meta"><?= htmlspecialchars(implode(' · ', $meta)) ?></p><?php endif; ?>
        <div class="ms-price">
          <?php if ($hasPrice): ?>
            <small><?= htmlspecialchars(t('package.from_lower')) ?></small>
            <b><?= htmlspecialchars(format_clp($pkg['priceFromClp'])) ?></b>
            <small><?= htmlspecialchars(t(($pkg['priceUnit'] ?? '') === 'per_couple' ? 'package.per_couple_short' : 'package.per_person')) ?></small>
          <?php else: ?>
            <b class="consult"><?= htmlspecialchars(t('package.consult')) ?></b>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php
}
