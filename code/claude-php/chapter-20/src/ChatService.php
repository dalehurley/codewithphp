<?php

declare(strict_types=1);

namespace ClaudePhp\RealtimeChat;

use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Chat Service for Claude AI integration
 *
 * Handles conversation management, streaming responses, and context preservation
 */
class ChatService
{
    private ClaudePhp $client;
    private array $conversationHistory = [];
    private int $maxHistoryLength;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'claude-sonnet-4-5',
        private readonly int $maxTokens = 4096,
        private readonly LoggerInterface $logger = new NullLogger(),
        int $maxHistoryLength = 50
    ) {
        $this->client = new ClaudePhp(
                apiKey: $this->apiKey
            );

        $this->maxHistoryLength = $maxHistoryLength;
    }

    /**
     * Send a message and get a streaming response
     */
    public function sendMessage(
        string $message,
        string $userId,
        ?string $systemPrompt = null,
        ?callable $onChunk = null
    ): string {
        // Add user message to history
        $this->addToHistory($userId, 'user', $message);

        // Prepare conversation history
        $conversationMessages = $this->getConversationMessages($userId);

        $params = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $conversationMessages,
        ];

        if ($systemPrompt) {
            $params['system'] = $systemPrompt;
        }

        $this->logger->info('Sending message to Claude', [
            'user_id' => $userId,
            'message_length' => strlen($message),
            'history_count' => count($conversationMessages),
        ]);

        $fullResponse = '';

        if ($onChunk) {
            // Streaming mode
            $params['stream'] = true;
            $stream = $this->client->messages()->stream($params);

            foreach ($stream as $event) {
                if ($event->type === 'content_block_delta' &&
                    $event->delta->type === 'text_delta') {
                    $chunk = $event->delta->text;
                    $fullResponse .= $chunk;
                    $onChunk($chunk);
                }
            }
        } else {
            // Non-streaming mode
            $response = $this->client->messages()->create($params);
            $fullResponse = $response->content[0]->text;
        }

        // Add assistant response to history
        $this->addToHistory($userId, 'assistant', $fullResponse);

        return $fullResponse;
    }

    /**
     * Send a message and return full response
     */
    public function sendMessageSync(
        string $message,
        string $userId,
        ?string $systemPrompt = null
    ): array {
        $this->addToHistory($userId, 'user', $message);
        $conversationMessages = $this->getConversationMessages($userId);

        $params = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $conversationMessages,
        ];

        if ($systemPrompt) {
            $params['system'] = $systemPrompt;
        }

        $response = $this->client->messages()->create($params);
        $assistantMessage = $response->content[0]->text;

        $this->addToHistory($userId, 'assistant', $assistantMessage);

        return [
            'message' => $assistantMessage,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
            ],
            'model' => $response->model,
            'stop_reason' => $response->stopReason,
        ];
    }

    /**
     * Add a message to conversation history
     */
    private function addToHistory(string $userId, string $role, string $content): void
    {
        if (!isset($this->conversationHistory[$userId])) {
            $this->conversationHistory[$userId] = [];
        }

        $this->conversationHistory[$userId][] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => time(),
        ];

        // Trim history if it exceeds max length
        if (count($this->conversationHistory[$userId]) > $this->maxHistoryLength) {
            $this->conversationHistory[$userId] = array_slice(
                $this->conversationHistory[$userId],
                -$this->maxHistoryLength
            );
        }
    }

    /**
     * Get conversation messages in Claude API format
     */
    private function getConversationMessages(string $userId): array
    {
        if (!isset($this->conversationHistory[$userId])) {
            return [];
        }

        return array_map(
            fn($msg) => [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ],
            $this->conversationHistory[$userId]
        );
    }

    /**
     * Clear conversation history for a user
     */
    public function clearHistory(string $userId): void
    {
        unset($this->conversationHistory[$userId]);
        $this->logger->info('Cleared conversation history', ['user_id' => $userId]);
    }

    /**
     * Get conversation history for a user
     */
    public function getHistory(string $userId): array
    {
        return $this->conversationHistory[$userId] ?? [];
    }

    /**
     * Export conversation history
     */
    public function exportHistory(string $userId): string
    {
        $history = $this->getHistory($userId);

        $export = "Conversation Export\n";
        $export .= "User ID: {$userId}\n";
        $export .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $export .= str_repeat('=', 80) . "\n\n";

        foreach ($history as $message) {
            $role = strtoupper($message['role']);
            $time = date('H:i:s', $message['timestamp']);
            $export .= "[{$time}] {$role}:\n{$message['content']}\n\n";
        }

        return $export;
    }
}
