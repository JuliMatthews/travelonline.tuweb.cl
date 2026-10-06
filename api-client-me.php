<?php
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$session = current_client();
if (!$session) {
    json_ok(['authenticated' => false]);
}

json_ok(['authenticated' => true, 'client' => [
    'name' => $session['client']['name'],
    'email' => $session['client']['email'],
    'phone' => $session['client']['phone'],
    'emailVerified' => $session['client']['email_verified_at'] !== null,
]]);
