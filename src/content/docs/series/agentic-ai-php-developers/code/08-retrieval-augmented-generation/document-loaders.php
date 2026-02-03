<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudeAgents\RAG\Loaders\TextFileLoader;
use ClaudeAgents\RAG\Loaders\JSONLoader;
use ClaudeAgents\RAG\Loaders\CSVLoader;
use ClaudeAgents\RAG\Loaders\DirectoryLoader;
use ClaudeAgents\RAG\Loaders\WebLoader;
use ClaudePhp\ClaudePhp;

/**
 * Document Loaders
 * 
 * Demonstrates loading documents from various sources into a RAG pipeline.
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           Document Loaders - Loading from Sources             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$rag = RAGPipeline::create($client);

// Create sample data directory for demonstration
$dataDir = __DIR__ . '/sample-data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// 1. Text File Loader
echo "=== 1. Text File Loader ===\n";
echo "Load plain text files (.txt, .md, etc.)\n\n";

// Create sample text file
$textFile = $dataDir . '/readme.txt';
file_put_contents($textFile, <<<'TEXT'
Welcome to ProductX Documentation

ProductX is a revolutionary platform for modern web development.

Features:
- Fast performance
- Easy deployment
- Great developer experience
- Comprehensive documentation

Visit our website for more information.
TEXT
);

$textLoader = new TextFileLoader();
$textDocs = $textLoader->load($textFile);

echo "Loaded from {$textFile}:\n";
foreach ($textDocs as $doc) {
    echo "  Title: {$doc['title']}\n";
    echo "  Content: " . substr($doc['content'], 0, 80) . "...\n";
    echo "  Metadata: " . json_encode($doc['metadata']) . "\n";
    
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}

echo "\n✓ Loaded " . count($textDocs) . " text documents\n\n";

echo str_repeat('─', 70) . "\n\n";

// 2. JSON Loader
echo "=== 2. JSON Loader ===\n";
echo "Load structured JSON data\n\n";

// Create sample JSON file
$jsonFile = $dataDir . '/products.json';
file_put_contents($jsonFile, json_encode([
    [
        'id' => 'prod_001',
        'name' => 'ProductX Pro',
        'description' => 'Advanced features for professional developers. Includes priority support, advanced analytics, and custom integrations.',
        'price' => 99.99,
        'category' => 'software',
    ],
    [
        'id' => 'prod_002',
        'name' => 'ProductX Starter',
        'description' => 'Perfect for beginners and small projects. Includes basic features, community support, and standard templates.',
        'price' => 29.99,
        'category' => 'software',
    ],
], JSON_PRETTY_PRINT));

$jsonLoader = new JSONLoader();
$jsonDocs = $jsonLoader->load($jsonFile);

echo "Loaded from {$jsonFile}:\n";
foreach ($jsonDocs as $i => $doc) {
    echo "  Document " . ($i + 1) . ":\n";
    echo "    Title: {$doc['title']}\n";
    echo "    Content: " . substr($doc['content'], 0, 60) . "...\n";
    
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}

echo "\n✓ Loaded " . count($jsonDocs) . " JSON documents\n\n";

echo str_repeat('─', 70) . "\n\n";

// 3. CSV Loader
echo "=== 3. CSV Loader ===\n";
echo "Load tabular data from CSV files\n\n";

// Create sample CSV file
$csvFile = $dataDir . '/customers.csv';
file_put_contents($csvFile, <<<'CSV'
customer_id,name,email,plan,status
1001,Acme Corp,contact@acme.com,enterprise,active
1002,TechStart,info@techstart.com,pro,active
1003,DevShop,hello@devshop.com,starter,trial
CSV
);

$csvLoader = new CSVLoader();
$csvDocs = $csvLoader->load($csvFile);

echo "Loaded from {$csvFile}:\n";
foreach ($csvDocs as $i => $doc) {
    if ($i < 2) {  // Show first 2 for brevity
        echo "  Row " . ($i + 1) . ":\n";
        echo "    Title: {$doc['title']}\n";
        echo "    Content: " . substr($doc['content'], 0, 60) . "...\n";
    }
    
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}

echo "\n✓ Loaded " . count($csvDocs) . " CSV rows\n\n";

echo str_repeat('─', 70) . "\n\n";

// 4. Directory Loader
echo "=== 4. Directory Loader ===\n";
echo "Recursively load all files from a directory\n\n";

// Create sample directory structure
$knowledgeBase = $dataDir . '/knowledge-base';
$guidesDir = $knowledgeBase . '/guides';

if (!is_dir($guidesDir)) {
    mkdir($guidesDir, 0755, true);
}

file_put_contents($knowledgeBase . '/intro.md', <<<'MD'
# Introduction to ProductX

ProductX is a comprehensive platform for modern development.

## Key Benefits

- Rapid development
- Scalable architecture
- Built-in security
MD
);

file_put_contents($guidesDir . '/getting-started.md', <<<'MD'
# Getting Started Guide

Follow these steps to get started with ProductX:

1. Sign up for an account
2. Install the CLI tool
3. Create your first project
MD
);

$dirLoader = new DirectoryLoader([
    'extensions' => ['md', 'txt'],
    'recursive' => true,
    'exclude_patterns' => ['node_modules', 'vendor', '.git'],
]);

$dirDocs = $dirLoader->load($knowledgeBase);

echo "Loaded from {$knowledgeBase}:\n";
foreach ($dirDocs as $i => $doc) {
    echo "  File " . ($i + 1) . ":\n";
    echo "    Title: {$doc['title']}\n";
    echo "    Path: {$doc['metadata']['source_path']}\n";
    
    $rag->addDocument($doc['title'], $doc['content'], $doc['metadata']);
}

echo "\n✓ Loaded " . count($dirDocs) . " files recursively\n\n";

echo "Directory Loader Features:\n";
echo "  • Recursive directory traversal\n";
echo "  • File extension filtering\n";
echo "  • Pattern-based exclusion\n";
echo "  • Preserves directory structure in metadata\n\n";

echo str_repeat('─', 70) . "\n\n";

// 5. Web Loader (commented out to avoid network calls)
echo "=== 5. Web Loader ===\n";
echo "Load content from web pages\n\n";

echo "Example usage (requires network access):\n\n";

echo "```php\n";
echo "\$webLoader = new WebLoader([\n";
echo "    'user_agent' => 'ProductX RAG Indexer/1.0',\n";
echo "    'timeout' => 10,\n";
echo "    'follow_redirects' => true,\n";
echo "]);\n";
echo "\n";
echo "// Load single page\n";
echo "\$docs = \$webLoader->load('https://www.php.net/releases/8.4/en.php');\n";
echo "\n";
echo "// Load multiple pages\n";
echo "\$urls = [\n";
echo "    'https://example.com/docs/intro',\n";
echo "    'https://example.com/docs/api',\n";
echo "    'https://example.com/docs/examples',\n";
echo "];\n";
echo "\n";
echo "\$allDocs = [];\n";
echo "foreach (\$urls as \$url) {\n";
echo "    \$docs = \$webLoader->load(\$url);\n";
echo "    \$allDocs = array_merge(\$allDocs, \$docs);\n";
echo "}\n";
echo "```\n\n";

echo "Web Loader Features:\n";
echo "  • HTML parsing and text extraction\n";
echo "  • Removes scripts, styles, navigation\n";
echo "  • Preserves article content\n";
echo "  • Configurable user agent and timeout\n\n";

echo str_repeat('─', 70) . "\n\n";

// Summary
echo "=== Indexing Summary ===\n\n";

echo "Total Documents Indexed: {$rag->getDocumentCount()}\n";
echo "Total Chunks Created: {$rag->getChunkCount()}\n";
echo "Average Chunks per Document: " . round($rag->getChunkCount() / max(1, $rag->getDocumentCount()), 1) . "\n\n";

// Query the indexed content
echo "=== Testing Indexed Content ===\n\n";

$testQueries = [
    'What is ProductX?',
    'What plans are available?',
];

foreach ($testQueries as $query) {
    echo "Q: {$query}\n";
    $result = $rag->query($query, topK: 2);
    echo "A: " . substr($result['answer'], 0, 120) . "...\n";
    echo "Sources: " . implode(', ', array_column($result['sources'], 'source')) . "\n\n";
}

echo str_repeat('─', 70) . "\n\n";

// Loader Comparison
echo "=== Loader Comparison ===\n\n";

echo "┌──────────────────┬─────────────────────┬────────────────┬─────────────┐\n";
echo "│ Loader           │ Best For            │ Features       │ Complexity  │\n";
echo "├──────────────────┼─────────────────────┼────────────────┼─────────────┤\n";
echo "│ TextFileLoader   │ Plain text          │ Simple, fast   │ Low         │\n";
echo "│ JSONLoader       │ Structured data     │ Schema aware   │ Low         │\n";
echo "│ CSVLoader        │ Tabular data        │ Row parsing    │ Low         │\n";
echo "│ DirectoryLoader  │ File collections    │ Recursive      │ Medium      │\n";
echo "│ WebLoader        │ Web content         │ HTML parsing   │ Medium      │\n";
echo "└──────────────────┴─────────────────────┴────────────────┴─────────────┘\n\n";

// Best Practices
echo "=== Best Practices ===\n\n";

echo "1. **Choose the Right Loader**\n";
echo "   • Plain text → TextFileLoader\n";
echo "   • API responses → JSONLoader\n";
echo "   • Databases → CSVLoader (export first)\n";
echo "   • Documentation → DirectoryLoader\n";
echo "   • Web content → WebLoader\n\n";

echo "2. **Metadata Enrichment**\n";
echo "   • Add source path, timestamp, author\n";
echo "   • Include domain-specific metadata\n";
echo "   • Use metadata for filtering during retrieval\n\n";

echo "3. **Batch Processing**\n";
echo "   • Process large directories in batches\n";
echo "   • Use background jobs for web scraping\n";
echo "   • Implement retry logic for failures\n\n";

echo "4. **Content Cleaning**\n";
echo "   • Remove boilerplate (headers, footers)\n";
echo "   • Strip HTML/markdown formatting if needed\n";
echo "   • Normalize whitespace and encoding\n\n";

echo "5. **Error Handling**\n";
echo "   • Handle missing files gracefully\n";
echo "   • Validate JSON/CSV structure\n";
echo "   • Log failed loads for investigation\n\n";

// Clean up sample data
echo "Cleaning up sample data...\n";
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

deleteDirectory($dataDir);
echo "✓ Cleaned up\n\n";

echo "✅ Document loaders demonstration complete!\n\n";

echo "💡 Key Takeaways:\n";
echo "   1. Different loaders for different sources\n";
echo "   2. Metadata enriches retrieval\n";
echo "   3. DirectoryLoader handles complex structures\n";
echo "   4. WebLoader enables web content indexing\n";
echo "   5. Batch processing scales to large datasets\n";
