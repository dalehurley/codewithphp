<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

require_once __DIR__ . '/tokenizer.php';
require_once __DIR__ . '/stop-words.php';
require_once __DIR__ . '/stemmer.php';
require_once __DIR__ . '/bag-of-words.php';
require_once __DIR__ . '/tfidf.php';

/**
 * Complete Text Processing Pipeline
 *
 * Chains tokenization, stop-word removal, stemming, and vectorization.
 */
class TextProcessor
{
    private Tokenizer $tokenizer;
    private StopWordRemover $stopWordRemover;
    private Stemmer $stemmer;
    private bool $useStemming;
    private bool $useStopWords;

    public function __construct(
        bool $useStemming = true,
        bool $useStopWords = true,
        array $customStopWords = []
    ) {
        $this->tokenizer = new Tokenizer();
        $this->stopWordRemover = new StopWordRemover($customStopWords);
        $this->stemmer = new Stemmer();
        $this->useStemming = $useStemming;
        $this->useStopWords = $useStopWords;
    }

    /**
     * Process a single text document
     */
    public function process(string $text): array
    {
        // Step 1: Tokenize
        $tokens = $this->tokenizer->tokenize($text);

        // Step 2: Remove stop words
        if ($this->useStopWords) {
            $tokens = $this->stopWordRemover->remove($tokens);
        }

        // Step 3: Stem
        if ($this->useStemming) {
            $tokens = $this->stemmer->stemTokens($tokens);
        }

        return $tokens;
    }

    /**
     * Process multiple documents
     */
    public function processMany(array $texts): array
    {
        return array_map(fn($text) => $this->process($text), $texts);
    }

    /**
     * Process and vectorize with bag-of-words
     */
    public function processToBagOfWords(array $texts): array
    {
        $processedDocs = $this->processMany($texts);
        $bow = new BagOfWords();
        return [
            'vectors' => $bow->fitTransform($processedDocs),
            'vocabulary' => $bow->getVocabulary(),
            'vectorizer' => $bow
        ];
    }

    /**
     * Process and vectorize with TF-IDF
     */
    public function processToTfIdf(array $texts): array
    {
        $processedDocs = $this->processMany($texts);
        $tfidf = new TfIdfVectorizer();
        return [
            'vectors' => $tfidf->fitTransform($processedDocs),
            'vocabulary' => $tfidf->getVocabulary(),
            'idf' => $tfidf->getIdf(),
            'vectorizer' => $tfidf
        ];
    }

    /**
     * Get processing statistics
     */
    public function getStats(string $originalText, array $processedTokens): array
    {
        $originalTokens = $this->tokenizer->tokenize($originalText);

        return [
            'original_length' => mb_strlen($originalText),
            'original_tokens' => count($originalTokens),
            'processed_tokens' => count($processedTokens),
            'reduction_pct' => round((1 - count($processedTokens) / count($originalTokens)) * 100, 1),
            'unique_terms' => count(array_unique($processedTokens))
        ];
    }
}
