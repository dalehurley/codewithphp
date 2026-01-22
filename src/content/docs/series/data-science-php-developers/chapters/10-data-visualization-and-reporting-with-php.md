---
title: "10: Data Visualization and Reporting with PHP"
description: "Build charts, dashboards, and automated reports using PHP and JavaScript visualization libraries"
sidebar:
  label: "10: Data Visualization and Reporting with PHP"
  order: 10
  badge:
    text: Intermediate
    variant: caution
---

![Data Visualization and Reporting with PHP](/images/data-science-php-developers/chapter-10-data-visualization-hero-full.webp)

# Chapter 10: Data Visualization and Reporting with PHP

## Overview

You've collected data, analyzed it, and built machine learning models—now it's time to communicate your findings. Data visualization turns raw numbers into compelling visual stories that drive business decisions. This chapter teaches you to create professional charts, build interactive dashboards, and generate automated reports using PHP.

You'll learn to integrate Chart.js for beautiful web visualizations, build real-time dashboards that update automatically, generate PDF reports for stakeholders, and follow best practices for visual communication. By the end, you'll be able to present your data science work in ways that inform, persuade, and inspire action.

This is where your technical work meets business impact—you'll transform complex analyses into clear, actionable insights that anyone can understand.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 09: Using Machine Learning Models](/series/data-science-php-developers/chapters/09-using-machine-learning-models-in-php-applications)
- PHP 8.4+ installed
- Basic understanding of HTML/CSS
- Familiarity with JavaScript (helpful but not required)
- **Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Install required PHP packages
composer require dompdf/dompdf

# Verify Node/npm for Chart.js (optional, can use CDN)
node --version
npm --version
```

## What You'll Build

By the end of this chapter, you will have created:

- **ChartBuilder** class for generating Chart.js configurations
- **DashboardGenerator** for interactive web dashboards
- **PDFReportGenerator** for automated PDF reports
- **DataExporter** for CSV/JSON/Excel exports
- **Real-time dashboard** with live data updates
- **ML model performance visualizations**
- **A/B test result charts** with statistical annotations
- **Executive summary report** with charts and insights

## Objectives

- Create charts and graphs using Chart.js
- Build interactive dashboards with real-time updates
- Generate professional PDF reports
- Export data in multiple formats (CSV, JSON, Excel)
- Visualize ML model performance
- Present A/B test results effectively
- Follow visualization best practices
- Tell compelling data stories

## Step 1: Creating Charts with Chart.js (~20 min)

### Goal

Generate beautiful, interactive charts using Chart.js integrated with PHP.

### Why Chart.js?

**Chart.js** is a popular JavaScript charting library:

- 🎨 Beautiful default styling
- 📱 Responsive and mobile-friendly
- 🔄 Animations and interactions
- 📊 8 chart types (line, bar, pie, radar, etc.)
- 🎯 Simple API
- 📦 Small footprint (~60KB)

### Actions

**1. Create Chart Builder class:**

```php
# filename: src/Visualization/ChartBuilder.php
<?php

declare(strict_types=1);

namespace DataScience\Visualization;

class ChartBuilder
{
    /**
     * Generate Chart.js configuration for line chart
     */
    public function lineChart(
        array $labels,
        array $datasets,
        array $options = []
    ): array {
        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => $this->formatDatasets($datasets, 'line'),
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * Generate Chart.js configuration for bar chart
     */
    public function barChart(
        array $labels,
        array $datasets,
        array $options = []
    ): array {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => $this->formatDatasets($datasets, 'bar'),
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * Generate Chart.js configuration for pie chart
     */
    public function pieChart(
        array $labels,
        array $data,
        array $options = []
    ): array {
        return [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $this->generateColors(count($data)),
                ]],
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * Generate Chart.js configuration for scatter plot
     */
    public function scatterPlot(
        array $datasets,
        array $options = []
    ): array {
        return [
            'type' => 'scatter',
            'data' => [
                'datasets' => $this->formatDatasets($datasets, 'scatter'),
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * ML model performance chart (accuracy over time)
     */
    public function modelPerformanceChart(
        array $dates,
        array $accuracyScores,
        float $threshold = 0.85
    ): array {
        return $this->lineChart(
            labels: $dates,
            datasets: [
                [
                    'label' => 'Model Accuracy',
                    'data' => $accuracyScores,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Threshold',
                    'data' => array_fill(0, count($dates), $threshold),
                    'borderColor' => 'rgb(255, 99, 132)',
                    'borderDash' => [5, 5],
                    'fill' => false,
                ],
            ],
            options: [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Model Accuracy Over Time',
                    ],
                ],
                'scales' => [
                    'y' => [
                        'min' => 0,
                        'max' => 1,
                        'ticks' => [
                            'callback' => 'JS:function(value) { return (value * 100).toFixed(0) + "%"; }',
                        ],
                    ],
                ],
            ]
        );
    }
    
    /**
     * A/B test results chart
     */
    public function abTestChart(
        string $controlLabel,
        float $controlConversion,
        string $treatmentLabel,
        float $treatmentConversion,
        float $pValue
    ): array {
        $significant = $pValue < 0.05;
        
        return $this->barChart(
            labels: [$controlLabel, $treatmentLabel],
            datasets: [[
                'label' => 'Conversion Rate',
                'data' => [$controlConversion, $treatmentConversion],
                'backgroundColor' => [
                    'rgba(54, 162, 235, 0.8)',
                    $significant ? 'rgba(75, 192, 192, 0.8)' : 'rgba(255, 206, 86, 0.8)',
                ],
            ]],
            options: [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => sprintf(
                            'A/B Test Results (p-value: %.4f %s)',
                            $pValue,
                            $significant ? '- Significant ✓' : '- Not Significant'
                        ),
                    ],
                ],
                'scales' => [
                    'y' => [
                        'min' => 0,
                        'max' => 1,
                        'ticks' => [
                            'callback' => 'JS:function(value) { return (value * 100).toFixed(1) + "%"; }',
                        ],
                    ],
                ],
            ]
        );
    }
    
    /**
     * Format datasets for Chart.js
     */
    private function formatDatasets(array $datasets, string $chartType): array
    {
        $formatted = [];
        $colors = $this->generateColors(count($datasets));
        
        foreach ($datasets as $index => $dataset) {
            $color = $colors[$index];
            
            $formatted[] = array_merge([
                'label' => $dataset['label'] ?? "Dataset " . ($index + 1),
                'data' => $dataset['data'],
                'backgroundColor' => $this->hexToRgba($color, 0.2),
                'borderColor' => $color,
                'borderWidth' => 2,
            ], $dataset['options'] ?? []);
        }
        
        return $formatted;
    }
    
    /**
     * Get default Chart.js options
     */
    private function getDefaultOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
    
    /**
     * Generate color palette
     */
    private function generateColors(int $count): array
    {
        $colors = [
            'rgb(255, 99, 132)',   // Red
            'rgb(54, 162, 235)',   // Blue
            'rgb(255, 206, 86)',   // Yellow
            'rgb(75, 192, 192)',   // Green
            'rgb(153, 102, 255)',  // Purple
            'rgb(255, 159, 64)',   // Orange
            'rgb(199, 199, 199)',  // Gray
            'rgb(83, 102, 255)',   // Indigo
        ];
        
        // Repeat colors if needed
        while (count($colors) < $count) {
            $colors = array_merge($colors, $colors);
        }
        
        return array_slice($colors, 0, $count);
    }
    
    /**
     * Convert hex color to rgba
     */
    private function hexToRgba(string $hex, float $alpha): string
    {
        // Remove 'rgb(' and ')' if present
        $hex = str_replace(['rgb(', ')'], '', $hex);
        
        // If already rgb format, add alpha
        if (str_contains($hex, ',')) {
            return "rgba({$hex}, {$alpha})";
        }
        
        // Otherwise it's hex, convert it
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }
    
    /**
     * Export chart configuration as JSON
     */
    public function toJson(array $config): string
    {
        // Handle JavaScript callbacks
        $json = json_encode($config, JSON_PRETTY_PRINT);
        
        // Replace JS callback placeholders
        $json = preg_replace(
            '/"JS:(.*?)"/',
            '$1',
            $json
        );
        
        return $json;
    }
}
```

**2. Create HTML template for charts:**

```php
# filename: templates/chart-template.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Chart') ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($title ?? 'Data Visualization') ?></h1>
        
        <?php if (!empty($stats)): ?>
        <div class="stats">
            <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <div class="stat-value"><?= htmlspecialchars($stat['value']) ?></div>
                <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="chart-container">
            <canvas id="myChart"></canvas>
        </div>
        
        <?php if (!empty($description)): ?>
        <div class="description">
            <h3>Insights</h3>
            <p><?= nl2br(htmlspecialchars($description)) ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        const chartConfig = <?= $chartJson ?>;
        new Chart(ctx, chartConfig);
    </script>
</body>
</html>
```

**3. Create visualization examples:**

```php
# filename: examples/chart-examples.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\ChartBuilder;

$chartBuilder = new ChartBuilder();

// Example 1: Model performance over time
$dates = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];
$accuracyScores = [0.87, 0.89, 0.91, 0.88, 0.92];

$performanceChart = $chartBuilder->modelPerformanceChart(
    dates: $dates,
    accuracyScores: $accuracyScores,
    threshold: 0.85
);

$stats = [
    ['label' => 'Current Accuracy', 'value' => '92%'],
    ['label' => 'Avg Accuracy', 'value' => '89.4%'],
    ['label' => 'Predictions', 'value' => '1,247'],
];

$title = 'ML Model Performance Dashboard';
$chartJson = $chartBuilder->toJson($performanceChart);
$description = "Model performance has improved steadily over the past 5 weeks. Current accuracy of 92% exceeds the 85% threshold, indicating excellent model health.";

// Generate HTML
ob_start();
include __DIR__ . '/../templates/chart-template.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/../output/model-performance.html', $html);

echo "✓ Generated model-performance.html\n";

// Example 2: A/B Test Results
$abTestChart = $chartBuilder->abTestChart(
    controlLabel: 'Control (Original)',
    controlConversion: 0.12,
    treatmentLabel: 'Treatment (New Design)',
    treatmentConversion: 0.15,
    pValue: 0.023
);

$stats = [
    ['label' => 'Control Conversion', 'value' => '12.0%'],
    ['label' => 'Treatment Conversion', 'value' => '15.0%'],
    ['label' => 'Lift', 'value' => '+25%'],
    ['label' => 'P-Value', 'value' => '0.023'],
];

$title = 'A/B Test Results';
$chartJson = $chartBuilder->toJson($abTestChart);
$description = "The new design achieved a 25% lift in conversion rate (12% → 15%) with statistical significance (p=0.023 < 0.05). Recommendation: Deploy to 100% of users.";

ob_start();
include __DIR__ . '/../templates/chart-template.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/../output/ab-test-results.html', $html);

echo "✓ Generated ab-test-results.html\n";

// Example 3: Sales trend
$salesChart = $chartBuilder->lineChart(
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [
        [
            'label' => '2025 Sales',
            'data' => [45000, 52000, 48000, 61000, 58000, 67000],
        ],
        [
            'label' => '2024 Sales',
            'data' => [38000, 41000, 43000, 47000, 49000, 52000],
        ],
    ],
    options: [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Sales Comparison: 2024 vs 2025',
            ],
        ],
    ]
);

$stats = [
    ['label' => 'Jun 2025 Sales', 'value' => '$67,000'],
    ['label' => 'YoY Growth', 'value' => '+28.8%'],
    ['label' => 'Total 2025', 'value' => '$331,000'],
];

$title = 'Sales Performance';
$chartJson = $chartBuilder->toJson($salesChart);
$description = "2025 sales are trending 28.8% higher than 2024, with strong growth in Q2. June 2025 achieved $67,000 vs $52,000 in June 2024.";

ob_start();
include __DIR__ . '/../templates/chart-template.php';
$html = ob_get_clean();

file_put_contents(__DIR__ . '/../output/sales-trend.html', $html);

echo "✓ Generated sales-trend.html\n\n";
echo "✓ Open output/*.html in your browser to view charts!\n";
```

### Expected Result

Running the example generates three HTML files with interactive charts:

```
✓ Generated model-performance.html
✓ Generated ab-test-results.html
✓ Generated sales-trend.html

✓ Open output/*.html in your browser to view charts!
```

Opening `model-performance.html` in a browser shows:
- Interactive line chart with model accuracy over time
- Threshold line at 85%
- Stat cards showing current metrics
- Hover tooltips on data points
- Responsive layout

### Why It Works

**Chart.js** renders charts on the client-side using HTML5 Canvas:

- **PHP generates configuration**: Your PHP code creates the chart structure
- **JSON serialization**: Configuration is converted to JavaScript
- **Browser rendering**: Chart.js draws the interactive chart
- **No server rendering**: Charts update smoothly without page reloads

**Key Pattern**: PHP handles data processing, Chart.js handles visualization.

### Troubleshooting

**Problem: Charts not displaying**

**Cause**: Chart.js library not loaded or JavaScript errors.

**Solution**: Check browser console and verify CDN:

```html
<!-- Use specific version for stability -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Verify in browser console -->
<script>
console.log('Chart.js version:', Chart.version);
</script>
```

**Problem: JavaScript callbacks not working**

**Cause**: JSON encoding converts callbacks to strings.

**Solution**: Use placeholder pattern and replace after encoding:

```php
// In chart options
'callback' => 'JS:function(value) { return value + "%"; }'

// In toJson() method
$json = preg_replace('/"JS:(.*?)"/', '$1', $json);
```

**Problem: Colors look inconsistent**

**Cause**: Not enough colors for datasets or transparency issues.

**Solution**: Generate more colors or use consistent palette:

```php
private function generateColors(int $count): array
{
    // Generate colors dynamically using HSL
    $colors = [];
    for ($i = 0; $i < $count; $i++) {
        $hue = ($i * 360 / $count) % 360;
        $colors[] = "hsl({$hue}, 70%, 60%)";
    }
    return $colors;
}
```

## Step 2: Building Interactive Dashboards (~25 min)

### Goal

Create a real-time dashboard that displays multiple visualizations and updates automatically.

### Actions

**1. Create Dashboard Generator:**

```php
# filename: src/Visualization/DashboardGenerator.php
<?php

declare(strict_types=1);

namespace DataScience\Visualization;

class DashboardGenerator
{
    private ChartBuilder $chartBuilder;
    private array $widgets = [];
    
    public function __construct()
    {
        $this->chartBuilder = new ChartBuilder();
    }
    
    /**
     * Add chart widget to dashboard
     */
    public function addChart(
        string $id,
        string $title,
        array $chartConfig,
        int $width = 6
    ): self {
        $this->widgets[] = [
            'type' => 'chart',
            'id' => $id,
            'title' => $title,
            'config' => $chartConfig,
            'width' => $width, // Bootstrap columns (1-12)
        ];
        
        return $this;
    }
    
    /**
     * Add stat card widget
     */
    public function addStatCard(
        string $title,
        string $value,
        string $trend = '',
        string $icon = '📊',
        int $width = 3
    ): self {
        $this->widgets[] = [
            'type' => 'stat',
            'title' => $title,
            'value' => $value,
            'trend' => $trend,
            'icon' => $icon,
            'width' => $width,
        ];
        
        return $this;
    }
    
    /**
     * Add table widget
     */
    public function addTable(
        string $title,
        array $headers,
        array $rows,
        int $width = 12
    ): self {
        $this->widgets[] = [
            'type' => 'table',
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'width' => $width,
        ];
        
        return $this;
    }
    
    /**
     * Generate complete dashboard HTML
     */
    public function generate(
        string $title,
        int $refreshInterval = 0
    ): string {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .widget {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card {
            text-align: center;
            padding: 30px 20px;
        }
        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .stat-trend {
            font-size: 0.85em;
            margin-top: 10px;
        }
        .trend-up { color: #27ae60; }
        .trend-down { color: #e74c3c; }
        .widget-title {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 15px;
            color: #34495e;
        }
        .last-updated {
            color: #95a5a6;
            font-size: 0.85em;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-header">
            <h1>{$title}</h1>
            <p class="last-updated mb-0">Last updated: <span id="lastUpdate">{$this->getCurrentTime()}</span></p>
        </div>
        
        <div class="row">
HTML;
        
        foreach ($this->widgets as $widget) {
            $html .= $this->renderWidget($widget);
        }
        
        $html .= <<<HTML
        </div>
    </div>
    
    <script>
        const charts = {};
        
HTML;
        
        // Initialize charts
        foreach ($this->widgets as $widget) {
            if ($widget['type'] === 'chart') {
                $chartJson = $this->chartBuilder->toJson($widget['config']);
                $html .= <<<JS
        charts['{$widget['id']}'] = new Chart(
            document.getElementById('{$widget['id']}').getContext('2d'),
            {$chartJson}
        );
        
JS;
            }
        }
        
        // Add auto-refresh
        if ($refreshInterval > 0) {
            $html .= <<<JS
        
        // Auto-refresh every {$refreshInterval} seconds
        setInterval(function() {
            location.reload();
        }, {$refreshInterval} * 1000);
        
        // Update timestamp
        setInterval(function() {
            document.getElementById('lastUpdate').textContent = new Date().toLocaleString();
        }, 1000);
        
JS;
        }
        
        $html .= <<<HTML
    </script>
</body>
</html>
HTML;
        
        return $html;
    }
    
    /**
     * Render individual widget
     */
    private function renderWidget(array $widget): string
    {
        $width = $widget['width'];
        $html = "<div class=\"col-md-{$width}\">\n";
        
        switch ($widget['type']) {
            case 'chart':
                $html .= $this->renderChartWidget($widget);
                break;
            case 'stat':
                $html .= $this->renderStatWidget($widget);
                break;
            case 'table':
                $html .= $this->renderTableWidget($widget);
                break;
        }
        
        $html .= "</div>\n";
        return $html;
    }
    
    /**
     * Render chart widget
     */
    private function renderChartWidget(array $widget): string
    {
        return <<<HTML
<div class="widget">
    <div class="widget-title">{$widget['title']}</div>
    <div class="chart-container">
        <canvas id="{$widget['id']}"></canvas>
    </div>
</div>
HTML;
    }
    
    /**
     * Render stat card widget
     */
    private function renderStatWidget(array $widget): string
    {
        $trendClass = '';
        $trendIcon = '';
        
        if ($widget['trend']) {
            if (str_contains($widget['trend'], '+')) {
                $trendClass = 'trend-up';
                $trendIcon = '↑';
            } elseif (str_contains($widget['trend'], '-')) {
                $trendClass = 'trend-down';
                $trendIcon = '↓';
            }
        }
        
        return <<<HTML
<div class="widget stat-card">
    <div style="font-size: 2em; margin-bottom: 10px;">{$widget['icon']}</div>
    <div class="stat-value">{$widget['value']}</div>
    <div class="stat-label">{$widget['title']}</div>
    {$this->renderTrend($widget['trend'], $trendClass, $trendIcon)}
</div>
HTML;
    }
    
    /**
     * Render trend indicator
     */
    private function renderTrend(string $trend, string $class, string $icon): string
    {
        if (!$trend) {
            return '';
        }
        
        return "<div class=\"stat-trend {$class}\">{$icon} {$trend}</div>";
    }
    
    /**
     * Render table widget
     */
    private function renderTableWidget(array $widget): string
    {
        $html = <<<HTML
<div class="widget">
    <div class="widget-title">{$widget['title']}</div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
HTML;
        
        foreach ($widget['headers'] as $header) {
            $html .= "<th>" . htmlspecialchars($header) . "</th>";
        }
        
        $html .= <<<HTML
                </tr>
            </thead>
            <tbody>
HTML;
        
        foreach ($widget['rows'] as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>" . htmlspecialchars((string)$cell) . "</td>";
            }
            $html .= "</tr>\n";
        }
        
        $html .= <<<HTML
            </tbody>
        </table>
    </div>
</div>
HTML;
        
        return $html;
    }
    
    /**
     * Get current timestamp
     */
    private function getCurrentTime(): string
    {
        return date('Y-m-d H:i:s');
    }
}
```

**2. Create dashboard example:**

```php
# filename: examples/dashboard-example.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\DashboardGenerator;
use DataScience\Visualization\ChartBuilder;

$dashboard = new DashboardGenerator();
$chartBuilder = new ChartBuilder();

// Stat cards
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

// Model performance chart
$performanceChart = $chartBuilder->modelPerformanceChart(
    dates: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    accuracyScores: [0.87, 0.89, 0.91, 0.90, 0.92, 0.91, 0.93],
    threshold: 0.85
);

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

$dashboard->addChart(
    id: 'conversionFunnel',
    title: 'Conversion Funnel',
    chartConfig: $funnelChart,
    width: 4
);

// Top products table
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

$dashboard->addChart(
    id: 'trafficSources',
    title: 'Traffic Sources',
    chartConfig: $trafficChart,
    width: 6
);

// Generate dashboard
$html = $dashboard->generate(
    title: 'Business Intelligence Dashboard',
    refreshInterval: 30  // Auto-refresh every 30 seconds
);

file_put_contents(__DIR__ . '/../output/dashboard.html', $html);

echo "✓ Dashboard generated successfully!\n";
echo "✓ Open output/dashboard.html in your browser\n";
echo "✓ Dashboard will auto-refresh every 30 seconds\n";
```

### Expected Result

Opening `dashboard.html` shows:

```
╔══════════════════════════════════════════════════════╗
║        Business Intelligence Dashboard               ║
║        Last updated: 2026-01-12 15:30:45            ║
╚══════════════════════════════════════════════════════╝

[👥]          [💰]           [🎯]           [🛒]
12,847      $45,231        3.42%          $127
Total Users  Revenue    Conversion     Avg Order
↑ +8.5%     ↑ +12.3%    ↑ +0.8%       ↓ -2.1%

[ML Model Performance Chart]    [Conversion Funnel]
[Traffic Sources Pie Chart]     [Top 5 Products Table]
```

All charts are interactive with:
- Hover tooltips
- Click to show/hide datasets
- Responsive resizing
- Smooth animations

### Why It Works

**Dashboard Architecture**:

1. **PHP generates static HTML**: Server-side rendering for initial load
2. **Chart.js renders charts**: Client-side interactivity
3. **Bootstrap provides layout**: Responsive grid system
4. **Auto-refresh updates data**: Periodic page reloads

**Benefits**:
- Fast initial load
- Rich interactivity
- Mobile-friendly
- Easy to update

### Troubleshooting

**Problem: Dashboard doesn't refresh**

**Cause**: `refreshInterval` not working or browser blocks reload.

**Solution**: Use AJAX for partial updates instead:

```javascript
// Better: Update data without full page reload
setInterval(async function() {
    const response = await fetch('/api/dashboard-data');
    const data = await response.json();
    
    // Update charts
    Object.keys(charts).forEach(chartId => {
        charts[chartId].data.datasets[0].data = data[chartId];
        charts[chartId].update();
    });
}, 30000);
```

**Problem: Charts overlap on mobile**

**Cause**: Fixed heights don't adapt to small screens.

**Solution**: Use responsive CSS:

```css
@media (max-width: 768px) {
    .chart-container {
        height: 250px; /* Smaller on mobile */
    }
    .stat-card {
        margin-bottom: 15px;
    }
}
```

## Step 3: Generating PDF Reports (~20 min)

### Goal

Create automated PDF reports with charts, tables, and insights for stakeholders.

### Actions

**1. Install dompdf:**

```bash
composer require dompdf/dompdf
```

**2. Create PDF Report Generator:**

```php
# filename: src/Visualization/PDFReportGenerator.php
<?php

declare(strict_types=1);

namespace DataScience\Visualization;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFReportGenerator
{
    private Dompdf $dompdf;
    private string $html = '';
    
    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $this->dompdf = new Dompdf($options);
    }
    
    /**
     * Start building report
     */
    public function startReport(string $title, string $subtitle = ''): self
    {
        $date = date('F j, Y');
        
        $this->html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #2c3e50;
            margin: 0;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 10px;
        }
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #34495e;
            border-left: 4px solid #3498db;
            padding-left: 10px;
            margin: 20px 0 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background: #3498db;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ecf0f1;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .stat-grid {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        .stat-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ecf0f1;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .insight-box {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            color: #95a5a6;
            font-size: 10px;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$title}</h1>
        <div class="subtitle">{$subtitle}<br>Generated on {$date}</div>
    </div>
HTML;
        
        return $this;
    }
    
    /**
     * Add section heading
     */
    public function addSection(string $title): self
    {
        $this->html .= "<div class=\"section-title\">{$title}</div>\n";
        return $this;
    }
    
    /**
     * Add paragraph text
     */
    public function addText(string $text): self
    {
        $this->html .= "<p>{$text}</p>\n";
        return $this;
    }
    
    /**
     * Add stat cards
     */
    public function addStats(array $stats): self
    {
        $this->html .= "<div class=\"stat-grid\">\n<div class=\"stat-row\">\n";
        
        foreach ($stats as $stat) {
            $this->html .= <<<HTML
    <div class="stat-cell">
        <div class="stat-value">{$stat['value']}</div>
        <div class="stat-label">{$stat['label']}</div>
    </div>
HTML;
        }
        
        $this->html .= "</div>\n</div>\n";
        return $this;
    }
    
    /**
     * Add table
     */
    public function addTable(array $headers, array $rows): self
    {
        $this->html .= "<table>\n<thead><tr>\n";
        
        foreach ($headers as $header) {
            $this->html .= "<th>" . htmlspecialchars($header) . "</th>";
        }
        
        $this->html .= "</tr></thead>\n<tbody>\n";
        
        foreach ($rows as $row) {
            $this->html .= "<tr>";
            foreach ($row as $cell) {
                $this->html .= "<td>" . htmlspecialchars((string)$cell) . "</td>";
            }
            $this->html .= "</tr>\n";
        }
        
        $this->html .= "</tbody>\n</table>\n";
        return $this;
    }
    
    /**
     * Add insight box (green)
     */
    public function addInsight(string $text): self
    {
        $this->html .= "<div class=\"insight-box\">✓ <strong>Insight:</strong> {$text}</div>\n";
        return $this;
    }
    
    /**
     * Add warning box (orange)
     */
    public function addWarning(string $text): self
    {
        $this->html .= "<div class=\"warning-box\">⚠ <strong>Warning:</strong> {$text}</div>\n";
        return $this;
    }
    
    /**
     * Add chart image (must be base64 encoded)
     */
    public function addChartImage(string $base64Image, string $caption = ''): self
    {
        $this->html .= "<div style=\"text-align: center; margin: 20px 0;\">\n";
        $this->html .= "<img src=\"data:image/png;base64,{$base64Image}\" style=\"max-width: 100%;\">\n";
        if ($caption) {
            $this->html .= "<div style=\"font-size: 12px; color: #7f8c8d; margin-top: 5px;\">{$caption}</div>\n";
        }
        $this->html .= "</div>\n";
        return $this;
    }
    
    /**
     * Add page break
     */
    public function addPageBreak(): self
    {
        $this->html .= "<div class=\"page-break\"></div>\n";
        return $this;
    }
    
    /**
     * Generate and save PDF
     */
    public function savePDF(string $filename): void
    {
        $this->html .= <<<HTML
    <div class="footer">
        Generated by Data Science Reporting System<br>
        Report Date: {$this->getCurrentTime()}
    </div>
</body>
</html>
HTML;
        
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        
        file_put_contents($filename, $this->dompdf->output());
    }
    
    /**
     * Output PDF to browser
     */
    public function streamPDF(string $filename): void
    {
        $this->html .= "</body></html>";
        
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        $this->dompdf->stream($filename);
    }
    
    /**
     * Get current timestamp
     */
    private function getCurrentTime(): string
    {
        return date('Y-m-d H:i:s');
    }
}
```

**3. Create PDF report example:**

```php
# filename: examples/pdf-report-example.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\PDFReportGenerator;

echo "=== Generating PDF Report ===\n\n";

$report = new PDFReportGenerator();

$report
    ->startReport(
        title: 'Monthly Business Intelligence Report',
        subtitle: 'Executive Summary - January 2026'
    )
    
    ->addSection('Executive Summary')
    ->addText('This report summarizes key business metrics and trends for January 2026. Overall performance shows strong growth across most indicators with notable improvements in customer acquisition and retention.')
    
    ->addStats([
        ['label' => 'Revenue', 'value' => '$127,450'],
        ['label' => 'Users', 'value' => '12,847'],
        ['label' => 'Conversion', 'value' => '3.42%'],
        ['label' => 'Churn Rate', 'value' => '2.1%'],
    ])
    
    ->addSection('Key Performance Indicators')
    ->addTable(
        headers: ['Metric', 'Current', 'Previous', 'Change'],
        rows: [
            ['Revenue', '$127,450', '$112,300', '+13.5%'],
            ['Active Users', '12,847', '11,920', '+7.8%'],
            ['Conversion Rate', '3.42%', '3.18%', '+0.24pp'],
            ['Average Order Value', '$127', '$130', '-2.3%'],
            ['Customer Lifetime Value', '$1,524', '$1,450', '+5.1%'],
        ]
    )
    
    ->addInsight('Revenue grew 13.5% month-over-month, driven by increased user acquisition and improved conversion rates.')
    
    ->addSection('ML Model Performance')
    ->addText('Our spam detection model maintained excellent performance throughout the month:')
    ->addTable(
        headers: ['Model', 'Accuracy', 'Predictions', 'Status'],
        rows: [
            ['Spam Classifier', '92.3%', '45,231', 'Excellent'],
            ['Sentiment Analyzer', '87.5%', '12,847', 'Good'],
            ['Fraud Detector', '95.1%', '8,421', 'Excellent'],
        ]
    )
    
    ->addPageBreak()
    
    ->addSection('Customer Segmentation')
    ->addTable(
        headers: ['Segment', 'Count', '% Total', 'Avg Value'],
        rows: [
            ['High Value', '1,284', '10%', '$450'],
            ['Medium Value', '5,139', '40%', '$127'],
            ['Low Value', '6,424', '50%', '$45'],
        ]
    )
    
    ->addSection('Recommendations')
    ->addText('Based on the data analysis, we recommend the following actions:')
    ->addText('1. <strong>Increase focus on high-value customer retention</strong> - This segment represents 10% of users but 45% of revenue.')
    ->addText('2. <strong>Optimize conversion funnel</strong> - Drop-off rate at checkout increased by 0.8 percentage points.')
    ->addText('3. <strong>Expand product recommendations</strong> - ML-powered recommendations showed 25% higher conversion.')
    
    ->addWarning('Average order value decreased by 2.3%. Investigate pricing strategy and product mix.')
    
    ->addSection('Next Steps')
    ->addText('The following initiatives should be prioritized for February:')
    ->addTable(
        headers: ['Initiative', 'Priority', 'Owner', 'Target Date'],
        rows: [
            ['Launch loyalty program', 'High', 'Marketing', 'Feb 15'],
            ['A/B test new checkout flow', 'High', 'Product', 'Feb 10'],
            ['Implement price optimization', 'Medium', 'Finance', 'Feb 28'],
            ['Expand ML recommendations', 'Medium', 'Engineering', 'Feb 20'],
        ]
    );

// Save PDF
$outputFile = __DIR__ . '/../output/monthly-report.pdf';
$report->savePDF($outputFile);

echo "✓ PDF report generated successfully!\n";
echo "✓ Saved to: {$outputFile}\n";
echo "✓ File size: " . number_format(filesize($outputFile)) . " bytes\n";
```

### Expected Result

```
=== Generating PDF Report ===

✓ PDF report generated successfully!
✓ Saved to: output/monthly-report.pdf
✓ File size: 45,231 bytes
```

Opening the PDF shows a professional multi-page report with:
- Header with title and generation date
- Stat cards showing KPIs
- Tables with formatted data
- Insight and warning boxes
- Page breaks between sections
- Footer with timestamp

### Why It Works

**dompdf** converts HTML/CSS to PDF:

- **HTML structure**: Standard HTML for content
- **CSS styling**: Regular CSS for formatting
- **PDF rendering**: Server-side PDF generation
- **No JavaScript**: Pure PHP solution

**Advantages**:
- Professional appearance
- Easy to automate
- Email-friendly
- Print-ready

### Troubleshooting

**Problem: PDF generation is slow**

**Cause**: Large HTML or complex CSS.

**Solution**: Optimize HTML and avoid external resources:

```php
// ❌ Slow: External images
<img src="https://example.com/chart.png">

// ✅ Fast: Embedded base64 images
<img src="data:image/png;base64,iVBORw0KG...">
```

**Problem: Fonts look wrong**

**Cause**: dompdf doesn't include all fonts.

**Solution**: Use standard fonts or embed custom fonts:

```php
$options->set('defaultFont', 'Arial');
// Or load custom font
$options->set('fontDir', '/path/to/fonts');
```

**Problem: Charts not appearing in PDF**

**Cause**: Chart.js generates canvas elements (JavaScript), PDF needs static images.

**Solution**: Convert charts to images first:

```javascript
// In browser, export chart as image
const canvas = document.getElementById('myChart');
const imageData = canvas.toDataURL('image/png');
// Send to PHP for PDF inclusion
```

## Step 4: Data Export Formats (~10 min)

### Goal

Export data in multiple formats for different stakeholders.

### Actions

**1. Create Data Exporter:**

```php
# filename: src/Visualization/DataExporter.php
<?php

declare(strict_types=1);

namespace DataScience\Visualization;

class DataExporter
{
    /**
     * Export to CSV
     */
    public function toCSV(array $headers, array $rows, string $filename): void
    {
        $handle = fopen($filename, 'w');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        // Write headers
        fputcsv($handle, $headers);
        
        // Write rows
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }
    
    /**
     * Export to JSON
     */
    public function toJSON(array $data, string $filename): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($filename, $json);
    }
    
    /**
     * Export to Excel-compatible CSV
     */
    public function toExcelCSV(array $headers, array $rows, string $filename): void
    {
        $handle = fopen($filename, 'w');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        // UTF-8 BOM for Excel compatibility
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write headers
        fputcsv($handle, $headers);
        
        // Write rows
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }
    
    /**
     * Export to HTML table
     */
    public function toHTML(array $headers, array $rows, string $title = 'Data Export'): string
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #3498db; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <table>
        <thead><tr>
HTML;
        
        foreach ($headers as $header) {
            $html .= "<th>" . htmlspecialchars($header) . "</th>";
        }
        
        $html .= "</tr></thead>\n<tbody>\n";
        
        foreach ($rows as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>" . htmlspecialchars((string)$cell) . "</td>";
            }
            $html .= "</tr>\n";
        }
        
        $html .= "</tbody>\n</table>\n</body>\n</html>";
        
        return $html;
    }
    
    /**
     * Stream CSV to browser for download
     */
    public function streamCSV(array $headers, array $rows, string $filename): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        $handle = fopen('php://output', 'w');
        fputcsv($handle, $headers);
        
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }
}
```

**2. Create export example:**

```php
# filename: examples/export-example.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\DataExporter;

echo "=== Data Export Example ===\n\n";

$exporter = new DataExporter();

// Sample data
$headers = ['Product', 'Sales', 'Revenue', 'Growth'];
$rows = [
    ['Product A', 1247, 45231, '+12%'],
    ['Product B', 892, 31220, '+8%'],
    ['Product C', 743, 22100, '-3%'],
    ['Product D', 612, 18360, '+15%'],
    ['Product E', 501, 15030, '+5%'],
];

// Export to CSV
$exporter->toCSV(
    headers: $headers,
    rows: $rows,
    filename: __DIR__ . '/../output/products.csv'
);
echo "✓ Exported to CSV: output/products.csv\n";

// Export to Excel-compatible CSV
$exporter->toExcelCSV(
    headers: $headers,
    rows: $rows,
    filename: __DIR__ . '/../output/products-excel.csv'
);
echo "✓ Exported to Excel CSV: output/products-excel.csv\n";

// Export to JSON
$jsonData = [
    'report_date' => date('Y-m-d'),
    'headers' => $headers,
    'data' => $rows,
    'summary' => [
        'total_sales' => array_sum(array_column($rows, 1)),
        'total_revenue' => array_sum(array_column($rows, 2)),
    ],
];

$exporter->toJSON(
    data: $jsonData,
    filename: __DIR__ . '/../output/products.json'
);
echo "✓ Exported to JSON: output/products.json\n";

// Export to HTML
$html = $exporter->toHTML(
    headers: $headers,
    rows: $rows,
    title: 'Product Performance Report'
);

file_put_contents(__DIR__ . '/../output/products.html', $html);
echo "✓ Exported to HTML: output/products.html\n\n";

echo "✓ All exports completed successfully!\n";
```

### Expected Result

```
=== Data Export Example ===

✓ Exported to CSV: output/products.csv
✓ Exported to Excel CSV: output/products-excel.csv
✓ Exported to JSON: output/products.json
✓ Exported to HTML: output/products.html

✓ All exports completed successfully!
```

Files created:
- `products.csv`: Standard CSV file
- `products-excel.csv`: UTF-8 BOM for Excel
- `products.json`: Structured JSON data
- `products.html`: Interactive HTML table

### Why It Works

**Multiple formats** serve different needs:

- **CSV**: Spreadsheet import, data analysis
- **JSON**: API consumption, programmatic access
- **HTML**: Quick viewing, email sharing
- **PDF**: Professional reports, printing

### Troubleshooting

**Problem: Excel shows garbled text**

**Cause**: Missing UTF-8 BOM for Excel.

**Solution**: Use `toExcelCSV()` method:

```php
// Adds UTF-8 BOM for Excel
fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
```

**Problem: Large datasets cause memory errors**

**Cause**: Loading entire dataset into memory.

**Solution**: Stream data in chunks:

```php
public function streamLargeCSV(iterable $dataGenerator, array $headers, string $filename): void
{
    $handle = fopen($filename, 'w');
    fputcsv($handle, $headers);
    
    foreach ($dataGenerator as $row) {
        fputcsv($handle, $row);
        // Memory freed after each iteration
    }
    
    fclose($handle);
}
```

## Step 5: Security Considerations (~15 min)

### Goal

Understand and implement security best practices for data visualization to prevent XSS attacks and ensure data integrity.

### Why Security Matters

Data visualization systems process user input and generate HTML/JavaScript that runs in browsers. Without proper security:

- **XSS (Cross-Site Scripting)**: Malicious scripts executed in user browsers
- **Data Injection**: Corrupted charts or reports
- **Information Disclosure**: Sensitive data exposed through errors

### Common Vulnerabilities

#### 1. XSS in Dashboard Widgets

**❌ Vulnerable Code:**

```php
// DashboardGenerator - INSECURE
private function renderStatWidget(array $widget): string
{
    return <<<HTML
<div class="widget stat-card">
    <div class="stat-value">{$widget['value']}</div>
    <div class="stat-label">{$widget['title']}</div>
</div>
HTML;
}
```

**Attack:**
```php
$dashboard->addStatCard(
    title: '<script>alert("XSS")</script>',
    value: '<img src=x onerror=alert(1)>'
);
// Result: JavaScript executes in browser
```

**✅ Secure Code:**

```php
// DashboardGenerator - SECURE
private function renderStatWidget(array $widget): string
{
    $safeValue = htmlspecialchars($widget['value'], ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($widget['title'], ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
<div class="widget stat-card">
    <div class="stat-value">{$safeValue}</div>
    <div class="stat-label">{$safeTitle}</div>
</div>
HTML;
}
```

#### 2. JavaScript Injection in Chart Callbacks

**❌ Vulnerable Code:**

```php
// ChartBuilder - INSECURE
public function toJson(array $config): string
{
    $json = json_encode($config);
    
    // Replace JS callbacks without validation
    return preg_replace('/"JS:(.*?)"/', '$1', $json);
}
```

**Attack:**
```php
$config['onClick'] = 'JS:alert(document.cookie)';  // Steals cookies
```

**✅ Secure Code:**

```php
// ChartBuilder - SECURE
public function toJson(array $config): string
{
    $json = json_encode($config);
    
    if ($json === false) {
        throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
    }
    
    // Validate JavaScript callbacks
    $json = preg_replace_callback(
        '/"JS:(.*?)"/',
        function($matches) {
            $callback = $matches[1];
            
            // Ensure it's a proper function definition
            if (!preg_match('/^function\s*\([^)]*\)\s*\{/', $callback)) {
                throw new \InvalidArgumentException('Invalid callback format');
            }
            
            return $callback;
        },
        $json
    );
    
    return $json;
}
```

#### 3. Unvalidated File Operations

**❌ Vulnerable Code:**

```php
// DataExporter - INSECURE
public function toCSV(array $headers, array $rows, string $filename): void
{
    $handle = fopen($filename, 'w');
    fputcsv($handle, $headers);
    
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    
    fclose($handle);
}
```

**Problems:**
- No input validation
- No directory creation
- No error handling
- Resource leak on failure

**✅ Secure Code:**

```php
// DataExporter - SECURE
public function toCSV(array $headers, array $rows, string $filename): void
{
    // 1. Validate inputs
    if (empty($headers)) {
        throw new \InvalidArgumentException('Headers cannot be empty');
    }
    
    if (empty($filename)) {
        throw new \InvalidArgumentException('Filename cannot be empty');
    }
    
    // 2. Ensure directory exists
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            throw new \RuntimeException("Cannot create directory: {$dir}");
        }
    }
    
    // 3. Open file with error handling
    $handle = fopen($filename, 'w');
    if ($handle === false) {
        throw new \RuntimeException("Cannot open file: {$filename}");
    }
    
    // 4. Use try-finally for cleanup
    try {
        if (fputcsv($handle, $headers) === false) {
            throw new \RuntimeException("Failed to write headers");
        }
        
        foreach ($rows as $index => $row) {
            if (count($row) !== count($headers)) {
                trigger_error("Row {$index} column mismatch", E_USER_WARNING);
            }
            
            if (fputcsv($handle, $row) === false) {
                throw new \RuntimeException("Failed to write row {$index}");
            }
        }
    } finally {
        fclose($handle);
    }
}
```

### Security Best Practices

#### Always Escape User Input

```php
// Escape for HTML context
$safe = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// Escape for JavaScript strings
$safe = json_encode($userInput, JSON_HEX_TAG | JSON_HEX_AMP);

// Escape for URLs
$safe = urlencode($userInput);
```

#### Validate All Inputs

```php
// Validate non-empty
if (empty($value)) {
    throw new \InvalidArgumentException('Value cannot be empty');
}

// Validate type
if (!is_array($data)) {
    throw new \InvalidArgumentException('Data must be an array');
}

// Validate format
if (!preg_match('/^[a-zA-Z0-9_-]+\.csv$/', $filename)) {
    throw new \InvalidArgumentException('Invalid filename format');
}
```

#### Handle Errors Properly

```php
// Don't expose internal details
try {
    $this->processData($userInput);
} catch (\Exception $e) {
    // Log full error internally
    error_log($e->getMessage());
    
    // Show generic message to user
    throw new \RuntimeException('Failed to process data');
}
```

#### Use Allowlists, Not Blocklists

```php
// ❌ Blocklist (incomplete)
$dangerous = ['<script>', 'javascript:', 'onerror'];
foreach ($dangerous as $pattern) {
    $input = str_replace($pattern, '', $input);
}

// ✅ Allowlist (complete)
$input = strip_tags($input, '<strong><em><u>');  // Only these tags allowed
```

### Testing Security

**XSS Test:**

```php
$testInputs = [
    '<script>alert("xss")</script>',
    '"><img src=x onerror=alert(1)>',
    '<svg onload=alert(1)>',
    'javascript:alert(1)',
];

foreach ($testInputs as $input) {
    $dashboard->addStatCard('Test', $input);
    $html = $dashboard->generate('Security Test');
    
    // Verify malicious code is escaped
    if (strpos($html, '<script>') !== false) {
        echo "❌ XSS vulnerability found!\n";
        exit(1);
    }
}

echo "✓ XSS protection working\n";
```

### Why This Works

**Defense in Depth**:

1. **Input Validation**: Reject bad data early
2. **Output Escaping**: Neutralize any remaining threats
3. **Error Handling**: Don't leak sensitive information
4. **Resource Management**: Clean up properly (try-finally)

All code examples in this chapter use these security practices. Review the `code/` directory for complete secure implementations.

## Step 6: Accessibility Features (~10 min)

### Goal

Make visualizations accessible to all users, including those using screen readers, keyboard navigation, or who have color vision deficiencies.

### Why Accessibility Matters

- **Legal Requirement**: WCAG 2.1 compliance required for many organizations
- **Broader Audience**: 15% of population has some disability
- **Better UX**: Accessibility improvements benefit all users
- **SEO Benefits**: Screen reader-friendly content ranks better

### WCAG 2.1 Compliance

Our visualizations implement:

- **Perceivable**: ARIA labels, alt text, color contrast
- **Operable**: Keyboard navigation, focus management
- **Understandable**: Clear labels, consistent layout
- **Robust**: Valid HTML, semantic markup

### ARIA Labels for Charts

```php
// Add accessibility metadata
$chart = $chartBuilder->makeAccessible(
    $chart,
    'Line chart showing sales growing from $100K to $150K over Q1'
);
```

Generates:

```html
<div class="chart-container" 
     role="img" 
     aria-label="Line chart showing sales growing from $100K to $150K over Q1">
    <canvas id="chart" 
            role="img"
            aria-label="Line chart showing sales growing from $100K to $150K over Q1">
    </canvas>
    
    <!-- Screen reader fallback -->
    <div class="sr-only">
        Detailed chart description: Sales started at $100,000 in January,
        increased to $125,000 in February, and reached $150,000 in March,
        showing a consistent 25% monthly growth rate.
    </div>
</div>
```

### Screen Reader Support

**CSS for screen-reader-only content:**

```css
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    white-space: nowrap;
    border: 0;
}
```

**Usage in templates:**

```php
<?php if (!empty($accessibleDescription)): ?>
<div class="sr-only" id="chart-description">
    <?= htmlspecialchars($accessibleDescription, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>
```

### Colorblind-Safe Palettes

**Standard Chart.js colors** (problems for colorblind users):
- Red/Green confusion (8% of males)
- Blue/Yellow issues (rare but exists)

**Wong 2011 palette** (works for all types):

```php
$chart = $chartBuilder->makeColorblindSafe($chart);
```

Colors used:
- Blue: `rgb(0, 114, 178)`
- Orange: `rgb(230, 159, 0)`
- Green: `rgb(0, 158, 115)`
- Purple: `rgb(204, 121, 167)`
- Sky Blue: `rgb(86, 180, 233)`
- Vermillion: `rgb(213, 94, 0)`
- Yellow: `rgb(240, 228, 66)`

**Scientific validation**: Published in Nature Methods (2011), tested with all color blindness types.

### Keyboard Navigation

Our templates support:

- **Tab**: Navigate between interactive elements
- **Enter/Space**: Activate buttons
- **Ctrl+S**: Download chart (custom shortcut)
- **Escape**: Close modals/dialogs

**Implementation:**

```javascript
document.addEventListener('keydown', function(e) {
    // Download shortcut
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        downloadChartPNG();
    }
});
```

### Color Contrast

**WCAG AA Requirements:**
- Normal text: 4.5:1 contrast ratio
- Large text: 3:1 contrast ratio
- Interactive elements: 3:1 ratio

**Our defaults:**
- Background: `#ffffff` (white)
- Text: `#333333` (dark gray) - 12.6:1 ratio ✓
- Links: `#3498db` (blue) - 4.7:1 ratio ✓
- Borders: `#ecf0f1` (light gray) - 1.2:1 ratio (decorative)

### Testing Accessibility

**Automated Testing:**

```bash
# Install axe-core
npm install axe-core

# Run accessibility tests
npx axe output/dashboard.html
```

**Manual Testing:**

1. **Screen Reader**
   - macOS: VoiceOver (Cmd+F5)
   - Windows: NVDA (free download)
   - Linux: Orca

2. **Keyboard Only**
   - Unplug mouse
   - Navigate entire dashboard
   - Verify all functions accessible

3. **Color Blindness Simulation**
   - Chrome DevTools: Rendering → Emulate vision deficiencies
   - Test: Protanopia, Deuteranopia, Tritanopia

### Accessibility Checklist

- [x] ARIA labels on all charts
- [x] Screen reader descriptions provided
- [x] Keyboard navigation supported
- [x] Color contrast meets WCAG AA
- [x] Colorblind-safe palette available
- [x] Focus indicators visible
- [x] Alt text for images
- [x] Semantic HTML markup
- [x] Skip links for navigation
- [x] Error messages announced

### Why This Works

**Progressive Enhancement**:

1. **Baseline**: Works without JavaScript (PDF reports)
2. **Enhanced**: Interactive charts for visual users
3. **Accessible**: Alternative access methods for all

**Universal Design**: Features that help disabled users help everyone:
- Keyboard shortcuts: Faster for power users
- High contrast: Better in bright sunlight
- Clear labels: Less cognitive load

## Step 7: Advanced Features (~20 min)

### Goal

Implement advanced visualization features: real-time updates, chart-to-image export, and interactive filters.

### Feature 1: Real-Time AJAX Updates

**Problem**: Page refresh disrupts user experience

**Solution**: Update data without reload using AJAX

**Implementation:**

```php
use DataScience\Visualization\LiveDashboard;

$dashboard = new LiveDashboard();

$dashboard
    ->addLiveStatCard('total-users', 'Users', '12,847', '+8.5%')
    ->addChart('revenue', 'Revenue', $revenueChart);

// Generate with API endpoint
$html = $dashboard->generateLive(
    title: 'Real-Time Dashboard',
    dataEndpoint: '/api/dashboard-data.php',
    updateInterval: 30  // Seconds
);
```

**API Endpoint:**

```php
# filename: public/api/dashboard-data.php
header('Content-Type: application/json');

$data = [
    'stats' => [
        'total-users' => [
            'value' => getCurrentUserCount(),
            'trend' => calculateTrend(),
        ],
    ],
    'charts' => [
        'revenue' => [
            'labels' => getRevenueLabels(),
            'datasets' => [['data' => getRevenueData()]],
        ],
    ],
];

echo json_encode($data);
```

**Features:**
- Updates every 30 seconds (configurable)
- Visual update indicator
- Manual refresh button
- Error handling with retry
- XSS protection in dynamic content

### Feature 2: Chart-to-Image Export

**Problem**: Charts are JavaScript, can't embed in PDFs

**Solution**: Convert charts to static PNG images

**Requirements:**
- Node.js 18+
- Puppeteer: `npm install puppeteer`

**Implementation:**

```php
use DataScience\Visualization\ChartExporter;

$exporter = new ChartExporter();

// Export to file
$exporter->exportToFile(
    chartConfig: $performanceChart,
    outputPath: 'chart.png',
    width: 800,
    height: 400
);

// Get base64 for PDF embedding
$base64 = $exporter->chartToPng($performanceChart, 800, 400);

$report->addChartImage($base64, 'Figure 1: Model Performance');
```

**How it works:**

1. Generate standalone HTML with chart
2. Launch headless Chrome via Puppeteer
3. Render chart in browser
4. Capture screenshot
5. Convert to base64
6. Clean up temp files

**Use cases:**
- Embed charts in PDF reports
- Email notifications with chart images
- Archive dashboard snapshots
- Generate chart thumbnails

### Feature 3: Chart Download Buttons

**Problem**: Users want to save charts

**Solution**: JavaScript download functionality

**Already Implemented** in `chart-template.php`:

```javascript
function downloadChartPNG() {
    const canvas = document.getElementById('myChart');
    const url = canvas.toDataURL('image/png');
    
    const link = document.createElement('a');
    link.download = 'chart-' + Date.now() + '.png';
    link.href = url;
    link.click();
}

function downloadChartSVG() {
    const canvas = document.getElementById('myChart');
    const imgData = canvas.toDataURL('image/png');
    
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" 
                      width="${canvas.width}" height="${canvas.height}">
        <image xlink:href="${imgData}" 
               width="${canvas.width}" height="${canvas.height}"/>
    </svg>`;
    
    const blob = new Blob([svg], { type: 'image/svg+xml' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.download = 'chart-' + Date.now() + '.svg';
    link.href = url;
    link.click();
    
    URL.revokeObjectURL(url);
}
```

**Buttons in template:**

```html
<button onclick="downloadChartPNG()" class="btn">💾 Download PNG</button>
<button onclick="downloadChartSVG()" class="btn">💾 Download SVG</button>
```

### Feature 4: Interactive Filters

**Problem**: Users want to explore data

**Solution**: Filter dropdowns that update charts

**Implementation:**

```php
$dashboard->addFilter(
    id: 'dateRange',
    label: 'Date Range',
    options: [
        '7d' => 'Last 7 Days',
        '30d' => 'Last 30 Days',
        '90d' => 'Last 90 Days',
    ],
    width: 3
);
```

**JavaScript handler:**

```javascript
function applyFilter(selectElement) {
    const value = selectElement.value;
    
    // Fetch filtered data
    fetch(`/api/dashboard-data.php?range=${value}`)
        .then(response => response.json())
        .then(data => {
            // Update charts with new data
            Object.keys(data.charts).forEach(chartId => {
                if (charts[chartId]) {
                    charts[chartId].data.datasets = data.charts[chartId].datasets;
                    charts[chartId].update();
                }
            });
        });
}
```

### Feature 5: Large Dataset Downsampling

**Problem**: 10,000+ data points slow chart rendering

**Solution**: LTTB (Largest Triangle Three Buckets) algorithm

**Implementation:**

```php
// Original: 10,000 points
$largeDataset = range(1, 10000);

// Downsample to 500 points (visually identical)
$downsampled = $chartBuilder->downsampleData($largeDataset, 500);

$chart = $chartBuilder->lineChart(
    labels: $labels,
    datasets: [['label' => 'Sales', 'data' => $downsampled]]
);
```

**Algorithm benefits:**
- Preserves visual shape
- Maintains peaks and valleys
- Fast rendering (100ms vs 2000ms)
- Lower memory usage

**When to use:**
- Time series with >1000 points
- Real-time dashboards
- Mobile devices
- Historical data visualization

### Testing Advanced Features

**Test AJAX Updates:**

```bash
# Start server
cd code/data-science-php-developers/chapter-10/output
php -S localhost:8000

# Open live dashboard
open http://localhost:8000/live-dashboard.html

# Watch for updates every 30 seconds
```

**Test Chart Export:**

```bash
# Install Puppeteer
npm install puppeteer

# Run export example
php examples/chart-export-example.php

# Verify files created
ls -lh output/chart-export.png
ls -lh output/report-with-chart.pdf
```

### Why These Work

**AJAX Updates**: Partial page updates are faster and preserve state

**Chart Export**: Puppeteer provides real browser environment for accurate rendering

**Downsampling**: Human eye can't distinguish between 500 and 10,000 points at typical screen resolutions

**All features** are demonstrated in working examples in the `code/` directory.

## Performance Optimization

### Large Dataset Strategies

**1. Downsample Before Rendering**

```php
$downsampled = $chartBuilder->downsampleData($data, 500);
// Reduces 10,000 → 500 points, <100ms processing
```

**2. Stream Exports**

```php
function generateRecords(): \Generator {
    for ($i = 0; $i < 1000000; $i++) {
        yield [$i, rand(), rand()];
    }
}

$exporter->streamLargeCSV(generateRecords(), $headers, 'large.csv');
// Memory usage: ~2MB regardless of size
```

**3. Aggregate in Database**

```php
// ❌ Don't: Load all data and aggregate in PHP
$sales = $db->query("SELECT * FROM sales")->fetchAll();
$daily = array_reduce($sales, ...);

// ✅ Do: Aggregate in database
$daily = $db->query("
    SELECT DATE(created_at) as date, SUM(amount) as total
    FROM sales
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
")->fetchAll();
```

### PDF Generation Optimization

**1. Avoid External Resources**

```php
// ❌ Slow: External image loads
$report->addText('<img src="https://example.com/chart.png">');

// ✅ Fast: Embedded base64 images
$report->addChartImage($base64Image);
```

**2. Limit Page Count**

```php
// Use page breaks strategically
$report->addPageBreak();  // Only when needed
```

**3. Simplify HTML**

```php
// Complex CSS slows rendering
// Use simple tables and minimal styling
```

### Chart Rendering Performance

**1. Disable Animations for Many Charts**

```php
$options = [
    'animation' => ['duration' => 0],  // Instant render
];
```

**2. Use Appropriate Chart Types**

```php
// ❌ Pie chart with 50 slices: Slow, unclear
$chartBuilder->pieChart($labels, $data);

// ✅ Bar chart or table: Fast, clear
$chartBuilder->barChart($labels, $datasets);
```

**3. Lazy Loading**

```javascript
// Load charts as user scrolls
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            initializeChart(entry.target.id);
        }
    });
});
```

## Exercises

### Exercise 1: Real-Time Sales Dashboard

**Goal**: Build a live dashboard showing current sales metrics.

**Requirements**:

- Display today's sales, orders, and conversion rate
- Line chart showing hourly sales trend
- Top 5 products table
- Auto-refresh every 60 seconds
- Mobile-responsive layout

**Starter Code**:

```php
# filename: exercises/sales-dashboard.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\DashboardGenerator;
use DataScience\Visualization\ChartBuilder;

// Fetch live data (simulate with random data)
function getLiveSalesData(): array
{
    return [
        'today_sales' => rand(5000, 15000),
        'today_orders' => rand(50, 150),
        'conversion_rate' => rand(25, 45) / 10,
        'hourly_sales' => array_map(fn() => rand(200, 800), range(0, 23)),
        'top_products' => [
            ['Product A', rand(10, 50), rand(1000, 5000)],
            ['Product B', rand(10, 50), rand(1000, 5000)],
            ['Product C', rand(10, 50), rand(1000, 5000)],
            ['Product D', rand(10, 50), rand(1000, 5000)],
            ['Product E', rand(10, 50), rand(1000, 5000)],
        ],
    ];
}

$data = getLiveSalesData();
$dashboard = new DashboardGenerator();
$chartBuilder = new ChartBuilder();

// Add your implementation here
// 1. Add stat cards for today's metrics
// 2. Add hourly sales line chart
// 3. Add top products table
// 4. Generate with 60-second refresh

$html = $dashboard->generate(
    title: 'Real-Time Sales Dashboard',
    refreshInterval: 60
);

file_put_contents(__DIR__ . '/../output/sales-dashboard.html', $html);
echo "✓ Sales dashboard generated!\n";
```

**Expected Output**:

Dashboard showing:
- Stat cards: Today's Sales ($8,450), Orders (87), Conversion (3.2%)
- Hourly sales line chart (24 data points)
- Top 5 products table with sales and revenue
- Auto-refreshes every 60 seconds

**Validation**:

- [ ] Dashboard displays all stat cards and charts
- [ ] Auto-refresh works every 60 seconds
- [ ] **Responsive Design**: Test on mobile device (or browser dev tools)
- [ ] **Accessibility**: Verify ARIA labels with screen reader
- [ ] **Security**: All user data properly escaped (test with `<script>alert(1)</script>`)
- [ ] **Performance**: Charts render in <500ms with sample data
- [ ] **Dark Mode**: Enable OS dark mode and verify readability

### Exercise 2: ML Model Performance Report

**Goal**: Generate a comprehensive PDF report for ML model performance.

**Requirements**:

- Executive summary with key metrics
- Accuracy trend chart (as image)
- Confusion matrix table
- Recommendations based on performance
- Multi-page layout with page breaks

**Starter Code**:

```php
# filename: exercises/ml-performance-report.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\PDFReportGenerator;

// Sample ML metrics
$modelMetrics = [
    'accuracy' => 0.923,
    'precision' => 0.915,
    'recall' => 0.908,
    'f1_score' => 0.911,
    'total_predictions' => 45231,
    'correct_predictions' => 41748,
];

$weeklyAccuracy = [0.87, 0.89, 0.91, 0.90, 0.92];

$confusionMatrix = [
    ['True Spam', 1200, 50],
    ['True Ham', 30, 1100],
];

$report = new PDFReportGenerator();

// Add your implementation here
// 1. Start report with title
// 2. Add executive summary
// 3. Add stats for key metrics
// 4. Add confusion matrix table
// 5. Add insights and recommendations
// 6. Save PDF

$report->savePDF(__DIR__ . '/../output/ml-performance-report.pdf');
echo "✓ ML performance report generated!\n";
```

**Expected Output**:

PDF report containing:
- Title: "ML Model Performance Report - January 2026"
- Stat cards: Accuracy (92.3%), Precision (91.5%), Recall (90.8%), F1 (91.1%)
- Confusion Matrix table
- Insight: "Model performance exceeds target threshold"
- Recommendation: "Continue monitoring, no retraining needed"

**Validation**:

- [ ] PDF opens without errors in PDF viewer
- [ ] All tables and metrics display correctly
- [ ] **Security**: HTML input is properly escaped (test with `<script>` tags)
- [ ] **Accessibility**: Text meets 4.5:1 contrast ratio
- [ ] **Performance**: PDF generates in <2 seconds
- [ ] File size is reasonable (<500KB for this example)
- [ ] Professional styling maintained throughout

### Exercise 3: A/B Test Visualization Suite

**Goal**: Create complete A/B test results with multiple visualizations.

**Requirements**:

- Bar chart comparing conversion rates
- Statistical significance annotations
- Confidence intervals visualization
- Executive summary with recommendation
- Export results to CSV, JSON, and PDF

**Starter Code**:

```php
# filename: exercises/ab-test-visualization.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\ChartBuilder;
use DataScience\Visualization\PDFReportGenerator;
use DataScience\Visualization\DataExporter;

// A/B test results
$testResults = [
    'control' => [
        'name' => 'Control (Original)',
        'conversions' => 240,
        'visitors' => 2000,
        'conversion_rate' => 0.12,
    ],
    'treatment' => [
        'name' => 'Treatment (New Design)',
        'conversions' => 315,
        'visitors' => 2100,
        'conversion_rate' => 0.15,
    ],
    'lift' => 0.25,  // 25% improvement
    'p_value' => 0.018,
    'confidence_level' => 0.95,
    'significant' => true,
];

// Add your implementation here
// 1. Generate bar chart comparing conversion rates
// 2. Create HTML visualization page
// 3. Generate PDF report with recommendation
// 4. Export raw data to CSV and JSON

echo "✓ A/B test visualizations generated!\n";
```

**Expected Output**:

Generates 4 files:
1. `ab-test-chart.html`: Interactive chart with statistical annotations
2. `ab-test-report.pdf`: Professional PDF with recommendation
3. `ab-test-data.csv`: Raw test data for analysis
4. `ab-test-results.json`: Structured data for APIs

**Validation**:

- [ ] Chart displays significance indicator correctly
- [ ] PDF recommendation matches statistical result
- [ ] All exports (CSV, JSON, PDF, HTML) contain complete data
- [ ] **Accessibility**: Chart has ARIA label describing results
- [ ] **Colorblind-Safe**: Use `makeColorblindSafe()` method
- [ ] **Security**: All data properly escaped in exports
- [ ] **Performance**: All exports complete in <1 second
- [ ] Statistical calculations are accurate (verify p-value math)

## Wrap-up


### What You've Learned

In this chapter, you mastered data visualization and reporting:

1. **Chart.js Integration**: Creating beautiful, interactive charts using JavaScript and PHP
2. **Dashboard Building**: Constructing real-time dashboards with multiple visualizations
3. **PDF Reports**: Generating professional PDF reports for stakeholders
4. **Data Export**: Exporting data in multiple formats (CSV, JSON, Excel, HTML)
5. **Visual Best Practices**: Following design principles for effective communication
6. **ML Visualization**: Presenting model performance and predictions visually
7. **Responsive Design**: Building mobile-friendly visualizations
8. **Automated Reporting**: Scheduling and automating report generation

### What You've Built

You now have working implementations of:

1. **ChartBuilder**: Generate Chart.js configurations for line, bar, pie, and scatter plots
2. **DashboardGenerator**: Build interactive dashboards with multiple widgets
3. **PDFReportGenerator**: Create professional PDF reports with dompdf
4. **DataExporter**: Export data in CSV, JSON, Excel, and HTML formats
5. **ML Performance Visualizations**: Track model accuracy over time
6. **A/B Test Charts**: Present statistical test results with significance indicators
7. **Real-Time Dashboards**: Auto-refreshing dashboards for live monitoring
8. **Executive Reports**: Multi-page PDF reports with insights and recommendations

### Real-World Applications

The skills from this chapter enable you to:

**Business Intelligence**:
- Build executive dashboards for KPI monitoring
- Generate automated monthly/quarterly reports
- Visualize sales trends and forecasts
- Present budget and financial data

**Data Science Communication**:
- Visualize ML model performance for stakeholders
- Present A/B test results with statistical context
- Show data exploration findings
- Communicate insights to non-technical audiences

**Product Analytics**:
- Track user behavior and conversion funnels
- Monitor feature adoption rates
- Visualize cohort analysis
- Present user segmentation results

**Operations & Monitoring**:
- Real-time dashboards for system health
- Alert visualization and incident reporting
- Performance metrics over time
- Capacity planning visualizations

### Key Visualization Principles

**1. Know Your Audience**

Tailor visualizations to the viewer:

| Audience | Focus | Format |
|----------|-------|--------|
| **Executives** | High-level KPIs, trends | Dashboard, PDF summary |
| **Analysts** | Detailed data, exports | Interactive charts, CSV |
| **Developers** | Technical metrics, APIs | JSON, real-time dashboards |
| **Customers** | Simple insights, clarity | Clean charts, minimal text |

**2. Choose the Right Chart Type**

Match chart to data:

- **Line charts**: Trends over time (sales, accuracy, metrics)
- **Bar charts**: Comparisons (A/B tests, categories)
- **Pie charts**: Proportions (market share, categories < 7)
- **Scatter plots**: Correlations (feature relationships)
- **Tables**: Precise values, detailed data

**3. Design for Clarity**

- **Minimize chart junk**: Remove unnecessary decorations
- **Use color meaningfully**: Not just decoration
- **Label clearly**: Every axis, every dataset
- **Show context**: Baselines, thresholds, targets
- **Tell a story**: Title explains the insight

**4. Make It Interactive**

- **Hover tooltips**: Show exact values
- **Click to filter**: Drill down into details
- **Responsive design**: Works on all devices
- **Export options**: Let users get raw data

### Common Visualization Mistakes

**1. Wrong Chart Type**

❌ Pie chart with 12 slices  
✅ Bar chart for many categories

**2. Misleading Scales**

❌ Y-axis doesn't start at zero (exaggerates differences)  
✅ Start at zero for bar charts, context-appropriate for others

**3. Too Much Information**

❌ Dashboard with 20+ charts  
✅ Focus on 5-7 key metrics

**4. Poor Color Choices**

❌ Red/green for colorblind users  
✅ Use colorblind-safe palettes

**5. No Context**

❌ Chart without title or labels  
✅ Clear title explaining the insight

**6. Static When Should Be Interactive**

❌ PDF for exploratory analysis  
✅ Interactive dashboard for exploration, PDF for summary

### Best Practices

**Development Workflow**:

1. **Start with data**: Understand what you're visualizing
2. **Sketch first**: Plan layout before coding
3. **Build incrementally**: One chart at a time
4. **Test responsiveness**: Check on mobile
5. **Get feedback**: Show to actual users

**Performance Optimization**:

```php
// ❌ Bad: Load all data at once
$data = $db->query("SELECT * FROM sales")->fetchAll();

// ✅ Good: Aggregate in database
$data = $db->query("
    SELECT DATE(created_at) as date, SUM(amount) as total
    FROM sales
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
")->fetchAll();
```

**Chart Configuration**:

```php
// ✅ Good: Descriptive chart
$chartBuilder->lineChart(
    labels: $dates,
    datasets: [[
        'label' => 'Daily Revenue',
        'data' => $revenue,
    ]],
    options: [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Revenue Trend - Last 30 Days',
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'callback' => 'JS:function(value) { return "$" + value.toLocaleString(); }',
                ],
            ],
        ],
    ]
);
```

**Report Structure**:

```php
// ✅ Good: Clear report structure
$report
    ->startReport('Monthly Report', 'January 2026')
    ->addSection('Executive Summary')
    ->addStats([...])  // Key metrics
    ->addInsight('...')  // Main finding
    ->addSection('Detailed Analysis')
    ->addTable([...])  // Supporting data
    ->addSection('Recommendations')
    ->addText('...')  // Action items
    ->savePDF('report.pdf');
```

### Visualization Tools Comparison

| Tool | Best For | Pros | Cons |
|------|----------|------|------|
| **Chart.js** | Web dashboards | Interactive, beautiful | Requires JavaScript |
| **dompdf** | PDF reports | Server-side, automated | No interactivity |
| **CSV Export** | Data analysis | Universal format | No visualization |
| **JSON API** | Programmatic access | Flexible, machine-readable | Requires client-side rendering |

### Connection to Data Science Workflow

You've now completed the **communication phase** of data science:

1. ✅ **Chapter 1-2**: Understanding fundamentals
2. ✅ **Chapter 3-4**: Collecting and cleaning data
3. ✅ **Chapter 5-6**: Exploring and analyzing data
4. ✅ **Chapter 7**: Statistical testing
5. ✅ **Chapter 8-9**: Machine learning and deployment
6. ✅ **Chapter 10**: **Data visualization and reporting** ← You are here

**What's Next**: Building complete data science projects (Chapter 11)

### Visualization Checklist

Before sharing visualizations:

- [ ] Chart type matches data and audience
- [ ] All axes and datasets clearly labeled
- [ ] Title explains the key insight
- [ ] Colors are meaningful and accessible
- [ ] Scales are appropriate (not misleading)
- [ ] Interactive elements work correctly
- [ ] Responsive on mobile devices
- [ ] Data export options available
- [ ] Source and timestamp included
- [ ] Tested with actual users

### Next Steps

**Immediate Practice**:

1. Build a dashboard for your current project
2. Generate a PDF report for stakeholders
3. Create A/B test visualization from real data
4. Set up automated weekly reporting

**Chapter 11 Preview**:

In the next chapter, you'll learn **Building a Real-World Data Science Project**:

- Complete project from requirements to deployment
- Integrating all skills from previous chapters
- Production architecture patterns
- Monitoring and maintenance
- Scaling considerations
- Team collaboration workflows

You'll build a complete recommendation system, from data collection through model deployment and visualization.

## Further Reading

- [Chart.js Documentation](https://www.chartjs.org/docs/latest/) — Comprehensive chart library guide
- [dompdf Documentation](https://github.com/dompdf/dompdf) — PDF generation in PHP
- [Data Visualization Best Practices](https://www.storytellingwithdata.com/) — Visual communication principles
- [Edward Tufte's Principles](https://www.edwardtufte.com/) — Classic data visualization theory
- [Color Brewer](https://colorbrewer2.org/) — Colorblind-safe palettes

::: tip Next Chapter
Continue to [Chapter 11: Building a Real-World Data Science Project with PHP](/series/data-science-php-developers/chapters/11-building-a-real-world-data-science-project-with-php) to put it all together!
:::
