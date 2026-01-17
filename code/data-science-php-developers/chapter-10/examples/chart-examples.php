<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\ChartBuilder;

echo "=== Chart Examples Generation ===\n\n";

$chartBuilder = new ChartBuilder();

// Example 1: Model performance over time with accessibility
echo "1. Generating model performance chart...\n";

$dates = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];
$accuracyScores = [0.87, 0.89, 0.91, 0.88, 0.92];

$performanceChart = $chartBuilder->modelPerformanceChart(
    dates: $dates,
    accuracyScores: $accuracyScores,
    threshold: 0.85
);

// Make chart colorblind-safe and accessible
$performanceChart = $chartBuilder->makeColorblindSafe($performanceChart);
$performanceChart = $chartBuilder->makeAccessible(
    $performanceChart,
    'ML Model Accuracy Over Time - Shows model performance trending above 85% threshold'
);

$stats = [
    ['label' => 'Current Accuracy', 'value' => '92%'],
    ['label' => 'Avg Accuracy', 'value' => '89.4%'],
    ['label' => 'Predictions', 'value' => '1,247'],
];

$title = 'ML Model Performance Dashboard';
$chartJson = $chartBuilder->toJson($performanceChart);
$description = "Model performance has improved steadily over the past 5 weeks. Current accuracy of 92% exceeds the 85% threshold, indicating excellent model health.";
$chartDescription = "Line chart showing model accuracy improving from 87% to 92% over 5 weeks";
$accessibleDescription = "The chart shows model accuracy starting at 87% in week 1, rising to 89% in week 2, peaking at 91% in week 3, dipping slightly to 88% in week 4, and reaching the highest point of 92% in week 5. A horizontal threshold line is shown at 85%, and the accuracy line stays above this threshold throughout the period.";

// Generate HTML
ob_start();
include __DIR__ . '/../templates/chart-template.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/../output/model-performance.html', $html);
echo "   ✓ Generated model-performance.html\n";

// Example 2: A/B Test Results
echo "2. Generating A/B test results chart...\n";

$abTestChart = $chartBuilder->abTestChart(
    controlLabel: 'Control (Original)',
    controlConversion: 0.12,
    treatmentLabel: 'Treatment (New Design)',
    treatmentConversion: 0.15,
    pValue: 0.023
);

$abTestChart = $chartBuilder->makeColorblindSafe($abTestChart);

$stats = [
    ['label' => 'Control Conversion', 'value' => '12.0%'],
    ['label' => 'Treatment Conversion', 'value' => '15.0%'],
    ['label' => 'Lift', 'value' => '+25%'],
    ['label' => 'P-Value', 'value' => '0.023'],
];

$title = 'A/B Test Results';
$chartJson = $chartBuilder->toJson($abTestChart);
$description = "The new design achieved a 25% lift in conversion rate (12% → 15%) with statistical significance (p=0.023 < 0.05). Recommendation: Deploy to 100% of users.";
$chartDescription = "Bar chart comparing control at 12% vs treatment at 15% conversion rate";
$accessibleDescription = "Bar chart showing two bars: Control group with 12% conversion rate in blue, and Treatment group with 15% conversion rate in green, indicating statistical significance.";

ob_start();
include __DIR__ . '/../templates/chart-template.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/../output/ab-test-results.html', $html);
echo "   ✓ Generated ab-test-results.html\n";

// Example 3: Sales trend with large dataset downsampling
echo "3. Generating sales trend with downsampling...\n";

// Simulate large dataset (365 days)
$allDates = [];
$allSales = [];
for ($i = 0; $i < 365; $i++) {
    $allDates[] = 'Day ' . ($i + 1);
    $allSales[] = 40000 + rand(-5000, 10000) + ($i * 50); // Upward trend with noise
}

// Downsample to 100 points for performance
$downsampledSales = $chartBuilder->downsampleData($allSales, 100);

// Create corresponding date labels
$step = (int)floor(count($allDates) / count($downsampledSales));
$downsampledDates = [];
for ($i = 0; $i < count($downsampledSales); $i++) {
    $downsampledDates[] = $allDates[$i * $step] ?? end($allDates);
}

$salesChart = $chartBuilder->lineChart(
    labels: $downsampledDates,
    datasets: [
        [
            'label' => '2026 Sales (Downsampled)',
            'data' => $downsampledSales,
        ],
    ],
    options: [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Sales Performance (365 days downsampled to 100 points)',
            ],
        ],
    ]
);

$salesChart = $chartBuilder->makeColorblindSafe($salesChart);

$stats = [
    ['label' => 'Data Points', 'value' => '365 → 100'],
    ['label' => 'Avg Daily Sales', 'value' => '$' . number_format((int)array_sum($downsampledSales) / count($downsampledSales))],
    ['label' => 'Growth', 'value' => '+32%'],
];

$title = 'Sales Performance (Large Dataset Example)';
$chartJson = $chartBuilder->toJson($salesChart);
$description = "Demonstrates LTTB downsampling algorithm that reduces 365 data points to 100 while preserving visual shape and trends. Useful for rendering performance with large datasets.";
$chartDescription = "Line chart showing sales trend over 365 days with intelligent downsampling";

ob_start();
include __DIR__ . '/../templates/chart-template.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/../output/sales-trend-downsampled.html', $html);
echo "   ✓ Generated sales-trend-downsampled.html\n";

echo "\n✓ All chart examples generated successfully!\n";
echo "✓ Open output/*.html in your browser to view charts\n";
echo "✓ Try downloading charts as PNG/SVG using the download buttons\n";
echo "✓ Test accessibility with screen readers (NVDA, JAWS, VoiceOver)\n";
echo "✓ Test responsive design by resizing browser window\n";
echo "✓ Test dark mode by enabling it in your OS settings\n";
