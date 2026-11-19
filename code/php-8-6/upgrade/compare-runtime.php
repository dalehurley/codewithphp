<?php

declare(strict_types=1);

printf("PHP %s\n", PHP_VERSION);
printf("trim() bytes: %s\n", bin2hex(trim("\fhello\f")));

try {
    $positiveNumbers = array_filter([1], static fn (int $value): bool => $value > 0, -1);
    printf("Invalid array_filter() mode: returned %s\n", json_encode($positiveNumbers, JSON_THROW_ON_ERROR));
} catch (ValueError) {
    echo "Invalid array_filter() mode: ValueError\n";
}

if (!function_exists('session_get_cookie_params')) {
    echo "Session defaults: unavailable (session extension not loaded)\n";
    exit;
}

$cookie = session_get_cookie_params();
printf(
    "Session defaults: strict=%s, HttpOnly=%s, SameSite=%s\n",
    ini_get('session.use_strict_mode'),
    $cookie['httponly'] ? 'yes' : 'no',
    $cookie['samesite'] === '' ? '(unset)' : $cookie['samesite'],
);
