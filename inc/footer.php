<?php require_once __DIR__ . '/i18n.php';
$loc = current_locale();
$u = fn(string $path = '') => htmlspecialchars(locale_url($loc, $path));
?>
</main>
<footer class="ms-foot">
  <div class="ms-wrap">
    <div class="ms-foot-in">
      <div>
        <a class="ms-logo" href="<?= $u() ?>"><i></i>Travel Online</a>
        <p style="margin-top:10px"><?= htmlspecialchars(t('footer.address')) ?></p>
      </div>
      <div>
        <h4><?= htmlspecialchars(t('footer.travel')) ?></h4>
        <a href="<?= $u('/promociones') ?>"><?= htmlspecialchars(t('nav.paquetes')) ?></a>
        <a href="<?= $u('/viajes-a-medida/') ?>"><?= htmlspecialchars(t('nav.viajes_medida')) ?></a>
        <a href="<?= $u('/programas/todo-incluido') ?>"><?= htmlspecialchars(t('nav.todo_incluido')) ?></a>
        <a href="<?= $u('/destinos') ?>"><?= htmlspecialchars(t('nav.destinos')) ?></a>
      </div>
      <div>
        <h4><?= htmlspecialchars(t('footer.agency')) ?></h4>
        <a href="<?= $u('/nosotros') ?>"><?= htmlspecialchars(t('nav.nosotros')) ?></a>
        <a href="<?= $u('/blog') ?>"><?= htmlspecialchars(t('nav.blog')) ?></a>
        <a href="<?= $u('/area-clientes') ?>"><?= htmlspecialchars(t('header.area_clientes')) ?></a>
      </div>
      <div>
        <h4><?= htmlspecialchars(t('footer.contact')) ?></h4>
        <a href="tel:+56233661174">+56 2 3366 1174</a>
        <a href="https://wa.me/56981991292" target="_blank" rel="noopener noreferrer">WhatsApp +56 9 8199 1292</a>
        <a href="mailto:reservas@travelonline.cl">reservas@travelonline.cl</a>
        <p><?= htmlspecialchars(t('footer.hours')) ?></p>
      </div>
    </div>
    <div class="ms-legal">
      <span>© <?= date('Y') ?> Travel Online SPA. <?= htmlspecialchars(t('footer.rights')) ?></span>
      <span>
        <a href="<?= $u('/legal/condiciones-de-reserva') ?>"><?= htmlspecialchars(t('footer.conditions')) ?></a>
        <a href="<?= $u('/legal/politica-de-cookies') ?>"><?= htmlspecialchars(t('footer.cookies')) ?></a>
        <a href="<?= $u('/legal/politicas-de-privacidad') ?>"><?= htmlspecialchars(t('footer.privacy')) ?></a>
      </span>
    </div>
  </div>
</footer>

<a class="ms-wsp" href="https://wa.me/56981991292?text=<?= rawurlencode(t('footer.whatsapp_message')) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars(t('footer.whatsapp_aria')) ?>">
  <svg viewBox="0 0 32 32" aria-hidden="true" width="28" height="28" fill="currentColor">
    <path d="M16.04 3C9.37 3 3.98 8.4 3.98 15.06c0 2.24.6 4.34 1.66 6.15L3 29l7.98-2.55a12.9 12.9 0 0 0 5.06 1.02h.01c6.67 0 12.06-5.4 12.06-12.06C28.1 8.4 22.7 3 16.04 3Zm0 22.06h-.01a10 10 0 0 1-5.1-1.4l-.37-.22-4.73 1.51 1.53-4.6-.24-.38a9.98 9.98 0 0 1-1.54-5.35c0-5.5 4.48-9.98 9.98-9.98 2.67 0 5.17 1.04 7.06 2.93a9.9 9.9 0 0 1 2.93 7.06c0 5.5-4.48 9.43-9.51 9.43Zm5.47-7.44c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.06 2.87 1.21 3.07.15.2 2.09 3.19 5.06 4.47.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
  </svg>
</a>
<script src="/js/theme-toggle.js?v=ms1" defer></script>
</body>
</html>
