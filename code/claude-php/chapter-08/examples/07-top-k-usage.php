<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Note: Claude API doesn't expose top_k as directly as some models
// But understanding it helps grasp sampling mechanics

// Conceptual usage (if supported by SDK/Model):
// This example shows how top_k would work conceptually

echo "Top-k Usage Concept:\n\n";

echo "While Claude's API doesn't directly expose top_k,\n";
echo "understanding the concept helps with sampling strategies.\n\n";

echo "Lower top_k = more focused/conventional results\n";
echo "Higher top_k = more diverse possibilities\n\n";

// Example of how you might implement top_k filtering manually
// (Though this is not how Claude actually works internally)

function simulateTopKSampling(array $probs, int $topK): string
{
    arsort($probs); // Sort by probability
    $topTokens = array_slice($probs, 0, $topK, true);

    // Normalize probabilities among selected tokens
    $sum = array_sum($topTokens);
    $normalized = array_map(fn($p) => $p / $sum, $topTokens);

    // Sample from the filtered set
    $rand = mt_rand() / mt_getrandmax();
    $cumulative = 0;

    foreach ($normalized as $token => $prob) {
        $cumulative += $prob;
        if ($rand <= $cumulative) {
            return $token;
        }
    }

    return array_key_first($normalized);
}

// Demonstrate the concept
$frameworkProbs = [
    'Laravel' => 0.35,
    'Symfony' => 0.25,
    'CodeIgniter' => 0.15,
    'Yii' => 0.10,
    'Laminas' => 0.05,
];

echo "Simulating top_k sampling with different values:\n\n";

for ($k = 1; $k <= 5; $k++) {
    $results = [];
    for ($i = 0; $i < 5; $i++) {
        $results[] = simulateTopKSampling($frameworkProbs, $k);
    }

    $unique = array_unique($results);
    echo "top_k = {$k}: [" . implode(', ', $unique) . "] ";
    echo "(variety: " . count($unique) . "/5)\n";
}

echo "\nIn practice, use temperature and top_p for Claude.\n";
echo "Top_k is more common in older models like GPT-2/3.\n";
