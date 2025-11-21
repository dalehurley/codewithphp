<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$prompt = 'Complete this sentence: The future of PHP is';

echo "Testing different temperature settings...\n\n";

// Test different temperatures
$temperatures = [0.0, 0.5, 1.0, 1.5, 2.0];

foreach ($temperatures as $temp) {
    echo "Temperature {$temp}:\n";

    // Generate 3 completions to see variation
    for ($i = 1; $i <= 3; $i++) {
        try {
            $response = $client->messages()->create([
                'model' => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 50,
                'temperature' => $temp,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            echo "  {$i}. " . ($response->content[0]['text'] ?? 'No response') . "\n";
        } catch (Exception $e) {
            echo "  {$i}. Error: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

echo "Note: Temperature 0.0 should give nearly identical results.\n";
echo "Higher temperatures (1.5-2.0) will show more variation.\n";
