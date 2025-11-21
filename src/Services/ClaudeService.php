<?php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AnthropicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ClaudeService implements ClaudeServiceInterface
{
    private string $defaultModel;
    private int $defaultMaxTokens;
    private float $defaultTemperature;

    public function __construct(
        private ClaudePhp $client,
        private ?LoggerInterface $logger = null,
        array $config = []
    ) {
        $this->logger ??= new NullLogger();

        // Load configuration with sensible defaults
        $this->defaultModel = $config['model'] ?? 'claude-sonnet-4-5';
        $this->defaultMaxTokens = $config['max_tokens'] ?? 4096;
        $this->defaultTemperature = $config['temperature'] ?? 1.0;
    }

    public function generate(
        string $prompt,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $model = null
    ): string {
        $this->logger->info('Generating text', [
            'prompt_length' => strlen($prompt),
            'max_tokens' => $maxTokens ?? $this->defaultMaxTokens,
            'model' => $model ?? $this->defaultModel,
        ]);

        try {
            $response = $this->client->messages()->create([
                'model' => $model ?? $this->defaultModel,
                'max_tokens' => $maxTokens ?? $this->defaultMaxTokens,
                'temperature' => $temperature ?? $this->defaultTemperature,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            // Access content from array response
            $text = $response->content[0]->text;

            $this->logger->info('Text generated successfully', [
                'output_length' => strlen($text),
                'tokens_used' => $response->usage->outputTokens,
            ]);

            return $text;

        } catch (AnthropicException $e) {
            $this->logger->error('Text generation failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);
            throw $e;
        }
    }

    public function generateWithMetadata(
        string $prompt,
        array $options = []
    ): array {
        $model = $options['model'] ?? $this->defaultModel;
        $maxTokens = $options['max_tokens'] ?? $this->defaultMaxTokens;
        $temperature = $options['temperature'] ?? $this->defaultTemperature;
        $system = $options['system'] ?? null;

        $params = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        if ($system) {
            $params['system'] = $system;
        }

        $response = $this->client->messages()->create($params);

        return [
            'text' => $response->content[0]->text,
            'metadata' => [
                'id' => $response->id,
                'model' => $response->model,
                'stop_reason' => $response->stopReason,
                'usage' => [
                    'input_tokens' => $response->usage->inputTokens,
                    'output_tokens' => $response->usage->outputTokens,
                ],
            ]
        ];
    }

    public function stream(
        string $prompt,
        callable $callback,
        array $options = []
    ): void {
        $model = $options['model'] ?? $this->defaultModel;
        $maxTokens = $options['max_tokens'] ?? $this->defaultMaxTokens;

        $this->logger->info('Starting stream', [
            'model' => $model,
            'max_tokens' => $maxTokens,
        ]);

        // Get generator from SDK
        $stream = $this->client->messages()->create([
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'stream' => true
        ]);

        foreach ($stream as $event) {
            // Check for content block deltas (text chunks)
            if (isset($event['type']) && $event['type'] === 'content_block_delta') {
                $callback($event['delta']['text'] ?? '');
            }
        }

        $this->logger->info('Stream completed');
    }

    public function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
    }

    public function healthCheck(): bool
    {
        try {
            $response = $this->client->messages()->create([
                'model' => $this->defaultModel,
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'ping']
                ]
            ]);

            return !empty($response->content[0]->text);

        } catch (\Exception $e) {
            $this->logger->error('Health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
