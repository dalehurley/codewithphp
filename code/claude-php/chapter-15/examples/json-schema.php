<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\StructuredOutputs\StructuredOutputHelper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Structured Outputs - JSON Schema\n\n";

$helper = new StructuredOutputHelper($_ENV['ANTHROPIC_API_KEY']);

// Define schema for product data
$schema = [
    'type' => 'object',
    'properties' => [
        'name' => ['type' => 'string'],
        'price' => ['type' => 'number'],
        'category' => ['type' => 'string'],
        'in_stock' => ['type' => 'boolean'],
        'tags' => [
            'type' => 'array',
            'items' => ['type' => 'string'],
        ],
    ],
    'required' => ['name', 'price', 'category', 'in_stock'],
];

$prompt = 'Extract product information: MacBook Pro 16-inch, priced at $2499, laptop category, currently in stock, tags: apple, laptop, professional';

echo "Extracting structured data...\n\n";
$data = $helper->extractStructured($prompt, $schema);

echo "Extracted and validated data:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
