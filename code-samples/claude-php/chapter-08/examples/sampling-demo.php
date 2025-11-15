<?php

declare(strict_types=1);

/**
 * Sampling Parameters Demo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Sampling Parameters Demo ===\n\n";

try {
    $client = Anthropic::factory()->withApiKey($apiKey)->make();

    $prompt = "Write a creative opening line for a story.";

    // Demo different sampling parameters
    $configs = [
        ['name' => 'Low temperature (0.0)', 'temperature' => 0.0],
        ['name' => 'Medium temperature (0.5)', 'temperature' => 0.5],
        ['name' => 'High temperature (1.0)', 'temperature' => 1.0],
    ];

    foreach ($configs as $config) {
        echo "{$config['name']}:\n";

        for ($i = 1; $i <= 3; $i++) {
            $response = $client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 100,
                'temperature' => $config['temperature'],
                'messages' => [['role' => 'user', 'content' => $prompt]]
            ]);

            echo "  {$i}. {$response->content[0]->text}\n";
        }
        echo "\n";
    }

    echo "✓ Sampling demo complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
