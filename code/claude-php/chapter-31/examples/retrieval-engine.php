<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\RetrievalEngine;
use App\EmbeddingService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('retrieval');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

echo "=== Retrieval Engine Examples ===\n\n";

// Setup
$embedder = new EmbeddingService(provider: 'mock');
$retriever = new RetrievalEngine($embedder, topK: 3, similarityThreshold: 0.5, logger: $logger);

// Sample documents
$documents = [
    ['text' => 'Claude is an AI assistant created by Anthropic. It excels at tasks requiring reasoning and analysis.', 'id' => 'doc1'],
    ['text' => 'PHP 8.2 introduces readonly classes and improved type system features for better code safety.', 'id' => 'doc2'],
    ['text' => 'RAG combines retrieval systems with generation models to provide context-aware responses.', 'id' => 'doc3'],
    ['text' => 'Vector databases store embeddings and enable fast similarity search for AI applications.', 'id' => 'doc4'],
    ['text' => 'Anthropic focuses on AI safety and developing helpful, honest, and harmless AI systems.', 'id' => 'doc5'],
];

// Index documents
$retriever->indexDocuments($documents);

// Example 1: Basic retrieval
echo "\nExample 1: Semantic Retrieval\n";
echo str_repeat('-', 50) . "\n";
$query = "Tell me about Claude";
$results = $retriever->retrieve($query);
echo "Query: {$query}\n";
echo "Found " . count($results) . " relevant documents:\n\n";
foreach ($results as $result) {
    echo sprintf("Rank %d (Score: %.3f):\n  %s\n\n",
        $result['rank'],
        $result['score'],
        $result['document']['text']
    );
}

// Example 2: Different query
echo "Example 2: Technical Query\n";
echo str_repeat('-', 50) . "\n";
$query = "What's new in PHP?";
$results = $retriever->retrieve($query);
echo "Query: {$query}\n";
echo "Found " . count($results) . " relevant documents:\n\n";
foreach ($results as $result) {
    echo sprintf("Rank %d (Score: %.3f):\n  %s\n\n",
        $result['rank'],
        $result['score'],
        $result['document']['text']
    );
}

// Example 3: Statistics
echo "Example 3: Retriever Statistics\n";
echo str_repeat('-', 50) . "\n";
$stats = $retriever->getStats();
print_r($stats);

echo "\n=== Retrieval Engine Examples Complete ===\n";
