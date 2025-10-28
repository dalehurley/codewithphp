<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * TF-IDF Vectorizer
 *
 * Converts text to vectors weighted by term importance.
 * TF-IDF = Term Frequency × Inverse Document Frequency
 */
class TfIdfVectorizer
{
    private array $vocabulary = [];
    private array $idf = [];
    private bool $fitted = false;

    /**
     * Build vocabulary and calculate IDF weights
     *
     * @param array $documents Array of token arrays
     */
    public function fit(array $documents): self
    {
        // Build vocabulary
        $allTokens = [];
        foreach ($documents as $tokens) {
            $allTokens = array_merge($allTokens, $tokens);
        }
        $this->vocabulary = array_values(array_unique($allTokens));
        sort($this->vocabulary);

        // Calculate IDF for each term
        $numDocs = count($documents);
        $this->idf = [];

        foreach ($this->vocabulary as $term) {
            // Count documents containing this term
            $docFreq = 0;
            foreach ($documents as $tokens) {
                if (in_array($term, $tokens, true)) {
                    $docFreq++;
                }
            }

            // IDF = log(total docs / docs containing term)
            // Add 1 to avoid division by zero
            $this->idf[$term] = log($numDocs / ($docFreq + 1)) + 1;
        }

        $this->fitted = true;
        return $this;
    }

    /**
     * Transform documents into TF-IDF weighted vectors
     *
     * @param array $documents Array of token arrays
     * @return array Array of TF-IDF vectors
     */
    public function transform(array $documents): array
    {
        if (!$this->fitted) {
            throw new \RuntimeException("Vectorizer must be fitted before transform");
        }

        $vectors = [];

        foreach ($documents as $tokens) {
            $vectors[] = $this->vectorize($tokens);
        }

        return $vectors;
    }

    /**
     * Fit and transform in one step
     */
    public function fitTransform(array $documents): array
    {
        return $this->fit($documents)->transform($documents);
    }

    /**
     * Convert single document to TF-IDF vector
     */
    private function vectorize(array $tokens): array
    {
        $vector = array_fill(0, count($this->vocabulary), 0.0);

        // Calculate term frequencies
        $termFreq = [];
        $totalTokens = count($tokens);

        foreach ($tokens as $token) {
            $termFreq[$token] = ($termFreq[$token] ?? 0) + 1;
        }

        // Calculate TF-IDF for each term
        foreach ($this->vocabulary as $idx => $term) {
            if (isset($termFreq[$term])) {
                // TF = term frequency in document
                $tf = $termFreq[$term] / $totalTokens;

                // TF-IDF = TF × IDF
                $vector[$idx] = $tf * $this->idf[$term];
            }
        }

        return $vector;
    }

    /**
     * Get vocabulary
     */
    public function getVocabulary(): array
    {
        return $this->vocabulary;
    }

    /**
     * Get IDF weights
     */
    public function getIdf(): array
    {
        return $this->idf;
    }

    /**
     * Display vector with feature names and scores
     */
    public function displayVector(array $vector, int $topN = 10): array
    {
        $result = [];

        foreach ($this->vocabulary as $idx => $term) {
            if ($vector[$idx] > 0) {
                $result[$term] = round($vector[$idx], 4);
            }
        }

        // Sort by score descending
        arsort($result);

        // Return top N terms
        return array_slice($result, 0, $topN, true);
    }

    /**
     * Get most important terms across all documents
     */
    public function getMostImportantTerms(int $topN = 10): array
    {
        if (!$this->fitted) {
            throw new \RuntimeException("Vectorizer must be fitted first");
        }

        // Get average IDF (lower = more common)
        $idfCopy = $this->idf;
        arsort($idfCopy);

        return array_slice($idfCopy, 0, $topN, true);
    }
}
