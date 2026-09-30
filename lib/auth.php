<?php
// public_html/api/lib/auth.php

function session_boot(): array {
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config.php';
    }
    return $cfg;
}

function session_start_secure(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $cfg = session_boot();

    session_name($cfg['cookie_name']);
    session_set_cookie_params([
        'lifetime' => $cfg['session_ttl'],
        'path'     => '/',
        'domain'   => '',
        'secure'   => $cfg['cookie_secure'],
        'httponly' => true,
        'samesite' => $cfg['cookie_samesite'],
    ]);
    session_start();
}

function session_login(array $user): void {
    session_start_secure();
    session_regenerate_id(true);
   $_SESSION['user'] = [
    'id'       => (int)$user['id'],
    'code'     => $user['code'],
    'role'     => $user['role'] ?? 'user',
    'login_at' => time(),
    ];
}

function session_logout(): void {
    session_start_secure();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function current_user(): ?array {
    session_start_secure();
    $u = $_SESSION['user'] ?? null;
    if (!$u) return null;

    $cfg = session_boot();
    if (time() - (int)$u['login_at'] > $cfg['session_ttl']) {
        session_logout();
        return null;
    }
    return $u;
}

function require_auth(): array {
    $u = current_user();
    if (!$u) {
        json_error('Unauthorized', 401);
    }
    return $u;
}