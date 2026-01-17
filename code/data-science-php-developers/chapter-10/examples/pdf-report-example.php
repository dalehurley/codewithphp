<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\PDFReportGenerator;

echo "=== PDF Report Generation Example ===\n\n";

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
    ->addText('Our machine learning models maintained excellent performance throughout the month:')
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
    ->addText('Analysis of customer segments reveals clear patterns:', allowHTML: false)
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
    ->addText('1. <strong>Increase focus on high-value customer retention</strong> - This segment represents 10% of users but 45% of revenue.', allowHTML: true)
    ->addText('2. <strong>Optimize conversion funnel</strong> - Drop-off rate at checkout increased by 0.8 percentage points.', allowHTML: true)
    ->addText('3. <strong>Expand product recommendations</strong> - ML-powered recommendations showed 25% higher conversion.', allowHTML: true)
    
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
echo "✓ Features:\n";
echo "   - Secure HTML escaping prevents XSS\n";
echo "   - Professional formatting with sections\n";
echo "   - Tables with proper styling\n";
echo "   - Insight and warning boxes\n";
echo "   - Multi-page layout with page breaks\n";
