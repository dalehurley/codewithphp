<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ToolUse\ToolExecutor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Tool Use - Multiple Tools\n\n";

$executor = new ToolExecutor($_ENV['ANTHROPIC_API_KEY']);

// Weather tool
$executor->registerTool(
    'get_weather',
    'Get current weather for a location',
    [
        'type' => 'object',
        'properties' => [
            'location' => ['type' => 'string', 'description' => 'City name'],
            'unit' => ['type' => 'string', 'enum' => ['celsius', 'fahrenheit']],
        ],
        'required' => ['location'],
    ],
    fn($input) => [
        'temperature' => rand(15, 30),
        'condition' => ['sunny', 'cloudy', 'rainy'][rand(0, 2)],
        'unit' => $input['unit'] ?? 'celsius',
    ]
);

// Time tool
$executor->registerTool(
    'get_time',
    'Get current time for a timezone',
    [
        'type' => 'object',
        'properties' => [
            'timezone' => ['type' => 'string', 'description' => 'Timezone (e.g., America/New_York)'],
        ],
        'required' => ['timezone'],
    ],
    fn($input) => [
        'time' => (new DateTime('now', new DateTimeZone($input['timezone'])))->format('Y-m-d H:i:s'),
        'timezone' => $input['timezone'],
    ]
);

$result = $executor->execute(
    "What's the weather in Paris and what time is it there?"
);

echo "\nResponse: {$result['response']}\n";
echo "Iterations: {$result['iterations']}\n";
