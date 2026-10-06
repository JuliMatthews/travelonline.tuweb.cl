<?php
// Verifica el ID token del botón "Cotiza con tu cuenta Google" de /cotizar y
// devuelve nombre/correo para autocompletar el formulario. De paso, registra
// (o actualiza) al cliente en la base para trazabilidad — aunque la persona
// no termine de enviar la cotización, ya queda su visita/login guardada.
// NO abre sesión de cliente acá (ver api-client-google.php para eso).
require_once __DIR__ . '/inc/google_auth.php';

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

$claims = verify_google_credential($credential);
if ($claims === null) bad_request('Token de Google inválido');

upsert_client_from_google($claims);

echo json_encode([
    'ok' => true,
    'name' => $claims['name'],
    'email' => $claims['email'],
]);
