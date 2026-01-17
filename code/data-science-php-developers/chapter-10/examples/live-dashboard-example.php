<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\LiveDashboard;
use DataScience\Visualization\ChartBuilder;

echo "=== Live Dashboard Example ===\n\n";

$dashboard = new LiveDashboard();
$chartBuilder = new ChartBuilder();

// Add live stat cards with IDs for AJAX updates
$dashboard
    ->addLiveStatCard(
        id: 'total-users',
        title: 'Total Users',
        value: '12,847',
        trend: '+8.5%',
        icon: '👥',
        width: 3
    )
    ->addLiveStatCard(
        id: 'revenue',
        title: 'Revenue',
        value: '$45,231',
        trend: '+12.3%',
        icon: '💰',
        width: 3
    )
    ->addLiveStatCard(
        id: 'conversion',
        title: 'Conversion Rate',
        value: '3.42%',
        trend: '+0.8%',
        icon: '🎯',
        width: 3
    )
    ->addLiveStatCard(
        id: 'avg-order',
        title: 'Avg Order Value',
        value: '$127',
        trend: '-2.1%',
        icon: '🛒',
        width: 3
    );

// Model performance chart (will update via AJAX)
$performanceChart = $chartBuilder->modelPerformanceChart(
    dates: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    accuracyScores: [0.87, 0.89, 0.91, 0.90, 0.92, 0.91, 0.93],
    threshold: 0.85
);
$performanceChart = $chartBuilder->makeColorblindSafe($performanceChart);

$dashboard->addChart(
    id: 'modelPerformance',
    title: 'ML Model Performance (Live Updates)',
    chartConfig: $performanceChart,
    width: 8
);

// Conversion funnel (will update via AJAX)
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
                'text' => 'Live Conversion Funnel',
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

// Top products table (will update via AJAX)
$dashboard->addTable(
    title: 'Top 5 Products (Live)',
    headers: ['Product', 'Sales', 'Revenue', 'Trend'],
    rows: [
        ['Product A', '1,247', '$45,231', '+12%'],
        ['Product B', '892', '$31,220', '+8%'],
        ['Product C', '743', '$22,100', '-3%'],
        ['Product D', '612', '$18,360', '+15%'],
        ['Product E', '501', '$15,030', '+5%'],
    ],
    width: 12
);

// Generate live dashboard with API endpoint
// Data endpoint: /api/dashboard-data.php
$html = $dashboard->generateLive(
    title: 'Real-Time Business Dashboard',
    dataEndpoint: '/api/dashboard-data.php',
    updateInterval: 30 // Update every 30 seconds
);

file_put_contents(__DIR__ . '/../output/live-dashboard.html', $html);

echo "✓ Live dashboard generated successfully!\n";
echo "✓ Saved to: output/live-dashboard.html\n";
echo "\n✓ To use live updates:\n";
echo "   1. Start PHP development server:\n";
echo "      cd " . __DIR__ . "/../output\n";
echo "      php -S localhost:8000\n";
echo "   2. Open http://localhost:8000/live-dashboard.html\n";
echo "   3. Dashboard will update every 30 seconds from API\n";
echo "   4. Click 'Refresh Now' button for manual update\n";
echo "\n✓ Features:\n";
echo "   - AJAX updates without page reload\n";
echo "   - Visual update indicators\n";
echo "   - Error handling for failed updates\n";
echo "   - Manual refresh button\n";
echo "   - XSS protection in dynamic content\n";
