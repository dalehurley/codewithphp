#!/usr/bin/env php
<?php
/**
 * Relevance Scoring Example
 * 
 * Demonstrates multi-signal relevance scoring combining:
 * - Semantic similarity
 * - Recency (time decay)
 * - Access frequency
 * - Stored importance
 * 
 * Usage: php relevance-scoring.php
 */

declare(strict_types=1);

class RelevanceScorer
{
    private array $weights;
    
    public function __construct(array $weights = [])
    {
        $this->weights = array_merge([
            'semantic' => 0.5,
            'recency' => 0.2,
            'access_frequency' => 0.15,
            'importance' => 0.15,
        ], $weights);
    }
    
    public function score(array $memory, float $semanticSimilarity, float $queryTime): float
    {
        $scores = [
            'semantic' => $semanticSimilarity,
            'recency' => $this->recencyScore($memory, $queryTime),
            'access_frequency' => $this->accessFrequencyScore($memory),
            'importance' => $memory['importance'] ?? 0.5,
        ];
        
        $totalScore = 0.0;
        
        foreach ($scores as $signal => $score) {
            $totalScore += $score * $this->weights[$signal];
        }
        
        return $totalScore;
    }
    
    public function rank(array $memories, string $query, float $queryTime): array
    {
        foreach ($memories as &$item) {
            $breakdown = [
                'semantic' => $item['similarity'],
                'recency' => $this->recencyScore($item['memory'], $queryTime),
                'access_frequency' => $this->accessFrequencyScore($item['memory']),
                'importance' => $item['memory']['importance'] ?? 0.5,
            ];
            
            $item['relevance_score'] = $this->score(
                memory: $item['memory'],
                semanticSimilarity: $item['similarity'],
                queryTime: $queryTime
            );
            
            $item['score_breakdown'] = $breakdown;
        }
        
        usort($memories, fn($a, $b) => $b['relevance_score'] <=> $a['relevance_score']);
        
        return $memories;
    }
    
    private function recencyScore(array $memory, float $queryTime): float
    {
        $createdAt = $memory['created_at'];
        $lastAccessed = $memory['last_accessed_at'] ?? $createdAt;
        
        $referenceTime = max($createdAt, $lastAccessed);
        
        $daysSince = max(0, ($queryTime - $referenceTime) / 86400);
        
        $halfLife = 30.0;
        $decayRate = log(2) / $halfLife;
        
        return exp(-$decayRate * $daysSince);
    }
    
    private function accessFrequencyScore(array $memory): float
    {
        $accessCount = $memory['access_count'] ?? 0;
        
        if ($accessCount === 0) {
            return 0.0;
        }
        
        return min(1.0, log($accessCount + 1) / log(100));
    }
}

// Mock embedding service (simplified)
class MockEmbeddingService
{
    public function embed(string $text): array
    {
        $hash = hash('sha256', $text);
        $embedding = [];
        
        for ($i = 0; $i < 384; $i++) {
            $hexPair = substr($hash, ($i * 2) % 64, 2);
            $value = hexdec($hexPair) / 255.0;
            $embedding[] = ($value - 0.5) * 2.0;
        }
        
        return $this->normalize($embedding);
    }
    
    public function similarity(array $e1, array $e2): float
    {
        if (count($e1) !== count($e2) || count($e1) === 0) return 0.0;
        
        $dot = $norm1 = $norm2 = 0.0;
        for ($i = 0; $i < count($e1); $i++) {
            $dot += $e1[$i] * $e2[$i];
            $norm1 += $e1[$i] * $e1[$i];
            $norm2 += $e2[$i] * $e2[$i];
        }
        
        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);
        
        return ($norm1 === 0.0 || $norm2 === 0.0) ? 0.0 : $dot / ($norm1 * $norm2);
    }
    
    private function normalize(array $vector): array
    {
        $norm = sqrt(array_sum(array_map(fn($v) => $v * $v, $vector)));
        return $norm === 0.0 ? $vector : array_map(fn($v) => $v / $norm, $vector);
    }
}

// Main execution
echo "=== Relevance Scoring Example ===\n\n";

$embeddings = new MockEmbeddingService();
$scorer = new RelevanceScorer();

// Create sample memories with different characteristics
$now = time();
$day = 86400;

$memories = [
    [
        'id' => 'mem_1',
        'content' => 'PHP is a popular server-side scripting language',
        'importance' => 0.8,
        'access_count' => 25,
        'created_at' => $now - (5 * $day),    // 5 days ago
        'last_accessed_at' => $now - (1 * $day), // Accessed recently
    ],
    [
        'id' => 'mem_2',
        'content' => 'Laravel is a modern PHP framework with elegant syntax',
        'importance' => 0.7,
        'access_count' => 5,
        'created_at' => $now - (2 * $day),    // 2 days ago
        'last_accessed_at' => null,
    ],
    [
        'id' => 'mem_3',
        'content' => 'Python is used for machine learning and data science',
        'importance' => 0.6,
        'access_count' => 50,                  // Very popular
        'created_at' => $now - (60 * $day),   // Old
        'last_accessed_at' => $now - (45 * $day),
    ],
    [
        'id' => 'mem_4',
        'content' => 'Composer manages PHP dependencies efficiently',
        'importance' => 0.9,                   // Very important
        'access_count' => 3,
        'created_at' => $now - (30 * $day),
        'last_accessed_at' => $now - (7 * $day),
    ],
];

$query = "Tell me about PHP development tools";
$queryEmbedding = $embeddings->embed($query);

echo "Query: \"{$query}\"\n\n";

// Calculate similarities and prepare for ranking
$results = [];

foreach ($memories as $memory) {
    $memoryEmbedding = $embeddings->embed($memory['content']);
    $similarity = $embeddings->similarity($queryEmbedding, $memoryEmbedding);
    
    $results[] = [
        'memory' => $memory,
        'similarity' => $similarity,
    ];
}

// Rank by relevance
$rankedResults = $scorer->rank($results, $query, $now);

// Display results
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Ranked Results (by composite relevance score)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

foreach ($rankedResults as $i => $result) {
    $memory = $result['memory'];
    $breakdown = $result['score_breakdown'];
    
    echo sprintf("Rank %d: %.3f overall score\n", $i + 1, $result['relevance_score']);
    echo "Content: {$memory['content']}\n\n";
    
    echo "Score Breakdown:\n";
    echo sprintf("  • Semantic Similarity: %.3f (weight: %.2f)\n", 
        $breakdown['semantic'], $scorer->weights['semantic'] ?? 0.5);
    echo sprintf("  • Recency:            %.3f (weight: %.2f)\n", 
        $breakdown['recency'], $scorer->weights['recency'] ?? 0.2);
    echo sprintf("  • Access Frequency:    %.3f (weight: %.2f)\n", 
        $breakdown['access_frequency'], $scorer->weights['access_frequency'] ?? 0.15);
    echo sprintf("  • Importance:          %.3f (weight: %.2f)\n", 
        $breakdown['importance'], $scorer->weights['importance'] ?? 0.15);
    
    echo "\nMemory Stats:\n";
    echo "  • Age: " . round(($now - $memory['created_at']) / $day) . " days\n";
    echo "  • Access Count: {$memory['access_count']}\n";
    echo "  • Stored Importance: {$memory['importance']}\n";
    
    if ($memory['last_accessed_at']) {
        echo "  • Last Accessed: " . round(($now - $memory['last_accessed_at']) / $day) . " days ago\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
}

// Compare different weight configurations
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Comparing Different Weight Configurations\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$configurations = [
    'Balanced' => [
        'semantic' => 0.5,
        'recency' => 0.2,
        'access_frequency' => 0.15,
        'importance' => 0.15,
    ],
    'Semantic-First' => [
        'semantic' => 0.7,
        'recency' => 0.1,
        'access_frequency' => 0.1,
        'importance' => 0.1,
    ],
    'Popularity-First' => [
        'semantic' => 0.3,
        'recency' => 0.2,
        'access_frequency' => 0.4,
        'importance' => 0.1,
    ],
    'Recency-First' => [
        'semantic' => 0.3,
        'recency' => 0.5,
        'access_frequency' => 0.1,
        'importance' => 0.1,
    ],
];

foreach ($configurations as $name => $weights) {
    $scorer = new RelevanceScorer($weights);
    $ranked = $scorer->rank($results, $query, $now);
    
    echo "{$name} Strategy:\n";
    
    foreach ($ranked as $i => $result) {
        echo sprintf("  %d. [%.3f] %s\n", 
            $i + 1, 
            $result['relevance_score'],
            substr($result['memory']['content'], 0, 50) . '...'
        );
    }
    
    echo "\n";
}

echo "✓ Relevance scoring example complete!\n\n";
echo "Key Insights:\n";
echo "• Different weight configurations prioritize different signals\n";
echo "• Semantic-first: Best for knowledge retrieval\n";
echo "• Popularity-first: Best for frequently used information\n";
echo "• Recency-first: Best for time-sensitive information\n";
echo "• Balanced: Good default for most use cases\n";
