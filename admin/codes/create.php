<?php
// public_html/api/admin/codes/create.php
require __DIR__ . '/../../lib/cors.php';
require __DIR__ . '/../../lib/json.php';
require __DIR__ . '/../../lib/auth.php';
require __DIR__ . '/../../db.php';

$user = require_auth();

if (($user['role'] ?? 'user') !== 'admin') {
    json_error('Admin access required', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$body = json_input();
$code = trim((string)($body['code'] ?? ''));
$role = trim((string)($body['role'] ?? 'user'));

// Validate code
if ($code === '') {
    json_error('Code is required', 400);
}
if (strlen($code) < 4) {
    json_error('Code must be at least 4 characters', 400);
}
if (strlen($code) > 64) {
    json_error('Code must be 64 characters or fewer', 400);
}
if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
    json_error('Code may only contain letters, numbers, hyphens, and underscores', 400);
}

// Validate role
if (!in_array($role, ['user', 'admin'], true)) {
    json_error('Role must be "user" or "admin"', 400);
}

try {
    // Check if code already exists
    $check = $pdo->prepare("SELECT id FROM access_code WHERE code = ?");
    $check->execute([$code]);
    if ($check->fetch()) {
        json_error('That code already exists', 409);
    }

    // Insert
    $stmt = $pdo->prepare("
        INSERT INTO access_code (code, role)
        VALUES (?, ?)
    ");
    $stmt->execute([$code, $role]);

    $newId = (int)$pdo->lastInsertId();

    json_response([
        'success' => true,
        'code'    => [
            'id'   => $newId,
            'code' => $code,
            'role' => $role,
        ],
    ], 201);
} catch (PDOException $e) {
    error_log('[codes/create] ' . $e->getMessage());
    json_error('Database error', 500);
}