<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Statistics\ConfidenceIntervalCalculator;

$calculator = new ConfidenceIntervalCalculator();

echo "=== Confidence Intervals ===\n\n";

// 1. Confidence interval for mean (customer satisfaction)
echo "1. Customer Satisfaction Scores:\n";

$satisfaction = [4.2, 4.5, 3.8, 4.7, 4.1, 4.3, 4.6, 3.9, 4.4, 4.2, 4.5, 4.3];
$ci = $calculator->forMean($satisfaction, 0.95);

echo "   Scores: " . implode(', ', array_slice($satisfaction, 0, 6)) . "...\n";
echo "   Sample size: {$ci['sample_size']}\n";
echo "   Mean: " . round($ci['mean'], 2) . "\n";
echo "   Std Error: " . round($ci['std_error'], 3) . "\n";
echo "   95% CI: [" . round($ci['lower_bound'], 2) . ", " . round($ci['upper_bound'], 2) . "]\n";
echo "   → " . $calculator->format($ci) . "\n";
echo "   → We're 95% confident the true average satisfaction is between " .
     round($ci['lower_bound'], 2) . " and " . round($ci['upper_bound'], 2) . "\n\n";

// 2. Confidence interval for proportion (conversion rate)
echo "2. Website Conversion Rate:\n";

$visitors = 1000;
$conversions = 87;
$ci = $calculator->forProportion($conversions, $visitors, 0.95);

echo "   Visitors: {$visitors}\n";
echo "   Conversions: {$conversions}\n";
echo "   Conversion rate: " . round($ci['percentage'], 2) . "%\n";
echo "   95% CI: [" . round($ci['lower_bound'] * 100, 2) . "%, " . 
     round($ci['upper_bound'] * 100, 2) . "%]\n";
echo "   → " . $calculator->format($ci) . "\n";
echo "   → We're 95% confident the true conversion rate is between " .
     round($ci['lower_bound'] * 100, 2) . "% and " . round($ci['upper_bound'] * 100, 2) . "%\n\n";

// 3. Confidence interval for difference (A/B test)
echo "3. A/B Test: New vs Old Design:\n";

$oldDesign = [4.2, 4.1, 4.3, 4.0, 4.2, 4.1, 4.3, 4.2, 4.1, 4.2];
$newDesign = [4.5, 4.7, 4.6, 4.8, 4.5, 4.6, 4.7, 4.5, 4.6, 4.7];

$ci = $calculator->forMeanDifference($newDesign, $oldDesign, 0.95);

echo "   Old design mean: " . round($ci['mean2'], 2) . "\n";
echo "   New design mean: " . round($ci['mean1'], 2) . "\n";
echo "   Difference: " . round($ci['mean_difference'], 2) . "\n";
echo "   95% CI for difference: [" . round($ci['lower_bound'], 2) . ", " . 
     round($ci['upper_bound'], 2) . "]\n";

if ($ci['lower_bound'] > 0) {
    echo "   → New design is significantly better (CI doesn't include 0)\n";
} elseif ($ci['upper_bound'] < 0) {
    echo "   → Old design is significantly better (CI doesn't include 0)\n";
} else {
    echo "   → No significant difference (CI includes 0)\n";
}

echo "\n";

// 4. Effect of sample size on confidence interval width
echo "4. Effect of Sample Size on CI Width:\n\n";

$population = array_fill(0, 10000, 0);
for ($i = 0; $i < 10000; $i++) {
    $population[$i] = 100 + (mt_rand() / mt_getrandmax()) * 20; // Mean ~110
}

foreach ([10, 50, 100, 500] as $sampleSize) {
    $sample = array_slice($population, 0, $sampleSize);
    $ci = $calculator->forMean($sample, 0.95);
    $width = $ci['upper_bound'] - $ci['lower_bound'];
    
    echo "   n = {$sampleSize}:\n";
    echo "     Mean: " . round($ci['mean'], 2) . "\n";
    echo "     CI: [" . round($ci['lower_bound'], 2) . ", " . round($ci['upper_bound'], 2) . "]\n";
    echo "     Width: " . round($width, 2) . "\n\n";
}

echo "   → Larger samples = narrower confidence intervals = more precision\n\n";

echo "✓ Confidence interval analysis complete!\n";
