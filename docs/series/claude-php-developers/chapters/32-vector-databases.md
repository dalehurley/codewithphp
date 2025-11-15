---
title: "32: Vector Databases in PHP"
description: "Master vector database integration in PHP with Pinecone, Weaviate, and Milvus. Learn embedding strategies, similarity search, indexing optimization, and production deployment patterns."
series: "claude-php-developers"
chapter: 32
order: 32
difficulty: "Expert"
prerequisites:
  - "Completed Chapter 31 (RAG)"
  - "Understanding of vector embeddings"
  - "Knowledge of database optimization"
  - "Experience with cloud services"
---

![32: Vector Databases in PHP](/images/claude-php/chapter-32-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 32</span>
</div>

# Chapter 32: Vector Databases in PHP

## Overview

Vector databases are purpose-built for semantic search and AI applications. Unlike traditional databases that search for exact matches, vector databases find semantically similar content using mathematical distance calculations in high-dimensional space.

This chapter teaches you to integrate and optimize vector databases in PHP applications. You'll learn to work with Pinecone, Weaviate, and Milvus—choosing the right database for your use case, implementing efficient indexing strategies, and optimizing search performance for production workloads.

**What You'll Build**: Production-ready vector database integrations with multiple providers, intelligent indexing, hybrid search capabilities, and performance monitoring.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 31** (RAG implementation)
- ✓ **Vector embedding understanding** for similarity search
- ✓ **Database optimization knowledge** for performance tuning
- ✓ **Cloud service experience** for deployment

**Estimated Time**: 120-150 minutes

## Vector Database Abstraction Layer

```php
<?php
# filename: src/VectorDB/VectorStore.php
declare(strict_types=1);

namespace App\VectorDB;

interface VectorStore
{
    /**
     * Insert vectors with metadata
     */
    public function insert(array $vectors, array $metadata = []): InsertResult;

    /**
     * Search for similar vectors
     */
    public function search(
        array $queryVector,
        int $limit = 10,
        array $filters = []
    ): SearchResult;

    /**
     * Update vector by ID
     */
    public function update(string $id, array $vector, array $metadata = []): bool;

    /**
     * Delete vectors by ID or filter
     */
    public function delete(array $ids = [], array $filters = []): DeleteResult;

    /**
     * Create or update index
     */
    public function createIndex(string $name, array $config = []): bool;

    /**
     * Get statistics
     */
    public function getStats(): array;
}
```

## Pinecone Implementation

```php
<?php
# filename: src/VectorDB/Pinecone/PineconeStore.php
declare(strict_types=1);

namespace App\VectorDB\Pinecone;

use App\VectorDB\VectorStore;
use App\VectorDB\InsertResult;
use App\VectorDB\SearchResult;
use App\VectorDB\DeleteResult;
use GuzzleHttp\Client;

class PineconeStore implements VectorStore
{
    private Client $client;

    public function __construct(
        private string $apiKey,
        private string $environment,
        private string $indexName,
        private int $dimension = 1536
    ) {
        $this->client = new Client([
            'base_uri' => "https://{$indexName}-{$environment}.svc.pinecone.io",
            'headers' => [
                'Api-Key' => $apiKey,
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Insert vectors into Pinecone
     */
    public function insert(array $vectors, array $metadata = []): InsertResult
    {
        $vectors = $this->prepareVectorsForUpsert($vectors, $metadata);

        // Batch upserts (Pinecone recommends batches of 100)
        $batches = array_chunk($vectors, 100);
        $totalInserted = 0;

        foreach ($batches as $batch) {
            $response = $this->client->post('/vectors/upsert', [
                'json' => [
                    'vectors' => $batch,
                    'namespace' => $metadata['namespace'] ?? ''
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $totalInserted += $data['upsertedCount'] ?? count($batch);
        }

        return new InsertResult(
            count: $totalInserted,
            ids: array_column($vectors, 'id')
        );
    }

    /**
     * Search for similar vectors
     */
    public function search(
        array $queryVector,
        int $limit = 10,
        array $filters = []
    ): SearchResult {
        $payload = [
            'vector' => $queryVector,
            'topK' => $limit,
            'includeMetadata' => true,
            'includeValues' => false
        ];

        // Add namespace filter
        if (isset($filters['namespace'])) {
            $payload['namespace'] = $filters['namespace'];
            unset($filters['namespace']);
        }

        // Add metadata filters
        if (!empty($filters)) {
            $payload['filter'] = $this->buildFilter($filters);
        }

        $response = $this->client->post('/query', [
            'json' => $payload
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $results = [];
        foreach ($data['matches'] ?? [] as $match) {
            $results[] = [
                'id' => $match['id'],
                'score' => $match['score'],
                'metadata' => $match['metadata'] ?? []
            ];
        }

        return new SearchResult(
            results: $results,
            count: count($results)
        );
    }

    /**
     * Update vector
     */
    public function update(string $id, array $vector, array $metadata = []): bool
    {
        $payload = [
            'vectors' => [[
                'id' => $id,
                'values' => $vector,
                'metadata' => $metadata
            ]]
        ];

        if (isset($metadata['namespace'])) {
            $payload['namespace'] = $metadata['namespace'];
        }

        $response = $this->client->post('/vectors/upsert', [
            'json' => $payload
        ]);

        return $response->getStatusCode() === 200;
    }

    /**
     * Delete vectors
     */
    public function delete(array $ids = [], array $filters = []): DeleteResult
    {
        $payload = [];

        if (!empty($ids)) {
            $payload['ids'] = $ids;
        }

        if (!empty($filters)) {
            if (isset($filters['namespace'])) {
                $payload['namespace'] = $filters['namespace'];
                unset($filters['namespace']);
            }

            if (!empty($filters)) {
                $payload['filter'] = $this->buildFilter($filters);
            }
        }

        // Delete all if deleteAll flag is set
        if (isset($filters['deleteAll']) && $filters['deleteAll']) {
            $payload['deleteAll'] = true;
        }

        $response = $this->client->post('/vectors/delete', [
            'json' => $payload
        ]);

        return new DeleteResult(
            success: $response->getStatusCode() === 200,
            count: count($ids)
        );
    }

    /**
     * Create index
     */
    public function createIndex(string $name, array $config = []): bool
    {
        // Note: Index creation typically done via Pinecone console or API
        // This is a placeholder for the interface
        $client = new Client([
            'base_uri' => 'https://api.pinecone.io',
            'headers' => [
                'Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ]
        ]);

        $response = $client->post('/indexes', [
            'json' => [
                'name' => $name,
                'dimension' => $config['dimension'] ?? $this->dimension,
                'metric' => $config['metric'] ?? 'cosine',
                'pods' => $config['pods'] ?? 1,
                'replicas' => $config['replicas'] ?? 1,
                'pod_type' => $config['pod_type'] ?? 'p1.x1'
            ]
        ]);

        return $response->getStatusCode() === 201;
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $response = $this->client->post('/describe_index_stats', [
            'json' => []
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'dimension' => $data['dimension'] ?? 0,
            'index_fullness' => $data['indexFullness'] ?? 0,
            'total_vector_count' => $data['totalVectorCount'] ?? 0,
            'namespaces' => $data['namespaces'] ?? []
        ];
    }

    /**
     * Prepare vectors for upsert
     */
    private function prepareVectorsForUpsert(array $vectors, array $metadata): array
    {
        $prepared = [];

        foreach ($vectors as $i => $vector) {
            $id = $metadata['ids'][$i] ?? uniqid('vec_');

            $prepared[] = [
                'id' => $id,
                'values' => $vector,
                'metadata' => array_merge(
                    $metadata['items'][$i] ?? [],
                    ['created_at' => date('c')]
                )
            ];
        }

        return $prepared;
    }

    /**
     * Build Pinecone filter from array
     */
    private function buildFilter(array $filters): array
    {
        $filter = [];

        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $filter[$key] = ['$in' => $value];
            } else {
                $filter[$key] = ['$eq' => $value];
            }
        }

        return $filter;
    }
}
```

## Weaviate Implementation

```php
<?php
# filename: src/VectorDB/Weaviate/WeaviateStore.php
declare(strict_types=1);

namespace App\VectorDB\Weaviate;

use App\VectorDB\VectorStore;
use App\VectorDB\InsertResult;
use App\VectorDB\SearchResult;
use App\VectorDB\DeleteResult;
use GuzzleHttp\Client;

class WeaviateStore implements VectorStore
{
    private Client $client;

    public function __construct(
        private string $host,
        private ?string $apiKey = null,
        private string $className = 'Document'
    ) {
        $headers = ['Content-Type' => 'application/json'];

        if ($apiKey) {
            $headers['Authorization'] = "Bearer {$apiKey}";
        }

        $this->client = new Client([
            'base_uri' => rtrim($host, '/'),
            'headers' => $headers
        ]);
    }

    /**
     * Insert vectors into Weaviate
     */
    public function insert(array $vectors, array $metadata = []): InsertResult
    {
        $objects = [];

        foreach ($vectors as $i => $vector) {
            $id = $metadata['ids'][$i] ?? null;
            $props = $metadata['items'][$i] ?? [];

            $object = [
                'class' => $this->className,
                'properties' => array_merge($props, [
                    'created_at' => date('c')
                ]),
                'vector' => $vector
            ];

            if ($id) {
                $object['id'] = $id;
            }

            $objects[] = $object;
        }

        // Batch insert
        $response = $this->client->post('/v1/batch/objects', [
            'json' => [
                'objects' => $objects
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $inserted = 0;
        $ids = [];

        foreach ($data as $result) {
            if (isset($result['result']['status']) && $result['result']['status'] === 'SUCCESS') {
                $inserted++;
                $ids[] = $result['id'];
            }
        }

        return new InsertResult(
            count: $inserted,
            ids: $ids
        );
    }

    /**
     * Search using vector similarity
     */
    public function search(
        array $queryVector,
        int $limit = 10,
        array $filters = []
    ): SearchResult {
        $query = [
            'query' => sprintf('{
                Get {
                    %s(
                        nearVector: {
                            vector: %s
                        }
                        limit: %d
                        %s
                    ) {
                        _additional {
                            id
                            distance
                            certainty
                        }
                        %s
                    }
                }
            }',
                $this->className,
                json_encode($queryVector),
                $limit,
                $this->buildWhereFilter($filters),
                $this->getPropertiesString($filters['properties'] ?? [])
            )
        ];

        $response = $this->client->post('/v1/graphql', [
            'json' => $query
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $results = [];

        $items = $data['data']['Get'][$this->className] ?? [];

        foreach ($items as $item) {
            $additional = $item['_additional'] ?? [];
            unset($item['_additional']);

            $results[] = [
                'id' => $additional['id'] ?? null,
                'score' => $additional['certainty'] ?? 0.0,
                'distance' => $additional['distance'] ?? 0.0,
                'metadata' => $item
            ];
        }

        return new SearchResult(
            results: $results,
            count: count($results)
        );
    }

    /**
     * Update object
     */
    public function update(string $id, array $vector, array $metadata = []): bool
    {
        $response = $this->client->put("/v1/objects/{$this->className}/{$id}", [
            'json' => [
                'class' => $this->className,
                'properties' => array_merge($metadata, [
                    'updated_at' => date('c')
                ]),
                'vector' => $vector
            ]
        ]);

        return $response->getStatusCode() === 200;
    }

    /**
     * Delete objects
     */
    public function delete(array $ids = [], array $filters = []): DeleteResult
    {
        $deleted = 0;

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $response = $this->client->delete("/v1/objects/{$this->className}/{$id}");
                if ($response->getStatusCode() === 204) {
                    $deleted++;
                }
            }
        } elseif (!empty($filters)) {
            // Batch delete with where filter
            $response = $this->client->delete('/v1/batch/objects', [
                'json' => [
                    'match' => [
                        'class' => $this->className,
                        'where' => $this->buildWhereFilterArray($filters)
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $deleted = $data['results']['successful'] ?? 0;
        }

        return new DeleteResult(
            success: $deleted > 0,
            count: $deleted
        );
    }

    /**
     * Create schema (class)
     */
    public function createIndex(string $name, array $config = []): bool
    {
        $schema = [
            'class' => $name,
            'vectorizer' => $config['vectorizer'] ?? 'none',
            'properties' => $config['properties'] ?? [
                [
                    'name' => 'content',
                    'dataType' => ['text']
                ],
                [
                    'name' => 'created_at',
                    'dataType' => ['date']
                ]
            ]
        ];

        $response = $this->client->post('/v1/schema', [
            'json' => $schema
        ]);

        return $response->getStatusCode() === 200;
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $response = $this->client->get('/v1/schema');
        $schema = json_decode($response->getBody()->getContents(), true);

        $classInfo = null;
        foreach ($schema['classes'] ?? [] as $class) {
            if ($class['class'] === $this->className) {
                $classInfo = $class;
                break;
            }
        }

        // Get object count
        $query = [
            'query' => sprintf('{
                Aggregate {
                    %s {
                        meta {
                            count
                        }
                    }
                }
            }', $this->className)
        ];

        $response = $this->client->post('/v1/graphql', ['json' => $query]);
        $data = json_decode($response->getBody()->getContents(), true);

        $count = $data['data']['Aggregate'][$this->className][0]['meta']['count'] ?? 0;

        return [
            'class' => $this->className,
            'total_objects' => $count,
            'properties' => $classInfo['properties'] ?? []
        ];
    }

    /**
     * Build WHERE filter for GraphQL
     */
    private function buildWhereFilter(array $filters): string
    {
        if (empty($filters) || isset($filters['properties'])) {
            return '';
        }

        $conditions = [];
        foreach ($filters as $key => $value) {
            if ($key === 'properties') continue;

            if (is_array($value)) {
                $conditions[] = sprintf('{
                    path: ["%s"]
                    operator: ContainsAny
                    valueText: %s
                }', $key, json_encode($value));
            } else {
                $conditions[] = sprintf('{
                    path: ["%s"]
                    operator: Equal
                    valueText: "%s"
                }', $key, $value);
            }
        }

        if (empty($conditions)) {
            return '';
        }

        return sprintf('where: {
            operator: And
            operands: [%s]
        }', implode(', ', $conditions));
    }

    private function buildWhereFilterArray(array $filters): array
    {
        $operands = [];

        foreach ($filters as $key => $value) {
            $operands[] = [
                'path' => [$key],
                'operator' => is_array($value) ? 'ContainsAny' : 'Equal',
                'valueText' => is_array($value) ? $value : [$value]
            ];
        }

        return [
            'operator' => 'And',
            'operands' => $operands
        ];
    }

    private function getPropertiesString(array $properties): string
    {
        if (empty($properties)) {
            return 'content created_at';
        }

        return implode(' ', $properties);
    }
}
```

## Hybrid Search Implementation

```php
<?php
# filename: src/VectorDB/HybridSearch.php
declare(strict_types=1);

namespace App\VectorDB;

use Anthropic\Anthropic;

class HybridSearch
{
    public function __construct(
        private VectorStore $vectorStore,
        private Anthropic $claude,
        private float $vectorWeight = 0.7,
        private float $keywordWeight = 0.3
    ) {}

    /**
     * Hybrid search combining vector and keyword search
     */
    public function search(
        string $query,
        array $queryVector,
        int $limit = 10,
        array $filters = []
    ): SearchResult {
        // Vector search
        $vectorResults = $this->vectorStore->search(
            queryVector: $queryVector,
            limit: $limit * 2, // Get more results for fusion
            filters: $filters
        );

        // Keyword search (if supported by vector store)
        $keywordResults = $this->keywordSearch($query, $limit * 2, $filters);

        // Reciprocal Rank Fusion
        $fusedResults = $this->reciprocalRankFusion(
            vectorResults: $vectorResults->results,
            keywordResults: $keywordResults,
            k: 60
        );

        // Take top K
        $topResults = array_slice($fusedResults, 0, $limit);

        return new SearchResult(
            results: $topResults,
            count: count($topResults)
        );
    }

    /**
     * Reciprocal Rank Fusion algorithm
     */
    private function reciprocalRankFusion(
        array $vectorResults,
        array $keywordResults,
        int $k = 60
    ): array {
        $scores = [];

        // Score from vector search
        foreach ($vectorResults as $rank => $result) {
            $id = $result['id'];
            $scores[$id] = ($scores[$id] ?? 0) + $this->vectorWeight / ($k + $rank + 1);

            if (!isset($scores[$id . '_data'])) {
                $scores[$id . '_data'] = $result;
            }
        }

        // Score from keyword search
        foreach ($keywordResults as $rank => $result) {
            $id = $result['id'];
            $scores[$id] = ($scores[$id] ?? 0) + $this->keywordWeight / ($k + $rank + 1);

            if (!isset($scores[$id . '_data'])) {
                $scores[$id . '_data'] = $result;
            }
        }

        // Sort by fused score
        arsort($scores);

        // Build final results
        $results = [];
        foreach ($scores as $key => $score) {
            if (str_ends_with($key, '_data')) {
                continue;
            }

            $result = $scores[$key . '_data'];
            $result['score'] = $score;
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Simple keyword search (BM25-like)
     */
    private function keywordSearch(string $query, int $limit, array $filters): array
    {
        // This is a simplified keyword search
        // In production, integrate with full-text search like Elasticsearch

        $keywords = $this->extractKeywords($query);

        // Search for each keyword
        $matches = [];

        // This would typically query a traditional search index
        // For now, we'll simulate with vector store metadata filters

        return $matches;
    }

    /**
     * Extract keywords from query
     */
    private function extractKeywords(string $query): array
    {
        // Remove common stop words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for'];

        $words = str_word_count(strtolower($query), 1);

        return array_values(array_diff($words, $stopWords));
    }
}
```

## Vector Database Manager

```php
<?php
# filename: src/VectorDB/VectorDBManager.php
declare(strict_types=1);

namespace App\VectorDB;

class VectorDBManager
{
    private array $stores = [];

    public function __construct(
        private array $config
    ) {}

    /**
     * Get vector store by name
     */
    public function store(string $name = 'default'): VectorStore
    {
        if (isset($this->stores[$name])) {
            return $this->stores[$name];
        }

        $config = $this->config[$name] ?? throw new \InvalidArgumentException("Store '{$name}' not configured");

        $this->stores[$name] = $this->createStore($config);

        return $this->stores[$name];
    }

    /**
     * Create store from configuration
     */
    private function createStore(array $config): VectorStore
    {
        return match($config['driver']) {
            'pinecone' => new Pinecone\PineconeStore(
                apiKey: $config['api_key'],
                environment: $config['environment'],
                indexName: $config['index'],
                dimension: $config['dimension'] ?? 1536
            ),
            'weaviate' => new Weaviate\WeaviateStore(
                host: $config['host'],
                apiKey: $config['api_key'] ?? null,
                className: $config['class'] ?? 'Document'
            ),
            'milvus' => new Milvus\MilvusStore(
                host: $config['host'],
                port: $config['port'] ?? 19530,
                collectionName: $config['collection']
            ),
            'qdrant' => new Qdrant\QdrantStore(
                host: $config['host'],
                apiKey: $config['api_key'] ?? null,
                collectionName: $config['collection']
            ),
            default => throw new \InvalidArgumentException("Unknown driver: {$config['driver']}")
        };
    }

    /**
     * Migrate data between vector stores
     */
    public function migrate(string $from, string $to, array $filters = []): int
    {
        $sourceStore = $this->store($from);
        $targetStore = $this->store($to);

        // This is a simplified migration
        // In production, implement batched migration with progress tracking

        $stats = $sourceStore->getStats();
        echo "Migrating {$stats['total_vector_count']} vectors from {$from} to {$to}...\n";

        // Migration logic would go here

        return 0;
    }
}
```

## Performance Monitoring

```php
<?php
# filename: src/VectorDB/PerformanceMonitor.php
declare(strict_types=1);

namespace App\VectorDB;

class PerformanceMonitor
{
    private array $metrics = [];

    /**
     * Track search performance
     */
    public function trackSearch(
        callable $searchFn,
        array $context = []
    ): mixed {
        $start = microtime(true);
        $memoryBefore = memory_get_usage();

        try {
            $result = $searchFn();

            $this->recordMetric('search', [
                'duration_ms' => (microtime(true) - $start) * 1000,
                'memory_mb' => (memory_get_usage() - $memoryBefore) / 1024 / 1024,
                'status' => 'success',
                'context' => $context
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->recordMetric('search', [
                'duration_ms' => (microtime(true) - $start) * 1000,
                'status' => 'error',
                'error' => $e->getMessage(),
                'context' => $context
            ]);

            throw $e;
        }
    }

    /**
     * Get performance statistics
     */
    public function getStats(): array
    {
        if (empty($this->metrics['search'])) {
            return [];
        }

        $searches = $this->metrics['search'];
        $durations = array_column($searches, 'duration_ms');

        return [
            'total_searches' => count($searches),
            'avg_duration_ms' => array_sum($durations) / count($durations),
            'min_duration_ms' => min($durations),
            'max_duration_ms' => max($durations),
            'p95_duration_ms' => $this->percentile($durations, 0.95),
            'p99_duration_ms' => $this->percentile($durations, 0.99),
            'error_rate' => $this->calculateErrorRate($searches)
        ];
    }

    private function recordMetric(string $type, array $data): void
    {
        if (!isset($this->metrics[$type])) {
            $this->metrics[$type] = [];
        }

        $this->metrics[$type][] = array_merge($data, [
            'timestamp' => microtime(true)
        ]);
    }

    private function percentile(array $values, float $percentile): float
    {
        sort($values);
        $index = (int)ceil(count($values) * $percentile) - 1;
        return $values[max(0, $index)];
    }

    private function calculateErrorRate(array $searches): float
    {
        $errors = count(array_filter($searches, fn($s) => $s['status'] === 'error'));
        return count($searches) > 0 ? $errors / count($searches) : 0.0;
    }
}
```

## Complete Example

```php
<?php
# filename: examples/vector-db-demo.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\VectorDB\VectorDBManager;
use App\VectorDB\HybridSearch;
use App\VectorDB\PerformanceMonitor;
use App\RAG\EmbeddingService;
use Anthropic\Anthropic;

// Configuration
$config = [
    'default' => [
        'driver' => 'pinecone',
        'api_key' => getenv('PINECONE_API_KEY'),
        'environment' => getenv('PINECONE_ENVIRONMENT'),
        'index' => 'my-knowledge-base',
        'dimension' => 1536
    ],
    'weaviate' => [
        'driver' => 'weaviate',
        'host' => 'http://localhost:8080',
        'class' => 'Document'
    ]
];

// Initialize services
$manager = new VectorDBManager($config);
$vectorStore = $manager->store('default');

$embeddings = new EmbeddingService(
    apiKey: getenv('OPENAI_API_KEY'),
    provider: 'openai'
);

$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$monitor = new PerformanceMonitor();

// Insert sample vectors
echo "Inserting sample documents...\n";

$documents = [
    "Laravel is a PHP web framework with elegant syntax.",
    "Vector databases store high-dimensional embeddings for semantic search.",
    "Claude is an AI assistant created by Anthropic."
];

$vectors = $embeddings->embedTexts($documents);

$result = $vectorStore->insert($vectors, [
    'ids' => ['doc1', 'doc2', 'doc3'],
    'items' => [
        ['content' => $documents[0], 'category' => 'php'],
        ['content' => $documents[1], 'category' => 'database'],
        ['content' => $documents[2], 'category' => 'ai']
    ]
]);

echo "✓ Inserted {$result->count} vectors\n\n";

// Perform searches with monitoring
$queries = [
    "What is Laravel?",
    "How do vector databases work?",
    "Tell me about Claude"
];

foreach ($queries as $query) {
    echo "Query: {$query}\n";

    $queryVector = $embeddings->embedQuery($query);

    $searchResult = $monitor->trackSearch(
        fn() => $vectorStore->search($queryVector, limit: 3),
        ['query' => $query]
    );

    echo "Results:\n";
    foreach ($searchResult->results as $i => $result) {
        echo "  " . ($i + 1) . ". Score: " . number_format($result['score'], 4) . "\n";
        echo "     " . ($result['metadata']['content'] ?? 'N/A') . "\n";
    }
    echo "\n";
}

// Show performance stats
echo "--- Performance Statistics ---\n";
$stats = $monitor->getStats();
foreach ($stats as $metric => $value) {
    echo sprintf("%-20s: %s\n", $metric, is_float($value) ? number_format($value, 2) : $value);
}

// Show vector store stats
echo "\n--- Vector Store Statistics ---\n";
$storeStats = $vectorStore->getStats();
print_r($storeStats);
```

## Data Structures

```php
<?php
# filename: src/VectorDB/DataStructures.php
declare(strict_types=1);

namespace App\VectorDB;

readonly class InsertResult
{
    public function __construct(
        public int $count,
        public array $ids
    ) {}
}

readonly class SearchResult
{
    public function __construct(
        public array $results,
        public int $count
    ) {}
}

readonly class DeleteResult
{
    public function __construct(
        public bool $success,
        public int $count
    ) {}
}
```

## Key Takeaways

- ✓ Vector databases enable semantic search at scale
- ✓ Pinecone, Weaviate, and Milvus each have unique strengths
- ✓ Abstraction layers enable switching between providers
- ✓ Hybrid search combines vector and keyword approaches
- ✓ Batch operations improve performance significantly
- ✓ Proper indexing strategies reduce search latency
- ✓ Namespaces/collections organize vectors logically
- ✓ Metadata filtering enables precise retrieval
- ✓ Performance monitoring identifies bottlenecks
- ✓ Migration tools enable vendor flexibility

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="32"
  label="You've mastered vector database integration in PHP!"
/>

---

Continue to [Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems) to learn how to orchestrate multiple AI agents.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 32 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-32)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-32
composer install
export PINECONE_API_KEY="your-key-here"
export OPENAI_API_KEY="your-key-here"
php examples/vector-db-demo.php
```
