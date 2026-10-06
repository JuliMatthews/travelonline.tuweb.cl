// Botón real "Cotiza con tu cuenta Google" — solo autocompleta nombre/correo,
// no es un login del sitio. El token que entrega Google SIEMPRE se valida en
// el servidor (api-google-verify.php) antes de confiar en el nombre/correo;
// este script nunca decodifica el token por su cuenta.
(function () {
  const buttonEl = document.getElementById("google-signin-button");
  if (!buttonEl) return;
  const clientId = buttonEl.dataset.clientId;
  if (!clientId) return;

  const doneBlock = document.getElementById("google-signin-done");
  const doneName = document.getElementById("google-signin-name");
  const resetBtn = document.getElementById("google-signin-reset");
  const nameInput = document.getElementById("passenger-name");
  const emailInput = document.getElementById("passenger-email");

  async function handleCredential(response) {
    try {
      const res = await fetch("/api-google-verify.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ credential: response.credential }),
      });
      const data = await res.json();
      if (!data.ok) return;

      if (nameInput && !nameInput.value) nameInput.value = data.name || "";
      if (emailInput) emailInput.value = data.email || "";

      doneName.textContent = "Conectado como " + data.email;
      buttonEl.classList.add("hidden");
      doneBlock.classList.remove("hidden");
      doneBlock.classList.add("flex");
    } catch (err) {
      // Silencioso: si falla, la persona igual puede llenar el formulario a mano.
    }
  }

  function init() {
    if (!window.google || !window.google.accounts || !window.google.accounts.id) {
      setTimeout(init, 100);
      return;
    }
    google.accounts.id.initialize({ client_id: clientId, callback: handleCredential });
    const width = Math.min(Math.max(buttonEl.offsetWidth || 320, 200), 400);
    google.accounts.id.renderButton(buttonEl, { theme: "outline", size: "large", text: "continue_with", width });
  }
  init();

  if (resetBtn) {
    resetBtn.addEventListener("click", () => {
      doneBlock.classList.add("hidden");
      doneBlock.classList.remove("flex");
      buttonEl.classList.remove("hidden");
      if (nameInput) nameInput.value = "";
      if (emailInput) emailInput.value = "";
    });
  }
})();
