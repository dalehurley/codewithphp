<?php

declare(strict_types=1);

/**
 * 01: Basic Adaptive Agent Selection
 *
 * This example demonstrates the fundamental usage of AdaptiveAgentService:
 * - Automatic agent selection based on task analysis
 * - Quality validation of results
 * - Simple setup with multiple agents
 *
 * The service will analyze tasks and intelligently choose the best agent.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\AdaptiveAgentService;
use ClaudeAgents\Agents\ReactAgent;
use ClaudeAgents\Agents\ReflectionAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

echo "=================================================================\n";
echo "Basic Adaptive Agent Selection\n";
echo "=================================================================\n\n";

// Initialize Claude client
$client = new ClaudePhp(apiKey: $apiKey);

// =================================================================
// Setup: Create Specialized Agents
// =================================================================

echo "Creating specialized agents...\n\n";

// 1. React Agent - Good for tool-using tasks
$calculatorTool = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->stringParam('expression', 'Mathematical expression to evaluate (e.g., "25 * 17")')
    ->handler(function (array $input): string {
        try {
            $expr = $input['expression'];
            // Simple safe evaluation
            $expr = preg_replace('/[^0-9+\-*\/().\s]/', '', $expr);
            $result = eval("return {$expr};");
            return (string) $result;
        } catch (\Throwable $e) {
            return "Error: {$e->getMessage()}";
        }
    });

$reactAgent = new ReactAgent($client, [
    'tools' => [$calculatorTool],
    'max_iterations' => 5,
    'system' => 'You are a helpful assistant with access to calculation tools.',
]);

// 2. Reflection Agent - Good for quality-critical tasks
$reflectionAgent = new ReflectionAgent($client, [
    'max_refinements' => 2,
    'quality_threshold' => 7,
    'system' => 'You are a thoughtful assistant who produces high-quality outputs.',
]);

echo "✓ Created React agent (with calculator tool)\n";
echo "✓ Created Reflection agent (quality-focused)\n\n";

// =================================================================
// Create Adaptive Agent Service
// =================================================================

echo "Creating Adaptive Agent Service...\n\n";

$adaptiveService = new AdaptiveAgentService($client, [
    'max_attempts' => 3,                // Try up to 3 times
    'quality_threshold' => 7.0,         // Require 7/10 quality
    'enable_reframing' => true,         // Reframe on failure
    'enable_knn' => true,               // Enable learning
    'history_store_path' => __DIR__ . '/storage/adaptive_history.json',
]);

// Register React agent
$adaptiveService->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'strengths' => ['tool usage', 'calculations', 'iterative problem solving'],
    'best_for' => ['math problems', 'API calls', 'multi-step tasks'],
    'complexity_level' => 'medium',
    'speed' => 'medium',
    'quality' => 'standard',
]);

echo "✓ Registered 'react' agent\n";

// Register Reflection agent
$adaptiveService->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'strengths' => ['quality refinement', 'self-improvement', 'careful thinking'],
    'best_for' => ['writing', 'critical outputs', 'complex explanations'],
    'complexity_level' => 'medium',
    'speed' => 'slow',
    'quality' => 'high',
]);

echo "✓ Registered 'reflection' agent\n\n";

echo "Registered agents: " . implode(', ', $adaptiveService->getRegisteredAgents()) . "\n\n";

// =================================================================
// Example 1: Math Problem (Should select React agent)
// =================================================================

echo "─────────────────────────────────────────────────────────────────\n";
echo "Example 1: Mathematical Calculation\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

$task1 = "Calculate the result of (25 * 17) + 100";
echo "Task: {$task1}\n\n";

echo "Running adaptive service (analyzing task, selecting agent)...\n\n";

$result1 = $adaptiveService->run($task1);

if ($result1->isSuccess()) {
    echo "✓ SUCCESS\n\n";
    echo "Answer: {$result1->getAnswer()}\n\n";

    $metadata = $result1->getMetadata();
    echo "Selected Agent: {$metadata['final_agent']}\n";
    echo "Quality Score: {$metadata['final_quality']}/10\n";
    echo "Attempts: {$result1->getIterations()}\n";
    echo "Duration: {$metadata['total_duration']}s\n\n";

    if (!empty($metadata['task_analysis'])) {
        $analysis = $metadata['task_analysis'];
        echo "Task Analysis:\n";
        echo "  Complexity: {$analysis['complexity']}\n";
        echo "  Domain: {$analysis['domain']}\n";
        echo "  Requires tools: " . ($analysis['requires_tools'] ? 'yes' : 'no') . "\n\n";
    }
} else {
    echo "✗ FAILED: {$result1->getError()}\n\n";
}

// =================================================================
// Example 2: Writing Task (Should select Reflection agent)
// =================================================================

echo "─────────────────────────────────────────────────────────────────\n";
echo "Example 2: Quality-Critical Writing Task\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

$task2 = "Write a professional email apologizing for a project delay and proposing next steps.";
echo "Task: {$task2}\n\n";

echo "Running adaptive service...\n\n";

$result2 = $adaptiveService->run($task2);

if ($result2->isSuccess()) {
    echo "✓ SUCCESS\n\n";
    echo "Answer (first 250 chars):\n";
    echo substr($result2->getAnswer(), 0, 250) . "...\n\n";

    $metadata = $result2->getMetadata();
    echo "Selected Agent: {$metadata['final_agent']}\n";
    echo "Quality Score: {$metadata['final_quality']}/10\n";
    echo "Attempts: {$result2->getIterations()}\n\n";
} else {
    echo "✗ FAILED: {$result2->getError()}\n\n";
}

// =================================================================
// Example 3: Check Performance Metrics
// =================================================================

echo "─────────────────────────────────────────────────────────────────\n";
echo "Performance Summary\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

$performance = $adaptiveService->getPerformance();

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 0) {
        $successRate = round(($stats['successes'] / $stats['attempts']) * 100, 1);
        $avgQuality = round($stats['average_quality'], 1);
        $avgDuration = round($stats['total_duration'] / $stats['attempts'], 2);

        echo "{$agentId} Agent:\n";
        echo "  Attempts: {$stats['attempts']}\n";
        echo "  Success Rate: {$successRate}%\n";
        echo "  Average Quality: {$avgQuality}/10\n";
        echo "  Average Duration: {$avgDuration}s\n\n";
    }
}

// =================================================================
// Key Takeaways
// =================================================================

echo "=================================================================\n";
echo "Key Features Demonstrated:\n";
echo "=================================================================\n\n";

echo "✓ Automatic agent selection based on task requirements\n";
echo "✓ Quality validation of results (0-10 scoring)\n";
echo "✓ Different agents chosen for different task types\n";
echo "✓ Performance tracking per agent\n";
echo "✓ Task analysis (complexity, domain, requirements)\n\n";

echo "The service intelligently matched:\n";
echo "  • Math task → React agent (has calculator tool)\n";
echo "  • Writing task → Reflection agent (high quality output)\n\n";
