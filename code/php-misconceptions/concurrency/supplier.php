<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

[$delayUs, $status, $payload] = match ($path) {
    '/north' => [350_000, 200, ['cents' => 1250]],
    '/south' => [550_000, 200, ['cents' => 1850]],
    '/south-slow' => [2_000_000, 200, ['cents' => 1850]],
    '/south-error' => [550_000, 503, ['error' => 'Supplier unavailable.']],
    default => [0, 404, ['error' => 'Unknown supplier route.']],
};

usleep($delayUs);
http_response_code($status);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
