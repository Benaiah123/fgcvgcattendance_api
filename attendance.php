<?php
// public_html/api/attendance.php
require __DIR__ . '/lib/cors.php'; 
require __DIR__ . '/db.php';
require __DIR__ . '/lib/json.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/date.php';

require_auth();                                  // must be signed in

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$body        = json_input();
$serviceName = (string)($body['serviceName'] ?? '');
$dateRaw     = (string)($body['date'] ?? '');
$fields      = $body['fields'] ?? [];

$SERVICE_MAP = require __DIR__ . '/lib/serviceMap.php';

if (!isset($SERVICE_MAP[$serviceName])) {
    json_error("Unknown service: $serviceName", 400);
}

$isoDate = to_mysql_date($dateRaw);
if (!$isoDate) {
    json_error("Invalid date: $dateRaw", 400);
}

$service = $SERVICE_MAP[$serviceName];
$columns = ['date'];
$values  = [$isoDate];

foreach ($service['fields'] as $key => $def) {
    if (!array_key_exists($key, $fields)) continue;

    $raw = $fields[$key];

    if ($def['type'] === 'int') {
        $value = is_numeric($raw) ? (int)$raw : null;
    } else {
        $value = ($raw === '' || $raw === null) ? null : (string)$raw;
    }

    $columns[] = $def['column'];
    $values[]  = $value;
}

if (count($columns) === 1) {
    json_error('No valid fields were provided', 400);
}

$colList      = implode(', ', array_map(fn($c) => "`$c`", $columns));
$placeholders = implode(', ', array_fill(0, count($columns), '?'));

$updates = [];
foreach ($columns as $c) {
    if ($c === 'date') continue;
    $updates[] = "`$c` = VALUES(`$c`)";
}
$updateSql = implode(', ', $updates);

$sql = "INSERT INTO `{$service['table']}` ($colList)
        VALUES ($placeholders)
        ON DUPLICATE KEY UPDATE $updateSql";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    json_response([
        'message'      => 'Attendance record saved.',
        'table'        => $service['table'],
        'date'         => $isoDate,
        'affectedRows' => $stmt->rowCount(),
    ]);
} catch (PDOException $e) {
    error_log('[attendance] ' . $e->getMessage());
    json_error('Database error while saving attendance', 500);
}