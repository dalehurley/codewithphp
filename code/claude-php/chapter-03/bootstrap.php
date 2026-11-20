<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->safeLoad();

$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? $_SERVER['ANTHROPIC_API_KEY'] ?? '';
if ($apiKey === '') {
    fwrite(STDERR, "Set ANTHROPIC_API_KEY in .env or in your shell.\n");
    exit(1);
}

return [
    'apiKey' => $apiKey,
    'model' => $_ENV['ANTHROPIC_MODEL'] ?? $_SERVER['ANTHROPIC_MODEL'] ?? 'claude-sonnet-5-5',
];
