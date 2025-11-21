<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\AnalyticsEngine;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Setup logger
$logger = new Logger('analytics');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// Initialize Claude client
$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException('ANTHROPIC_API_KEY not set');
$client = new ClaudePhp(
        apiKey: $apiKey
    );

$analytics = new AnalyticsEngine($client, $logger);

echo "=== Analytics Engine Examples ===\n\n";

// Example 1: Basic analytics
echo "Example 1: Sales Data Analytics\n";
echo str_repeat('-', 50) . "\n";

$salesData = [
    'invoice_number' => 'INV-2024-001',
    'customer_name' => 'Acme Corp',
    'product' => 'Enterprise License',
    'quantity' => 10,
    'unit_price' => 1999.99,
    'total' => 19999.90,
    'discount' => 2000.00,
    'final_total' => 17999.90,
    'payment_method' => 'Credit Card',
    'status' => 'Paid',
    'invoice_date' => '2024-01-15',
    'due_date' => '2024-02-15'
];

$analysis = $analytics->analyze($salesData, [
    'detect_patterns' => true,
    'ai_insights' => false,  // Disable AI for this example
    'detect_anomalies' => true
]);

echo "Statistics:\n";
print_r($analysis['statistics']);

echo "\nPatterns:\n";
print_r($analysis['patterns']);

echo "\nQuality Metrics:\n";
print_r($analysis['quality']);

if (!empty($analysis['anomalies'])) {
    echo "\nAnomalies Detected:\n";
    foreach ($analysis['anomalies'] as $anomaly) {
        echo "  - {$anomaly['field']}: {$anomaly['description']}\n";
    }
}
echo "\n";

// Example 2: Customer data analytics
echo "Example 2: Customer Profile Analytics\n";
echo str_repeat('-', 50) . "\n";

$customerData = [
    'customer_id' => 'CUST-12345',
    'name' => 'Jane Smith',
    'email' => 'jane.smith@example.com',
    'phone' => '+1-555-123-4567',
    'registration_date' => '2023-06-15',
    'total_orders' => 42,
    'total_spent' => 15678.50,
    'average_order_value' => 373.30,
    'loyalty_tier' => 'Gold',
    'last_purchase_date' => '2024-01-10'
];

$analysis = $analytics->analyze($customerData, [
    'detect_patterns' => true,
    'detect_anomalies' => true
]);

echo "Customer Data Quality:\n";
echo "- Completeness: " . round($analysis['statistics']['completeness'] * 100, 1) . "%\n";
echo "- Quality Score: " . round($analysis['quality']['overall_score'] * 100, 1) . "%\n";
echo "- Non-empty Fields: {$analysis['statistics']['non_empty_fields']}\n";
echo "- Total Fields: {$analysis['statistics']['total_fields']}\n\n";

if (isset($analysis['patterns']['numeric_stats'])) {
    echo "Numeric Analysis:\n";
    $stats = $analysis['patterns']['numeric_stats'];
    echo "- Count: {$stats['count']}\n";
    echo "- Sum: \$" . number_format($stats['sum'], 2) . "\n";
    echo "- Average: \$" . number_format($stats['average'], 2) . "\n";
    echo "- Min: \$" . number_format($stats['min'], 2) . "\n";
    echo "- Max: \$" . number_format($stats['max'], 2) . "\n";
}
echo "\n";

// Example 3: Generate comprehensive report
echo "Example 3: Comprehensive Analytics Report\n";
echo str_repeat('-', 50) . "\n";

$orderData = [
    'order_id' => 'ORD-2024-789',
    'customer' => 'Tech Innovations LLC',
    'items_count' => 5,
    'subtotal' => 4599.95,
    'shipping' => 49.99,
    'tax' => 372.00,
    'total' => 5021.94,
    'order_date' => '2024-01-12',
    'ship_date' => '2024-01-13',
    'delivery_date' => '2024-01-15'
];

$analysis = $analytics->analyze($orderData);
$report = $analytics->generateReport($orderData, $analysis);

echo $report;

// Example 4: Compare datasets
echo "\nExample 4: Dataset Comparison\n";
echo str_repeat('-', 50) . "\n";

$datasets = [
    [
        'name' => 'Product A',
        'price' => 99.99,
        'stock' => 150,
        'sales' => 450
    ],
    [
        'name' => 'Product B',
        'price' => 149.99,
        'stock' => 75,
        'sales' => 320
    ],
    [
        'name' => 'Product C',
        'price' => 199.99,
        'stock' => 50,
        'sales' => 180,
        'featured' => true  // Unique field
    ]
];

$comparison = $analytics->compareDatasets($datasets);

echo "Dataset Comparison:\n";
echo "- Total Datasets: {$comparison['count']}\n";
echo "- Common Fields: " . implode(', ', $comparison['common_fields']) . "\n";
echo "- Field Overlap: " . round($comparison['field_overlap'] * 100, 1) . "%\n\n";

// Example 5: Pattern detection
echo "Example 5: Advanced Pattern Detection\n";
echo str_repeat('-', 50) . "\n";

$complexData = [
    'id' => 'TRX-2024-001',
    'transaction_date' => '2024-01-15',
    'processed_date' => '2024-01-16',
    'settlement_date' => '2024-01-17',
    'amount' => 5000.00,
    'fee' => 150.00,
    'net' => 4850.00,
    'currency' => 'USD',
    'payment_method' => 'Wire Transfer',
    'status' => 'Completed',
    'items' => ['item1', 'item2', 'item3'],
    'metadata' => [
        'source' => 'api',
        'version' => '2.0'
    ]
];

$analysis = $analytics->analyze($complexData, [
    'detect_patterns' => true,
    'detect_anomalies' => true
]);

if (isset($analysis['patterns'])) {
    echo "Detected Patterns:\n";

    if (isset($analysis['patterns']['date_fields'])) {
        echo "  Date Fields: " . implode(', ', $analysis['patterns']['date_fields']) . "\n";
    }

    if (isset($analysis['patterns']['numeric_fields'])) {
        echo "  Numeric Fields: " . implode(', ', $analysis['patterns']['numeric_fields']) . "\n";
    }

    if (isset($analysis['patterns']['array_fields'])) {
        echo "  Array Fields:\n";
        foreach ($analysis['patterns']['array_fields'] as $field => $count) {
            echo "    - {$field}: {$count} items\n";
        }
    }
}
echo "\n";

echo "=== Analytics Engine Examples Complete ===\n";
