<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Text Tokenizer
 *
 * Splits text into tokens (words) with multiple strategies.
 */
class Tokenizer
{
    /**
     * Simple word-based tokenization
     * Splits on whitespace and removes punctuation
     */
    public function tokenize(string $text, bool $lowercase = true): array
    {
        // Convert to lowercase if requested
        if ($lowercase) {
            $text = mb_strtolower($text);
        }

        // Remove punctuation except apostrophes (for contractions like "don't")
        $text = preg_replace("/[^\p{L}\p{N}\s']/u", ' ', $text);

        // Split on whitespace
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $tokens ?: [];
    }

    /**
     * Advanced tokenization preserving contractions and hyphenated words
     */
    public function tokenizeAdvanced(string $text, bool $lowercase = true): array
    {
        if ($lowercase) {
            $text = mb_strtolower($text);
        }

        // Pattern: words, contractions (don't), hyphenated words, numbers
        preg_match_all("/\b[\p{L}][\p{L}']*\b|\b\p{N}+\b/u", $text, $matches);

        return $matches[0] ?? [];
    }

    /**
     * Character-level tokenization (for language models)
     */
    public function tokenizeChars(string $text): array
    {
        return mb_str_split($text);
    }

    /**
     * N-gram tokenization (sequences of N words)
     *
     * @param int $n Size of n-grams (2 = bigrams, 3 = trigrams)
     */
    public function tokenizeNgrams(string $text, int $n = 2): array
    {
        $words = $this->tokenize($text);
        $ngrams = [];

        for ($i = 0; $i <= count($words) - $n; $i++) {
            $ngrams[] = implode(' ', array_slice($words, $i, $n));
        }

        return $ngrams;
    }

    /**
     * Sentence tokenization
     */
    public function tokenizeSentences(string $text): array
    {
        // Split on sentence boundaries (., !, ?)
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $sentences ?: []);
    }
}
