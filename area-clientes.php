<?php
require_once __DIR__ . '/inc/client_auth.php';

$session = current_client();

$pageTitle = 'Área Clientes — Travel Online';
$activeNav = '';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';

$verificado = $_GET['verificado'] ?? null;
$verificadoMsg = $_GET['msg'] ?? '';
?>

<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">

  <?php if ($verificado === 'ok'): ?>
    <div class="mb-6 rounded-lg border border-green-600/20 bg-green-50 px-4 py-3 text-sm text-green-800">
      <?= htmlspecialchars($verificadoMsg ?: 'Correo confirmado.') ?>
    </div>
  <?php elseif ($verificado === 'error'): ?>
    <div class="mb-6 rounded-lg border border-red-600/20 bg-red-50 px-4 py-3 text-sm text-red-800">
      <?= htmlspecialchars($verificadoMsg ?: 'No pudimos confirmar tu correo.') ?>
    </div>
  <?php endif; ?>

  <?php if ($session): ?>
    <h1 class="font-display text-3xl font-bold text-brand-dark">Hola, <?= htmlspecialchars($session['client']['name']) ?></h1>
    <p class="mt-2 text-foreground/70">Este es el historial de tus cotizaciones con nosotros.</p>

    <div class="mt-6 flex items-center justify-between">
      <span class="text-sm text-foreground/50"><?= htmlspecialchars($session['client']['email']) ?></span>
      <button type="button" id="logout-btn" class="text-sm text-brand underline">Cerrar sesión</button>
    </div>

    <div id="quotes-list" class="mt-6">
      <p class="text-sm text-foreground/50">Cargando...</p>
    </div>

  <?php else: ?>
    <h1 class="font-display text-3xl font-bold text-brand-dark">Área Clientes</h1>
    <p class="mt-2 max-w-xl text-foreground/70">Entra para ver el historial de tus cotizaciones, o crea una cuenta si es tu primera vez.</p>

    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
      <div class="rounded-2xl border border-black/5 bg-brand-light/30 p-6">
        <div class="mb-4 flex gap-2 text-sm font-semibold">
          <button type="button" data-tab="login" class="tab-btn rounded-full bg-brand px-4 py-1.5 text-white">Iniciar sesión</button>
          <button type="button" data-tab="register" class="tab-btn rounded-full px-4 py-1.5 text-brand-dark">Crear cuenta</button>
        </div>

        <div id="google-signin-button" data-client-id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>" class="mb-4"></div>
        <p class="mb-4 text-center text-xs text-foreground/40">— o —</p>

        <form id="login-form" class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-brand-dark">Correo</label>
            <input required type="email" name="email" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-brand-dark">Contraseña</label>
            <input required type="password" name="password" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          </div>
          <p id="login-error" class="hidden text-sm text-red-600"></p>
          <button type="submit" class="w-full rounded-full bg-brand px-4 py-2.5 font-semibold text-white hover:bg-brand-dark">
            Iniciar sesión
          </button>
        </form>

        <form id="register-form" class="hidden space-y-3">
          <div>
            <label class="block text-sm font-medium text-brand-dark">Nombre</label>
            <input required type="text" name="name" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-brand-dark">Correo</label>
            <input required type="email" name="email" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-brand-dark">Teléfono (opcional)</label>
            <input type="tel" name="phone" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-brand-dark">Contraseña</label>
            <input required type="password" name="password" minlength="8" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
            <p class="mt-1 text-xs text-foreground/50">Mínimo 8 caracteres.</p>
          </div>
          <p id="register-error" class="hidden text-sm text-red-600"></p>
          <p id="register-success" class="hidden text-sm text-green-700"></p>
          <button type="submit" class="w-full rounded-full bg-brand px-4 py-2.5 font-semibold text-white hover:bg-brand-dark">
            Crear cuenta
          </button>
        </form>
      </div>

      <div class="flex flex-col justify-center rounded-2xl border border-black/5 p-6">
        <h2 class="font-display font-bold text-brand-dark">¿Para qué sirve esto?</h2>
        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-foreground/70">
          <li>Ver el estado de todas tus cotizaciones en un solo lugar.</li>
          <li>No tener que volver a escribir tus datos cada vez que cotizas.</li>
          <li>Acceso rápido con tu cuenta de Google, si prefieres.</li>
        </ul>
      </div>
    </div>
  <?php endif; ?>

</div>

<script id="area-clientes-data" type="application/json"><?= json_encode(['loggedIn' => (bool) $session], JSON_UNESCAPED_UNICODE) ?></script>
<script src="https://accounts.google.com/gsi/client" defer></script>
<script src="/js/area-clientes.js" defer></script>

<?php require __DIR__ . '/inc/footer.php'; ?>
