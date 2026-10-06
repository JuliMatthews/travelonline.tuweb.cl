(function () {
  const profileForm = document.getElementById('profile-form');
  if (profileForm) {
    profileForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const errorEl = document.getElementById('profile-error');
      const successEl = document.getElementById('profile-success');
      errorEl.classList.add('hidden');
      successEl.classList.add('hidden');
      const fd = new FormData(profileForm);
      const res = await fetch('/api-client-update-profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: fd.get('name'), phone: fd.get('phone') }),
      });
      const data = await res.json();
      if (!data.ok) {
        errorEl.textContent = data.error;
        errorEl.classList.remove('hidden');
        return;
      }
      successEl.textContent = 'Guardado.';
      successEl.classList.remove('hidden');
    });
  }

  const passwordForm = document.getElementById('password-form');
  if (passwordForm) {
    passwordForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const errorEl = document.getElementById('password-error');
      const successEl = document.getElementById('password-success');
      errorEl.classList.add('hidden');
      successEl.classList.add('hidden');
      const fd = new FormData(passwordForm);
      const res = await fetch('/api-client-change-password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          currentPassword: fd.get('currentPassword') || '',
          newPassword: fd.get('newPassword'),
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
      passwordForm.reset();
    });
  }
})();
