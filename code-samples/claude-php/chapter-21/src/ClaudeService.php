<?php

declare(strict_types=1);

namespace ClaudePhp\LaravelIntegration;

use Anthropic\Anthropic;
use Anthropic\Resources\Messages;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Psr\Log\LoggerInterface;

/**
 * Main Claude Service for Laravel
 *
 * Provides a clean interface to Claude AI with caching and logging
 */
class ClaudeService
{
    private Messages $messages;

    public function __construct(
        private readonly Anthropic $client,
        private readonly CacheContract $cache,
        private readonly LoggerInterface $logger
    ) {
        $this->messages = $client->messages();
    }

    /**
     * Send a message to Claude
     */
    public function message(
        string|array $messages,
        ?string $system = null,
        ?string $model = null,
        ?int $maxTokens = null,
        ?float $temperature = null
    ): ClaudeResponse {
        $params = $this->buildParams($messages, $system, $model, $maxTokens, $temperature);

        $cacheKey = $this->getCacheKey($params);

        if (config('claude.cache.enabled') && $this->cache->has($cacheKey)) {
            $this->logger->debug('Claude response from cache', ['cache_key' => $cacheKey]);
            return new ClaudeResponse($this->cache->get($cacheKey));
        }

        $this->logger->info('Sending message to Claude', [
            'model' => $params['model'],
            'messages_count' => is_array($messages) ? count($messages) : 1,
        ]);

        $response = $this->messages->create($params);

        $responseData = [
            'id' => $response->id,
            'content' => $response->content[0]->text,
            'model' => $response->model,
            'role' => $response->role,
            'stop_reason' => $response->stopReason,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
            ],
        ];

        if (config('claude.cache.enabled')) {
            $this->cache->put(
                $cacheKey,
                $responseData,
                config('claude.cache.ttl')
            );
        }

        return new ClaudeResponse($responseData);
    }

    /**
     * Stream a message from Claude
     */
    public function stream(
        string|array $messages,
        callable $onChunk,
        ?string $system = null,
        ?string $model = null,
        ?int $maxTokens = null
    ): void {
        $params = $this->buildParams($messages, $system, $model, $maxTokens);
        $params['stream'] = true;

        $this->logger->info('Starting Claude stream', ['model' => $params['model']]);

        $stream = $this->messages->createStreamed($params);

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta' &&
                $event->delta->type === 'text_delta') {
                $onChunk($event->delta->text);
            }
        }
    }

    /**
     * Create a conversation chain
     */
    public function conversation(?string $system = null): Conversation
    {
        return new Conversation($this, $system);
    }

    /**
     * Build request parameters
     */
    private function buildParams(
        string|array $messages,
        ?string $system,
        ?string $model,
        ?int $maxTokens,
        ?float $temperature = null
    ): array {
        if (is_string($messages)) {
            $messages = [['role' => 'user', 'content' => $messages]];
        }

        $params = [
            'model' => $model ?? config('claude.model'),
            'max_tokens' => $maxTokens ?? config('claude.max_tokens'),
            'messages' => $messages,
        ];

        if ($system) {
            $params['system'] = $system;
        }

        if ($temperature !== null) {
            $params['temperature'] = $temperature;
        } elseif (config('claude.temperature') !== null) {
            $params['temperature'] = config('claude.temperature');
        }

        return $params;
    }

    /**
     * Generate cache key for request
     */
    private function getCacheKey(array $params): string
    {
        return 'claude:' . md5(json_encode($params));
    }

    /**
     * Get the underlying Anthropic client
     */
    public function client(): Anthropic
    {
        return $this->client;
    }
}
