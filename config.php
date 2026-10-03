<?php
// public_html/api/config.php

// Detect environment by looking at the server name
$serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($serverName, ['localhost', '127.0.0.1', '::1'], true)
        || strpos($serverName, 'localhost:') === 0;

if ($isLocal) {
    // ── LOCAL (XAMPP) ────────────────────────
    $dbHost = '127.0.0.1';
    $dbUser = 'root';
    $dbPass = '';
} else {
    // ── PRODUCTION (cPanel) ──────────────────
    $dbHost = 'localhost';
    $dbUser = 'appotg_foursquarevgcattendance';
    $dbPass = 'B6C18xirG+';
}

return [
    'db' => [
        'host'    => $dbHost,
        'name'    => 'appotg_foursquarevgcattendance',
        'user'    => $dbUser,
        'pass'    => $dbPass,
        'charset' => 'utf8mb4',
    ],

    'app_secret'      => 'long_random_string_at_least_32_chars_here',
    'session_ttl'     => 60 * 60,
    'cookie_name'     => 'fgc_session',
    'cookie_secure'   => false,           // false on both (both HTTP)
    'cookie_samesite' => 'Lax',
];