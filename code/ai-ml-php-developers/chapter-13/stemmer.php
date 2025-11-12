<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Simple English Stemmer
 *
 * Implements a basic suffix-stripping algorithm similar to Porter Stemmer.
 * Note: This is a simplified version for educational purposes.
 * For production, consider using a full Porter Stemmer implementation.
 */
class Stemmer
{
    private const SUFFIX_PATTERNS = [
        // Plural and verb forms
        'sses' => 'ss',    // dresses → dress
        'ies' => 'i',      // ponies → poni
        'ss' => 'ss',      // mess → mess
        's' => '',         // cats → cat

        // Gerunds and past tense
        'eed' => 'ee',     // agreed → agree
        'ing' => '',       // running → run
        'ed' => '',        // played → play

        // Adjective forms
        'ful' => '',       // beautiful → beauti
        'ness' => '',      // sadness → sad
        'ly' => '',        // quickly → quick
        'ment' => '',      // development → develop

        // Comparative
        'er' => '',        // faster → fast
        'est' => '',       // fastest → fast

        // Other common suffixes
        'ation' => '',     // creation → creat
        'ence' => '',      // presence → pres
        'ance' => '',      // importance → import
    ];

    private int $minStemLength;

    public function __construct(int $minStemLength = 3)
    {
        $this->minStemLength = $minStemLength;
    }

    /**
     * Stem a single word
     */
    public function stem(string $word): string
    {
        $word = mb_strtolower($word);
        $originalLength = mb_strlen($word);

        // Don't stem very short words
        if ($originalLength <= $this->minStemLength) {
            return $word;
        }

        // Try each suffix pattern (longest first)
        foreach (self::SUFFIX_PATTERNS as $suffix => $replacement) {
            if (str_ends_with($word, $suffix)) {
                $stem = mb_substr($word, 0, -mb_strlen($suffix)) . $replacement;

                // Only keep the stem if it's long enough
                if (mb_strlen($stem) >= $this->minStemLength) {
                    return $stem;
                }
            }
        }

        return $word;
    }

    /**
     * Stem an array of tokens
     */
    public function stemTokens(array $tokens): array
    {
        return array_map(fn($token) => $this->stem($token), $tokens);
    }

    /**
     * Stem and return with original mapping
     * Useful for displaying results
     */
    public function stemWithMapping(array $tokens): array
    {
        $mapping = [];

        foreach ($tokens as $token) {
            $stem = $this->stem($token);
            if (!isset($mapping[$stem])) {
                $mapping[$stem] = [];
            }
            $mapping[$stem][] = $token;
        }

        return $mapping;
    }
}
