<?php

declare(strict_types=1);

/**
 * Use Case 2: A/B Test Analysis
 * 
 * This example shows how to analyze A/B test results to determine
 * if a change (like a new button color) improves conversions.
 * 
 * Requirements:
 * - PHP 8.4+
 * - Database with 'events' table tracking variants and conversions
 * - Laravel/Eloquent (or adapt to PDO)
 */

use Illuminate\Support\Facades\DB;

// Collect experiment data
$controlGroup = DB::table('events')
    ->where('variant', 'control')
    ->where('event_type', 'conversion')
    ->count();

$testGroup = DB::table('events')
    ->where('variant', 'test')
    ->where('event_type', 'conversion')
    ->count();

$controlViews = DB::table('events')
    ->where('variant', 'control')
    ->where('event_type', 'view')
    ->count();

$testViews = DB::table('events')
    ->where('variant', 'test')
    ->where('event_type', 'view')
    ->count();

// Calculate conversion rates
$controlRate = $controlViews > 0 ? $controlGroup / $controlViews : 0;
$testRate = $testViews > 0 ? $testGroup / $testViews : 0;
$lift = $controlRate > 0 ? (($testRate - $controlRate) / $controlRate) * 100 : 0;

// Display results
echo "A/B Test Results:\n";
echo "Control: " . number_format($controlRate * 100, 2) . "%\n";
echo "Test: " . number_format($testRate * 100, 2) . "%\n";
echo "Lift: " . number_format($lift, 2) . "%\n\n";

// Statistical significance (simplified)
// In production, use proper statistical tests like chi-square or z-test
if ($testGroup > 100 && $lift > 10) {
    echo "✅ Test variant shows significant improvement\n";
    echo "Recommendation: Deploy the test variant\n";
} else {
    echo "⚠️ Not enough data or lift to declare winner\n";
    echo "Recommendation: Continue running test or try a different approach\n";
}

// Additional metrics
echo "\nSample Sizes:\n";
echo "Control: {$controlViews} views, {$controlGroup} conversions\n";
echo "Test: {$testViews} views, {$testGroup} conversions\n";
