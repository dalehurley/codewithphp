<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\RAG\Reranking\ScoreReranker;
use ClaudeAgents\RAG\Reranking\LLMReranker;
use ClaudePhp\ClaudePhp;

/**
 * Reranking Results
 * 
 * Demonstrates reranking techniques to improve retrieval relevance:
 * - Score-based reranking with weighted signals
 * - LLM-based semantic relevance reranking
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          Reranking Results - Improving Relevance              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Initial retrieval results (simulated)
$initialResults = [
    [
        'id' => 'doc_1',
        'text' => 'PHP 8.4 introduces property hooks which provide clean syntax for implementing getters and setters without verbose boilerplate code. This feature simplifies class design and improves readability.',
        'score' => 0.85,  // Initial embedding similarity
        'metadata' => [
            'relevance' => 9,    // Domain-specific relevance
            'recency' => 0.95,   // Published recently
            'quality' => 8,      // Content quality score
            'views' => 1250,     // Popularity
        ],
    ],
    [
        'id' => 'doc_2',
        'text' => 'PHP versions prior to 8.0 required traditional getter and setter methods with manual implementation. Developers had to write repetitive code for property access.',
        'score' => 0.82,
        'metadata' => [
            'relevance' => 5,
            'recency' => 0.20,   // Older information
            'quality' => 6,
            'views' => 450,
        ],
    ],
    [
        'id' => 'doc_3',
        'text' => 'Property hooks reduce boilerplate code significantly while maintaining type safety and encapsulation. They integrate seamlessly with PHP\'s type system.',
        'score' => 0.80,
        'metadata' => [
            'relevance' => 8,
            'recency' => 0.90,
            'quality' => 9,
            'views' => 890,
        ],
    ],
    [
        'id' => 'doc_4',
        'text' => 'Laravel framework now supports PHP 8.4 features including property hooks in Eloquent models.',
        'score' => 0.78,
        'metadata' => [
            'relevance' => 6,
            'recency' => 0.85,
            'quality' => 7,
            'views' => 2100,
        ],
    ],
    [
        'id' => 'doc_5',
        'text' => 'Asymmetric visibility is another PHP 8.4 feature that complements property hooks by allowing different access levels for reading and writing properties.',
        'score' => 0.75,
        'metadata' => [
            'relevance' => 7,
            'recency' => 0.92,
            'quality' => 8,
            'views' => 670,
        ],
    ],
];

$query = 'PHP 8.4 property hooks syntax and usage';

// 1. Score-Based Reranking
echo "=== 1. Score-Based Reranking ===\n";
echo "Goal: Combine multiple signals for better ranking\n\n";

$scoreReranker = new ScoreReranker();

echo "Query: {$query}\n\n";

echo "Initial Results (by embedding similarity):\n";
foreach ($initialResults as $i => $result) {
    echo sprintf(
        "  %d. [%.3f] %s\n",
        $i + 1,
        $result['score'],
        substr($result['text'], 0, 60) . '...'
    );
}

echo "\nReranking with weighted signals...\n";
echo "  Weights: similarity=0.4, relevance=0.3, recency=0.2, quality=0.1\n\n";

// Rerank with composite scoring
$reranked = $scoreReranker->rerank($initialResults, $query, weights: [
    'score' => 0.4,        // Embedding similarity
    'relevance' => 0.3,    // Domain relevance
    'recency' => 0.2,      // Freshness
    'quality' => 0.1,      // Content quality
]);

echo "Reranked Results:\n";
foreach ($reranked as $i => $result) {
    $finalScore = $result['final_score'] ?? $result['score'];
    echo sprintf(
        "  %d. [%.3f] %s\n",
        $i + 1,
        $finalScore,
        substr($result['text'], 0, 60) . '...'
    );
    
    // Show score breakdown
    $meta = $result['metadata'];
    echo sprintf(
        "     Components: sim=%.2f, rel=%d, rec=%.2f, qual=%d\n",
        $result['score'],
        $meta['relevance'],
        $meta['recency'],
        $meta['quality']
    );
}

echo "\n✓ Result: More recent, relevant docs moved up\n";
echo "✓ Result: Old or tangential docs moved down\n\n";

echo str_repeat('─', 70) . "\n\n";

// 2. LLM-Based Reranking
echo "=== 2. LLM-Based Semantic Reranking ===\n";
echo "Goal: Use LLM to judge true semantic relevance\n\n";

$llmReranker = new LLMReranker($client);

$llmQuery = 'How do property hooks work in PHP 8.4?';

// Subset for LLM reranking (to save costs)
$candidatesForLLM = array_slice($initialResults, 0, 5);

echo "Query: {$llmQuery}\n\n";

echo "Candidates (initial ranking):\n";
foreach ($candidatesForLLM as $i => $result) {
    echo "  " . ($i + 1) . ". " . substr($result['text'], 0, 70) . "...\n";
}

echo "\nAsking LLM to judge relevance...\n";

// LLM reranks based on true semantic relevance
$llmReranked = $llmReranker->rerank($candidatesForLLM, $llmQuery);

echo "\nLLM-Reranked Results:\n";
foreach ($llmReranked as $i => $result) {
    $relevanceScore = $result['relevance_score'] ?? $result['score'];
    echo sprintf(
        "  %d. [%.3f] %s\n",
        $i + 1,
        $relevanceScore,
        substr($result['text'], 0, 70) . '...'
    );
}

echo "\n💡 Why LLM Reranking Works:\n";
echo "   • Understands semantic relevance beyond keywords\n";
echo "   • Recognizes implicit connections\n";
echo "   • Handles complex or ambiguous queries\n";
echo "   • Can incorporate domain knowledge\n\n";

echo "⚠️  Cost Consideration:\n";
echo "   • LLM reranking requires API calls\n";
echo "   • Use as second stage after score-based filtering\n";
echo "   • Rerank top-K candidates (e.g., top 10-20)\n\n";

echo str_repeat('─', 70) . "\n\n";

// 3. Hybrid Reranking Pipeline
echo "=== 3. Hybrid Reranking Pipeline ===\n";
echo "Goal: Combine score-based and LLM reranking\n\n";

echo "Production Pipeline:\n\n";

echo "Stage 1: Initial Retrieval\n";
echo "  → Retrieve top 50 candidates by embedding similarity\n";
echo "  → Fast vector search across entire corpus\n";
echo "  → Cost: ~0.1ms per query\n\n";

echo "Stage 2: Score-Based Reranking\n";
echo "  → Combine signals: similarity + metadata\n";
echo "  → Rerank all 50 candidates\n";
echo "  → Select top 10 for next stage\n";
echo "  → Cost: ~1ms per query\n\n";

echo "Stage 3: LLM Reranking (Optional)\n";
echo "  → LLM judges relevance of top 10\n";
echo "  → Reorders based on true semantic match\n";
echo "  → Select final top 5 for generation\n";
echo "  → Cost: ~100-200ms + API cost\n\n";

echo "Stage 4: Generate Answer\n";
echo "  → Use top 5 reranked sources\n";
echo "  → Generate answer with citations\n";
echo "  → Return to user\n\n";

echo "Benefits of Hybrid Approach:\n";
echo "  ✓ Balances speed and quality\n";
echo "  ✓ Reduces LLM API costs (only rerank top-K)\n";
echo "  ✓ Maintains high relevance\n";
echo "  ✓ Scales to large document collections\n\n";

echo str_repeat('─', 70) . "\n\n";

// 4. Comparison of Approaches
echo "=== Reranking Approach Comparison ===\n\n";

echo "┌─────────────────────┬──────────────┬────────────┬──────────┬───────────┐\n";
echo "│ Approach            │ Quality      │ Speed      │ Cost     │ Use When  │\n";
echo "├─────────────────────┼──────────────┼────────────┼──────────┼───────────┤\n";
echo "│ No Reranking        │ Baseline     │ ⚡⚡⚡      │ $        │ MVP/Demo  │\n";
echo "│ Score-Based         │ Good         │ ⚡⚡       │ $        │ Default   │\n";
echo "│ LLM Reranking       │ Excellent    │ ⚡         │ $$$$     │ Quality   │\n";
echo "│ Hybrid Pipeline     │ Excellent    │ ⚡⚡       │ $$       │ Production│\n";
echo "└─────────────────────┴──────────────┴────────────┴──────────┴───────────┘\n\n";

// 5. Reranking Signals
echo "=== Common Reranking Signals ===\n\n";

$signals = [
    ['Embedding Similarity', 'Primary relevance signal', 'High', 'Always'],
    ['Metadata Relevance', 'Domain-specific score', 'Medium', 'If available'],
    ['Recency/Freshness', 'Content age', 'Medium', 'Time-sensitive'],
    ['Content Quality', 'Manual or computed', 'Low', 'High-stakes'],
    ['Popularity/Views', 'User engagement', 'Low', 'User-facing'],
    ['Citation Count', 'Authority signal', 'Low', 'Academic'],
    ['User Feedback', 'Explicit ratings', 'High', 'If collected'],
];

echo "┌───────────────────────┬─────────────────────┬──────────┬─────────────┐\n";
echo "│ Signal                │ What It Measures    │ Impact   │ When to Use │\n";
echo "├───────────────────────┼─────────────────────┼──────────┼─────────────┤\n";

foreach ($signals as $signal) {
    echo sprintf(
        "│ %-21s │ %-19s │ %-8s │ %-11s │\n",
        $signal[0],
        $signal[1],
        $signal[2],
        $signal[3]
    );
}

echo "└───────────────────────┴─────────────────────┴──────────┴─────────────┘\n\n";

// 6. Best Practices
echo "=== Best Practices ===\n\n";

echo "1. **Start Simple, Add Complexity**\n";
echo "   • Begin with embedding similarity only\n";
echo "   • Add metadata signals if available\n";
echo "   • Consider LLM reranking for quality-critical apps\n\n";

echo "2. **Tune Weights for Your Domain**\n";
echo "   • Different domains need different weights\n";
echo "   • News: recency=high, technical docs: quality=high\n";
echo "   • Use validation set to optimize weights\n\n";

echo "3. **Monitor Reranking Impact**\n";
echo "   • Track: retrieval quality before/after\n";
echo "   • Measure: user satisfaction, click-through\n";
echo "   • A/B test: different reranking strategies\n\n";

echo "4. **Cost-Quality Trade-offs**\n";
echo "   • LLM reranking: expensive but high quality\n";
echo "   • Score-based: cheap and fast\n";
echo "   • Hybrid: best of both worlds\n\n";

echo "5. **Cache Reranking Results**\n";
echo "   • Cache LLM relevance scores\n";
echo "   • Invalidate on index updates\n";
echo "   • Saves cost on common queries\n\n";

echo "✅ Reranking demonstration complete!\n\n";

echo "💡 Key Takeaways:\n";
echo "   1. Reranking improves initial retrieval results\n";
echo "   2. Score-based: fast, cheap, good for most cases\n";
echo "   3. LLM-based: slow, expensive, best quality\n";
echo "   4. Hybrid: balance of speed and quality\n";
echo "   5. Tune weights and signals for your domain\n";
