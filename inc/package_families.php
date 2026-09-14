<?php
// Agrupa paquetes "el mismo producto, distinto destino" bajo una sola
// entrada en el selector de /cotizar — traducción de packageFamilies.ts.
// Agregar un grupo nuevo acá no toca nada más del código.
const PACKAGE_FAMILIES = [
    [
        'id' => 'caribe-romantico',
        'label' => 'Caribe Romántico (Todo Incluido)',
        'memberSlugs' => [
            'caribe-romantico-cancun-todo-incluido',
            'caribe-romantico-punta-cana-todo-incluido',
            'caribe-romantico-san-andres-todo-incluido',
        ],
    ],
    [
        'id' => 'circuito-madrid',
        'label' => 'Circuito Madrid',
        'memberSlugs' => [
            'circuito-madrid-paris',
            'circuito-madrid-roma',
            'circuito-madrid-portugal-andalucia-y-marruecos',
        ],
    ],
    [
        'id' => 'circuito-las-vegas',
        'label' => 'Circuito Las Vegas',
        'memberSlugs' => ['circuito-las-vegas-gran-canon', 'circuito-las-vegas-grandes-parques'],
    ],
];

function short_member_label(string $title): string {
    $parts = preg_split('/[:–-]/', $title);
    if (count($parts) > 1) {
        return trim(preg_replace('/todo incluido/i', '', $parts[1]));
    }
    return $title;
}

// $packages: array de get_all_packages() — devuelve una lista mezclada de
// { kind: 'family', ... } y { kind: 'package', ... } ordenada por label.
function build_package_selector_options(array $packages): array {
    $bySlug = [];
    foreach ($packages as $p) $bySlug[$p['slug']] = $p;

    $groupedSlugs = [];
    foreach (PACKAGE_FAMILIES as $f) {
        foreach ($f['memberSlugs'] as $s) $groupedSlugs[$s] = true;
    }

    $familyOptions = [];
    foreach (PACKAGE_FAMILIES as $family) {
        $members = [];
        foreach ($family['memberSlugs'] as $slug) {
            if (!isset($bySlug[$slug])) continue;
            $p = $bySlug[$slug];
            $members[] = [
                'slug' => $p['slug'],
                'title' => short_member_label($p['title']),
                'imageUrl' => $p['heroGallery'][0] ?? null,
            ];
        }
        if (count($members) === 0) continue;
        $familyOptions[] = ['kind' => 'family', 'id' => $family['id'], 'label' => $family['label'], 'members' => $members];
    }

    $standaloneOptions = [];
    foreach ($packages as $p) {
        if (isset($groupedSlugs[$p['slug']])) continue;
        $standaloneOptions[] = ['kind' => 'package', 'slug' => $p['slug'], 'title' => $p['title'], 'imageUrl' => $p['heroGallery'][0] ?? null];
    }

    $options = array_merge($familyOptions, $standaloneOptions);
    usort($options, function ($a, $b) {
        $labelA = $a['kind'] === 'family' ? $a['label'] : $a['title'];
        $labelB = $b['kind'] === 'family' ? $b['label'] : $b['title'];
        return strcoll($labelA, $labelB);
    });
    return $options;
}

function find_family_containing(array $options, string $slug): ?array {
    foreach ($options as $opt) {
        if ($opt['kind'] !== 'family') continue;
        foreach ($opt['members'] as $m) {
            if ($m['slug'] === $slug) return $opt;
        }
    }
    return null;
}
