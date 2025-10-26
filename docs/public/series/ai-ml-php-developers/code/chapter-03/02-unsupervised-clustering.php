<?php

declare(strict_types=1);

/**
 * Example 2: Unsupervised Learning - Customer Segmentation
 * 
 * This demonstrates unsupervised learning where we discover patterns
 * and groups in unlabeled data without being told what to look for.
 */

require __DIR__ . '/../../chapter-02/vendor/autoload.php';

use Phpml\Clustering\KMeans;

echo "=== Unsupervised Learning: Customer Segmentation ===\n\n";

// Unlabeled customer data: [monthly_spending, visit_frequency]
// Notice: NO labels! We don't tell the algorithm what groups exist
$customerData = [
    [20, 2],    // Low spender, rare visitor
    [25, 3],    // Low spender, rare visitor
    [22, 2],    // Low spender, rare visitor
    [200, 15],  // High spender, frequent visitor
    [220, 18],  // High spender, frequent visitor
    [210, 16],  // High spender, frequent visitor
    [100, 8],   // Medium spender, regular visitor
    [110, 9],   // Medium spender, regular visitor
    [105, 7],   // Medium spender, regular visitor
    [23, 3],    // Low spender, rare visitor
    [215, 17],  // High spender, frequent visitor
    [108, 8],   // Medium spender, regular visitor
];

echo "Customer data: " . count($customerData) . " customers\n";
echo "Features: [monthly_spending ($), visit_frequency (visits/month)]\n";
echo "Note: Data is UNLABELED - we don't know which group each belongs to\n\n";

// Step 1: Cluster the data into 3 groups
echo "Running K-Means clustering (k=3)...\n";
$kmeans = new KMeans(n: 3);
$clusters = $kmeans->cluster($customerData);
echo "✓ Clustering complete\n\n";

// Step 2: Display the discovered clusters
echo "Discovered Customer Segments:\n";
echo str_repeat('=', 60) . "\n";

foreach ($clusters as $clusterIndex => $clusterMembers) {
    echo "\nCluster " . ($clusterIndex + 1) . " (" . count($clusterMembers) . " customers):\n";
    
    // Calculate cluster characteristics
    $avgSpending = array_sum(array_column($clusterMembers, 0)) / count($clusterMembers);
    $avgVisits = array_sum(array_column($clusterMembers, 1)) / count($clusterMembers);
    
    echo "  Average spending: $" . round($avgSpending, 2) . "/month\n";
    echo "  Average visits: " . round($avgVisits, 1) . " visits/month\n";
    
    // Assign a business-friendly label based on characteristics
    if ($avgSpending > 150) {
        $segment = "VIP Customers (high value)";
    } elseif ($avgSpending > 80) {
        $segment = "Regular Customers (medium value)";
    } else {
        $segment = "Occasional Customers (low engagement)";
    }
    
    echo "  → Business Segment: {$segment}\n";
    
    // Show first few customers in this cluster
    echo "  Sample customers: ";
    $sampleCount = min(3, count($clusterMembers));
    for ($i = 0; $i < $sampleCount; $i++) {
        echo "[${$clusterMembers[$i][0]}, ${$clusterMembers[$i][1]}] ";
    }
    echo "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "How It Works:\n";
echo str_repeat('=', 60) . "\n";
echo "1. We provided only the features (spending, visits) - NO labels\n";
echo "2. K-Means discovered natural groupings in the data\n";
echo "3. Customers with similar behavior were grouped together\n";
echo "4. We interpreted the clusters to understand customer segments\n\n";

echo "This is unsupervised learning because:\n";
echo "- No labels or 'correct answers' were provided\n";
echo "- The algorithm discovered patterns on its own\n";
echo "- We found structure in the data we didn't know existed\n\n";

echo "Business Application:\n";
echo "- Target VIP customers with premium offers\n";
echo "- Engage Regular customers with loyalty programs\n";
echo "- Re-activate Occasional customers with special deals\n";

