<?php

/**
 * Decision framework: Should you use ML?
 */
function shouldUseMachineLearning(array $problem): array
{
    $score = 0;
    $reasons = [];

    // ✅ Good candidates for ML
    if ($problem['has_patterns_in_data']) {
        $score += 3;
        $reasons[] = "✓ Data contains learnable patterns";
    }

    if ($problem['rules_are_complex_or_unknown']) {
        $score += 3;
        $reasons[] = "✓ Rules are too complex to code manually";
    }

    if ($problem['has_large_dataset']) {
        $score += 2;
        $reasons[] = "✓ Sufficient data for training";
    }

    if ($problem['needs_to_adapt']) {
        $score += 2;
        $reasons[] = "✓ System needs to adapt to changing patterns";
    }

    // ❌ Bad candidates for ML
    if ($problem['rules_are_simple_and_known']) {
        $score -= 3;
        $reasons[] = "✗ Simple rules—traditional code is better";
    }

    if ($problem['requires_100_percent_accuracy']) {
        $score -= 2;
        $reasons[] = "✗ ML is probabilistic—can't guarantee 100% accuracy";
    }

    if ($problem['has_small_dataset']) {
        $score -= 2;
        $reasons[] = "✗ Insufficient data for training";
    }

    if ($problem['needs_explainability']) {
        $score -= 1;
        $reasons[] = "⚠ ML models can be hard to explain (use interpretable models)";
    }

    $recommendation = match(true) {
        $score >= 5 => "Strong candidate for ML",
        $score >= 3 => "Good candidate for ML",
        $score >= 0 => "Consider ML, but evaluate alternatives",
        default => "ML probably not the best approach"
    };

    return [
        'score' => $score,
        'recommendation' => $recommendation,
        'reasons' => $reasons,
    ];
}

// Example 1: Spam detection
echo "=== Spam Detection ===\n";
$spamDetection = shouldUseMachineLearning([
    'has_patterns_in_data' => true,
    'rules_are_complex_or_unknown' => true,
    'has_large_dataset' => true,
    'needs_to_adapt' => true,
    'rules_are_simple_and_known' => false,
    'requires_100_percent_accuracy' => false,
    'has_small_dataset' => false,
    'needs_explainability' => false,
]);

echo "Score: {$spamDetection['score']}\n";
echo "Recommendation: {$spamDetection['recommendation']}\n";
foreach ($spamDetection['reasons'] as $reason) {
    echo "  {$reason}\n";
}
echo "\n";

// Example 2: Tax calculation
echo "=== Tax Calculation ===\n";
$taxCalculation = shouldUseMachineLearning([
    'has_patterns_in_data' => false,
    'rules_are_complex_or_unknown' => false,
    'has_large_dataset' => false,
    'needs_to_adapt' => false,
    'rules_are_simple_and_known' => true,
    'requires_100_percent_accuracy' => true,
    'has_small_dataset' => true,
    'needs_explainability' => true,
]);

echo "Score: {$taxCalculation['score']}\n";
echo "Recommendation: {$taxCalculation['recommendation']}\n";
foreach ($taxCalculation['reasons'] as $reason) {
    echo "  {$reason}\n";
}
