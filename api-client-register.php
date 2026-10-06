<?php
// Registro de cliente con correo+contraseña. No abre sesión de inmediato —
// exige verificar el correo primero (ver api-client-verify-email.php), para
// no quedarnos con direcciones falsas en la base de un negocio que depende
// de contactar gente real.
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$body = json_body();
$name = str_field($body, 'name');
$email = strtolower(str_field($body, 'email'));
$password = (string) ($body['password'] ?? '');
$phone = str_field($body, 'phone') ?: null;

if ($name === '') json_error('Falta el nombre');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Correo inválido');
if (strlen($password) < 8) json_error('La contraseña debe tener al menos 8 caracteres');

$mysqli = db();

$stmt = $mysqli->prepare('SELECT id, password_hash FROM clients WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    if ($existing['password_hash'] !== null) {
        json_error('Ya existe una cuenta con ese correo. Si es tuya, inicia sesión.');
    }
    // Existía solo por Google (sin contraseña) — le agregamos una, sin perder
    // el historial de cotizaciones ya vinculado a ese cliente.
    $id = $existing['id'];
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare('UPDATE clients SET password_hash = ?, name = ?, phone = COALESCE(?, phone) WHERE id = ?');
    $stmt->bind_param('ssss', $hash, $name, $phone, $id);
    $stmt->execute();
    $stmt->close();
} else {
    $id = new_uuid();
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare('INSERT INTO clients (id, email, password_hash, name, phone) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $id, $email, $hash, $name, $phone);
    $stmt->execute();
    $stmt->close();
}

// Token de verificación — 48h de validez.
$token = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', time() + 48 * 3600);
$verificationId = new_uuid();
$stmt = $mysqli->prepare('INSERT INTO client_email_verifications (id, client_id, token, expires_at) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $verificationId, $id, $token, $expiresAt);
$stmt->execute();
$stmt->close();

$verifyUrl = rtrim((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'], '/')
    . '/api-client-verify-email.php?token=' . $token;

$subject = '=?UTF-8?B?' . base64_encode('Confirma tu correo — Travel Online') . '?=';
$messageBody = "Hola $name,\r\n\r\n"
    . "Gracias por crear tu cuenta en Travel Online. Confirma tu correo entrando a este link:\r\n\r\n"
    . "$verifyUrl\r\n\r\n"
    . "Si no creaste esta cuenta, puedes ignorar este correo.\r\n";
$headers = "From: Travel Online <noreply@travelonline.tuweb.cl>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
@mail($email, $subject, $messageBody, $headers);

json_ok(['message' => 'Cuenta creada. Revisa tu correo para confirmarla antes de iniciar sesión.']);
