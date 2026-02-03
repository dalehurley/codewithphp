<?php

declare(strict_types=1);

/**
 * 03: Task Analysis and Matching
 *
 * This example demonstrates:
 * - How task analysis works
 * - The matching algorithm between tasks and agents
 * - Getting agent recommendations without execution
 *
 * Learn how the service analyzes tasks and scores agents.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\AdaptiveAgentService;
use ClaudeAgents\Agents\ReactAgent;
use ClaudeAgents\Agents\ReflectionAgent;
use ClaudeAgents\Agents\ChainOfThoughtAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

echo "=================================================================\n";
echo "Task Analysis and Agent Matching\n";
echo "=================================================================\n\n";

$client = new ClaudePhp(apiKey: $apiKey);

// =================================================================
// Setup Agents
// =================================================================

$calculatorTool = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->stringParam('expression', 'Mathematical expression')
    ->handler(function (array $input): string {
        try {
            $expr = preg_replace('/[^0-9+\-*\/().\s]/', '', $input['expression']);
            return (string) eval("return {$expr};");
        } catch (\Throwable $e) {
            return "Error: {$e->getMessage()}";
        }
    });

$reactAgent = new ReactAgent($client, ['tools' => [$calculatorTool]]);
$reflectionAgent = new ReflectionAgent($client);
$cotAgent = new ChainOfThoughtAgent($client);

// =================================================================
// Create Service
// =================================================================

$service = new AdaptiveAgentService($client, [
    'enable_knn' => true,
    'history_store_path' => __DIR__ . '/storage/analysis_history.json',
]);

$service->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'speed' => 'medium',
    'quality' => 'standard',
    'best_for' => ['calculations', 'tool usage'],
]);

$service->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'complexity_level' => 'medium',
    'speed' => 'slow',
    'quality' => 'high',
    'best_for' => ['writing', 'quality-critical'],
]);

$service->registerAgent('cot', $cotAgent, [
    'type' => 'cot',
    'complexity_level' => 'medium',
    'speed' => 'fast',
    'quality' => 'standard',
    'best_for' => ['reasoning', 'logic'],
]);

echo "Registered agents: " . implode(', ', $service->getRegisteredAgents()) . "\n\n";

// =================================================================
// Demonstration: Task Analysis and Recommendations
// =================================================================

$testTasks = [
    [
        'task' => 'Calculate the compound interest on $1000 at 5% for 3 years',
        'expected_complexity' => 'simple',
        'expected_agent' => 'react',
    ],
    [
        'task' => 'Write a comprehensive essay about climate change impacts',
        'expected_complexity' => 'medium',
        'expected_agent' => 'reflection',
    ],
    [
        'task' => 'If all dogs are animals, and all animals need food, do all dogs need food? Explain.',
        'expected_complexity' => 'simple',
        'expected_agent' => 'cot',
    ],
    [
        'task' => 'Design a complete microservices architecture for an e-commerce platform with scalability considerations',
        'expected_complexity' => 'complex',
        'expected_agent' => 'reflection',
    ],
];

foreach ($testTasks as $i => $testCase) {
    $num = $i + 1;
    echo "═════════════════════════════════════════════════════════════════\n";
    echo "Test Case {$num}\n";
    echo "═════════════════════════════════════════════════════════════════\n\n";

    echo "Task: {$testCase['task']}\n\n";

    // Get recommendation WITHOUT execution
    echo "Getting agent recommendation...\n\n";
    $recommendation = $service->recommendAgent($testCase['task']);

    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Recommendation:\n";
    echo "─────────────────────────────────────────────────────────────────\n\n";

    echo "Recommended Agent: {$recommendation['agent_id']}\n";
    echo "Confidence: " . round($recommendation['confidence'] * 100, 1) . "%\n";
    echo "Method: {$recommendation['method']}\n";
    echo "Reasoning: {$recommendation['reasoning']}\n\n";

    if (!empty($recommendation['alternatives'])) {
        echo "Alternatives:\n";
        foreach ($recommendation['alternatives'] as $alt) {
            echo "  - {$alt['agent_id']} (score: " . round($alt['score'], 2) . ")\n";
        }
        echo "\n";
    }

    // Show task analysis
    if (isset($recommendation['task_analysis'])) {
        $analysis = $recommendation['task_analysis'];

        echo "─────────────────────────────────────────────────────────────────\n";
        echo "Task Analysis:\n";
        echo "─────────────────────────────────────────────────────────────────\n\n";

        echo "Complexity: {$analysis['complexity']}\n";
        echo "Domain: {$analysis['domain']}\n";
        echo "Requires tools: " . ($analysis['requires_tools'] ? 'yes' : 'no') . "\n";
        echo "Requires quality: {$analysis['requires_quality']}\n";
        echo "Requires knowledge: " . ($analysis['requires_knowledge'] ? 'yes' : 'no') . "\n";
        echo "Requires reasoning: " . ($analysis['requires_reasoning'] ? 'yes' : 'no') . "\n";
        echo "Requires iteration: " . ($analysis['requires_iteration'] ? 'yes' : 'no') . "\n";
        echo "Estimated steps: {$analysis['estimated_steps']}\n";

        if (!empty($analysis['key_requirements'])) {
            echo "Key requirements: " . implode(', ', $analysis['key_requirements']) . "\n";
        }

        echo "\n";
    }

    // Validate expectations
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Validation:\n";
    echo "─────────────────────────────────────────────────────────────────\n\n";

    $complexityMatch = $recommendation['task_analysis']['complexity'] === $testCase['expected_complexity'];
    $agentMatch = $recommendation['agent_id'] === $testCase['expected_agent'];

    echo "Expected complexity: {$testCase['expected_complexity']}\n";
    echo "Actual complexity: {$recommendation['task_analysis']['complexity']}\n";
    echo "Match: " . ($complexityMatch ? '✓' : '✗') . "\n\n";

    echo "Expected agent: {$testCase['expected_agent']}\n";
    echo "Recommended agent: {$recommendation['agent_id']}\n";
    echo "Match: " . ($agentMatch ? '✓' : '✗') . "\n\n";
}

// =================================================================
// Demonstrate Learning Over Time
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Learning Demonstration\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

$calcTask = "Calculate 20% of 450";

// Get initial recommendation
echo "Initial recommendation (before execution history):\n\n";
$rec1 = $service->recommendAgent($calcTask);
echo "Agent: {$rec1['agent_id']}\n";
echo "Confidence: " . round($rec1['confidence'] * 100, 1) . "%\n";
echo "Method: {$rec1['method']}\n\n";

// Execute the task to build history
echo "Executing task to build history...\n\n";
$result = $service->run($calcTask);

if ($result->isSuccess()) {
    echo "✓ Executed successfully\n";
    echo "  Answer: {$result->getAnswer()}\n";
    echo "  Agent: {$result->getMetadata()['final_agent']}\n";
    echo "  Quality: {$result->getMetadata()['final_quality']}/10\n\n";
}

// Get recommendation again
echo "Recommendation after execution:\n\n";
$rec2 = $service->recommendAgent($calcTask);
echo "Agent: {$rec2['agent_id']}\n";
echo "Confidence: " . round($rec2['confidence'] * 100, 1) . "%\n";
echo "Method: {$rec2['method']}\n";
echo "Reasoning: {$rec2['reasoning']}\n\n";

// Show history stats
$historyStats = $service->getHistoryStats();
if ($historyStats['knn_enabled']) {
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Learning History:\n";
    echo "─────────────────────────────────────────────────────────────────\n\n";

    echo "Total tasks recorded: {$historyStats['total_records']}\n";
    echo "Unique agents used: {$historyStats['unique_agents']}\n";

    if (isset($historyStats['success_rate'])) {
        echo "Overall success rate: " . round($historyStats['success_rate'] * 100, 1) . "%\n";
    }

    if (isset($historyStats['avg_quality'])) {
        echo "Average quality: " . round($historyStats['avg_quality'], 1) . "/10\n";
    }

    echo "\n";
}

// =================================================================
// Key Takeaways
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Key Lessons:\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

echo "1. Task Analysis is Automatic\n";
echo "   - Service uses Claude to analyze task characteristics\n";
echo "   - Determines complexity, domain, requirements\n\n";

echo "2. Recommendation Without Execution\n";
echo "   - Use recommendAgent() to preview selection\n";
echo "   - See confidence scores and reasoning\n\n";

echo "3. Learning Improves Over Time\n";
echo "   - First execution uses rule-based selection\n";
echo "   - Subsequent similar tasks use k-NN (historical learning)\n";
echo "   - Confidence increases with more data\n\n";

echo "4. Confidence Levels\n";
echo "   - 50-60%: Rule-based (no history)\n";
echo "   - 70-85%: k-NN with some history\n";
echo "   - 85-95%: k-NN with strong history\n\n";
