<?php
// public_html/api/lib/cors.php

$allowedOrigins = [
    // Local dev
    'http://localhost:3000',
    'http://127.0.0.1:3000',

    // Production
    'http://fgcvgcattendance.appotg.com',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}