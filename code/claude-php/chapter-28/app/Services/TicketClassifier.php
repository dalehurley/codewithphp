<?php

declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;

/**
 * Support Ticket Classifier
 */
class TicketClassifier
{
    private $client;

    public function __construct(string $apiKey)
    {
                $this->client = new ClaudePhp(
                apiKey: $apiKey
            );
    }

    /**
     * Classify support ticket
     */
    public function classify(string $message): array
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Classify this support ticket:

Message: "{$message}"

Provide:
1. Category (billing, technical, account, general)
2. Priority (low, medium, high, urgent)
3. Sentiment (positive, neutral, negative, angry)
4. Requires human (yes/no)
5. Estimated complexity (simple, moderate, complex)

Format as JSON.
PROMPT,
            ]],
        ]);

        return $this->parseClassification($response->content[0]->text);
    }

    /**
     * Detect urgency
     */
    public function detectUrgency(string $message): string
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 50,
            'messages' => [[
                'role' => 'user',
                'content' => "How urgent is this support request? '{$message}'. Respond with only: low, medium, high, or urgent.",
            ]],
        ]);

        return strtolower(trim($response->content[0]->text));
    }

    /**
     * Extract key information
     */
    public function extractInfo(string $message): array
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Extract key information from this support ticket:

"{$message}"

Extract:
- Order number (if mentioned)
- Product name (if mentioned)
- Error messages (if any)
- Specific issue description
- Customer requests

Format as JSON.
PROMPT,
            ]],
        ]);

        return json_decode($response->content[0]->text, true) ?? [];
    }

    /**
     * Suggest response
     */
    public function suggestResponse(string $message, string $category): string
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Suggest a professional support response for:

Category: {$category}
Customer Message: "{$message}"

Provide a helpful, empathetic response that:
- Acknowledges the issue
- Provides solution or next steps
- Maintains professional tone
PROMPT,
            ]],
        ]);

        return $response->content[0]->text;
    }

    private function parseClassification(string $text): array
    {
        // Would parse JSON properly
        return [
            'category' => 'technical',
            'priority' => 'medium',
            'sentiment' => 'neutral',
            'requires_human' => false,
            'complexity' => 'moderate',
        ];
    }
}
