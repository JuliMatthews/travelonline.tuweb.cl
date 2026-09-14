<?php
$pageTitle = '¡Gracias! — Travel Online';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';
?>

<div class="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
  <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-light">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-dark">
      <path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </div>
  <h1 class="font-display mt-6 text-3xl font-bold text-brand-dark">¡Gracias por tu cotización!</h1>
  <p class="mt-3 text-foreground/70">
    Recibimos tu solicitud. Uno de nuestros asesores se pondrá en contacto contigo a la brevedad para afinar los detalles de tu viaje.
  </p>
  <a href="/" class="mt-8 inline-block rounded-full bg-brand px-6 py-3 font-semibold text-white transition hover:bg-brand-dark">
    Volver al inicio
  </a>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
