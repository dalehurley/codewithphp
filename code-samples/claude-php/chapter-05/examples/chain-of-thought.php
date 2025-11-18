<?php

declare(strict_types=1);

/**
 * Chain-of-Thought Prompting Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\PromptEngineering\PromptTemplate;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Chain-of-Thought Prompting ===\n\n";

try {
    $client = new ClaudePhp(apiKey: $apiKey);

    $prompt = PromptTemplate::chainOfThought(
        "If a store has 15 apples and sells 60% of them, how many are left?"
    );

    echo "Prompt:\n{$prompt}\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 500,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]);

    echo "Response:\n{$response->content[0]['text']}\n\n";

    echo "✓ Chain-of-thought complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
