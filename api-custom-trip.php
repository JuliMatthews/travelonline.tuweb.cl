<?php
// Solicitud de "Viaje a medida" (/viajes-a-medida/) → CRM del panel.
// Se guarda en quote_requests como request_type = 'a_medida' (ver
// admin/db/migration-crm.sql), con folio WEB-AAMMDD-NNNN, y se envían:
//   - al cliente: "recibimos tu solicitud" + folio + detalle completo
//   - al equipo (QUOTE_NOTIFICATION_EMAILS): aviso con enlace al panel.
// Todo se valida acá; el JS del formulario solo ayuda.
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/json.php';
require_once __DIR__ . '/inc/client_auth.php';
require_once __DIR__ . '/inc/i18n.php';

install_json_error_handlers();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Método no permitido', 405);

$body = json_body();

// Anti-spam: campo trampa invisible. Si viene lleno, respondemos "ok" sin guardar.
if (str_field($body, 'web') !== '') json_ok(['folio' => 'WEB-000000-0000']);

// Opciones válidas (mismo orden que el formulario). Se guardan en español,
// que es el idioma del equipo que las lee en el panel.
const WHEN = [1 => 'En los próximos 3 meses', 2 => 'Entre 3 y 6 meses', 3 => 'En más de 6 meses', 4 => 'Aún no lo sé', 5 => 'Fecha exacta'];
const TYPES = [1 => 'Vacaciones', 2 => 'Luna de miel', 3 => 'Familiar', 4 => 'Grupo', 5 => 'Aventura', 6 => 'Crucero', 7 => 'Negocios', 8 => 'Otro'];
const INCLUDES = [1 => 'Vuelo', 2 => 'Hotel', 3 => 'Traslados', 4 => 'Excursiones', 5 => 'Seguro de viaje', 6 => 'Crucero', 7 => 'Arriendo de auto'];
// Presupuesto por persona: etiqueta + valor medio (para el "ingreso potencial" del CRM).
const BUDGETS = [
    1 => ['Hasta $800.000', 700000],
    2 => ['$800.000 – $1.500.000', 1150000],
    3 => ['$1.500.000 – $2.500.000', 2000000],
    4 => ['$2.500.000 – $4.000.000', 3200000],
    5 => ['Más de $4.000.000', 4800000],
    6 => ['Aún no lo sé', null],
];

$errors = [];
$dest = mb_substr(str_field($body, 'destino'), 0, 160);
if (mb_strlen($dest) < 2) $errors['destino'] = 'Destino requerido';
$origin = mb_substr(str_field($body, 'origen'), 0, 120) ?: null;
$when = (int) ($body['cuando'] ?? 1);
if (!isset(WHEN[$when])) $when = 4;
$dateFrom = $dateTo = null;
if ($when === 5) {
    $f = str_field($body, 'ida');
    $t = str_field($body, 'vuelta');
    $dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) ? $f : null;
    $dateTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', $t) ? $t : null;
}
$adults = max(1, min(20, (int) ($body['adultos'] ?? 1)));
$children = max(0, min(10, (int) ($body['ninos'] ?? 0)));
$ages = array_slice(array_map(fn($a) => max(0, min(17, (int) $a)), array_filter((array) ($body['edades'] ?? []), fn($a) => $a !== '' && $a !== null)), 0, $children);
$type = (int) ($body['tipo'] ?? 0);
$typeLabel = TYPES[$type] ?? null;
$includes = array_values(array_intersect_key(INCLUDES, array_flip(array_map('intval', (array) ($body['incluir'] ?? [])))));
$budgetKey = (int) ($body['presupuesto'] ?? 0);
[$budgetLabel, $budgetPp] = BUDGETS[$budgetKey] ?? [null, null];
$comments = mb_substr(str_field($body, 'comentarios'), 0, 1200);

$name = mb_substr(str_field($body, 'nombre'), 0, 120);
if (!preg_match('/\S+\s+\S+/u', $name)) $errors['nombre'] = 'Nombre y apellido requeridos';
$email = strtolower(mb_substr(str_field($body, 'correo'), 0, 160));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['correo'] = 'Correo inválido';
$cc = preg_replace('/[^0-9+]/', '', str_field($body, 'codigo')) ?: '+56';
$phoneRaw = str_field($body, 'whatsapp');
if (strlen(preg_replace('/\D/', '', $phoneRaw)) < 8) $errors['whatsapp'] = 'Teléfono inválido';
$phone = mb_substr($cc . ' ' . $phoneRaw, 0, 60);
if (empty($body['consentimiento'])) $errors['consentimiento'] = 'Falta el consentimiento';

if ($errors) json_response(['ok' => false, 'error' => 'Datos inválidos', 'fields' => $errors], 400);

$mysqli = db();

// Ligar al cliente: sesión activa, o cuenta existente con ese correo.
$clientId = null;
$clientSession = current_client();
if ($clientSession) {
    $clientId = $clientSession['client']['id'];
} else {
    $stmt = $mysqli->prepare('SELECT id FROM clients WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $clientId = $stmt->get_result()->fetch_assoc()['id'] ?? null;
    $stmt->close();
}

$details = [
    'cuando' => WHEN[$when],
    'incluir' => $includes,
    'edades' => $ages ? implode(', ', $ages) : null,
    'idioma' => current_locale(),
    'origen_web' => '/viajes-a-medida/',
];
$detailsJson = json_encode($details, JSON_UNESCAPED_UNICODE);
$budgetTotal = $budgetPp !== null ? $budgetPp * ($adults + $children) : null;
$commentsOrNull = $comments !== '' ? $comments : null;

$stmt = $mysqli->prepare(
    "INSERT INTO quote_requests (request_type, channel, destination_text, origin_city, travel_type, budget_range, budget_clp,
        client_id, adults, children, selected_addons_json, preferred_date_from, preferred_date_to,
        passenger_name, passenger_email, passenger_phone, comments, details_json, last_contact_at)
     VALUES ('a_medida', 'web', ?, ?, ?, ?, ?, ?, ?, ?, '[]', ?, ?, ?, ?, ?, ?, ?, NOW())"
);
$stmt->bind_param(
    'ssssisiisssssss',
    $dest, $origin, $typeLabel, $budgetLabel, $budgetTotal, $clientId, $adults, $children,
    $dateFrom, $dateTo, $name, $email, $phone, $commentsOrNull, $detailsJson
);
$stmt->execute();
$id = $stmt->insert_id;
$stmt->close();

$folio = sprintf('WEB-%s-%04d', (new DateTime('now', new DateTimeZone('America/Santiago')))->format('ymd'), $id % 10000);
$stmt = $mysqli->prepare('UPDATE quote_requests SET folio = ? WHERE id = ?');
$stmt->bind_param('si', $folio, $id);
$stmt->execute();
$stmt->close();
$log = 'Solicitud de viaje a medida recibida por el sitio web';
$stmt = $mysqli->prepare("INSERT INTO quote_activity (quote_id, kind, body) VALUES (?, 'sistema', ?)");
$stmt->bind_param('is', $id, $log);
$stmt->execute();
$stmt->close();

// ── Correos (best-effort: si mail() falla, la solicitud ya quedó guardada) ──
$pax = $adults . ' adulto' . ($adults === 1 ? '' : 's') . ($children ? " + $children niño" . ($children === 1 ? '' : 's') . ($ages ? ' (' . implode(', ', $ages) . ' años)' : '') : '');
$detailLines = [
    'Destino: ' . $dest,
    'Sale desde: ' . ($origin ?? '—'),
    'Cuándo: ' . ($when === 5 ? (($dateFrom ?? '¿?') . ' → ' . ($dateTo ?? '¿?')) : WHEN[$when]),
    'Viajeros: ' . $pax,
    'Tipo de viaje: ' . ($typeLabel ?? 'Sin indicar'),
    'Incluir: ' . ($includes ? implode(', ', $includes) : 'Sin indicar'),
    'Presupuesto por persona: ' . ($budgetLabel ?? 'Sin indicar'),
    'Comentarios: ' . ($comments !== '' ? $comments : '—'),
    '',
    'Nombre: ' . $name,
    'Correo: ' . $email,
    'WhatsApp: ' . $phone,
];
$from = "From: Travel Online <noreply@travelonline.tuweb.cl>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$subjectEnc = fn(string $s) => '=?UTF-8?B?' . base64_encode($s) . '?=';

// Al cliente (en su idioma para el saludo; el detalle va tal cual lo pidió).
$clientBody = implode("\r\n", array_merge([
    sprintf(t('vam.th_title'), explode(' ', $name)[0]),
    '',
    t('vam.th_folio') . ': ' . $folio,
    t('vam.th_soon'),
    '',
    t('vam.th_detail') . ':',
], $detailLines, [
    '',
    'WhatsApp: +56 9 8199 1292 · reservas@travelonline.cl',
    'Travel Online SPA · Galvarino Gallardo 1941, Providencia',
]));
@mail($email, $subjectEnc(t('vam.th_eyebrow') . ' · ' . $folio . ' · Travel Online'), $clientBody,
    $from . "Reply-To: Travel Online <reservas@travelonline.cl>\r\n");

// Al equipo.
$recipients = array_filter(array_map('trim', explode(',', QUOTE_NOTIFICATION_EMAILS)));
$teamBody = implode("\r\n", array_merge([
    'Llegó una nueva solicitud de VIAJE A MEDIDA desde el sitio web.',
    'Folio: ' . $folio,
    '',
], $detailLines, [
    '',
    'Ver en el CRM: ' . rtrim(ADMIN_PUBLIC_URL, '/') . '/solicitudes/' . $id,
]));
foreach ($recipients as $to) {
    @mail($to, $subjectEnc("Viaje a medida — $dest · $folio"), $teamBody,
        $from . 'Reply-To: ' . $name . ' <' . $email . ">\r\n");
}

json_ok(['folio' => $folio, 'id' => $id, 'linkedToAccount' => $clientId !== null]);
