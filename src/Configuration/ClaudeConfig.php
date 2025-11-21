<?php
declare(strict_types=1);

namespace App\Configuration;

class ClaudeConfig
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $defaultModel = 'claude-sonnet-4-5',
        public readonly int $maxTokens = 4096,
        public readonly float $temperature = 1.0,
        public readonly int $timeout = 30,
        public readonly bool $loggingEnabled = true,
    ) {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }

        if ($this->maxTokens < 1 || $this->maxTokens > 200000) {
            throw new \InvalidArgumentException('Max tokens must be between 1 and 200000');
        }
    }

    public static function fromArray(array $config): self
    {
        return new self(
            apiKey: $config['api_key'] ?? throw new \InvalidArgumentException('api_key is required'),
            defaultModel: $config['model'] ?? 'claude-sonnet-4-5',
            maxTokens: $config['max_tokens'] ?? 4096,
            temperature: $config['temperature'] ?? 1.0,
            timeout: $config['timeout'] ?? 30,
            loggingEnabled: $config['logging_enabled'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'api_key' => $this->apiKey,
            'model' => $this->defaultModel,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'timeout' => $this->timeout,
            'logging_enabled' => $this->loggingEnabled,
        ];
    }
}
