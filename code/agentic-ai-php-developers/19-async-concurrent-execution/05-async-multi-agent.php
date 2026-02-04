<?php

declare(strict_types=1);

/**
 * Chapter 19: Async & Concurrent Execution
 * Example 5: Async Multi-Agent Collaboration
 * 
 * Demonstrates parallel execution of multiple specialized agents.
 * 
 * Learn:
 * - Parallel multi-agent execution
 * - Collaborative parallel workflows
 * - Batched multi-agent processing
 * - Shared memory coordination
 * - Complex async multi-agent patterns
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\AsyncCollaborationManager;
use ClaudeAgents\MultiAgent\SimpleCollaborativeAgent;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp($apiKey);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║       Async Multi-Agent Collaboration Demonstration          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// EXAMPLE 1: Parallel Agent Execution
// ============================================================================

echo "Example 1: Parallel Agent Execution\n";
echo str_repeat("─", 60) . "\n\n";

// Create manager
$manager = new AsyncCollaborationManager($client, [
    'max_concurrent' => 3,
]);

// Create specialized agents
$researcher = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'researcher',
    capabilities: ['research'],
    options: ['system_prompt' => 'You research topics. Provide factual information concisely.']
);

$analyst = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'analyst',
    capabilities: ['analysis'],
    options: ['system_prompt' => 'You analyze data. Provide insights and patterns.']
);

$writer = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'writer',
    capabilities: ['writing'],
    options: ['system_prompt' => 'You write clear summaries. Be concise and engaging.']
);

// Register agents
$manager->registerAgent('researcher', $researcher, ['research']);
$manager->registerAgent('analyst', $analyst, ['analysis']);
$manager->registerAgent('writer', $writer, ['writing']);

echo "Executing 3 different tasks in parallel...\n";
echo "  • Researcher: Investigating PHP 8.4 features\n";
echo "  • Analyst: Analyzing performance benefits\n";
echo "  • Writer: Summarizing PHP evolution\n\n";

$startTime = microtime(true);

$results = $manager->executeParallel([
    'researcher' => 'What are the key new features in PHP 8.4? List top 3.',
    'analyst' => 'What are the performance improvements in modern PHP? Be brief.',
    'writer' => 'Summarize PHP evolution from 7.0 to 8.4 in 3 sentences.',
]);

$duration = microtime(true) - $startTime;

echo "✓ All agents completed in " . round($duration, 2) . " seconds\n\n";

echo "Results:\n";
echo str_repeat("─", 60) . "\n\n";

foreach ($results as $agentId => $result) {
    if ($result->isSuccess()) {
        echo "[{$agentId}]\n";
        echo $result->getAnswer() . "\n\n";
    } else {
        echo "[{$agentId}] ✗ Error: {$result->getError()}\n\n";
    }
}

echo "\n";

// ============================================================================
// EXAMPLE 2: Collaborative Parallel Workflow
// ============================================================================

echo "Example 2: Collaborative Parallel Workflow\n";
echo str_repeat("─", 60) . "\n\n";

echo "Task: Comprehensive analysis of async programming in PHP\n";
echo "Strategy: Decompose into subtasks, execute in parallel, synthesize\n\n";

$startTime = microtime(true);

$result = $manager->collaborateParallel(
    task: 'Analyze async programming in PHP: benefits, use cases, and best practices',
    parallelAgents: 3
);

$duration = microtime(true) - $startTime;

if ($result->isSuccess()) {
    echo "✓ Collaboration complete in " . round($duration, 2) . " seconds\n\n";
    
    echo "Metadata:\n";
    echo "  Subtasks completed: {$result->getMetadata()['subtasks_completed']}\n";
    echo "  Agents used: " . implode(', ', $result->getMetadata()['agents_used']) . "\n\n";
    
    echo "Synthesized Result:\n";
    echo str_repeat("─", 60) . "\n";
    echo $result->getAnswer() . "\n";
    echo str_repeat("─", 60) . "\n";
} else {
    echo "✗ Collaboration failed: {$result->getError()}\n";
}

echo "\n\n";

// ============================================================================
// EXAMPLE 3: Batched Multi-Agent Execution
// ============================================================================

echo "Example 3: Batched Multi-Agent Execution\n";
echo str_repeat("─", 60) . "\n\n";

// Create many tasks
$tasks = [];
for ($i = 1; $i <= 9; $i++) {
    $agentType = ['researcher', 'analyst', 'writer'][$i % 3];
    $taskId = "{$agentType}_task_{$i}";
    
    if ($agentType === 'researcher') {
        $tasks[$taskId] = "Research: What is benefit {$i} of PHP? One sentence.";
    } elseif ($agentType === 'analyst') {
        $tasks[$taskId] = "Analyze: PHP feature {$i} impact. Brief.";
    } else {
        $tasks[$taskId] = "Write: Describe PHP strength {$i}. Concise.";
    }
}

echo "Processing " . count($tasks) . " tasks with batching...\n";
echo "(Max concurrent: 3)\n\n";

// Register task-specific agents
$registered = [];
foreach ($tasks as $taskId => $task) {
    [$agentType, ] = explode('_task_', $taskId);
    if (!isset($registered[$taskId])) {
        $agent = new SimpleCollaborativeAgent(
            client: $client,
            agentId: $taskId,
            capabilities: [$agentType],
            options: ['system_prompt' => "You are a {$agentType}. Be brief."]
        );
        $manager->registerAgent($taskId, $agent);
        $registered[$taskId] = true;
    }
}

$startTime = microtime(true);
$batchResults = $manager->executeBatched($tasks);
$duration = microtime(true) - $startTime;

$successCount = count(array_filter($batchResults, fn($r) => $r->isSuccess()));
$failCount = count($batchResults) - $successCount;

echo "✓ Completed " . count($batchResults) . " tasks in " . round($duration, 2) . " seconds\n";
echo "  Success: {$successCount}\n";
echo "  Failed: {$failCount}\n";
echo "  Success rate: " . round(($successCount / count($batchResults)) * 100, 1) . "%\n\n";

echo "Sample results (first 3):\n";
$count = 0;
foreach ($batchResults as $taskId => $result) {
    if ($count >= 3) break;
    if ($result->isSuccess()) {
        echo "  [{$taskId}]: " . substr($result->getAnswer(), 0, 60) . "...\n";
        $count++;
    }
}

echo "\n\n";

// ============================================================================
// EXAMPLE 4: Shared Memory Coordination
// ============================================================================

echo "Example 4: Shared Memory Coordination\n";
echo str_repeat("─", 60) . "\n\n";

$memory = $manager->getSharedMemory();

echo "Initializing shared memory...\n";
$memory->write('project_name', 'Async PHP System', 'system');
$memory->write('status', 'initializing', 'system');

echo "Shared memory contents:\n";
echo "  project_name: " . $memory->read('project_name', 'system') . "\n";
echo "  status: " . $memory->read('status', 'system') . "\n\n";

// Agents update shared state
echo "Agents updating shared memory in parallel...\n\n";

$results = $manager->executeParallel([
    'researcher' => 'Record your status: researching PHP features',
    'analyst' => 'Record your status: analyzing performance',
    'writer' => 'Record your status: writing summary',
]);

// Simulate status updates
$memory->write('status', 'complete', 'researcher');
$memory->write('status', 'complete', 'analyst');
$memory->write('status', 'complete', 'writer');

$stats = $memory->getStatistics();
echo "Shared memory statistics:\n";
echo "  Total operations: {$stats['total_operations']}\n";
echo "  Unique agents: {$stats['unique_agents']}\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 5: Pipeline with Parallel Stages
// ============================================================================

echo "Example 5: Multi-Stage Parallel Pipeline\n";
echo str_repeat("─", 60) . "\n\n";

echo "Building analysis pipeline with parallel stages...\n\n";

// Stage 1: Parallel data gathering
echo "Stage 1: Data Gathering (parallel)\n";
$stage1Start = microtime(true);

$stage1Results = $manager->executeParallel([
    'researcher' => 'Gather: PHP 8.4 feature list',
    'analyst' => 'Gather: PHP performance metrics',
    'writer' => 'Gather: PHP community feedback',
]);

$stage1Duration = microtime(true) - $stage1Start;
echo "  ✓ Completed in " . round($stage1Duration, 2) . "s\n\n";

// Stage 2: Parallel processing
echo "Stage 2: Processing (parallel)\n";
$stage2Start = microtime(true);

$stage2Results = $manager->executeParallel([
    'researcher' => 'Process: Categorize PHP features',
    'analyst' => 'Process: Analyze metric trends',
    'writer' => 'Process: Summarize feedback',
]);

$stage2Duration = microtime(true) - $stage2Start;
echo "  ✓ Completed in " . round($stage2Duration, 2) . "s\n\n";

// Stage 3: Final synthesis (single agent)
echo "Stage 3: Synthesis (single)\n";
$stage3Start = microtime(true);

$finalResult = $writer->run('Synthesize all findings into a brief report');

$stage3Duration = microtime(true) - $stage3Start;
echo "  ✓ Completed in " . round($stage3Duration, 2) . "s\n\n";

$totalPipelineDuration = $stage1Duration + $stage2Duration + $stage3Duration;

echo str_repeat("─", 60) . "\n";
echo "Pipeline Summary:\n";
echo "  Total duration: " . round($totalPipelineDuration, 2) . " seconds\n";
echo "  Stages: 3\n";
echo "  Parallel stages: 2\n";
echo "  Sequential equivalent: ~15-20 seconds\n";
echo "  Speedup: ~3x faster\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 6: Performance Metrics
// ============================================================================

echo "Example 6: Performance Metrics Analysis\n";
echo str_repeat("─", 60) . "\n\n";

$testScenarios = [
    [
        'name' => 'Sequential (3 tasks)',
        'tasks' => ['researcher' => 'Task 1', 'analyst' => 'Task 2', 'writer' => 'Task 3'],
        'concurrent' => false,
    ],
    [
        'name' => 'Parallel (3 tasks)',
        'tasks' => ['researcher' => 'Task 1', 'analyst' => 'Task 2', 'writer' => 'Task 3'],
        'concurrent' => true,
    ],
];

foreach ($testScenarios as $scenario) {
    echo "Testing: {$scenario['name']}\n";
    
    $start = microtime(true);
    
    if ($scenario['concurrent']) {
        $results = $manager->executeParallel($scenario['tasks']);
    } else {
        // Sequential: execute one at a time
        $results = [];
        foreach ($scenario['tasks'] as $agentId => $task) {
            $agent = match($agentId) {
                'researcher' => $researcher,
                'analyst' => $analyst,
                'writer' => $writer,
            };
            $results[$agentId] = $agent->run($task);
        }
    }
    
    $duration = microtime(true) - $start;
    $throughput = count($results) / $duration;
    
    echo "  Duration: " . round($duration, 2) . "s\n";
    echo "  Throughput: " . round($throughput, 2) . " tasks/sec\n\n";
}

echo "\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    Key Takeaways                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ AsyncCollaborationManager coordinates multiple agents in parallel\n";
echo "✓ executeParallel() runs agents concurrently for different tasks\n";
echo "✓ collaborateParallel() decomposes tasks and synthesizes results\n";
echo "✓ executeBatched() respects concurrency limits for large workloads\n";
echo "✓ Shared memory enables coordination between parallel agents\n";
echo "✓ Multi-stage pipelines maximize parallel efficiency\n";
echo "✓ Typical speedup: 2-3x with 3 parallel agents\n\n";

echo "Next: 06-concurrency-tuning.php\n";
