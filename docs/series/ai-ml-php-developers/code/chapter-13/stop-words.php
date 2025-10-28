<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Stop Word Remover
 *
 * Filters common words that carry little semantic meaning.
 */
class StopWordRemover
{
    private array $stopWords;

    public function __construct(array $customStopWords = [])
    {
        // Common English stop words
        $defaultStopWords = [
            'a',
            'an',
            'and',
            'are',
            'as',
            'at',
            'be',
            'by',
            'for',
            'from',
            'has',
            'he',
            'in',
            'is',
            'it',
            'its',
            'of',
            'on',
            'that',
            'the',
            'to',
            'was',
            'will',
            'with',
            'the',
            'this',
            'but',
            'they',
            'have',
            'had',
            'what',
            'when',
            'where',
            'who',
            'which',
            'why',
            'how',
            'all',
            'each',
            'every',
            'both',
            'few',
            'more',
            'most',
            'other',
            'some',
            'such',
            'no',
            'nor',
            'not',
            'only',
            'own',
            'same',
            'so',
            'than',
            'too',
            'very',
            'can',
            'will',
            'just',
            'should',
            'now'
        ];

        $this->stopWords = array_merge($defaultStopWords, $customStopWords);
    }

    /**
     * Remove stop words from token array
     */
    public function remove(array $tokens): array
    {
        return array_values(
            array_filter($tokens, fn($token) => !$this->isStopWord($token))
        );
    }

    /**
     * Check if a single token is a stop word
     */
    public function isStopWord(string $token): bool
    {
        return in_array(mb_strtolower($token), $this->stopWords, true);
    }

    /**
     * Load stop words from file
     */
    public static function fromFile(string $filepath): self
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Stop words file not found: $filepath");
        }

        $content = file_get_contents($filepath);
        $words = array_filter(
            array_map('trim', explode("\n", $content)),
            fn($w) => $w !== '' && !str_starts_with($w, '#')
        );

        return new self($words);
    }

    /**
     * Get all stop words
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    /**
     * Add custom stop words
     */
    public function addStopWords(array $words): void
    {
        $this->stopWords = array_unique(array_merge($this->stopWords, $words));
    }
}
