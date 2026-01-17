<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\ML\SimpleClusterer;

echo "=== Unsupervised Learning: K-Means Clustering ===\n\n";

// Customer segmentation: [annual_spend, visits_per_month]
$customers = [
    // Low spenders, few visits
    [100, 2], [150, 3], [120, 2], [110, 2],
    // Medium spenders, medium visits
    [500, 8], [600, 10], [550, 9], [580, 8],
    // High spenders, many visits
    [1200, 20], [1100, 18], [1300, 22], [1250, 21],
];

$clusterer = new SimpleClusterer();

echo "Customer Data (spend, visits):\n";
foreach (array_slice($customers, 0, 4) as $i => $customer) {
    echo "  Customer " . ($i + 1) . ": [$" . $customer[0] . ", {$customer[1]} visits]\n";
}
echo "  ... and " . (count($customers) - 4) . " more\n\n";

// Fit K-Means with K=3 clusters
$clusterer->fit($customers, k: 3);

$labels = $clusterer->getLabels();
$centroids = $clusterer->getCentroids();

echo "Clustering Results (K=3):\n\n";

// Show cluster assignments
$clusterGroups = [];
foreach ($labels as $i => $cluster) {
    $clusterGroups[$cluster][] = $i + 1;
}

foreach ($clusterGroups as $cluster => $customerIds) {
    $centroid = $centroids[$cluster];

    echo "Cluster " . ($cluster + 1) . ":\n";
    echo "  Centroid: [$" . round($centroid[0]) . ", " . round($centroid[1]) . " visits]\n";
    echo "  Customers: " . implode(', ', $customerIds) . "\n";

    // Interpret cluster
    if ($centroid[0] < 200) {
        echo "  → Low-value customers\n";
    } elseif ($centroid[0] < 700) {
        echo "  → Medium-value customers\n";
    } else {
        echo "  → High-value customers (VIP)\n";
    }

    echo "\n";
}

// Calculate inertia (quality metric)
$inertia = $clusterer->inertia($customers);
echo "Inertia: " . round($inertia, 2) . " (lower = tighter clusters)\n\n";

// Predict cluster for new customers
echo "Predicting clusters for new customers:\n";

$newCustomers = [
    [130, 3],   // Should be cluster 1 (low)
    [580, 9],   // Should be cluster 2 (medium)
    [1150, 19], // Should be cluster 3 (high)
];

$predictions = $clusterer->predict($newCustomers);

foreach ($newCustomers as $i => $customer) {
    $cluster = $predictions[$i] + 1;
    echo "  [$" . $customer[0] . ", {$customer[1]} visits] → Cluster {$cluster}\n";
}

echo "\n✓ Clustering complete!\n";
