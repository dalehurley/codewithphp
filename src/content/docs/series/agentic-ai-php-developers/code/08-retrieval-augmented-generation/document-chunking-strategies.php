<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\RAG\Chunker;
use ClaudeAgents\RAG\Splitters\RecursiveCharacterTextSplitter;
use ClaudeAgents\RAG\Splitters\TokenTextSplitter;
use ClaudeAgents\RAG\Splitters\MarkdownTextSplitter;
use ClaudeAgents\RAG\Splitters\CodeTextSplitter;

/**
 * Document Chunking Strategies
 * 
 * Demonstrates different chunking approaches and when to use each one.
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║            Document Chunking Strategies Comparison            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Sample text for testing
$sampleText = <<<'TEXT'
PHP 8.4 introduces several groundbreaking features that enhance developer productivity. 
Property hooks are one of the most anticipated additions. They provide a clean syntax for 
implementing getters and setters without verbose boilerplate code.

Asymmetric visibility is another powerful feature. It allows properties to have different 
visibility levels for reading and writing. This solves common encapsulation challenges.

The new array functions make data manipulation more expressive. Functions like array_find, 
array_find_key, and array_any provide modern alternatives to traditional loops.

Performance improvements in PHP 8.4 include JIT compiler enhancements and memory optimizations. 
Applications see speed improvements of 10-30% in compute-intensive workloads. The garbage 
collector has been optimized for better memory management patterns.

Type system improvements include better inference and stricter checks. This helps catch bugs 
earlier in the development cycle. Generic types are still under consideration for future releases.
TEXT;

// 1. Basic Sentence-Based Chunker
echo "=== 1. Basic Sentence Chunker (Default) ===\n";
echo "Best for: General text, simple Q&A\n\n";

$basicChunker = new Chunker(
    chunkSize: 100,  // 100 words per chunk
    overlap: 10      // 10 word overlap between chunks
);

$chunks1 = $basicChunker->chunk($sampleText);

echo "Configuration:\n";
echo "  • Chunk size: {$basicChunker->getChunkSize()} words\n";
echo "  • Overlap: {$basicChunker->getOverlap()} words\n";
echo "  • Created {" . count($chunks1) . "} chunks\n\n";

echo "Sample Chunks:\n";
foreach ($chunks1 as $i => $chunk) {
    $wordCount = str_word_count($chunk);
    echo "  Chunk " . ($i + 1) . " ({$wordCount} words):\n";
    echo "  " . substr($chunk, 0, 80) . "...\n\n";
}

echo str_repeat('─', 70) . "\n\n";

// 2. Recursive Character Text Splitter
echo "=== 2. Recursive Character Splitter ===\n";
echo "Best for: General text with structure (paragraphs, sentences)\n\n";

$recursiveSplitter = new RecursiveCharacterTextSplitter(
    chunkSize: 300,         // 300 characters
    chunkOverlap: 50,       // 50 character overlap
    separators: ["\n\n", "\n", ". ", ", ", " ", ""]  // Try these in order
);

$chunks2 = $recursiveSplitter->split($sampleText);

echo "Configuration:\n";
echo "  • Chunk size: 300 characters\n";
echo "  • Overlap: 50 characters\n";
echo "  • Separators: paragraphs → sentences → words → characters\n";
echo "  • Created " . count($chunks2) . " chunks\n\n";

echo "Sample Chunks:\n";
foreach ($chunks2 as $i => $chunk) {
    $charCount = strlen($chunk);
    echo "  Chunk " . ($i + 1) . " ({$charCount} chars):\n";
    echo "  " . substr($chunk, 0, 80) . "...\n\n";
}

echo "✓ Advantage: Respects natural text boundaries\n";
echo "✓ Advantage: Maintains paragraph coherence\n\n";

echo str_repeat('─', 70) . "\n\n";

// 3. Token Text Splitter
echo "=== 3. Token Text Splitter ===\n";
echo "Best for: Token-constrained applications (respects model limits)\n\n";

$tokenSplitter = new TokenTextSplitter(
    chunkSize: 100,         // 100 tokens max
    chunkOverlap: 10        // 10 token overlap
);

$chunks3 = $tokenSplitter->split($sampleText);

echo "Configuration:\n";
echo "  • Chunk size: 100 tokens (strict limit)\n";
echo "  • Overlap: 10 tokens\n";
echo "  • Created " . count($chunks3) . " chunks\n\n";

echo "Sample Chunks:\n";
foreach ($chunks3 as $i => $chunk) {
    $tokenCount = $tokenSplitter->countTokens($chunk);
    echo "  Chunk " . ($i + 1) . " (~{$tokenCount} tokens):\n";
    echo "  " . substr($chunk, 0, 80) . "...\n\n";
}

echo "✓ Advantage: Never exceeds model token limits\n";
echo "✓ Advantage: Predictable costs and performance\n\n";

echo str_repeat('─', 70) . "\n\n";

// 4. Markdown Text Splitter
echo "=== 4. Markdown Text Splitter ===\n";
echo "Best for: Documentation, markdown files (preserves structure)\n\n";

$markdownText = <<<'MD'
# PHP 8.4 Feature Guide

## Property Hooks

Property hooks provide clean getter/setter syntax.

### Basic Example

```php
class User {
    public string $name {
        get => $this->name;
        set => $this->name = ucfirst($value);
    }
}
```

## Asymmetric Visibility

Control read/write access independently.

### Usage

```php
class Account {
    public private(set) float $balance = 0.0;
}
```
MD;

$markdownSplitter = new MarkdownTextSplitter(
    chunkSize: 200,
    chunkOverlap: 20
);

$chunks4 = $markdownSplitter->split($markdownText);

echo "Configuration:\n";
echo "  • Chunk size: 200 characters\n";
echo "  • Overlap: 20 characters\n";
echo "  • Preserves: Headers, code blocks, lists\n";
echo "  • Created " . count($chunks4) . " chunks\n\n";

echo "Sample Chunks (showing structure preservation):\n";
foreach ($chunks4 as $i => $chunk) {
    $lines = explode("\n", trim($chunk));
    $firstLine = $lines[0];
    echo "  Chunk " . ($i + 1) . " starts with: {$firstLine}\n";
}

echo "\n✓ Advantage: Maintains document hierarchy\n";
echo "✓ Advantage: Keeps code blocks intact\n\n";

echo str_repeat('─', 70) . "\n\n";

// 5. Code Text Splitter
echo "=== 5. Code Text Splitter ===\n";
echo "Best for: Source code (respects language syntax)\n\n";

$phpCode = <<<'PHP'
<?php

namespace App\Services;

class PaymentService
{
    private Gateway $gateway;
    
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }
    
    public function processPayment(float $amount, string $currency): PaymentResult
    {
        // Validate amount
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }
        
        // Process through gateway
        try {
            $transaction = $this->gateway->charge($amount, $currency);
            return new PaymentResult(success: true, transactionId: $transaction->id);
        } catch (GatewayException $e) {
            return new PaymentResult(success: false, error: $e->getMessage());
        }
    }
    
    public function refund(string $transactionId): bool
    {
        return $this->gateway->refund($transactionId);
    }
}
PHP;

$codeSplitter = new CodeTextSplitter(
    language: 'php',
    chunkSize: 200,
    chunkOverlap: 30
);

$chunks5 = $codeSplitter->split($phpCode);

echo "Configuration:\n";
echo "  • Language: PHP\n";
echo "  • Chunk size: 200 characters\n";
echo "  • Overlap: 30 characters\n";
echo "  • Respects: Class/method boundaries\n";
echo "  • Created " . count($chunks5) . " chunks\n\n";

echo "Sample Chunks (showing code structure):\n";
foreach ($chunks5 as $i => $chunk) {
    $lines = explode("\n", trim($chunk));
    $relevantLines = array_filter($lines, fn($line) => !empty(trim($line)) && trim($line) !== '<?php');
    $preview = array_slice($relevantLines, 0, 2);
    echo "  Chunk " . ($i + 1) . ":\n";
    foreach ($preview as $line) {
        echo "    " . trim($line) . "\n";
    }
    echo "\n";
}

echo "✓ Advantage: Keeps functions/classes together\n";
echo "✓ Advantage: Maintains valid syntax\n\n";

echo str_repeat('─', 70) . "\n\n";

// Comparison Table
echo "=== Strategy Comparison ===\n\n";

echo "┌──────────────────────┬─────────────────┬──────────────────┬──────────────┐\n";
echo "│ Strategy             │ Best Use Case   │ Preserves        │ Performance  │\n";
echo "├──────────────────────┼─────────────────┼──────────────────┼──────────────┤\n";
echo "│ Basic Chunker        │ Simple Q&A      │ Sentences        │ ⚡⚡⚡         │\n";
echo "│ Recursive Splitter   │ General text    │ Paragraphs       │ ⚡⚡          │\n";
echo "│ Token Splitter       │ Token limits    │ Token boundaries │ ⚡           │\n";
echo "│ Markdown Splitter    │ Documentation   │ Structure        │ ⚡⚡          │\n";
echo "│ Code Splitter        │ Source code     │ Syntax           │ ⚡           │\n";
echo "└──────────────────────┴─────────────────┴──────────────────┴──────────────┘\n\n";

// Best Practices
echo "=== Best Practices ===\n\n";

echo "1. **Choose by content type:**\n";
echo "   • General text → RecursiveCharacterTextSplitter\n";
echo "   • Documentation → MarkdownTextSplitter\n";
echo "   • Source code → CodeTextSplitter\n";
echo "   • Token-constrained → TokenTextSplitter\n\n";

echo "2. **Chunk size guidelines:**\n";
echo "   • Q&A: 200-500 tokens (precise retrieval)\n";
echo "   • Summarization: 800-1200 tokens (more context)\n";
echo "   • Balance: 500-800 tokens (general purpose)\n\n";

echo "3. **Overlap recommendations:**\n";
echo "   • Use 10-20% overlap to prevent information loss\n";
echo "   • More overlap → better recall, higher cost\n";
echo "   • Less overlap → lower cost, potential gaps\n\n";

echo "4. **Testing your strategy:**\n";
echo "   • Test retrieval quality on sample queries\n";
echo "   • Check for missing context at boundaries\n";
echo "   • Monitor token costs vs quality trade-off\n\n";

echo "✅ Chunking strategies demonstration complete!\n";
