// Botón de modo oscuro/claro del header — el estado inicial ya lo aplica el
// script inline en inc/head.php (antes de pintar, para evitar el flash);
// esto solo maneja el clic y lo guarda.
(function () {
  const STORAGE_KEY = 'to_web_theme';
  const btn = document.getElementById('theme-toggle');
  if (!btn) return;

  btn.addEventListener('click', () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem(STORAGE_KEY, isDark ? 'dark' : 'light');
  });
})();
