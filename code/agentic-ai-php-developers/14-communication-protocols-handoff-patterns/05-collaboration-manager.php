<?php

/**
 * Chapter 14: Communication Protocols and Handoff Patterns
 * Example 5: CollaborationManager for Multi-Agent Orchestration
 *
 * Demonstrates using CollaborationManager to orchestrate multiple agents
 * with automatic message routing and protocol enforcement.
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\{CollaborationManager, Protocol, SharedMemory, SimpleCollaborativeAgent};
use ClaudePhp\ClaudePhp;

// Initialize
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

echo "=== CollaborationManager Orchestration Demo ===\n\n";

// ============================================================================
// Example 1: Basic Setup
// ============================================================================

echo "--- Example 1: Basic Collaboration Setup ---\n\n";

$sharedMemory = new SharedMemory();
$manager = new CollaborationManager($client, [
    'max_rounds' => 10,
    'protocol' => Protocol::requestResponse(),
    'shared_memory' => $sharedMemory,
    'enable_message_passing' => true,
]);

echo "CollaborationManager created:\n";
echo "  Max rounds: 10\n";
echo "  Protocol: request-response\n";
echo "  Message passing: enabled\n";
echo "  Shared memory: available\n\n";

// ============================================================================
// Example 2: Registering Agents
// ============================================================================

echo "--- Example 2: Agent Registration ---\n\n";

$researcher = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'researcher',
    capabilities: ['research', 'fact-checking', 'data-gathering'],
    options: [
        'name' => 'Research Specialist',
        'system_prompt' => 'You are a research specialist. Gather accurate information and provide well-sourced facts.',
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
    ]
);

$analyst = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'analyst',
    capabilities: ['analysis', 'statistics', 'insights'],
    options: [
        'name' => 'Data Analyst',
        'system_prompt' => 'You are a data analyst. Analyze information and extract actionable insights.',
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
    ]
);

$writer = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'writer',
    capabilities: ['writing', 'communication', 'storytelling'],
    options: [
        'name' => 'Content Writer',
        'system_prompt' => 'You are a professional writer. Create clear, engaging content that communicates insights effectively.',
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
    ]
);

// Register agents with manager
$manager->registerAgent('researcher', $researcher, ['research', 'fact-checking']);
$manager->registerAgent('analyst', $analyst, ['analysis', 'statistics']);
$manager->registerAgent('writer', $writer, ['writing', 'communication']);

echo "Agents registered:\n";
echo "  1. researcher: research, fact-checking\n";
echo "  2. analyst: analysis, statistics\n";
echo "  3. writer: writing, communication\n\n";

// ============================================================================
// Example 3: Simple Collaboration
// ============================================================================

echo "--- Example 3: Multi-Agent Collaboration ---\n\n";

$task = "Analyze the impact of remote work on employee productivity in tech companies. " .
        "Provide research-backed insights and write a clear executive summary.";

echo "Task: {$task}\n\n";
echo "Starting collaboration...\n\n";

$result = $manager->collaborate($task);

if ($result->isSuccess()) {
    echo "✓ Collaboration completed successfully\n\n";
    
    $metadata = $result->getMetadata();
    echo "Execution metrics:\n";
    echo "  • Rounds: {$metadata['rounds']}\n";
    echo "  • Agents involved: " . implode(', ', $metadata['agents_involved']) . "\n";
    echo "  • Conversation length: {$metadata['conversation_length']} exchanges\n\n";
    
    echo "Final result:\n";
    echo str_repeat("─", 80) . "\n";
    echo wordwrap($result->getAnswer(), 80) . "\n";
    echo str_repeat("─", 80) . "\n\n";
} else {
    echo "✗ Collaboration failed: {$result->getError()}\n\n";
}

// ============================================================================
// Example 4: Conversation History
// ============================================================================

echo "--- Example 4: Conversation History ---\n\n";

$history = $manager->getConversationHistory();

echo "Conversation flow (" . count($history) . " exchanges):\n\n";
foreach ($history as $entry) {
    echo "Round {$entry['round']} - {$entry['agent']}:\n";
    echo "  Task: " . substr($entry['task'], 0, 60) . "...\n";
    echo "  Result: " . substr($entry['result'], 0, 100) . "...\n";
    echo "  Timestamp: " . date('H:i:s', (int)$entry['timestamp']) . "\n\n";
}

// ============================================================================
// Example 5: Shared Memory Integration
// ============================================================================

echo "--- Example 5: Shared Memory Access ---\n\n";

$sharedMemory = $manager->getSharedMemory();

echo "Shared memory state:\n";
$stats = $sharedMemory->getStatistics();
echo "  Keys stored: {$stats['total_keys']}\n";
echo "  Total operations: {$stats['total_operations']}\n";
echo "  Read operations: {$stats['reads']}\n";
echo "  Write operations: {$stats['writes']}\n";
echo "  Unique agents: {$stats['unique_agents']}\n\n";

if ($stats['total_keys'] > 0) {
    echo "Data in shared memory:\n";
    foreach ($sharedMemory->keys() as $key) {
        $meta = $sharedMemory->getMetadata($key);
        echo "  • {$key} (by {$meta['written_by']})\n";
    }
    echo "\n";
}

// ============================================================================
// Example 6: Manager Metrics
// ============================================================================

echo "--- Example 6: Manager Metrics ---\n\n";

$metrics = $manager->getMetrics();

echo "CollaborationManager metrics:\n";
echo "  Agents registered: {$metrics['agents_registered']}\n";
echo "  Messages routed: {$metrics['messages_routed']}\n";
echo "  Messages in queue: {$metrics['messages_in_queue']}\n";
echo "  Conversation length: {$metrics['conversation_length']}\n\n";

if (isset($metrics['performance'])) {
    $perf = $metrics['performance'];
    echo "Performance metrics:\n";
    echo "  Total requests: {$perf['total_requests']}\n";
    echo "  Success rate: " . round($perf['success_rate'] * 100, 1) . "%\n";
    
    if ($perf['total_requests'] > 0) {
        echo "  Avg response time: " . round($perf['avg_response_time'], 3) . "s\n";
        echo "  Total tokens: {$perf['total_tokens']}\n";
    }
    echo "\n";
}

// ============================================================================
// Example 7: Protocol Enforcement
// ============================================================================

echo "--- Example 7: Protocol Validation ---\n\n";

echo "Creating manager with contract-net protocol...\n";

$contractNetManager = new CollaborationManager($client, [
    'protocol' => Protocol::contractNet(),
    'enable_message_passing' => true,
]);

$contractNetManager->registerAgent('agent1', $researcher, ['research']);
$contractNetManager->registerAgent('agent2', $analyst, ['analysis']);

echo "Protocol: contract-net\n";
echo "Valid message types: cfp, proposal, award, reject\n";
echo "Invalid message types will be rejected by protocol\n\n";

// ============================================================================
// Example 8: Agent Unregistration
// ============================================================================

echo "--- Example 8: Dynamic Agent Management ---\n\n";

// Create a temporary agent
$tempAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'temp_agent',
    capabilities: ['temporary_task'],
    options: ['name' => 'Temporary Agent']
);

$manager->registerAgent('temp_agent', $tempAgent, ['temporary_task']);
echo "Temporary agent registered\n";

$initialMetrics = $manager->getMetrics();
echo "  Agents: {$initialMetrics['agents_registered']}\n\n";

// Unregister the agent
$removed = $manager->unregisterAgent('temp_agent');
echo "Temporary agent unregistered: " . ($removed ? 'success' : 'failed') . "\n";

$finalMetrics = $manager->getMetrics();
echo "  Agents: {$finalMetrics['agents_registered']}\n\n";

// ============================================================================
// Example 9: Multi-Round Collaboration
// ============================================================================

echo "--- Example 9: Complex Multi-Round Task ---\n\n";

$complexTask = "Create a comprehensive market analysis for AI coding assistants. " .
               "Research the market, analyze competition, identify opportunities, " .
               "and write a strategic recommendation.";

echo "Complex task: {$complexTask}\n\n";
echo "This will require multiple agents across several rounds...\n\n";

$complexResult = $manager->collaborate($complexTask);

if ($complexResult->isSuccess()) {
    $meta = $complexResult->getMetadata();
    
    echo "✓ Completed in {$meta['rounds']} rounds\n";
    echo "  Agents: " . implode(' → ', $meta['agents_involved']) . "\n";
    echo "  Total exchanges: {$meta['conversation_length']}\n\n";
    
    echo "Strategic recommendation (summary):\n";
    echo "  " . substr($complexResult->getAnswer(), 0, 200) . "...\n\n";
}

// ============================================================================
// Final Summary
// ============================================================================

echo "=== Demo Complete ===\n\n";

$finalMetrics = $manager->getMetrics();

echo "Session Summary:\n";
echo "  Total agents: {$finalMetrics['agents_registered']}\n";
echo "  Messages routed: {$finalMetrics['messages_routed']}\n";
echo "  Collaborations: 2 completed\n";
echo "  Shared memory keys: {$finalMetrics['shared_memory_stats']['total_keys']}\n\n";

echo "Key Capabilities:\n";
echo "• Automatic agent selection based on capabilities\n";
echo "• Protocol-enforced message validation\n";
echo "• Round-based collaboration with max limits\n";
echo "• Shared memory for indirect coordination\n";
echo "• Automatic result synthesis from multi-agent work\n";
echo "• Comprehensive metrics and conversation history\n";
echo "• Dynamic agent registration/unregistration\n";
