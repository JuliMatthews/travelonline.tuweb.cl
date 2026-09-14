<?php
// Recibe el POST de /cotizar y SIEMPRE recalcula el total en el servidor con
// inc/pricing.php — nunca confía en un total que mande el navegador (mismo
// principio ya probado en el stack Node). Endpoint público, sin auth.
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/pricing.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

set_exception_handler(function ($e) {
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

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) bad_request('Cuerpo inválido');

$packageSlug = trim((string) ($body['packageSlug'] ?? ''));
if ($packageSlug === '') bad_request('Falta el paquete');

$package = get_package_by_slug($packageSlug);
if (!$package) bad_request('Paquete no encontrado');

$adults = max(0, (int) ($body['adults'] ?? 0));
$children = max(0, (int) ($body['children'] ?? 0));
if ($adults + $children < 1) bad_request('Debes indicar al menos 1 pasajero');

$passengerName = trim((string) ($body['passengerName'] ?? ''));
$passengerEmail = trim((string) ($body['passengerEmail'] ?? ''));
$passengerPhone = trim((string) ($body['passengerPhone'] ?? ''));
if ($passengerName === '') bad_request('Falta el nombre');
if (!filter_var($passengerEmail, FILTER_VALIDATE_EMAIL)) bad_request('Correo inválido');
if ($passengerPhone === '') bad_request('Falta el teléfono');

$comments = trim((string) ($body['comments'] ?? ''));
$dateFrom = !empty($body['dateFrom']) ? (string) $body['dateFrom'] : null;
$dateTo = !empty($body['dateTo']) ? (string) $body['dateTo'] : null;

$roomOptionId = !empty($body['roomOptionId']) ? (string) $body['roomOptionId'] : null;
$selectedAddonIds = array_values(array_filter((array) ($body['selectedAddonIds'] ?? []), 'is_string'));

// El servidor recalcula todo — el navegador solo elige IDs, nunca precios.
$quote = calculate_quote([
    'basePriceClp' => $package['priceFromClp'],
    'priceUnit' => $package['priceUnit'],
    'adults' => $adults,
    'children' => $children,
    'selectedAddonIds' => $selectedAddonIds,
    'addons' => $package['addons'],
    'roomOptionId' => $roomOptionId,
    'roomOptions' => $package['roomOptions'],
]);

$roomOptionLabel = null;
if ($roomOptionId) {
    foreach ($package['roomOptions'] as $r) {
        if ($r['id'] === $roomOptionId) { $roomOptionLabel = $r['label']; break; }
    }
    if ($roomOptionLabel === null) $roomOptionId = null;
}

$selectedAddonsJson = json_encode(array_values($quote['selectedAddons']), JSON_UNESCAPED_UNICODE);

$perPersonBaseClp = $quote['perPersonBase'] !== null ? (int) round($quote['perPersonBase']) : null;
$passengersSubtotalClp = $quote['passengersSubtotal'] !== null ? (int) round($quote['passengersSubtotal']) : null;
$addonsTotalClp = (int) $quote['addonsTotal'];
$roomAdjustmentClp = (int) $quote['roomAdjustment'];
$totalClp = $quote['total'] !== null ? (int) round($quote['total']) : null;
$depositSuggestedClp = $quote['depositSuggested'];

$mysqli = db();
$stmt = $mysqli->prepare(
    'INSERT INTO quote_requests (
        package_id, package_slug, package_title, adults, children,
        room_option_id, room_option_label, selected_addons_json,
        per_person_base_clp, passengers_subtotal_clp, addons_total_clp, room_adjustment_clp,
        total_clp, deposit_suggested_clp,
        preferred_date_from, preferred_date_to,
        passenger_name, passenger_email, passenger_phone, comments
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'sssiisssiiiiiissssss',
    $package['id'], $package['slug'], $package['title'], $adults, $children,
    $roomOptionId, $roomOptionLabel, $selectedAddonsJson,
    $perPersonBaseClp, $passengersSubtotalClp, $addonsTotalClp, $roomAdjustmentClp,
    $totalClp, $depositSuggestedClp,
    $dateFrom, $dateTo,
    $passengerName, $passengerEmail, $passengerPhone, $comments
);
$stmt->execute();
$id = $stmt->insert_id;
$stmt->close();

echo json_encode(['ok' => true, 'id' => $id]);
