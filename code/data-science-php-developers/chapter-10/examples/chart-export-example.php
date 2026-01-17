<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\ChartBuilder;
use DataScience\Visualization\ChartExporter;
use DataScience\Visualization\PDFReportGenerator;

echo "=== Chart Export Example ===\n\n";

$chartBuilder = new ChartBuilder();

// Create a chart
$performanceChart = $chartBuilder->modelPerformanceChart(
    dates: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
    accuracyScores: [0.87, 0.89, 0.91, 0.88, 0.92],
    threshold: 0.85
);

$performanceChart = $chartBuilder->makeColorblindSafe($performanceChart);

echo "1. Checking chart export requirements...\n";

// Check if Node.js is available
exec('which node', $output, $returnCode);
if ($returnCode !== 0) {
    echo "   ⚠️  Node.js not found. Chart export requires:\n";
    echo "      - Node.js 18+ (https://nodejs.org/)\n";
    echo "      - Puppeteer: npm install puppeteer\n";
    echo "\n   Skipping chart-to-image export example.\n";
    echo "   Other examples work without Node.js.\n";
    exit(0);
}

echo "   ✓ Node.js found: " . trim(implode('', $output)) . "\n";

// Check if Puppeteer is installed
$nodeModulesPath = __DIR__ . '/../node_modules/puppeteer';
if (!is_dir($nodeModulesPath)) {
    echo "   ⚠️  Puppeteer not installed\n";
    echo "      Run: npm install puppeteer\n";
    echo "      From: " . dirname(__DIR__) . "\n";
    echo "\n   Skipping chart export example.\n";
    exit(0);
}

echo "   ✓ Puppeteer installed\n";

echo "\n2. Exporting chart as PNG image...\n";

$exporter = new ChartExporter(__DIR__ . '/../temp/charts');

try {
    // Export chart to PNG file
    $exporter->exportToFile(
        chartConfig: $performanceChart,
        outputPath: __DIR__ . '/../output/chart-export.png',
        width: 800,
        height: 400
    );
    
    echo "   ✓ Chart exported to: output/chart-export.png\n";
    
    echo "\n3. Converting chart to base64 for PDF embedding...\n";
    
    // Convert chart to base64 for PDF inclusion
    $base64Image = $exporter->chartToPng($performanceChart, 800, 400);
    
    echo "   ✓ Chart converted to base64 (" . number_format(strlen($base64Image)) . " characters)\n";
    
    echo "\n4. Generating PDF report with embedded chart...\n";
    
    // Create PDF report with embedded chart image
    $report = new PDFReportGenerator();
    
    $report
        ->startReport(
            title: 'ML Model Performance Report',
            subtitle: 'With Embedded Chart Image'
        )
        ->addSection('Executive Summary')
        ->addText('This report demonstrates embedding Chart.js visualizations as static images in PDF documents.')
        ->addStats([
            ['label' => 'Current Accuracy', 'value' => '92%'],
            ['label' => 'Avg Accuracy', 'value' => '89.4%'],
            ['label' => 'Total Predictions', 'value' => '1,247'],
        ])
        ->addSection('Model Performance Trend')
        ->addChartImage(
            base64Image: $base64Image,
            caption: 'Figure 1: Model accuracy over 5 weeks with 85% threshold'
        )
        ->addText('The chart above shows steady improvement in model accuracy over the past 5 weeks.')
        ->addInsight('Current accuracy of 92% exceeds the required 85% threshold.')
        ->savePDF(__DIR__ . '/../output/report-with-chart.pdf');
    
    echo "   ✓ PDF with embedded chart saved to: output/report-with-chart.pdf\n";
    
    // Clean up temp files
    $exporter->cleanup();
    
    echo "\n✓ Chart export example completed successfully!\n";
    echo "\n✓ Generated files:\n";
    echo "   - output/chart-export.png (standalone image)\n";
    echo "   - output/report-with-chart.pdf (PDF with embedded chart)\n";
    echo "\n✓ Use cases:\n";
    echo "   - Embed charts in PDF reports for offline viewing\n";
    echo "   - Create chart thumbnails for email notifications\n";
    echo "   - Generate static chart images for documentation\n";
    echo "   - Archive dashboard snapshots\n";
    
} catch (\RuntimeException $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "\n   Make sure:\n";
    echo "   1. Node.js 18+ is installed\n";
    echo "   2. Puppeteer is installed: npm install puppeteer\n";
    echo "   3. scripts/capture-chart.js exists\n";
}
