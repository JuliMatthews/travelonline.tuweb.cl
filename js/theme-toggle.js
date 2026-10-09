// Botón de modo oscuro/claro + menú de celular del header. El modo inicial ya
// lo aplica el script inline en inc/head.php (antes de pintar, para evitar el
// flash; oscuro por defecto); esto solo maneja los clics y lo guarda.
(function () {
  const STORAGE_KEY = 'to_web_theme';
  const btn = document.getElementById('theme-toggle');
  if (btn) {
    btn.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      try { localStorage.setItem(STORAGE_KEY, isDark ? 'dark' : 'light'); } catch (e) {}
    });
  }

  const menuBtn = document.getElementById('ms-menu-btn');
  const menu = document.getElementById('ms-mnav');
  if (!menuBtn || !menu) return;
  const openIcon = menuBtn.innerHTML;
  const closeIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
  function setMenu(open) {
    menu.hidden = !open;
    menuBtn.setAttribute('aria-expanded', String(open));
    menuBtn.setAttribute('aria-label', open ? menuBtn.dataset.closeLabel : menuBtn.dataset.openLabel);
    menuBtn.innerHTML = open ? closeIcon : openIcon;
  }
  menuBtn.addEventListener('click', () => setMenu(menu.hidden));
  menu.addEventListener('click', (e) => { if (e.target.closest('a')) setMenu(false); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !menu.hidden) { setMenu(false); menuBtn.focus(); }
  });
})();
