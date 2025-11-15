<?php

declare(strict_types=1);

/**
 * Prompt Templates Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\PromptEngineering\PromptTemplate;
use Anthropic\Anthropic;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Prompt Templates ===\n\n";

try {
    $client = Anthropic::factory()->withApiKey($apiKey)->make();

    // Example 1: Email template
    $emailTemplate = new PromptTemplate(
        "Write a professional email to {{recipient}} about {{topic}}. " .
        "The tone should be {{tone}}."
    );

    $emailTemplate->setVariables([
        'recipient' => 'the development team',
        'topic' => 'upcoming code review',
        'tone' => 'friendly but professional'
    ]);

    $prompt = $emailTemplate->render();
    echo "Prompt:\n{$prompt}\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 500,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]);

    echo "Generated Email:\n{$response->content[0]->text}\n\n";

    echo "✓ Templates complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
