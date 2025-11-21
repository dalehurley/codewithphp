<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\ChunkingService;

echo "=== Document Chunking Examples ===\n\n";

$chunker = new ChunkingService(chunkSize: 300, overlap: 50);

$sampleText = <<<TEXT
# Introduction to RAG

Retrieval Augmented Generation (RAG) is a technique that enhances large language models by providing them with relevant context from external knowledge sources.

## How RAG Works

RAG works in three main steps. First, documents are chunked into smaller pieces. Second, these chunks are embedded into vector space. Third, relevant chunks are retrieved based on similarity to the query.

## Benefits of RAG

RAG offers several advantages. It reduces hallucinations by grounding responses in factual data. It enables models to access up-to-date information. It allows for domain-specific knowledge without fine-tuning.

## Implementation Considerations

When implementing RAG, consider chunk size, embedding quality, and retrieval strategy. Proper chunking preserves semantic meaning while maintaining manageable sizes.
TEXT;

// Example 1: Chunk by size
echo "Example 1: Size-based Chunking\n";
echo str_repeat('-', 50) . "\n";
$chunks = $chunker->chunkBySize($sampleText);
echo "Created " . count($chunks) . " chunks\n\n";
foreach ($chunks as $i => $chunk) {
    echo "Chunk " . ($i + 1) . " ({$chunk['start']}-{$chunk['end']}):\n";
    echo substr($chunk['text'], 0, 100) . "...\n\n";
}

// Example 2: Chunk by paragraph
echo "\nExample 2: Paragraph-based Chunking\n";
echo str_repeat('-', 50) . "\n";
$chunks = $chunker->chunkByParagraph($sampleText);
echo "Created " . count($chunks) . " paragraph chunks\n\n";
foreach ($chunks as $i => $chunk) {
    echo "Paragraph " . ($i + 1) . ":\n";
    echo substr($chunk['text'], 0, 100) . "...\n\n";
}

// Example 3: Semantic chunking
echo "\nExample 3: Semantic Chunking\n";
echo str_repeat('-', 50) . "\n";
$chunks = $chunker->semanticChunk($sampleText);
echo "Created " . count($chunks) . " semantic chunks\n\n";
foreach ($chunks as $i => $chunk) {
    echo "Section: {$chunk['heading']}\n";
    echo substr($chunk['text'], 0, 100) . "...\n\n";
}

echo "=== Chunking Examples Complete ===\n";
