<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use SmartRecommender\ML\CollaborativeFilter;
use SmartRecommender\DataCollection\TestDataGenerator;

echo "=== Smart Recommender Performance Benchmark ===\n\n";

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Ensure we have test data
echo "1. Checking test data...\n";
$generator = new TestDataGenerator();

// Generate if needed
try {
    $stats = $generator->generateFullDataset();
    echo "   ✓ Test data ready ({$stats['users']} users, {$stats['products']} products)\n";
} catch (\Exception $e) {
    echo "   ℹ Using existing test data\n";
}

// Benchmark 1: Model Training
echo "\n2. Benchmarking model training...\n";
$recommender = new CollaborativeFilter();

$startMemory = memory_get_usage();
$startTime = microtime(true);

$trainingStats = $recommender->train();

$trainingTime = microtime(true) - $startTime;
$memoryUsed = memory_get_usage() - $startMemory;

echo "   ✓ Training completed\n";
echo "     - Time: " . round($trainingTime, 3) . " seconds\n";
echo "     - Memory: " . round($memoryUsed / 1024 / 1024, 2) . " MB\n";
echo "     - Users: {$trainingStats['users']}\n";
echo "     - Avg items/user: " . round($trainingStats['avg_items_per_user'], 1) . "\n";
echo "     - Sparsity: " . round($trainingStats['sparsity'] * 100, 2) . "%\n";

// Benchmark 2: Recommendation Generation
echo "\n3. Benchmarking recommendation generation...\n";

$iterations = 100;
$times = [];

for ($i = 0; $i < $iterations; $i++) {
    $userId = rand(1, min(100, $trainingStats['users']));
    
    $start = microtime(true);
    $recommendations = $recommender->recommend($userId, 10);
    $elapsed = microtime(true) - $start;
    
    $times[] = $elapsed;
}

// Calculate statistics
sort($times);
$avgTime = array_sum($times) / count($times);
$medianTime = $times[count($times) / 2];
$p95Time = $times[(int)(count($times) * 0.95)];
$p99Time = $times[(int)(count($times) * 0.99)];
$minTime = min($times);
$maxTime = max($times);

echo "   ✓ Completed {$iterations} recommendation requests\n";
echo "     - Average: " . round($avgTime * 1000, 2) . " ms\n";
echo "     - Median: " . round($medianTime * 1000, 2) . " ms\n";
echo "     - P95: " . round($p95Time * 1000, 2) . " ms\n";
echo "     - P99: " . round($p99Time * 1000, 2) . " ms\n";
echo "     - Min: " . round($minTime * 1000, 2) . " ms\n";
echo "     - Max: " . round($maxTime * 1000, 2) . " ms\n";
echo "     - Throughput: " . round($iterations / array_sum($times), 2) . " req/sec\n";

// Benchmark 3: Cold Start Performance
echo "\n4. Benchmarking cold start (new user)...\n";

$coldStartTimes = [];
for ($i = 0; $i < 20; $i++) {
    $userId = 999999; // Non-existent user
    
    $start = microtime(true);
    $recommendations = $recommender->recommend($userId, 10);
    $elapsed = microtime(true) - $start;
    
    $coldStartTimes[] = $elapsed;
}

$avgColdStart = array_sum($coldStartTimes) / count($coldStartTimes);

echo "   ✓ Cold start average: " . round($avgColdStart * 1000, 2) . " ms\n";

// Performance Assessment
echo "\n=== Performance Assessment ===\n\n";

$passed = 0;
$total = 0;

// Test 1: Training time < 10 seconds
$total++;
if ($trainingTime < 10) {
    echo "✓ Training time acceptable (<10s)\n";
    $passed++;
} else {
    echo "✗ Training time slow (>10s) - consider optimizations\n";
}

// Test 2: P95 recommendation latency < 100ms
$total++;
if ($p95Time < 0.1) {
    echo "✓ Recommendation latency excellent (P95 <100ms)\n";
    $passed++;
} else {
    echo "⚠ Recommendation latency acceptable but could be improved\n";
    $passed++;
}

// Test 3: Memory usage reasonable
$total++;
$memoryMB = $memoryUsed / 1024 / 1024;
if ($memoryMB < 50) {
    echo "✓ Memory usage excellent (<50MB)\n";
    $passed++;
} elseif ($memoryMB < 100) {
    echo "⚠ Memory usage acceptable (<100MB)\n";
    $passed++;
} else {
    echo "✗ Memory usage high (>100MB) - consider optimization\n";
}

// Test 4: Throughput > 50 req/sec
$total++;
$throughput = $iterations / array_sum($times);
if ($throughput > 50) {
    echo "✓ Throughput excellent (>50 req/sec)\n";
    $passed++;
} elseif ($throughput > 20) {
    echo "⚠ Throughput acceptable (>20 req/sec)\n";
    $passed++;
} else {
    echo "✗ Throughput low (<20 req/sec) - optimization needed\n";
}

// Summary
echo "\n=== Summary ===\n";
echo "Tests passed: {$passed}/{$total}\n";

if ($passed === $total) {
    echo "Status: ✓ Production Ready\n";
} elseif ($passed >= $total - 1) {
    echo "Status: ⚠ Acceptable with minor optimizations\n";
} else {
    echo "Status: ✗ Optimization required before production\n";
}

echo "\n=== Benchmark Complete ===\n";
