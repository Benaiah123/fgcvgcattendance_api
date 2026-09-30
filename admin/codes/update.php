<?php
// public_html/api/admin/codes/update.php
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
$id   = (int)($body['id'] ?? 0);
$role = trim((string)($body['role'] ?? ''));

if ($id <= 0) {
    json_error('Valid id is required', 400);
}
if (!in_array($role, ['user', 'admin'], true)) {
    json_error('Role must be "user" or "admin"', 400);
}

try {
    // Check the code exists
    $check = $pdo->prepare("SELECT id, code, role FROM access_code WHERE id = ?");
    $check->execute([$id]);
    $existing = $check->fetch();

    if (!$existing) {
        json_error('Code not found', 404);
    }

    // Safety: if demoting an admin to user, make sure it's not the last admin
    if ($existing['role'] === 'admin' && $role === 'user') {
        $adminCount = $pdo->query("SELECT COUNT(*) FROM access_code WHERE role = 'admin'")->fetchColumn();
        if ((int)$adminCount <= 1) {
            json_error('Cannot demote the last admin code', 409);
        }
    }

    // Update
    $stmt = $pdo->prepare("UPDATE access_code SET role = ? WHERE id = ?");
    $stmt->execute([$role, $id]);

    json_response([
        'success' => true,
        'code'    => [
            'id'   => $id,
            'code' => $existing['code'],
            'role' => $role,
        ],
    ]);
} catch (PDOException $e) {
    error_log('[codes/update] ' . $e->getMessage());
    json_error('Database error', 500);
}