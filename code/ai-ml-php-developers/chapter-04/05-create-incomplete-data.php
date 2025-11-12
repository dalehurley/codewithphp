<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 5: Creating Incomplete Data
 * 
 * Demonstrates: Generating data with missing values for testing preprocessing
 */

// Generate customer data with intentional missing values
$customers = [];
$cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', null];
$subscriptions = [1, 0, null];

for ($i = 1; $i <= 30; $i++) {
    $customers[] = [
        'customer_id' => $i,
        'age' => rand(0, 10) < 8 ? rand(22, 65) : null, // 20% missing
        'city' => $cities[array_rand($cities)],
        'total_orders' => rand(1, 50),
        'avg_order_value' => rand(0, 10) < 9 ? rand(20, 200) : null, // 10% missing
        'has_subscription' => $subscriptions[array_rand($subscriptions)]
    ];
}

file_put_contents(
    __DIR__ . '/data/incomplete_customers.json',
    json_encode($customers, JSON_PRETTY_PRINT)
);

echo "Generated 30 customer records with missing values\n";
