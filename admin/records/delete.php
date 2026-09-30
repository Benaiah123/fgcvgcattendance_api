<?php
// public_html/api/admin/records/delete.php
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

$body        = json_input();
$serviceName = (string)($body['serviceName'] ?? '');
$id          = (int)($body['id'] ?? 0);

if ($id <= 0) {
    json_error('Valid record id is required', 400);
}

$SERVICE_MAP = require __DIR__ . '/../../lib/serviceMap.php';

if (!isset($SERVICE_MAP[$serviceName])) {
    json_error("Unknown service: $serviceName", 400);
}

$table = $SERVICE_MAP[$serviceName]['table'];

try {
    // Confirm the row exists
    $check = $pdo->prepare("SELECT id, date FROM `$table` WHERE id = ?");
    $check->execute([$id]);
    $existing = $check->fetch();

    if (!$existing) {
        json_error('Record not found', 404);
    }

    // Delete
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);

    json_response([
        'success' => true,
        'deleted' => [
            'id'    => $id,
            'date'  => $existing['date'],
            'table' => $table,
        ],
    ]);
} catch (PDOException $e) {
    error_log('[records/delete] ' . $e->getMessage());
    json_error('Database error', 500);
}