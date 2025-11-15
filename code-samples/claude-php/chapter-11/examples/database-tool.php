<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ToolUse\ToolExecutor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Tool Use - Database Integration\n\n";

$executor = new ToolExecutor($_ENV['ANTHROPIC_API_KEY']);

// Simulated database
$database = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
        ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
    ],
    'orders' => [
        ['id' => 1, 'user_id' => 1, 'total' => 99.99, 'status' => 'completed'],
        ['id' => 2, 'user_id' => 2, 'total' => 149.99, 'status' => 'pending'],
    ],
];

$executor->registerTool(
    'query_database',
    'Query the database for users or orders',
    [
        'type' => 'object',
        'properties' => [
            'table' => ['type' => 'string', 'enum' => ['users', 'orders']],
            'filter' => ['type' => 'object'],
        ],
        'required' => ['table'],
    ],
    function($input) use ($database) {
        $table = $input['table'];
        $results = $database[$table] ?? [];

        if (isset($input['filter'])) {
            $results = array_filter($results, function($row) use ($input) {
                foreach ($input['filter'] as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] != $value) {
                        return false;
                    }
                }
                return true;
            });
        }

        return ['results' => array_values($results)];
    }
);

$result = $executor->execute(
    "Show me all users and their pending orders"
);

echo "\nResponse: {$result['response']}\n";
