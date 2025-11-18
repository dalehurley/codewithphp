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

## What You'll Build

By the end of this chapter, you will have created:

- **Vector database abstraction layer** enabling seamless switching between providers
- **Pinecone integration** with batch operations, namespaces, and metadata filtering
- **Weaviate integration** using GraphQL queries for semantic search
- **Hybrid search system** combining vector similarity with keyword matching using Reciprocal Rank Fusion
- **Vector database manager** for multi-provider configuration and migration
- **Performance monitoring** tracking search latency, memory usage, and error rates
- **Production-ready patterns** for indexing, batching, and optimization

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 31** (RAG implementation)
- ✓ **Vector embedding understanding** for similarity search
- ✓ **Database optimization knowledge** for performance tuning
- ✓ **Cloud service experience** for deployment

**Estimated Time**: 120-150 minutes

## Objectives

By completing this chapter, you will:

- Understand vector database architecture and when to use different providers
- Implement abstraction layers for vendor-agnostic vector operations
- Integrate Pinecone, Weaviate, and other vector databases in PHP
- Build hybrid search combining semantic and keyword matching
- Optimize indexing strategies for production workloads
- Monitor and analyze vector database performance
- Handle batch operations, namespaces, and metadata filtering efficiently

## Quick Start

Get started with vector databases in under 5 minutes. This example shows a complete workflow from setup to search:

```php
<?php
# filename: examples/quick-start.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\VectorDB\VectorDBManager;
use App\RAG\EmbeddingService;

// Configure Pinecone (or any other provider)
$config = [
    'default' => [
        'driver' => 'pinecone',
        'api_key' => getenv('PINECONE_API_KEY'),
        'environment' => getenv('PINECONE_ENVIRONMENT'),
        'index' => 'quickstart-index',
        'dimension' => 1536
    ]
];

// Initialize services
$manager = new VectorDBManager($config);
$vectorStore = $manager->store('default');
$embeddings = new EmbeddingService(
    apiKey: getenv('OPENAI_API_KEY'),
    provider: 'openai'
);

// 1. Create embeddings for your documents
$documents = [
    "PHP is a popular server-side scripting language.",
    "Vector databases enable semantic search for AI applications.",
    "Claude is an AI assistant created by Anthropic."
];

$vectors = $embeddings->embedTexts($documents);

// 2. Insert vectors into database
$result = $vectorStore->insert($vectors, [
    'ids' => ['doc1', 'doc2', 'doc3'],
    'items' => [
        ['content' => $documents[0]],
        ['content' => $documents[1]],
        ['content' => $documents[2]]
    ]
]);

echo "✓ Inserted {$result->count} vectors\n";

// 3. Search for similar content
$query = "What is PHP?";
$queryVector = $embeddings->embedQuery($query);

$searchResults = $vectorStore->search($queryVector, limit: 2);

echo "\nSearch results for: {$query}\n";
foreach ($searchResults->results as $i => $result) {
    echo ($i + 1) . ". Score: " . number_format($result['score'], 3) . "\n";
    echo "   " . ($result['metadata']['content'] ?? 'N/A') . "\n";
}
```

**Expected Output:**
```
✓ Inserted 3 vectors

Search results for: What is PHP?
1. Score: 0.892
   PHP is a popular server-side scripting language.
2. Score: 0.756
   Vector databases enable semantic search for AI applications.
```

This quick start demonstrates the core workflow: create embeddings, insert vectors, and search. The rest of this chapter covers production-ready patterns, error handling, and optimization.

## Understanding Distance Metrics

Vector databases use different distance metrics to measure similarity between vectors. Choosing the right metric is crucial for search quality and performance.

### Distance Metrics Explained

**Cosine Similarity** (Most Common)
- Measures angle between vectors, ignoring magnitude
- Range: -1 to 1 (or 0 to 1 for normalized vectors)
- Best for: Text embeddings, semantic search
- Why: Word embeddings are normalized, magnitude doesn't matter
- Performance: Fast to compute

**Euclidean Distance** (L2)
- Measures straight-line distance in n-dimensional space
- Range: 0 to infinity
- Best for: Spatial data, image embeddings, geographic coordinates
- Why: Preserves actual distances in feature space
- Performance: Slower than cosine, more accurate for spatial data

**Dot Product (Inner Product)**
- Simple multiplication and sum of vector components
- Range: -∞ to +∞
- Best for: Pre-normalized vectors, neural network embeddings
- Why: Very fast, requires normalized vectors for fair comparison
- Performance: Fastest option

### Practical Code Examples

```php
<?php
# filename: src/VectorDB/SimilarityCalculator.php
declare(strict_types=1);

namespace App\VectorDB;

class SimilarityCalculator
{
    /**
     * Calculate cosine similarity between two vectors
     */
    public static function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        foreach ($a as $i => $value) {
            $dotProduct += $value * $b[$i];
            $magnitudeA += $value * $value;
            $magnitudeB += $b[$i] * $b[$i];
        }

        if ($magnitudeA === 0.0 || $magnitudeB === 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($magnitudeA) * sqrt($magnitudeB));
    }

    /**
     * Calculate euclidean distance between two vectors
     */
    public static function euclideanDistance(array $a, array $b): float
    {
        $sumSquares = 0.0;

        foreach ($a as $i => $value) {
            $diff = $value - $b[$i];
            $sumSquares += $diff * $diff;
        }

        return sqrt($sumSquares);
    }

    /**
     * Calculate dot product (requires normalized vectors)
     */
    public static function dotProduct(array $a, array $b): float
    {
        $product = 0.0;

        foreach ($a as $i => $value) {
            $product += $value * $b[$i];
        }

        return $product;
    }

    /**
     * Normalize a vector to unit length
     */
    public static function normalize(array $vector): array
    {
        $magnitude = sqrt(array_reduce($vector, fn($sum, $v) => $sum + $v * $v, 0.0));

        if ($magnitude === 0.0) {
            return $vector;
        }

        return array_map(fn($v) => $v / $magnitude, $vector);
    }

    /**
     * Compare different metrics on same vectors
     */
    public static function compareMetrics(array $query, array $vector): array
    {
        // Normalize for dot product comparison
        $queryNorm = self::normalize($query);
        $vectorNorm = self::normalize($vector);

        return [
            'cosine_similarity' => self::cosineSimilarity($query, $vector),
            'euclidean_distance' => self::euclideanDistance($query, $vector),
            'dot_product' => self::dotProduct($queryNorm, $vectorNorm),
            'normalized' => true
        ];
    }
}
```

### Choosing the Right Metric

```php
<?php
# filename: examples/metric-selection.php

use App\VectorDB\SimilarityCalculator;

// Example vectors from an embedding model
$queryVector = [0.1, 0.2, 0.3, 0.4, 0.5];
$documentVector = [0.15, 0.25, 0.35, 0.45, 0.55];

// Calculate all metrics
$results = SimilarityCalculator::compareMetrics($queryVector, $documentVector);

echo "Similarity Metrics Comparison:\n";
echo "Cosine Similarity:   " . number_format($results['cosine_similarity'], 4) . "\n";
echo "Euclidean Distance:  " . number_format($results['euclidean_distance'], 4) . "\n";
echo "Dot Product (norm):  " . number_format($results['dot_product'], 4) . "\n";

// Decision tree for metric selection:
// 1. Text embeddings (OpenAI, Voyage, etc.) → Use COSINE
// 2. Spatial/geographic data → Use EUCLIDEAN
// 3. Pre-normalized vectors for speed → Use DOT PRODUCT
// 4. Image embeddings → Usually COSINE
// 5. Custom embeddings → Test all three metrics
```

## Choosing a Vector Database

Different vector databases excel in different scenarios:

**Pinecone** — Best for:
- Managed cloud deployments with minimal operations overhead
- High-scale production applications (millions of vectors)
- Simple REST API integration
- Automatic scaling and maintenance

**Weaviate** — Best for:
- Self-hosted deployments with full control
- GraphQL-based queries and complex filtering
- Multi-modal data (text, images, etc.)
- Built-in vectorization with various models

**Milvus** — Best for:
- Open-source self-hosted solutions
- High-performance requirements
- Custom deployment configurations
- Cost-effective at scale

**Qdrant** — Best for:
- Fast local development and testing
- Payload filtering and hybrid search
- RESTful API simplicity
- Docker-based deployments

The abstraction layer in this chapter lets you switch between providers as your needs evolve.

## Index Types & Strategies

Vector databases use different indexing algorithms to optimize search speed and accuracy:

### HNSW (Hierarchical Navigable Small World) — Most Popular
- **Accuracy**: Very high (~95%+)
- **Speed**: Fast (~1-10ms for millions of vectors)
- **Memory**: Moderate overhead (~2-3x vector size)
- **Use when**: You need both speed and accuracy (most common choice)
- **Providers**: Pinecone, Weaviate, Qdrant all support HNSW

### IVF (Inverted File Index)
- **Accuracy**: Good (~90%+)
- **Speed**: Very fast (~0.1-1ms)
- **Memory**: Low overhead (~0.5x vector size)
- **Use when**: You need speed over perfect accuracy or have very large datasets

### Flat (Exact Search)
- **Accuracy**: Perfect (100%)
- **Speed**: Slow (O(n) complexity)
- **Memory**: No overhead
- **Use when**: You have small datasets or need 100% accuracy

### Index Configuration Best Practices

```php
<?php
// Pinecone Index Configuration
$pineconeConfig = [
    'name' => 'my-index',
    'dimension' => 1536,           // Match embedding dimension
    'metric' => 'cosine',           // Distance metric
    'pods' => 2,                    // Number of pods for scale
    'replicas' => 1,                // Replication for HA
    'pod_type' => 'p1.x1',         // Pod type
    'metadata_config' => [
        'indexed' => ['category', 'date']  // Index these fields for filtering
    ]
];

// Weaviate Index Configuration
$weaviateConfig = [
    'class' => 'Document',
    'vectorizer' => 'none',         // Don't vectorize (we do it)
    'vectorIndexType' => 'hnsw',   // Use HNSW
    'vectorIndexConfig' => [
        'distance' => 'cosine',
        'ef' => 128,                // Efactor for HNSW
        'efConstruction' => 200,   // Bigger = better but slower to build
        'maxConnections' => 16      // Max connections per node
    ],
    'properties' => [
        [
            'name' => 'content',
            'dataType' => ['text'],
            'indexInverted' => true  // Enable full-text search
        ]
    ]
];
```

## Vector Database Abstraction Layer

To enable flexibility and vendor-agnostic code, we'll start by defining a common interface that all vector database implementations must follow. This abstraction layer allows you to:

- **Switch providers** without changing application code
- **Test implementations** independently
- **Support multiple providers** simultaneously
- **Standardize operations** across different databases

The `VectorStore` interface defines the core operations: insert, search, update, delete, index management, and statistics. Each provider implementation (Pinecone, Weaviate, Milvus, Qdrant) implements this interface, ensuring consistent behavior across providers.

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

Pinecone is a fully managed vector database service that simplifies deployment and scaling. It provides a REST API for vector operations and automatically handles indexing, replication, and scaling.

**Key Features:**
- Fully managed cloud service with automatic scaling
- Simple REST API integration
- Namespace support for data organization
- Metadata filtering for precise queries
- Batch operations for efficient insertions

The implementation below shows how to integrate Pinecone with proper error handling, batch processing, and metadata filtering.

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
        $insertedIds = [];

        foreach ($batches as $batch) {
            try {
                $response = $this->client->post('/vectors/upsert', [
                    'json' => [
                        'vectors' => $batch,
                        'namespace' => $metadata['namespace'] ?? ''
                    ],
                    'timeout' => 30
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException(
                        "Pinecone upsert failed with status: {$response->getStatusCode()}"
                    );
                }

                $data = json_decode($response->getBody()->getContents(), true);
                $batchCount = $data['upsertedCount'] ?? count($batch);
                $totalInserted += $batchCount;
                $insertedIds = array_merge($insertedIds, array_column($batch, 'id'));

            } catch (\GuzzleHttp\Exception\RequestException $e) {
                error_log("Pinecone batch insert failed: " . $e->getMessage());
                // Continue with next batch, but log the error
                // In production, you might want to retry or throw
                throw new \RuntimeException(
                    "Failed to insert vectors to Pinecone: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        return new InsertResult(
            count: $totalInserted,
            ids: $insertedIds
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
        // Validate vector dimension
        $vectorDimension = count($queryVector);
        if ($vectorDimension !== $this->dimension) {
            throw new \InvalidArgumentException(
                "Query vector dimension ({$vectorDimension}) doesn't match index dimension ({$this->dimension})"
            );
        }

        $payload = [
            'vector' => $queryVector,
            'topK' => min($limit, 10000), // Pinecone max is 10000
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

        try {
            $response = $this->client->post('/query', [
                'json' => $payload,
                'timeout' => 30
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "Pinecone query failed with status: {$response->getStatusCode()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['matches'])) {
                return new SearchResult(results: [], count: 0);
            }

            $results = [];
            foreach ($data['matches'] as $match) {
                $results[] = [
                    'id' => $match['id'],
                    'score' => $match['score'] ?? 0.0,
                    'metadata' => $match['metadata'] ?? []
                ];
            }

            return new SearchResult(
                results: $results,
                count: count($results)
            );

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \RuntimeException(
                "Failed to search Pinecone: " . $e->getMessage(),
                0,
                $e
            );
        }
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

Weaviate is an open-source vector database that uses GraphQL for queries and supports self-hosted deployments. It's particularly powerful for complex filtering and multi-modal data.

**Key Features:**
- GraphQL-based query interface
- Self-hosted or cloud deployment options
- Built-in vectorization with various models
- Complex filtering with WHERE clauses
- Multi-modal support (text, images, etc.)

The implementation below demonstrates GraphQL query construction, batch operations, and schema management.

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

        try {
            // Batch insert
            $response = $this->client->post('/v1/batch/objects', [
                'json' => [
                    'objects' => $objects
                ],
                'timeout' => 60 // Weaviate batch operations can take longer
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    "Weaviate batch insert failed with status: {$response->getStatusCode()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data)) {
                throw new \RuntimeException("Invalid response from Weaviate batch insert");
            }

            $inserted = 0;
            $ids = [];
            $errors = [];

            foreach ($data as $result) {
                if (isset($result['result']['status']) && $result['result']['status'] === 'SUCCESS') {
                    $inserted++;
                    $ids[] = $result['id'] ?? null;
                } else {
                    $errors[] = $result['result']['errors'] ?? ['Unknown error'];
                }
            }

            if (!empty($errors) && $inserted === 0) {
                throw new \RuntimeException(
                    "Weaviate batch insert failed: " . json_encode($errors)
                );
            }

            return new InsertResult(
                count: $inserted,
                ids: array_filter($ids) // Remove null IDs
            );

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \RuntimeException(
                "Failed to insert vectors to Weaviate: " . $e->getMessage(),
                0,
                $e
            );
        }
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

Hybrid search combines vector similarity search with traditional keyword matching to improve relevance. This approach uses Reciprocal Rank Fusion (RRF) to merge results from both search methods.

**Why Hybrid Search?**
- Vector search excels at semantic similarity but may miss exact keyword matches
- Keyword search finds exact matches but misses semantic relationships
- Combining both provides better overall relevance
- RRF algorithm balances both approaches without requiring score normalization

The implementation below shows how to fuse vector and keyword search results using RRF.

```php
<?php
# filename: src/VectorDB/HybridSearch.php
declare(strict_types=1);

namespace App\VectorDB;

use ClaudePhp\ClaudePhp;

class HybridSearch
{
    public function __construct(
        private VectorStore $vectorStore,
        private ClaudePhp $claude,
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
use ClaudePhp\ClaudePhp;

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

$claude = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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

## Exercises

### Exercise 1: Implement Milvus Store

**Goal**: Complete the vector database abstraction by implementing a Milvus store

Create a `MilvusStore` class that implements the `VectorStore` interface. Milvus uses gRPC for communication, so you'll need to use a gRPC client library.

Requirements:

- Implement all `VectorStore` interface methods
- Use Milvus PHP SDK or gRPC client for communication
- Support collection creation and management
- Handle batch insertions efficiently
- Implement similarity search with distance metrics

**Validation**: Test your implementation:

```php
$milvusStore = new MilvusStore(
    host: 'localhost',
    port: 19530,
    collectionName: 'test_collection'
);

$vectors = [[0.1, 0.2, 0.3], [0.4, 0.5, 0.6]];
$result = $milvusStore->insert($vectors, [
    'ids' => ['vec1', 'vec2'],
    'items' => [['text' => 'doc1'], ['text' => 'doc2']]
]);

// Should insert 2 vectors successfully
assert($result->count === 2);
```

### Exercise 2: Implement Qdrant Store

**Goal**: Add Qdrant support to your vector database manager

Qdrant is another popular open-source vector database. Implement a `QdrantStore` class following the same pattern as Pinecone and Weaviate.

Requirements:

- Implement REST API client for Qdrant
- Support collection creation with custom configuration
- Handle payload filtering for metadata queries
- Implement batch operations
- Add support for different distance metrics (cosine, euclidean, dot)

**Validation**: Verify collection creation and search work:

```php
$qdrantStore = new QdrantStore(
    host: 'http://localhost:6333',
    collectionName: 'documents'
);

$qdrantStore->createIndex('documents', [
    'vector_size' => 1536,
    'distance' => 'Cosine'
]);

// Should create collection successfully
```

### Exercise 3: Optimize Batch Insertions

**Goal**: Improve insertion performance with intelligent batching

Create a `BatchInserter` class that optimizes vector insertions by:

- Automatically determining optimal batch size based on vector dimensions
- Implementing retry logic with exponential backoff
- Tracking insertion progress and providing callbacks
- Handling partial failures gracefully

Requirements:

- Support configurable batch sizes (default: 100)
- Implement retry mechanism for failed batches
- Provide progress callbacks for long-running insertions
- Return detailed results including failed items

**Validation**: Test with large datasets:

```php
$inserter = new BatchInserter($vectorStore);
$inserter->setBatchSize(50);
$inserter->setRetryAttempts(3);

$result = $inserter->insert($largeVectorSet, [
    'onProgress' => function($inserted, $total) {
        echo "Progress: {$inserted}/{$total}\n";
    }
]);

// Should handle 1000+ vectors efficiently
assert($result->successCount > 0);
assert($result->failedCount === 0);
```

### Exercise 4: Build Vector Database Benchmark Tool

**Goal**: Create a performance testing tool for comparing vector databases

Build a `VectorDBBenchmark` class that tests:

- Insertion throughput (vectors per second)
- Search latency (p50, p95, p99)
- Memory usage during operations
- Concurrent search performance
- Accuracy of similarity search results

Requirements:

- Generate test datasets of various sizes
- Measure and report performance metrics
- Compare multiple vector stores side-by-side
- Export results to JSON/CSV for analysis

**Validation**: Run benchmark and verify metrics:

```php
$benchmark = new VectorDBBenchmark();
$results = $benchmark->compare([
    'pinecone' => $pineconeStore,
    'weaviate' => $weaviateStore
], [
    'vector_count' => 10000,
    'dimension' => 1536,
    'queries' => 100
]);

// Should return detailed performance comparison
assert(isset($results['pinecone']['avg_search_latency_ms']));
assert(isset($results['weaviate']['avg_search_latency_ms']));
```

## Best Practices

### Vector Database Selection

- **Start with Pinecone** for quick prototyping and managed infrastructure
- **Choose Weaviate** when you need GraphQL queries or self-hosting
- **Use Milvus** for maximum performance and cost control at scale
- **Consider Qdrant** for local development and simple REST APIs
- **Build abstraction layers** to switch providers as needs evolve

### Index Configuration

- **Dimension consistency**: Always use the same embedding dimension across your system
- **Distance metrics**: Use cosine similarity for text embeddings, euclidean for spatial data
- **Index size**: Pre-allocate capacity to avoid expensive resizing operations
- **Replication**: Configure replicas for high availability in production
- **Namespaces**: Use namespaces to partition data logically (by tenant, date, category)

### Batch Operations

- **Optimal batch size**: Pinecone recommends 100 vectors per batch
- **Parallel batching**: Process multiple batches concurrently when possible
- **Error handling**: Implement retry logic with exponential backoff for failed batches
- **Progress tracking**: Monitor batch insertion progress for large datasets
- **Memory management**: Process large datasets in chunks to avoid memory exhaustion

### Search Optimization

- **Limit results**: Only retrieve the number of results you actually need
- **Metadata filtering**: Use filters to narrow search space before vector comparison
- **Namespace isolation**: Search within specific namespaces to improve performance
- **Hybrid search**: Combine vector and keyword search for better relevance
- **Re-ranking**: Use Claude or other models to re-rank top results for accuracy

### Performance Monitoring

- **Track latency**: Monitor p50, p95, p99 search latencies
- **Monitor errors**: Track error rates and types (rate limits, timeouts, etc.)
- **Memory usage**: Monitor memory consumption during batch operations
- **Throughput**: Measure vectors inserted per second and searches per second
- **Cost tracking**: Monitor API usage and optimize for cost efficiency

### Production Deployment

- **Error handling**: Implement comprehensive error handling with retries and fallbacks
- **Rate limiting**: Respect API rate limits and implement client-side throttling
- **Connection pooling**: Reuse HTTP connections for better performance
- **Caching**: Cache frequently accessed vectors and search results
- **Monitoring**: Set up alerts for error rates, latency spikes, and capacity limits
- **Backup strategy**: Implement regular backups or replication for critical data
- **Security**: Use environment variables for API keys, enable TLS, validate inputs

### Code Organization

- **Abstraction layers**: Use interfaces to decouple from specific providers
- **Configuration management**: Centralize database configuration
- **Service classes**: Create dedicated service classes for vector operations
- **Error types**: Define custom exception types for better error handling
- **Logging**: Log all operations for debugging and auditing
- **Testing**: Write unit tests for each vector store implementation

## Vector Deduplication & Quality

Duplicate or near-duplicate vectors waste storage and hurt search quality. Implement deduplication strategies:

```php
<?php
# filename: src/VectorDB/DeduplicationService.php
declare(strict_types=1);

namespace App\VectorDB;

class DeduplicationService
{
    private const SIMILARITY_THRESHOLD = 0.99;

    /**
     * Remove duplicate vectors using similarity threshold
     */
    public function deduplicateVectors(array $vectors): array
    {
        $unique = [];
        $similarity = new SimilarityCalculator();

        foreach ($vectors as $vector) {
            $isDuplicate = false;

            foreach ($unique as $existingVector) {
                $similarityScore = $similarity->cosineSimilarity(
                    $vector,
                    $existingVector
                );

                if ($similarityScore >= self::SIMILARITY_THRESHOLD) {
                    $isDuplicate = true;
                    break;
                }
            }

            if (!$isDuplicate) {
                $unique[] = $vector;
            }
        }

        return $unique;
    }

    /**
     * Find duplicate vectors in existing store
     */
    public function findDuplicates(VectorStore $store, float $threshold = 0.98): array
    {
        // This would require scanning all vectors and comparing
        // Implementation depends on your specific use case
        $duplicates = [];
        // ... scanning and comparison logic
        return $duplicates;
    }

    /**
     * Detect near-duplicates using LSH (Locality Sensitive Hashing)
     */
    public function detectNearDuplicatesBuckets(array $vectors, int $buckets = 256): array
    {
        $bucketMap = [];

        foreach ($vectors as $id => $vector) {
            // Hash vector to bucket using simple approach
            $hash = $this->hashVector($vector, $buckets);

            if (!isset($bucketMap[$hash])) {
                $bucketMap[$hash] = [];
            }

            $bucketMap[$hash][] = $id;
        }

        // Return only buckets with multiple vectors (potential duplicates)
        return array_filter($bucketMap, fn($ids) => count($ids) > 1);
    }

    private function hashVector(array $vector, int $buckets): int
    {
        $sum = array_sum($vector);
        return abs((int)($sum * 1000)) % $buckets;
    }
}
```

## Similarity Threshold Calibration

Understanding and setting appropriate similarity thresholds is crucial:

```php
<?php
# filename: examples/threshold-calibration.php

use App\VectorDB\SimilarityCalculator;

/**
 * Find optimal threshold for your use case
 */
function calibrateThreshold(array $relevantPairs, array $irrelevantPairs): float
{
    $similarity = new SimilarityCalculator();
    $relevantScores = [];
    $irrelevantScores = [];

    // Calculate scores for relevant pairs
    foreach ($relevantPairs as [$vectorA, $vectorB]) {
        $relevantScores[] = $similarity->cosineSimilarity($vectorA, $vectorB);
    }

    // Calculate scores for irrelevant pairs
    foreach ($irrelevantPairs as [$vectorA, $vectorB]) {
        $irrelevantScores[] = $similarity->cosineSimilarity($vectorA, $vectorB);
    }

    // Find threshold that maximizes precision and recall
    $minRelevant = min($relevantScores);
    $maxIrrelevant = max($irrelevantScores);

    // Optimal threshold is usually between these values
    $optimalThreshold = ($minRelevant + $maxIrrelevant) / 2;

    echo "Relevant scores range: " . min($relevantScores) . " - " . max($relevantScores) . "\n";
    echo "Irrelevant scores range: " . min($irrelevantScores) . " - " . max($irrelevantScores) . "\n";
    echo "Recommended threshold: " . $optimalThreshold . "\n";

    return $optimalThreshold;
}

// Example usage:
$relevant = [
    [[0.1, 0.2, 0.3], [0.11, 0.21, 0.31]],
    [[0.5, 0.6, 0.7], [0.51, 0.61, 0.71]],
];

$irrelevant = [
    [[0.1, 0.2, 0.3], [0.9, 0.8, 0.7]],
    [[0.2, 0.2, 0.2], [0.7, 0.7, 0.7]],
];

$threshold = calibrateThreshold($relevant, $irrelevant);
```

## Monitoring & Metrics

Implement comprehensive monitoring for production vector databases:

```php
<?php
# filename: src/VectorDB/VectorDBMetrics.php
declare(strict_types=1);

namespace App\VectorDB;

class VectorDBMetrics
{
    private array $metrics = [];

    /**
     * Track search quality metrics
     */
    public function trackSearchQuality(
        array $queryVector,
        array $results,
        ?string $groundTruth = null
    ): void {
        $scores = array_column($results, 'score');

        $this->metrics['search_quality'][] = [
            'timestamp' => microtime(true),
            'result_count' => count($results),
            'avg_score' => array_sum($scores) / count($scores),
            'min_score' => min($scores),
            'max_score' => max($scores),
            'groundtruth_match' => $groundTruth ? true : false
        ];
    }

    /**
     * Generate health report
     */
    public function generateHealthReport(): array
    {
        $searches = $this->metrics['search_quality'] ?? [];

        if (empty($searches)) {
            return ['status' => 'no_data'];
        }

        $avgScores = array_column($searches, 'avg_score');
        $resultCounts = array_column($searches, 'result_count');

        return [
            'status' => 'healthy',
            'total_searches' => count($searches),
            'avg_result_quality' => array_sum($avgScores) / count($avgScores),
            'avg_results_returned' => array_sum($resultCounts) / count($resultCounts),
            'quality_trend' => $this->calculateTrend(array_slice($avgScores, -10))
        ];
    }

    private function calculateTrend(array $scores): string
    {
        if (count($scores) < 2) {
            return 'insufficient_data';
        }

        $first_half = array_sum(array_slice($scores, 0, (int)(count($scores) / 2))) / (count($scores) / 2);
        $second_half = array_sum(array_slice($scores, (int)(count($scores) / 2))) / (count($scores) / 2);

        if ($second_half > $first_half * 1.05) {
            return 'improving';
        } elseif ($second_half < $first_half * 0.95) {
            return 'degrading';
        }

        return 'stable';
    }
}
```

## Troubleshooting

### Error: "Invalid vector dimension"

**Symptom**: `InvalidArgumentException: Vector dimension mismatch. Expected 1536, got 768`

**Cause**: Vector dimension doesn't match the index configuration. Different embedding models produce different dimensions (OpenAI text-embedding-3-small: 1536, text-embedding-ada-002: 1536, some models: 768).

**Solution**: Ensure consistent embedding dimensions throughout your system:

```php
// Check dimension before insertion
$dimension = count($vector);
if ($dimension !== $expectedDimension) {
    throw new \InvalidArgumentException(
        "Vector dimension mismatch. Expected {$expectedDimension}, got {$dimension}"
    );
}

// Or normalize dimensions
$vector = array_slice($vector, 0, $expectedDimension);
```

### Error: "Rate limit exceeded"

**Symptom**: `429 Too Many Requests` from Pinecone or Weaviate API

**Cause**: Exceeding API rate limits with too many requests per second

**Solution**: Implement rate limiting and exponential backoff:

```php
class RateLimitedVectorStore implements VectorStore
{
    private int $requestCount = 0;
    private float $windowStart = 0;
    private const MAX_REQUESTS_PER_SECOND = 10;

    public function search(...): SearchResult
    {
        $this->throttle();
        // ... perform search
    }

    private function throttle(): void
    {
        $now = microtime(true);
        
        if ($now - $this->windowStart >= 1.0) {
            $this->requestCount = 0;
            $this->windowStart = $now;
        }

        if ($this->requestCount >= self::MAX_REQUESTS_PER_SECOND) {
            $sleepTime = 1.0 - ($now - $this->windowStart);
            usleep((int)($sleepTime * 1000000));
            $this->requestCount = 0;
            $this->windowStart = microtime(true);
        }

        $this->requestCount++;
    }
}
```

### Issue: Slow Search Performance

**Symptom**: Vector searches taking several seconds, especially with large indexes

**Cause**: Inefficient indexing strategy or too many vectors in single namespace

**Solution**: Optimize indexing and use namespaces:

```php
// Use namespaces to partition data
$vectorStore->insert($vectors, [
    'namespace' => 'products_2024',
    'ids' => $ids,
    'items' => $metadata
]);

// Search within specific namespace
$results = $vectorStore->search(
    queryVector: $queryVector,
    limit: 10,
    filters: ['namespace' => 'products_2024']
);

// Optimize index configuration
$vectorStore->createIndex('documents', [
    'metric' => 'cosine',
    'pods' => 2, // Increase pods for better performance
    'replicas' => 1
]);
```

### Issue: Memory Exhaustion During Batch Insertion

**Symptom**: `Fatal error: Allowed memory size exhausted` when inserting large batches

**Cause**: Loading all vectors into memory at once

**Solution**: Process vectors in smaller chunks and use generators:

```php
function insertLargeDataset(VectorStore $store, iterable $vectors): void
{
    $batch = [];
    $batchSize = 100;

    foreach ($vectors as $vector) {
        $batch[] = $vector;

        if (count($batch) >= $batchSize) {
            $store->insert($batch);
            $batch = [];
            
            // Free memory
            gc_collect_cycles();
        }
    }

    // Insert remaining vectors
    if (!empty($batch)) {
        $store->insert($batch);
    }
}
```

### Issue: Inaccurate Search Results

**Symptom**: Search returns irrelevant results even with high similarity scores

**Cause**: Embedding quality issues or incorrect distance metric

**Solution**: Verify embeddings and use appropriate metrics:

```php
// Ensure high-quality embeddings
$embeddings = $embeddingService->embedTexts($texts, [
    'model' => 'text-embedding-3-large', // Use better model
    'dimensions' => 3072 // Higher dimensions for better accuracy
]);

// Normalize vectors for cosine similarity
function normalizeVector(array $vector): array
{
    $magnitude = sqrt(array_sum(array_map(fn($x) => $x * $x, $vector)));
    return array_map(fn($x) => $x / $magnitude, $vector);
}

// Use appropriate distance metric
// Cosine: Best for text embeddings
// Euclidean: Best for spatial data
// Dot product: Fast but requires normalized vectors
```

### Issue: Weaviate GraphQL Query Errors

**Symptom**: `GraphQL error: Unknown field` or syntax errors in GraphQL queries

**Cause**: Incorrect GraphQL query syntax or missing fields in schema

**Solution**: Validate GraphQL queries and ensure schema matches:

```php
// Validate query before execution
private function validateGraphQLQuery(string $query): bool
{
    // Use a GraphQL validator library or test query
    try {
        $testResponse = $this->client->post('/v1/graphql', [
            'json' => ['query' => $query]
        ]);
        return $testResponse->getStatusCode() === 200;
    } catch (\Exception $e) {
        error_log("GraphQL validation failed: " . $e->getMessage());
        return false;
    }
}

// Ensure schema properties exist
$vectorStore->createIndex('Document', [
    'properties' => [
        ['name' => 'content', 'dataType' => ['text']],
        ['name' => 'category', 'dataType' => ['string']],
        ['name' => 'created_at', 'dataType' => ['date']]
    ]
]);
```

## Wrap-up

You've successfully mastered vector database integration in PHP! Here's what you accomplished:

- ✓ **Understood distance metrics** (cosine, euclidean, dot product) and when to use each
- ✓ **Learned index strategies** (HNSW, IVF, Flat) with speed/accuracy trade-offs
- ✓ **Built abstraction layer** enabling seamless switching between vector database providers
- ✓ **Integrated Pinecone** with batch operations, namespaces, and metadata filtering
- ✓ **Integrated Weaviate** using GraphQL for semantic search and object management
- ✓ **Implemented hybrid search** combining vector similarity with keyword matching
- ✓ **Created vector database manager** for multi-provider configuration and migration
- ✓ **Built performance monitoring** tracking search latency, memory usage, and error rates
- ✓ **Optimized batch operations** for efficient large-scale vector insertions
- ✓ **Handled metadata filtering** for precise retrieval and organization
- ✓ **Implemented namespace support** for logical data partitioning
- ✓ **Detected and removed duplicate vectors** using similarity thresholds and LSH
- ✓ **Calibrated similarity thresholds** for optimal precision and recall
- ✓ **Created comprehensive monitoring** for search quality and system health
- ✓ **Created production-ready patterns** for indexing, error handling, and optimization

Vector databases are essential infrastructure for AI applications requiring semantic search. By mastering multiple providers, understanding similarity metrics, and building abstraction layers, you can choose the best database for each use case while maintaining flexibility to switch as requirements evolve.

## Key Takeaways

- ✓ Vector databases enable semantic search at scale
- ✓ Pinecone, Weaviate, and Milvus each have unique strengths
- ✓ Abstraction layers enable switching between providers
- ✓ Distance metrics (cosine, euclidean, dot product) dramatically affect results
- ✓ Index types (HNSW, IVF, Flat) represent speed/accuracy trade-offs
- ✓ Hybrid search combines vector and keyword approaches
- ✓ Batch operations improve performance significantly
- ✓ Proper indexing strategies reduce search latency
- ✓ Namespaces/collections organize vectors logically
- ✓ Metadata filtering enables precise retrieval
- ✓ Vector deduplication maintains data quality
- ✓ Similarity threshold calibration ensures accurate results
- ✓ Comprehensive monitoring identifies quality issues
- ✓ Performance monitoring identifies bottlenecks
- ✓ Migration tools enable vendor flexibility
- ✓ Vector normalization is critical for cosine similarity

## Further Reading

- [Pinecone Documentation](https://docs.pinecone.io) — Official Pinecone API reference and best practices
- [Weaviate Documentation](https://weaviate.io/developers/weaviate) — Weaviate GraphQL API and schema design
- [Milvus Documentation](https://milvus.io/docs) — Milvus architecture and performance tuning
- [Vector Database Comparison](https://www.pinecone.io/learn/vector-database/) — Comparing popular vector databases
- [Semantic Search Best Practices](https://www.pinecone.io/learn/semantic-search/) — Optimizing semantic search performance
- [Hybrid Search Guide](https://www.pinecone.io/learn/hybrid-search/) — Combining vector and keyword search
- [Chapter 31: RAG](/series/claude-php-developers/chapters/31-retrieval-augmented-generation) — Building RAG systems with vector databases
- [Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems) — Using vector databases in agent workflows

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
