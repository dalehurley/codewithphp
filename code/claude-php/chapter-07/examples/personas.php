<?php

declare(strict_types=1);

/**
 * Personas Example - Different AI personalities
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\SystemPrompts\SystemPromptLibrary;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== AI Personas ===\n\n";

try {
    $client = new ClaudePhp(    apiKey: $apiKey);

    $question = "How should I structure my PHP project?";

    // Persona 1: Code Reviewer
    echo "1. Code Reviewer Persona\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 300,
        'system' => SystemPromptLibrary::codeReviewer(),
        'messages' => [['role' => 'user', 'content' => $question]]
    ]);
    echo "{$response->content[0]->text}\n\n";

    // Persona 2: Technical Writer
    echo "2. Technical Writer Persona\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 300,
        'system' => SystemPromptLibrary::technicalWriter(),
        'messages' => [['role' => 'user', 'content' => $question]]
    ]);
    echo "{$response->content[0]->text}\n\n";

    echo "✓ Personas complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
