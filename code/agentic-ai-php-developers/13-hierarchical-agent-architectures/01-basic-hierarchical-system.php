#!/usr/bin/env php
<?php
/**
 * Basic Hierarchical System
 * 
 * Demonstrates the fundamentals of master-worker coordination with
 * two specialized agents: a math expert and a writing expert.
 * 
 * This example shows:
 * - Creating specialized worker agents
 * - Registering workers with a master agent
 * - Task decomposition and delegation
 * - Result synthesis
 * - Execution metadata tracking
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\HierarchicalAgent;
use ClaudeAgents\Agents\WorkerAgent;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "❌ Error: ANTHROPIC_API_KEY environment variable not set\n";
    echo "Set it with: export ANTHROPIC_API_KEY='your-key-here'\n";
    exit(1);
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║             Basic Hierarchical System Example                             ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Step 1: Create Specialized Worker Agents
// ============================================================================

echo "Step 1: Creating specialized workers...\n";

// Math specialist - handles calculations and numerical analysis
$mathWorker = new WorkerAgent($client, [
    'name' => 'math_expert',
    'specialty' => 'mathematical calculations, statistics, and numerical analysis',
    'system' => 'You are a mathematics expert. Provide precise calculations with clear explanations of your methodology.',
]);

echo "  ✓ Created math_expert\n";
echo "    Specialty: {$mathWorker->getSpecialty()}\n";

// Writing specialist - handles explanations and content creation
$writerWorker = new WorkerAgent($client, [
    'name' => 'writer_expert',
    'specialty' => 'clear and engaging writing and explanations',
    'system' => 'You are a professional writer. Create clear, engaging content that explains complex topics in simple terms.',
]);

echo "  ✓ Created writer_expert\n";
echo "    Specialty: {$writerWorker->getSpecialty()}\n\n";

// ============================================================================
// Step 2: Create the Master Agent
// ============================================================================

echo "Step 2: Creating master coordinator...\n";

$master = new HierarchicalAgent($client, [
    'name' => 'master_coordinator',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
]);

echo "  ✓ Created {$master->getName()}\n\n";

// ============================================================================
// Step 3: Register Workers with Master
// ============================================================================

echo "Step 3: Registering workers...\n";

$master->registerWorker('math_expert', $mathWorker);
$master->registerWorker('writer_expert', $writerWorker);

echo "  ✓ Registered workers:\n";
foreach ($master->getWorkerNames() as $name) {
    $worker = $master->getWorker($name);
    echo "    • {$name}: {$worker->getSpecialty()}\n";
}
echo "\n";

// ============================================================================
// Step 4: Execute a Multi-Domain Task
// ============================================================================

echo "Step 4: Executing complex task...\n\n";

$task = "Calculate the average of 45, 67, 89, and 123, then explain in simple terms what an average represents and why it's useful in everyday life.";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Task:\n{$task}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Processing (this will take ~15-20 seconds)...\n";
echo "  1. Master decomposes task into subtasks\n";
echo "  2. Workers execute their assigned subtasks\n";
echo "  3. Master synthesizes results\n\n";

$startTime = microtime(true);
$result = $master->run($task);
$duration = microtime(true) - $startTime;

// ============================================================================
// Step 5: Display Results
// ============================================================================

if ($result->isSuccess()) {
    echo "✅ SUCCESS!\n\n";
    
    echo "Final Answer:\n";
    echo str_repeat("-", 80) . "\n";
    echo $result->getAnswer() . "\n";
    echo str_repeat("-", 80) . "\n\n";
    
    // Display execution metadata
    $metadata = $result->getMetadata();
    
    echo "📊 Execution Statistics:\n";
    echo "  • Duration: " . round($duration, 2) . " seconds\n";
    echo "  • Iterations: {$result->getIterations()}\n";
    echo "  • Subtasks created: {$metadata['subtasks']}\n";
    echo "  • Workers used: " . implode(', ', $metadata['workers_used']) . "\n";
    
    echo "\n💰 Token Usage:\n";
    $usage = $result->getTokenUsage();
    echo "  • Input tokens: {$usage['input']}\n";
    echo "  • Output tokens: {$usage['output']}\n";
    echo "  • Total tokens: {$usage['total']}\n";
    
    // Estimate cost (Sonnet 4.5 pricing: $3 per 1M input, $15 per 1M output)
    $inputCost = $usage['input'] * 0.003 / 1000;
    $outputCost = $usage['output'] * 0.015 / 1000;
    $totalCost = $inputCost + $outputCost;
    
    echo "  • Estimated cost: $" . number_format($totalCost, 4) . "\n";
    
} else {
    echo "❌ ERROR: {$result->getError()}\n";
    exit(1);
}

// ============================================================================
// Understanding What Happened
// ============================================================================

echo "\n" . str_repeat("═", 80) . "\n";
echo "Understanding the Execution Flow:\n";
echo str_repeat("═", 80) . "\n\n";

echo "Phase 1 (Decomposition):\n";
echo "  The master agent analyzed the task and identified two subtasks:\n";
echo "  1. Math expert: Calculate the average of 45, 67, 89, and 123\n";
echo "  2. Writer expert: Explain what an average is and why it's useful\n\n";

echo "Phase 2 (Execution):\n";
echo "  Each worker independently processed their subtask:\n";
echo "  • Math expert performed the calculation and provided the result\n";
echo "  • Writer expert created an explanation of averages\n\n";

echo "Phase 3 (Synthesis):\n";
echo "  The master combined both outputs into a coherent final answer\n";
echo "  that addresses all aspects of the original task.\n\n";

echo "Key Benefits:\n";
echo "  ✓ Specialization: Each worker focuses on their domain of expertise\n";
echo "  ✓ Quality: Specialists produce better results than generalists\n";
echo "  ✓ Auditability: You can see which worker handled each part\n";
echo "  ✓ Flexibility: Easy to add new specialists for new domains\n\n";

echo "Next Steps:\n";
echo "  • Try modifying the task to require different types of expertise\n";
echo "  • Add more workers (research, data analysis, etc.)\n";
echo "  • Experiment with different specialty descriptions\n";
echo "  • Build a system for your specific use case\n";

echo "\n" . str_repeat("═", 80) . "\n";
echo "Example completed successfully!\n";
