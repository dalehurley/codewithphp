<?php

declare(strict_types=1);

namespace App;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Retrieval Engine
 *
 * Retrieve relevant document chunks based on query.
 */
class RetrievalEngine
{
    private array $documents = [];
    private array $embeddings = [];

    public function __construct(
        private EmbeddingService $embeddingService,
        private int $topK = 5,
        private float $similarityThreshold = 0.7,
        private LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * Index documents for retrieval
     */
    public function indexDocuments(array $documents): void
    {
        $this->logger->info('Indexing documents', ['count' => count($documents)]);

        $this->documents = $documents;
        $texts = array_column($documents, 'text');

        $this->embeddings = $this->embeddingService->embedBatch($texts);

        $this->logger->info('Documents indexed', [
            'total' => count($this->documents),
            'embeddings' => count($this->embeddings)
        ]);
    }

    /**
     * Retrieve relevant documents for query
     */
    public function retrieve(string $query, ?int $topK = null): array
    {
        if (empty($this->documents)) {
            throw new \RuntimeException('No documents indexed');
        }

        $topK = $topK ?? $this->topK;

        $this->logger->debug('Retrieving documents', [
            'query' => substr($query, 0, 100),
            'top_k' => $topK
        ]);

        // Generate query embedding
        $queryEmbedding = $this->embeddingService->embed($query);

        // Find similar documents
        $results = $this->embeddingService->findSimilar(
            $queryEmbedding,
            $this->embeddings,
            $topK
        );

        // Filter by threshold and enrich with document data
        $retrieved = [];
        foreach ($results as $result) {
            if ($result['score'] >= $this->similarityThreshold) {
                $retrieved[] = [
                    'document' => $this->documents[$result['index']],
                    'score' => $result['score'],
                    'rank' => count($retrieved) + 1
                ];
            }
        }

        $this->logger->info('Documents retrieved', [
            'found' => count($retrieved),
            'avg_score' => $this->calculateAverageScore($retrieved)
        ]);

        return $retrieved;
    }

    /**
     * Hybrid retrieval (semantic + keyword)
     */
    public function hybridRetrieve(string $query, ?int $topK = null): array
    {
        $topK = $topK ?? $this->topK;

        // Semantic retrieval
        $semanticResults = $this->retrieve($query, $topK * 2);

        // Keyword retrieval
        $keywordResults = $this->keywordSearch($query, $topK * 2);

        // Merge and re-rank
        $merged = $this->mergeResults($semanticResults, $keywordResults);

        return array_slice($merged, 0, $topK);
    }

    /**
     * Simple keyword search
     */
    private function keywordSearch(string $query, int $limit): array
    {
        $keywords = array_map('trim', explode(' ', strtolower($query)));
        $results = [];

        foreach ($this->documents as $index => $doc) {
            $text = strtolower($doc['text']);
            $score = 0;

            foreach ($keywords as $keyword) {
                if (strlen($keyword) < 3) continue;
                $score += substr_count($text, $keyword);
            }

            if ($score > 0) {
                $results[] = [
                    'document' => $doc,
                    'score' => $score / count($keywords),  // Normalize
                    'type' => 'keyword'
                ];
            }
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, $limit);
    }

    /**
     * Merge semantic and keyword results
     */
    private function mergeResults(array $semantic, array $keyword): array
    {
        $merged = [];
        $seen = [];

        // Weight: 70% semantic, 30% keyword
        foreach ($semantic as $result) {
            $id = $result['document']['id'] ?? md5($result['document']['text']);
            $merged[$id] = [
                'document' => $result['document'],
                'score' => $result['score'] * 0.7,
                'sources' => ['semantic']
            ];
            $seen[$id] = true;
        }

        foreach ($keyword as $result) {
            $id = $result['document']['id'] ?? md5($result['document']['text']);

            if (isset($merged[$id])) {
                $merged[$id]['score'] += $result['score'] * 0.3;
                $merged[$id]['sources'][] = 'keyword';
            } else {
                $merged[$id] = [
                    'document' => $result['document'],
                    'score' => $result['score'] * 0.3,
                    'sources' => ['keyword']
                ];
            }
        }

        $results = array_values($merged);
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_map(function($result, $index) {
            $result['rank'] = $index + 1;
            return $result;
        }, $results, array_keys($results));
    }

    /**
     * Calculate average score
     */
    private function calculateAverageScore(array $results): float
    {
        if (empty($results)) {
            return 0.0;
        }

        $total = array_sum(array_column($results, 'score'));
        return round($total / count($results), 3);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'total_documents' => count($this->documents),
            'total_embeddings' => count($this->embeddings),
            'top_k' => $this->topK,
            'threshold' => $this->similarityThreshold
        ];
    }
}
