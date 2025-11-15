<?php

declare(strict_types=1);

namespace ClaudePhp\LaravelIntegration;

/**
 * Conversation builder for multi-turn interactions
 */
class Conversation
{
    private array $messages = [];

    public function __construct(
        private readonly ClaudeService $service,
        private readonly ?string $systemPrompt = null
    ) {
    }

    /**
     * Add a user message
     */
    public function user(string $content): self
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $content,
        ];

        return $this;
    }

    /**
     * Add an assistant message
     */
    public function assistant(string $content): self
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content,
        ];

        return $this;
    }

    /**
     * Send the conversation and get response
     */
    public function send(?string $model = null, ?int $maxTokens = null): ClaudeResponse
    {
        return $this->service->message(
            messages: $this->messages,
            system: $this->systemPrompt,
            model: $model,
            maxTokens: $maxTokens
        );
    }

    /**
     * Continue the conversation with a new user message
     */
    public function continue(string $message, ?string $model = null): ClaudeResponse
    {
        $this->user($message);
        $response = $this->send($model);

        // Add assistant response to history
        $this->assistant($response->content());

        return $response;
    }

    /**
     * Get all messages
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * Clear conversation history
     */
    public function clear(): self
    {
        $this->messages = [];
        return $this;
    }

    /**
     * Get the last message
     */
    public function lastMessage(): ?array
    {
        return end($this->messages) ?: null;
    }
}
