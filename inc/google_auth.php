<?php
// Verificación de tokens de Google Identity Services, compartida entre el
// autocompletar de /cotizar (api-google-verify.php) y el login real de
// /area-clientes (api-client-google.php). SIEMPRE se valida el token contra
// el propio servidor de Google antes de confiar en nombre/correo — nunca se
// decodifica del lado del cliente.
require_once __DIR__ . '/client_auth.php';

// Devuelve los claims verificados (sub/name/email) o null si el token es
// inválido, expiró, o no corresponde a este Client ID.
function verify_google_credential(string $credential): ?array {
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) return null;

    $claims = json_decode($response, true);
    if (!is_array($claims) || isset($claims['error'])) return null;

    $validIssuer = in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
    $validAudience = ($claims['aud'] ?? '') === GOOGLE_CLIENT_ID;
    $emailVerified = ($claims['email_verified'] ?? 'false') === 'true';
    if (!$validIssuer || !$validAudience || !$emailVerified) return null;

    return [
        'sub' => $claims['sub'],
        'email' => $claims['email'],
        'name' => $claims['name'] ?? trim(($claims['given_name'] ?? '') . ' ' . ($claims['family_name'] ?? '')),
    ];
}

// Crea o actualiza el registro de cliente a partir de un login de Google —
// esto es lo que da la trazabilidad pedida: cualquier inicio de sesión con
// Google (se use solo para autocompletar el formulario o para entrar de
// verdad al área de clientes) queda guardado en la base. Si ya existía una
// cuenta con ese correo creada por contraseña, se la vincula a Google y de
// paso se confirma el correo (Google ya lo verificó).
function upsert_client_from_google(array $googleClaims): array {
    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT id FROM clients WHERE google_sub = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $googleClaims['sub'], $googleClaims['email']);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        $stmt = $mysqli->prepare(
            'UPDATE clients SET google_sub = ?, name = IF(name = "" OR name IS NULL, ?, name),
                email_verified_at = COALESCE(email_verified_at, NOW()) WHERE id = ?'
        );
        $stmt->bind_param('sss', $googleClaims['sub'], $googleClaims['name'], $existing['id']);
        $stmt->execute();
        $stmt->close();
        $id = $existing['id'];
    } else {
        $id = new_uuid();
        $stmt = $mysqli->prepare(
            'INSERT INTO clients (id, email, name, google_sub, email_verified_at) VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->bind_param('ssss', $id, $googleClaims['email'], $googleClaims['name'], $googleClaims['sub']);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $mysqli->prepare('SELECT id, email, name, phone, email_verified_at FROM clients WHERE id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $client;
}
