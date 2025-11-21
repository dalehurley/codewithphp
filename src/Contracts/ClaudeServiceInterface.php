<?php
declare(strict_types=1);

namespace App\Contracts;

interface ClaudeServiceInterface
{
    /**
     * Generate text completion from a prompt
     */
    public function generate(
        string $prompt,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $model = null
    ): string;

    /**
     * Generate with full response details
     */
    public function generateWithMetadata(
        string $prompt,
        array $options = []
    ): array;

    /**
     * Stream a response
     */
    public function stream(
        string $prompt,
        callable $callback,
        array $options = []
    ): void;

    /**
     * Get token count estimate
     */
    public function estimateTokens(string $text): int;

    /**
     * Check service health
     */
    public function healthCheck(): bool;
}
