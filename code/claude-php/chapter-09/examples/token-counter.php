<?php

declare(strict_types=1);

/**
 * Token Counter Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\TokenManagement\TokenCounter;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Token Counter ===\n\n";

try {
    $client = new ClaudePhp(    apiKey: $apiKey);

    // Example texts
    $texts = [
        "Hello, world!",
        "The quick brown fox jumps over the lazy dog.",
        "<?php\nfunction add(\$a, \$b) {\n    return \$a + \$b;\n}\n?>",
        str_repeat("This is a longer text to test token estimation. ", 10)
    ];

    foreach ($texts as $i => $text) {
        echo "Text " . ($i + 1) . ":\n";
        echo substr($text, 0, 100) . (strlen($text) > 100 ? "..." : "") . "\n";
        echo "Characters: " . strlen($text) . "\n";
        echo "Estimated tokens (chars): " . TokenCounter::estimate($text) . "\n";
        echo "Estimated tokens (words): " . TokenCounter::estimateByWords($text) . "\n";

        // Get actual count from API
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 10,
            'messages' => [['role' => 'user', 'content' => $text]]
        ]);

        echo "Actual input tokens: " . $response->usage->inputTokens . "\n\n";
    }

    echo "✓ Token counting complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
