<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use Anthropic\Anthropic;
use Anthropic\Resources\Messages;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Chatbot Service
 *
 * Handles Claude AI integration for chatbot functionality
 */
class ChatbotService
{
    private Messages $messages;

    public function __construct()
    {
        $client = Anthropic::factory()
            ->withApiKey(config('services.anthropic.api_key'))
            ->make();

        $this->messages = $client->messages();
    }

    /**
     * Send a message and get response
     */
    public function sendMessage(Conversation $conversation, string $message): array
    {
        $conversationMessages = $this->buildConversationMessages($conversation, $message);

        $params = [
            'model' => config('services.anthropic.model'),
            'max_tokens' => config('services.anthropic.max_tokens'),
            'messages' => $conversationMessages,
        ];

        if ($conversation->system_prompt) {
            $params['system'] = $conversation->system_prompt;
        }

        Log::info('Chatbot sending message', [
            'conversation_id' => $conversation->id,
            'message_count' => count($conversationMessages),
        ]);

        $response = $this->messages->create($params);

        return [
            'content' => $response->content[0]->text,
            'model' => $response->model,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
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

        $params = [
            'model' => config('services.anthropic.model'),
            'max_tokens' => config('services.anthropic.max_tokens'),
            'messages' => $conversationMessages,
            'stream' => true,
        ];

        if ($conversation->system_prompt) {
            $params['system'] = $conversation->system_prompt;
        }

        $stream = $this->messages->createStreamed($params);

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta' &&
                $event->delta->type === 'text_delta') {
                $onChunk($event->delta->text);
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
                $response = $this->messages->create([
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => 50,
                    'messages' => [[
                        'role' => 'user',
                        'content' => "Generate a short, descriptive title (max 50 chars) for a conversation starting with: \"{$firstMessage}\"\n\nRespond with ONLY the title, nothing else.",
                    ]],
                ]);

                return trim($response->content[0]->text);

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
        $response = $this->messages->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 50,
            'messages' => [[
                'role' => 'user',
                'content' => "Analyze the sentiment of this message in one word (positive, negative, or neutral): \"{$message}\"",
            ]],
        ]);

        return strtolower(trim($response->content[0]->text));
    }

    /**
     * Detect intent of a message
     */
    public function detectIntent(string $message): string
    {
        $response = $this->messages->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 100,
            'messages' => [[
                'role' => 'user',
                'content' => "Classify the intent of this message (question, request, statement, command): \"{$message}\". Respond with only the classification.",
            ]],
        ]);

        return strtolower(trim($response->content[0]->text));
    }
}
