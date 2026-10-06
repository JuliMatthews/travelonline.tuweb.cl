<?php
// Genera/actualiza lang/en.php y lang/pt.php a partir de lang/es.php usando
// la API gratuita de MyMemory (sin clave, sin costo). Re-ejecutable: una
// llave ya traducida a mano (no se puede distinguir automáticamente de una
// traducida por la API, así que por ahora SIEMPRE sobreescribe — si alguien
// edita en.php/pt.php a mano, que lo haga en una copia o avise antes de
// re-correr esto). Uso: php scripts/translate-strings.php
//
// MyMemory limita ~500 caracteres por consulta y tiene cuota diaria gratuita
// anónima (~5000 palabras/día) — de sobra para las llaves de interfaz, que
// son frases cortas.

function mymemory_translate(string $text, string $target): string {
    if (trim($text) === '') return $text;
    $url = 'https://api.mymemory.translated.net/get?' . http_build_query([
        'q' => $text,
        'langpair' => 'es|' . $target,
    ]);
    $ctx = stream_context_create(['http' => ['timeout' => 15]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        fwrite(STDERR, "  ! fallo de red traduciendo: \"$text\"\n");
        return $text;
    }
    $data = json_decode($raw, true);
    $translated = $data['responseData']['translatedText'] ?? null;
    if (!$translated || ($data['responseStatus'] ?? 200) != 200) {
        fwrite(STDERR, "  ! MyMemory no devolvió traducción para: \"$text\"\n");
        return $text;
    }
    return html_entity_decode($translated, ENT_QUOTES, 'UTF-8');
}

$esFile = __DIR__ . '/../lang/es.php';
$es = require $esFile;

foreach (['en', 'pt'] as $target) {
    echo "Traduciendo a '$target'...\n";
    $out = [];
    foreach ($es as $key => $text) {
        $out[$key] = mymemory_translate($text, $target);
        echo "  $key: $text -> {$out[$key]}\n";
        usleep(200000); // no saturar la API gratuita
    }

    $exported = var_export($out, true);
    $contents = "<?php\n// Generado automáticamente por scripts/translate-strings.php a partir de\n"
        . "// lang/es.php (API gratuita MyMemory). Si corriges algo a mano aquí, avisa\n"
        . "// antes de volver a correr el script o se sobreescribe.\nreturn $exported;\n";
    file_put_contents(__DIR__ . "/../lang/$target.php", $contents);
    echo "Guardado lang/$target.php\n\n";
}
