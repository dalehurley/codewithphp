<?php

declare(strict_types=1);

namespace CodeWithPHP\ClaudeShared;

use ClaudePhp\ClaudePhp;
use Anthropic\Contracts\MessageContract;

/**
 * Base Claude client with common functionality used across all chapters
 */
class BaseClaudeClient
{
    private Anthropic $client;
    private string $defaultModel;
    private int $defaultMaxTokens;

    public function __construct(
        ?string $apiKey = null,
        string $model = 'claude-sonnet-4-5',
        int $maxTokens = 4096
    ) {
                $this->client = new ClaudePhp(
                apiKey: $apiKey ?? getenv('ANTHROPIC_API_KEY')
            );

        $this->defaultModel = $model;
        $this->defaultMaxTokens = $maxTokens;
    }

    /**
     * Generate a response from a simple prompt
     */
    public function generate(
        string $prompt,
        ?string $system = null,
        array $options = []
    ): MessageContract {
        return $this->client->messages()->create([
            'model' => $options['model'] ?? $this->defaultModel,
            'max_tokens' => $options['max_tokens'] ?? $this->defaultMaxTokens,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            ...$options
        ]);
    }

    /**
     * Generate with conversation history
     */
    public function chat(
        array $messages,
        ?string $system = null,
        array $options = []
    ): MessageContract {
        return $this->client->messages()->create([
            'model' => $options['model'] ?? $this->defaultModel,
            'max_tokens' => $options['max_tokens'] ?? $this->defaultMaxTokens,
            'system' => $system,
            'messages' => $messages,
            ...$options
        ]);
    }

    /**
     * Get the underlying Anthropic client
     */
    public function getClient(): Anthropic
    {
        return $this->client;
    }

    /**
     * Get default model
     */
    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }

    /**
     * Set default model
     */
    public function setDefaultModel(string $model): self
    {
        $this->defaultModel = $model;
        return $this;
    }
}
