<?php

declare(strict_types=1);

/**
 * Response Parsing Example
 *
 * Parse and extract different types of data from Claude responses.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Response Parsing Examples ===\n\n";

try {
    $client = Anthropic::factory()->withApiKey($apiKey)->make();

    // Example 1: Extract JSON
    echo "1. Extracting JSON Data\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 200,
        'messages' => [
            ['role' => 'user', 'content' => 'Return a JSON object with keys: name="Claude", version="4", year=2025']
        ]
    ]);

    $text = $response->content[0]->text;
    echo "Raw: {$text}\n";

    // Extract JSON from response
    preg_match('/\{[^}]+\}/', $text, $matches);
    if (isset($matches[0])) {
        $data = json_decode($matches[0], true);
        echo "Parsed: " . print_r($data, true) . "\n";
    }

    // Example 2: Extract Code Blocks
    echo "\n2. Extracting Code Blocks\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 300,
        'messages' => [
            ['role' => 'user', 'content' => 'Write a PHP function to add two numbers']
        ]
    ]);

    $text = $response->content[0]->text;
    preg_match('/```php\n(.*?)\n```/s', $text, $matches);
    if (isset($matches[1])) {
        echo "Extracted code:\n{$matches[1]}\n";
    }

    echo "\n✓ Parsing examples complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
