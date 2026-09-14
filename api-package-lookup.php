<?php
// JSON para el JS de /cotizar: trae el detalle fresco de un paquete
// (precio, addons, habitaciones) cuando la persona cambia el selector.
require_once __DIR__ . '/inc/content.php';

header('Content-Type: application/json; charset=utf-8');

$slug = $_GET['slug'] ?? '';
$package = $slug !== '' ? get_package_by_slug($slug) : null;

if (!$package) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Paquete no encontrado']);
    exit;
}

echo json_encode(['ok' => true, 'package' => $package], JSON_UNESCAPED_UNICODE);
