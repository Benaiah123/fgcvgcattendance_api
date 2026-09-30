<?php
// public_html/api/admin/records/update.php
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
$fields      = $body['fields'] ?? [];

if ($id <= 0) {
    json_error('Valid record id is required', 400);
}

$SERVICE_MAP = require __DIR__ . '/../../lib/serviceMap.php';

if (!isset($SERVICE_MAP[$serviceName])) {
    json_error("Unknown service: $serviceName", 400);
}

$service = $SERVICE_MAP[$serviceName];
$table   = $service['table'];

try {
    // Confirm the row exists
    $check = $pdo->prepare("SELECT id, date FROM `$table` WHERE id = ?");
    $check->execute([$id]);
    $existing = $check->fetch();

    if (!$existing) {
        json_error('Record not found', 404);
    }

    // Build the UPDATE from the fields provided
    $setParts = [];
    $values   = [];

    foreach ($service['fields'] as $key => $def) {
        if (!array_key_exists($key, $fields)) continue;

        $raw = $fields[$key];
        if ($def['type'] === 'int') {
            $value = is_numeric($raw) ? (int)$raw : null;
        } else {
            $value = ($raw === '' || $raw === null) ? null : (string)$raw;
        }

        $setParts[] = "`{$def['column']}` = ?";
        $values[]   = $value;
    }

    if (empty($setParts)) {
        json_error('No fields to update', 400);
    }

    $values[] = $id;

    $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    // Return the updated row
    $fetch = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
    $fetch->execute([$id]);
    $updated = $fetch->fetch();

    json_response([
        'success' => true,
        'record'  => $updated,
    ]);
} catch (PDOException $e) {
    error_log('[records/update] ' . $e->getMessage());
    json_error('Database error', 500);
}