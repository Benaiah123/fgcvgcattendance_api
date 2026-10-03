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
    $now = time();
    $_SESSION['user'] = [
        'id'        => (int)$user['id'],
        'code'      => $user['code'],
        'label'     => $user['label'] ?? null,
        'role'      => $user['role'] ?? 'user',
        'login_at'  => $now,
        'last_seen' => $now,
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

    // Idle timeout — has it been too long since the last request?
    $lastSeen = (int)($u['last_seen'] ?? $u['login_at'] ?? 0);
    if (time() - $lastSeen > $cfg['session_ttl']) {
        session_logout();
        return null;
    }

    // Refresh idle timer
    $_SESSION['user']['last_seen'] = time();

    return $u;
}

function require_auth(): array {
    $u = current_user();
    if (!$u) {
        json_error('Unauthorized', 401);
    }
    return $u;
}