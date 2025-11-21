<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Stop sequences can help control output length with high temperature
// High temperature might generate longer outputs, but stop sequences provide a safety net

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1000,
        'temperature' => 1.5,  // High creativity
        'top_p' => 0.95,
        'stop_sequences' => ['</response>', '---END---'],  // Stop when these appear
        'messages' => [[
            'role' => 'user',
            'content' => 'Generate a creative product description. End with </response>'
        ]]
    ]);

    echo "Response with stop sequence:\n";
    echo $response->content[0]['text'] ?? 'No response';
    echo "\n\nNote: The response should end before '</response>' appears.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stop sequences work independently of sampling parameters.\n";
    echo "They provide deterministic stopping points regardless of temperature.\n";
}
