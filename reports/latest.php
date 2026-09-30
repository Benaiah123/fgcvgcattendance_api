<?php
// public_html/api/reports/latest.php
require __DIR__ . '/../lib/cors.php';
require __DIR__ . '/../lib/json.php';      // ← ADD THIS LINE
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../db.php';

require_auth();

$serviceName = $_GET['service'] ?? '';

$SERVICE_MAP = require __DIR__ . '/../lib/serviceMap.php';

if (!isset($SERVICE_MAP[$serviceName])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unknown service']);
    exit;
}

$table = $SERVICE_MAP[$serviceName]['table'];

try {
    $stmt = $pdo->query("SELECT MAX(date) AS latest, COUNT(*) AS total FROM `$table`");
    $row = $stmt->fetch();
    json_response([
        'latest' => $row['latest'],
        'total'  => (int)$row['total'],
    ]);
} catch (PDOException $e) {
    error_log('[latest] ' . $e->getMessage());
    json_error('Database error', 500);
}