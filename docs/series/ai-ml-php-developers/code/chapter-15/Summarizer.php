<?php

declare(strict_types=1);

/**
 * Article summarization using OpenAI GPT models
 * 
 * Condenses long text into concise summaries with configurable style and length.
 * 
 * Usage:
 *   $client = OpenAI::client($_ENV['OPENAI_API_KEY']);
 *   $summarizer = new Summarizer($client);
 *   $result = $summarizer->summarize($longArticle, 'brief', maxWords: 50);
 *   echo $result['summary'];
 */
final class Summarizer
{
    private const MAX_CHUNK_TOKENS = 3000;  // Leave room for response

    public function __construct(
        private readonly \OpenAI\Client $client,
        private readonly string $model = 'gpt-3.5-turbo',
    ) {}

    /**
     * Summarize text with specified length and style
     *
     * @param string $text Text to summarize
     * @param string $style brief|detailed|bulletPoints
     * @param int|null $maxWords Target word count (null for auto)
     * @return array{summary: string, originalLength: int, summaryLength: int, compressionRatio: float, tokens: int, cost: float}
     */
    public function summarize(
        string $text,
        string $style = 'brief',
        ?int $maxWords = null,
    ): array {
        // Estimate tokens (rough: 1 token ≈ 4 chars)
        $estimatedTokens = (int)(strlen($text) / 4);

        // If text is very long, chunk it
        if ($estimatedTokens > self::MAX_CHUNK_TOKENS) {
            return $this->summarizeLongText($text, $style, $maxWords);
        }

        // Build prompt based on style
        $systemPrompt = "You are an expert at creating clear, accurate summaries.";
        $userPrompt = $this->buildPrompt($text, $style, $maxWords);

        // Generate summary
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => $maxWords ? (int)($maxWords * 1.5) : 500,
            'temperature' => 0.3,  // Low for factual accuracy
        ]);

        $summary = trim($response->choices[0]->message->content);
        $originalWordCount = str_word_count($text);
        $summaryWordCount = str_word_count($summary);
        $compressionRatio = $originalWordCount > 0
            ? $summaryWordCount / $originalWordCount
            : 0;

        $cost = ($response->usage->totalTokens / 1000) * $this->getCostPerThousandTokens();

        return [
            'summary' => $summary,
            'originalLength' => $originalWordCount,
            'summaryLength' => $summaryWordCount,
            'compressionRatio' => $compressionRatio,
            'tokens' => $response->usage->totalTokens,
            'cost' => $cost,
        ];
    }

    /**
     * Build prompt based on summarization style
     */
    private function buildPrompt(string $text, string $style, ?int $maxWords): string
    {
        $lengthInstruction = $maxWords
            ? "Keep it to approximately {$maxWords} words."
            : "Keep it concise.";

        return match ($style) {
            'brief' => "Summarize the following text in 2-3 sentences. {$lengthInstruction}\n\n{$text}",
            'detailed' => "Provide a comprehensive summary covering all main points. {$lengthInstruction}\n\n{$text}",
            'bulletPoints' => "Summarize the key points as a bullet list. {$lengthInstruction}\n\n{$text}",
            default => "Summarize this text. {$lengthInstruction}\n\n{$text}",
        };
    }

    /**
     * Handle very long text by chunking
     */
    private function summarizeLongText(string $text, string $style, ?int $maxWords): array
    {
        // For simplicity, just truncate for now
        // A production system would chunk intelligently and combine summaries
        $truncated = substr($text, 0, self::MAX_CHUNK_TOKENS * 4);
        $result = $this->summarize($truncated, $style, $maxWords);
        $result['note'] = 'Text was truncated to fit context window';
        return $result;
    }

    /**
     * Summarize a file
     *
     * @param string $filepath Path to file to summarize
     * @param string $style Summarization style
     * @return array{summary: string, originalLength: int, summaryLength: int, compressionRatio: float, tokens: int, cost: float}
     */
    public function summarizeFile(string $filepath, string $style = 'brief'): array
    {
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException("File not found: {$filepath}");
        }

        $text = file_get_contents($filepath);
        if ($text === false) {
            throw new \RuntimeException("Failed to read file: {$filepath}");
        }

        return $this->summarize($text, $style);
    }

    /**
     * Extract key quotes from text
     *
     * @param string $text Text to extract quotes from
     * @param int $numQuotes Number of quotes to extract
     * @return array{quotes: array<string>, tokens: int, cost: float}
     */
    public function extractKeyQuotes(string $text, int $numQuotes = 3): array
    {
        $systemPrompt = "You are an expert at identifying the most important and impactful quotes from text.";
        $userPrompt = "Extract the {$numQuotes} most important quotes from this text. " .
            "Return only the quotes, one per line, without numbering or commentary:\n\n{$text}";

        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 300,
            'temperature' => 0.3,
        ]);

        $quotesText = trim($response->choices[0]->message->content);
        $quotes = array_filter(explode("\n", $quotesText));

        $cost = ($response->usage->totalTokens / 1000) * $this->getCostPerThousandTokens();

        return [
            'quotes' => array_values($quotes),
            'tokens' => $response->usage->totalTokens,
            'cost' => $cost,
        ];
    }

    /**
     * Summarize multiple documents and combine
     *
     * @param array<string> $texts Array of texts to summarize
     * @param string $style Summarization style
     * @return array{summary: string, originalLength: int, summaryLength: int, compressionRatio: float, tokens: int, cost: float}
     */
    public function summarizeMultiple(array $texts, string $style = 'brief'): array
    {
        // Summarize each text individually
        $individualSummaries = [];
        $totalTokens = 0;
        $totalCost = 0.0;

        foreach ($texts as $text) {
            $result = $this->summarize($text, $style, maxWords: 100);
            $individualSummaries[] = $result['summary'];
            $totalTokens += $result['tokens'];
            $totalCost += $result['cost'];
        }

        // Combine individual summaries
        $combinedText = implode("\n\n", $individualSummaries);

        // Summarize the combined summaries
        $finalResult = $this->summarize($combinedText, $style, maxWords: 200);

        // Aggregate statistics
        return [
            'summary' => $finalResult['summary'],
            'originalLength' => array_sum(array_map('str_word_count', $texts)),
            'summaryLength' => $finalResult['summaryLength'],
            'compressionRatio' => $finalResult['compressionRatio'],
            'tokens' => $totalTokens + $finalResult['tokens'],
            'cost' => $totalCost + $finalResult['cost'],
        ];
    }

    /**
     * Get cost per thousand tokens for the current model
     */
    private function getCostPerThousandTokens(): float
    {
        return match ($this->model) {
            'gpt-4' => 0.03,
            'gpt-4-turbo', 'gpt-4-turbo-preview' => 0.01,
            'gpt-3.5-turbo', 'gpt-3.5-turbo-16k' => 0.002,
            default => 0.002,
        };
    }
}
