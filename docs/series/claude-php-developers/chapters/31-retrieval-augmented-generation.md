---
title: "31: Retrieval Augmented Generation (RAG)"
description: "Build production-ready RAG systems that combine Claude's intelligence with your private knowledge base through semantic search, intelligent chunking, and context-aware retrieval."
series: "claude-php-developers"
chapter: 31
order: 31
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 1-15"
  - "Understanding of vector embeddings"
  - "Knowledge of semantic search concepts"
  - "Experience with document processing"
---

![31: Retrieval Augmented Generation (RAG)](/images/claude-php/chapter-31-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 31</span>
</div>

# Chapter 31: Retrieval Augmented Generation (RAG)

## Overview

Retrieval Augmented Generation (RAG) extends Claude's capabilities by grounding its responses in your private knowledge base. Instead of relying solely on Claude's training data, RAG retrieves relevant context from your documents, databases, or APIs before generating responses.

This chapter teaches you to build production-ready RAG systems with intelligent document chunking, semantic search, relevance ranking, and context optimization. You'll learn to handle everything from simple document Q&A to complex multi-source knowledge synthesis.

**What You'll Build**: A complete RAG system that ingests documents, creates semantic chunks, performs intelligent retrieval, ranks results by relevance, and generates accurate, contextual responses grounded in your data.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 1-15** (Core API usage and structured outputs)
- ✓ **Vector embedding understanding** for semantic search
- ✓ **Semantic search concepts** for similarity matching
- ✓ **Document processing experience** for text extraction

**Estimated Time**: 120-150 minutes

## RAG Architecture Overview

```php
<?php
# filename: src/RAG/RAGPipeline.php
declare(strict_types=1);

namespace App\RAG;

use Anthropic\Anthropic;

class RAGPipeline
{
    public function __construct(
        private Anthropic $claude,
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
        // Step 1: Process document
        $document = $this->processor->process($documentPath);

        // Step 2: Chunk document intelligently
        $chunks = $this->chunker->chunk($document);

        // Step 3: Generate embeddings
        $embeddings = $this->embeddings->embed($chunks);

        // Step 4: Store in vector database
        $stored = $this->vectorStore->store($chunks, $embeddings, $metadata);

        return new IngestResult(
            documentId: $stored->id,
            chunkCount: count($chunks),
            metadata: array_merge($metadata, [
                'ingested_at' => date('c'),
                'chunk_strategy' => get_class($this->chunker)
            ])
        );
    }

    /**
     * Query the knowledge base and generate response
     */
    public function query(
        string $question,
        array $options = []
    ): RAGResponse {
        // Step 1: Generate query embedding
        $queryEmbedding = $this->embeddings->embedQuery($question);

        // Step 2: Retrieve relevant chunks
        $retrievedChunks = $this->retriever->retrieve(
            embedding: $queryEmbedding,
            topK: $options['top_k'] ?? 5,
            filters: $options['filters'] ?? []
        );

        // Step 3: Optimize context (re-rank, deduplicate, etc.)
        $optimizedContext = $this->optimizer->optimize(
            chunks: $retrievedChunks,
            query: $question,
            maxTokens: $options['max_context_tokens'] ?? 4000
        );

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
            'model' => $options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'temperature' => $options['temperature'] ?? 0.2,
            'system' => $this->getRAGSystemPrompt(),
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
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
            content: $content,
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
                content: $section['content'],
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
            content: $content,
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

use GuzzleHttp\Client;

class EmbeddingService
{
    private Client $client;
    private string $model = 'text-embedding-3-small';

    public function __construct(
        private string $apiKey,
        private string $provider = 'openai' // or 'voyage', 'cohere'
    ) {
        $this->client = new Client([
            'base_uri' => $this->getBaseUri(),
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json'
            ]
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
        // Batch texts to respect API limits
        $batches = array_chunk($texts, 100);
        $allEmbeddings = [];

        foreach ($batches as $batch) {
            $response = $this->client->post('/embeddings', [
                'json' => [
                    'model' => $this->model,
                    'input' => $batch
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            foreach ($data['data'] as $item) {
                $allEmbeddings[] = $item['embedding'];
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

use Anthropic\Anthropic;

class RetrievalEngine
{
    public function __construct(
        private VectorStore $vectorStore,
        private Anthropic $claude,
        private bool $enableReranking = true
    ) {}

    /**
     * Retrieve and optionally re-rank relevant chunks
     */
    public function retrieve(
        array $embedding,
        int $topK = 5,
        array $filters = []
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
            $chunks = $this->rerank($chunks, $topK);
        }

        return array_slice($chunks, 0, $topK);
    }

    /**
     * Re-rank chunks using Claude for better relevance
     */
    private function rerank(array $chunks, int $topK): array
    {
        // Use Claude to assess relevance
        $chunkTexts = array_map(fn($c) => $c->content, $chunks);

        $prompt = <<<PROMPT
Rank these text chunks by relevance to the query. Return a JSON array of indices (0-based) ordered from most to least relevant.

Chunks:

PROMPT;

        foreach ($chunkTexts as $i => $text) {
            $preview = substr($text, 0, 200);
            $prompt .= "\n[$i]: {$preview}...\n";
        }

        $prompt .= "\nReturn ONLY a JSON array of indices, like: [2, 0, 5, 1, 3, 4]";

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-20250514', // Use fast model for re-ranking
            'max_tokens' => 256,
            'temperature' => 0.1,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
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

use Anthropic\Anthropic;
use App\RAG\RAGPipeline;
use App\RAG\DocumentProcessor;
use App\RAG\Chunking\SemanticChunker;
use App\RAG\EmbeddingService;
use App\RAG\VectorStore\SimpleVectorStore;
use App\RAG\RetrievalEngine;
use App\RAG\ContextOptimizer;

// Initialize services
$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$embeddings = new EmbeddingService(
    apiKey: getenv('OPENAI_API_KEY'),
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

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="31"
  label="You've built a production-ready RAG system!"
/>

---

Continue to [Chapter 32: Vector Databases in PHP](/series/claude-php-developers/chapters/32-vector-databases) to learn advanced vector storage and search techniques.

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
