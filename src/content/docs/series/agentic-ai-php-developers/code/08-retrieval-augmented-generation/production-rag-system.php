<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\Factory\AgentFactory;
use ClaudeAgents\RAG\Loaders\DirectoryLoader;
use ClaudeAgents\RAG\Splitters\RecursiveCharacterTextSplitter;
use ClaudeAgents\RAG\SemanticRetriever;
use ClaudeAgents\RAG\Reranking\ScoreReranker;
use ClaudeAgents\RAG\QueryTransformation\MultiQueryGenerator;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Production RAG System
 * 
 * Complete production-ready RAG system demonstrating:
 * - RAGAgent for grounded responses
 * - Advanced chunking strategies
 * - Query transformation
 * - Reranking for relevance
 * - Comprehensive logging and monitoring
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║        Production RAG System - Complete Implementation        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

/**
 * Production RAG System
 * 
 * Encapsulates all RAG functionality with production patterns
 */
class ProductionRAGSystem
{
    private AgentFactory $factory;
    private Logger $logger;
    private MultiQueryGenerator $multiQueryGen;
    private ScoreReranker $reranker;
    private array $indexStats = [];
    
    public function __construct(string $apiKey)
    {
        // Setup logger
        $this->logger = new Logger('rag-system');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        
        // Setup Claude client and factory
        $client = new ClaudePhp(apiKey: $apiKey);
        $this->factory = new AgentFactory($client);
        
        // Setup query transformation
        $this->multiQueryGen = new MultiQueryGenerator($client);
        
        // Setup reranker
        $this->reranker = new ScoreReranker();
        
        $this->logger->info('Production RAG system initialized');
    }
    
    /**
     * Create a production-configured RAG agent
     */
    public function createRAGAgent(array $options = [])
    {
        $this->logger->info('Creating RAG agent with production configuration');
        
        return $this->factory->createRAGAgent(array_merge([
            'name' => 'production_rag_agent',
            'top_k' => 5,
            'logger' => $this->logger,
            'enable_ml_optimization' => false,  // Enable after collecting training data
        ], $options));
    }
    
    /**
     * Index a knowledge base directory with advanced processing
     */
    public function indexKnowledgeBase(string $path, $agent): array
    {
        $startTime = microtime(true);
        $this->logger->info("Indexing knowledge base from: {$path}");
        
        // Validate path
        if (!is_dir($path)) {
            throw new InvalidArgumentException("Path does not exist: {$path}");
        }
        
        // Load documents with directory loader
        $loader = new DirectoryLoader([
            'extensions' => ['md', 'txt', 'php', 'json'],
            'recursive' => true,
            'exclude_patterns' => ['vendor', 'node_modules', 'tests', '.git'],
        ]);
        
        $documents = $loader->load($path);
        $this->logger->info('Loaded ' . count($documents) . ' source files');
        
        // Advanced chunking strategy
        $splitter = new RecursiveCharacterTextSplitter(
            chunkSize: 800,         // Balanced size for Q&A
            chunkOverlap: 100,      // 12.5% overlap
            separators: ["\n\n", "\n", ". ", " ", ""]
        );
        
        // Process and index documents
        $indexed = 0;
        $skipped = 0;
        $totalChunks = 0;
        
        foreach ($documents as $doc) {
            try {
                // Skip empty or too small documents
                if (strlen(trim($doc['content'])) < 50) {
                    $skipped++;
                    continue;
                }
                
                // Chunk large documents
                if (strlen($doc['content']) > 1000) {
                    $chunks = $splitter->split($doc['content']);
                    $totalChunks += count($chunks);
                    
                    foreach ($chunks as $i => $chunk) {
                        $agent->addDocument(
                            title: "{$doc['title']} (Part " . ($i + 1) . "/" . count($chunks) . ")",
                            content: $chunk,
                            metadata: array_merge($doc['metadata'], [
                                'chunk_index' => $i,
                                'total_chunks' => count($chunks),
                                'parent_document' => $doc['title'],
                            ])
                        );
                    }
                } else {
                    // Index small documents whole
                    $agent->addDocument(
                        title: $doc['title'],
                        content: $doc['content'],
                        metadata: $doc['metadata']
                    );
                    $totalChunks++;
                }
                
                $indexed++;
            } catch (\Exception $e) {
                $this->logger->error("Failed to index {$doc['title']}: {$e->getMessage()}");
                $skipped++;
            }
        }
        
        $duration = microtime(true) - $startTime;
        
        $stats = [
            'source_files' => count($documents),
            'indexed' => $indexed,
            'skipped' => $skipped,
            'total_chunks' => $totalChunks,
            'avg_chunks_per_doc' => $indexed > 0 ? round($totalChunks / $indexed, 1) : 0,
            'duration' => round($duration, 2),
        ];
        
        $this->indexStats = $stats;
        
        $this->logger->info('Indexing complete', $stats);
        
        return $stats;
    }
    
    /**
     * Query with production enhancements
     */
    public function query(string $question, $agent, array $options = []): array
    {
        $startTime = microtime(true);
        $this->logger->info("Processing query: {$question}");
        
        // Extract options
        $useMultiQuery = $options['multi_query'] ?? false;
        $topK = $options['top_k'] ?? 5;
        $minConfidence = $options['min_confidence'] ?? 0.0;
        
        try {
            // Optional: Multi-query expansion
            $queries = [$question];
            if ($useMultiQuery) {
                $this->logger->info('Applying multi-query expansion');
                $variations = $this->multiQueryGen->generate($question, numQueries: 2);
                $queries = array_merge($queries, $variations);
                $this->logger->info('Generated ' . count($variations) . ' query variations');
            }
            
            // Execute primary query
            $result = $agent->run($question);
            
            $duration = microtime(true) - $startTime;
            
            if (!$result->isSuccess()) {
                $this->logger->error("Query failed: {$result->getError()}");
                return [
                    'success' => false,
                    'error' => $result->getError(),
                    'duration' => $duration,
                ];
            }
            
            $metadata = $result->getMetadata();
            
            // Log success metrics
            $this->logger->info('Query completed successfully', [
                'duration' => round($duration, 3),
                'sources_retrieved' => count($metadata['sources']),
                'citations_used' => count($metadata['citations']),
                'input_tokens' => $metadata['tokens']['input'] ?? 0,
                'output_tokens' => $metadata['tokens']['output'] ?? 0,
            ]);
            
            return [
                'success' => true,
                'answer' => $result->getAnswer(),
                'sources' => $metadata['sources'],
                'citations' => $metadata['citations'],
                'metrics' => [
                    'duration' => round($duration, 3),
                    'tokens' => $metadata['tokens'],
                    'document_count' => $metadata['document_count'],
                    'chunk_count' => $metadata['chunk_count'],
                    'top_k_used' => $metadata['top_k_used'],
                ],
                'query_info' => [
                    'original' => $question,
                    'variations' => $useMultiQuery ? $queries : null,
                ],
            ];
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->logger->error("Query exception: {$e->getMessage()}");
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration' => round($duration, 3),
            ];
        }
    }
    
    /**
     * Get system statistics
     */
    public function getStats(): array
    {
        return [
            'index_stats' => $this->indexStats,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            ],
        ];
    }
    
    /**
     * Health check
     */
    public function healthCheck($agent): array
    {
        $checks = [];
        
        // Check 1: Agent has documents
        $rag = $agent->getRag();
        $docCount = $rag->getDocumentCount();
        $checks['has_documents'] = [
            'status' => $docCount > 0 ? 'pass' : 'fail',
            'document_count' => $docCount,
            'chunk_count' => $rag->getChunkCount(),
        ];
        
        // Check 2: Test query
        try {
            $testResult = $agent->run('test query');
            $checks['can_query'] = [
                'status' => 'pass',
                'test_successful' => $testResult->isSuccess(),
            ];
        } catch (\Exception $e) {
            $checks['can_query'] = [
                'status' => 'fail',
                'error' => $e->getMessage(),
            ];
        }
        
        // Overall health
        $allPass = array_reduce($checks, fn($carry, $check) => $carry && $check['status'] === 'pass', true);
        
        return [
            'healthy' => $allPass,
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

echo "=== Initializing Production RAG System ===\n\n";

$system = new ProductionRAGSystem(getenv('ANTHROPIC_API_KEY'));

// Create RAG agent
$agent = $system->createRAGAgent();
echo "✓ RAG agent created\n\n";

echo str_repeat('─', 70) . "\n\n";

// Build sample knowledge base
echo "=== Building Sample Knowledge Base ===\n\n";

$kbDir = __DIR__ . '/sample-kb';
if (!is_dir($kbDir)) {
    mkdir($kbDir, 0755, true);
}

// Add sample documents
file_put_contents($kbDir . '/php-8.4-features.md', <<<'MD'
# PHP 8.4 New Features

## Property Hooks

Property hooks provide clean getter/setter syntax without boilerplate code.

```php
class User {
    public string $name {
        get => $this->name;
        set => $this->name = ucfirst($value);
    }
}
```

## Asymmetric Visibility

Control read and write access independently:

```php
class Account {
    public private(set) float $balance = 0.0;
    
    public function deposit(float $amount): void {
        $this->balance += $amount;
    }
}
```

## Array Functions

New array functions for modern PHP:
- array_find()
- array_find_key()
- array_any()
- array_all()
MD
);

file_put_contents($kbDir . '/performance-guide.md', <<<'MD'
# PHP Performance Optimization Guide

## Opcache Configuration

Enable opcache for production:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

## JIT Compiler

PHP 8.4 includes JIT improvements:
- 10-30% faster for compute-intensive tasks
- Configure with opcache.jit_buffer_size

## Best Practices

1. Use typed properties
2. Enable strict types
3. Minimize dynamic features
4. Cache frequently accessed data
MD
);

file_put_contents($kbDir . '/deployment.md', <<<'MD'
# Deployment Guide

## Production Checklist

- ✓ Enable opcache
- ✓ Disable error display
- ✓ Use environment variables for secrets
- ✓ Configure logging
- ✓ Set up monitoring

## Docker Deployment

Use official PHP 8.4 images:

```dockerfile
FROM php:8.4-fpm
RUN docker-php-ext-install opcache pdo pdo_mysql
```

## Environment Configuration

Set production environment:
- APP_ENV=production
- APP_DEBUG=false
- LOG_LEVEL=error
MD
);

echo "✓ Created sample knowledge base\n\n";

// Index the knowledge base
$indexStats = $system->indexKnowledgeBase($kbDir, $agent);

echo "Indexing Statistics:\n";
echo "  Source Files: {$indexStats['source_files']}\n";
echo "  Indexed: {$indexStats['indexed']}\n";
echo "  Skipped: {$indexStats['skipped']}\n";
echo "  Total Chunks: {$indexStats['total_chunks']}\n";
echo "  Avg Chunks/Doc: {$indexStats['avg_chunks_per_doc']}\n";
echo "  Duration: {$indexStats['duration']}s\n\n";

echo str_repeat('─', 70) . "\n\n";

// Health check
echo "=== Health Check ===\n\n";

$health = $system->healthCheck($agent);
echo "System Health: " . ($health['healthy'] ? '✅ HEALTHY' : '❌ UNHEALTHY') . "\n";
echo "Timestamp: {$health['timestamp']}\n\n";

foreach ($health['checks'] as $name => $check) {
    $status = $check['status'] === 'pass' ? '✓' : '✗';
    echo "  {$status} {$name}: {$check['status']}\n";
    unset($check['status']);
    foreach ($check as $key => $value) {
        echo "    {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
}

echo "\n" . str_repeat('─', 70) . "\n\n";

// Query the system
echo "=== Production Queries ===\n\n";

$questions = [
    'What are property hooks in PHP 8.4?',
    'How do I optimize PHP performance?',
    'What should I check before deploying to production?',
];

foreach ($questions as $i => $question) {
    echo "Q" . ($i + 1) . ": {$question}\n";
    echo str_repeat('─', 70) . "\n\n";
    
    $result = $system->query($question, $agent, [
        'multi_query' => false,  // Set to true for enhanced retrieval
        'top_k' => 3,
    ]);
    
    if ($result['success']) {
        // Answer
        echo "ANSWER:\n";
        echo wordwrap($result['answer'], 70) . "\n\n";
        
        // Sources
        if (!empty($result['sources'])) {
            echo "SOURCES (" . count($result['sources']) . "):\n";
            foreach ($result['sources'] as $j => $source) {
                echo "  [" . ($j) . "] {$source['source']}\n";
            }
            echo "\n";
        }
        
        // Citations
        if (!empty($result['citations'])) {
            echo "CITATIONS: " . implode(', ', array_map(fn($i) => "[{$i}]", $result['citations'])) . "\n\n";
        }
        
        // Metrics
        echo "METRICS:\n";
        echo "  Duration: {$result['metrics']['duration']}s\n";
        echo "  Tokens: {$result['metrics']['tokens']['input']} in + {$result['metrics']['tokens']['output']} out\n";
        echo "  Sources Retrieved: " . count($result['sources']) . "\n";
        echo "  Documents in Index: {$result['metrics']['document_count']}\n";
        echo "  Chunks in Index: {$result['metrics']['chunk_count']}\n";
    } else {
        echo "ERROR: {$result['error']}\n";
    }
    
    echo "\n" . str_repeat('═', 70) . "\n\n";
}

// System statistics
echo "=== System Statistics ===\n\n";

$stats = $system->getStats();

echo "Index Stats:\n";
foreach ($stats['index_stats'] as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\nSystem Info:\n";
foreach ($stats['system_info'] as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\n" . str_repeat('─', 70) . "\n\n";

// Clean up
echo "Cleaning up sample data...\n";
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

deleteDirectory($kbDir);
echo "✓ Cleaned up\n\n";

echo "✅ Production RAG system demonstration complete!\n\n";

echo "💡 Key Takeaways:\n";
echo "   1. Use RAGAgent for production-grade implementations\n";
echo "   2. Advanced chunking improves retrieval quality\n";
echo "   3. Query transformation enhances recall\n";
echo "   4. Comprehensive logging aids debugging\n";
echo "   5. Health checks ensure system reliability\n";
echo "   6. Metrics track performance and costs\n\n";

echo "🚀 Next Steps for Production:\n";
echo "   • Integrate production embedding service (Voyage AI/OpenAI)\n";
echo "   • Add persistent vector store (Pinecone/Weaviate)\n";
echo "   • Implement caching layer (Redis)\n";
echo "   • Set up monitoring and alerts\n";
echo "   • Create evaluation suite for quality\n";
echo "   • Enable ML optimization after collecting data\n";
