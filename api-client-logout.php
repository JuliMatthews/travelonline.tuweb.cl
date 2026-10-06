<?php
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$session = current_client();
if ($session) destroy_client_session($session['sessionId']);
clear_client_session_cookie();

json_ok();
