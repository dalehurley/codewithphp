---
title: "31: Retrieval Augmented Generation (RAG)"
description: "Build production-ready RAG systems that combine Claude's intelligence with your private knowledge base through semantic search, intelligent chunking, and context-aware retrieval."
sidebar:
  label: "31: Retrieval Augmented Generation (RAG)"
  order: 31
  badge:
    text: Expert
    variant: danger
---
![31: Retrieval Augmented Generation (RAG)](/images/claude-php/chapter-31-hero-full.webp)


# Chapter 31: Retrieval Augmented Generation (RAG)

## Overview

Retrieval Augmented Generation (RAG) extends Claude's capabilities by grounding its responses in your private knowledge base. Instead of relying solely on Claude's training data, RAG retrieves relevant context from your documents, databases, or APIs before generating responses.

This chapter teaches you to build production-ready RAG systems with intelligent document chunking, semantic search, relevance ranking, and context optimization. You'll learn to handle everything from simple document Q&A to complex multi-source knowledge synthesis.

## What You'll Build

By the end of this chapter, you will have created:

- **Complete RAG Pipeline** — A production-ready system that ingests documents, creates semantic chunks, performs intelligent retrieval, and generates contextual responses
- **Intelligent Chunking Strategies** — Both semantic and hierarchical chunking implementations that preserve context
- **Embedding Service** — Multi-provider embedding generation with batching and error handling
- **Retrieval Engine** — Vector search with Claude-powered re-ranking for improved relevance
- **Context Optimizer** — Token-aware context optimization with deduplication and hierarchical merging
- **Document Processor** — Text extraction from multiple formats (PDF, Markdown, HTML)
- **Vector Store** — Simple in-memory vector storage with similarity search capabilities

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 1-15** (Core API usage and structured outputs)
- ✓ **Vector embedding understanding** for semantic search
- ✓ **Semantic search concepts** for similarity matching
- ✓ **Document processing experience** for text extraction

**Estimated Time**: 120-150 minutes

## Objectives

By completing this chapter, you will:

- Understand the RAG architecture and how it extends Claude's capabilities
- Implement intelligent document chunking strategies (semantic and hierarchical)
- Build an embedding service that supports multiple providers
- Create a retrieval engine with re-ranking capabilities
- Optimize context for Claude's token limits while preserving information
- Process documents from various formats (PDF, Markdown, HTML, plain text)
- Store and search vectors efficiently for semantic similarity
- Handle errors gracefully and optimize for production performance

## RAG Architecture Overview

```php
<?php
# filename: src/RAG/RAGPipeline.php
declare(strict_types=1);

namespace App\RAG;


class RAGPipeline
{
    public function __construct(
        private \ClaudePhp\ClaudePhp $claude,
        private DocumentProcessor $processor,
        private ChunkingStrategy $chunker,
        private EmbeddingService $embeddings,
        private VectorStore $vectorStore,
        private RetrievalEngine $retriever,
        private ContextOptimizer $optimizer
    ) {}

    /**
     * Ingest documents into the knowledge base
     */
    public function ingest(string $documentPath, array $metadata = []): IngestResult
    {
        if (!file_exists($documentPath)) {
            throw new \InvalidArgumentException("Document not found: {$documentPath}");
        }

        try {
            // Step 1: Process document
            $document = $this->processor->process($documentPath, $metadata);

            // Step 2: Chunk document intelligently
            $chunks = $this->chunker->chunk($document);

            if (empty($chunks)) {
                throw new \RuntimeException("Document produced no chunks after processing");
            }

            // Step 3: Generate embeddings
            $embeddings = $this->embeddings->embed($chunks);

            if (count($chunks) !== count($embeddings)) {
                throw new \RuntimeException(
                    "Chunk/embedding count mismatch: " . count($chunks) . " chunks, " . count($embeddings) . " embeddings"
                );
            }

            // Step 4: Store in vector database
            $stored = $this->vectorStore->store($chunks, $embeddings, $metadata);

            return new IngestResult(
                documentId: $stored->id,
                chunkCount: count($chunks),
                metadata: array_merge($metadata, [
                    'ingested_at' => date('c'),
                    'chunk_strategy' => get_class($this->chunker),
                    'document_size' => filesize($documentPath)
                ])
            );
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to ingest document {$documentPath}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Query the knowledge base and generate response
     */
    public function query(
        string $question,
        array $options = []
    ): RAGResponse {
        if (empty(trim($question))) {
            throw new \InvalidArgumentException("Question cannot be empty");
        }

        try {
            // Step 1: Generate query embedding
            $queryEmbedding = $this->embeddings->embedQuery($question);

            // Step 2: Retrieve relevant chunks
            $retrievedChunks = $this->retriever->retrieve(
                embedding: $queryEmbedding,
                topK: $options['top_k'] ?? 5,
                filters: $options['filters'] ?? [],
                query: $question
            );

            if (empty($retrievedChunks)) {
                return new RAGResponse(
                    answer: "I couldn't find any relevant information in the knowledge base to answer your question.",
                    sources: [],
                    confidence: 0.0,
                    metadata: [
                        'chunks_retrieved' => 0,
                        'chunks_used' => 0,
                        'tokens_used' => 0,
                        'warning' => 'No relevant chunks found'
                    ]
                );
            }

            // Step 3: Optimize context (re-rank, deduplicate, etc.)
            $optimizedContext = $this->optimizer->optimize(
                chunks: $retrievedChunks,
                query: $question,
                'max_tokens' => $options['max_context_tokens'] ?? 4000
            );

            if (empty($optimizedContext->chunks)) {
                return new RAGResponse(
                    answer: "I couldn't find sufficient context to answer your question.",
                    sources: [],
                    confidence: 0.0,
                    metadata: [
                        'chunks_retrieved' => count($retrievedChunks),
                        'chunks_used' => 0,
                        'tokens_used' => 0,
                        'warning' => 'Context optimization removed all chunks'
                    ]
                );
            }

            // Step 4: Generate response with Claude
            $response = $this->generateResponse(
                question: $question,
                context: $optimizedContext,
                options: $options
            );

            return new RAGResponse(
                answer: $response->content[0]->text,
                sources: $optimizedContext->sources,
                confidence: $optimizedContext->averageScore,
                metadata: [
                    'chunks_retrieved' => count($retrievedChunks),
                    'chunks_used' => count($optimizedContext->chunks),
                    'tokens_used' => $response->usage->inputTokens + $response->usage->outputTokens
                ]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to query knowledge base: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate response using retrieved context
     */
    private function generateResponse(
        string $question,
        OptimizedContext $context,
        array $options
    ): object {
        $contextText = $this->formatContext($context);

        $prompt = <<<PROMPT
Answer the question based on the provided context. If the context doesn't contain enough information to answer the question, say so clearly.

Context:
{$contextText}

Question: {$question}

Instructions:
1. Base your answer solely on the provided context
2. Cite specific sources when making claims
3. If the context is insufficient, explain what information is missing
4. Be precise and factual
5. Include relevant quotes from the context when helpful

Answer:
PROMPT;

        return $this->claude->messages()->create([
            'model' => $options['model'] ?? 'claude-sonnet-4-5-20250929',
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'temperature' => $options['temperature'] ?? 0.2,
            'system' => $this->getRAGSystemPrompt(),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);
    }

    private function formatContext(OptimizedContext $context): string
    {
        $formatted = [];

        foreach ($context->chunks as $i => $chunk) {
            $source = $chunk->metadata['source'] ?? 'Unknown';
            $formatted[] = "Source {$i}: {$source}\n{$chunk->content}\n";
        }

        return implode("\n---\n\n", $formatted);
    }

    private function getRAGSystemPrompt(): string
    {
        return <<<SYSTEM
You are a knowledgeable assistant with access to a curated knowledge base. Your responses must be:

1. Grounded in the provided context
2. Accurate and factual
3. Properly sourced with citations
4. Clear about limitations when context is insufficient

When answering:
- Quote relevant passages from the context
- Reference source numbers (e.g., "According to Source 2...")
- Distinguish between facts from the context and general knowledge
- Admit when you don't have enough information

Never make up information not present in the context.
SYSTEM;
    }
}
```

## Interfaces

```php
<?php
# filename: src/RAG/Chunking/ChunkingStrategy.php
declare(strict_types=1);

namespace App\RAG\Chunking;

use App\RAG\Document;

interface ChunkingStrategy
{
    /**
     * Chunk a document into smaller pieces
     */
    public function chunk(Document $document): array;
}
```

```php
<?php
# filename: src/RAG/VectorStore.php
declare(strict_types=1);

namespace App\RAG;

interface VectorStore
{
    /**
     * Store chunks with their embeddings
     */
    public function store(array $chunks, array $embeddings, array $metadata = []): object;

    /**
     * Search for similar vectors
     */
    public function search(
        array $queryEmbedding,
        int $limit = 10,
        array $filters = []
    ): array;

    /**
     * Get statistics about stored vectors (optional)
     */
    public function getStats(): array;
}
```

## Intelligent Document Chunking

```php
<?php
# filename: src/RAG/Chunking/SemanticChunker.php
declare(strict_types=1);

namespace App\RAG\Chunking;

use App\RAG\Document;
use App\RAG\Chunk;

class SemanticChunker implements ChunkingStrategy
{
    public function __construct(
        private int $targetChunkSize = 512,
        private int $chunkOverlap = 64,
        private float $semanticThreshold = 0.7
    ) {}

    /**
     * Chunk document using semantic boundaries
     */
    public function chunk(Document $document): array
    {
        // Split into sentences first
        $sentences = $this->splitIntoSentences($document->content);

        // Group sentences into semantic chunks
        $chunks = [];
        $currentChunk = [];
        $currentSize = 0;

        foreach ($sentences as $i => $sentence) {
            $sentenceSize = $this->estimateTokenCount($sentence);

            // Check if adding this sentence would exceed target size
            if ($currentSize + $sentenceSize > $this->targetChunkSize && !empty($currentChunk)) {
                // Save current chunk
                $chunks[] = $this->createChunk($currentChunk, $document, count($chunks));

                // Start new chunk with overlap
                $overlapSentences = $this->getOverlapSentences($currentChunk);
                $currentChunk = $overlapSentences;
                $currentSize = array_sum(array_map(
                    fn($s) => $this->estimateTokenCount($s),
                    $currentChunk
                ));
            }

            $currentChunk[] = $sentence;
            $currentSize += $sentenceSize;
        }

        // Add final chunk
        if (!empty($currentChunk)) {
            $chunks[] = $this->createChunk($currentChunk, $document, count($chunks));
        }

        return $chunks;
    }

    private function splitIntoSentences(string $text): array
    {
        // Advanced sentence splitting that handles edge cases
        $text = preg_replace('/([.!?])\s+/', "$1\n", $text);
        $sentences = explode("\n", $text);

        return array_filter(array_map('trim', $sentences));
    }

    private function estimateTokenCount(string $text): int
    {
        // Rough estimate: ~4 characters per token
        return (int)ceil(strlen($text) / 4);
    }

    private function getOverlapSentences(array $sentences): array
    {
        $overlapSentences = [];
        $overlapSize = 0;
        $targetOverlap = $this->chunkOverlap;

        // Take sentences from end until we reach overlap size
        for ($i = count($sentences) - 1; $i >= 0; $i--) {
            $sentenceSize = $this->estimateTokenCount($sentences[$i]);

            if ($overlapSize + $sentenceSize > $targetOverlap) {
                break;
            }

            array_unshift($overlapSentences, $sentences[$i]);
            $overlapSize += $sentenceSize;
        }

        return $overlapSentences;
    }

    private function createChunk(array $sentences, Document $document, int $index): Chunk
    {
        $content = implode(' ', $sentences);

        return new Chunk(
            'content' => $content,
            index: $index,
            tokenCount: $this->estimateTokenCount($content),
            metadata: [
                'document_id' => $document->id,
                'source' => $document->source,
                'chunk_method' => 'semantic',
                'sentence_count' => count($sentences)
            ]
        );
    }
}
```

## Hierarchical Chunking Strategy

```php
<?php
# filename: src/RAG/Chunking/HierarchicalChunker.php
declare(strict_types=1);

namespace App\RAG\Chunking;

use App\RAG\Document;
use App\RAG\Chunk;

class HierarchicalChunker implements ChunkingStrategy
{
    public function __construct(
        private int $parentChunkSize = 2048,
        private int $childChunkSize = 512
    ) {}

    /**
     * Create hierarchical chunks (parent-child relationships)
     */
    public function chunk(Document $document): array
    {
        $chunks = [];

        // Create parent chunks (large sections)
        $sections = $this->splitIntoSections($document->content);

        foreach ($sections as $sectionIndex => $section) {
            // Create parent chunk
            $parentChunk = new Chunk(
                'content' => $section['content'],
                index: count($chunks),
                tokenCount: $this->estimateTokenCount($section['content']),
                metadata: [
                    'document_id' => $document->id,
                    'source' => $document->source,
                    'level' => 'parent',
                    'section_title' => $section['title'] ?? "Section {$sectionIndex}"
                ]
            );

            $chunks[] = $parentChunk;
            $parentId = $parentChunk->metadata['id'] = uniqid('chunk_');

            // Create child chunks from parent
            $childSentences = $this->splitIntoSentences($section['content']);
            $childChunks = $this->createChildChunks(
                sentences: $childSentences,
                parentId: $parentId,
                document: $document,
                startIndex: count($chunks)
            );

            $chunks = array_merge($chunks, $childChunks);
        }

        return $chunks;
    }

    private function splitIntoSections(string $content): array
    {
        $sections = [];

        // Split by markdown headers or double newlines
        $parts = preg_split('/\n#{1,6}\s+(.+)\n|\n\n\n+/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

        $currentSection = ['title' => null, 'content' => ''];

        foreach ($parts as $i => $part) {
            if ($i % 2 === 1) {
                // This is a header
                if (!empty($currentSection['content'])) {
                    $sections[] = $currentSection;
                }
                $currentSection = ['title' => $part, 'content' => ''];
            } else {
                // This is content
                $currentSection['content'] .= $part;
            }
        }

        if (!empty($currentSection['content'])) {
            $sections[] = $currentSection;
        }

        return $sections;
    }

    private function createChildChunks(
        array $sentences,
        string $parentId,
        Document $document,
        int $startIndex
    ): array {
        $chunks = [];
        $currentSentences = [];
        $currentSize = 0;

        foreach ($sentences as $sentence) {
            $sentenceSize = $this->estimateTokenCount($sentence);

            if ($currentSize + $sentenceSize > $this->childChunkSize && !empty($currentSentences)) {
                $chunks[] = $this->createChildChunk(
                    sentences: $currentSentences,
                    parentId: $parentId,
                    document: $document,
                    index: $startIndex + count($chunks)
                );

                $currentSentences = [];
                $currentSize = 0;
            }

            $currentSentences[] = $sentence;
            $currentSize += $sentenceSize;
        }

        if (!empty($currentSentences)) {
            $chunks[] = $this->createChildChunk(
                sentences: $currentSentences,
                parentId: $parentId,
                document: $document,
                index: $startIndex + count($chunks)
            );
        }

        return $chunks;
    }

    private function createChildChunk(
        array $sentences,
        string $parentId,
        Document $document,
        int $index
    ): Chunk {
        $content = implode(' ', $sentences);

        return new Chunk(
            'content' => $content,
            index: $index,
            tokenCount: $this->estimateTokenCount($content),
            metadata: [
                'document_id' => $document->id,
                'source' => $document->source,
                'level' => 'child',
                'parent_id' => $parentId,
                'sentence_count' => count($sentences)
            ]
        );
    }

    private function splitIntoSentences(string $text): array
    {
        $text = preg_replace('/([.!?])\s+/', "$1\n", $text);
        return array_filter(array_map('trim', explode("\n", $text)));
    }

    private function estimateTokenCount(string $text): int
    {
        return (int)ceil(strlen($text) / 4);
    }
}
```

## Embedding Service

```php
<?php
# filename: src/RAG/EmbeddingService.php
declare(strict_types=1);

namespace App\RAG;

use GuzzleHttp\ClaudePhp;
use GuzzleHttp\Exception\RequestException;

class EmbeddingService
{
    private ClaudePhp $client;
    private string $model = 'text-embedding-3-small';

    public function __construct(
        private string $apiKey,
        private string $provider = 'openai' // or 'voyage', 'cohere'
    ) {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key is required for embedding service');
        }

        $this->client = new ClaudePhp([
            'base_uri' => $this->getBaseUri(),
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ]);
    }

    /**
     * Generate embeddings for multiple chunks
     */
    public function embed(array $chunks): array
    {
        $texts = array_map(fn($chunk) => $chunk->content, $chunks);

        return $this->embedTexts($texts);
    }

    /**
     * Generate embedding for a query
     */
    public function embedQuery(string $query): array
    {
        $embeddings = $this->embedTexts([$query]);
        return $embeddings[0];
    }

    /**
     * Generate embeddings for texts
     */
    private function embedTexts(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        // Batch texts to respect API limits
        $batches = array_chunk($texts, 100);
        $allEmbeddings = [];

        foreach ($batches as $batchIndex => $batch) {
            $retries = 3;
            $lastException = null;

            while ($retries > 0) {
                try {
                    $response = $this->client->post('/embeddings', [
                        'json' => [
                            'model' => $this->model,
                            'input' => $batch
                        ]
                    ]);

                    if ($response->getStatusCode() !== 200) {
                        throw new \RuntimeException(
                            "Embedding API returned status {$response->getStatusCode()}"
                        );
                    }

                    $data = json_decode($response->getBody()->getContents(), true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \RuntimeException("Failed to parse embedding response: " . json_last_error_msg());
                    }

                    if (!isset($data['data']) || !is_array($data['data'])) {
                        throw new \RuntimeException("Invalid embedding response structure");
                    }

                    foreach ($data['data'] as $item) {
                        if (!isset($item['embedding']) || !is_array($item['embedding'])) {
                            throw new \RuntimeException("Invalid embedding format in response");
                        }
                        $allEmbeddings[] = $item['embedding'];
                    }

                    break; // Success, exit retry loop
                } catch (RequestException $e) {
                    $lastException = $e;
                    $retries--;

                    if ($retries > 0) {
                        // Exponential backoff: wait 1s, 2s, 4s
                        sleep(pow(2, 3 - $retries));
                    }
                }
            }

            if ($retries === 0 && $lastException) {
                throw new \RuntimeException(
                    "Failed to generate embeddings after 3 retries: " . $lastException->getMessage(),
                    0,
                    $lastException
                );
            }
        }

        return $allEmbeddings;
    }

    private function getBaseUri(): string
    {
        return match($this->provider) {
            'openai' => 'https://api.openai.com/v1',
            'voyage' => 'https://api.voyageai.com/v1',
            'cohere' => 'https://api.cohere.ai/v1',
            default => throw new \InvalidArgumentException("Unknown provider: {$this->provider}")
        };
    }
}
```

## Retrieval Engine with Re-ranking

```php
<?php
# filename: src/RAG/RetrievalEngine.php
declare(strict_types=1);

namespace App\RAG;


class RetrievalEngine
{
    public function __construct(
        private VectorStore $vectorStore,
        private \ClaudePhp\ClaudePhp $claude,
        private bool $enableReranking = true
    ) {}

    /**
     * Retrieve and optionally re-rank relevant chunks
     */
    public function retrieve(
        array $embedding,
        int $topK = 5,
        array $filters = [],
        string $query = ''
    ): array {
        // Initial retrieval from vector store (get more than needed for re-ranking)
        $retrievalCount = $this->enableReranking ? $topK * 3 : $topK;

        $chunks = $this->vectorStore->search(
            embedding: $embedding,
            limit: $retrievalCount,
            filters: $filters
        );

        // Re-rank using Claude if enabled
        if ($this->enableReranking && count($chunks) > $topK) {
            $chunks = $this->rerank($chunks, $topK, $query);
        }

        return array_slice($chunks, 0, $topK);
    }

    /**
     * Re-rank chunks using Claude for better relevance
     */
    private function rerank(array $chunks, int $topK, string $query = ''): array
    {
        // Use Claude to assess relevance
        $chunkTexts = array_map(fn($c) => $c->content, $chunks);

        $prompt = <<<PROMPT
Rank these text chunks by relevance to the query: "{$query}"

Return a JSON array of indices (0-based) ordered from most to least relevant.

Chunks:

PROMPT;

        foreach ($chunkTexts as $i => $text) {
            $preview = substr($text, 0, 200);
            $prompt .= "\n[$i]: {$preview}...\n";
        }

        $prompt .= "\nReturn ONLY a JSON array of indices, like: [2, 0, 5, 1, 3, 4]";

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001', // Use fast model for re-ranking
            'max_tokens' => 256,
            'temperature' => 0.1,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\[[\d,\s]+\]/', $jsonText, $matches)) {
            $indices = json_decode($matches[0], true);

            // Reorder chunks based on Claude's ranking
            $reranked = [];
            foreach ($indices as $index) {
                if (isset($chunks[$index])) {
                    $reranked[] = $chunks[$index];
                }
            }

            return $reranked;
        }

        // Fallback to original order if re-ranking fails
        return $chunks;
    }
}
```

## Context Optimizer

```php
<?php
# filename: src/RAG/ContextOptimizer.php
declare(strict_types=1);

namespace App\RAG;

class ContextOptimizer
{
    public function __construct(
        private int $maxContextTokens = 4000
    ) {}

    /**
     * Optimize retrieved chunks for context window
     */
    public function optimize(
        array $chunks,
        string $query,
        int $maxTokens = null
    ): OptimizedContext {
        $maxTokens = $maxTokens ?? $this->maxContextTokens;

        // Step 1: Deduplicate similar chunks
        $deduplicated = $this->deduplicateChunks($chunks);

        // Step 2: Merge child chunks with their parents if available
        $merged = $this->mergeHierarchicalChunks($deduplicated);

        // Step 3: Fit within token budget
        $fitted = $this->fitTokenBudget($merged, $maxTokens);

        // Step 4: Calculate confidence scores
        $scores = array_map(fn($c) => $c->score ?? 0.0, $fitted);
        $averageScore = !empty($scores) ? array_sum($scores) / count($scores) : 0.0;

        // Step 5: Extract sources
        $sources = $this->extractSources($fitted);

        return new OptimizedContext(
            chunks: $fitted,
            sources: $sources,
            totalTokens: $this->calculateTotalTokens($fitted),
            averageScore: $averageScore
        );
    }

    private function deduplicateChunks(array $chunks): array
    {
        $unique = [];
        $seen = [];

        foreach ($chunks as $chunk) {
            $hash = md5($chunk->content);

            if (!isset($seen[$hash])) {
                $unique[] = $chunk;
                $seen[$hash] = true;
            }
        }

        return $unique;
    }

    private function mergeHierarchicalChunks(array $chunks): array
    {
        // Group by parent_id
        $parents = [];
        $children = [];

        foreach ($chunks as $chunk) {
            if (($chunk->metadata['level'] ?? null) === 'parent') {
                $parents[$chunk->metadata['id']] = $chunk;
            } elseif (isset($chunk->metadata['parent_id'])) {
                $parentId = $chunk->metadata['parent_id'];
                if (!isset($children[$parentId])) {
                    $children[$parentId] = [];
                }
                $children[$parentId][] = $chunk;
            }
        }

        // If child chunks from same parent, consider using parent instead
        $optimized = [];
        $usedParents = [];

        foreach ($chunks as $chunk) {
            if (($chunk->metadata['level'] ?? null) === 'child') {
                $parentId = $chunk->metadata['parent_id'];

                // If we have multiple children from same parent, use parent
                if (isset($children[$parentId]) && count($children[$parentId]) >= 2 && !isset($usedParents[$parentId])) {
                    if (isset($parents[$parentId])) {
                        $optimized[] = $parents[$parentId];
                        $usedParents[$parentId] = true;
                    }
                } elseif (!isset($usedParents[$parentId])) {
                    $optimized[] = $chunk;
                }
            } else {
                $optimized[] = $chunk;
            }
        }

        return $optimized;
    }

    private function fitTokenBudget(array $chunks, int $maxTokens): array
    {
        $fitted = [];
        $currentTokens = 0;

        foreach ($chunks as $chunk) {
            $chunkTokens = $chunk->tokenCount;

            if ($currentTokens + $chunkTokens <= $maxTokens) {
                $fitted[] = $chunk;
                $currentTokens += $chunkTokens;
            } else {
                break;
            }
        }

        return $fitted;
    }

    private function calculateTotalTokens(array $chunks): int
    {
        return array_sum(array_map(fn($c) => $c->tokenCount, $chunks));
    }

    private function extractSources(array $chunks): array
    {
        $sources = [];

        foreach ($chunks as $chunk) {
            $source = $chunk->metadata['source'] ?? 'Unknown';
            if (!in_array($source, $sources)) {
                $sources[] = $source;
            }
        }

        return $sources;
    }
}
```

## Complete RAG Example

```php
<?php
# filename: examples/rag-demo.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\RAG\RAGPipeline;
use App\RAG\DocumentProcessor;
use App\RAG\Chunking\SemanticChunker;
use App\RAG\EmbeddingService;
use App\RAG\VectorStore\SimpleVectorStore;
use App\RAG\RetrievalEngine;
use App\RAG\ContextOptimizer;

// Initialize services
use ClaudePhp\ClaudePhp;

// Validate environment variables
$anthropicKey = getenv('ANTHROPIC_API_KEY');
$openaiKey = getenv('OPENAI_API_KEY');

if (!$anthropicKey) {
    die("Error: ANTHROPIC_API_KEY environment variable is required\n");
}

if (!$openaiKey) {
    die("Error: OPENAI_API_KEY environment variable is required for embeddings\n");
}

$claude = new ClaudePhp(
    apiKey: $anthropicKey
);

$embeddings = new EmbeddingService(
    apiKey: $openaiKey,
    provider: 'openai'
);

$vectorStore = new SimpleVectorStore(__DIR__ . '/../storage/vectors');

$pipeline = new RAGPipeline(
    claude: $claude,
    processor: new DocumentProcessor(),
    chunker: new SemanticChunker(targetChunkSize: 512, chunkOverlap: 64),
    embeddings: $embeddings,
    vectorStore: $vectorStore,
    retriever: new RetrievalEngine($vectorStore, $claude, enableReranking: true),
    optimizer: new ContextOptimizer(maxContextTokens: 4000)
);

// Ingest documents
echo "Ingesting documents...\n";

$docs = [
    __DIR__ . '/../docs/laravel-guide.md',
    __DIR__ . '/../docs/php-best-practices.md',
    __DIR__ . '/../docs/api-documentation.md'
];

foreach ($docs as $doc) {
    $result = $pipeline->ingest($doc, [
        'category' => 'technical-docs',
        'language' => 'en'
    ]);

    echo "✓ Ingested {$doc}: {$result->chunkCount} chunks\n";
}

// Query the knowledge base
echo "\n--- Querying Knowledge Base ---\n\n";

$questions = [
    "What are Laravel's best practices for dependency injection?",
    "How do I optimize database queries in PHP?",
    "What's the recommended way to handle API authentication?"
];

foreach ($questions as $question) {
    echo "Q: {$question}\n";

    $response = $pipeline->query($question, [
        'top_k' => 5,
        'max_tokens' => 1024,
        'temperature' => 0.2
    ]);

    echo "A: {$response->answer}\n\n";
    echo "Sources: " . implode(', ', $response->sources) . "\n";
    echo "Confidence: " . number_format($response->confidence * 100, 1) . "%\n";
    echo "Chunks used: {$response->metadata['chunks_used']}\n";
    echo "\n" . str_repeat('-', 80) . "\n\n";
}
```

## Document Processor

```php
<?php
# filename: src/RAG/DocumentProcessor.php
declare(strict_types=1);

namespace App\RAG;

class DocumentProcessor
{
    /**
     * Process document from file path
     */
    public function process(string $filePath, array $options = []): Document
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException("File is not readable: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (empty($extension)) {
            throw new \InvalidArgumentException("Cannot determine file type for: {$filePath}");
        }

        try {
            $content = match($extension) {
                'md', 'markdown' => $this->processMarkdown($filePath),
                'pdf' => $this->processPDF($filePath),
                'html', 'htm' => $this->processHTML($filePath),
                'txt', 'text' => $this->processText($filePath),
                default => throw new \InvalidArgumentException("Unsupported file type: {$extension}")
            };

            if (empty(trim($content))) {
                throw new \RuntimeException("Document is empty after processing: {$filePath}");
            }

            return new Document(
                id: uniqid('doc_'),
                'content' => $this->cleanText($content),
                source: $filePath,
                metadata: array_merge([
                    'file_type' => $extension,
                    'file_size' => filesize($filePath),
                    'processed_at' => date('c')
                ], $options)
            );
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to process document {$filePath}: " . $e->getMessage(), 0, $e);
        }
    }

    private function processMarkdown(string $filePath): string
    {
        $content = file_get_contents($filePath);

        // Remove markdown syntax but keep structure
        $content = preg_replace('/#{1,6}\s+(.+)/', '$1', $content);
        $content = preg_replace('/\*\*(.+?)\*\*/', '$1', $content);
        $content = preg_replace('/\*(.+?)\*/', '$1', $content);
        $content = preg_replace('/\[(.+?)\]\(.+?\)/', '$1', $content);

        return $content;
    }

    private function processPDF(string $filePath): string
    {
        // For production, use a library like smalot/pdfparser
        // This is a simplified example
        throw new \RuntimeException("PDF processing requires smalot/pdfparser. Install with: composer require smalot/pdfparser");

        // Example with library:
        // $parser = new \Smalot\PdfParser\Parser();
        // $pdf = $parser->parseFile($filePath);
        // return $pdf->getText();
    }

    private function processHTML(string $filePath): string
    {
        $html = file_get_contents($filePath);

        // Strip HTML tags and decode entities
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function processText(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    private function cleanText(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove control characters except newlines
        $text = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $text);

        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return trim($text);
    }
}
```

## Simple Vector Store Implementation

```php
<?php
# filename: src/RAG/VectorStore/SimpleVectorStore.php
declare(strict_types=1);

namespace App\RAG\VectorStore;

use App\RAG\VectorStore;
use App\RAG\Chunk;

class SimpleVectorStore implements VectorStore
{
    private array $vectors = [];
    private array $metadata = [];
    private int $dimension;

    public function __construct(
        private string $storagePath = null
    ) {
        $this->dimension = 1536; // Default for text-embedding-3-small

        if ($storagePath && file_exists($storagePath)) {
            $this->loadFromDisk();
        }
    }

    /**
     * Store chunks with embeddings
     */
    public function store(array $chunks, array $embeddings, array $metadata = []): object
    {
        $documentId = uniqid('doc_');
        $storedIds = [];

        foreach ($chunks as $i => $chunk) {
            if (!isset($embeddings[$i])) {
                throw new \InvalidArgumentException("Missing embedding for chunk {$i}");
            }

            $id = uniqid('vec_');
            $this->vectors[$id] = $embeddings[$i];
            $this->metadata[$id] = array_merge($chunk->metadata, $metadata, [
                'id' => $id,
                'document_id' => $documentId,
                'chunk_index' => $chunk->index,
                'content' => $chunk->content
            ]);

            $storedIds[] = $id;
        }

        if ($this->storagePath) {
            $this->saveToDisk();
        }

        return (object)[
            'id' => $documentId,
            'chunk_ids' => $storedIds
        ];
    }

    /**
     * Search for similar vectors
     */
    public function search(
        array $queryEmbedding,
        int $limit = 10,
        array $filters = []
    ): array {
        if (empty($this->vectors)) {
            return [];
        }

        // Calculate cosine similarity for all vectors
        $similarities = [];

        foreach ($this->vectors as $id => $vector) {
            // Apply filters
            if (!$this->matchesFilters($this->metadata[$id], $filters)) {
                continue;
            }

            $similarity = $this->cosineSimilarity($queryEmbedding, $vector);
            $similarities[$id] = $similarity;
        }

        // Sort by similarity (descending)
        arsort($similarities);

        // Get top K results
        $topIds = array_slice(array_keys($similarities), 0, $limit, true);

        // Build result chunks
        $results = [];
        foreach ($topIds as $id) {
            $meta = $this->metadata[$id];
            $results[] = new Chunk(
                'content' => $meta['content'] ?? '',
                index: $meta['chunk_index'] ?? 0,
                tokenCount: $this->estimateTokens($meta['content'] ?? ''),
                metadata: $meta,
                score: $similarities[$id]
            );
        }

        return $results;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new \InvalidArgumentException("Vectors must have same dimension");
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator == 0) {
            return 0.0;
        }

        return $dotProduct / $denominator;
    }

    /**
     * Check if metadata matches filters
     */
    private function matchesFilters(array $metadata, array $filters): bool
    {
        foreach ($filters as $key => $value) {
            if (!isset($metadata[$key]) || $metadata[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Estimate token count
     */
    private function estimateTokens(string $text): int
    {
        return (int)ceil(strlen($text) / 4);
    }

    /**
     * Save vectors to disk
     */
    private function saveToDisk(): void
    {
        $data = [
            'vectors' => $this->vectors,
            'metadata' => $this->metadata,
            'dimension' => $this->dimension
        ];

        file_put_contents($this->storagePath, serialize($data));
    }

    /**
     * Load vectors from disk
     */
    private function loadFromDisk(): void
    {
        $data = unserialize(file_get_contents($this->storagePath));

        $this->vectors = $data['vectors'] ?? [];
        $this->metadata = $data['metadata'] ?? [];
        $this->dimension = $data['dimension'] ?? 1536;
    }

    /**
     * Get statistics about stored vectors
     */
    public function getStats(): array
    {
        return [
            'total_vectors' => count($this->vectors),
            'dimension' => $this->dimension,
            'storage_path' => $this->storagePath,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ];
    }
}
```

## Data Structures

```php
<?php
# filename: src/RAG/DataStructures.php
declare(strict_types=1);

namespace App\RAG;

readonly class Document
{
    public function __construct(
        public string $id,
        public string $content,
        public string $source,
        public array $metadata = []
    ) {}
}

readonly class Chunk
{
    public function __construct(
        public string $content,
        public int $index,
        public int $tokenCount,
        public array $metadata = [],
        public ?float $score = null
    ) {}
}

readonly class IngestResult
{
    public function __construct(
        public string $documentId,
        public int $chunkCount,
        public array $metadata
    ) {}
}

readonly class RAGResponse
{
    public function __construct(
        public string $answer,
        public array $sources,
        public float $confidence,
        public array $metadata
    ) {}
}

readonly class OptimizedContext
{
    public function __construct(
        public array $chunks,
        public array $sources,
        public int $totalTokens,
        public float $averageScore
    ) {}
}
```

## Exercises

### Exercise 1: Implement Fixed-Size Chunking

**Goal**: Create an alternative chunking strategy that splits documents into fixed-size chunks.

Create a `FixedSizeChunker` class that implements `ChunkingStrategy`:

- Split documents into chunks of exactly 500 tokens
- Include 50-token overlap between chunks
- Preserve sentence boundaries (don't split mid-sentence)
- Add metadata indicating chunk size and position

**Validation**: Test with a 2000-token document and verify you get approximately 4 chunks with proper overlap.

### Exercise 2: Add Hybrid Search

**Goal**: Combine semantic search with keyword search for better retrieval.

Extend `RetrievalEngine` to support hybrid search:

- Perform both vector similarity search and keyword matching
- Combine scores using a weighted average (e.g., 70% semantic, 30% keyword)
- Allow configuration of the weighting ratio
- Return results sorted by combined score

**Validation**: Test with a query that has both semantic meaning and specific keywords, verify both types of matches appear in results.

### Exercise 3: Implement Query Expansion

**Goal**: Expand user queries to improve retrieval quality.

Create a `QueryExpander` class that:

- Uses Claude to generate related terms and synonyms
- Expands the original query with 2-3 related concepts
- Generates multiple query embeddings and combines results
- Improves recall for ambiguous queries

**Validation**: Test with a short query like "authentication" and verify expanded queries include related terms like "login", "security", "credentials".

## Best Practices

### Chunk Size Selection

**Optimal chunk sizes depend on your use case:**

- **Small chunks (256-512 tokens)**: Better for precise fact retrieval, code snippets, FAQs
- **Medium chunks (512-1024 tokens)**: Good balance for most document Q&A
- **Large chunks (1024-2048 tokens)**: Better for complex reasoning, multi-paragraph context

**Recommendation**: Start with 512 tokens and adjust based on your retrieval quality metrics.

### Chunking Strategy Selection

**When to use each strategy:**

- **Semantic Chunking**: Best for general documents, preserves sentence boundaries and meaning
- **Hierarchical Chunking**: Ideal for structured documents (markdown, technical docs) with clear sections
- **Fixed-Size Chunking**: Use when you need consistent chunk sizes for performance optimization

### Embedding Provider Selection

**Provider comparison:**

- **OpenAI** (`text-embedding-3-small`): Good balance of quality and cost, 1536 dimensions
- **OpenAI** (`text-embedding-3-large`): Higher quality, 3072 dimensions, more expensive
- **Voyage AI**: Optimized for retrieval quality, competitive pricing
- **Cohere**: Good for multilingual content

**Recommendation**: Start with `text-embedding-3-small` for cost efficiency, upgrade if quality is insufficient.

### Re-ranking Configuration

**When to enable re-ranking:**

- ✅ Enable for queries requiring high precision (factual answers, citations)
- ✅ Enable when you retrieve 10+ chunks and need to select top 5
- ❌ Disable for simple keyword-style queries where vector similarity is sufficient
- ❌ Disable if latency is critical (re-ranking adds ~200-500ms)

**Cost consideration**: Re-ranking uses Claude API calls, so disable if cost is a concern.

### Context Optimization Tips

1. **Token Budget**: Reserve 20-30% of Claude's context window for the prompt and system message
2. **Chunk Selection**: Retrieve 2-3x more chunks than needed, then optimize down
3. **Deduplication**: Always enable to avoid redundant information
4. **Hierarchical Merging**: Use when multiple child chunks from same parent are retrieved

### Performance Optimization

**For large knowledge bases:**

1. **Batch Processing**: Process documents in batches during ingestion
2. **Async Embeddings**: Use async HTTP client for parallel embedding generation
3. **Caching**: Cache embeddings for unchanged documents
4. **Indexing**: Use production vector databases (Pinecone, Weaviate) instead of in-memory storage
5. **Query Caching**: Cache query embeddings and results for frequently asked questions

### Security Considerations

1. **Input Validation**: Always validate and sanitize document content before processing
2. **API Keys**: Store embedding API keys securely, never commit to version control
3. **Content Filtering**: Filter sensitive information before storing in vector database
4. **Access Control**: Implement access controls for knowledge base queries
5. **Rate Limiting**: Implement rate limiting to prevent abuse

### Monitoring and Evaluation

**Key metrics to track:**

- **Retrieval Quality**: Average similarity scores of retrieved chunks
- **Answer Quality**: User feedback, answer relevance ratings
- **Latency**: End-to-end query time (embedding + retrieval + generation)
- **Cost**: Token usage, API call counts, embedding generation costs
- **Coverage**: Percentage of queries that find relevant chunks

**Evaluation approach:**

```php
// Track metrics for each query
$metrics = [
    'query' => $question,
    'chunks_retrieved' => count($retrievedChunks),
    'avg_similarity' => $averageScore,
    'latency_ms' => $latency,
    'tokens_used' => $tokensUsed,
    'user_rating' => null // Collect from user feedback
];

// Log or store metrics for analysis
$this->metricsLogger->log($metrics);
```

## RAG Evaluation Metrics

Evaluating RAG system quality requires measuring both retrieval and generation performance. Here's a comprehensive evaluation framework:

````php
<?php
# filename: src/RAG/Evaluation/RAGEvaluator.php
declare(strict_types=1);

namespace App\RAG\Evaluation;

use App\RAG\EmbeddingService;

class RAGEvaluator
{
    /**
     * Evaluate retrieval quality using precision and recall
     */
    public function evaluateRetrieval(
        array $retrievedChunks,
        array $relevantChunkIds,
        int $topK
    ): RetrievalMetrics {
        $retrievedIds = array_map(fn($c) => $c->metadata['id'] ?? '', $retrievedChunks);
        $retrievedIds = array_slice($retrievedIds, 0, $topK);

        $relevantRetrieved = array_intersect($retrievedIds, $relevantChunkIds);

        $precision = count($retrievedIds) > 0
            ? count($relevantRetrieved) / count($retrievedIds)
            : 0.0;

        $recall = count($relevantChunkIds) > 0
            ? count($relevantRetrieved) / count($relevantChunkIds)
            : 0.0;

        $f1 = ($precision + $recall) > 0
            ? 2 * ($precision * $recall) / ($precision + $recall)
            : 0.0;

        return new RetrievalMetrics(
            precision: $precision,
            recall: $recall,
            f1: $f1,
            retrievedCount: count($retrievedIds),
            relevantCount: count($relevantChunkIds),
            relevantRetrieved: count($relevantRetrieved)
        );
    }

    /**
     * Calculate Mean Reciprocal Rank (MRR) for retrieval
     */
    public function calculateMRR(
        array $queries,
        array $groundTruth
    ): float {
        $reciprocalRanks = [];

        foreach ($queries as $queryId => $retrievedChunks) {
            $relevantIds = $groundTruth[$queryId] ?? [];
            $retrievedIds = array_map(fn($c) => $c->metadata['id'] ?? '', $retrievedChunks);

            $rank = null;
            foreach ($retrievedIds as $position => $id) {
                if (in_array($id, $relevantIds)) {
                    $rank = $position + 1;
                    break;
                }
            }

            $reciprocalRanks[] = $rank !== null ? 1.0 / $rank : 0.0;
        }

        return count($reciprocalRanks) > 0
            ? array_sum($reciprocalRanks) / count($reciprocalRanks)
            : 0.0;
    }

    /**
     * Calculate Normalized Discounted Cumulative Gain (NDCG)
     */
    public function calculateNDCG(
        array $retrievedChunks,
        array $relevanceScores,
        int $k = 10
    ): float {
        $dcg = 0.0;
        $retrieved = array_slice($retrievedChunks, 0, $k);

        foreach ($retrieved as $i => $chunk) {
            $id = $chunk->metadata['id'] ?? '';
            $relevance = $relevanceScores[$id] ?? 0;
            $position = $i + 1;
            $dcg += $relevance / log2($position + 1);
        }

        // Calculate ideal DCG (IDCG)
        arsort($relevanceScores);
        $idealRelevance = array_slice($relevanceScores, 0, $k);
        $idcg = 0.0;

        foreach ($idealRelevance as $i => $relevance) {
            $position = $i + 1;
            $idcg += $relevance / log2($position + 1);
        }

        return $idcg > 0 ? $dcg / $idcg : 0.0;
    }

    /**
     * Evaluate answer quality using semantic similarity
     */
    public function evaluateAnswerQuality(
        string $generatedAnswer,
        string $referenceAnswer,
        EmbeddingService $embeddings
    ): float {
        $genEmbedding = $embeddings->embedQuery($generatedAnswer);
        $refEmbedding = $embeddings->embedQuery($referenceAnswer);

        return $this->cosineSimilarity($genEmbedding, $refEmbedding);
    }

    /**
     * Detect potential hallucinations (claims without source support)
     */
    public function detectHallucinations(
        string $answer,
        array $retrievedChunks,
        Anthropic $claude
    ): HallucinationReport {
        $chunkContents = array_map(fn($c) => $c->content, $retrievedChunks);
        $context = implode("\n\n", $chunkContents);

        $prompt = <<<PROMPT
Analyze the following answer and determine if any claims are made that are NOT supported by the provided context.

Answer to analyze:
{$answer}

Context from knowledge base:
{$context}

For each unsupported claim, identify:
1. The specific claim made
2. Why it's not supported by the context
3. Confidence level (high/medium/low)

Return JSON format:
{
  "has_hallucinations": true/false,
  "unsupported_claims": [
    {
      "claim": "specific claim text",
      "reason": "why not supported",
      "confidence": "high|medium|low"
    }
  ],
  "supported_claims_count": number,
  "total_claims_count": number
}
PROMPT;

        $response = $claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 1024,
            'temperature' => 0.1,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        $jsonText = $response->content[0]->text;
        $jsonText = preg_replace('/```json\s*/', '', $jsonText);
        $jsonText = preg_replace('/```\s*/', '', $jsonText);
        $data = json_decode(trim($jsonText), true);

        return new HallucinationReport(
            hasHallucinations: $data['has_hallucinations'] ?? false,
            unsupportedClaims: $data['unsupported_claims'] ?? [],
            supportedClaimsCount: $data['supported_claims_count'] ?? 0,
            totalClaimsCount: $data['total_claims_count'] ?? 0
        );
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return 0.0;
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);
        return $denominator > 0 ? $dotProduct / $denominator : 0.0;
    }
}

readonly class RetrievalMetrics
{
    public function __construct(
        public float $precision,
        public float $recall,
        public float $f1,
        public int $retrievedCount,
        public int $relevantCount,
        public int $relevantRetrieved
    ) {}
}

readonly class HallucinationReport
{
    public function __construct(
        public bool $hasHallucinations,
        public array $unsupportedClaims,
        public int $supportedClaimsCount,
        public int $totalClaimsCount
    ) {}
}
````

## Citation Verification

Ensuring answers cite sources correctly is critical for RAG systems. Here's a citation verification system:

````php
<?php
# filename: src/RAG/CitationVerifier.php
declare(strict_types=1);

namespace App\RAG;


class CitationVerifier
{
    public function __construct(
        private \ClaudePhp\ClaudePhp $claude
    ) {}

    /**
     * Verify citations in answer match retrieved sources
     */
    public function verifyCitations(
        string $answer,
        array $retrievedChunks
    ): CitationVerification {
        $sources = array_map(fn($c) => [
            'id' => $c->metadata['id'] ?? '',
            'source' => $c->metadata['source'] ?? 'Unknown',
            'content' => substr($c->content, 0, 500)
        ], $retrievedChunks);

        $prompt = <<<PROMPT
Analyze the following answer and verify that all citations reference actual sources.

Answer:
{$answer}

Available sources:
PROMPT;

        foreach ($sources as $i => $source) {
            $prompt .= "\n[Source {$i}] ID: {$source['id']}, File: {$source['source']}\n";
            $prompt .= substr($source['content'], 0, 200) . "...\n";
        }

        $prompt .= <<<PROMPT

Check:
1. Are all cited sources (e.g., "Source 1", "According to Source 2") valid?
2. Do the citations match the content being referenced?
3. Are there any claims that should be cited but aren't?

Return JSON:
{
  "valid_citations": ["Source 1", "Source 2"],
  "invalid_citations": ["Source 5"],
  "missing_citations": ["claim about X"],
  "citation_accuracy": 0.0-1.0
}
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 512,
            'temperature' => 0.1,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        $jsonText = $response->content[0]->text;
        $jsonText = preg_replace('/```json\s*/', '', $jsonText);
        $jsonText = preg_replace('/```\s*/', '', $jsonText);
        $data = json_decode(trim($jsonText), true);

        return new CitationVerification(
            validCitations: $data['valid_citations'] ?? [],
            invalidCitations: $data['invalid_citations'] ?? [],
            missingCitations: $data['missing_citations'] ?? [],
            citationAccuracy: $data['citation_accuracy'] ?? 0.0
        );
    }
}

readonly class CitationVerification
{
    public function __construct(
        public array $validCitations,
        public array $invalidCitations,
        public array $missingCitations,
        public float $citationAccuracy
    ) {}
}
````

## Using Evaluation in Practice

Here's how to integrate evaluation into your RAG pipeline:

```php
<?php
# filename: examples/evaluate-rag.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\RAG\RAGPipeline;
use App\RAG\Evaluation\RAGEvaluator;
use App\RAG\CitationVerifier;

use ClaudePhp\ClaudePhp;

// Validate environment variables
$anthropicKey = getenv('ANTHROPIC_API_KEY');
if (!$anthropicKey) {
    die("Error: ANTHROPIC_API_KEY environment variable is required\n");
}

$claude = new ClaudePhp(
    apiKey: $anthropicKey
);

$pipeline = new RAGPipeline(/* ... */);
$evaluator = new RAGEvaluator();
$citationVerifier = new CitationVerifier($claude);

// Test query
$question = "What are the best practices for dependency injection?";
$response = $pipeline->query($question);

// Evaluate retrieval quality
$groundTruth = ['chunk_123', 'chunk_456']; // Known relevant chunks
$retrievalMetrics = $evaluator->evaluateRetrieval(
    retrievedChunks: $response->metadata['retrieved_chunks'] ?? [],
    relevantChunkIds: $groundTruth,
    topK: 5
);

echo "Retrieval Metrics:\n";
echo "  Precision: " . number_format($retrievalMetrics->precision * 100, 1) . "%\n";
echo "  Recall: " . number_format($retrievalMetrics->recall * 100, 1) . "%\n";
echo "  F1 Score: " . number_format($retrievalMetrics->f1 * 100, 1) . "%\n";

// Check for hallucinations
$hallucinationReport = $evaluator->detectHallucinations(
    answer: $response->answer,
    retrievedChunks: $response->metadata['retrieved_chunks'] ?? [],
    claude: $claude
);

if ($hallucinationReport->hasHallucinations) {
    echo "\n⚠️  Potential Hallucinations Detected:\n";
    foreach ($hallucinationReport->unsupportedClaims as $claim) {
        echo "  - {$claim['claim']} (Confidence: {$claim['confidence']})\n";
    }
}

// Verify citations
$citationVerification = $citationVerifier->verifyCitations(
    answer: $response->answer,
    retrievedChunks: $response->metadata['retrieved_chunks'] ?? []
);

echo "\nCitation Verification:\n";
echo "  Accuracy: " . number_format($citationVerification->citationAccuracy * 100, 1) . "%\n";
echo "  Valid: " . count($citationVerification->validCitations) . "\n";
echo "  Invalid: " . count($citationVerification->invalidCitations) . "\n";
echo "  Missing: " . count($citationVerification->missingCitations) . "\n";
```

**When to use evaluation:**

- **During Development**: Evaluate on a test set to tune chunking and retrieval parameters
- **Before Deployment**: Run comprehensive evaluation to establish baseline metrics
- **In Production**: Sample queries for ongoing quality monitoring
- **After Changes**: Re-evaluate when modifying chunking strategies or retrieval logic

## Troubleshooting

### Error: "Missing embedding for chunk"

**Symptom**: `InvalidArgumentException: Missing embedding for chunk 5`

**Cause**: The number of chunks doesn't match the number of embeddings returned from the embedding service.

**Solution**: Ensure embeddings are generated for all chunks:

```php
// Check counts match
if (count($chunks) !== count($embeddings)) {
    throw new \RuntimeException("Chunk/embedding count mismatch");
}

// Verify embedding service returns correct count
$embeddings = $this->embeddings->embed($chunks);
```

### Problem: Low Retrieval Quality

**Symptom**: Retrieved chunks don't match the query well, leading to poor answers.

**Causes and Solutions**:

1. **Chunk size too large** — Reduce `targetChunkSize` to 256-512 tokens
2. **No re-ranking** — Enable re-ranking in `RetrievalEngine`
3. **Poor chunking** — Use semantic chunking instead of fixed-size
4. **Insufficient overlap** — Increase `chunkOverlap` to 10-20% of chunk size

```php
// Better chunking configuration
$chunker = new SemanticChunker(
    targetChunkSize: 512,
    chunkOverlap: 100  // ~20% overlap
);

// Enable re-ranking
$retriever = new RetrievalEngine(
    $vectorStore,
    $claude,
    enableReranking: true
);
```

### Problem: Context Window Exceeded

**Symptom**: `InvalidArgumentException: Context exceeds token limit`

**Cause**: Retrieved chunks exceed Claude's context window after formatting.

**Solution**: Reduce `maxContextTokens` or increase chunk filtering:

```php
// Reduce context size
$optimizer = new ContextOptimizer(maxContextTokens: 3000);

// Or retrieve fewer chunks
$response = $pipeline->query($question, [
    'top_k' => 3,  // Reduced from 5
    'max_context_tokens' => 3000
]);
```

### Error: "Unsupported file type"

**Symptom**: `InvalidArgumentException: Unsupported file type: docx`

**Cause**: Document processor doesn't support the file format.

**Solution**: Add support for the format or convert to supported format:

```php
// Add support in DocumentProcessor
private function processDocx(string $filePath): string
{
    // Use library like PhpOffice/PhpWord
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
    return $phpWord->getText();
}

// Or convert to supported format first
// docx -> markdown using pandoc or similar
```

### Problem: Slow Embedding Generation

**Symptom**: Document ingestion takes too long.

**Causes and Solutions**:

1. **Not batching** — Ensure embeddings are batched (100 at a time)
2. **Sequential processing** — Process multiple documents in parallel
3. **Large chunks** — Reduce chunk size to generate fewer embeddings

```php
// Batch embeddings (already implemented)
$batches = array_chunk($texts, 100);

// Process documents in parallel (if using async)
// Or use queue system for background processing
```

### Problem: Empty Results from Query

**Symptom**: Query returns "I couldn't find any relevant information" even when documents exist.

**Causes and Solutions**:

1. **Low similarity threshold** — Vector store may filter out low-scoring results
2. **Query too specific** — Try broader queries or query expansion
3. **Embedding mismatch** — Ensure query and document embeddings use same model
4. **Empty knowledge base** — Verify documents were successfully ingested

```php
// Debug retrieval
$retrievedChunks = $this->retriever->retrieve($queryEmbedding, topK: 10);

if (empty($retrievedChunks)) {
    // Check if vector store has any vectors
    $stats = $this->vectorStore->getStats();
    error_log("Vector store stats: " . json_encode($stats));

    // Try with lower similarity threshold
    $chunks = $this->vectorStore->search($queryEmbedding, limit: 20);
    error_log("Retrieved chunks: " . count($chunks));
}
```

### Problem: Inconsistent Answer Quality

**Symptom**: Same query returns different quality answers.

**Causes and Solutions**:

1. **Non-deterministic re-ranking** — Use lower temperature (0.0-0.1) for re-ranking
2. **Chunk order matters** — Ensure consistent chunk ordering in context
3. **Insufficient context** — Increase `top_k` or `max_context_tokens`
4. **Poor chunking** — Review chunk boundaries, may need different strategy

```php
// More deterministic configuration
$response = $pipeline->query($question, [
    'top_k' => 7,  // Retrieve more chunks
    'max_context_tokens' => 5000,  // Allow more context
    'temperature' => 0.1,  // Lower temperature for consistency
    'model' => 'claude-sonnet-4-5'  // Use consistent model
]);
```

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've built a complete RAG system from scratch. Here's what you accomplished:

- ✓ **RAG Pipeline** — Complete ingestion and query pipeline that grounds Claude in your knowledge base
- ✓ **Intelligent Chunking** — Semantic and hierarchical strategies that preserve context
- ✓ **Embedding Service** — Multi-provider support with efficient batching
- ✓ **Retrieval Engine** — Vector search with Claude-powered re-ranking
- ✓ **Context Optimization** — Token-aware optimization with deduplication and merging
- ✓ **Document Processing** — Support for multiple file formats
- ✓ **Vector Storage** — Simple but effective vector store with similarity search

### Key Concepts Learned

- **RAG Architecture** — How retrieval augments generation by providing relevant context
- **Semantic Chunking** — Preserving meaning across chunk boundaries with overlap
- **Hierarchical Chunking** — Multi-level context retrieval for complex documents
- **Vector Similarity** — Cosine similarity for finding semantically similar content
- **Re-ranking** — Using Claude to improve retrieval quality beyond vector similarity
- **Context Optimization** — Maximizing information density within token limits
- **Source Tracking** — Enabling citations and verification of answers

### Real-World Applications

Your RAG system can now power:

- **Document Q&A Systems** — Answer questions about technical documentation, manuals, or knowledge bases
- **Customer Support Bots** — Ground responses in product documentation and FAQs
- **Research Assistants** — Synthesize information from multiple sources
- **Code Documentation** — Answer questions about codebases and APIs
- **Legal Document Analysis** — Extract and answer questions from contracts and regulations

### Next Steps

- **Chapter 32** covers vector databases (Pinecone, Weaviate, Milvus) for production-scale storage and hybrid search
- **Chapter 33** explores multi-agent systems for complex collaborative workflows
- **Chapter 34** shows how to chain multiple prompts for complex workflows

## Key Takeaways

- ✓ RAG grounds Claude's responses in your private knowledge base
- ✓ Intelligent chunking preserves semantic meaning and context
- ✓ Semantic search finds relevant information, not just keyword matches
- ✓ Hierarchical chunking enables multi-level context retrieval
- ✓ Re-ranking with Claude improves relevance beyond vector similarity
- ✓ Context optimization maximizes information density within token limits
- ✓ Source tracking enables citation and verification
- ✓ Chunk overlap ensures continuity across boundaries
- ✓ Confidence scores help assess answer reliability
- ✓ RAG scales to large knowledge bases efficiently


---

Continue to [Chapter 32: Vector Databases in PHP](/series/claude-php-developers/chapters/32-vector-databases) to learn advanced vector storage and search techniques.

## Further Reading

- [Retrieval-Augmented Generation (RAG) — LangChain Documentation](https://python.langchain.com/docs/use_cases/question_answering/) — Comprehensive RAG guide with best practices
- [Vector Embeddings — OpenAI Documentation](https://platform.openai.com/docs/guides/embeddings) — Understanding embeddings and their applications
- [Semantic Search — Pinecone Documentation](https://www.pinecone.io/learn/semantic-search/) — Deep dive into semantic search techniques
- [RAG Evaluation — LlamaIndex Documentation](https://docs.llamameta.ai/en/stable/module_guides/evaluating/rag_evaluation/) — How to evaluate RAG system quality
- [Chunking Strategies — LangChain Documentation](https://python.langchain.com/docs/modules/data_connection/document_transformers/) — Advanced chunking techniques
- [Chapter 32: Vector Databases](/series/claude-php-developers/chapters/32-vector-databases) — Production vector database integration
- [Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems) — Agent orchestration and collaboration

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 31 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-31)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-31
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
export OPENAI_API_KEY="sk-your-openai-key-here"
php examples/rag-demo.php
```
