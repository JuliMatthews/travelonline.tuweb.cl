// Formulario de Viajes a medida (/viajes-a-medida/): 3 pasos, resumen en vivo,
// validación de ayuda (la real está en api-custom-trip.php), envío al CRM y
// modal de acceso con las cuentas reales de clientes (correo o Google).
(function () {
  const root = document.getElementById('vam');
  if (!root) return;
  const T = JSON.parse(root.dataset.i18n || '{}');
  const t = (k) => T[k] || k;
  const $ = (s) => document.querySelector(s);
  const $$ = (s) => [...document.querySelectorAll(s)];
  const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const fmt = (tpl, v) => tpl.replace(/%[sd]/, v);

  let step = 1;
  const st = { adultos: Number($('#adultos').textContent) || 2, ninos: 0 };

  // ── Pasos ──
  function show(n) {
    step = n;
    $$('[data-step]').forEach((s) => (s.hidden = Number(s.dataset.step) !== n));
    $$('.ms-st').forEach((s) => {
      const k = Number(s.dataset.st);
      s.classList.toggle('on', k === n);
      s.classList.toggle('done', k < n);
    });
    $('#back').hidden = n === 1;
    $('#skip').hidden = n !== 2;
    $('#next').textContent = n === 3 ? t('vam.send') : t('vam.next');
    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  function bad(id, msg) {
    const f = $('#fd-' + id);
    if (!f) return !msg;
    f.classList.toggle('bad', !!msg);
    f.querySelector('.ms-err').textContent = msg || '';
    return !msg;
  }
  function valid(n) {
    if (n === 1) return bad('destino', $('#destino').value.trim().length < 2 ? t('vam.err_dest') : '');
    if (n === 3) {
      const a = bad('nombre', /\S+\s+\S+/.test($('#nombre').value.trim()) ? '' : t('vam.err_name'));
      const b = bad('correo', /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test($('#correo').value.trim()) ? '' : t('vam.err_email'));
      const c = bad('whatsapp', $('#whatsapp').value.replace(/\D/g, '').length >= 8 ? '' : t('vam.err_phone'));
      const d = bad('consentimiento', $('#consentimiento').checked ? '' : t('vam.err_consent'));
      return a && b && c && d;
    }
    return true;
  }
  $('#next').addEventListener('click', () => {
    if (!valid(step)) return;
    if (step < 3) show(step + 1);
    else send();
  });
  $('#back').addEventListener('click', () => show(step - 1));
  $('#skip').addEventListener('click', () => show(3));

  // ── Contadores y edades ──
  $$('.ms-counter').forEach((c) =>
    c.addEventListener('click', (e) => {
      const b = e.target.closest('button');
      if (!b) return;
      const k = c.dataset.c;
      const min = k === 'adultos' ? 1 : 0;
      const max = k === 'adultos' ? 20 : 10;
      st[k] = Math.max(min, Math.min(max, st[k] + Number(b.dataset.d)));
      $('#' + k).textContent = st[k];
      if (k === 'ninos') ages();
      summary();
    })
  );
  function ages() {
    const box = $('#edades');
    const prev = $$('#edades select').map((s) => s.value);
    let html = '';
    for (let i = 0; i < st.ninos; i++) {
      let opts = `<option value="">${esc(fmt(t('vam.child_n'), i + 1))}</option>`;
      for (let a = 0; a <= 17; a++) opts += `<option value="${a}"${prev[i] === String(a) ? ' selected' : ''}>${a} ${esc(t('vam.years'))}</option>`;
      html += `<select class="ms-input" aria-label="${esc(fmt(t('vam.child_n'), i + 1))}">${opts}</select>`;
    }
    box.innerHTML = html;
    $('#f-edades').hidden = st.ninos === 0;
  }
  $('#cuando').addEventListener('change', () => {
    const exact = $('#cuando').value === '5';
    $('#f-ida').hidden = !exact;
    $('#f-vuelta').hidden = !exact;
    summary();
  });

  // ── Resumen ──
  function data() {
    const cuandoSel = $('#cuando');
    const when = cuandoSel.value === '5'
      ? `${$('#ida').value || '¿?'} → ${$('#vuelta').value || '¿?'}`
      : cuandoSel.options[cuandoSel.selectedIndex].text;
    const eds = $$('#edades select').map((s) => (s.value === '' ? '¿?' : s.value)).join(', ');
    let paxTxt = `${st.adultos} ${st.adultos === 1 ? t('vam.adult_s') : t('vam.adult_p')}`;
    if (st.ninos) paxTxt += ` + ${st.ninos} ${st.ninos === 1 ? t('vam.child_s') : t('vam.child_p')} (${eds} ${t('vam.years')})`;
    const inc = $$('input[name="incluir"]:checked').map((i) => i.nextElementSibling.textContent.replace(/^[^\p{L}]+/u, '').trim());
    const tipo = $$('input[name="tipo"]:checked').map((i) => i.nextElementSibling.textContent.trim())[0];
    const pres = $$('input[name="presupuesto"]:checked').map((i) => i.nextElementSibling.textContent.trim())[0];
    return [
      [t('vam.sum_dest'), $('#destino').value.trim() || '—'],
      [t('vam.sum_origin'), $('#origen').value.trim() || '—'],
      [t('vam.sum_when'), when],
      [t('vam.sum_pax'), paxTxt],
      [t('vam.sum_type'), tipo || t('vam.not_set')],
      [t('vam.sum_inc'), inc.join(', ') || t('vam.not_set')],
      [t('vam.sum_budget'), pres || t('vam.not_set')],
      [t('vam.sum_comments'), $('#comentarios').value.trim() || '—'],
    ];
  }
  function summary() {
    $('#resumen').innerHTML = data().slice(0, 7).map(([k, v]) => `<dt>${esc(k)}</dt><dd>${esc(v)}</dd>`).join('');
  }
  document.addEventListener('input', summary);
  document.addEventListener('change', summary);

  // ── Envío ──
  async function send() {
    const btn = $('#next');
    const errEl = $('#send-error');
    errEl.hidden = true;
    btn.disabled = true;
    btn.textContent = t('vam.sending');
    const payload = {
      destino: $('#destino').value,
      origen: $('#origen').value,
      cuando: Number($('#cuando').value),
      ida: $('#ida').value,
      vuelta: $('#vuelta').value,
      adultos: st.adultos,
      ninos: st.ninos,
      edades: $$('#edades select').map((s) => s.value),
      tipo: Number(($$('input[name="tipo"]:checked')[0] || {}).value || 0),
      incluir: $$('input[name="incluir"]:checked').map((i) => Number(i.value)),
      presupuesto: Number(($$('input[name="presupuesto"]:checked')[0] || {}).value || 0),
      comentarios: $('#comentarios').value,
      nombre: $('#nombre').value,
      correo: $('#correo').value,
      codigo: $('#codigo').value,
      whatsapp: $('#whatsapp').value,
      consentimiento: $('#consentimiento').checked,
      web: document.querySelector('input[name="web"]').value,
    };
    try {
      const res = await fetch('/api-custom-trip.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const out = await res.json();
      if (!out.ok) throw new Error(out.error || 'error');
      thanks(out.folio);
    } catch (e) {
      errEl.textContent = t('vam.err_send');
      errEl.hidden = false;
      btn.disabled = false;
      btn.textContent = t('vam.send');
    }
  }
  function thanks(folio) {
    const name = $('#nombre').value.trim().split(/\s+/)[0];
    const email = $('#correo').value.trim();
    $('#th-title').textContent = fmt(t('vam.th_title'), name);
    $('#th-folio').textContent = folio;
    $('#th-copy').textContent = fmt(t('vam.th_copy'), email);
    $('#th-wsp').href = 'https://wa.me/56981991292?text=' + encodeURIComponent(fmt(t('vam.th_wsp_msg'), folio));
    const rows = [...data(), [t('vam.name').replace(' *', ''), $('#nombre').value.trim()], [t('vam.email').replace(' *', ''), email], [t('vam.whatsapp').replace(' *', ''), $('#codigo').value + ' ' + $('#whatsapp').value.trim()]];
    $('#th-detail').innerHTML = rows.map(([k, v]) => `<tr><td>${esc(k)}</td><td>${esc(v)}</td></tr>`).join('');
    $('#vam-form-view').hidden = true;
    $('#vam-thanks').hidden = false;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ── Sesión del cliente + modal de acceso ──
  function toast(msg) {
    const el = $('#toast');
    el.textContent = msg;
    el.hidden = false;
    clearTimeout(toast.t);
    toast.t = setTimeout(() => (el.hidden = true), 2600);
  }
  function applyClient(c) {
    const on = !!c;
    $('#guest-hint').hidden = on;
    $('#logged-card').hidden = !on;
    $('#correo').readOnly = on;
    if (on) {
      $('#lc-name').textContent = c.name;
      $('#lc-av').textContent = (c.name || 'C').charAt(0).toUpperCase();
      if (c.name) $('#nombre').value = c.name;
      if (c.email) $('#correo').value = c.email;
      if (c.phone && !$('#whatsapp').value) $('#whatsapp').value = String(c.phone).replace(/^\+?56\s*/, '');
      ['nombre', 'correo', 'whatsapp'].forEach((k) => bad(k, ''));
    }
    summary();
  }
  async function refreshClient() {
    try {
      const r = await fetch('/api-client-me.php', { credentials: 'same-origin' });
      const d = await r.json();
      if (d.ok && d.authenticated) {
        applyClient(d.client);
        return true;
      }
    } catch (e) {}
    return false;
  }

  const dlg = $('#auth');
  function view(name) {
    dlg.querySelectorAll('[data-view]').forEach((v) => (v.hidden = v.dataset.view !== name));
    if (name === 'login') setTimeout(() => $('#a-mail').focus(), 30);
  }
  function openAuth() {
    view('elegir');
    if (!dlg.open) dlg.showModal();
    renderGoogle();
  }
  async function loggedIn() {
    if (await refreshClient()) {
      dlg.close();
      toast(t('vam.m_ok'));
    }
  }
  dlg.addEventListener('click', (e) => {
    if (e.target === dlg) return dlg.close();
    const go = e.target.closest('[data-go]');
    if (go) view(go.dataset.go);
    if (e.target.closest('[data-close]')) dlg.close();
  });
  document.addEventListener('click', async (e) => {
    if (e.target.closest('[data-open-auth]')) openAuth();
    if (e.target.closest('[data-logout]')) {
      await fetch('/api-client-logout.php', { method: 'POST', credentials: 'same-origin' }).catch(() => {});
      applyClient(null);
      ['nombre', 'correo', 'whatsapp'].forEach((k) => ($('#' + k).value = ''));
      toast(t('vam.signed_out'));
    }
  });
  $('#a-login').addEventListener('click', async () => {
    const err = $('#a-err');
    err.hidden = true;
    try {
      const r = await fetch('/api-client-login.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: $('#a-mail').value, password: $('#a-pass').value }),
      });
      const d = await r.json();
      if (!d.ok) {
        err.textContent = d.error;
        err.hidden = false;
        return;
      }
      loggedIn();
    } catch (e) {
      err.textContent = t('vam.err_send');
      err.hidden = false;
    }
  });
  $('#a-ready').addEventListener('click', loggedIn);

  let googleReady = false;
  function renderGoogle() {
    const el = $('#g-btn');
    if (googleReady || !el || !el.dataset.clientId) return;
    if (!window.google || !google.accounts || !google.accounts.id) return setTimeout(renderGoogle, 150);
    googleReady = true;
    google.accounts.id.initialize({
      client_id: el.dataset.clientId,
      callback: async (resp) => {
        try {
          const r = await fetch('/api-client-google.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ credential: resp.credential }),
          });
          const d = await r.json();
          if (d.ok) loggedIn();
        } catch (e) {}
      },
    });
    google.accounts.id.renderButton(el, { theme: 'outline', size: 'large', text: 'continue_with', shape: 'pill', width: Math.round(Math.max(200, Math.min(el.getBoundingClientRect().width || dlg.querySelector('.ms-dialog-in').clientWidth - 52, 360))), locale: root.dataset.locale });
  }

  if (st.ninos) ages();
  summary();
})();
