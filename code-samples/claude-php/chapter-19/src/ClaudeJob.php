<?php

declare(strict_types=1);

namespace ClaudePHP\Queue;

use GuzzleHttp\Client;

/**
 * Queue job for processing Claude API requests
 * (Simulated Laravel job interface)
 */
class ClaudeJob
{
    private string $prompt;
    private ?string $webhookUrl;
    private Client $client;

    public function __construct(
        string $prompt,
        ?string $webhookUrl = null
    ) {
        $this->prompt = $prompt;
        $this->webhookUrl = $webhookUrl;
        $this->client = new Client(['base_uri' => 'https://api.anthropic.com']);
    }

    public function handle(): void
    {
        echo "Processing job: " . substr($this->prompt, 0, 50) . "...\n";

        try {
            // Call Claude API
            $response = $this->client->post('/v1/messages', [
                'headers' => [
                    'x-api-key' => $_ENV['ANTHROPIC_API_KEY'] ?? '',
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'model' => 'claude-sonnet-4-20250514',
                    'max_tokens' => 1024,
                    'messages' => [
                        ['role' => 'user', 'content' => $this->prompt],
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            echo "✓ Job completed successfully\n";

            // Send webhook notification
            if ($this->webhookUrl) {
                $this->sendWebhook($result);
            }
        } catch (\Exception $e) {
            echo "✗ Job failed: {$e->getMessage()}\n";
            throw $e; // Laravel will retry the job
        }
    }

    private function sendWebhook(array $result): void
    {
        if (!$this->webhookUrl) {
            return;
        }

        try {
            $webhookClient = new Client();
            $webhookClient->post($this->webhookUrl, [
                'json' => [
                    'status' => 'completed',
                    'prompt' => $this->prompt,
                    'response' => $result['content'][0]['text'],
                    'timestamp' => date('c'),
                ],
            ]);

            echo "✓ Webhook sent\n";
        } catch (\Exception $e) {
            echo "⚠ Webhook failed: {$e->getMessage()}\n";
        }
    }

    public function failed(\Exception $exception): void
    {
        // Handle job failure
        echo "Job permanently failed: {$exception->getMessage()}\n";

        if ($this->webhookUrl) {
            try {
                $webhookClient = new Client();
                $webhookClient->post($this->webhookUrl, [
                    'json' => [
                        'status' => 'failed',
                        'prompt' => $this->prompt,
                        'error' => $exception->getMessage(),
                    ],
                ]);
            } catch (\Exception $e) {
                // Ignore webhook errors in failure handler
            }
        }
    }
}
