<?php
// public_html/api/admin/codes/delete.php
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

if ($id <= 0) {
    json_error('Valid id is required', 400);
}

try {
    // Get the code
    $check = $pdo->prepare("SELECT id, code, role FROM access_code WHERE id = ?");
    $check->execute([$id]);
    $existing = $check->fetch();

    if (!$existing) {
        json_error('Code not found', 404);
    }

    // Safety 1: can't delete your own code
    if ((int)$existing['id'] === (int)$user['id']) {
        json_error('You cannot delete your own access code', 409);
    }

    // Safety 2: can't delete the last admin
    if ($existing['role'] === 'admin') {
        $adminCount = $pdo->query("SELECT COUNT(*) FROM access_code WHERE role = 'admin'")->fetchColumn();
        if ((int)$adminCount <= 1) {
            json_error('Cannot delete the last admin code', 409);
        }
    }

    // Delete
    $stmt = $pdo->prepare("DELETE FROM access_code WHERE id = ?");
    $stmt->execute([$id]);

    json_response([
        'success' => true,
        'deleted' => [
            'id'   => $id,
            'code' => $existing['code'],
        ],
    ]);
} catch (PDOException $e) {
    error_log('[codes/delete] ' . $e->getMessage());
    json_error('Database error', 500);
}