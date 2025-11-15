<?php

declare(strict_types=1);

namespace ClaudePhp\TokenManagement;

/**
 * TokenCounter - Estimate and track token usage
 */
class TokenCounter
{
    /**
     * Estimate token count for text (approximation)
     * Note: This is a rough estimate. Use API response for accurate counts.
     */
    public static function estimate(string $text): int
    {
        // Rough estimation: ~4 characters per token for English
        // This is approximate and varies by content
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * More accurate estimation using word count
     */
    public static function estimateByWords(string $text): int
    {
        $words = str_word_count($text);
        // Average: ~1.3 tokens per word
        return (int) ceil($words * 1.3);
    }

    /**
     * Estimate tokens for a conversation
     */
    public static function estimateConversation(array $messages): int
    {
        $total = 0;
        foreach ($messages as $message) {
            $total += self::estimate($message['content']);
        }
        return $total;
    }

    /**
     * Check if text is within token budget
     */
    public static function isWithinBudget(string $text, int $budget): bool
    {
        return self::estimate($text) <= $budget;
    }
}
