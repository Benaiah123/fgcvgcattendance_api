<?php
// public_html/api/admin/codes/generate.php
require __DIR__ . '/../../lib/cors.php';
require __DIR__ . '/../../lib/json.php';
require __DIR__ . '/../../lib/auth.php';
require __DIR__ . '/../../db.php';

$user = require_auth();

if (($user['role'] ?? 'user') !== 'admin') {
    json_error('Admin access required', 403);
}

// Alphabet excludes I, O, 0, 1 — avoids confusion when typing
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

$chars = '';
for ($i = 0; $i < 12; $i++) {
    $chars .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}

// Group into 4-char chunks: "K9M2-P7Q4-X3R8"
$body = implode('-', str_split($chars, 4));
$code = 'FGC-' . $body;

json_response([
    'code' => $code,
]);