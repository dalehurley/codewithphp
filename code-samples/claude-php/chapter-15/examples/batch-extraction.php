<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\StructuredOutputs\StructuredOutputHelper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Structured Outputs - Batch Extraction\n\n";

$helper = new StructuredOutputHelper($_ENV['ANTHROPIC_API_KEY']);

$schema = [
    'type' => 'array',
    'items' => [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'role' => ['type' => 'string'],
            'email' => ['type' => 'string'],
        ],
        'required' => ['name', 'role'],
    ],
];

$prompt = <<<PROMPT
Extract all team members:
- Alice Johnson, CEO, alice@company.com
- Bob Smith, CTO, bob@company.com
- Carol White, CFO, carol@company.com
PROMPT;

echo "Extracting multiple records...\n\n";
$data = $helper->extractStructured($prompt, $schema);

echo "Extracted " . count($data) . " team members:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
