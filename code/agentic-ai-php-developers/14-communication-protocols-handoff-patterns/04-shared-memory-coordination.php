<?php

/**
 * Chapter 14: Communication Protocols and Handoff Patterns
 * Example 4: Shared Memory for Agent Coordination
 *
 * Demonstrates using SharedMemory as a blackboard system for indirect
 * agent communication and coordination.
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\{SharedMemory, SimpleCollaborativeAgent};
use ClaudePhp\ClaudePhp;

// Initialize
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$sharedMemory = new SharedMemory(['track_access' => true]);

echo "=== Shared Memory Coordination Demo ===\n\n";

// Create agents that will use shared memory
$dataAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'data_collector',
    capabilities: ['data_collection'],
    options: ['name' => 'Data Collector']
);

$analysisAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'analyst',
    capabilities: ['analysis'],
    options: ['name' => 'Analyst']
);

$reportAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'reporter',
    capabilities: ['reporting'],
    options: ['name' => 'Reporter']
);

// ============================================================================
// Example 1: Basic Read/Write Operations
// ============================================================================

echo "--- Example 1: Basic Shared Memory Operations ---\n\n";

// Data agent writes to shared memory
$sharedMemory->write(
    key: 'sales_data',
    value: [
        'q1' => 250000,
        'q2' => 280000,
        'q3' => 310000,
        'q4' => 350000,
    ],
    agentId: 'data_collector',
    metadata: ['source' => 'crm_database', 'updated' => '2024-12-01']
);

echo "Data Collector wrote sales data to shared memory\n";
echo "  Key: sales_data\n";
echo "  Data: Q1-Q4 revenue figures\n\n";

// Analyst reads from shared memory
$salesData = $sharedMemory->read('sales_data', 'analyst');

echo "Analyst read sales data:\n";
echo "  Q1: \${$salesData['q1']}\n";
echo "  Q2: \${$salesData['q2']}\n";
echo "  Q3: \${$salesData['q3']}\n";
echo "  Q4: \${$salesData['q4']}\n\n";

// Check metadata
$metadata = $sharedMemory->getMetadata('sales_data');
echo "Metadata:\n";
echo "  Written by: {$metadata['written_by']}\n";
echo "  Version: {$metadata['version']}\n";
echo "  Source: {$metadata['metadata']['source']}\n\n";

// ============================================================================
// Example 2: Multi-Agent Coordination
// ============================================================================

echo "--- Example 2: Multi-Agent Workflow ---\n\n";

// Agent 1: Data Collector gathers data
echo "Step 1: Data Collector gathers customer data\n";
$sharedMemory->write(
    'customer_count',
    4850,
    'data_collector'
);
$sharedMemory->write(
    'churn_rate',
    0.08,
    'data_collector'
);
echo "  • customer_count: 4850\n";
echo "  • churn_rate: 0.08\n\n";

// Agent 2: Analyst calculates metrics
echo "Step 2: Analyst calculates derived metrics\n";
$customerCount = $sharedMemory->read('customer_count', 'analyst');
$churnRate = $sharedMemory->read('churn_rate', 'analyst');

$expectedChurn = (int)($customerCount * $churnRate);
$retention = 1 - $churnRate;

$sharedMemory->write('expected_churn', $expectedChurn, 'analyst');
$sharedMemory->write('retention_rate', $retention, 'analyst');

echo "  • expected_churn: {$expectedChurn}\n";
echo "  • retention_rate: {$retention}\n\n";

// Agent 3: Reporter generates summary
echo "Step 3: Reporter compiles final report\n";
$report = [
    'total_customers' => $sharedMemory->read('customer_count', 'reporter'),
    'churn_rate' => $sharedMemory->read('churn_rate', 'reporter'),
    'expected_churn' => $sharedMemory->read('expected_churn', 'reporter'),
    'retention_rate' => $sharedMemory->read('retention_rate', 'reporter'),
];

$sharedMemory->write('final_report', $report, 'reporter');

echo "  Final report compiled and stored\n";
echo "  Keys accessed: customer_count, churn_rate, expected_churn, retention_rate\n\n";

// ============================================================================
// Example 3: Append and Increment Operations
// ============================================================================

echo "--- Example 3: Collaborative Data Building ---\n\n";

// Initialize task list
$sharedMemory->write('task_queue', [], 'data_collector');

// Multiple agents append tasks
$sharedMemory->append('task_queue', 'Analyze customer feedback', 'analyst');
$sharedMemory->append('task_queue', 'Generate monthly report', 'reporter');
$sharedMemory->append('task_queue', 'Update dashboard metrics', 'data_collector');

$tasks = $sharedMemory->read('task_queue', 'analyst');
echo "Collaborative task queue:\n";
foreach ($tasks as $i => $task) {
    echo "  " . ($i + 1) . ". {$task}\n";
}
echo "\n";

// Counter example
$sharedMemory->write('api_calls', 0, 'data_collector');

echo "API call counter:\n";
$count1 = $sharedMemory->increment('api_calls', 'data_collector');
echo "  After data collector: {$count1}\n";

$count2 = $sharedMemory->increment('api_calls', 'analyst');
echo "  After analyst: {$count2}\n";

$count3 = $sharedMemory->increment('api_calls', 'reporter');
echo "  After reporter: {$count3}\n\n";

// ============================================================================
// Example 4: Compare-and-Swap (Atomic Operations)
// ============================================================================

echo "--- Example 4: Atomic Compare-and-Swap ---\n\n";

$sharedMemory->write('task_status', 'pending', 'data_collector');

echo "Initial task status: pending\n\n";

// Agent tries to claim task
$claimed = $sharedMemory->compareAndSwap(
    key: 'task_status',
    expected: 'pending',
    new: 'in_progress',
    agentId: 'analyst'
);

echo "Analyst attempts to claim task:\n";
echo "  Success: " . ($claimed ? 'Yes' : 'No') . "\n";
echo "  New status: " . $sharedMemory->read('task_status', 'analyst') . "\n\n";

// Another agent tries to claim (should fail)
$claimed2 = $sharedMemory->compareAndSwap(
    key: 'task_status',
    expected: 'pending',
    new: 'in_progress',
    agentId: 'reporter'
);

echo "Reporter attempts to claim task:\n";
echo "  Success: " . ($claimed2 ? 'Yes' : 'No') . " (already claimed)\n";
echo "  Current status: " . $sharedMemory->read('task_status', 'reporter') . "\n\n";

// ============================================================================
// Example 5: Access Patterns and Statistics
// ============================================================================

echo "--- Example 5: Access Tracking ---\n\n";

$stats = $sharedMemory->getStatistics();

echo "Shared Memory Statistics:\n";
echo "  Total keys: {$stats['total_keys']}\n";
echo "  Total operations: {$stats['total_operations']}\n";
echo "  Reads: {$stats['reads']}\n";
echo "  Writes: {$stats['writes']}\n";
echo "  Unique agents: {$stats['unique_agents']}\n\n";

// Show access log
echo "Recent access log (last 5):\n";
$accessLog = array_slice($sharedMemory->getAccessLog(), -5);
foreach ($accessLog as $log) {
    echo "  • {$log['operation']} '{$log['key']}' by {$log['agent_id']}\n";
}
echo "\n";

// ============================================================================
// Example 6: Coordination Patterns
// ============================================================================

echo "--- Example 6: Coordination Patterns ---\n\n";

// Pattern 1: Work Queue
echo "Pattern 1: Work Queue\n";
$sharedMemory->write('work_queue', [
    ['id' => 1, 'task' => 'Process order #1001', 'status' => 'pending'],
    ['id' => 2, 'task' => 'Process order #1002', 'status' => 'pending'],
    ['id' => 3, 'task' => 'Process order #1003', 'status' => 'pending'],
], 'data_collector');

echo "  Work queue created with 3 tasks\n";
echo "  Agents can claim and process tasks atomically\n\n";

// Pattern 2: Producer-Consumer
echo "Pattern 2: Producer-Consumer\n";
$sharedMemory->write('event_stream', [], 'data_collector');

// Producer adds events
$sharedMemory->append('event_stream', ['type' => 'user_signup', 'time' => time()], 'data_collector');
$sharedMemory->append('event_stream', ['type' => 'purchase', 'time' => time()], 'data_collector');

echo "  Producer (data_collector) published 2 events\n";

// Consumer reads events
$events = $sharedMemory->read('event_stream', 'analyst');
echo "  Consumer (analyst) received " . count($events) . " events\n\n";

// Pattern 3: Flag-based Coordination
echo "Pattern 3: Flag-based Coordination\n";
$sharedMemory->write('all_agents_ready', false, 'data_collector');
$sharedMemory->write('agents_ready_count', 0, 'data_collector');

// Agents signal readiness
$sharedMemory->increment('agents_ready_count', 'analyst');
$sharedMemory->increment('agents_ready_count', 'reporter');
$sharedMemory->increment('agents_ready_count', 'data_collector');

$readyCount = $sharedMemory->read('agents_ready_count', 'data_collector');
if ($readyCount >= 3) {
    $sharedMemory->write('all_agents_ready', true, 'data_collector');
}

$allReady = $sharedMemory->read('all_agents_ready', 'analyst');
echo "  Ready agents: {$readyCount}/3\n";
echo "  All ready flag: " . ($allReady ? 'true' : 'false') . "\n\n";

// ============================================================================
// Example 7: State Export/Import
// ============================================================================

echo "--- Example 7: State Persistence ---\n\n";

// Export current state
$state = $sharedMemory->export();

echo "Exported shared memory state:\n";
echo "  Data keys: " . count($state['data']) . "\n";
echo "  Metadata entries: " . count($state['metadata']) . "\n";
echo "  Access log entries: " . count($state['access_log']) . "\n\n";

// Clear and reimport
$sharedMemory->clear();
echo "Shared memory cleared\n";
echo "  Keys remaining: " . count($sharedMemory->keys()) . "\n\n";

$sharedMemory->import($state);
echo "State reimported\n";
echo "  Keys restored: " . count($sharedMemory->keys()) . "\n\n";

// ============================================================================
// Example 8: All Keys and Batch Operations
// ============================================================================

echo "--- Example 8: Batch Operations ---\n\n";

echo "All keys in shared memory:\n";
foreach ($sharedMemory->keys() as $key) {
    $value = $sharedMemory->read($key, 'reporter');
    $meta = $sharedMemory->getMetadata($key);
    echo "  • {$key} (v{$meta['version']}, by {$meta['written_by']})\n";
}
echo "\n";

echo "=== Demo Complete ===\n\n";

echo "Shared Memory Benefits:\n";
echo "• Decouples agents (no direct dependencies)\n";
echo "• Enables async coordination\n";
echo "• Atomic operations prevent race conditions\n";
echo "• Tracks access patterns for debugging\n";
echo "• Supports multiple coordination patterns\n";
echo "• State can be persisted and restored\n";
