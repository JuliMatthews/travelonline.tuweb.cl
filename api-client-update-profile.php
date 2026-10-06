<?php
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$session = current_client();
if (!$session) json_error('No autenticado', 401);

$body = json_body();
$name = str_field($body, 'name');
$phone = str_field($body, 'phone') ?: null;

if ($name === '') json_error('Falta el nombre');

$mysqli = db();
$stmt = $mysqli->prepare('UPDATE clients SET name = ?, phone = ? WHERE id = ?');
$stmt->bind_param('sss', $name, $phone, $session['client']['id']);
$stmt->execute();
$stmt->close();

json_ok();
