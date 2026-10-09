<?php
// Cada página define $pageTitle y opcionalmente $pageDescription ANTES de
// incluir este archivo — es lo que da SEO real página por página (el punto
// completo de haber portado el sitio a PHP server-rendered).
$pageTitle = $pageTitle ?? 'Travel Online';
$pageDescription = $pageDescription ?? 'Circuitos, paquetes todo incluido y combinados a los destinos más buscados.';
?><!doctype html>
<html lang="<?= htmlspecialchars(function_exists('current_locale') ? current_locale() : 'es') ?>" class="h-full antialiased">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta name="theme-color" content="#0f1522">
  <link rel="stylesheet" href="/css/style.css?v=ms2">
  <script>
    // Evita el "flash" de tema incorrecto al cargar — se aplica antes de
    // que se pinte la página. Ver js/theme-toggle.js.
    (function () {
      // Oscuro es el modo principal (decisión 08-10-2026); el claro queda en
      // el botón y se recuerda por visitante.
      var stored = null;
      try { stored = localStorage.getItem("to_web_theme"); } catch (e) {}
      var isDark = stored ? stored === "dark" : true;
      if (isDark) document.documentElement.classList.add("dark");
    })();
  </script>
</head>
<body class="flex min-h-full flex-col">
