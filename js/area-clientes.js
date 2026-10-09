(function () {
  const dataEl = document.getElementById('area-clientes-data');
  const { loggedIn } = JSON.parse(dataEl.textContent);

  // El CRM tiene 6 estados internos; el cliente ve una línea de tiempo simple
  // (decisión 2026-10-08): Recibida → En preparación → Cotización enviada → Confirmada · Cerrada.
  const STATUS_LABEL = {
    nueva: 'Recibida',
    cotizacion_enviada: 'Cotización enviada',
    en_seguimiento: 'Cotización enviada',
    respondido: 'En preparación',
    venta_cerrada: 'Confirmada',
    no_interesado: 'Cerrada',
  };

  function formatCLP(clp) {
    return '$' + Math.round(clp).toLocaleString('es-CL');
  }

  // --- Tabs (login / registro) ---
  const tabBtns = document.querySelectorAll('.tab-btn');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');
  if (tabBtns.length) {
    tabBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        tabBtns.forEach((b) => {
          b.classList.toggle('bg-brand', b === btn);
          b.classList.toggle('text-white', b === btn);
          b.classList.toggle('text-brand-dark', b !== btn);
        });
        loginForm.classList.toggle('hidden', btn.dataset.tab !== 'login');
        registerForm.classList.toggle('hidden', btn.dataset.tab !== 'register');
      });
    });
  }

  // --- Login con correo+contraseña ---
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const errorEl = document.getElementById('login-error');
      errorEl.classList.add('hidden');
      const fd = new FormData(loginForm);
      const res = await fetch('/api-client-login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: fd.get('email'), password: fd.get('password') }),
      });
      const data = await res.json();
      if (!data.ok) {
        errorEl.textContent = data.error;
        errorEl.classList.remove('hidden');
        return;
      }
      window.location.href = '/area-clientes';
    });
  }

  // --- Registro con correo+contraseña ---
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const errorEl = document.getElementById('register-error');
      const successEl = document.getElementById('register-success');
      errorEl.classList.add('hidden');
      successEl.classList.add('hidden');
      const fd = new FormData(registerForm);
      const res = await fetch('/api-client-register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: fd.get('name'),
          email: fd.get('email'),
          phone: fd.get('phone'),
          password: fd.get('password'),
        }),
      });
      const data = await res.json();
      if (!data.ok) {
        errorEl.textContent = data.error;
        errorEl.classList.remove('hidden');
        return;
      }
      successEl.textContent = data.message;
      successEl.classList.remove('hidden');
      registerForm.reset();
    });
  }

  // --- Google ---
  const googleBtn = document.getElementById('google-signin-button');
  if (googleBtn) {
    const clientId = googleBtn.dataset.clientId;
    async function handleCredential(response) {
      const res = await fetch('/api-client-google.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: response.credential }),
      });
      const data = await res.json();
      if (data.ok) window.location.href = '/area-clientes';
    }
    function initGoogle() {
      if (!window.google || !window.google.accounts || !window.google.accounts.id) {
        setTimeout(initGoogle, 100);
        return;
      }
      google.accounts.id.initialize({ client_id: clientId, callback: handleCredential });
      const width = Math.min(Math.max(googleBtn.offsetWidth || 320, 200), 400);
      google.accounts.id.renderButton(googleBtn, { theme: 'outline', size: 'large', text: 'continue_with', width });
    }
    initGoogle();
  }

  // --- Lista de cotizaciones (si está logueado) ---
  const quotesList = document.getElementById('quotes-list');
  if (loggedIn && quotesList) {
    fetch('/api-client-quotes.php')
      .then((r) => r.json())
      .then((data) => {
        if (!data.ok) {
          quotesList.innerHTML = '<p class="text-sm text-red-600">No pudimos cargar tus cotizaciones.</p>';
          return;
        }
        if (data.quotes.length === 0) {
          quotesList.innerHTML = '<p class="text-sm text-foreground/50">Todavía no tienes cotizaciones. <a href="/cotizar" class="text-brand underline">Cotiza tu próximo viaje</a>.</p>';
          return;
        }
        quotesList.innerHTML = data.quotes.map((q) => `
          <div class="mb-3 rounded-xl border border-black/5 bg-surface p-4">
            <div class="flex items-center justify-between">
              <p class="font-semibold text-brand-dark">${q.packageTitle}</p>
              <span class="rounded-full bg-brand-light px-2 py-1 text-xs text-brand-dark">${STATUS_LABEL[q.status] || q.status}</span>
            </div>
            <p class="mt-1 text-xs text-foreground/50">${new Date(q.createdAt).toLocaleDateString('es-CL')}</p>
            <p class="mt-2 text-sm text-foreground/70">
              ${q.totalClp != null ? 'Total estimado: ' + formatCLP(q.totalClp) : 'Bajo consulta'}
            </p>
          </div>
        `).join('');
      });
  }
})();
