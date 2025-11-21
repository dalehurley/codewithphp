<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

function testTopP(ClaudePhp $client, float $topP): array
{
    $responses = [];

    for ($i = 0; $i < 3; $i++) {
        try {
            $response = $client->messages()->create([
                'model' => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 100,
                'temperature' => 1.0,  // Keep temperature constant
                'top_p' => $topP,
                'messages' => [[
                    'role' => 'user',
                    'content' => 'Name 3 unique PHP frameworks:'
                ]]
            ]);

            $responses[] = $response->content[0]['text'] ?? 'No response';
        } catch (Exception $e) {
            $responses[] = "Error: " . $e->getMessage();
        }
    }

    return $responses;
}

// Compare different top_p values
echo "top_p = 0.5 (conservative):\n";
$responses = testTopP($client, 0.5);
foreach ($responses as $i => $resp) {
    echo "  " . ($i + 1) . ". " . substr($resp, 0, 100) . "...\n";
}
echo "\n";

echo "top_p = 0.9 (standard):\n";
$responses = testTopP($client, 0.9);
foreach ($responses as $i => $resp) {
    echo "  " . ($i + 1) . ". " . substr($resp, 0, 100) . "...\n";
}
echo "\n";

echo "top_p = 1.0 (all options):\n";
$responses = testTopP($client, 1.0);
foreach ($responses as $i => $resp) {
    echo "  " . ($i + 1) . ". " . substr($resp, 0, 100) . "...\n";
}
echo "\n";

echo "Note: Lower top_p values focus on higher probability tokens.\n";
echo "Higher top_p values allow more diverse token selection.\n";
