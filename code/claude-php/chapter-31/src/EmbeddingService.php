<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\ClaudePhp;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Embedding Service
 *
 * Generate embeddings for text chunks (mock implementation for demo).
 * In production, integrate with OpenAI, Cohere, or custom embedding service.
 */
class EmbeddingService
{
    private Client $http;

    public function __construct(
        private string $provider = 'mock',
        private string $model = 'text-embedding-3-small',
        private int $dimensions = 1536,
        private LoggerInterface $logger = new NullLogger(),
        private ?string $apiKey = null
    ) {
        $this->http = new ClaudePhp(['timeout' => 30]);
    }

    /**
     * Generate embedding for single text
     */
    public function embed(string $text): array
    {
        return match($this->provider) {
            'openai' => $this->embedOpenAI($text),
            'cohere' => $this->embedCohere($text),
            'mock' => $this->embedMock($text),
            default => throw new \InvalidArgumentException("Unsupported provider: {$this->provider}")
        };
    }

    /**
     * Generate embeddings for multiple texts
     */
    public function embedBatch(array $texts): array
    {
        $embeddings = [];
        foreach ($texts as $text) {
            $embeddings[] = $this->embed($text);
        }
        return $embeddings;
    }

    /**
     * OpenAI embedding (requires API key)
     */
    private function embedOpenAI(string $text): array
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('OpenAI API key required');
        }

        try {
            $response = $this->http->post('https://api.openai.com/v1/embeddings', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'model' => $this->model,
                    'input' => $text
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'][0]['embedding'];

        } catch (\Throwable $e) {
            $this->logger->error('OpenAI embedding failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cohere embedding (requires API key)
     */
    private function embedCohere(string $text): array
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('Cohere API key required');
        }

        try {
            $response = $this->http->post('https://api.cohere.ai/v1/embed', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'texts' => [$text],
                    'model' => 'embed-english-v3.0'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['embeddings'][0];

        } catch (\Throwable $e) {
            $this->logger->error('Cohere embedding failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Mock embedding for testing
     * Generates a deterministic pseudo-embedding based on text
     */
    private function embedMock(string $text): array
    {
        // Generate deterministic embedding based on text hash
        $hash = md5($text);
        $embedding = [];

        for ($i = 0; $i < $this->dimensions; $i++) {
            $seed = hexdec(substr($hash, $i % 32, 2));
            $embedding[] = (sin($seed + $i) + 1) / 2;  // Normalize to 0-1
        }

        return $embedding;
    }

    /**
     * Calculate cosine similarity between embeddings
     */
    public function cosineSimilarity(array $vec1, array $vec2): float
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('Vectors must have same dimensions');
        }

        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $norm1 += $vec1[$i] * $vec1[$i];
            $norm2 += $vec2[$i] * $vec2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }

        return $dotProduct / ($norm1 * $norm2);
    }

    /**
     * Find most similar embeddings
     */
    public function findSimilar(
        array $queryEmbedding,
        array $candidateEmbeddings,
        int $topK = 5
    ): array {
        $similarities = [];

        foreach ($candidateEmbeddings as $index => $candidate) {
            $similarities[] = [
                'index' => $index,
                'score' => $this->cosineSimilarity($queryEmbedding, $candidate)
            ];
        }

        usort($similarities, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($similarities, 0, $topK);
    }
}
