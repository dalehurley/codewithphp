<?php

declare(strict_types=1);

/**
 * Adaptive Temperature Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Temperature\SamplingConfig;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Adaptive Temperature ===\n\n";

try {
    $client = new ClaudePhp(apiKey: $apiKey);

    $tasks = [
        ['type' => 'code', 'prompt' => 'Write a function to validate email'],
        ['type' => 'analysis', 'prompt' => 'Explain this error: 404 Not Found'],
        ['type' => 'general', 'prompt' => 'What is Composer?'],
        ['type' => 'creative', 'prompt' => 'Write a tagline for a PHP framework']
    ];

    foreach ($tasks as $task) {
        $config = SamplingConfig::forTask($task['type']);

        echo "Task: {$task['type']}\n";
        echo "Temperature: {$config['temperature']}\n";
        echo "Prompt: {$task['prompt']}\n";

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 200,
            'temperature' => $config['temperature'],
            'messages' => [['role' => 'user', 'content' => $task['prompt']]]
        ]);

        echo "Response: {$response->content[0]['text']}\n\n";
    }

    echo "✓ Adaptive temperature complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
