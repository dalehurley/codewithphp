<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Statistics\ANOVAAnalyzer;

$anova = new ANOVAAnalyzer();

echo "=== ANOVA (Analysis of Variance) ===\n\n";

// 1. One-way ANOVA: Compare website load times across 3 servers
echo "1. Website Load Times Across Servers:\n\n";

$groups = [
    'Server A' => [245, 253, 238, 267, 241, 259, 248, 252],
    'Server B' => [312, 305, 318, 301, 314, 308, 311, 306],
    'Server C' => [198, 205, 192, 208, 195, 203, 199, 202],
];

echo "   Testing if server performance differs significantly\n\n";

foreach ($groups as $name => $data) {
    $mean = array_sum($data) / count($data);
    $stdDev = sqrt(array_sum(array_map(fn($x) => ($x - $mean) ** 2, $data)) / (count($data) - 1));
    echo "   {$name}:\n";
    echo "     n = " . count($data) . "\n";
    echo "     Mean: " . round($mean, 2) . " ms\n";
    echo "     Std Dev: " . round($stdDev, 2) . " ms\n\n";
}

$result = $anova->oneWayANOVA(array_values($groups), 0.05);

echo "   ANOVA Results:\n";
echo "     F-statistic: " . round($result['F_statistic'], 3) . "\n";
echo "     df (between): {$result['df_between']}\n";
echo "     df (within): {$result['df_within']}\n";
echo "     p-value: " . round($result['p_value'], 6) . "\n";
echo "     Significant: " . ($result['significant'] ? 'Yes' : 'No') . " (α = 0.05)\n\n";

// Calculate effect size
$effectSize = $anova->calculateEtaSquared($result['ss_between'], $result['ss_within']);
echo "   Effect Size:\n";
echo "     Eta-squared: " . round($effectSize['eta_squared'], 4) . "\n";
echo "     Category: {$effectSize['effect_size']}\n";
echo "     → {$effectSize['interpretation']}\n\n";

echo "   → {$result['interpretation']}\n\n";

// 2. One-way ANOVA: Compare conversion rates for different page designs
echo "2. Conversion Rates for Page Designs:\n\n";

$designGroups = [
    'Design A' => [0.085, 0.092, 0.088, 0.091, 0.087, 0.089],
    'Design B' => [0.102, 0.098, 0.105, 0.100, 0.103, 0.101],
    'Design C' => [0.078, 0.082, 0.080, 0.079, 0.081, 0.083],
];

echo "   Testing if design significantly affects conversion\n\n";

foreach ($designGroups as $name => $data) {
    $mean = array_sum($data) / count($data);
    echo "   {$name}: Mean = " . round($mean * 100, 2) . "%\n";
}
echo "\n";

$result = $anova->oneWayANOVA(array_values($designGroups), 0.05);

echo "   ANOVA Results:\n";
echo "     F-statistic: " . round($result['F_statistic'], 3) . "\n";
echo "     p-value: " . round($result['p_value'], 6) . "\n";
echo "     Significant: " . ($result['significant'] ? 'Yes' : 'No') . "\n\n";

$effectSize = $anova->calculateEtaSquared($result['ss_between'], $result['ss_within']);
echo "   Effect Size (Eta-squared): " . round($effectSize['eta_squared'], 4) . " ({$effectSize['effect_size']})\n\n";

echo "   → {$result['interpretation']}\n\n";

// 3. ANOVA components breakdown
echo "3. Understanding ANOVA Components:\n\n";

$simpleGroups = [
    [5, 6, 7, 8, 9],
    [10, 11, 12, 13, 14],
    [15, 16, 17, 18, 19],
];

$result = $anova->oneWayANOVA($simpleGroups, 0.05);

echo "   Sum of Squares:\n";
echo "     Between Groups (SSB): " . round($result['ss_between'], 2) . "\n";
echo "     Within Groups (SSW): " . round($result['ss_within'], 2) . "\n";
echo "     Total (SST): " . round($result['ss_between'] + $result['ss_within'], 2) . "\n\n";

echo "   Mean Squares:\n";
echo "     Between Groups (MSB): " . round($result['ms_between'], 2) . "\n";
echo "     Within Groups (MSW): " . round($result['ms_within'], 2) . "\n\n";

echo "   F-statistic = MSB / MSW = " . round($result['F_statistic'], 2) . "\n\n";

if ($result['significant']) {
    echo "   → Groups differ significantly. Follow up with post-hoc tests to find which pairs differ.\n\n";
} else {
    echo "   → No significant difference between groups.\n\n";
}

// 4. When to use ANOVA
echo "4. When to Use ANOVA:\n\n";
echo "   ✓ Use ANOVA when:\n";
echo "     - Comparing 3+ groups (use t-test for 2 groups)\n";
echo "     - Continuous dependent variable\n";
echo "     - Independent groups (between-subjects design)\n";
echo "     - Normal distribution within groups\n";
echo "     - Equal variances (homogeneity of variance)\n\n";

echo "   ⚠ Caution:\n";
echo "     - ANOVA tells you groups differ, not which ones\n";
echo "     - Significant ANOVA requires post-hoc tests\n";
echo "     - Consider effect size, not just p-value\n\n";

echo "✓ ANOVA analysis complete!\n";
