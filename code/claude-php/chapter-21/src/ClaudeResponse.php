<?php

declare(strict_types=1);

namespace ClaudePhp\LaravelIntegration;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Claude Response wrapper
 *
 * Provides a convenient interface to access Claude API responses
 */
class ClaudeResponse implements Arrayable, Jsonable
{
    public function __construct(private readonly array $data)
    {
    }

    /**
     * Get the response content
     */
    public function content(): string
    {
        return $this->data['content'];
    }

    /**
     * Get the response ID
     */
    public function id(): string
    {
        return $this->data['id'];
    }

    /**
     * Get the model used
     */
    public function model(): string
    {
        return $this->data['model'];
    }

    /**
     * Get the stop reason
     */
    public function stopReason(): string
    {
        return $this->data['stop_reason'];
    }

    /**
     * Get token usage
     */
    public function usage(): array
    {
        return $this->data['usage'];
    }

    /**
     * Get input tokens
     */
    public function inputTokens(): int
    {
        return $this->data['usage']['input_tokens'];
    }

    /**
     * Get output tokens
     */
    public function outputTokens(): int
    {
        return $this->data['usage']['output_tokens'];
    }

    /**
     * Get total tokens
     */
    public function totalTokens(): int
    {
        return $this->inputTokens() + $this->outputTokens();
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Convert to JSON
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->data, $options);
    }

    /**
     * Magic get method
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Magic toString method
     */
    public function __toString(): string
    {
        return $this->content();
    }
}
