<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Caching - Prompt Caching (Claude Feature)\n\n";

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Prompt caching allows caching of system prompts and long contexts
// This reduces costs and improves latency for repeated content

$longContext = str_repeat("This is context information. ", 100);

$response = $client->messages()->create(
    model: 'claude-sonnet-4-5',
    maxTokens: 1024,
    system: [
        [
            'type' => 'text',
            'text' => $longContext,
            'cache_control' => ['type' => 'ephemeral'], // Enable caching
        ],
    ],
    messages: [
        ['role' => 'user', 'content' => 'Summarize the context'],
    ]
);

$text = $response->content[0]['text'] ?? '';
echo "Response: " . $text . "\n\n";

// Access usage data safely
$usage = $response->usage ?? null;
if ($usage) {
    $creationTokens = $usage->cacheCreationInputTokens ?? 0;
    $readTokens = $usage->cacheReadInputTokens ?? 0;
    
    if ($creationTokens > 0) {
        echo "Cache creation tokens: {$creationTokens}\n";
    }
    if ($readTokens > 0) {
        echo "Cache read tokens: {$readTokens}\n";
    }
}

echo "\nPrompt caching is useful for:\n";
echo "- Long system prompts\n";
echo "- Large context documents\n";
echo "- Repeated conversations\n";
