<?php

declare(strict_types=1);

/**
 * Chapter 19: Async & Concurrent Execution
 * Example 3: Promise-Based Workflows
 * 
 * Demonstrates promise-based async patterns using AMPHP.
 * 
 * Learn:
 * - Creating promises for async operations
 * - Promise chaining with then/catch
 * - Waiting for multiple promises
 * - Racing promises
 * - Building complex async workflows
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Async\BatchProcessor;
use ClaudeAgents\Async\Promise;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp($apiKey);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║          Promise-Based Workflows Demonstration               ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// EXAMPLE 1: Basic Promise Usage
// ============================================================================

echo "Example 1: Basic Promise Usage\n";
echo str_repeat("─", 60) . "\n\n";

// Create agent
$agent = Agent::create($client)
    ->withSystemPrompt("You are a helpful assistant. Be concise.");

// Create batch processor
$processor = BatchProcessor::create($agent);

// Add tasks
$processor
    ->add('task1', 'What is 2 + 2?')
    ->add('task2', 'Name a color')
    ->add('task3', 'What is the capital of Italy?');

echo "Creating promises for async execution...\n\n";

// Get promises
$promises = $processor->runAsync();

echo "Received " . count($promises) . " promises\n";
echo "Promises represent future results that are being computed\n\n";

// Wait for all promises to resolve
echo "Waiting for all promises to complete...\n";

try {
    $results = Promise::all($promises);
    
    echo "\nAll promises resolved!\n";
    echo str_repeat("─", 60) . "\n";
    
    foreach ($results as $result) {
        if ($result->isSuccess()) {
            echo "✓ " . $result->getAnswer() . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "✗ Promise failed: {$e->getMessage()}\n";
}

echo "\n\n";

// ============================================================================
// EXAMPLE 2: Promise Callbacks (then/catch)
// ============================================================================

echo "Example 2: Promise Callbacks\n";
echo str_repeat("─", 60) . "\n\n";

$processor->reset();
$processor->add('callback_task', 'What is the smallest prime number?');

$promises = $processor->runAsync();
$promise = $promises['callback_task'];

echo "Adding callbacks to promise...\n\n";

// Add success callback
$promise->then(function ($result) {
    echo "✓ Promise succeeded!\n";
    echo "  Answer: {$result->getAnswer()}\n";
    return $result;
});

// Add error callback
$promise->catch(function ($error) {
    echo "✗ Promise failed!\n";
    echo "  Error: {$error->getMessage()}\n";
});

// Wait for completion
try {
    $result = $promise->wait();
    echo "\nPromise completed successfully\n";
} catch (\Throwable $e) {
    echo "\nPromise was rejected: {$e->getMessage()}\n";
}

echo "\n\n";

// ============================================================================
// EXAMPLE 3: Promise.all (Wait for All)
// ============================================================================

echo "Example 3: Promise.all - Wait for All\n";
echo str_repeat("─", 60) . "\n\n";

$processor->reset();
$processor
    ->add('math1', 'What is 15 * 8?')
    ->add('math2', 'What is 100 / 4?')
    ->add('math3', 'What is 50 + 75?');

echo "Starting 3 tasks asynchronously...\n\n";

$promises = $processor->runAsync();

echo "Waiting for ALL promises to complete (blocks until all done)...\n";

$startTime = microtime(true);

try {
    $allResults = Promise::all($promises);
    $duration = microtime(true) - $startTime;
    
    echo "\n✓ All " . count($allResults) . " promises completed!\n";
    echo "Total time: " . round($duration, 2) . " seconds\n\n";
    
    echo "Results:\n";
    foreach ($allResults as $result) {
        echo "  • " . $result->getAnswer() . "\n";
    }
} catch (\Throwable $e) {
    echo "✗ At least one promise failed: {$e->getMessage()}\n";
}

echo "\n\n";

// ============================================================================
// EXAMPLE 4: Promise.allSettled (Complete Regardless of Errors)
// ============================================================================

echo "Example 4: Promise.allSettled - Handle Partial Failures\n";
echo str_repeat("─", 60) . "\n\n";

$processor->reset();
$processor
    ->add('valid1', 'What is 5 * 5?')
    ->add('valid2', 'Name a fruit')
    ->add('valid3', 'What color is grass?');

$promises = $processor->runAsync();

echo "Using allSettled (doesn't throw on individual failures)...\n\n";

$settled = Promise::allSettled($promises);

$successCount = 0;
$errorCount = 0;

foreach ($settled as $key => $result) {
    if ($result instanceof \Throwable) {
        $errorCount++;
        echo "✗ {$key} failed: {$result->getMessage()}\n";
    } else {
        $successCount++;
        echo "✓ {$key} succeeded\n";
    }
}

echo "\nSummary: {$successCount} successful, {$errorCount} failed\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 5: Resolved and Rejected Promises
// ============================================================================

echo "Example 5: Creating Resolved/Rejected Promises\n";
echo str_repeat("─", 60) . "\n\n";

// Create already-resolved promise
$resolvedPromise = Promise::resolved("Immediate value");
echo "Resolved promise value: " . $resolvedPromise->getResult() . "\n\n";

// Create already-rejected promise
$rejectedPromise = Promise::rejected(new \Exception("Immediate error"));
try {
    $rejectedPromise->getResult();
} catch (\Throwable $e) {
    echo "Rejected promise error: {$e->getMessage()}\n";
}

echo "\n\n";

// ============================================================================
// EXAMPLE 6: Complex Workflow with Promise Composition
// ============================================================================

echo "Example 6: Complex Async Workflow\n";
echo str_repeat("─", 60) . "\n\n";

class AsyncWorkflow
{
    public function __construct(
        private ClaudePhp $client
    ) {}
    
    public function execute(): array
    {
        echo "Step 1: Fetch initial data (parallel)\n";
        
        $agent = Agent::create($this->client, systemPrompt: "Be concise.");
        $processor = BatchProcessor::create($agent);
        
        $processor
            ->add('data1', 'What is PHP?')
            ->add('data2', 'What is async programming?');
        
        $promises = $processor->runAsync();
        $initialData = Promise::all($promises);
        
        echo "✓ Initial data fetched\n\n";
        
        echo "Step 2: Process data (parallel)\n";
        
        $processor->reset();
        $processor
            ->add('processed1', 'Summarize PHP in 10 words')
            ->add('processed2', 'List 3 benefits of async programming');
        
        $promises = $processor->runAsync();
        $processedData = Promise::all($promises);
        
        echo "✓ Data processed\n\n";
        
        echo "Step 3: Final synthesis\n";
        
        $processor->reset();
        $processor->add('final', 'Combine concepts: PHP and async programming in 20 words');
        
        $promises = $processor->runAsync();
        $finalResult = Promise::all($promises);
        
        echo "✓ Synthesis complete\n\n";
        
        return [
            'initial' => $initialData,
            'processed' => $processedData,
            'final' => $finalResult,
        ];
    }
}

$workflow = new AsyncWorkflow($client);

echo "Executing multi-step async workflow...\n";
echo str_repeat("─", 60) . "\n\n";

$startTime = microtime(true);
$results = $workflow->execute();
$duration = microtime(true) - $startTime;

echo str_repeat("─", 60) . "\n";
echo "Workflow completed in " . round($duration, 2) . " seconds\n";
echo "\nFinal result:\n";
foreach ($results['final'] as $result) {
    echo "  " . $result->getAnswer() . "\n";
}

echo "\n\n";

// ============================================================================
// EXAMPLE 7: Promise Chaining
// ============================================================================

echo "Example 7: Promise Chaining Pattern\n";
echo str_repeat("─", 60) . "\n\n";

$processor->reset();
$processor->add('chain_start', 'What is 10 * 10?');

$promises = $processor->runAsync();
$promise = $promises['chain_start'];

echo "Creating promise chain...\n\n";

$chainedValue = null;

$promise
    ->then(function ($result) use (&$chainedValue) {
        echo "Step 1: Got initial result\n";
        $chainedValue = $result->getAnswer();
        return $result;
    })
    ->then(function ($result) use (&$chainedValue) {
        echo "Step 2: Processing result\n";
        echo "Step 3: Final value available\n";
        return $result;
    })
    ->catch(function ($error) {
        echo "Error in chain: {$error->getMessage()}\n";
    });

// Wait for chain to complete
$promise->wait();

echo "\nChain completed!\n";
echo "Final value: {$chainedValue}\n";

echo "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    Key Takeaways                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ Promises represent future values that are being computed\n";
echo "✓ then() adds success callbacks, catch() handles errors\n";
echo "✓ Promise::all() waits for all promises (fails if any fail)\n";
echo "✓ Promise::allSettled() completes regardless of errors\n";
echo "✓ Promises enable complex async workflow composition\n";
echo "✓ Promise chaining allows sequential async operations\n";
echo "✓ Great for coordinating multiple async tasks\n\n";

echo "Next: 04-agent-racing.php\n";
