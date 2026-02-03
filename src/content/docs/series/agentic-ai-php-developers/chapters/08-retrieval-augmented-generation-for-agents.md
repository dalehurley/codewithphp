---
title: "08: Retrieval-Augmented Generation (RAG) for Agents"
description: "Add a retrieval layer for grounded responses. Cover chunking, indexing, and citation-style responses to reduce hallucinations."
series: "agentic-ai-php-developers"
chapter: 8
difficulty: "Advanced"
keywords: ["RAG PHP", "Retrieval-Augmented Generation", "Vector Search", "Document Chunking", "Semantic Search", "Citation Generation", "Grounded AI"]
teaches: ["RAG architecture and components", "Document chunking strategies", "Vector stores and semantic search", "Citation-style response generation", "Query transformation techniques", "Reranking for relevance", "Production RAG pipelines"]
estimatedTime: "PT120M"
---

# Chapter 08: Retrieval-Augmented Generation (RAG) for Agents

## Overview

In Chapter 07, you built long-term memory systems that let agents store and recall information. But what if you need your agent to answer questions from thousands of documents, technical documentation, or an entire knowledge base? What if the information changes frequently and can't be memorized?

This is where **Retrieval-Augmented Generation (RAG)** comes in: the ability to retrieve relevant information from external sources and use it to generate accurate, grounded, cited responses. RAG is the cornerstone of modern AI applications—from customer support bots to research assistants to documentation Q&A systems.

The [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) framework provides a complete RAG system with `RAGPipeline`, `RAGAgent`, document loaders, chunking strategies, vector stores, and retrieval algorithms. In this chapter, you'll learn to build production-grade RAG systems that reduce hallucinations, cite sources, and scale to massive knowledge bases.

In this chapter you'll:

- Understand **RAG architecture** and how it differs from pure LLM generation
- Implement **document chunking** with overlap for better retrieval
- Build **semantic search** with embeddings and vector stores
- Create **citation-style responses** that reference source material
- Apply **query transformation** techniques (multi-query, HyDE, decomposition)
- Use **reranking** to improve retrieval relevance
- Deploy **production RAG pipelines** with the framework's components

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. We'll use the framework's RAG namespace and components extensively.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`basic-rag-pipeline.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/basic-rag-pipeline.php) — Simple RAG pipeline with keyword retrieval
- [`document-chunking-strategies.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/document-chunking-strategies.php) — Different chunking approaches
- [`semantic-vector-search.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/semantic-vector-search.php) — Embedding-based semantic retrieval
- [`citation-generation.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/citation-generation.php) — Citation-style response generation
- [`query-transformation.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/query-transformation.php) — Query transformation techniques
- [`reranking-results.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/reranking-results.php) — Reranking for better relevance
- [`document-loaders.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/document-loaders.php) — Loading documents from various sources
- [`production-rag-system.php`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/production-rag-system.php) — Complete production RAG system

All files are in [`code/agentic-ai-php-developers/08-retrieval-augmented-generation/`](../../code/agentic-ai-php-developers/08-retrieval-augmented-generation/README.md).
:::

---

## Understanding Retrieval-Augmented Generation

Before diving into implementation, let's understand what RAG is and why it's essential.

### The Hallucination Problem

**Pure LLM Generation (without RAG):**

```text
User: What's the refund policy for ProductX?
Agent: ❌ ProductX offers a 60-day money-back guarantee.
       (Made up—actual policy is 30 days)
```

**With RAG:**

```text
User: What's the refund policy for ProductX?
Agent: ✅ ProductX offers a 30-day money-back guarantee. [Source: refund-policy.pdf]
       (Retrieved from actual policy document)
```

RAG **grounds** responses in factual sources, dramatically reducing hallucinations.

### What is RAG?

**RAG = Retrieval + Generation**

1. **Retrieval:** Find relevant documents/chunks from a knowledge base
2. **Augmentation:** Add retrieved context to the LLM prompt
3. **Generation:** Generate answer based on provided context

### RAG vs Long-Term Memory

| Feature | Long-Term Memory (Chapter 07) | RAG (This Chapter) |
|---------|------------------------------|-------------------|
| **Data source** | Extracted facts from conversations | External documents/knowledge bases |
| **Scale** | 100s to 10,000s of memories | 100,000s to millions of documents |
| **Update frequency** | Continuous (during conversations) | Batch (periodic re-indexing) |
| **Use case** | Personal context, user preferences | Documentation, support, research |
| **Retrieval** | Semantic + recency + importance | Semantic similarity to query |

### RAG Architecture

```text
┌─────────────────────────────────────────────────────────────────┐
│                         RAG PIPELINE                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐   │
│  │  INDEXING    │     │  RETRIEVAL   │     │  GENERATION  │   │
│  │  (Offline)   │     │   (Online)   │     │   (Online)   │   │
│  └──────────────┘     └──────────────┘     └──────────────┘   │
│         │                     │                     │           │
│         ▼                     ▼                     ▼           │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐   │
│  │ 1. Load Docs │     │ 1. Query     │     │ 1. Context   │   │
│  │ 2. Chunk     │────▶│ 2. Embed     │────▶│ 2. Prompt    │   │
│  │ 3. Embed     │     │ 3. Search    │     │ 3. Generate  │   │
│  │ 4. Index     │     │ 4. Rank      │     │ 4. Cite      │   │
│  └──────────────┘     └──────────────┘     └──────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Basic RAG Pipeline

Let's start with a simple RAG pipeline using the framework's `RAGPipeline`.

### Simple Document Q&A

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudePhp\ClaudePhp;

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create RAG pipeline
$rag = RAGPipeline::create($client);

// Add documents to the knowledge base
echo "=== Indexing Documents ===\n\n";

$rag->addDocument(
    title: 'Product Refund Policy',
    content: 'ProductX offers a 30-day money-back guarantee. Returns must be in original condition. 
              Refunds are processed within 5-7 business days. Shipping costs are non-refundable 
              unless the item is defective.',
    metadata: ['category' => 'policy', 'type' => 'refund']
);

$rag->addDocument(
    title: 'Product Warranty',
    content: 'ProductX includes a 1-year manufacturer warranty covering defects in materials and 
              workmanship. Extended warranty available for purchase. Warranty does not cover 
              accidental damage or normal wear and tear.',
    metadata: ['category' => 'policy', 'type' => 'warranty']
);

$rag->addDocument(
    title: 'Shipping Information',
    content: 'ProductX ships within 1-2 business days. Free shipping on orders over $50. 
              Express shipping available for $15. International shipping available to select countries.',
    metadata: ['category' => 'logistics', 'type' => 'shipping']
);

echo "Indexed {$rag->getDocumentCount()} documents with {$rag->getChunkCount()} chunks\n\n";

// Query the knowledge base
echo "=== Querying Knowledge Base ===\n\n";

$questions = [
    'What is the refund policy?',
    'Does ProductX have a warranty?',
    'How long does shipping take?',
];

foreach ($questions as $question) {
    echo "Q: {$question}\n";
    
    $result = $rag->query($question, topK: 2);
    
    echo "A: {$result['answer']}\n";
    echo "\nSources:\n";
    
    foreach ($result['sources'] as $source) {
        echo "  - {$source['source']}\n";
    }
    
    if (!empty($result['citations'])) {
        echo "Citations: " . implode(', ', array_map(fn($i) => "[Source {$i}]", $result['citations'])) . "\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}
```

**Output:**

```
=== Indexing Documents ===

Indexed 3 documents with 3 chunks

=== Querying Knowledge Base ===

Q: What is the refund policy?
A: ProductX offers a 30-day money-back guarantee [Source 0]. Returns must be in original 
   condition, and refunds are processed within 5-7 business days [Source 0]. 
   Shipping costs are non-refundable unless the item is defective [Source 0].

Sources:
  - Product Refund Policy
Citations: [Source 0]

------------------------------------------------------------
```

### How It Works

1. **Indexing:** Documents are chunked and stored in the pipeline
2. **Retrieval:** Query finds top K most relevant chunks
3. **Generation:** Claude generates answer using retrieved chunks as context
4. **Citation:** Answer includes source references

---

## Document Chunking Strategies

Chunking is critical for RAG—chunks that are too large waste context, chunks that are too small lose meaning.

### The Chunking Challenge

**Too large:**
- Wastes token budget
- Includes irrelevant information
- Reduces retrieval precision

**Too small:**
- Loses semantic context
- Fragments information
- Requires more chunks for same question

**Overlap:**
- Prevents information loss at boundaries
- Improves retrieval recall

### Framework Chunking Strategies

The framework provides several chunking strategies:

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudeAgents\RAG\Chunker;
use ClaudeAgents\RAG\Splitters\RecursiveCharacterTextSplitter;
use ClaudeAgents\RAG\Splitters\TokenTextSplitter;
use ClaudeAgents\RAG\Splitters\MarkdownTextSplitter;
use ClaudeAgents\RAG\Splitters\CodeTextSplitter;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// 1. Basic Chunker (sentence-based)
echo "=== Basic Sentence Chunker ===\n";

$basicChunker = new Chunker(
    chunkSize: 200,  // 200 words per chunk
    overlap: 20      // 20 word overlap
);

$rag1 = RAGPipeline::create($client)->withChunker($basicChunker);

$text = "This is a long document. " . str_repeat("It has many sentences. ", 50);
$chunks = $basicChunker->chunk($text);

echo "Created " . count($chunks) . " chunks\n";
echo "First chunk: " . substr($chunks[0], 0, 100) . "...\n\n";

// 2. Recursive Character Text Splitter (best for general text)
echo "=== Recursive Character Splitter ===\n";

$recursiveSplitter = new RecursiveCharacterTextSplitter(
    chunkSize: 1000,        // 1000 characters
    chunkOverlap: 200,      // 200 character overlap
    separators: ["\n\n", "\n", ". ", " ", ""]
);

$chunks2 = $recursiveSplitter->split($text);
echo "Created " . count($chunks2) . " chunks with recursive splitting\n\n";

// 3. Token Text Splitter (respects model token limits)
echo "=== Token Text Splitter ===\n";

$tokenSplitter = new TokenTextSplitter(
    chunkSize: 512,         // 512 tokens
    chunkOverlap: 50        // 50 token overlap
);

$chunks3 = $tokenSplitter->split($text);
echo "Created " . count($chunks3) . " chunks with token splitting\n";
echo "Each chunk respects 512 token limit\n\n";

// 4. Markdown Text Splitter (preserves structure)
echo "=== Markdown Text Splitter ===\n";

$markdownText = <<<'MD'
# Main Title

## Section 1

This is section 1 content.

### Subsection 1.1

More detailed content here.

## Section 2

This is section 2 content.
MD;

$markdownSplitter = new MarkdownTextSplitter(
    chunkSize: 500,
    chunkOverlap: 50
);

$chunks4 = $markdownSplitter->split($markdownText);
echo "Created " . count($chunks4) . " chunks preserving markdown structure\n";
foreach ($chunks4 as $i => $chunk) {
    $firstLine = explode("\n", trim($chunk))[0];
    echo "  Chunk {$i}: {$firstLine}\n";
}
echo "\n";

// 5. Code Text Splitter (respects code structure)
echo "=== Code Text Splitter ===\n";

$phpCode = <<<'PHP'
<?php

class UserService
{
    public function createUser(string $name): User
    {
        // Validate name
        if (empty($name)) {
            throw new InvalidArgumentException('Name required');
        }
        
        // Create user
        $user = new User($name);
        $this->repository->save($user);
        
        return $user;
    }
    
    public function deleteUser(int $id): void
    {
        $user = $this->repository->find($id);
        $this->repository->delete($user);
    }
}
PHP;

$codeSplitter = new CodeTextSplitter(
    language: 'php',
    chunkSize: 300,
    chunkOverlap: 30
);

$chunks5 = $codeSplitter->split($phpCode);
echo "Created " . count($chunks5) . " chunks preserving PHP code structure\n";
echo "Each chunk maintains valid code boundaries\n\n";

// Best practices
echo "=== Best Practices ===\n\n";

echo "1. **General text:** Use RecursiveCharacterTextSplitter (respects paragraphs)\n";
echo "2. **Documentation:** Use MarkdownTextSplitter (preserves headers/structure)\n";
echo "3. **Code:** Use CodeTextSplitter (respects function/class boundaries)\n";
echo "4. **Token-constrained:** Use TokenTextSplitter (respects model limits)\n";
echo "5. **Simple needs:** Use basic Chunker (fast, sentence-based)\n";
```

### Choosing Chunk Size

**General guidelines:**

- **Small chunks (200-500 tokens):** Precise retrieval, good for Q&A
- **Medium chunks (500-1000 tokens):** Balance precision/context
- **Large chunks (1000-2000 tokens):** Retain more context, good for summaries
- **Overlap (10-20%):** Prevents information loss at boundaries

---

## Semantic Search with Vector Stores

Keyword matching is limited—semantic search with embeddings finds meaning, not just words.

### Vector Store Implementation

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudeAgents\RAG\SemanticRetriever;
use ClaudeAgents\RAG\VectorStore\InMemoryVectorStore;
use ClaudePhp\ClaudePhp;

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Mock embedding function (in production, use Voyage AI, OpenAI, or Cohere)
$embeddingFunction = function (string $text): array {
    // Simple mock: hash-based deterministic embedding
    // In production, use: VoyageAI, OpenAI embeddings, Cohere, etc.
    $hash = hash('sha256', $text);
    $embedding = [];
    
    for ($i = 0; $i < 384; $i++) {
        $hexPair = substr($hash, ($i * 2) % 64, 2);
        $value = hexdec($hexPair) / 255.0;
        $embedding[] = ($value - 0.5) * 2.0;
    }
    
    // Normalize to unit vector
    $norm = sqrt(array_sum(array_map(fn($v) => $v * $v, $embedding)));
    return array_map(fn($v) => $v / $norm, $embedding);
};

// Create semantic retriever
$semanticRetriever = new SemanticRetriever($embeddingFunction);

// Create RAG pipeline with semantic retrieval
$rag = RAGPipeline::create($client)
    ->withRetriever($semanticRetriever);

echo "=== Semantic RAG Pipeline ===\n\n";

// Add technical documentation
$rag->addDocument(
    title: 'PHP Arrays',
    content: 'Arrays in PHP are ordered maps that can hold multiple values. 
              They support numeric and associative keys. Use array functions like 
              array_map, array_filter, and array_reduce for manipulation.',
    metadata: ['topic' => 'arrays', 'language' => 'php']
);

$rag->addDocument(
    title: 'PHP Type System',
    content: 'PHP 8.4 includes property hooks, asymmetric visibility, and enhanced 
              type system features. Scalar types include int, float, string, and bool. 
              Use union types and intersection types for complex type definitions.',
    metadata: ['topic' => 'types', 'language' => 'php']
);

$rag->addDocument(
    title: 'PHP Functions',
    content: 'Functions in PHP support type hints, return types, and named arguments. 
              Arrow functions provide concise syntax. First-class callables allow 
              treating functions as values.',
    metadata: ['topic' => 'functions', 'language' => 'php']
);

// Semantic queries (meaning-based, not keyword-based)
$queries = [
    'How do I work with lists in PHP?',           // Should match "Arrays"
    'Tell me about PHP data types',               // Should match "Type System"
    'What are PHP methods?',                      // Should match "Functions"
];

foreach ($queries as $query) {
    echo "Query: {$query}\n";
    
    $result = $rag->query($query, topK: 1);
    
    echo "Answer: {$result['answer']}\n";
    echo "Top Source: {$result['sources'][0]['source']}\n";
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

// Demonstrate semantic similarity
echo "=== Semantic Similarity ===\n\n";

$pairs = [
    ['arrays', 'lists'],
    ['functions', 'methods'],
    ['variables', 'constants'],
];

foreach ($pairs as [$term1, $term2]) {
    $emb1 = $embeddingFunction($term1);
    $emb2 = $embeddingFunction($term2);
    
    // Cosine similarity
    $similarity = 0.0;
    for ($i = 0; $i < count($emb1); $i++) {
        $similarity += $emb1[$i] * $emb2[$i];
    }
    
    echo "'{$term1}' vs '{$term2}': " . round($similarity, 3) . "\n";
}

echo "\nSemantic search finds meaning, not just keywords!\n";
```

### Production Embedding Services

For production RAG systems, use professional embedding APIs:

**1. Voyage AI (recommended for code/technical content)**

```php
// Voyage AI embeddings
class VoyageEmbeddings
{
    private string $apiKey;
    
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
    
    public function embed(string $text): array
    {
        $response = $this->httpPost('https://api.voyageai.com/v1/embeddings', [
            'input' => $text,
            'model' => 'voyage-code-2',  // Best for code
        ]);
        
        return $response['data'][0]['embedding'];
    }
    
    private function httpPost(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($result, true);
    }
}
```

**2. OpenAI Embeddings**

```php
// OpenAI embeddings
class OpenAIEmbeddings
{
    private string $apiKey;
    
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
    
    public function embed(string $text): array
    {
        $response = $this->httpPost('https://api.openai.com/v1/embeddings', [
            'input' => $text,
            'model' => 'text-embedding-3-small',  // Cheap and fast
        ]);
        
        return $response['data'][0]['embedding'];
    }
}
```

---

## Citation Generation

Citations ground responses in sources and let users verify information.

### Citation Patterns

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$rag = RAGPipeline::create($client);

// Add research papers
$rag->addDocument(
    title: 'Smith et al. (2023) - RAG Performance',
    content: 'Our study shows RAG reduces hallucinations by 67% compared to pure LLM generation. 
              We tested on 1000 factual questions across domains.',
    metadata: ['year' => 2023, 'authors' => 'Smith et al.', 'venue' => 'NeurIPS']
);

$rag->addDocument(
    title: 'Johnson (2024) - Chunking Strategies',
    content: 'Optimal chunk size varies by domain. For Q&A, 300-500 tokens works best. 
              For summarization, 800-1200 tokens is optimal.',
    metadata: ['year' => 2024, 'authors' => 'Johnson', 'venue' => 'EMNLP']
);

// Query with citations
$result = $rag->query('How much does RAG reduce hallucinations?', topK: 2);

echo "=== Answer with Citations ===\n\n";
echo $result['answer'] . "\n\n";

echo "=== Source Details ===\n\n";
foreach ($result['sources'] as $source) {
    echo "Source {$source['index']}: {$source['source']}\n";
    echo "Preview: {$source['text_preview']}\n";
    echo "Metadata: " . json_encode($source['metadata']) . "\n\n";
}

echo "=== Verification ===\n\n";
echo "User can verify claims by checking [Source N] references\n";
echo "Cited sources: " . implode(', ', $result['citations']) . "\n";
```

**Output:**

```
=== Answer with Citations ===

RAG reduces hallucinations by 67% compared to pure LLM generation [Source 0]. 
This finding comes from a study testing 1000 factual questions across 
various domains [Source 0].

=== Source Details ===

Source 0: Smith et al. (2023) - RAG Performance
Preview: Our study shows RAG reduces hallucinations by 67% compared to pure LLM generation...
Metadata: {"year":2023,"authors":"Smith et al.","venue":"NeurIPS"}

=== Verification ===

User can verify claims by checking [Source N] references
Cited sources: 0
```

---

## Query Transformation

Transform queries to improve retrieval quality.

### Multi-Query Generation

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\QueryTransformation\MultiQueryGenerator;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$generator = new MultiQueryGenerator($client);

$originalQuery = 'How do I deploy a PHP application?';

echo "=== Multi-Query Generation ===\n\n";
echo "Original Query: {$originalQuery}\n\n";

// Generate multiple query variations
$variations = $generator->generate($originalQuery, numQueries: 3);

echo "Generated Variations:\n";
foreach ($variations as $i => $variation) {
    echo "  " . ($i + 1) . ". {$variation}\n";
}

echo "\nBenefit: Retrieve from multiple perspectives, improve recall\n";
```

### HyDE (Hypothetical Document Embeddings)

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\QueryTransformation\HyDEGenerator;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$hyde = new HyDEGenerator($client);

$query = 'What are the benefits of property hooks in PHP 8.4?';

echo "=== HyDE Query Transformation ===\n\n";
echo "Original Query: {$query}\n\n";

// Generate hypothetical document
$hypotheticalDoc = $hyde->generate($query);

echo "Hypothetical Document:\n{$hypotheticalDoc}\n\n";

// Combine for retrieval
$augmentedQuery = $hyde->augmentQuery($query);

echo "Augmented Query (for retrieval):\n{$augmentedQuery}\n\n";

echo "Benefit: Search for document that would answer the question\n";
echo "         (More effective than searching for the question itself)\n";
```

### Query Decomposition

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\QueryTransformation\QueryDecomposer;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$decomposer = new QueryDecomposer($client);

$complexQuery = 'Compare the performance and developer experience of Laravel vs Symfony for building REST APIs';

echo "=== Query Decomposition ===\n\n";
echo "Complex Query: {$complexQuery}\n\n";

// Decompose into sub-queries
$subQueries = $decomposer->decompose($complexQuery);

echo "Sub-Queries:\n";
foreach ($subQueries as $i => $subQuery) {
    echo "  " . ($i + 1) . ". {$subQuery}\n";
}

echo "\nBenefit: Answer each sub-query separately, combine results\n";
echo "         (Better for multi-part questions)\n";
```

---

## Reranking Results

Retrieval returns candidates; reranking orders them by true relevance.

### Score-Based Reranking

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\Reranking\ScoreReranker;

$reranker = new ScoreReranker();

// Initial retrieval results (by similarity)
$results = [
    [
        'text' => 'PHP 8.4 introduces property hooks for clean getter/setter syntax.',
        'score' => 0.85,
        'metadata' => ['relevance' => 9, 'recency' => 0.9],
    ],
    [
        'text' => 'PHP versions prior to 8.0 required traditional getter/setter methods.',
        'score' => 0.82,
        'relevance' => 6,
        'metadata' => ['recency' => 0.3],
    ],
    [
        'text' => 'Property hooks reduce boilerplate code significantly.',
        'score' => 0.80,
        'metadata' => ['relevance' => 8, 'recency' => 0.9],
    ],
];

$query = 'PHP 8.4 property hooks';

echo "=== Score-Based Reranking ===\n\n";

// Rerank by composite score
$reranked = $reranker->rerank($results, $query, weights: [
    'score' => 0.5,        // Embedding similarity
    'relevance' => 0.3,    // Metadata relevance
    'recency' => 0.2,      // Recency score
]);

echo "Reranked Results:\n";
foreach ($reranked as $i => $result) {
    echo "  " . ($i + 1) . ". {$result['text']}\n";
    echo "     Final Score: " . round($result['final_score'], 3) . "\n\n";
}
```

### LLM-Based Reranking

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\Reranking\LLMReranker;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$reranker = new LLMReranker($client);

$results = [
    ['text' => 'PHP 8.4 property hooks provide clean syntax.'],
    ['text' => 'Laravel framework supports PHP 8.4.'],
    ['text' => 'Property hooks replace traditional getters/setters.'],
];

$query = 'How do property hooks work in PHP 8.4?';

echo "=== LLM-Based Reranking ===\n\n";

// LLM judges relevance of each result to query
$reranked = $reranker->rerank($results, $query);

echo "Reranked by LLM relevance:\n";
foreach ($reranked as $i => $result) {
    echo "  " . ($i + 1) . ". {$result['text']}\n";
    echo "     Relevance: " . round($result['relevance_score'], 3) . "\n\n";
}

echo "Benefit: LLM understands semantic relevance better than keyword/embedding alone\n";
```

---

## Document Loaders

Load documents from various sources.

### Framework Document Loaders

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudeAgents\RAG\Loaders\TextFileLoader;
use ClaudeAgents\RAG\Loaders\JSONLoader;
use ClaudeAgents\RAG\Loaders\CSVLoader;
use ClaudeAgents\RAG\Loaders\DirectoryLoader;
use ClaudeAgents\RAG\Loaders\WebLoader;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$rag = RAGPipeline::create($client);

echo "=== Document Loaders ===\n\n";

// 1. Text File Loader
echo "1. Loading text files...\n";
$textLoader = new TextFileLoader();
$documents = $textLoader->load(__DIR__ . '/docs/readme.txt');
foreach ($documents as $doc) {
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}
echo "   Loaded " . count($documents) . " text documents\n\n";

// 2. JSON Loader
echo "2. Loading JSON data...\n";
$jsonLoader = new JSONLoader();
$documents = $jsonLoader->load(__DIR__ . '/data/products.json');
foreach ($documents as $doc) {
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}
echo "   Loaded " . count($documents) . " JSON documents\n\n";

// 3. CSV Loader
echo "3. Loading CSV data...\n";
$csvLoader = new CSVLoader();
$documents = $csvLoader->load(__DIR__ . '/data/customers.csv');
foreach ($documents as $doc) {
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}
echo "   Loaded " . count($documents) . " CSV rows\n\n";

// 4. Directory Loader (recursive)
echo "4. Loading directory...\n";
$dirLoader = new DirectoryLoader([
    'extensions' => ['md', 'txt', 'php'],
    'recursive' => true,
    'exclude_patterns' => ['vendor', 'node_modules'],
]);
$documents = $dirLoader->load(__DIR__ . '/knowledge-base');
foreach ($documents as $doc) {
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}
echo "   Loaded " . count($documents) . " files from directory\n\n";

// 5. Web Loader
echo "5. Loading web content...\n";
$webLoader = new WebLoader();
$documents = $webLoader->load('https://www.php.net/releases/8.4/en.php');
foreach ($documents as $doc) {
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}
echo "   Loaded " . count($documents) . " web pages\n\n";

echo "Total: {$rag->getDocumentCount()} documents, {$rag->getChunkCount()} chunks\n";
```

---

## Production RAG System

Putting it all together with the framework's `RAGAgent`.

### Complete Production Implementation

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Factory\AgentFactory;
use ClaudeAgents\RAG\Loaders\DirectoryLoader;
use ClaudeAgents\RAG\Splitters\RecursiveCharacterTextSplitter;
use ClaudeAgents\RAG\SemanticRetriever;
use ClaudeAgents\RAG\Reranking\ScoreReranker;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ProductionRAGSystem
{
    private AgentFactory $factory;
    private Logger $logger;
    
    public function __construct(string $apiKey)
    {
        // Setup logger
        $this->logger = new Logger('rag-system');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        
        // Setup agent factory
        $client = new ClaudePhp(apiKey: $apiKey);
        $this->factory = new AgentFactory($client);
        
        $this->logger->info('Production RAG system initialized');
    }
    
    /**
     * Create a RAG agent with production configuration.
     */
    public function createRAGAgent(array $options = [])
    {
        return $this->factory->createRAGAgent(array_merge([
            'name' => 'production_rag_agent',
            'top_k' => 5,
            'logger' => $this->logger,
            'enable_ml_optimization' => true,
        ], $options));
    }
    
    /**
     * Index a knowledge base directory.
     */
    public function indexKnowledgeBase(string $path, $agent): array
    {
        $this->logger->info("Indexing knowledge base: {$path}");
        
        // Load documents
        $loader = new DirectoryLoader([
            'extensions' => ['md', 'txt', 'php', 'json'],
            'recursive' => true,
            'exclude_patterns' => ['vendor', 'node_modules', 'tests'],
        ]);
        
        $documents = $loader->load($path);
        
        $this->logger->info('Loaded ' . count($documents) . ' documents');
        
        // Use advanced chunking
        $splitter = new RecursiveCharacterTextSplitter(
            chunkSize: 800,
            chunkOverlap: 100
        );
        
        // Add to agent
        $indexed = 0;
        foreach ($documents as $doc) {
            // Split large documents
            if (strlen($doc['content']) > 1000) {
                $chunks = $splitter->split($doc['content']);
                foreach ($chunks as $i => $chunk) {
                    $agent->addDocument(
                        title: "{$doc['title']} (Part " . ($i + 1) . ")",
                        content: $chunk,
                        metadata: array_merge($doc['metadata'], ['chunk' => $i])
                    );
                    $indexed++;
                }
            } else {
                $agent->addDocument(
                    title: $doc['title'],
                    content: $doc['content'],
                    metadata: $doc['metadata']
                );
                $indexed++;
            }
        }
        
        $this->logger->info("Indexed {$indexed} document chunks");
        
        return [
            'documents' => count($documents),
            'chunks' => $indexed,
        ];
    }
    
    /**
     * Query with production features.
     */
    public function query(string $question, $agent): array
    {
        $startTime = microtime(true);
        
        $this->logger->info("Query: {$question}");
        
        // Run agent
        $result = $agent->run($question);
        
        $duration = microtime(true) - $startTime;
        
        if (!$result->isSuccess()) {
            $this->logger->error("Query failed: {$result->getError()}");
            return [
                'success' => false,
                'error' => $result->getError(),
            ];
        }
        
        $metadata = $result->getMetadata();
        
        $this->logger->info('Query completed', [
            'duration' => round($duration, 3),
            'sources' => count($metadata['sources']),
            'citations' => count($metadata['citations']),
            'tokens' => $metadata['tokens'],
        ]);
        
        return [
            'success' => true,
            'answer' => $result->getAnswer(),
            'sources' => $metadata['sources'],
            'citations' => $metadata['citations'],
            'metrics' => [
                'duration' => $duration,
                'tokens' => $metadata['tokens'],
                'document_count' => $metadata['document_count'],
                'chunk_count' => $metadata['chunk_count'],
            ],
        ];
    }
}

// Usage
$system = new ProductionRAGSystem(getenv('ANTHROPIC_API_KEY'));

echo "=== Production RAG System ===\n\n";

// Create agent
$agent = $system->createRAGAgent();

// Index knowledge base
echo "Indexing knowledge base...\n";
$stats = $system->indexKnowledgeBase(__DIR__ . '/knowledge-base', $agent);
echo "Indexed {$stats['documents']} documents into {$stats['chunks']} chunks\n\n";

// Query
$questions = [
    'How do property hooks work in PHP 8.4?',
    'What are the performance benefits of the new JIT compiler?',
    'How do I migrate from PHP 8.3 to 8.4?',
];

foreach ($questions as $question) {
    echo "Q: {$question}\n";
    
    $result = $system->query($question, $agent);
    
    if ($result['success']) {
        echo "A: {$result['answer']}\n";
        echo "\nSources: " . count($result['sources']) . " documents\n";
        echo "Citations: " . implode(', ', array_map(fn($i) => "[{$i}]", $result['citations'])) . "\n";
        echo "Tokens: {$result['metrics']['tokens']['input']} in, {$result['metrics']['tokens']['output']} out\n";
        echo "Duration: " . round($result['metrics']['duration'], 3) . "s\n";
    } else {
        echo "Error: {$result['error']}\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}
```

---

## Summary

In this chapter, you learned how to build production-grade RAG systems for agents:

✅ **RAG architecture** — Retrieval, augmentation, and generation pipeline  
✅ **Document chunking** — Strategies for splitting documents effectively  
✅ **Vector stores** — Semantic search with embeddings  
✅ **Citation generation** — Grounded, verifiable responses  
✅ **Query transformation** — Multi-query, HyDE, decomposition  
✅ **Reranking** — Improving retrieval relevance  
✅ **Document loaders** — Loading from files, databases, web  
✅ **Production patterns** — Complete RAG system with the framework

With RAG, your agents can now answer questions from massive knowledge bases, cite sources, and dramatically reduce hallucinations.

---

## Practice Exercises

### Exercise 1: Build a Documentation Q&A System

Create a RAG system for your project's documentation:

- Index all markdown files
- Support code examples in chunks
- Generate code-aware responses
- Track most-asked questions

### Exercise 2: Implement Hybrid Search

Combine keyword and semantic retrieval:

- Use both KeywordRetriever and SemanticRetriever
- Merge results with RRF (Reciprocal Rank Fusion)
- Compare performance vs single method
- Tune weights for your domain

### Exercise 3: Add Metadata Filtering

Implement filtered retrieval:

- Filter by date range
- Filter by author
- Filter by document type
- Filter by custom tags

### Exercise 4: Build Multi-Document Reasoning

Answer questions requiring multiple sources:

- Decompose complex queries
- Retrieve for each sub-query
- Synthesize comprehensive answer
- Cite all relevant sources

---

## Next Steps

Now that you have RAG for grounding agent responses in external knowledge, you're ready to build **planning systems**. In **Chapter 09: Planning: From Tasks to Steps**, you'll implement task decomposition using `PlanExecuteLoop`, generate plans, track progress, and handle replanning when things change.

Continue to [Chapter 09](/series/agentic-ai-php-developers/chapters/09-planning-from-tasks-to-steps) →

---

## Further Reading

- [`claude-php/agent` RAG System](https://github.com/claude-php/claude-php-agent/tree/master/src/RAG)
- [RAG Paper (Lewis et al., 2020)](https://arxiv.org/abs/2005.11401)
- [Voyage AI Embeddings](https://www.voyageai.com/)
- [Advanced RAG Techniques](https://www.pinecone.io/learn/advanced-rag-techniques/)
- [Chunking Strategies for RAG](https://www.llamaindex.ai/blog/evaluating-chunking-strategies-for-rag)
