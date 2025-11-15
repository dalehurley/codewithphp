<?php

declare(strict_types=1);

namespace ClaudePHP\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Professional Claude API service class
 */
class ClaudeService
{
    private Client $client;
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private LoggerInterface $logger;

    public function __construct(
        string $apiKey,
        string $model = 'claude-sonnet-4-20250514',
        int $maxTokens = 4096,
        ?LoggerInterface $logger = null
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->maxTokens = $maxTokens;
        $this->logger = $logger ?? new NullLogger();

        $this->client = new Client([
            'base_uri' => 'https://api.anthropic.com',
            'timeout' => 30,
        ]);
    }

    public function sendMessage(
        string|array $messages,
        ?string $system = null,
        ?array $tools = null
    ): array {
        // Normalize messages
        if (is_string($messages)) {
            $messages = [['role' => 'user', 'content' => $messages]];
        }

        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $messages,
        ];

        if ($system) {
            $payload['system'] = $system;
        }

        if ($tools) {
            $payload['tools'] = $tools;
        }

        $this->logger->info('Sending request to Claude API', [
            'model' => $this->model,
            'message_count' => count($messages),
        ]);

        try {
            $response = $this->client->post('/v1/messages', [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            $this->logger->info('Received response from Claude API', [
                'tokens_used' => $result['usage']['input_tokens'] + $result['usage']['output_tokens'],
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Claude API request failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function chat(string $message, ?string $system = null): string
    {
        $result = $this->sendMessage($message, $system);
        return $result['content'][0]['text'] ?? '';
    }

    public function withTools(array $tools): self
    {
        $clone = clone $this;
        // Tools would be stored in a property
        return $clone;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }
}
