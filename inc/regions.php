<?php
require_once __DIR__ . '/i18n.php';
// Fuente única de las 6 regiones fijas — mismos slugs/nombres que la tabla
// `regions` en MySQL, pero estos datos (nombre para el nav, imagen de
// portada) no son contenido editable por el panel (fuera de alcance).
const REGION_SLUGS = ['europa', 'asia', 'america', 'medio-oriente', 'africa', 'combinados'];

const REGION_NAMES = [
    'europa' => 'Europa',
    'asia' => 'Asia',
    'america' => 'América',
    'medio-oriente' => 'Medio Oriente',
    'africa' => 'África',
    'combinados' => 'Combinados',
];

// Las 6 regiones son una taxonomía fija (no editable por el panel, ver
// arriba) así que sus nombres por idioma se escriben a mano acá en vez de
// pasar por la tabla de traducciones o la API automática — son solo 18
// palabras y conviene que queden exactas.
const REGION_NAMES_I18N = [
    'es' => [
        'europa' => 'Europa', 'asia' => 'Asia', 'america' => 'América',
        'medio-oriente' => 'Medio Oriente', 'africa' => 'África', 'combinados' => 'Combinados',
    ],
    'en' => [
        'europa' => 'Europe', 'asia' => 'Asia', 'america' => 'Americas',
        'medio-oriente' => 'Middle East', 'africa' => 'Africa', 'combinados' => 'Combined',
    ],
    'pt' => [
        'europa' => 'Europa', 'asia' => 'Ásia', 'america' => 'América',
        'medio-oriente' => 'Médio Oriente', 'africa' => 'África', 'combinados' => 'Combinados',
    ],
];

function region_name(string $slug, ?string $locale = null): string {
    $locale = $locale ?? current_locale();
    return REGION_NAMES_I18N[$locale][$slug] ?? REGION_NAMES[$slug] ?? $slug;
}

const REGION_IMAGES = [
    'europa' => '/regions/europa.jpg',
    'asia' => '/regions/asia.jpg',
    'america' => '/regions/america.jpg',
    'medio-oriente' => '/regions/medio-oriente.jpg',
    'africa' => '/regions/africa.jpg',
    'combinados' => '/regions/combinados.jpg',
];
