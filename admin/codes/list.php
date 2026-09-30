<?php
// public_html/api/admin/codes/list.php
require __DIR__ . '/../../lib/cors.php';
require __DIR__ . '/../../lib/json.php';
require __DIR__ . '/../../lib/auth.php';
require __DIR__ . '/../../db.php';

$user = require_auth();

if (($user['role'] ?? 'user') !== 'admin') {
    json_error('Admin access required', 403);
}

try {
    $stmt = $pdo->query("
        SELECT id, code, role
        FROM access_code
        ORDER BY 
            CASE role WHEN 'admin' THEN 0 ELSE 1 END,
            code ASC
    ");
    $codes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[codes/list] ' . $e->getMessage());
    json_error('Database error', 500);
}

json_response([
    'count' => count($codes),
    'codes' => $codes,
]);