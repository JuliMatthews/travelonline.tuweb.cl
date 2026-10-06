<?php
// Auth de CLIENTES (visitantes del sitio público) — sistema separado del
// login de staff en admin/ (tabla/cookie propias: client_sessions /
// to_client_session). Ningún token de uno sirve para el otro. Mismo patrón
// ya probado (token opaco = la cookie, revocación borrando la fila).
require_once __DIR__ . '/db.php';

const CLIENT_SESSION_COOKIE = 'to_client_session';
const CLIENT_SESSION_DAYS = 30;
const CLIENT_UUID_RE = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

function create_client_session(string $clientId, ?string $userAgent, ?string $ip): array {
    $mysqli = db();
    $id = new_uuid();
    $expiresAt = date('Y-m-d H:i:s', time() + CLIENT_SESSION_DAYS * 86400);
    $stmt = $mysqli->prepare(
        'INSERT INTO client_sessions (id, client_id, expires_at, user_agent, ip_address) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssss', $id, $clientId, $expiresAt, $userAgent, $ip);
    $stmt->execute();
    $stmt->close();

    $mysqli->query("UPDATE clients SET last_login_at = NOW() WHERE id = '" . $mysqli->real_escape_string($clientId) . "'");

    return ['id' => $id, 'expiresAt' => $expiresAt];
}

function destroy_client_session(string $sessionId): void {
    $mysqli = db();
    $stmt = $mysqli->prepare('DELETE FROM client_sessions WHERE id = ?');
    $stmt->bind_param('s', $sessionId);
    $stmt->execute();
    $stmt->close();
}

// Gate autoritativo — siempre consulta la base, nunca confía solo en que la
// cookie exista. Cacheado por request.
function current_client(): ?array {
    static $cached = false;
    static $result = null;
    if ($cached) return $result;
    $cached = true;

    $sessionId = $_COOKIE[CLIENT_SESSION_COOKIE] ?? null;
    if (!$sessionId || !preg_match(CLIENT_UUID_RE, $sessionId)) {
        return $result;
    }

    $mysqli = db();
    $stmt = $mysqli->prepare(
        'SELECT c.id, c.email, c.name, c.phone, c.email_verified_at
         FROM client_sessions s JOIN clients c ON c.id = s.client_id
         WHERE s.id = ? AND s.expires_at > NOW()'
    );
    $stmt->bind_param('s', $sessionId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $result = ['sessionId' => $sessionId, 'client' => $row];
    }
    return $result;
}

function set_client_session_cookie(string $sessionId, string $expiresAt): void {
    setcookie(CLIENT_SESSION_COOKIE, $sessionId, [
        'expires' => strtotime($expiresAt),
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
}

function clear_client_session_cookie(): void {
    setcookie(CLIENT_SESSION_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
    ]);
}
