<?php
require_once __DIR__ . '/../config.php';

function db(): mysqli {
    static $mysqli = null;
    if ($mysqli !== null) {
        return $mysqli;
    }
    $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($mysqli->connect_error) {
        error_log('[DB] connect_error: ' . $mysqli->connect_error);
        http_response_code(500);
        echo 'Error de conexión. Intenta de nuevo en unos minutos.';
        exit;
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function new_uuid(): string {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
