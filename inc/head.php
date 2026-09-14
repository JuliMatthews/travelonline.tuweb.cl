<?php
// Cada página define $pageTitle y opcionalmente $pageDescription ANTES de
// incluir este archivo — es lo que da SEO real página por página (el punto
// completo de haber portado el sitio a PHP server-rendered).
$pageTitle = $pageTitle ?? 'Travel Online';
$pageDescription = $pageDescription ?? 'Circuitos, paquetes todo incluido y combinados a los destinos más buscados.';
?><!doctype html>
<html lang="es" class="h-full antialiased">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body class="flex min-h-full flex-col">
