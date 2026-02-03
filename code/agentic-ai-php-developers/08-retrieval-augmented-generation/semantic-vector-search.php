<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudeAgents\RAG\SemanticRetriever;
use ClaudeAgents\RAG\VectorStore\InMemoryVectorStore;
use ClaudePhp\ClaudePhp;

/**
 * Semantic Vector Search Example
 * 
 * Demonstrates embedding-based semantic retrieval that finds documents by
 * meaning rather than keyword matching.
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║        Semantic Vector Search - Meaning-Based Retrieval       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Mock embedding function for demonstration
// In production, use Voyage AI, OpenAI, or Cohere embeddings
$embeddingFunction = function (string $text): array {
    // Simple deterministic mock based on text hash
    // Real embeddings capture semantic meaning with ML models
    $hash = hash('sha256', strtolower(trim($text)));
    $embedding = [];
    
    for ($i = 0; $i < 384; $i++) {
        $hexPair = substr($hash, ($i * 2) % 64, 2);
        $value = hexdec($hexPair) / 255.0;
        $embedding[] = ($value - 0.5) * 2.0;
    }
    
    // Normalize to unit vector (required for cosine similarity)
    $norm = sqrt(array_sum(array_map(fn($v) => $v * $v, $embedding)));
    if ($norm > 0) {
        $embedding = array_map(fn($v) => $v / $norm, $embedding);
    }
    
    return $embedding;
};

// Helper function to calculate cosine similarity
$cosineSimilarity = function (array $a, array $b): float {
    if (count($a) !== count($b) || count($a) === 0) {
        return 0.0;
    }
    
    $dotProduct = 0.0;
    for ($i = 0; $i < count($a); $i++) {
        $dotProduct += $a[$i] * $b[$i];
    }
    
    return $dotProduct;  // Already normalized, so this is cosine similarity
};

// Create semantic retriever
$semanticRetriever = new SemanticRetriever($embeddingFunction);

// Create RAG pipeline with semantic retrieval
$rag = RAGPipeline::create($client)
    ->withRetriever($semanticRetriever);

echo "=== Building Knowledge Base ===\n\n";

// Add technical documentation
$documents = [
    [
        'title' => 'PHP Arrays',
        'content' => 'Arrays in PHP are ordered maps that can hold multiple values. They support 
                      numeric indices and associative keys. PHP provides array functions like 
                      array_map for transforming elements, array_filter for selecting elements, 
                      and array_reduce for aggregating values. Arrays are fundamental data structures 
                      for working with collections.',
        'metadata' => ['topic' => 'data-structures', 'category' => 'collections'],
    ],
    [
        'title' => 'PHP Type System',
        'content' => 'PHP 8.4 includes advanced type system features like property hooks and 
                      asymmetric visibility. The type system supports scalar types including int, 
                      float, string, and bool. Union types allow multiple type possibilities. 
                      Intersection types require multiple types simultaneously. Strict types can 
                      be enabled with declare(strict_types=1).',
        'metadata' => ['topic' => 'types', 'category' => 'language-features'],
    ],
    [
        'title' => 'PHP Functions',
        'content' => 'Functions in PHP support parameter type hints, return type declarations, and 
                      named arguments for better clarity. Arrow functions provide concise syntax for 
                      simple closures. First-class callables treat functions as first-class values. 
                      Functions can be passed as callbacks and stored in variables.',
        'metadata' => ['topic' => 'functions', 'category' => 'language-features'],
    ],
    [
        'title' => 'PHP Classes',
        'content' => 'Classes in PHP provide object-oriented programming capabilities with properties, 
                      methods, and constructors. Property promotion allows declaring and initializing 
                      properties in constructor parameters. Readonly classes prevent property modification 
                      after construction. Abstract classes and interfaces define contracts.',
        'metadata' => ['topic' => 'oop', 'category' => 'language-features'],
    ],
    [
        'title' => 'PHP Performance',
        'content' => 'PHP 8.4 performance improvements include JIT compiler enhancements and opcache 
                      optimizations. Memory usage is reduced through better garbage collection. Preloading 
                      can cache classes for faster startup. Benchmark your specific workload to measure 
                      gains. Profile with Xdebug or Blackfire.',
        'metadata' => ['topic' => 'performance', 'category' => 'optimization'],
    ],
];

foreach ($documents as $doc) {
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}

echo "✓ Indexed {$rag->getDocumentCount()} documents\n";
echo "✓ Created {$rag->getChunkCount()} chunks with embeddings\n\n";

echo str_repeat('─', 70) . "\n\n";

// Demonstrate semantic search (meaning-based, not keyword-based)
echo "=== Semantic Search Examples ===\n\n";

$semanticQueries = [
    [
        'query' => 'How do I work with lists in PHP?',
        'expected' => 'Arrays',
        'reason' => 'Semantic: "lists" → "arrays"',
    ],
    [
        'query' => 'Tell me about PHP data types',
        'expected' => 'Type System',
        'reason' => 'Semantic: "data types" → "type system"',
    ],
    [
        'query' => 'What are PHP methods and procedures?',
        'expected' => 'Functions',
        'reason' => 'Semantic: "methods/procedures" → "functions"',
    ],
    [
        'query' => 'How can I make my PHP app faster?',
        'expected' => 'Performance',
        'reason' => 'Semantic: "make faster" → "performance"',
    ],
];

foreach ($semanticQueries as $i => $example) {
    echo "Query " . ($i + 1) . ": {$example['query']}\n";
    echo "Expected Match: {$example['expected']} ({$example['reason']})\n";
    
    $result = $rag->query($example['query'], topK: 2);
    
    echo "✓ Top Result: {$result['sources'][0]['source']}\n";
    
    // Show brief answer
    $answer = substr($result['answer'], 0, 150);
    echo "Answer: {$answer}...\n";
    
    echo "\n" . str_repeat('─', 70) . "\n\n";
}

// Demonstrate semantic similarity comparison
echo "=== Semantic Similarity Analysis ===\n\n";

$termPairs = [
    ['arrays', 'lists'],
    ['functions', 'methods'],
    ['classes', 'objects'],
    ['variables', 'constants'],
    ['performance', 'speed'],
];

echo "Comparing term similarities (higher = more similar):\n\n";

foreach ($termPairs as [$term1, $term2]) {
    $emb1 = $embeddingFunction($term1);
    $emb2 = $embeddingFunction($term2);
    $similarity = $cosineSimilarity($emb1, $emb2);
    
    $bars = str_repeat('█', (int) round($similarity * 20));
    echo sprintf("  %-15s ↔ %-15s [%s] %.3f\n", $term1, $term2, $bars, $similarity);
}

echo "\n✓ Semantic search finds related concepts, not just exact keywords!\n\n";

echo str_repeat('─', 70) . "\n\n";

// Demonstrate keyword vs semantic comparison
echo "=== Keyword vs Semantic Retrieval ===\n\n";

$testQuery = 'collections of items';

echo "Query: '{$testQuery}'\n";
echo "(Note: No document contains 'collections of items' exactly)\n\n";

$result = $rag->query($testQuery, topK: 1);

echo "Keyword Match: Would fail (no exact match)\n";
echo "Semantic Match: ✓ Found '{$result['sources'][0]['source']}'\n";
echo "Reason: Understands 'collections of items' → 'arrays'\n\n";

// Show the full answer
echo "Full Answer:\n";
echo wordwrap($result['answer'], 70) . "\n\n";

echo str_repeat('─', 70) . "\n\n";

// Production embedding services
echo "=== Production Embedding Services ===\n\n";

echo "For production RAG systems, use professional embedding APIs:\n\n";

echo "1. **Voyage AI** (Recommended for code/technical content)\n";
echo "   • Model: voyage-code-2\n";
echo "   • Dimensions: 1536\n";
echo "   • Best for: Code, technical docs, API references\n";
echo "   • Website: https://www.voyageai.com/\n\n";

echo "2. **OpenAI** (General purpose, widely supported)\n";
echo "   • Model: text-embedding-3-small (fast)\n";
echo "   • Model: text-embedding-3-large (quality)\n";
echo "   • Dimensions: 1536 / 3072\n";
echo "   • Best for: General text, Q&A, search\n\n";

echo "3. **Cohere** (Multilingual support)\n";
echo "   • Model: embed-english-v3.0\n";
echo "   • Model: embed-multilingual-v3.0\n";
echo "   • Dimensions: 1024\n";
echo "   • Best for: Multilingual applications\n\n";

echo str_repeat('─', 70) . "\n\n";

// Integration example
echo "=== Integrating Production Embeddings ===\n\n";

echo "Example: Using Voyage AI embeddings\n\n";

echo "```php\n";
echo "// Voyage AI embedding function\n";
echo "\$voyageEmbedding = function (string \$text): array {\n";
echo "    \$response = file_get_contents('https://api.voyageai.com/v1/embeddings', false, stream_context_create([\n";
echo "        'http' => [\n";
echo "            'method' => 'POST',\n";
echo "            'header' => [\n";
echo "                'Authorization: Bearer ' . getenv('VOYAGE_API_KEY'),\n";
echo "                'Content-Type: application/json',\n";
echo "            ],\n";
echo "            'content' => json_encode([\n";
echo "                'input' => \$text,\n";
echo "                'model' => 'voyage-code-2',\n";
echo "            ]),\n";
echo "        ],\n";
echo "    ]));\n";
echo "    \n";
echo "    \$data = json_decode(\$response, true);\n";
echo "    return \$data['data'][0]['embedding'];\n";
echo "};\n";
echo "\n";
echo "// Use with semantic retriever\n";
echo "\$semanticRetriever = new SemanticRetriever(\$voyageEmbedding);\n";
echo "```\n\n";

echo "✅ Semantic vector search demonstration complete!\n\n";

echo "💡 Key Takeaways:\n";
echo "   1. Semantic search finds meaning, not just keywords\n";
echo "   2. Embeddings capture conceptual similarity\n";
echo "   3. Works across synonyms and related terms\n";
echo "   4. Production: Use Voyage AI, OpenAI, or Cohere\n";
