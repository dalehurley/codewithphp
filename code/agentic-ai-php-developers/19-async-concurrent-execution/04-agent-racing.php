<?php

declare(strict_types=1);

/**
 * Chapter 19: Async & Concurrent Execution
 * Example 4: Agent Racing
 * 
 * Demonstrates racing multiple agents to get the fastest response.
 * 
 * Learn:
 * - Setting up AsyncCollaborationManager
 * - Registering multiple agents
 * - Racing agents for fastest response
 * - Use cases for agent racing
 * - Performance implications
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
echo "║              Agent Racing Demonstration                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// EXAMPLE 1: Basic Agent Racing
// ============================================================================

echo "Example 1: Basic Agent Racing\n";
echo str_repeat("─", 60) . "\n\n";

// Create async collaboration manager
$manager = new AsyncCollaborationManager($client, [
    'max_concurrent' => 3,
]);

// Create multiple agents with different configurations
$agent1 = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'speedy_1',
    capabilities: ['quick_response'],
    options: [
        'system_prompt' => 'You are a fast responder. Be brief and direct.',
        'model' => 'claude-3-5-haiku-20241022',
    ]
);

$agent2 = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'speedy_2',
    capabilities: ['quick_response'],
    options: [
        'system_prompt' => 'You provide rapid answers. Keep it short.',
        'model' => 'claude-3-5-haiku-20241022',
    ]
);

$agent3 = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'speedy_3',
    capabilities: ['quick_response'],
    options: [
        'system_prompt' => 'You answer quickly. Be concise.',
        'model' => 'claude-3-5-haiku-20241022',
    ]
);

// Register agents
$manager->registerAgent('speedy_1', $agent1, ['quick_response']);
$manager->registerAgent('speedy_2', $agent2, ['quick_response']);
$manager->registerAgent('speedy_3', $agent3, ['quick_response']);

echo "Racing 3 agents to answer the same question...\n";
echo "Task: 'What is PHP?'\n\n";

$startTime = microtime(true);

$winner = $manager->race([
    'speedy_1' => 'What is PHP? Answer in 10 words.',
    'speedy_2' => 'What is PHP? Answer in 10 words.',
    'speedy_3' => 'What is PHP? Answer in 10 words.',
]);

$duration = microtime(true) - $startTime;

echo "🏆 Winner: {$winner['agent_id']}\n";
echo "⏱️  Time: " . round($duration, 2) . " seconds\n\n";
echo "Response:\n";
echo str_repeat("─", 60) . "\n";
echo $winner['result']->getAnswer() . "\n";
echo str_repeat("─", 60) . "\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 2: Model Speed Comparison
// ============================================================================

echo "Example 2: Comparing Model Speeds\n";
echo str_repeat("─", 60) . "\n\n";

// Create agents with different models
$haikuAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'haiku',
    capabilities: ['fast'],
    options: [
        'system_prompt' => 'Be concise and direct.',
        'model' => 'claude-3-5-haiku-20241022',
    ]
);

$sonnetAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'sonnet',
    capabilities: ['detailed'],
    options: [
        'system_prompt' => 'Be concise and direct.',
        'model' => 'claude-3-5-sonnet-20241022',
    ]
);

$manager->registerAgent('haiku', $haikuAgent, ['fast']);
$manager->registerAgent('sonnet', $sonnetAgent, ['detailed']);

echo "Racing Haiku vs Sonnet models...\n";
echo "Task: 'List 3 benefits of PHP'\n\n";

$startTime = microtime(true);

$winner = $manager->race([
    'haiku' => 'List 3 benefits of PHP. Be brief.',
    'sonnet' => 'List 3 benefits of PHP. Be brief.',
]);

$duration = microtime(true) - $startTime;

echo "🏆 Fastest model: {$winner['agent_id']}\n";
echo "⏱️  Response time: " . round($duration, 2) . " seconds\n\n";

echo "Answer:\n";
echo str_repeat("─", 60) . "\n";
echo $winner['result']->getAnswer() . "\n";
echo str_repeat("─", 60) . "\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 3: Redundancy Pattern (Multiple Providers)
// ============================================================================

echo "Example 3: Redundancy with Racing\n";
echo str_repeat("─", 60) . "\n\n";

echo "Use case: Having redundant agents ensures reliability\n";
echo "If one fails or is slow, others can provide the answer\n\n";

// Create redundant agents (simulating different providers/configs)
$primary = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'primary',
    capabilities: ['general'],
    options: ['system_prompt' => 'You are a primary agent. Be helpful.']
);

$backup1 = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'backup_1',
    capabilities: ['general'],
    options: ['system_prompt' => 'You are a backup agent. Be helpful.']
);

$backup2 = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'backup_2',
    capabilities: ['general'],
    options: ['system_prompt' => 'You are a backup agent. Be helpful.']
);

$manager->registerAgent('primary', $primary);
$manager->registerAgent('backup_1', $backup1);
$manager->registerAgent('backup_2', $backup2);

$query = 'What is 25 * 17?';

echo "Query: '{$query}'\n";
echo "Racing primary + 2 backups...\n\n";

$startTime = microtime(true);

$winner = $manager->race([
    'primary' => $query,
    'backup_1' => $query,
    'backup_2' => $query,
]);

$duration = microtime(true) - $startTime;

echo "✓ First responder: {$winner['agent_id']}\n";
echo "✓ Response time: " . round($duration, 2) . " seconds\n";
echo "✓ Answer: {$winner['result']->getAnswer()}\n";

echo "\n(Other agents were automatically cancelled)\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 4: Speed-Critical Responses
// ============================================================================

echo "Example 4: Speed-Critical Use Case\n";
echo str_repeat("─", 60) . "\n\n";

echo "Scenario: Live chatbot demo where speed matters most\n\n";

// Create fast-response agents
$fastAgents = [];
for ($i = 1; $i <= 3; $i++) {
    $agent = new SimpleCollaborativeAgent(
        client: $client,
        agentId: "fast_{$i}",
        capabilities: ['speed'],
        options: [
            'system_prompt' => 'Respond as quickly as possible. Be ultra-concise.',
            'model' => 'claude-3-5-haiku-20241022',
        ]
    );
    $manager->registerAgent("fast_{$i}", $agent);
    $fastAgents["fast_{$i}"] = 'Hello! How can you help me?';
}

echo "User message: 'Hello! How can you help me?'\n";
echo "Racing 3 fast agents for immediate response...\n\n";

$startTime = microtime(true);
$winner = $manager->race($fastAgents);
$duration = microtime(true) - $startTime;

echo "⚡ Ultra-fast response from: {$winner['agent_id']}\n";
echo "⚡ Response time: " . round($duration, 3) . " seconds\n\n";
echo "Bot response:\n";
echo str_repeat("─", 60) . "\n";
echo $winner['result']->getAnswer() . "\n";
echo str_repeat("─", 60) . "\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 5: Racing Statistics
// ============================================================================

echo "Example 5: Racing Performance Analysis\n";
echo str_repeat("─", 60) . "\n\n";

echo "Running multiple races to analyze performance...\n\n";

$queries = [
    'What is 2 + 2?',
    'Name a color',
    'What is the capital of France?',
    'What is the largest ocean?',
];

$winners = [];
$totalDuration = 0;

foreach ($queries as $i => $query) {
    echo "Race " . ($i + 1) . ": '{$query}'\n";
    
    $start = microtime(true);
    $winner = $manager->race([
        'speedy_1' => $query,
        'speedy_2' => $query,
        'speedy_3' => $query,
    ]);
    $duration = microtime(true) - $start;
    
    $winners[$winner['agent_id']] = ($winners[$winner['agent_id']] ?? 0) + 1;
    $totalDuration += $duration;
    
    echo "  Winner: {$winner['agent_id']} ({$duration}s)\n\n";
}

echo str_repeat("─", 60) . "\n";
echo "Race Statistics:\n";
echo str_repeat("─", 60) . "\n";

echo "\nWins by agent:\n";
foreach ($winners as $agentId => $wins) {
    $percentage = ($wins / count($queries)) * 100;
    echo "  {$agentId}: {$wins} wins ({$percentage}%)\n";
}

$avgDuration = $totalDuration / count($queries);
echo "\nAverage race duration: " . round($avgDuration, 3) . " seconds\n";

echo "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    Key Takeaways                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ Agent racing returns the first response, cancels others\n";
echo "✓ Perfect for speed-critical applications (chatbots, demos)\n";
echo "✓ Provides redundancy if one agent fails or is slow\n";
echo "✓ Can compare model speeds (Haiku vs Sonnet)\n";
echo "✓ Trade-off: Uses more API calls but gets faster response\n";
echo "✓ Best for: Low-latency requirements, reliability needs\n";
echo "✓ AsyncCollaborationManager handles racing logic\n\n";

echo "Next: 05-async-multi-agent.php\n";
