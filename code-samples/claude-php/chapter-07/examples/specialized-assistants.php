<?php

declare(strict_types=1);

/**
 * Specialized Assistants Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\SystemPrompts\SystemPromptLibrary;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Specialized Assistants ===\n\n";

try {
    $client = new ClaudePhp(apiKey: $apiKey);

    // Customer Support Assistant
    echo "Customer Support Assistant:\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 200,
        'system' => SystemPromptLibrary::customerSupport(),
        'messages' => [
            ['role' => 'user', 'content' => 'My payment failed. What should I do?']
        ]
    ]);
    echo "{$response->content[0]['text']}\n\n";

    // Data Analyst Assistant
    echo "Data Analyst Assistant:\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 200,
        'system' => SystemPromptLibrary::dataAnalyst(),
        'messages' => [
            ['role' => 'user', 'content' => 'Sales increased 25% last quarter. What does this mean?']
        ]
    ]);
    echo "{$response->content[0]['text']}\n\n";

    echo "✓ Specialized assistants complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
