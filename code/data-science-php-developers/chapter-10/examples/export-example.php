<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Visualization\DataExporter;

echo "=== Data Export Example ===\n\n";

$exporter = new DataExporter();

// Sample data
$headers = ['Product', 'Sales', 'Revenue', 'Growth'];
$rows = [
    ['Product A', '1247', '45231', '+12%'],
    ['Product B', '892', '31220', '+8%'],
    ['Product C', '743', '22100', '-3%'],
    ['Product D', '612', '18360', '+15%'],
    ['Product E', '501', '15030', '+5%'],
];

// Export to CSV
$exporter->toCSV(
    headers: $headers,
    rows: $rows,
    filename: __DIR__ . '/../output/products.csv'
);
echo "✓ Exported to CSV: output/products.csv\n";

// Export to Excel-compatible CSV (with UTF-8 BOM)
$exporter->toExcelCSV(
    headers: $headers,
    rows: $rows,
    filename: __DIR__ . '/../output/products-excel.csv'
);
echo "✓ Exported to Excel CSV: output/products-excel.csv\n";

// Export to JSON with metadata
$jsonData = [
    'report_date' => date('Y-m-d'),
    'report_type' => 'Product Performance',
    'headers' => $headers,
    'data' => $rows,
    'summary' => [
        'total_products' => count($rows),
        'total_sales' => array_sum(array_column($rows, 1)),
        'generated_at' => date('Y-m-d H:i:s'),
    ],
];

$exporter->toJSON(
    data: $jsonData,
    filename: __DIR__ . '/../output/products.json'
);
echo "✓ Exported to JSON: output/products.json\n";

// Export to HTML table (secure, XSS-protected)
$html = $exporter->toHTML(
    headers: $headers,
    rows: $rows,
    title: 'Product Performance Report'
);

file_put_contents(__DIR__ . '/../output/products.html', $html);
echo "✓ Exported to HTML: output/products.html\n";

// Demonstrate large dataset streaming export
echo "\n✓ Demonstrating streaming export for large datasets...\n";

function generateLargeDataset(): \Generator
{
    // Simulate 10,000 records
    for ($i = 1; $i <= 10000; $i++) {
        yield [
            'Record ' . $i,
            rand(100, 1000),
            rand(1000, 10000),
            (rand(0, 1) ? '+' : '-') . rand(1, 20) . '%'
        ];
    }
}

$exporter->streamLargeCSV(
    dataGenerator: generateLargeDataset(),
    headers: $headers,
    filename: __DIR__ . '/../output/products-large.csv'
);
echo "✓ Exported 10,000 records using streaming (memory-efficient)\n";

echo "\n✓ All exports completed successfully!\n";
echo "✓ Features:\n";
echo "   - Secure XSS protection in HTML export\n";
echo "   - UTF-8 BOM for Excel compatibility\n";
echo "   - Streaming for large datasets\n";
echo "   - Error handling and validation\n";
echo "   - Multiple format support\n";
