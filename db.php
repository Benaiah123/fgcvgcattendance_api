<?php
// public_html/api/db.php
$cfg = require __DIR__ . '/config.php';
$db  = $cfg['db'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}",
        $db['user'],
        $db['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('[DB] ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}