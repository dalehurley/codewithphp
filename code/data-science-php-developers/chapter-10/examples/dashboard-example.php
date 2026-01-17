<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\DashboardGenerator;
use DataScience\Visualization\ChartBuilder;

echo "=== Dashboard Generation Example ===\n\n";

$dashboard = new DashboardGenerator();
$chartBuilder = new ChartBuilder();

// Stat cards with proper escaping
$dashboard
    ->addStatCard(
        title: 'Total Users',
        value: '12,847',
        trend: '+8.5%',
        icon: '👥',
        width: 3
    )
    ->addStatCard(
        title: 'Revenue',
        value: '$45,231',
        trend: '+12.3%',
        icon: '💰',
        width: 3
    )
    ->addStatCard(
        title: 'Conversion Rate',
        value: '3.42%',
        trend: '+0.8%',
        icon: '🎯',
        width: 3
    )
    ->addStatCard(
        title: 'Avg Order Value',
        value: '$127',
        trend: '-2.1%',
        icon: '🛒',
        width: 3
    );

// Model performance chart with accessibility
$performanceChart = $chartBuilder->modelPerformanceChart(
    dates: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    accuracyScores: [0.87, 0.89, 0.91, 0.90, 0.92, 0.91, 0.93],
    threshold: 0.85
);
$performanceChart = $chartBuilder->makeColorblindSafe($performanceChart);

$dashboard->addChart(
    id: 'modelPerformance',
    title: 'ML Model Performance (Last 7 Days)',
    chartConfig: $performanceChart,
    width: 8
);

// Conversion funnel
$funnelChart = $chartBuilder->barChart(
    labels: ['Visitors', 'Sign-ups', 'Trials', 'Paid'],
    datasets: [[
        'label' => 'Conversion Funnel',
        'data' => [10000, 1500, 450, 342],
    ]],
    options: [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Conversion Funnel',
            ],
        ],
    ]
);
$funnelChart = $chartBuilder->makeColorblindSafe($funnelChart);

$dashboard->addChart(
    id: 'conversionFunnel',
    title: 'Conversion Funnel',
    chartConfig: $funnelChart,
    width: 4
);

// Add filter for date range
$dashboard->addFilter(
    id: 'dateRange',
    label: 'Date Range',
    options: [
        '7d' => 'Last 7 Days',
        '30d' => 'Last 30 Days',
        '90d' => 'Last 90 Days',
        'all' => 'All Time',
    ],
    width: 3
);

// Top products table (data is pre-escaped by addTable method)
$dashboard->addTable(
    title: 'Top 5 Products',
    headers: ['Product', 'Sales', 'Revenue', 'Trend'],
    rows: [
        ['Product A', '1,247', '$45,231', '+12%'],
        ['Product B', '892', '$31,220', '+8%'],
        ['Product C', '743', '$22,100', '-3%'],
        ['Product D', '612', '$18,360', '+15%'],
        ['Product E', '501', '$15,030', '+5%'],
    ],
    width: 6
);

// Traffic sources pie chart
$trafficChart = $chartBuilder->pieChart(
    labels: ['Organic', 'Direct', 'Social', 'Referral', 'Paid'],
    data: [45, 25, 15, 10, 5]
);
$trafficChart = $chartBuilder->makeColorblindSafe($trafficChart);

$dashboard->addChart(
    id: 'trafficSources',
    title: 'Traffic Sources',
    chartConfig: $trafficChart,
    width: 6
);

// Generate dashboard with 30-second refresh
$html = $dashboard->generate(
    title: 'Business Intelligence Dashboard',
    refreshInterval: 30
);

file_put_contents(__DIR__ . '/../output/dashboard.html', $html);

echo "✓ Dashboard generated successfully!\n";
echo "✓ Open output/dashboard.html in your browser\n";
echo "✓ Dashboard will auto-refresh every 30 seconds\n";
echo "✓ Features:\n";
echo "   - Responsive design (test on mobile)\n";
echo "   - Dark mode support (check OS settings)\n";
echo "   - Print-friendly layout\n";
echo "   - Colorblind-safe color palettes\n";
echo "   - Interactive filter dropdown\n";
