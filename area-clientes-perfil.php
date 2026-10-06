<?php
require_once __DIR__ . '/inc/client_auth.php';

$session = current_client();
if (!$session) {
    header('Location: /area-clientes');
    exit;
}

$stmt = db()->prepare('SELECT password_hash IS NOT NULL AS has_password, google_sub IS NOT NULL AS has_google FROM clients WHERE id = ?');
$stmt->bind_param('s', $session['client']['id']);
$stmt->execute();
$flags = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = 'Mi perfil — Travel Online';
$activeNav = '';
require __DIR__ . '/inc/head.php';
require __DIR__ . '/inc/header.php';

$clientActiveNav = 'perfil';
require __DIR__ . '/inc/client_sidebar.php';
?>

<h1 class="font-display text-3xl font-bold text-brand-dark">Mi perfil</h1>
<p class="mt-2 text-foreground/70">Correo: <?= htmlspecialchars($session['client']['email']) ?></p>

<div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
  <div class="card rounded-2xl border border-border bg-surface p-6">
    <h2 class="font-display font-bold text-brand-dark">Datos de contacto</h2>
    <form id="profile-form" class="mt-4 space-y-3">
      <div>
        <label class="block text-sm font-medium text-brand-dark">Nombre</label>
        <input required type="text" name="name" value="<?= htmlspecialchars($session['client']['name']) ?>"
          class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium text-brand-dark">Teléfono</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($session['client']['phone'] ?? '') ?>"
          class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
      </div>
      <p id="profile-error" class="hidden text-sm text-red-600"></p>
      <p id="profile-success" class="hidden text-sm text-green-700"></p>
      <button type="submit" class="rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">
        Guardar cambios
      </button>
    </form>
  </div>

  <div class="card rounded-2xl border border-border bg-surface p-6">
    <h2 class="font-display font-bold text-brand-dark">
      <?= $flags['has_password'] ? 'Cambiar contraseña' : 'Crear contraseña' ?>
    </h2>
    <p class="mt-1 text-xs text-foreground/50">
      <?= $flags['has_password']
        ? 'Úsala junto a tu correo para entrar sin pasar por Google.'
        : 'Hoy entras solo con Google — si quieres, agrega una contraseña para poder entrar también con tu correo.' ?>
    </p>
    <form id="password-form" class="mt-4 space-y-3">
      <?php if ($flags['has_password']): ?>
        <div>
          <label class="block text-sm font-medium text-brand-dark">Contraseña actual</label>
          <input required type="password" name="currentPassword" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        </div>
      <?php endif; ?>
      <div>
        <label class="block text-sm font-medium text-brand-dark"><?= $flags['has_password'] ? 'Nueva contraseña' : 'Contraseña' ?></label>
        <input required type="password" name="newPassword" minlength="8" class="mt-1 w-full rounded-lg border border-black/10 bg-background px-3 py-2">
        <p class="mt-1 text-xs text-foreground/50">Mínimo 8 caracteres.</p>
      </div>
      <p id="password-error" class="hidden text-sm text-red-600"></p>
      <p id="password-success" class="hidden text-sm text-green-700"></p>
      <button type="submit" class="rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">
        <?= $flags['has_password'] ? 'Actualizar contraseña' : 'Crear contraseña' ?>
      </button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/inc/client_sidebar_end.php'; ?>
<script src="/js/area-clientes-perfil.js" defer></script>
<?php require __DIR__ . '/inc/footer.php'; ?>
