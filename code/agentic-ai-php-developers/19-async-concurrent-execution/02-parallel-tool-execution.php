<?php

declare(strict_types=1);

/**
 * Chapter 19: Async & Concurrent Execution
 * Example 2: Parallel Tool Execution
 * 
 * Demonstrates executing multiple tools in parallel using ParallelToolExecutor.
 * 
 * Learn:
 * - Creating tools for parallel execution
 * - Using ParallelToolExecutor
 * - Batched tool execution with concurrency limits
 * - Performance benefits of parallel tool calls
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Async\ParallelToolExecutor;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║        Parallel Tool Execution Demonstration                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Define Example Tools
// ============================================================================

echo "Setting up example tools...\n\n";

// Tool 1: Weather (simulates API call with delay)
$weatherTool = Tool::create('get_weather')
    ->description('Get weather information for a city')
    ->parameter('city', 'string', 'City name')
    ->required('city')
    ->handler(function (array $input): ToolResult {
        // Simulate API call delay
        usleep(500000); // 500ms
        
        $city = $input['city'] ?? 'Unknown';
        $temperatures = [15, 18, 22, 25, 28, 20, 17];
        $conditions = ['sunny', 'cloudy', 'rainy', 'partly cloudy'];
        
        return ToolResult::success([
            'city' => $city,
            'temperature' => $temperatures[array_rand($temperatures)],
            'condition' => $conditions[array_rand($conditions)],
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    });

// Tool 2: Time Zone
$timeTool = Tool::create('get_time')
    ->description('Get current time in a timezone')
    ->parameter('timezone', 'string', 'Timezone name')
    ->required('timezone')
    ->handler(function (array $input): ToolResult {
        usleep(300000); // 300ms
        
        $tz = $input['timezone'] ?? 'UTC';
        $time = new DateTime('now', new DateTimeZone($tz));
        
        return ToolResult::success([
            'timezone' => $tz,
            'time' => $time->format('H:i:s'),
            'date' => $time->format('Y-m-d'),
        ]);
    });

// Tool 3: Calculator
$calculatorTool = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression')
    ->required('expression')
    ->handler(function (array $input): ToolResult {
        usleep(200000); // 200ms
        
        $expr = $input['expression'] ?? '0';
        
        try {
            // Safe evaluation (only allow numbers and basic operators)
            if (!preg_match('/^[0-9+\-*\/\(\)\s\.]+$/', $expr)) {
                return ToolResult::error('Invalid expression');
            }
            
            $result = eval("return {$expr};");
            
            return ToolResult::success([
                'expression' => $expr,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error($e->getMessage());
        }
    });

// Tool 4: Currency Converter
$currencyTool = Tool::create('convert_currency')
    ->description('Convert between currencies')
    ->parameter('amount', 'number', 'Amount to convert')
    ->parameter('from', 'string', 'Source currency')
    ->parameter('to', 'string', 'Target currency')
    ->required('amount')
    ->required('from')
    ->required('to')
    ->handler(function (array $input): ToolResult {
        usleep(400000); // 400ms
        
        // Simplified conversion rates
        $rates = [
            'USD' => 1.0,
            'EUR' => 0.92,
            'GBP' => 0.79,
            'JPY' => 149.50,
        ];
        
        $amount = $input['amount'];
        $from = strtoupper($input['from']);
        $to = strtoupper($input['to']);
        
        if (!isset($rates[$from]) || !isset($rates[$to])) {
            return ToolResult::error('Unsupported currency');
        }
        
        // Convert to USD, then to target
        $usdAmount = $amount / $rates[$from];
        $converted = $usdAmount * $rates[$to];
        
        return ToolResult::success([
            'from' => $from,
            'to' => $to,
            'original_amount' => $amount,
            'converted_amount' => round($converted, 2),
            'rate' => round($rates[$to] / $rates[$from], 4),
        ]);
    });

$tools = [$weatherTool, $timeTool, $calculatorTool, $currencyTool];

// ============================================================================
// EXAMPLE 1: Basic Parallel Execution
// ============================================================================

echo "Example 1: Basic Parallel Tool Execution\n";
echo str_repeat("─", 60) . "\n\n";

$executor = new ParallelToolExecutor($tools);

$toolCalls = [
    ['tool' => 'get_weather', 'input' => ['city' => 'London']],
    ['tool' => 'get_weather', 'input' => ['city' => 'Paris']],
    ['tool' => 'get_time', 'input' => ['timezone' => 'America/New_York']],
    ['tool' => 'calculate', 'input' => ['expression' => '42 * 8']],
];

echo "Executing " . count($toolCalls) . " tool calls in parallel...\n\n";

$startTime = microtime(true);
$results = $executor->execute($toolCalls);
$duration = microtime(true) - $startTime;

echo "Results:\n";
echo str_repeat("─", 60) . "\n";

foreach ($results as $i => $result) {
    echo "\n[Call " . ($i + 1) . "] {$result['tool']}\n";
    
    if ($result['result']->isSuccess()) {
        echo "✓ " . $result['result']->getContent() . "\n";
    } else {
        echo "✗ Error: " . $result['result']->getContent() . "\n";
    }
}

echo "\n" . str_repeat("─", 60) . "\n";
echo "Execution time: " . round($duration, 2) . " seconds\n";
echo "(Sequential would take ~1.4s, parallel execution is faster!)\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 2: Sequential vs Parallel Comparison
// ============================================================================

echo "Example 2: Performance Comparison\n";
echo str_repeat("─", 60) . "\n\n";

$testCalls = [
    ['tool' => 'calculate', 'input' => ['expression' => '10 + 5']],
    ['tool' => 'calculate', 'input' => ['expression' => '20 * 3']],
    ['tool' => 'calculate', 'input' => ['expression' => '100 - 25']],
    ['tool' => 'calculate', 'input' => ['expression' => '50 / 2']],
];

// Sequential (concurrency: 1)
echo "Sequential execution:\n";
$startSeq = microtime(true);
$resultsSeq = $executor->executeBatched($testCalls, concurrency: 1);
$durationSeq = microtime(true) - $startSeq;
echo "Time: " . round($durationSeq, 2) . "s\n\n";

// Parallel (all at once)
echo "Parallel execution:\n";
$startPar = microtime(true);
$resultsPar = $executor->execute($testCalls);
$durationPar = microtime(true) - $startPar;
echo "Time: " . round($durationPar, 2) . "s\n\n";

$speedup = $durationSeq / $durationPar;
echo "Speedup: {$speedup}x faster with parallel execution\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 3: Batched Execution with Concurrency Limit
// ============================================================================

echo "Example 3: Batched Tool Execution\n";
echo str_repeat("─", 60) . "\n\n";

// Create large batch of calculations
$largeBatch = [];
for ($i = 1; $i <= 12; $i++) {
    $largeBatch[] = [
        'tool' => 'calculate',
        'input' => ['expression' => "{$i} * 2"],
    ];
}

echo "Executing 12 calculations with concurrency limit of 4\n";
echo "(Will process in 3 batches of 4)\n\n";

$startTime = microtime(true);
$batchedResults = $executor->executeBatched($largeBatch, concurrency: 4);
$duration = microtime(true) - $startTime;

echo "Completed " . count($batchedResults) . " tool calls\n";
echo "Total time: " . round($duration, 2) . " seconds\n";

// Display sample results
echo "\nSample results:\n";
for ($i = 0; $i < 3; $i++) {
    $result = $batchedResults[$i];
    $data = json_decode($result['result']->getContent(), true);
    echo "  {$data['expression']} = {$data['result']}\n";
}
echo "  ...\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 4: Mixed Tool Types in Parallel
// ============================================================================

echo "Example 4: Mixed Tool Types\n";
echo str_repeat("─", 60) . "\n\n";

$mixedCalls = [
    ['tool' => 'get_weather', 'input' => ['city' => 'Tokyo']],
    ['tool' => 'get_time', 'input' => ['timezone' => 'Asia/Tokyo']],
    ['tool' => 'calculate', 'input' => ['expression' => '365 * 24']],
    ['tool' => 'convert_currency', 'input' => ['amount' => 100, 'from' => 'USD', 'to' => 'EUR']],
];

echo "Executing " . count($mixedCalls) . " different tool types in parallel...\n\n";

$startTime = microtime(true);
$mixedResults = $executor->execute($mixedCalls);
$duration = microtime(true) - $startTime;

foreach ($mixedResults as $result) {
    echo "[{$result['tool']}]\n";
    if ($result['result']->isSuccess()) {
        $data = json_decode($result['result']->getContent(), true);
        echo "  ✓ ";
        
        if ($result['tool'] === 'get_weather') {
            echo "{$data['city']}: {$data['temperature']}°C, {$data['condition']}\n";
        } elseif ($result['tool'] === 'get_time') {
            echo "{$data['timezone']}: {$data['time']}\n";
        } elseif ($result['tool'] === 'calculate') {
            echo "{$data['expression']} = {$data['result']}\n";
        } elseif ($result['tool'] === 'convert_currency') {
            echo "{$data['original_amount']} {$data['from']} = {$data['converted_amount']} {$data['to']}\n";
        }
    }
}

echo "\nCompleted in: " . round($duration, 2) . " seconds\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 5: Error Handling
// ============================================================================

echo "Example 5: Error Handling in Parallel Execution\n";
echo str_repeat("─", 60) . "\n\n";

$callsWithError = [
    ['tool' => 'calculate', 'input' => ['expression' => '10 + 5']],
    ['tool' => 'invalid_tool', 'input' => []],
    ['tool' => 'calculate', 'input' => ['expression' => '20 * 2']],
];

echo "Executing with one invalid tool call...\n\n";

$results = $executor->execute($callsWithError);

$successCount = 0;
$errorCount = 0;

foreach ($results as $result) {
    if ($result['result']->isSuccess()) {
        $successCount++;
        echo "✓ {$result['tool']} succeeded\n";
    } else {
        $errorCount++;
        echo "✗ {$result['tool']} failed: {$result['result']->getContent()}\n";
    }
}

echo "\nSummary: {$successCount} successful, {$errorCount} failed\n";
echo "(Errors don't stop other tasks from completing)\n";

echo "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    Key Takeaways                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ ParallelToolExecutor runs multiple tools concurrently\n";
echo "✓ Eliminates sequential bottlenecks in tool execution\n";
echo "✓ Concurrency limits prevent overwhelming external APIs\n";
echo "✓ Errors in one tool don't stop others from completing\n";
echo "✓ Typical speedup: 2-4x depending on tool count and delays\n";
echo "✓ Best for: Independent tool calls with I/O operations\n\n";

echo "Next: 03-promise-workflows.php\n";
