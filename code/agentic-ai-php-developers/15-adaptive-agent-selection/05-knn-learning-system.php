<?php

declare(strict_types=1);

/**
 * 05: k-NN Learning System
 *
 * This example demonstrates:
 * - How k-NN (k-Nearest Neighbors) machine learning works
 * - Learning from execution history
 * - Improving selection confidence over time
 * - Adaptive quality thresholds
 *
 * Watch the service get smarter with each execution!
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
echo "k-NN Learning System Demonstration\n";
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
// Create Service with k-NN Enabled
// =================================================================

$historyPath = __DIR__ . '/storage/knn_learning_history.json';

// Clear previous history for clean demo
if (file_exists($historyPath)) {
    unlink($historyPath);
}

$service = new AdaptiveAgentService($client, [
    'max_attempts' => 3,
    'quality_threshold' => 7.0,
    'enable_reframing' => true,
    'enable_knn' => true,  // Enable k-NN learning
    'history_store_path' => $historyPath,
]);

$service->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'quality' => 'standard',
    'best_for' => ['calculations', 'tool usage'],
]);

$service->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'complexity_level' => 'medium',
    'quality' => 'high',
    'best_for' => ['writing', 'quality-critical'],
]);

$service->registerAgent('cot', $cotAgent, [
    'type' => 'cot',
    'complexity_level' => 'medium',
    'quality' => 'standard',
    'best_for' => ['reasoning', 'logic'],
]);

echo "✓ k-NN learning system enabled\n";
echo "✓ History will be stored in: {$historyPath}\n\n";

// =================================================================
// Phase 1: Cold Start (No History)
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Phase 1: Cold Start (No Historical Data)\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

$task1 = "Calculate 15% of 240";

echo "Task: {$task1}\n\n";

// Get initial recommendation
$rec1 = $service->recommendAgent($task1);
echo "Initial Recommendation:\n";
echo "  Agent: {$rec1['agent_id']}\n";
echo "  Confidence: " . round($rec1['confidence'] * 100, 1) . "%\n";
echo "  Method: {$rec1['method']}\n";
echo "  Reasoning: {$rec1['reasoning']}\n\n";

// Execute to build history
echo "Executing task...\n";
$result1 = $service->run($task1);

if ($result1->isSuccess()) {
    $metadata = $result1->getMetadata();
    echo "✓ Success! Answer: {$result1->getAnswer()}\n";
    echo "  Agent used: {$metadata['final_agent']}\n";
    echo "  Quality: {$metadata['final_quality']}/10\n\n";
}

// Check history stats
$stats = $service->getHistoryStats();
echo "History Stats:\n";
echo "  Total records: {$stats['total_records']}\n";
echo "  k-NN enabled: " . ($stats['knn_enabled'] ? 'yes' : 'no') . "\n\n";

// =================================================================
// Phase 2: Building Learning History
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Phase 2: Building Learning History\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

echo "Executing similar calculation tasks to build k-NN history...\n\n";

$calcTasks = [
    "Calculate 20% of 500",
    "Calculate 10% of 150",
    "What is 25% of 800?",
    "Find 30% of 300",
];

foreach ($calcTasks as $i => $task) {
    $num = $i + 1;
    echo "Task {$num}: {$task}\n";

    $result = $service->run($task);

    if ($result->isSuccess()) {
        $metadata = $result->getMetadata();
        echo "  ✓ Agent: {$metadata['final_agent']}, Quality: {$metadata['final_quality']}/10\n";

        // Check if k-NN was used
        if (isset($metadata['knn_enabled']) && $metadata['knn_enabled']) {
            echo "  → k-NN learning active\n";
        }
    }

    echo "\n";
}

// Check updated history
$stats = $service->getHistoryStats();
echo "Updated History Stats:\n";
echo "  Total records: {$stats['total_records']}\n";
echo "  Unique agents: {$stats['unique_agents']}\n";

if (isset($stats['success_rate'])) {
    echo "  Success rate: " . round($stats['success_rate'] * 100, 1) . "%\n";
}

if (isset($stats['avg_quality'])) {
    echo "  Average quality: " . round($stats['avg_quality'], 1) . "/10\n";
}

echo "\n";

// =================================================================
// Phase 3: k-NN in Action
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Phase 3: k-NN-Based Selection\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

$task2 = "Calculate 18% of 650";

echo "Task: {$task2}\n\n";

// Get recommendation with history
$rec2 = $service->recommendAgent($task2);
echo "Recommendation (with k-NN history):\n";
echo "  Agent: {$rec2['agent_id']}\n";
echo "  Confidence: " . round($rec2['confidence'] * 100, 1) . "%\n";
echo "  Method: {$rec2['method']}\n";
echo "  Reasoning: {$rec2['reasoning']}\n\n";

if (!empty($rec2['alternatives'])) {
    echo "Alternative agents:\n";
    foreach ($rec2['alternatives'] as $alt) {
        echo "  - {$alt['agent_id']}: " . round($alt['score'], 2) . " score\n";
    }
    echo "\n";
}

// Compare confidence growth
echo "Confidence Growth:\n";
echo "  Cold start: " . round($rec1['confidence'] * 100, 1) . "% ({$rec1['method']})\n";
echo "  With history: " . round($rec2['confidence'] * 100, 1) . "% ({$rec2['method']})\n";
echo "  Improvement: " . round(($rec2['confidence'] - $rec1['confidence']) * 100, 1) . " percentage points\n\n";

// =================================================================
// Phase 4: Different Task Type
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Phase 4: Different Task Type (Writing)\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

$writingTasks = [
    "Write a brief thank you email",
    "Compose a professional introduction",
    "Draft an apology for a delay",
];

echo "Building history for writing tasks...\n\n";

foreach ($writingTasks as $i => $task) {
    $num = $i + 1;
    echo "Task {$num}: {$task}\n";

    $result = $service->run($task);

    if ($result->isSuccess()) {
        $metadata = $result->getMetadata();
        echo "  ✓ Agent: {$metadata['final_agent']}, Quality: {$metadata['final_quality']}/10\n";
    }

    echo "\n";
}

// Final stats
$finalStats = $service->getHistoryStats();
echo "Final Learning Stats:\n";
echo "  Total tasks: {$finalStats['total_records']}\n";
echo "  Unique agents: {$finalStats['unique_agents']}\n";

if (isset($finalStats['success_rate'])) {
    echo "  Success rate: " . round($finalStats['success_rate'] * 100, 1) . "%\n";
}

if (isset($finalStats['avg_quality'])) {
    echo "  Average quality: " . round($finalStats['avg_quality'], 1) . "/10\n";
}

echo "\n";

// =================================================================
// Demonstrate Adaptive Threshold
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Adaptive Quality Threshold\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

echo "With k-NN enabled, the service adjusts quality thresholds based on\n";
echo "historical difficulty of similar tasks:\n\n";

echo "• Hard tasks (historically 6.5 avg) → threshold adjusted to ~6.0\n";
echo "• Easy tasks (historically 9.0 avg) → threshold adjusted to ~8.5\n\n";

echo "This prevents unrealistic expectations and reduces unnecessary retries.\n\n";

// =================================================================
// Key Takeaways
// =================================================================

echo "=================================================================\n";
echo "Key Lessons:\n";
echo "=================================================================\n\n";

echo "1. Learning Phases\n";
echo "   - Cold Start (0-5 tasks): Rule-based, 50% confidence\n";
echo "   - Learning (5-20 tasks): Mixed, 60-70% confidence\n";
echo "   - Mature (20-50 tasks): k-NN, 75-85% confidence\n";
echo "   - Expert (50+ tasks): k-NN, 85-95% confidence\n\n";

echo "2. How k-NN Works\n";
echo "   - Converts task to 14D feature vector\n";
echo "   - Finds k=10 most similar historical tasks\n";
echo "   - Selects agent that performed best on similar tasks\n\n";

echo "3. Benefits\n";
echo "   - Continuous improvement over time\n";
echo "   - Higher confidence with more data\n";
echo "   - Learns which agents excel at specific task types\n\n";

echo "4. Storage\n";
echo "   - History persists in JSON file (~1KB per task)\n";
echo "   - Survives application restarts\n";
echo "   - Can be backed up or transferred\n\n";
