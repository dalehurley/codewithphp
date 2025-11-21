<?php
declare(strict_types=1);

/**
 * Conceptual demonstration of how Claude predicts the next token
 *
 * When Claude generates text, it:
 * 1. Looks at all tokens generated so far
 * 2. Calculates probability for EVERY possible next token
 * 3. Uses sampling parameters to choose which token to output
 * 4. Adds that token to the sequence
 * 5. Repeats until complete
 */

// Simplified example: Claude is completing "The best PHP framework is"
// Here are the top predicted tokens and their probabilities:

$tokenProbabilities = [
    'Laravel' => 0.45,      // 45% probability
    'Symfony' => 0.25,      // 25%
    'CodeIgniter' => 0.10,  // 10%
    'Yii' => 0.08,          // 8%
    'Laminas' => 0.05,      // 5%
    'Slim' => 0.04,         // 4%
    'CakePHP' => 0.03,      // 3%
    // ... thousands of other tokens with tiny probabilities
];

// Different sampling strategies will choose different tokens
// Let's simulate this:

function greedySampling(array $probs): string
{
    // Always pick the highest probability token
    arsort($probs);
    return array_key_first($probs);
}

function temperatureSampling(array $probs, float $temperature): string
{
    // Adjust probabilities based on temperature
    $adjusted = [];
    foreach ($probs as $token => $prob) {
        // Higher temperature = flatter distribution (more random)
        // Lower temperature = sharper distribution (more deterministic)
        $adjusted[$token] = pow($prob, 1 / $temperature);
    }

    // Normalize
    $sum = array_sum($adjusted);
    $normalized = array_map(fn($p) => $p / $sum, $adjusted);

    // Sample randomly based on adjusted probabilities
    return weightedRandomChoice($normalized);
}

function weightedRandomChoice(array $probabilities): string
{
    $rand = mt_rand() / mt_getrandmax();
    $cumulative = 0;

    foreach ($probabilities as $token => $probability) {
        $cumulative += $probability;
        if ($rand <= $cumulative) {
            return $token;
        }
    }

    return array_key_first($probabilities);
}

// Demonstrate different sampling strategies
echo "Original probabilities:\n";
print_r($tokenProbabilities);
echo "\n";

echo "Greedy sampling (always picks highest): ";
echo greedySampling($tokenProbabilities) . "\n\n";

echo "Temperature = 0.3 (deterministic): ";
echo temperatureSampling($tokenProbabilities, 0.3) . "\n";

echo "Temperature = 1.0 (balanced): ";
echo temperatureSampling($tokenProbabilities, 1.0) . "\n";

echo "Temperature = 2.0 (creative): ";
echo temperatureSampling($tokenProbabilities, 2.0) . "\n";

