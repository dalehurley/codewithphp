<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\EmbeddingService;

echo "=== Embedding Service Examples ===\n\n";

// Using mock embeddings for demo (no API key required)
$embedder = new EmbeddingService(provider: 'mock', dimensions: 128);

// Example 1: Generate single embedding
echo "Example 1: Single Text Embedding\n";
echo str_repeat('-', 50) . "\n";
$text = "Claude is a helpful AI assistant developed by Anthropic.";
$embedding = $embedder->embed($text);
echo "Text: {$text}\n";
echo "Embedding dimensions: " . count($embedding) . "\n";
echo "First 5 values: " . implode(', ', array_map(fn($v) => round($v, 4), array_slice($embedding, 0, 5))) . "\n\n";

// Example 2: Batch embeddings
echo "Example 2: Batch Embeddings\n";
echo str_repeat('-', 50) . "\n";
$texts = [
    "PHP is a server-side scripting language.",
    "Python is widely used for data science.",
    "JavaScript runs in web browsers."
];
$embeddings = $embedder->embedBatch($texts);
echo "Generated " . count($embeddings) . " embeddings\n";
foreach ($texts as $i => $text) {
    echo "  {$i}. {$text}\n";
}
echo "\n";

// Example 3: Cosine similarity
echo "Example 3: Similarity Calculation\n";
echo str_repeat('-', 50) . "\n";
$query = "Tell me about PHP programming";
$docs = [
    "PHP is great for web development",
    "Python is used for machine learning",
    "Java is an object-oriented language"
];

$queryEmb = $embedder->embed($query);
$docEmbs = $embedder->embedBatch($docs);

echo "Query: {$query}\n\nSimilarities:\n";
foreach ($docs as $i => $doc) {
    $similarity = $embedder->cosineSimilarity($queryEmb, $docEmbs[$i]);
    echo sprintf("  %.3f - %s\n", $similarity, $doc);
}
echo "\n";

// Example 4: Find most similar
echo "Example 4: Find Top Similar Documents\n";
echo str_repeat('-', 50) . "\n";
$similar = $embedder->findSimilar($queryEmb, $docEmbs, topK: 2);
echo "Top 2 most similar documents:\n";
foreach ($similar as $result) {
    echo sprintf("  Rank %d (Score: %.3f): %s\n",
        $result['index'] + 1,
        $result['score'],
        $docs[$result['index']]
    );
}

echo "\n=== Embedding Service Examples Complete ===\n";
