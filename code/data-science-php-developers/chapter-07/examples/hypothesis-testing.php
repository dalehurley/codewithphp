<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Statistics\HypothesisTester;

$tester = new HypothesisTester();

echo "=== Hypothesis Testing ===\n\n";

// 1. One-sample t-test: Is average page load time different from 2 seconds?
echo "1. One-Sample T-Test (Page Load Times):\n";

$loadTimes = [1.8, 2.1, 1.9, 2.3, 2.0, 1.7, 2.2, 1.9, 2.1, 2.0, 1.8, 2.0];
$expectedTime = 2.0;

$result = $tester->oneSampleTTest($loadTimes, $expectedTime, alpha: 0.05);

echo "   Null Hypothesis (H₀): Average load time = {$expectedTime}s\n";
echo "   Alternative (H₁): Average load time ≠ {$expectedTime}s\n\n";
echo "   Sample Mean: " . round($result['sample_mean'], 3) . "s\n";
echo "   t-statistic: " . round($result['t_statistic'], 3) . "\n";
echo "   p-value: " . round($result['p_value'], 4) . "\n";
echo "   Significant: " . ($result['significant'] ? 'Yes' : 'No') . " (α = 0.05)\n";
echo "   → {$result['interpretation']}\n\n";

// 2. Two-sample t-test: Is new feature faster than old?
echo "2. Two-Sample T-Test (A/B Test Performance):\n";

$oldFeature = [150, 145, 160, 155, 148, 152, 147, 153, 149, 151];
$newFeature = [135, 142, 130, 138, 140, 136, 133, 139, 137, 134];

$result = $tester->twoSampleTTest($oldFeature, $newFeature, alpha: 0.05);
$effectSize = $tester->cohensD($oldFeature, $newFeature);

echo "   Null Hypothesis (H₀): No difference in performance\n";
echo "   Alternative (H₁): New feature is faster\n\n";
echo "   Old Mean: " . round($result['mean1'], 1) . "ms\n";
echo "   New Mean: " . round($result['mean2'], 1) . "ms\n";
echo "   Difference: " . round($result['mean_difference'], 1) . "ms\n";
echo "   t-statistic: " . round($result['t_statistic'], 3) . "\n";
echo "   p-value: " . round($result['p_value'], 4) . "\n";
echo "   Significant: " . ($result['significant'] ? 'Yes' : 'No') . " (α = 0.05)\n";
echo "   Effect Size (Cohen's d): " . round($effectSize['cohens_d'], 2) . " ({$effectSize['effect_size']})\n";
echo "   → {$result['interpretation']}\n\n";

// 3. Z-test for proportions: Is conversion rate different from 10%?
echo "3. Z-Test for Proportion (Conversion Rate):\n";

$conversions = 87;
$visitors = 1000;
$expectedRate = 0.10; // 10%

$result = $tester->zTestProportion($conversions, $visitors, $expectedRate, alpha: 0.05);

echo "   Null Hypothesis (H₀): Conversion rate = 10%\n";
echo "   Alternative (H₁): Conversion rate ≠ 10%\n\n";
echo "   Observed: " . round($result['observed_proportion'] * 100, 2) . "%\n";
echo "   Expected: " . round($result['expected_proportion'] * 100, 2) . "%\n";
echo "   z-statistic: " . round($result['z_statistic'], 3) . "\n";
echo "   p-value: " . round($result['p_value'], 4) . "\n";
echo "   Significant: " . ($result['significant'] ? 'Yes' : 'No') . " (α = 0.05)\n";
echo "   → {$result['interpretation']}\n\n";

// 4. Chi-square test: Do preferences match expected distribution?
echo "4. Chi-Square Test (User Preferences):\n";

$observed = [45, 30, 15, 10]; // Actual clicks: Home, Products, About, Contact
$expected = [40, 35, 15, 10]; // Expected based on traffic

$result = $tester->chiSquareTest($observed, $expected, alpha: 0.05);

echo "   Null Hypothesis (H₀): Observed matches expected distribution\n";
echo "   Alternative (H₁): Distribution differs from expected\n\n";
echo "   Observed: " . implode(', ', $observed) . "\n";
echo "   Expected: " . implode(', ', $expected) . "\n";
echo "   χ² statistic: " . round($result['chi_square_statistic'], 3) . "\n";
echo "   Degrees of freedom: {$result['degrees_of_freedom']}\n";
echo "   p-value: " . round($result['p_value'], 4) . "\n";
echo "   Significant: " . ($result['significant'] ? 'Yes' : 'No') . " (α = 0.05)\n";
echo "   → {$result['interpretation']}\n\n";

// 5. Statistical power analysis
echo "5. Statistical Power Analysis:\n";

$effectSizes = [0.2, 0.5, 0.8]; // Small, medium, large
$sampleSize = 50;

foreach ($effectSizes as $effectSize) {
    $result = $tester->calculatePower($effectSize, $sampleSize, alpha: 0.05);
    
    echo "   Effect Size = {$effectSize}:\n";
    echo "     Power: " . round($result['power'] * 100, 1) . "%\n";
    echo "     → {$result['interpretation']}\n\n";
}

echo "✓ Hypothesis testing complete!\n";
