<?php

declare(strict_types=1);

require_once __DIR__ . '/text-processor.php';

use AiMlPhp\Chapter13\TextProcessor;

// Sample documents
$documents = [
    "Machine learning is a subset of artificial intelligence that enables computers to learn from data.",
    "Deep learning uses neural networks with multiple layers to process complex patterns in data.",
    "Natural language processing helps computers understand and generate human language effectively.",
    "Data science combines statistics, programming, and domain expertise to extract insights from data.",
    "Artificial intelligence systems can perform tasks that typically require human intelligence."
];

echo "=================================================================\n";
echo "Text Processing Pipeline Demo\n";
echo "=================================================================\n\n";

$processor = new TextProcessor(useStemming: true, useStopWords: true);

// Process each document and show transformation
echo "Document Processing:\n\n";
foreach (array_slice($documents, 0, 2) as $idx => $doc) {
    $processed = $processor->process($doc);
    $stats = $processor->getStats($doc, $processed);

    echo "Document " . ($idx + 1) . ":\n";
    echo "  Original: \"" . mb_substr($doc, 0, 60) . "...\"\n";
    echo "  Processed: " . implode(', ', $processed) . "\n";
    echo "  Stats: {$stats['original_tokens']} → {$stats['processed_tokens']} tokens ";
    echo "({$stats['reduction_pct']}% reduction)\n\n";
}

// Create TF-IDF vectors
echo "\n=================================================================\n";
echo "TF-IDF Vectorization\n";
echo "=================================================================\n\n";

$result = $processor->processToTfIdf($documents);
$tfidf = $result['vectorizer'];

echo "Vocabulary size: " . count($result['vocabulary']) . " unique terms\n\n";

// Show most important terms per document
echo "Most Important Terms per Document:\n\n";
foreach ($result['vectors'] as $idx => $vector) {
    echo "Document " . ($idx + 1) . ":\n";
    $topTerms = $tfidf->displayVector($vector, 5);
    foreach ($topTerms as $term => $score) {
        echo "  " . sprintf("%-20s: %.4f", $term, $score) . "\n";
    }
    echo "\n";
}

echo "\n=================================================================\n";
echo "Pipeline Complete!\n";
echo "=================================================================\n";
