<?php

declare(strict_types=1);

/**
 * Few-Shot Learning Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\PromptEngineering\PromptTemplate;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Few-Shot Learning ===\n\n";

try {
    $client = new ClaudePhp(    apiKey: $apiKey);

    $examples = [
        ['input' => 'The product is great!', 'output' => 'Positive'],
        ['input' => 'Terrible service', 'output' => 'Negative'],
        ['input' => 'It works fine', 'output' => 'Neutral']
    ];

    $prompt = PromptTemplate::fewShot(
        'Classify the sentiment',
        $examples,
        'I love this new feature!'
    );

    echo "Prompt:\n{$prompt}\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 50,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]);

    echo "Classification: {$response->content[0]->text}\n\n";

    echo "✓ Few-shot complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
