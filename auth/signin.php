<?php
// public_html/api/auth/signin.php
require __DIR__ . '/../lib/cors.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/../lib/json.php';
require __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$body = json_input();
$code = trim((string) ($body['code'] ?? ''));

if ($code === '') {
    json_error('Access code is required', 400);
}
if (strlen($code) > 64) {
    json_error('Invalid access code', 400);
}

$stmt = $pdo->prepare("
    SELECT id, code, role
    FROM access_code
    WHERE code = ?
    LIMIT 1
");
$stmt->execute([$code]);
$row = $stmt->fetch();

if (!$row) {
    json_error('Invalid access code', 401);
}

session_login($row);

json_response([
    'success' => true,
    'user' => [
        'id'   => (int) $row['id'],
        'code' => $row['code'],
        'role' => $row['role'] ?? 'user',
    ],
]);