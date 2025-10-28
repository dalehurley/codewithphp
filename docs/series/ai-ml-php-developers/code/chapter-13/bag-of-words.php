<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Bag of Words Vectorizer
 *
 * Converts text documents into numeric feature vectors
 * based on word frequencies.
 */
class BagOfWords
{
    private array $vocabulary = [];
    private bool $fitted = false;

    /**
     * Build vocabulary from training documents
     *
     * @param array $documents Array of token arrays
     */
    public function fit(array $documents): self
    {
        $allTokens = [];

        foreach ($documents as $tokens) {
            $allTokens = array_merge($allTokens, $tokens);
        }

        $this->vocabulary = array_values(array_unique($allTokens));
        sort($this->vocabulary); // Sort for consistent ordering
        $this->fitted = true;

        return $this;
    }

    /**
     * Transform documents into feature vectors
     *
     * @param array $documents Array of token arrays
     * @return array Array of feature vectors
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
     * Convert single document to feature vector
     */
    private function vectorize(array $tokens): array
    {
        $vector = array_fill(0, count($this->vocabulary), 0);

        foreach ($tokens as $token) {
            $index = array_search($token, $this->vocabulary, true);
            if ($index !== false) {
                $vector[$index]++;
            }
        }

        return $vector;
    }

    /**
     * Get the learned vocabulary
     */
    public function getVocabulary(): array
    {
        return $this->vocabulary;
    }

    /**
     * Get feature names (vocabulary terms)
     */
    public function getFeatureNames(): array
    {
        return $this->vocabulary;
    }

    /**
     * Display vector with feature names
     */
    public function displayVector(array $vector): array
    {
        $result = [];

        foreach ($this->vocabulary as $idx => $term) {
            if ($vector[$idx] > 0) {
                $result[$term] = $vector[$idx];
            }
        }

        return $result;
    }
}
