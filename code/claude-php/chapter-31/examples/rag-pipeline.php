<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\RAGPipeline;
use App\ChunkingService;
use App\EmbeddingService;
use App\RetrievalEngine;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$logger = new Logger('rag');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

echo "=== RAG Pipeline Examples ===\n\n";

// Initialize components
$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException('ANTHROPIC_API_KEY not set');
$client = new ClaudePhp(    apiKey: $apiKey);

$chunker = new ChunkingService(chunkSize: 500, overlap: 100);
$embedder = new EmbeddingService(provider: 'mock');  // Use 'openai' in production
$retriever = new RetrievalEngine($embedder, topK: 3);
$rag = new RAGPipeline($client, $chunker, $embedder, $retriever, $logger);

// Sample knowledge base
$knowledge = <<<TEXT
# Claude API Documentation

## Authentication
Claude API requires an API key for authentication. Include your API key in the x-api-key header of all requests. You can obtain an API key from the Anthropic Console.

## Models
Claude offers several models: Claude 3.5 Sonnet is the most intelligent model, balancing capability and speed. Claude 3 Opus provides maximum intelligence for complex tasks. Claude 3 Haiku is the fastest and most compact model.

## Rate Limits
API requests are subject to rate limits. The default rate limit is 50 requests per minute for most endpoints. Enterprise customers can request higher limits. Monitor the rate-limit headers in responses.

## Best Practices
Always include error handling in your code. Use streaming for long responses to improve user experience. Implement exponential backoff for retries. Cache responses when appropriate to reduce API calls.
TEXT;

// Index the knowledge base
echo "Indexing knowledge base...\n";
$rag->indexDocuments($knowledge, ['source' => 'documentation', 'version' => '1.0']);
echo "\n";

// Example 1: Simple query
echo "Example 1: Documentation Query\n";
echo str_repeat('-', 50) . "\n";
$result = $rag->query("How do I authenticate with the Claude API?");
echo "Question: {$result->question}\n\n";
echo "Answer:\n{$result->answer}\n\n";
echo "Sources: {$result->metadata['documents_retrieved']} documents\n";
echo "Processing time: " . round($result->processingTime, 2) . "s\n\n";

// Example 2: Different query
echo "Example 2: Model Information Query\n";
echo str_repeat('-', 50) . "\n";
$result = $rag->query("Which Claude model should I use for complex analysis?");
echo "Question: {$result->question}\n\n";
echo "Answer:\n{$result->answer}\n\n";
echo "Retrieved " . count($result->context) . " relevant chunks\n\n";

// Example 3: Show sources
echo "Example 3: Query with Source Citations\n";
echo str_repeat('-', 50) . "\n";
$result = $rag->query("What are the rate limits?");
echo "Question: {$result->question}\n\n";
echo "Answer:\n{$result->answer}\n\n";
echo "Sources used:\n";
foreach ($result->context as $item) {
    echo sprintf("  - Relevance: %.2f\n    %s...\n",
        $item['score'],
        substr($item['document']['text'], 0, 100)
    );
}

echo "\n=== RAG Pipeline Examples Complete ===\n";
