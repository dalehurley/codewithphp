<?php
# filename: examples/multi-agent-demo.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\MultiAgent\AgentOrchestrator;
use ClaudePhp\ClaudePhp;

// Initialize Claude
$claude = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY'] ?? throw new \RuntimeException('ANTHROPIC_API_KEY environment variable required')
);

// Create orchestrator
$orchestrator = new AgentOrchestrator($claude);

// Create supervisor
$supervisor = $orchestrator->createSupervisor('Project Manager');

// Create worker team
$workers = $orchestrator->createWorkerTeam();

// Register workers with supervisor
foreach ($workers as $worker) {
    $supervisor->registerWorkerAgent($worker);
}

echo "Multi-Agent System initialized\n";
echo "Supervisor: {$supervisor->name}\n";
echo "Workers:\n";
foreach ($workers as $id => $worker) {
    echo "  - {$id}: {$worker->name} ({$worker->role})\n";
}
echo "\n";

// Example tasks
$tasks = [
    "Create a comprehensive guide for building a Laravel API with authentication, including code examples and best practices documentation.",

    "Research the latest PHP 8.4 features, write code examples demonstrating each feature, and create a blog post summarizing the findings.",

    "Build a user registration system with validation, create database migrations, and write documentation explaining the implementation."
];

foreach ($tasks as $i => $taskDescription) {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Task " . ($i + 1) . ": {$taskDescription}\n";
    echo str_repeat('=', 80) . "\n\n";

    $startTime = microtime(true);

    $result = $orchestrator->executeTask($taskDescription, priority: 'high');

    $duration = microtime(true) - $startTime;

    echo "Status: {$result->status}\n";
    echo "Duration: " . number_format($duration, 2) . "s\n\n";
    echo "Result:\n";
    echo $result->output . "\n";
}

echo "\n--- Multi-Agent System Statistics ---\n";
echo "Total agents: " . count($orchestrator->getAgents()) . "\n";
