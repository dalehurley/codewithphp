<?php

declare(strict_types=1);

namespace ClaudePHP\Queue;

use ClaudePhp\ClaudePhp;

/**
 * Queue job for processing Claude API requests
 * (Simulated Laravel job interface)
 */
class ClaudeJob
{
    private string $prompt;
    private ?string $webhookUrl;
    private ClaudePhp $client;

    public function __construct(
        string $prompt,
        ?string $webhookUrl = null
    ) {
        $this->prompt = $prompt;
        $this->webhookUrl = $webhookUrl;

        $apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? '';
        $this->client = new ClaudePhp(
            apiKey: $apiKey
        );
    }

    public function handle(): void
    {
        echo "Processing job: " . substr($this->prompt, 0, 50) . "...\n";

        try {
            // Call Claude API
            $response = $this->client->messages()->create(
                model: 'claude-sonnet-4-5',
                maxTokens: 1024,
                messages: [
                    ['role' => 'user', 'content' => $this->prompt],
                ]
            );

            echo "✓ Job completed successfully\n";

            // Send webhook notification
            if ($this->webhookUrl) {
                // Convert response object to array for webhook
                $result = [
                    'content' => [
                        ['text' => $response->content[0]['text']]
                    ]
                ];
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
            // For webhook sending we still need a generic HTTP client or reuse Guzzle
            // But since we removed Guzzle from composer require (implicit dependency), 
            // we can assume it's available or use curl. 
            // Ideally we should keep Guzzle for this or use the one from SDK if exposed.
            // For simplicity in this example, we'll use Guzzle directly as it is likely installed.
            $webhookClient = new \GuzzleHttp\ClaudePhp();
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
                $webhookClient = new \GuzzleHttp\ClaudePhp();
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
