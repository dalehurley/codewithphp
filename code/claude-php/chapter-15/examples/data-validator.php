<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\StructuredOutputs\StructuredOutputHelper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Structured Outputs - Data Validator\n\n";

$helper = new StructuredOutputHelper($_ENV['ANTHROPIC_API_KEY']);

$schema = [
    'type' => 'object',
    'properties' => [
        'email' => ['type' => 'string', 'format' => 'email'],
        'age' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 150],
        'name' => ['type' => 'string', 'minLength' => 1],
        'subscribed' => ['type' => 'boolean'],
    ],
    'required' => ['email', 'name'],
];

$prompt = 'Extract user data: John Doe, age 28, email john@example.com, subscribed to newsletter';

echo "Validating extracted data...\n\n";

try {
    $data = $helper->extractStructured($prompt, $schema);
    echo "✓ Validation passed\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "✗ Validation failed: {$e->getMessage()}\n";
}
