<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Queue Processing - Batch Processing\n\n";

class BatchProcessor
{
    private Client $client;
    private array $results = [];

    public function __construct(string $apiKey)
    {
        $this->client = new Client(['base_uri' => 'https://api.anthropic.com']);
        $_ENV['ANTHROPIC_API_KEY'] = $apiKey;
    }

    public function processBatch(array $prompts): array
    {
        echo "Processing batch of " . count($prompts) . " items...\n\n";

        foreach ($prompts as $i => $prompt) {
            echo "Item " . ($i + 1) . ": " . substr($prompt, 0, 50) . "...\n";

            try {
                $response = $this->client->post('/v1/messages', [
                    'headers' => [
                        'x-api-key' => $_ENV['ANTHROPIC_API_KEY'],
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'claude-sonnet-4-20250514',
                        'max_tokens' => 512,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                    ],
                ]);

                $result = json_decode($response->getBody()->getContents(), true);
                $this->results[] = [
                    'prompt' => $prompt,
                    'response' => $result['content'][0]['text'],
                    'status' => 'success',
                ];

                echo "  ✓ Completed\n";
            } catch (\Exception $e) {
                $this->results[] = [
                    'prompt' => $prompt,
                    'error' => $e->getMessage(),
                    'status' => 'failed',
                ];
                echo "  ✗ Failed\n";
            }

            // Rate limiting
            usleep(500000); // 500ms delay
        }

        return $this->results;
    }

    public function getStats(): array
    {
        $successful = count(array_filter($this->results, fn($r) => $r['status'] === 'success'));
        $failed = count($this->results) - $successful;

        return [
            'total' => count($this->results),
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => count($this->results) > 0
                ? round(($successful / count($this->results)) * 100, 2)
                : 0,
        ];
    }
}

$processor = new BatchProcessor($_ENV['ANTHROPIC_API_KEY']);

$prompts = [
    'What is PHP?',
    'What is Laravel?',
    'What is Composer?',
    'What is PSR?',
    'What is Symfony?',
];

$results = $processor->processBatch($prompts);

echo "\n" . str_repeat("─", 60) . "\n";
echo "Batch Processing Complete\n";
echo str_repeat("─", 60) . "\n";

$stats = $processor->getStats();
echo "Total: {$stats['total']}\n";
echo "Successful: {$stats['successful']}\n";
echo "Failed: {$stats['failed']}\n";
echo "Success Rate: {$stats['success_rate']}%\n";
