<?php

declare(strict_types=1);

/**
 * Unsupervised Learning: Customer Segmentation
 * 
 * This example demonstrates unsupervised learning where we discover
 * natural groupings in customer data without predefined labels.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Clustering\KMeans;

echo "\n👥 Unsupervised Learning: Customer Segmentation\n";
echo "================================================\n\n";

// Customer Data (no labels!)
$customerData = [
    // [monthly_spending, visits_per_month, items_purchased]
    [20, 2, 1],      // Low engagement customers
    [25, 3, 2],
    [18, 2, 1],
    [22, 2, 2],

    [150, 12, 15],   // Regular customers
    [180, 14, 18],
    [160, 13, 16],
    [170, 15, 17],
    [155, 11, 14],

    [500, 25, 50],   // VIP customers
    [600, 28, 55],
    [550, 26, 52],
    [580, 27, 54],
    [520, 24, 48],

    [30, 4, 3],      // More low engagement
    [35, 5, 4],

    [190, 16, 20],   // More regulars
    [165, 14, 17],

    [610, 29, 58],   // More VIPs
    [540, 25, 51],
];

echo "Dataset: " . count($customerData) . " customers\n";
echo "Features: [monthly_spending, visits_per_month, items_purchased]\n";
echo "Labels: NONE - we don't know the customer segments yet!\n\n";

echo "Sample customers:\n";
for ($i = 0; $i < 5; $i++) {
    echo "  Customer " . ($i + 1) . ": \$" . $customerData[$i][0] . "/month, " .
        $customerData[$i][1] . " visits, " . $customerData[$i][2] . " items\n";
}
echo "\n";

// Perform Clustering
echo "Performing K-Means clustering (k=3 clusters)...\n";
$kmeans = new KMeans(3);  // Number of clusters
$clusters = $kmeans->cluster($customerData);
echo "✓ Clustering complete! Discovered " . count($clusters) . " customer segments\n\n";

// Analyze Discovered Clusters
echo "================================================\n";
echo "Discovered Customer Segments\n";
echo "================================================\n\n";

foreach ($clusters as $clusterIndex => $clusterPoints) {
    $clusterNumber = $clusterIndex + 1;
    $numCustomers = count($clusterPoints);

    // Calculate cluster statistics
    $avgSpending = array_sum(array_column($clusterPoints, 0)) / $numCustomers;
    $avgVisits = array_sum(array_column($clusterPoints, 1)) / $numCustomers;
    $avgItems = array_sum(array_column($clusterPoints, 2)) / $numCustomers;

    echo "Segment {$clusterNumber}: {$numCustomers} customers\n";
    echo "  Average monthly spending: \$" . number_format($avgSpending, 2) . "\n";
    echo "  Average visits per month: " . number_format($avgVisits, 1) . "\n";
    echo "  Average items purchased: " . number_format($avgItems, 1) . "\n";

    // Interpret the segment
    if ($avgSpending < 50) {
        echo "  📊 Business Segment: Occasional Customers (Low Engagement)\n";
        echo "     Strategy: Send targeted promotions to increase engagement\n";
    } elseif ($avgSpending < 300) {
        echo "  📊 Business Segment: Regular Customers (Core Base)\n";
        echo "     Strategy: Maintain satisfaction, encourage loyalty program\n";
    } else {
        echo "  📊 Business Segment: VIP Customers (High Value)\n";
        echo "     Strategy: Provide premium service, exclusive offers\n";
    }

    echo "\n";
}

// Show some individual customer assignments
echo "================================================\n";
echo "Sample Customer Assignments\n";
echo "================================================\n\n";

// Manually assign some customers to show the concept
$sampleCustomers = [
    ['spending' => 20, 'visits' => 2, 'items' => 1, 'name' => 'Alice'],
    ['spending' => 170, 'visits' => 15, 'items' => 17, 'name' => 'Bob'],
    ['spending' => 550, 'visits' => 26, 'items' => 52, 'name' => 'Charlie'],
];

foreach ($sampleCustomers as $customer) {
    $customerFeatures = [$customer['spending'], $customer['visits'], $customer['items']];

    // Find which cluster this customer belongs to
    $assignedCluster = null;
    foreach ($clusters as $clusterIndex => $clusterPoints) {
        foreach ($clusterPoints as $point) {
            if (
                $point[0] == $customerFeatures[0] &&
                $point[1] == $customerFeatures[1] &&
                $point[2] == $customerFeatures[2]
            ) {
                $assignedCluster = $clusterIndex + 1;
                break 2;
            }
        }
    }

    echo "Customer: {$customer['name']}\n";
    echo "  Profile: \${$customer['spending']}/month, {$customer['visits']} visits, {$customer['items']} items\n";
    echo "  Assigned to: Segment {$assignedCluster}\n\n";
}

echo "================================================\n";
echo "Key Concepts Demonstrated:\n";
echo "================================================\n\n";

echo "1. Unsupervised Learning: No predefined labels provided\n";
echo "2. Pattern Discovery: Algorithm found natural groupings\n";
echo "3. Clustering: Similar customers grouped together\n";
echo "4. Business Interpretation: We interpret segments after discovery\n";
echo "5. K-Means Algorithm: Grouped customers by similarity\n\n";

echo "How It Works:\n";
echo "- K-Means calculates distance between customer feature vectors\n";
echo "- Customers with similar spending/behavior are grouped together\n";
echo "- Each cluster represents a distinct customer segment\n";
echo "- We interpret what each segment means for business strategy\n\n";

echo "When to Use Unsupervised Learning:\n";
echo "- Exploring data without knowing what patterns exist\n";
echo "- Discovering natural groupings (customer segments, user types)\n";
echo "- Anomaly detection (finding unusual patterns)\n";
echo "- When you don't have labeled data\n\n";
