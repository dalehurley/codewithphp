<?php
declare(strict_types=1);

/**
 * Demonstrates how top_p filters token choices
 */

// Token probabilities from Claude's prediction
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

function applyTopP(array $probs, float $topP): array
{
    // Sort by probability descending
    arsort($probs);

    $cumulative = 0;
    $filtered = [];

    foreach ($probs as $token => $prob) {
        $cumulative += $prob;
        $filtered[$token] = $prob;

        if ($cumulative >= $topP) {
            break;
        }
    }

    return $filtered;
}

// Compare different top_p values
$topPValues = [0.5, 0.8, 0.9, 1.0];

echo "Top-p (Nucleus Sampling) Demonstration:\n\n";

foreach ($topPValues as $topP) {
    echo "top_p = {$topP}:\n";

    $filtered = applyTopP($tokenProbabilities, $topP);

    echo "  Tokens considered: " . count($filtered) . "\n";
    echo "  Options: " . implode(', ', array_keys($filtered)) . "\n";
    echo "  Cumulative probability: " . round(array_sum($filtered) * 100, 1) . "%\n\n";
}

echo "Key Insights:\n";
echo "- top_p = 0.5: Only considers tokens until 50% cumulative probability\n";
echo "- top_p = 0.8: Includes more options (80% coverage)\n";
echo "- top_p = 0.9: Standard setting, good balance of quality and diversity\n";
echo "- top_p = 1.0: Considers all tokens (no filtering)\n";

