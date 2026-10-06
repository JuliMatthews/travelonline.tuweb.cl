<?php
// Confirma el correo de un cliente a partir del link que le llegó por mail.
// Ruta pensada para abrirse directo en el navegador (no es JSON), por eso
// redirige a /area-clientes con un mensaje en vez de devolver JSON.
require_once __DIR__ . '/inc/client_auth.php';

function redirect_with_message(string $status, string $msg): void {
    header('Location: /area-clientes?verificado=' . $status . '&msg=' . urlencode($msg));
    exit;
}

$token = $_GET['token'] ?? '';
if (!is_string($token) || $token === '') redirect_with_message('error', 'Link inválido.');

$mysqli = db();
$stmt = $mysqli->prepare(
    'SELECT id, client_id FROM client_email_verifications WHERE token = ? AND used_at IS NULL AND expires_at > NOW()'
);
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) redirect_with_message('error', 'Ese link ya no es válido o expiró. Puedes pedir uno nuevo iniciando sesión.');

$mysqli->begin_transaction();
$stmt = $mysqli->prepare('UPDATE client_email_verifications SET used_at = NOW() WHERE id = ?');
$stmt->bind_param('s', $row['id']);
$stmt->execute();
$stmt->close();

$stmt = $mysqli->prepare('UPDATE clients SET email_verified_at = COALESCE(email_verified_at, NOW()) WHERE id = ?');
$stmt->bind_param('s', $row['client_id']);
$stmt->execute();
$stmt->close();
$mysqli->commit();

redirect_with_message('ok', 'Correo confirmado. Ya puedes iniciar sesión.');
