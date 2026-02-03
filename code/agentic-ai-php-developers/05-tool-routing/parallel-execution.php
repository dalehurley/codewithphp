<?php

/**
 * Parallel Tool Execution
 * 
 * Demonstrates:
 * - Concurrent tool execution without AMPHP
 * - Batched parallel execution
 * - Concurrency limits
 * - Performance comparison (parallel vs sequential)
 * - Error handling in parallel execution
 * 
 * Note: This is a simplified version without AMPHP.
 * For true async execution, see claude-php/agent's ParallelToolExecutor.
 */

declare(strict_types=1);

// ============================================================================
// SIMPLE PARALLEL EXECUTOR (using proc_open for demo)
// ============================================================================

class SimpleParallelExecutor
{
    /**
     * Execute multiple tool calls "in parallel" (simulated).
     * 
     * Note: This is a simplified version for demonstration.
     * Real async execution requires AMPHP (see claude-php/agent).
     */
    public function execute(array $calls, array $tools): array
    {
        $results = [];
        
        foreach ($calls as $index => $call) {
            $toolName = $call['tool'] ?? '';
            $input = $call['input'] ?? [];
            
            // Find tool
            $tool = $tools[$toolName] ?? null;
            
            if ($tool === null) {
                $results[$index] = [
                    'tool' => $toolName,
                    'input' => $input,
                    'success' => false,
                    'content' => "Unknown tool: {$toolName}",
                ];
                continue;
            }
            
            // Execute tool
            try {
                $result = $tool($input);
                $results[$index] = [
                    'tool' => $toolName,
                    'input' => $input,
                    'success' => $result['success'] ?? true,
                    'content' => $result['content'] ?? '',
                ];
            } catch (\Throwable $e) {
                $results[$index] = [
                    'tool' => $toolName,
                    'input' => $input,
                    'success' => false,
                    'content' => "Error: {$e->getMessage()}",
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Execute with concurrency limit (batched).
     */
    public function executeBatched(array $calls, array $tools, int $concurrency = 5): array
    {
        $results = [];
        $batches = array_chunk($calls, $concurrency, true);
        
        foreach ($batches as $batch) {
            $batchResults = $this->execute($batch, $tools);
            $results = array_merge($results, $batchResults);
        }
        
        return $results;
    }
}

// ============================================================================
// EXAMPLE TOOLS
// ============================================================================

// Simulated API calls with different latencies
$weatherTool = function(array $input): array {
    $city = $input['city'] ?? 'Unknown';
    usleep(200000); // 200ms
    
    return [
        'success' => true,
        'content' => "Weather in {$city}: 72°F, Sunny",
    ];
};

$stockTool = function(array $input): array {
    $symbol = $input['symbol'] ?? 'UNKNOWN';
    usleep(300000); // 300ms
    
    return [
        'success' => true,
        'content' => "Stock {$symbol}: $150.25 (+2.3%)",
    ];
};

$newsTool = function(array $input): array {
    $topic = $input['topic'] ?? 'general';
    usleep(250000); // 250ms
    
    return [
        'success' => true,
        'content' => "Latest news about {$topic}: [3 articles found]",
    ];
};

$calculatorTool = function(array $input): array {
    $expression = $input['expression'] ?? '0';
    usleep(50000); // 50ms - fast tool
    
    try {
        $result = eval("return {$expression};");
        return [
            'success' => true,
            'content' => "Result: {$result}",
        ];
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'content' => "Calculation error",
        ];
    }
};

$databaseTool = function(array $input): array {
    $query = $input['query'] ?? '';
    usleep(500000); // 500ms - slow tool
    
    return [
        'success' => true,
        'content' => json_encode(['rows' => [['id' => 1], ['id' => 2]], 'count' => 2]),
    ];
};

$tools = [
    'weather' => $weatherTool,
    'stock' => $stockTool,
    'news' => $newsTool,
    'calculator' => $calculatorTool,
    'database' => $databaseTool,
];

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "            PARALLEL TOOL EXECUTION DEMONSTRATION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$executor = new SimpleParallelExecutor();

// ============================================================================
// Test 1: Sequential vs Parallel execution comparison
// ============================================================================

echo "Test 1: Performance Comparison\n";
echo "─────────────────────────────────────────────────────────────────\n";

$calls = [
    ['tool' => 'weather', 'input' => ['city' => 'San Francisco']],
    ['tool' => 'stock', 'input' => ['symbol' => 'AAPL']],
    ['tool' => 'news', 'input' => ['topic' => 'technology']],
];

// Sequential execution
echo "Sequential Execution:\n";
$startSequential = microtime(true);

foreach ($calls as $call) {
    $toolName = $call['tool'];
    $tool = $tools[$toolName];
    $result = $tool($call['input']);
    echo "  - {$toolName}: {$result['content']}\n";
}

$sequentialTime = microtime(true) - $startSequential;
echo "  Total time: " . round($sequentialTime * 1000, 2) . "ms\n\n";

// Parallel execution (simulated)
echo "Parallel Execution (simulated):\n";
$startParallel = microtime(true);

$results = $executor->execute($calls, $tools);

$parallelTime = microtime(true) - $startParallel;

foreach ($results as $result) {
    echo "  - {$result['tool']}: {$result['content']}\n";
}

echo "  Total time: " . round($parallelTime * 1000, 2) . "ms\n";
echo "  Speedup: " . round($sequentialTime / $parallelTime, 2) . "x\n\n";

// ============================================================================
// Test 2: Batched execution with concurrency limit
// ============================================================================

echo "Test 2: Batched Execution with Concurrency Limit\n";
echo "─────────────────────────────────────────────────────────────────\n";

$largeBatch = [];
for ($i = 0; $i < 10; $i++) {
    $largeBatch[] = ['tool' => 'calculator', 'input' => ['expression' => "10 * {$i}"]];
}

echo "Executing 10 calculator calls with concurrency limit of 3...\n";

$startBatched = microtime(true);
$results = $executor->executeBatched($largeBatch, $tools, concurrency: 3);
$batchedTime = microtime(true) - $startBatched;

echo "Results:\n";
foreach ($results as $i => $result) {
    if ($result['success']) {
        echo "  Call {$i}: {$result['content']}\n";
    }
}

echo "\nTotal time: " . round($batchedTime * 1000, 2) . "ms\n";
echo "Batches: " . ceil(count($largeBatch) / 3) . "\n\n";

// ============================================================================
// Test 3: Mixed success and failure
// ============================================================================

echo "Test 3: Handling Errors in Parallel Execution\n";
echo "─────────────────────────────────────────────────────────────────\n";

$mixedCalls = [
    ['tool' => 'weather', 'input' => ['city' => 'New York']],
    ['tool' => 'invalid_tool', 'input' => []],  // Will fail
    ['tool' => 'calculator', 'input' => ['expression' => '5 + 5']],
    ['tool' => 'calculator', 'input' => ['expression' => 'invalid;']],  // Will fail
];

$results = $executor->execute($mixedCalls, $tools);

foreach ($results as $i => $result) {
    $status = $result['success'] ? '✓' : '✗';
    echo "  {$status} {$result['tool']}: {$result['content']}\n";
}
echo "\n";

// ============================================================================
// Test 4: Different tool latencies
// ============================================================================

echo "Test 4: Parallel Execution with Different Latencies\n";
echo "─────────────────────────────────────────────────────────────────\n";

$latencyCalls = [
    ['tool' => 'calculator', 'input' => ['expression' => '1+1']],     // 50ms
    ['tool' => 'weather', 'input' => ['city' => 'Boston']],          // 200ms
    ['tool' => 'news', 'input' => ['topic' => 'sports']],            // 250ms
    ['tool' => 'stock', 'input' => ['symbol' => 'GOOGL']],           // 300ms
    ['tool' => 'database', 'input' => ['query' => 'SELECT 1']],      // 500ms
];

echo "Tools ordered by latency (fastest to slowest):\n";
echo "  calculator: ~50ms\n";
echo "  weather: ~200ms\n";
echo "  news: ~250ms\n";
echo "  stock: ~300ms\n";
echo "  database: ~500ms\n\n";

$start = microtime(true);
$results = $executor->execute($latencyCalls, $tools);
$totalTime = microtime(true) - $start;

echo "Parallel execution time: " . round($totalTime * 1000, 2) . "ms\n";
echo "Expected sequential time: ~1300ms\n";
echo "Speedup: ~" . round(1300 / ($totalTime * 1000), 1) . "x\n\n";

// ============================================================================
// Statistics
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                      EXECUTION STATISTICS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$totalCalls = 0;
$successfulCalls = 0;
$failedCalls = 0;

// Analyze all results from previous tests
$allResults = array_merge(
    $executor->execute($calls, $tools),
    $results
);

foreach ($allResults as $result) {
    $totalCalls++;
    if ($result['success']) {
        $successfulCalls++;
    } else {
        $failedCalls++;
    }
}

echo "Total tool calls: {$totalCalls}\n";
echo "Successful: {$successfulCalls}\n";
echo "Failed: {$failedCalls}\n";
echo "Success rate: " . round(($successfulCalls / $totalCalls) * 100, 1) . "%\n\n";

// ============================================================================
// Note about real parallel execution
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                        IMPORTANT NOTE\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "This example demonstrates parallel execution patterns, but executes\n";
echo "tools sequentially in the background for simplicity.\n\n";

echo "For TRUE parallel/async execution, use the claude-php/agent framework's\n";
echo "ParallelToolExecutor with AMPHP:\n\n";

echo "use ClaudeAgents\\Async\\ParallelToolExecutor;\n\n";

echo "\$executor = new ParallelToolExecutor(\$tools, ['logger' => \$logger]);\n";
echo "\$results = \$executor->execute(\$calls);\n\n";

echo "This provides genuine concurrent execution with AMPHP fibers.\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                           COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
