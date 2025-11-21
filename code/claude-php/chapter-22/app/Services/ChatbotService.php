<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use ClaudePhp\ClaudePhp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Chatbot Service
 *
 * Handles Claude AI integration for chatbot functionality
 */
class ChatbotService
{
    private ClaudePhp $client;

    public function __construct()
    {
        $this->client = new ClaudePhp(
            apiKey: config('services.anthropic.api_key')
        );
    }

    /**
     * Send a message and get response
     */
    public function sendMessage(Conversation $conversation, string $message): array
    {
        $conversationMessages = $this->buildConversationMessages($conversation, $message);

        $response = $this->client->messages()->create(
            model: config('services.anthropic.model'),
            maxTokens: config('services.anthropic.max_tokens'),
            messages: $conversationMessages,
            system: $conversation->system_prompt
        );

        Log::info('Chatbot sending message', [
            'conversation_id' => $conversation->id,
            'message_count' => count($conversationMessages),
        ]);

        return [
            'content' => $response->content[0]['text'] ?? '',
            'model' => $response->model,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens ?? 0,
                'output_tokens' => $response->usage->outputTokens ?? 0,
            ],
        ];
    }

    /**
     * Stream a message response
     */
    public function streamMessage(
        Conversation $conversation,
        string $message,
        callable $onChunk
    ): void {
        $conversationMessages = $this->buildConversationMessages($conversation, $message);

        $stream = $this->client->messages()->create(
            model: config('services.anthropic.model'),
            maxTokens: config('services.anthropic.max_tokens'),
            messages: $conversationMessages,
            system: $conversation->system_prompt,
            stream: true
        );

        foreach ($stream as $chunk) {
            if (isset($chunk['delta']['text'])) {
                $onChunk($chunk['delta']['text']);
            }
        }
    }

    /**
     * Generate a title for the conversation
     */
    public function generateTitle(string $firstMessage): string
    {
        $cacheKey = 'chatbot:title:' . md5($firstMessage);

        return Cache::remember($cacheKey, 3600, function () use ($firstMessage) {
            try {
                $response = $this->client->messages()->create(
                    model: config('services.anthropic.model'),
                    maxTokens: 50,
                    messages: [[
                        'role' => 'user',
                        'content' => "Generate a short, descriptive title (max 50 chars) for a conversation starting with: \"{$firstMessage}\"\n\nRespond with ONLY the title, nothing else.",
                    ]]
                );

                return trim($response->content[0]['text'] ?? '');

            } catch (\Exception $e) {
                Log::warning('Failed to generate conversation title', [
                    'error' => $e->getMessage(),
                ]);

                // Fallback to truncated first message
                return substr($firstMessage, 0, 50) . '...';
            }
        });
    }

    /**
     * Build conversation messages for Claude API
     */
    private function buildConversationMessages(Conversation $conversation, string $newMessage): array
    {
        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        // Add new user message
        $messages[] = [
            'role' => 'user',
            'content' => $newMessage,
        ];

        return $messages;
    }

    /**
     * Analyze sentiment of a message
     */
    public function analyzeSentiment(string $message): string
    {
        $response = $this->client->messages()->create(
            model: config('services.anthropic.model'),
            maxTokens: 50,
            messages: [[
                'role' => 'user',
                'content' => "Analyze the sentiment of this message in one word (positive, negative, or neutral): \"{$message}\"",
            ]]
        );

        return strtolower(trim($response->content[0]['text'] ?? 'neutral'));
    }

    /**
     * Detect intent of a message
     */
    public function detectIntent(string $message): string
    {
        $response = $this->client->messages()->create(
            model: config('services.anthropic.model'),
            maxTokens: 100,
            messages: [[
                'role' => 'user',
                'content' => "Classify the intent of this message (question, request, statement, command): \"{$message}\". Respond with only the classification.",
            ]]
        );

        return strtolower(trim($response->content[0]['text'] ?? 'statement'));
    }
}
