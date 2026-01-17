<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Statistics\ABTestAnalyzer;

$analyzer = new ABTestAnalyzer();

echo "=== A/B Testing Analysis ===\n\n";

// Example 1: Button Color Test (Conversion Rates)
echo "1. Button Color A/B Test:\n";
echo "   Control (Blue Button) vs Variant (Green Button)\n\n";

$controlConversions = 145;
$controlTotal = 2000;
$variantConversions = 178;
$variantTotal = 2000;

$result = $analyzer->analyzeConversionTest(
    $controlConversions,
    $controlTotal,
    $variantConversions,
    $variantTotal,
    alpha: 0.05
);

echo "   Control Group:\n";
echo "     Conversions: {$result['control']['conversions']} / {$result['control']['total']}\n";
echo "     Rate: " . round($result['control']['percentage'], 2) . "%\n";
echo "     95% CI: [" . round($result['control']['ci_lower'], 2) . "%, " . 
     round($result['control']['ci_upper'], 2) . "%]\n\n";

echo "   Variant Group:\n";
echo "     Conversions: {$result['variant']['conversions']} / {$result['variant']['total']}\n";
echo "     Rate: " . round($result['variant']['percentage'], 2) . "%\n";
echo "     95% CI: [" . round($result['variant']['ci_lower'], 2) . "%, " . 
     round($result['variant']['ci_upper'], 2) . "%]\n\n";

echo "   Analysis:\n";
echo "     Lift: " . round($result['analysis']['lift'], 1) . "%\n";
echo "     Absolute Difference: +" . round($result['analysis']['absolute_difference'], 2) . "%\n";
echo "     Z-statistic: " . round($result['analysis']['z_statistic'], 3) . "\n";
echo "     P-value: " . round($result['analysis']['p_value'], 4) . "\n";
echo "     Significant: " . ($result['analysis']['is_significant'] ? 'Yes' : 'No') . 
     " (95% confidence)\n\n";

echo "   Recommendation:\n";
echo "     {$result['recommendation']}\n\n";

// Example 2: Pricing Test (Revenue per User)
echo "2. Pricing Strategy A/B Test:\n";
echo "   Control (\$9.99) vs Variant (\$12.99)\n\n";

$controlRevenue = [9.99, 9.99, 0, 9.99, 0, 9.99, 9.99, 0, 9.99, 9.99, 0, 9.99, 9.99, 0, 9.99];
$variantRevenue = [12.99, 0, 12.99, 0, 12.99, 12.99, 0, 0, 12.99, 12.99, 0, 12.99, 0, 12.99, 12.99];

$result = $analyzer->analyzeContinuousTest($controlRevenue, $variantRevenue, alpha: 0.05);

echo "   Control Group:\n";
echo "     n = {$result['control']['n']}\n";
echo "     Mean Revenue: \$" . round($result['control']['mean'], 2) . "\n";
echo "     Std Dev: \$" . round($result['control']['std_dev'], 2) . "\n\n";

echo "   Variant Group:\n";
echo "     n = {$result['variant']['n']}\n";
echo "     Mean Revenue: \$" . round($result['variant']['mean'], 2) . "\n";
echo "     Std Dev: \$" . round($result['variant']['std_dev'], 2) . "\n\n";

echo "   Analysis:\n";
echo "     Difference: \$" . round($result['analysis']['difference'], 2) . "\n";
echo "     Lift: " . round($result['analysis']['lift'], 1) . "%\n";
echo "     95% CI: [\$" . round($result['analysis']['ci_lower'], 2) . ", \$" . 
     round($result['analysis']['ci_upper'], 2) . "]\n";
echo "     T-statistic: " . round($result['analysis']['t_statistic'], 3) . "\n";
echo "     P-value: " . round($result['analysis']['p_value'], 4) . "\n";
echo "     Significant: " . ($result['analysis']['is_significant'] ? 'Yes' : 'No') . "\n\n";

echo "   Recommendation:\n";
echo "     {$result['recommendation']}\n\n";

// Example 3: Sample Size Calculator
echo "3. Sample Size Planning for New Test:\n\n";

$baselineRate = 0.10; // Current 10% conversion rate
$mde = 0.10; // Want to detect 10% relative change (to 11%)
$alpha = 0.05;
$power = 0.80;

$sampleSize = $analyzer->calculateSampleSize($baselineRate, $mde, $alpha, $power);

echo "   Test Parameters:\n";
echo "     Baseline Rate: " . round($sampleSize['baseline_rate'], 1) . "%\n";
echo "     Expected Variant: " . round($sampleSize['expected_variant_rate'], 1) . "%\n";
echo "     Minimum Detectable Effect: " . round($sampleSize['minimum_detectable_effect'], 1) . "%\n";
echo "     Significance Level (α): " . ($alpha * 100) . "%\n";
echo "     Statistical Power: " . ($power * 100) . "%\n\n";

echo "   Required Sample Size:\n";
echo "     Per Group: " . number_format($sampleSize['sample_per_group']) . " users\n";
echo "     Total: " . number_format($sampleSize['total_sample']) . " users\n\n";

echo "   → {$sampleSize['interpretation']}\n\n";

// Example 4: Multiple Comparisons
echo "4. Multi-Variant Test (3 variants):\n\n";

$variants = [
    'Control' => ['conversions' => 100, 'total' => 1000],
    'Variant A' => ['conversions' => 115, 'total' => 1000],
    'Variant B' => ['conversions' => 95, 'total' => 1000],
];

// Compare each variant against control
foreach ($variants as $name => $data) {
    if ($name === 'Control') continue;
    
    $result = $analyzer->analyzeConversionTest(
        $variants['Control']['conversions'],
        $variants['Control']['total'],
        $data['conversions'],
        $data['total'],
        alpha: 0.05 / 2 // Bonferroni correction for multiple comparisons
    );
    
    echo "   {$name} vs Control:\n";
    echo "     Lift: " . round($result['analysis']['lift'], 1) . "%\n";
    echo "     P-value: " . round($result['analysis']['p_value'], 4) . "\n";
    echo "     Significant (α = 0.025): " . ($result['analysis']['is_significant'] ? 'Yes' : 'No') . "\n\n";
}

echo "   Note: Using Bonferroni correction (α/2 = 0.025) to account for multiple comparisons\n\n";

echo "✓ A/B testing analysis complete!\n";
