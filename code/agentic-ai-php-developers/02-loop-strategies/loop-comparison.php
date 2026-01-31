<?php

/**
 * Loop Comparison Example - Side-by-Side Strategy Comparison
 * 
 * Runs the same task with all four loop strategies to demonstrate:
 * - Performance characteristics
 * - Token usage patterns
 * - Output quality differences
 * - Best use cases for each strategy
 */

declare(strict_types=1);

require_once __DIR__ . '/../../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudeAgents\Loops\StreamingLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define common tools for comparison
$searchWeb = Tool::create('search_web')
    ->description('Search the web for information')
    ->parameter('query', 'string', 'Search query')
    ->required('query')
    ->handler(function (array $input): string {
        usleep(500000); // 0.5s delay
        return json_encode([
            'query' => $input['query'],
            'results' => [
                'PHP 8.4 introduces property hooks and asymmetric visibility',
                'New array functions and performance improvements in PHP 8.4',
                'Release date: November 2024'
            ]
        ]);
    });

$summarizeText = Tool::create('summarize_text')
    ->description('Summarize a block of text')
    ->parameter('text', 'string', 'Text to summarize')
    ->required('text')
    ->handler(function (array $input): string {
        usleep(300000); // 0.3s delay
        $wordCount = str_word_count($input['text']);
        return json_encode([
            'summary' => 'Summary of ' . $wordCount . ' words of content',
            'key_points' => ['Point 1', 'Point 2', 'Point 3']
        ]);
    });

$formatOutput = Tool::create('format_output')
    ->description('Format text with markdown styling')
    ->parameter('content', 'string', 'Content to format')
    ->parameter('style', 'string', 'Formatting style')
    ->required(['content', 'style'])
    ->handler(function (array $input): string {
        usleep(200000); // 0.2s delay
        return json_encode([
            'formatted' => "**{$input['style']}**: {$input['content']}",
            'style_applied' => $input['style']
        ]);
    });

$tools = [$searchWeb, $summarizeText, $formatOutput];

// Common task for all loops
$task = "Research PHP 8.4 new features, summarize the key improvements, and format the response as a brief overview.";

// Storage for comparison results
$results = [];

echo str_repeat('=', 80) . "\n";
echo "Loop Strategy Comparison\n";
echo str_repeat('=', 80) . "\n";
echo "Task: {$task}\n\n";

// Test 1: ReactLoop
echo "\n" . str_repeat('-', 80) . "\n";
echo "1️⃣  ReactLoop (Default - Reason-Act-Observe)\n";
echo str_repeat('-', 80) . "\n";

try {
    $startTime = microtime(true);
    
    $agent = Agent::create($client)
        ->withSystemPrompt('You are a helpful research assistant.')
        ->withTools($tools)
        ->withLoopStrategy(new ReactLoop())
        ->maxIterations(8);
    
    $result = $agent->run($task);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    $results['ReactLoop'] = [
        'duration_ms' => $duration,
        'tool_calls' => count($result->getToolCalls()),
        'iterations' => $result->getIterations(),
        'answer' => $result->getAnswer(),
        'success' => true
    ];
    
    echo "✅ Completed in {$duration}ms\n";
    echo "   Tool calls: {$results['ReactLoop']['tool_calls']}\n";
    echo "   Iterations: {$results['ReactLoop']['iterations']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    $results['ReactLoop'] = ['success' => false, 'error' => $e->getMessage()];
}

// Test 2: PlanExecuteLoop
echo "\n" . str_repeat('-', 80) . "\n";
echo "2️⃣  PlanExecuteLoop (Plan First, Execute Systematically)\n";
echo str_repeat('-', 80) . "\n";

try {
    $startTime = microtime(true);
    
    $agent = Agent::create($client)
        ->withSystemPrompt('You are a helpful research assistant.')
        ->withTools($tools)
        ->withLoopStrategy(new PlanExecuteLoop(allowReplan: false))
        ->maxIterations(10);
    
    $result = $agent->run($task);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    $results['PlanExecuteLoop'] = [
        'duration_ms' => $duration,
        'tool_calls' => count($result->getToolCalls()),
        'steps_executed' => $result->getMetadata()['steps_executed'] ?? 0,
        'answer' => $result->getAnswer(),
        'success' => true
    ];
    
    echo "✅ Completed in {$duration}ms\n";
    echo "   Tool calls: {$results['PlanExecuteLoop']['tool_calls']}\n";
    echo "   Steps: {$results['PlanExecuteLoop']['steps_executed']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    $results['PlanExecuteLoop'] = ['success' => false, 'error' => $e->getMessage()];
}

// Test 3: ReflectionLoop
echo "\n" . str_repeat('-', 80) . "\n";
echo "3️⃣  ReflectionLoop (Self-Critique and Refinement)\n";
echo str_repeat('-', 80) . "\n";

try {
    $startTime = microtime(true);
    
    $agent = Agent::create($client)
        ->withSystemPrompt('You are a helpful research assistant focused on accuracy.')
        ->withTools($tools)
        ->withLoopStrategy(new ReflectionLoop(maxReflections: 2))
        ->maxIterations(12);
    
    $result = $agent->run($task);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    $results['ReflectionLoop'] = [
        'duration_ms' => $duration,
        'tool_calls' => count($result->getToolCalls()),
        'reflections' => $result->getMetadata()['reflections'] ?? 0,
        'answer' => $result->getAnswer(),
        'success' => true
    ];
    
    echo "✅ Completed in {$duration}ms\n";
    echo "   Tool calls: {$results['ReflectionLoop']['tool_calls']}\n";
    echo "   Reflections: {$results['ReflectionLoop']['reflections']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    $results['ReflectionLoop'] = ['success' => false, 'error' => $e->getMessage()];
}

// Test 4: StreamingLoop (wrapping ReactLoop for comparison)
echo "\n" . str_repeat('-', 80) . "\n";
echo "4️⃣  StreamingLoop (Real-Time Updates)\n";
echo str_repeat('-', 80) . "\n";

try {
    $startTime = microtime(true);
    $updateCount = 0;
    
    $agent = Agent::create($client)
        ->withSystemPrompt('You are a helpful research assistant.')
        ->withTools($tools)
        ->withLoopStrategy(new StreamingLoop())
        ->maxIterations(8)
        ->onUpdate(function ($update) use (&$updateCount) {
            $updateCount++;
            $data = $update->getData();
            $message = $data['message'] ?? $data['text'] ?? $update->getType();
            echo "  [{$update->getType()}] {$message}\n";
        });
    
    $result = $agent->run($task);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    $results['StreamingLoop'] = [
        'duration_ms' => $duration,
        'tool_calls' => count($result->getToolCalls()),
        'updates_emitted' => $updateCount,
        'answer' => $result->getAnswer(),
        'success' => true
    ];
    
    echo "\n✅ Completed in {$duration}ms\n";
    echo "   Tool calls: {$results['StreamingLoop']['tool_calls']}\n";
    echo "   Updates: {$results['StreamingLoop']['updates_emitted']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    $results['StreamingLoop'] = ['success' => false, 'error' => $e->getMessage()];
}

// Generate comparison table
echo "\n\n" . str_repeat('=', 80) . "\n";
echo "📊 Comparison Summary\n";
echo str_repeat('=', 80) . "\n\n";

echo "┌─────────────────────┬──────────────┬────────────┬─────────────┬─────────┐\n";
echo "│ Loop Strategy       │ Duration (ms)│ Tool Calls │ Special     │ Status  │\n";
echo "├─────────────────────┼──────────────┼────────────┼─────────────┼─────────┤\n";

foreach ($results as $name => $data) {
    if (!$data['success']) {
        printf(
            "│ %-19s │ %12s │ %10s │ %11s │ %7s │\n",
            $name,
            'N/A',
            'N/A',
            'N/A',
            'Failed'
        );
        continue;
    }
    
    $special = match ($name) {
        'ReactLoop' => "{$data['iterations']} iter",
        'PlanExecuteLoop' => "{$data['steps_executed']} steps",
        'ReflectionLoop' => "{$data['reflections']} reflect",
        'StreamingLoop' => "{$data['updates_emitted']} updates",
        default => 'N/A'
    };
    
    printf(
        "│ %-19s │ %12s │ %10s │ %11s │ %7s │\n",
        $name,
        number_format($data['duration_ms']),
        $data['tool_calls'],
        $special,
        'Success'
    );
}

echo "└─────────────────────┴──────────────┴────────────┴─────────────┴─────────┘\n";

// Calculate relative performance
if ($results['ReactLoop']['success'] ?? false) {
    $baseline = $results['ReactLoop']['duration_ms'];
    
    echo "\n📈 Performance Relative to ReactLoop (baseline):\n\n";
    
    foreach ($results as $name => $data) {
        if ($name === 'ReactLoop' || !$data['success']) continue;
        
        $ratio = round($data['duration_ms'] / $baseline, 2);
        $percent = round(($ratio - 1) * 100);
        $indicator = $ratio > 1 ? "↑" : "↓";
        $color = $ratio > 1 ? "slower" : "faster";
        
        echo "  {$name}: {$ratio}x ({$indicator} {$percent}% {$color})\n";
    }
}

// Recommendations
echo "\n\n" . str_repeat('=', 80) . "\n";
echo "💡 Recommendations Based on Results\n";
echo str_repeat('=', 80) . "\n\n";

echo "Use ReactLoop when:\n";
echo "  ✅ Task requires flexible, adaptive reasoning\n";
echo "  ✅ You need low latency (fastest option)\n";
echo "  ✅ Tool sequence isn't known upfront\n\n";

echo "Use PlanExecuteLoop when:\n";
echo "  ✅ Task has clear dependencies\n";
echo "  ✅ You want predictable execution order\n";
echo "  ✅ Upfront planning saves redundant tool calls\n\n";

echo "Use ReflectionLoop when:\n";
echo "  ✅ Output quality is critical\n";
echo "  ✅ Self-review can catch errors\n";
echo "  ✅ Higher latency is acceptable\n\n";

echo "Use StreamingLoop when:\n";
echo "  ✅ Task takes >5 seconds\n";
echo "  ✅ User needs progress feedback\n";
echo "  ✅ Better perceived performance matters\n\n";

echo "✅ Loop comparison completed\n";
