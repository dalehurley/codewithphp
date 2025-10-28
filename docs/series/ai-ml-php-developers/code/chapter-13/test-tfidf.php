<?php

declare(strict_types=1);

require_once __DIR__ . '/tfidf.php';

use AiMlPhp\Chapter13\TfIdfVectorizer;

// Sample documents (preprocessed)
$documents = [
    ['machine', 'learning', 'algorithm', 'data'],
    ['machine', 'learning', 'model', 'train'],
    ['deep', 'learning', 'neural', 'network'],
    ['data', 'analysis', 'statistics', 'model'],
    ['algorithm', 'optimization', 'performance']
];

$tfidf = new TfIdfVectorizer();

echo "Training Documents:\n";
foreach ($documents as $idx => $doc) {
    echo "  Doc " . ($idx + 1) . ": " . implode(', ', $doc) . "\n";
}
echo "\n";

// Fit and transform
$vectors = $tfidf->fitTransform($documents);

// Show IDF weights
echo "IDF Weights (Inverse Document Frequency):\n";
echo "  Higher IDF = rarer term = more distinctive\n\n";
$idf = $tfidf->getIdf();
arsort($idf);
foreach (array_slice($idf, 0, 10, true) as $term => $score) {
    echo "  " . sprintf("%-15s: %.4f", $term, $score) . "\n";
}
echo "\n";

// Show TF-IDF vectors for each document
echo "TF-IDF Vectors (Top Terms per Document):\n\n";
foreach ($vectors as $idx => $vector) {
    echo "Document " . ($idx + 1) . ":\n";
    $topTerms = $tfidf->displayVector($vector, 5);
    foreach ($topTerms as $term => $score) {
        echo "  " . sprintf("%-15s: %.4f", $term, $score) . "\n";
    }
    echo "\n";
}

// Compare: same term in different contexts
echo "Term Importance Comparison:\n";
echo "  'learning' appears in docs 1, 2, 3 (common)\n";
echo "  'optimization' appears in doc 5 only (rare)\n\n";

$learningIdf = $idf['learning'];
$optimizationIdf = $idf['optimization'];

echo "  IDF('learning'): " . round($learningIdf, 4) . "\n";
echo "  IDF('optimization'): " . round($optimizationIdf, 4) . "\n";
echo "  → 'optimization' is more distinctive for document classification\n";
