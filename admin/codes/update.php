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

$body      = json_input();
$id        = (int)($body['id'] ?? 0);
$role      = array_key_exists('role', $body)      ? trim((string)$body['role'])      : null;
$label     = array_key_exists('label', $body)     ? trim((string)$body['label'])     : null;
$is_active = array_key_exists('is_active', $body) ? (int)$body['is_active']          : null;
$expires   = array_key_exists('expires_at', $body) ? trim((string)$body['expires_at']) : null;

if ($id <= 0) {
    json_error('Valid id is required', 400);
}

if ($role !== null && !in_array($role, ['user', 'admin'], true)) {
    json_error('Role must be "user" or "admin"', 400);
}
if ($label !== null && strlen($label) > 120) {
    json_error('Label must be 120 characters or fewer', 400);
}
if ($expires !== null && $expires !== '') {
    if (strtotime($expires) === false) {
        json_error('Invalid expiry date', 400);
    }
}

try {
    $check = $pdo->prepare("SELECT id, code, role FROM access_code WHERE id = ?");
    $check->execute([$id]);
    $existing = $check->fetch();

    if (!$existing) {
        json_error('Code not found', 404);
    }

    // Safety: can't demote the last admin
    if ($role === 'user' && $existing['role'] === 'admin') {
        $adminCount = $pdo->query("SELECT COUNT(*) FROM access_code WHERE role = 'admin' AND is_active = 1")->fetchColumn();
        if ((int)$adminCount <= 1) {
            json_error('Cannot demote the last admin code', 409);
        }
    }

    // Safety: can't disable the last admin
    if ($is_active === 0 && $existing['role'] === 'admin') {
        $adminCount = $pdo->query("SELECT COUNT(*) FROM access_code WHERE role = 'admin' AND is_active = 1")->fetchColumn();
        if ((int)$adminCount <= 1) {
            json_error('Cannot disable the last admin code', 409);
        }
    }

    // Build dynamic UPDATE
    $updates = [];
    $values  = [];

    if ($role !== null) {
        $updates[] = 'role = ?';
        $values[]  = $role;
    }
    if ($label !== null) {
        $updates[] = 'label = ?';
        $values[]  = $label ?: null;
    }
    if ($is_active !== null) {
        $updates[] = 'is_active = ?';
        $values[]  = $is_active ? 1 : 0;
    }
    if ($expires !== null) {
        $updates[] = 'expires_at = ?';
        $values[]  = $expires === '' ? null : date('Y-m-d H:i:s', strtotime($expires));
    }

    if (empty($updates)) {
        json_error('No fields to update', 400);
    }

    $values[] = $id;
    $sql = "UPDATE access_code SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    // Return updated row
    $fetch = $pdo->prepare("
        SELECT id, code, label, role, is_active, expires_at
        FROM access_code WHERE id = ?
    ");
    $fetch->execute([$id]);

    json_response([
        'success' => true,
        'code'    => $fetch->fetch(),
    ]);
} catch (PDOException $e) {
    error_log('[codes/update] ' . $e->getMessage());
    json_error('Database error', 500);
}