<?php
declare(strict_types=1);

/**
 * Top-k is simpler than top-p: just take the top k tokens
 */

function applyTopK(array $probs, int $topK): array
{
    // Sort by probability descending
    arsort($probs);

    // Take only top k tokens
    return array_slice($probs, 0, $topK, true);
}

$tokenProbabilities = [
    'Laravel' => 0.35,
    'Symfony' => 0.25,
    'CodeIgniter' => 0.15,
    'Yii' => 0.10,
    'Laminas' => 0.05,
    'Slim' => 0.04,
    'CakePHP' => 0.03,
    'Phalcon' => 0.02,
    'FuelPHP' => 0.01,
];

$topKValues = [1, 3, 5, 10];

echo "Top-k Sampling Demonstration:\n\n";

foreach ($topKValues as $k) {
    echo "top_k = {$k}:\n";

    $filtered = applyTopK($tokenProbabilities, $k);

    echo "  Options: " . implode(', ', array_keys($filtered)) . "\n";
    echo "  Probability coverage: " . round(array_sum($filtered) * 100, 1) . "%\n\n";
}

echo "Key Insights:\n";
echo "- top_k = 1: Only the single best token (most deterministic)\n";
echo "- top_k = 3: Top 3 tokens (75% probability coverage)\n";
echo "- top_k = 5: Top 5 tokens (90% probability coverage)\n";
echo "- top_k = 10+: All available tokens (no filtering)\n\n";

echo "Note: Top-k is less commonly used than top-p in modern language models.\n";
echo "Top-p is generally preferred as it adapts to different probability distributions.\n";

