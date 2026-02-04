<?php

declare(strict_types=1);

/**
 * Chapter 19: Async & Concurrent Execution
 * Example 1: Basic Batch Processing
 * 
 * Demonstrates concurrent batch processing of multiple agent tasks using AMPHP.
 * 
 * Learn:
 * - Creating batch processors
 * - Adding tasks to batches
 * - Executing with concurrency control
 * - Collecting and analyzing results
 * - Performance comparison (sequential vs concurrent)
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Async\BatchProcessor;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp($apiKey);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         Async Batch Processing Demonstration                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// EXAMPLE 1: Basic Batch Processing
// ============================================================================

echo "Example 1: Basic Batch Processing\n";
echo str_repeat("─", 60) . "\n\n";

// Create an agent
$agent = Agent::create($client)
    ->withSystemPrompt("You are a helpful assistant. Provide concise, accurate answers.");

// Create batch processor
$processor = BatchProcessor::create($agent);

// Define tasks
$tasks = [
    'geography' => 'What is the capital of France?',
    'math' => 'Calculate 157 * 23',
    'science' => 'What is the speed of light in meters per second?',
    'history' => 'In what year did World War 2 end?',
    'astronomy' => 'What is the largest planet in our solar system?',
];

echo "Adding " . count($tasks) . " tasks to batch processor...\n\n";

// Add tasks
$processor->addMany($tasks);

// Execute with concurrency of 3
echo "Executing with concurrency: 3\n";
echo "(3 tasks will run simultaneously)\n\n";

$startTime = microtime(true);
$results = $processor->run(concurrency: 3);
$duration = microtime(true) - $startTime;

// Display results
echo "Results:\n";
echo str_repeat("─", 60) . "\n";

foreach ($results as $id => $result) {
    echo "\n[{$id}]\n";
    if ($result->isSuccess()) {
        echo "✓ " . $result->getAnswer() . "\n";
    } else {
        echo "✗ Error: " . $result->getError() . "\n";
    }
}

echo "\n" . str_repeat("─", 60) . "\n";
echo "Execution time: " . round($duration, 2) . " seconds\n";

// Show statistics
$stats = $processor->getStats();
echo "\nBatch Statistics:\n";
echo "─────────────────\n";
echo "Total tasks:     {$stats['total_tasks']}\n";
echo "Successful:      {$stats['successful']}\n";
echo "Failed:          {$stats['failed']}\n";
echo "Success rate:    " . round($stats['success_rate'] * 100, 1) . "%\n";
echo "Total tokens:    {$stats['total_tokens']['total']}\n";
echo "  - Input:       {$stats['total_tokens']['input']}\n";
echo "  - Output:      {$stats['total_tokens']['output']}\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 2: Performance Comparison (Sequential vs Concurrent)
// ============================================================================

echo "Example 2: Performance Comparison\n";
echo str_repeat("─", 60) . "\n\n";

// Small set of tasks for comparison
$comparisonTasks = [
    'task1' => 'Name three primary colors',
    'task2' => 'What is 10 + 25?',
    'task3' => 'What is the capital of Japan?',
    'task4' => 'Name a programming language',
];

// Sequential execution (concurrency: 1)
echo "Sequential execution (concurrency: 1):\n";
$processor->reset();
$processor->addMany($comparisonTasks);

$startSeq = microtime(true);
$resultsSeq = $processor->run(concurrency: 1);
$durationSeq = microtime(true) - $startSeq;

echo "✓ Completed in " . round($durationSeq, 2) . " seconds\n\n";

// Concurrent execution (concurrency: 4)
echo "Concurrent execution (concurrency: 4):\n";
$processor->reset();
$processor->addMany($comparisonTasks);

$startConc = microtime(true);
$resultsConc = $processor->run(concurrency: 4);
$durationConc = microtime(true) - $startConc;

echo "✓ Completed in " . round($durationConc, 2) . " seconds\n\n";

// Calculate speedup
$speedup = $durationSeq / $durationConc;
$improvement = (($durationSeq - $durationConc) / $durationSeq) * 100;

echo "Performance Improvement:\n";
echo "────────────────────────\n";
echo "Speedup:     {$speedup}x faster\n";
echo "Time saved:  " . round($improvement, 1) . "%\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 3: Handling Different Concurrency Levels
// ============================================================================

echo "Example 3: Testing Different Concurrency Levels\n";
echo str_repeat("─", 60) . "\n\n";

// Create larger task set
$largeBatch = [];
for ($i = 1; $i <= 12; $i++) {
    $largeBatch["task_{$i}"] = "What is {$i} times 2?";
}

$concurrencyLevels = [1, 3, 6, 12];

foreach ($concurrencyLevels as $level) {
    $processor->reset();
    $processor->addMany($largeBatch);
    
    $start = microtime(true);
    $results = $processor->run(concurrency: $level);
    $duration = microtime(true) - $start;
    
    $throughput = count($results) / $duration;
    
    echo "Concurrency {$level}:  ";
    echo round($duration, 2) . "s  ";
    echo "(" . round($throughput, 2) . " tasks/sec)\n";
}

echo "\n\n";

// ============================================================================
// EXAMPLE 4: Working with Results
// ============================================================================

echo "Example 4: Result Filtering and Analysis\n";
echo str_repeat("─", 60) . "\n\n";

// Create mixed tasks (some might fail)
$mixedTasks = [
    'valid1' => 'What is 2 + 2?',
    'valid2' => 'Name a fruit',
    'valid3' => 'What color is the sky?',
];

$processor->reset();
$processor->addMany($mixedTasks);
$results = $processor->run(concurrency: 3);

// Get successful results
$successful = $processor->getSuccessful();
echo "Successful tasks: " . count($successful) . "\n";
foreach ($successful as $id => $result) {
    echo "  ✓ {$id}: " . substr($result->getAnswer(), 0, 50) . "...\n";
}

echo "\n";

// Get failed results
$failed = $processor->getFailed();
if (count($failed) > 0) {
    echo "Failed tasks: " . count($failed) . "\n";
    foreach ($failed as $id => $result) {
        echo "  ✗ {$id}: " . $result->getError() . "\n";
    }
} else {
    echo "No failed tasks ✓\n";
}

echo "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    Key Takeaways                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ BatchProcessor enables concurrent execution of agent tasks\n";
echo "✓ Concurrency levels control how many tasks run simultaneously\n";
echo "✓ Higher concurrency = faster completion (with trade-offs)\n";
echo "✓ Statistics help monitor performance and success rates\n";
echo "✓ Results can be filtered (successful vs failed)\n";
echo "✓ Typical speedup: 2-5x depending on concurrency level\n\n";

echo "Next: 02-parallel-tool-execution.php\n";
