<?php
// public_html/api/lib/date.php

function to_mysql_date(string $input): ?string {
    $input = trim($input);
    if ($input === '') return null;

    // Already ISO: 2026-09-13 or 2026-09-13T...
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $input)) {
        return substr($input, 0, 10);
    }

    // Friendly: "13th September 2026"
    $months = [
        'january'=>1,'february'=>2,'march'=>3,'april'=>4,'may'=>5,'june'=>6,
        'july'=>7,'august'=>8,'september'=>9,'october'=>10,'november'=>11,'december'=>12,
    ];

    if (preg_match('/^(\d{1,2})(?:st|nd|rd|th)?\s+([A-Za-z]+)\s+(\d{4})$/', $input, $m)) {
        $day   = (int)$m[1];
        $month = $months[strtolower($m[2])] ?? 0;
        $year  = (int)$m[3];
        if ($day && $month && $year) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }
    return null;
}