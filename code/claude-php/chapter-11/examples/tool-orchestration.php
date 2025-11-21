<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ToolUse\ToolExecutor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Tool Use - Tool Orchestration\n\n";

$executor = new ToolExecutor($_ENV['ANTHROPIC_API_KEY']);

// Register multiple tools for complex workflows
$executor->registerTool(
    'search_products',
    'Search for products in the catalog',
    [
        'type' => 'object',
        'properties' => [
            'query' => ['type' => 'string'],
            'category' => ['type' => 'string'],
        ],
        'required' => ['query'],
    ],
    fn($input) => [
        'products' => [
            ['id' => 1, 'name' => 'Laptop Pro', 'price' => 1299],
            ['id' => 2, 'name' => 'Mouse Wireless', 'price' => 29],
        ],
    ]
);

$executor->registerTool(
    'get_inventory',
    'Check inventory for a product',
    [
        'type' => 'object',
        'properties' => [
            'product_id' => ['type' => 'integer'],
        ],
        'required' => ['product_id'],
    ],
    fn($input) => ['stock' => rand(0, 100), 'product_id' => $input['product_id']]
);

$executor->registerTool(
    'calculate_shipping',
    'Calculate shipping cost',
    [
        'type' => 'object',
        'properties' => [
            'weight' => ['type' => 'number'],
            'destination' => ['type' => 'string'],
        ],
        'required' => ['weight', 'destination'],
    ],
    fn($input) => ['cost' => round($input['weight'] * 0.5 + 5, 2)]
);

$result = $executor->execute(
    "Find laptops, check if the first one is in stock, and calculate shipping to New York for a 2kg package"
);

echo "\nResponse: {$result['response']}\n";
echo "Tool calls: {$result['iterations']}\n";
