<?php
// Login real (con sesión) para /area-clientes vía Google — distinto de
// api-google-verify.php, que solo autocompleta el formulario de /cotizar
// sin abrir sesión. Acá sí se crea una client_session y se deja la cookie.
require_once __DIR__ . '/inc/google_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$body = json_body();
$credential = $body['credential'] ?? null;
if (!is_string($credential) || $credential === '') json_error('Falta el token de Google');

$claims = verify_google_credential($credential);
if ($claims === null) json_error('Token de Google inválido');

$client = upsert_client_from_google($claims);

$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$session = create_client_session($client['id'], $userAgent, $ip);
set_client_session_cookie($session['id'], $session['expiresAt']);

json_ok(['client' => ['name' => $client['name'], 'email' => $client['email']]]);
