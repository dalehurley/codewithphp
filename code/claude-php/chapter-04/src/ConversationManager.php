<?php

declare(strict_types=1);

namespace ClaudePhp\Conversations;

use ClaudePhp\ClaudePhp;

/**
 * ConversationManager - Manage multi-turn conversations with Claude
 */
class ConversationManager
{
    private $client;
    private array $messages = [];
    private string $model;
    private int $maxTokens;

    public function __construct(
        string $apiKey,
        string $model = 'claude-sonnet-4-5',
        int $maxTokens = 4096
    ) {
                $this->client = new ClaudePhp(    apiKey: $apiKey);
        $this->model = $model;
        $this->maxTokens = $maxTokens;
    }

    /**
     * Add a user message to the conversation
     */
    public function addUserMessage(string $content): void
    {
        $this->messages[] = ['role' => 'user', 'content' => $content];
    }

    /**
     * Add an assistant message to the conversation
     */
    public function addAssistantMessage(string $content): void
    {
        $this->messages[] = ['role' => 'assistant', 'content' => $content];
    }

    /**
     * Send the conversation and get a response
     */
    public function send(?string $userMessage = null): object
    {
        if ($userMessage !== null) {
            $this->addUserMessage($userMessage);
        }

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->messages
        ]);

        // Add assistant response to conversation history
        $this->addAssistantMessage($response->content[0]->text);

        return $response;
    }

    /**
     * Get all messages
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Clear conversation history
     */
    public function clear(): void
    {
        $this->messages = [];
    }

    /**
     * Trim old messages to stay within token limits
     */
    public function trimMessages(int $keepLast = 10): void
    {
        if (count($this->messages) > $keepLast) {
            $this->messages = array_slice($this->messages, -$keepLast);
        }
    }
}
