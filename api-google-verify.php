<?php
// Verifica el ID token que entrega el botón "Cotiza con tu cuenta Google" de
// /cotizar y devuelve nombre/correo YA VERIFICADOS por Google — el JS nunca
// decodifica el token por su cuenta (podría venir alterado), siempre se
// valida acá contra el propio servidor de Google antes de confiar en nada.
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

set_exception_handler(function () {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
    exit;
});
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function bad_request(string $msg): void {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$credential = is_array($body) ? ($body['credential'] ?? null) : null;
if (!is_string($credential) || $credential === '') bad_request('Falta el token de Google');

$url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
]);
$response = curl_exec($ch);
curl_close($ch);
if ($response === false) bad_request('No se pudo verificar el token con Google');

$claims = json_decode($response, true);
if (!is_array($claims) || isset($claims['error'])) bad_request('Token de Google inválido o expirado');

$validIssuer = in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
$validAudience = ($claims['aud'] ?? '') === GOOGLE_CLIENT_ID;
$emailVerified = ($claims['email_verified'] ?? 'false') === 'true';

if (!$validIssuer || !$validAudience || !$emailVerified) {
    bad_request('Token de Google inválido');
}

echo json_encode([
    'ok' => true,
    'name' => $claims['name'] ?? trim(($claims['given_name'] ?? '') . ' ' . ($claims['family_name'] ?? '')),
    'email' => $claims['email'],
]);
