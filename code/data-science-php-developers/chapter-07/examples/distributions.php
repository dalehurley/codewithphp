<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Statistics\DistributionAnalyzer;

$analyzer = new DistributionAnalyzer();

echo "=== Statistical Distributions ===\n\n";

// 1. Test if data is normally distributed
echo "1. Testing for Normal Distribution:\n";

$heights = [165, 170, 168, 172, 169, 171, 167, 173, 166, 170, 169, 171, 168, 170, 172];
$result = $analyzer->isNormallyDistributed($heights);

echo "   Heights data: " . implode(', ', array_slice($heights, 0, 5)) . "...\n";
echo "   Is normal: " . ($result['is_normal'] ? 'Yes' : 'No') . "\n";
echo "   Mean: " . round($result['mean'], 2) . " cm\n";
echo "   Std Dev: " . round($result['std_dev'], 2) . " cm\n";
echo "   Skewness: " . round($result['skewness'], 3) . "\n";
echo "   Kurtosis: " . round($result['kurtosis'], 3) . "\n";
echo "   → {$result['interpretation']}\n\n";

// 2. Calculate z-scores
echo "2. Z-Scores (Standard Scores):\n";

$testScores = [85, 92, 78, 95, 88];
$mean = array_sum($testScores) / count($testScores);
$variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $testScores)) / count($testScores);
$stdDev = sqrt($variance);

echo "   Test scores: " . implode(', ', $testScores) . "\n";
echo "   Mean: " . round($mean, 2) . "\n";
echo "   Std Dev: " . round($stdDev, 2) . "\n\n";

foreach ([78, 88, 95] as $score) {
    $z = $analyzer->zScore($score, $mean, $stdDev);
    echo "   Score {$score}:\n";
    echo "     Z-score: " . round($z['z_score'], 2) . "\n";
    echo "     Percentile: " . round($z['percentile'], 1) . "%\n";
    echo "     → {$z['interpretation']}\n\n";
}

// 3. Normal distribution probabilities
echo "3. Normal Distribution Probabilities:\n";
echo "   Heights: Mean = 170cm, Std Dev = 10cm\n\n";

foreach ([160, 170, 180, 190] as $height) {
    $prob = $analyzer->normalProbability($height, 170, 10);
    echo "   Height {$height}cm:\n";
    echo "     Percentile: " . round($prob['percentile'], 1) . "%\n";
    echo "     P(X <= {$height}): " . round($prob['cdf'], 3) . "\n\n";
}

// 4. Binomial distribution (coin flips)
echo "4. Binomial Distribution (10 coin flips):\n";

for ($heads = 0; $heads <= 10; $heads += 2) {
    $prob = $analyzer->binomialProbability(10, $heads, 0.5);
    echo "   {$heads} heads: " . round($prob['probability'] * 100, 2) . "% probability\n";
}

echo "\n   Expected heads: " . $analyzer->binomialProbability(10, 5, 0.5)['expected_value'] . "\n\n";

// 5. Poisson distribution (website visits)
echo "5. Poisson Distribution (average 5 visits/hour):\n";

for ($visits = 0; $visits <= 10; $visits += 2) {
    $prob = $analyzer->poissonProbability(5, $visits);
    echo "   {$visits} visits: " . round($prob['probability'] * 100, 2) . "% probability\n";
}

echo "\n✓ Distribution analysis complete!\n";
