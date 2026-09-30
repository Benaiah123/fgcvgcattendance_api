<?php
// public_html/api/config.php
// Works in both local (XAMPP) and production (cPanel)

$isLocal = in_array(
    $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '',
    ['localhost', '127.0.0.1', '::1'],
    true
);

return [
    'db' => [
        'host'    => $isLocal ? '127.0.0.1' : 'localhost',
        'name'    => 'appotg_foursquarevgcattendance',
        'user'    => $isLocal ? 'root'                                : 'appotg_foursquarevgcattendance',
        'pass'    => $isLocal ? ''                                    : 'YOUR_REAL_PRODUCTION_PASSWORD',
        'charset' => 'utf8mb4',
    ],

    'app_secret'      => 'a_long_random_string_at_least_32_chars_here',
    'session_ttl'     => 8 * 60 * 60,
    'cookie_name'     => 'fgc_session',
    'cookie_secure'   => !$isLocal,       // false locally, true in prod
    'cookie_samesite' => 'Lax',
];