<?php
// Cambia (o establece por primera vez) la contraseña del cliente. Si ya
// tenía una, exige la actual antes de reemplazarla — si entró solo con
// Google hasta ahora, no la pide (no existe ninguna que validar) y esto le
// agrega la opción de entrar también con correo+contraseña.
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$session = current_client();
if (!$session) json_error('No autenticado', 401);

$body = json_body();
$currentPassword = (string) ($body['currentPassword'] ?? '');
$newPassword = (string) ($body['newPassword'] ?? '');

if (strlen($newPassword) < 8) json_error('La nueva contraseña debe tener al menos 8 caracteres');

$mysqli = db();
$stmt = $mysqli->prepare('SELECT password_hash FROM clients WHERE id = ?');
$stmt->bind_param('s', $session['client']['id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row['password_hash'] !== null && !password_verify($currentPassword, $row['password_hash'])) {
    json_error('La contraseña actual no es correcta');
}

$hash = password_hash($newPassword, PASSWORD_BCRYPT);
$stmt = $mysqli->prepare('UPDATE clients SET password_hash = ? WHERE id = ?');
$stmt->bind_param('ss', $hash, $session['client']['id']);
$stmt->execute();
$stmt->close();

json_ok(['message' => 'Contraseña actualizada.']);
