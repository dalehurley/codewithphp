<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "Caching - Semantic Cache\n\n";

class SemanticCache
{
    private array $cache = [];

    public function getCached(string $query): ?array
    {
        foreach ($this->cache as $item) {
            if ($this->isSimilar($query, $item['query'])) {
                echo "✓ Semantic match found: '{$item['query']}'\n";
                return $item['response'];
            }
        }
        return null;
    }

    public function store(string $query, array $response): void
    {
        $this->cache[] = [
            'query' => $query,
            'response' => $response,
            'timestamp' => time(),
        ];
    }

    private function isSimilar(string $q1, string $q2): bool
    {
        // Simple similarity check (in production, use vector embeddings)
        $q1 = strtolower(trim($q1));
        $q2 = strtolower(trim($q2));

        similar_text($q1, $q2, $percent);

        return $percent > 80; // 80% similarity threshold
    }
}

$semanticCache = new SemanticCache();

// Simulate storing responses
$semanticCache->store('What is PHP?', ['response' => 'PHP is a programming language']);
$semanticCache->store('How to use Laravel?', ['response' => 'Laravel is a PHP framework']);

// Test similar queries
$queries = [
    'What is PHP programming language?',  // Similar to "What is PHP?"
    'What is php?',                       // Exact match (case insensitive)
    'How do I use Laravel framework?',    // Similar to "How to use Laravel?"
    'What is JavaScript?',                // Not similar
];

foreach ($queries as $query) {
    echo "\nQuery: {$query}\n";
    $cached = $semanticCache->getCached($query);
    if ($cached) {
        echo "Cached response: {$cached['response']}\n";
    } else {
        echo "✗ No match found - would call API\n";
    }
}

echo "\nNote: Production semantic caching should use vector embeddings\n";
echo "Consider: OpenAI embeddings, sentence-transformers, or pgvector\n";
