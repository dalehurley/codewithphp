<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ToolUse\ToolExecutor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Claude Tool Use - Basic Tools\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$executor = new ToolExecutor(
    apiKey: $_ENV['ANTHROPIC_API_KEY'],
    model: $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-5'
);

// Register a calculator tool
$executor->registerTool(
    name: 'calculator',
    description: 'Perform basic arithmetic operations (add, subtract, multiply, divide)',
    inputSchema: [
        'type' => 'object',
        'properties' => [
            'operation' => [
                'type' => 'string',
                'enum' => ['add', 'subtract', 'multiply', 'divide'],
                'description' => 'The arithmetic operation to perform',
            ],
            'a' => [
                'type' => 'number',
                'description' => 'First number',
            ],
            'b' => [
                'type' => 'number',
                'description' => 'Second number',
            ],
        ],
        'required' => ['operation', 'a', 'b'],
    ],
    function: function(array $input): array {
        $a = $input['a'];
        $b = $input['b'];

        $result = match($input['operation']) {
            'add' => $a + $b,
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $b != 0 ? $a / $b : 'Error: Division by zero',
            default => 'Unknown operation',
        };

        return ['result' => $result];
    }
);

// Example 1: Simple calculation
echo "Example 1: Simple Calculation\n";
echo str_repeat("─", 60) . "\n";

$result = $executor->execute(
    userMessage: "What is 342 multiplied by 89?",
    systemPrompt: "You are a helpful assistant with access to a calculator."
);

echo "Response: {$result['response']}\n";
echo "Iterations: {$result['iterations']}\n";
echo "Tokens: {$result['usage']['input_tokens']} in, {$result['usage']['output_tokens']} out\n";

// Example 2: Multiple calculations
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Multiple Calculations\n";
echo str_repeat("─", 60) . "\n";

$result = $executor->execute(
    userMessage: "Calculate (15 + 27) and then multiply the result by 3",
);

echo "Response: {$result['response']}\n";
echo "Iterations: {$result['iterations']}\n\n";

echo "Best Practices:\n";
echo "1. Provide clear, detailed tool descriptions\n";
echo "2. Use JSON Schema for parameter validation\n";
echo "3. Handle errors gracefully in tool functions\n";
echo "4. Return structured data from tools\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
