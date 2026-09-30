<?php
// public_html/api/auth/me.php
require __DIR__ . '/../lib/cors.php'; 
require __DIR__ . '/../lib/json.php';
require __DIR__ . '/../lib/auth.php';

$u = current_user();
if (!$u) {
    json_error('Unauthorized', 401);
}

json_response([
    'user' => [
        'id'   => $u['id'],
        'code' => $u['code'],
        'role' => $u['role'] ?? 'user',
    ],
]);