<?php
// Login de cliente con correo+contraseña. Exige el correo ya verificado —
// si no lo está, se lo decimos claro en vez de dejarlo adivinar.
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$body = json_body();
$email = strtolower(str_field($body, 'email'));
$password = (string) ($body['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    json_error('Correo o contraseña inválidos');
}

$mysqli = db();
$stmt = $mysqli->prepare('SELECT id, name, email, phone, password_hash, email_verified_at FROM clients WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$client || $client['password_hash'] === null || !password_verify($password, $client['password_hash'])) {
    json_error('Correo o contraseña incorrectos');
}
if ($client['email_verified_at'] === null) {
    json_error('Todavía no confirmas tu correo. Revisa tu bandeja de entrada (o la de spam).');
}

$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$session = create_client_session($client['id'], $userAgent, $ip);
set_client_session_cookie($session['id'], $session['expiresAt']);

json_ok(['client' => ['name' => $client['name'], 'email' => $client['email']]]);
