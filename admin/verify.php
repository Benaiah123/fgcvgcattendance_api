<?php
// public_html/api/admin/verify.php
require __DIR__ . '/../lib/cors.php';
require __DIR__ . '/../lib/json.php';
require __DIR__ . '/../lib/auth.php';

$user = current_user();

if (!$user) {
    json_error('Unauthorized', 401);
}

json_response([
    'isAdmin' => ($user['role'] ?? 'user') === 'admin',
    'user'    => [
        'id'   => $user['id'],
        'code' => $user['code'],
        'role' => $user['role'] ?? 'user',
    ],
]);