<?php
// public_html/api/auth/signin.php
require __DIR__ . '/../lib/cors.php';
require __DIR__ . '/../lib/json.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

// ── Rate limit by IP ──────────────────────────
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

$maxAttempts = 5;    // failed attempts allowed
$windowMin   = 15;   // window (minutes) to count attempts
$lockoutMin  = 30;   // how long to block after max reached

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM login_attempts
    WHERE ip_address = ?
      AND attempted_at > (NOW() - INTERVAL ? MINUTE)
");
$stmt->execute([$ip, $windowMin]);
$recentFails = (int)$stmt->fetchColumn();

if ($recentFails >= $maxAttempts) {
    json_error(
        "Too many failed attempts. Please try again in $lockoutMin minutes.",
        429
    );
}

// ── Read input ─────────────────────────────────
$body = json_input();
$code = trim((string)($body['code'] ?? ''));

if ($code === '') {
    json_error('Access code is required', 400);
}
if (strlen($code) > 64) {
    json_error('Invalid access code', 400);
}

// ── Look up the code ───────────────────────────
$stmt = $pdo->prepare("
    SELECT id, code, label, role, is_active, expires_at
    FROM access_code
    WHERE code = ?
    LIMIT 1
");
$stmt->execute([$code]);
$row = $stmt->fetch();

if (!$row) {
    $pdo->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)")
        ->execute([$ip]);
    json_error('Invalid access code', 401);
}

// Is the code active?
if ((int)$row['is_active'] !== 1) {
    json_error('This access code has been disabled', 403);
}

// Has the code expired?
if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
    $pdo->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)")
        ->execute([$ip]);
    json_error('This access code has expired', 403);
}

// ── Success ────────────────────────────────────
$pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")
    ->execute([$ip]);

session_login($row);

json_response([
    'success' => true,
    'user' => [
        'id'    => (int)$row['id'],
        'code'  => $row['code'],
        'label' => $row['label'],
        'role'  => $row['role'] ?? 'user',
    ],
]);