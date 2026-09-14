<?php
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

const REGION_IMAGES = [
    'europa' => '/regions/europa.jpg',
    'asia' => '/regions/asia.jpg',
    'america' => '/regions/america.jpg',
    'medio-oriente' => '/regions/medio-oriente.jpg',
    'africa' => '/regions/africa.jpg',
    'combinados' => '/regions/combinados.jpg',
];
