<?php

declare(strict_types=1);

/**
 * Temperature Comparison Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Temperature Comparison ===\n\n";

try {
    $client = new ClaudePhp(apiKey: $apiKey);

    $tasks = [
        ['name' => 'Code Generation', 'prompt' => 'Write a PHP function to reverse a string', 'temp' => 0.0],
        ['name' => 'Data Analysis', 'prompt' => 'Analyze this: sales up 25%', 'temp' => 0.3],
        ['name' => 'General Q&A', 'prompt' => 'What is Laravel?', 'temp' => 0.7],
        ['name' => 'Creative Writing', 'prompt' => 'Write a poem about coding', 'temp' => 1.0]
    ];

    foreach ($tasks as $task) {
        echo "{$task['name']} (temp={$task['temp']}):\n";
        echo "Prompt: {$task['prompt']}\n";

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 200,
            'temperature' => $task['temp'],
            'messages' => [['role' => 'user', 'content' => $task['prompt']]]
        ]);

        echo "Response: {$response->content[0]['text']}\n\n";
    }

    echo "✓ Temperature comparison complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
