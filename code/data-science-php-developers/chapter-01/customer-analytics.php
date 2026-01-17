<?php

declare(strict_types=1);

/**
 * Use Case 1: Customer Analytics Dashboard
 * 
 * This example demonstrates how to segment customers based on their
 * purchasing behavior using PHP and database queries.
 * 
 * Requirements:
 * - PHP 8.4+
 * - Database with 'orders' table
 * - Laravel/Eloquent (or adapt to PDO)
 */

// For Laravel/Eloquent (replace with your database setup)
use Illuminate\Support\Facades\DB;

// Collect data from database
$customers = DB::table('orders')
    ->select(
        'customer_id',
        DB::raw('COUNT(*) as order_count'),
        DB::raw('SUM(total) as lifetime_value'),
        DB::raw('AVG(total) as avg_order_value'),
        DB::raw('MAX(created_at) as last_order_date')
    )
    ->groupBy('customer_id')
    ->get();

// Segment customers
$segments = [
    'high_value' => $customers->where('lifetime_value', '>', 1000)->count(),
    'at_risk' => $customers->where('last_order_date', '<', now()->subMonths(6))->count(),
    'new' => $customers->where('order_count', '=', 1)->count(),
];

// Generate insights
echo "Customer Segments:\n";
echo "High Value: {$segments['high_value']} customers\n";
echo "At Risk: {$segments['at_risk']} customers\n";
echo "New: {$segments['new']} customers\n";

// Additional analysis: Calculate segment percentages
$totalCustomers = $customers->count();
foreach ($segments as $name => $count) {
    $percentage = ($count / $totalCustomers) * 100;
    echo sprintf("%s: %.1f%%\n", ucfirst(str_replace('_', ' ', $name)), $percentage);
}
