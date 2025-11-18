<?php

declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use Illuminate\Support\Facades\Cache;

/**
 * Knowledge Base Service for Support Bot
 */
class KnowledgeBaseService
{
    private $client;
    private array $knowledgeBase = [];

    public function __construct(string $apiKey)
    {
        $this->client = new ClaudePhp(apiKey: $apiKey);

        $this->loadKnowledgeBase();
    }

    /**
     * Load knowledge base from storage
     */
    private function loadKnowledgeBase(): void
    {
        // In production, load from database
        $this->knowledgeBase = [
            'product_info' => [
                'What is your product?' => 'Our product is...',
                'What are the features?' => 'Key features include...',
            ],
            'billing' => [
                'How do I cancel my subscription?' => 'To cancel...',
                'What payment methods do you accept?' => 'We accept...',
            ],
            'technical' => [
                'How do I install?' => 'Installation steps...',
                'Troubleshooting steps?' => 'Common issues...',
            ],
        ];
    }

    /**
     * Search knowledge base for relevant articles
     */
    public function search(string $query): array
    {
        $cacheKey = 'kb:search:' . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            $prompt = $this->buildSearchPrompt($query);

            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
            ]);

            return $this->parseSearchResults($response->content[0]['text']);
        });
    }

    /**
     * Generate answer from knowledge base
     */
    public function generateAnswer(string $question, array $context = []): string
    {
        $relevantArticles = $this->search($question);

        $knowledgeContext = implode("\n\n", array_map(
            fn($article) => "Q: {$article['question']}\nA: {$article['answer']}",
            $relevantArticles
        ));

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
You are a helpful customer support assistant.

Knowledge Base:
{$knowledgeContext}

Customer Question: {$question}

Provide a helpful, accurate answer based on the knowledge base.
If the answer isn't in the knowledge base, politely say so and offer to escalate.

Be friendly, professional, and concise.
PROMPT,
            ]],
        ]);

        return $response->content[0]['text'];
    }

    /**
     * Add article to knowledge base
     */
    public function addArticle(string $category, string $question, string $answer): void
    {
        if (!isset($this->knowledgeBase[$category])) {
            $this->knowledgeBase[$category] = [];
        }

        $this->knowledgeBase[$category][$question] = $answer;

        // In production, save to database
    }

    /**
     * Update article in knowledge base
     */
    public function updateArticle(string $category, string $question, string $newAnswer): void
    {
        if (isset($this->knowledgeBase[$category][$question])) {
            $this->knowledgeBase[$category][$question] = $newAnswer;
            // In production, update database
        }
    }

    private function buildSearchPrompt(string $query): string
    {
        $kbText = json_encode($this->knowledgeBase, JSON_PRETTY_PRINT);

        return <<<PROMPT
Find the most relevant articles for this question: "{$query}"

Knowledge Base:
{$kbText}

Return the top 3 most relevant Q&A pairs.
PROMPT;
    }

    private function parseSearchResults(string $text): array
    {
        // Simplified - would parse structured output
        return [
            ['question' => 'Sample Q', 'answer' => 'Sample A', 'relevance' => 0.9],
        ];
    }
}
