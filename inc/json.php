<?php
// Respuesta JSON uniforme {"ok": bool, ...} — mismo patrón ya probado en
// admin/inc/json.php. Captura global de errores para que un warning/notice
// de PHP nunca rompa el JSON.parse() del frontend.

function json_response($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $msg, int $code = 400): void {
    json_response(['ok' => false, 'error' => $msg], $code);
}

function json_ok(array $data = []): void {
    json_response(array_merge(['ok' => true], $data), 200);
}

function install_json_error_handlers(): void {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');

    set_error_handler(function ($severity, $message, $file, $line) {
        error_log("[PHP_WARNING] $message in $file:$line");
        if ($severity === E_ERROR || $severity === E_USER_ERROR) {
            json_error('Error interno del servidor', 500);
        }
        return true;
    });

    set_exception_handler(function (Throwable $e) {
        error_log('[PHP_EXCEPTION] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        json_error('Error interno del servidor', 500);
    });

    register_shutdown_function(function () {
        $err = error_get_last();
        if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            error_log('[PHP_FATAL] ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
            if (!headers_sent()) {
                json_error('Error interno del servidor', 500);
            }
        }
    });
}

function json_body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function str_field(array $data, string $key, string $default = ''): string {
    $value = $data[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}
