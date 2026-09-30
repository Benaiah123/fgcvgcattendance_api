<?php
// public_html/api/records/list.php
require __DIR__ . '/../lib/cors.php';
require __DIR__ . '/../lib/json.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../db.php';

require_auth();

$serviceName = $_GET['service'] ?? '';

$SERVICE_MAP = require __DIR__ . '/../lib/serviceMap.php';

if (!isset($SERVICE_MAP[$serviceName])) {
    json_error("Unknown service: $serviceName", 400);
}

$table = $SERVICE_MAP[$serviceName]['table'];

try {
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY date DESC");
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[records/list] ' . $e->getMessage());
    json_error('Database error', 500);
}

json_response([
    'table'   => $table,
    'count'   => count($rows),
    'records' => $rows,
]);