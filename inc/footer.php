</main>
<footer class="mt-auto border-t border-border bg-brand-light/40">
  <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-foreground/70 sm:px-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <p>Travel Online — Chile</p>
      <div class="flex gap-4">
        <a href="/legal/condiciones-de-reserva">Condiciones de reserva</a>
        <a href="/legal/politica-de-cookies">Cookies</a>
        <a href="/legal/politicas-de-privacidad">Privacidad</a>
      </div>
    </div>
    <p class="mt-4">© <?= date('Y') ?> Travel Online. Todos los derechos reservados.</p>
  </div>
</footer>

<a
  href="https://wa.me/56981991292?text=<?= rawurlencode('Hola, me gustaría cotizar un viaje') ?>"
  target="_blank"
  rel="noopener noreferrer"
  aria-label="Escríbenos por WhatsApp"
  class="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition-transform hover:scale-105"
>
  <svg viewBox="0 0 32 32" aria-hidden="true" class="h-7 w-7 fill-current">
    <path d="M16.04 3C9.37 3 3.98 8.4 3.98 15.06c0 2.24.6 4.34 1.66 6.15L3 29l7.98-2.55a12.9 12.9 0 0 0 5.06 1.02h.01c6.67 0 12.06-5.4 12.06-12.06C28.1 8.4 22.7 3 16.04 3Zm0 22.06h-.01a10 10 0 0 1-5.1-1.4l-.37-.22-4.73 1.51 1.53-4.6-.24-.38a9.98 9.98 0 0 1-1.54-5.35c0-5.5 4.48-9.98 9.98-9.98 2.67 0 5.17 1.04 7.06 2.93a9.9 9.9 0 0 1 2.93 7.06c0 5.5-4.48 9.43-9.51 9.43Zm5.47-7.44c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.06 2.87 1.21 3.07.15.2 2.09 3.19 5.06 4.47.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z" />
  </svg>
</a>
<script src="/js/theme-toggle.js" defer></script>
</body>
</html>
