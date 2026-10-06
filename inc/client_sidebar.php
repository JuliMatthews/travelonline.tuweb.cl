<?php
// Layout de dos columnas para el área de clientes autenticada (Mis
// cotizaciones / Mi perfil). Cada página define $clientActiveNav antes de
// incluir esto ('cotizaciones' o 'perfil').
$clientActiveNav = $clientActiveNav ?? '';
$clientNavItems = [
    'cotizaciones' => ['href' => '/area-clientes', 'label' => 'Mis cotizaciones'],
    'perfil' => ['href' => '/area-clientes/perfil', 'label' => 'Mi perfil'],
];
?>
<div class="mx-auto grid max-w-5xl grid-cols-1 gap-8 px-4 py-16 sm:px-6 lg:grid-cols-[220px_1fr]">
  <aside class="h-fit rounded-2xl border border-border bg-surface p-4 lg:sticky lg:top-20">
    <nav class="flex flex-row gap-1 overflow-x-auto lg:flex-col lg:overflow-visible">
      <?php foreach ($clientNavItems as $key => $item): ?>
        <a href="<?= htmlspecialchars($item['href']) ?>"
           class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium transition <?= $key === $clientActiveNav ? 'bg-brand text-white' : 'text-foreground/70 hover:bg-brand-light/40' ?>">
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
      <button type="button" id="client-logout-btn"
        class="whitespace-nowrap rounded-lg px-3 py-2 text-left text-sm font-medium text-foreground/70 transition hover:bg-brand-light/40">
        Cerrar sesión
      </button>
    </nav>
  </aside>
  <div>
