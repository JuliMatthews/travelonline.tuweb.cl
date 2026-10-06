// Compartido por todas las páginas del área de clientes ya autenticada
// (el botón "Cerrar sesión" del sidebar).
(function () {
  const btn = document.getElementById('client-logout-btn');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    await fetch('/api-client-logout.php', { method: 'POST' });
    window.location.href = '/area-clientes';
  });
})();
