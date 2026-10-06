<?php
// Cotizaciones del cliente autenticado — busca por client_id (quedó
// vinculado al enviar, ver api-quote-submit.php) y también por correo, para
// no perder cotizaciones viejas enviadas antes de tener cuenta.
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/json.php';

install_json_error_handlers();

$session = current_client();
if (!$session) json_error('No autenticado', 401);

$mysqli = db();
$stmt = $mysqli->prepare(
    'SELECT id, created_at, package_title, status, total_clp, deposit_suggested_clp,
            preferred_date_from, preferred_date_to
     FROM quote_requests
     WHERE client_id = ? OR passenger_email = ?
     ORDER BY created_at DESC'
);
$stmt->bind_param('ss', $session['client']['id'], $session['client']['email']);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = [
        'id' => (int) $r['id'],
        'createdAt' => $r['created_at'],
        'packageTitle' => $r['package_title'],
        'status' => $r['status'],
        'totalClp' => $r['total_clp'] !== null ? (int) $r['total_clp'] : null,
        'depositSuggestedClp' => $r['deposit_suggested_clp'] !== null ? (int) $r['deposit_suggested_clp'] : null,
        'preferredDateFrom' => $r['preferred_date_from'],
        'preferredDateTo' => $r['preferred_date_to'],
    ];
}
$stmt->close();

json_ok(['quotes' => $rows]);
