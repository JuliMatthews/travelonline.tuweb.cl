<?php
// Recibe el POST de /cotizar y SIEMPRE recalcula el total en el servidor con
// inc/pricing.php — nunca confía en un total que mande el navegador (mismo
// principio ya probado en el stack Node). Endpoint público, sin auth.
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/pricing.php';
require_once __DIR__ . '/inc/client_auth.php';

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

// Avisa al equipo por correo que llegó una cotización nueva — best-effort:
// si el envío falla (o mail() no está disponible), la cotización ya quedó
// guardada en la base igual, así que nunca debe romper la respuesta al
// visitante. Reply-To apunta al pasajero para poder responderle directo.
function notify_new_quote(array $package, array $quote, int $id, array $passenger, ?string $comments): void {
    $recipients = array_filter(array_map('trim', explode(',', QUOTE_NOTIFICATION_EMAILS)));
    if (count($recipients) === 0) return;

    $totalLine = $quote['total'] !== null
        ? '$' . number_format($quote['total'], 0, ',', '.') . ' CLP'
        : 'Bajo consulta';
    $depositLine = $quote['depositSuggested'] !== null
        ? '$' . number_format($quote['depositSuggested'], 0, ',', '.') . ' CLP'
        : '—';

    $lines = [
        'Llegó una nueva solicitud de cotización desde travelonline.tuweb.cl.',
        '',
        'Paquete: ' . $package['title'],
        'Pasajeros: ' . $quote['passengers'] . ' (' . $passenger['adults'] . ' adultos, ' . $passenger['children'] . ' niños)',
        'Habitación: ' . ($passenger['roomOptionLabel'] ?? '—'),
        'Fechas preferidas: ' . ($passenger['dateFrom'] ? $passenger['dateFrom'] . ' al ' . $passenger['dateTo'] : 'sin especificar'),
        'Total estimado: ' . $totalLine,
        'Abono sugerido (30%): ' . $depositLine,
        '',
        'Nombre: ' . $passenger['name'],
        'Correo: ' . $passenger['email'],
        'Teléfono: ' . $passenger['phone'],
    ];
    if ($comments !== '') $lines[] = 'Comentarios: ' . $comments;
    $lines[] = '';
    $lines[] = 'Ver en el panel: ' . rtrim(ADMIN_PUBLIC_URL, '/') . '/cotizaciones/' . $id;

    $subject = '=?UTF-8?B?' . base64_encode('Nueva cotización — ' . $package['title']) . '?=';
    $messageBody = implode("\r\n", $lines);
    $headers = "From: Travel Online <noreply@travelonline.tuweb.cl>\r\n"
        . 'Reply-To: ' . $passenger['name'] . ' <' . $passenger['email'] . ">\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    foreach ($recipients as $to) {
        @mail($to, $subject, $messageBody, $headers);
    }
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

// Vincula la cotización a un cliente si existe sesión activa, o si ya hay
// una cuenta con ese correo (aunque no haya iniciado sesión ahora) — así el
// área de clientes la muestra igual más adelante si se registra.
$clientSession = current_client();
if ($clientSession) {
    $clientId = $clientSession['client']['id'];
} else {
    $stmt = db()->prepare('SELECT id FROM clients WHERE email = ?');
    $stmt->bind_param('s', $passengerEmail);
    $stmt->execute();
    $clientRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $clientId = $clientRow['id'] ?? null;
}

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
        package_id, package_slug, package_title, client_id, adults, children,
        room_option_id, room_option_label, selected_addons_json,
        per_person_base_clp, passengers_subtotal_clp, addons_total_clp, room_adjustment_clp,
        total_clp, deposit_suggested_clp,
        preferred_date_from, preferred_date_to,
        passenger_name, passenger_email, passenger_phone, comments
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'ssssiisssiiiiiissssss',
    $package['id'], $package['slug'], $package['title'], $clientId, $adults, $children,
    $roomOptionId, $roomOptionLabel, $selectedAddonsJson,
    $perPersonBaseClp, $passengersSubtotalClp, $addonsTotalClp, $roomAdjustmentClp,
    $totalClp, $depositSuggestedClp,
    $dateFrom, $dateTo,
    $passengerName, $passengerEmail, $passengerPhone, $comments
);
$stmt->execute();
$id = $stmt->insert_id;
$stmt->close();

// Datos de seguimiento del CRM (admin/db/migration-crm.sql): folio con el mismo
// formato que se le muestra al pasajero (WEB-AAMMDD-NNNN), canal, destino y
// primer registro del historial.
$folio = sprintf('WEB-%s-%04d', (new DateTime('now', new DateTimeZone('America/Santiago')))->format('ymd'), $id % 10000);
$stmt = $mysqli->prepare(
    "UPDATE quote_requests SET folio = ?, request_type = 'paquete', channel = 'web',
        destination_text = COALESCE(destination_text, package_title), last_contact_at = NOW() WHERE id = ?"
);
$stmt->bind_param('si', $folio, $id);
$stmt->execute();
$stmt->close();
$body = 'Solicitud recibida por el sitio web (cotizador del paquete)';
$stmt = $mysqli->prepare("INSERT INTO quote_activity (quote_id, kind, body) VALUES (?, 'sistema', ?)");
$stmt->bind_param('is', $id, $body);
$stmt->execute();
$stmt->close();

notify_new_quote($package, $quote, $id, [
    'adults' => $adults,
    'children' => $children,
    'roomOptionLabel' => $roomOptionLabel,
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'name' => $passengerName,
    'email' => $passengerEmail,
    'phone' => $passengerPhone,
], $comments);

echo json_encode(['ok' => true, 'id' => $id, 'folio' => $folio]);
