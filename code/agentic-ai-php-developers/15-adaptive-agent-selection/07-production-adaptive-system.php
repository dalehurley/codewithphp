<?php

declare(strict_types=1);

/**
 * 07: Production Adaptive System
 *
 * This example demonstrates a complete production-ready adaptive agent system:
 * - Multiple specialized agents for different use cases
 * - Comprehensive logging and monitoring
 * - Error handling and graceful degradation
 * - Performance tracking and reporting
 * - k-NN learning enabled for continuous improvement
 *
 * This is a template for real-world adaptive agent deployments.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\AdaptiveAgentService;
use ClaudeAgents\Agents\ReactAgent;
use ClaudeAgents\Agents\ReflectionAgent;
use ClaudeAgents\Agents\ChainOfThoughtAgent;
use ClaudeAgents\Agents\RAGAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

echo "=================================================================\n";
echo "Production Adaptive Agent System\n";
echo "=================================================================\n\n";

// =================================================================
// Setup: Logging (Optional - uses Monolog if available)
// =================================================================

$logger = null;

// Check if Monolog is available
if (class_exists('Monolog\Logger')) {
    $logger = new \Monolog\Logger('adaptive-service');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/storage/adaptive.log', \Monolog\Logger::INFO));
    echo "✓ Logging configured (Monolog)\n";
} else {
    echo "ℹ Logging not available (Monolog not installed - this is optional)\n";
}

// =================================================================
// Setup: Claude Client
// =================================================================

$client = new ClaudePhp(apiKey: $apiKey);

echo "✓ Claude client initialized\n";

// =================================================================
// Setup: Specialized Agents
// =================================================================

echo "\nCreating specialized agent fleet...\n\n";

// 1. React Agent - Tool usage and iterative tasks
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

$reactAgent = new ReactAgent($client, [
    'tools' => [$calculatorTool],
    'max_iterations' => 5,
    'system' => 'You are a helpful assistant with access to tools for solving problems.',
]);

echo "✓ React Agent (tool usage specialist)\n";

// 2. Reflection Agent - Quality-critical tasks
$reflectionAgent = new ReflectionAgent($client, [
    'max_refinements' => 3,
    'quality_threshold' => 8,
    'system' => 'You are a meticulous assistant who produces high-quality, polished outputs.',
]);

echo "✓ Reflection Agent (quality specialist)\n";

// 3. Chain of Thought - Reasoning tasks
$cotAgent = new ChainOfThoughtAgent($client, [
    'mode' => 'zero_shot',
    'system' => 'You are a logical assistant who explains reasoning step-by-step.',
]);

echo "✓ Chain of Thought Agent (reasoning specialist)\n";

// 4. RAG Agent - Knowledge-based tasks
$ragAgent = new RAGAgent($client, [
    'system' => 'You are a knowledgeable assistant who provides accurate, source-grounded answers.',
]);

// Add knowledge base
$ragAgent->addDocument(
    'Company Policies',
    'Our company offers 20 days paid vacation per year. ' .
    'Health insurance is provided to all full-time employees. ' .
    'Remote work is allowed up to 3 days per week. ' .
    'Performance reviews are conducted quarterly.'
);

$ragAgent->addDocument(
    'Technical Documentation',
    'API rate limit is 1000 requests per hour. ' .
    'Authentication uses JWT tokens with 24-hour expiration. ' .
    'All endpoints require HTTPS. ' .
    'Error responses follow RFC 7807 Problem Details standard.'
);

echo "✓ RAG Agent (knowledge specialist)\n\n";

// =================================================================
// Setup: Adaptive Agent Service
// =================================================================

echo "Creating production Adaptive Agent Service...\n\n";

$serviceConfig = [
    'name' => 'production-adaptive-service',
    'max_attempts' => 3,
    'quality_threshold' => 7.5,       // Higher for production
    'enable_reframing' => true,
    'enable_knn' => true,
    'history_store_path' => __DIR__ . '/storage/production_history.json',
];

// Add logger if available
if ($logger) {
    $serviceConfig['logger'] = $logger;
}

$service = new AdaptiveAgentService($client, $serviceConfig);

// Register React Agent
$service->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'strengths' => [
        'tool orchestration',
        'iterative problem solving',
        'multi-step workflows',
    ],
    'best_for' => [
        'calculations',
        'API calls',
        'data processing',
        'multi-step tasks',
    ],
    'complexity_level' => 'medium',
    'speed' => 'medium',
    'quality' => 'standard',
]);

echo "✓ Registered React Agent\n";

// Register Reflection Agent
$service->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'strengths' => [
        'quality refinement',
        'self-improvement',
        'careful review',
    ],
    'best_for' => [
        'writing',
        'code generation',
        'critical outputs',
        'quality-sensitive tasks',
    ],
    'complexity_level' => 'medium',
    'speed' => 'slow',
    'quality' => 'high',
]);

echo "✓ Registered Reflection Agent\n";

// Register Chain of Thought Agent
$service->registerAgent('cot', $cotAgent, [
    'type' => 'cot',
    'strengths' => [
        'step-by-step reasoning',
        'logical explanations',
        'transparent thinking',
    ],
    'best_for' => [
        'math problems',
        'logic puzzles',
        'explanations',
        'teaching',
    ],
    'complexity_level' => 'medium',
    'speed' => 'fast',
    'quality' => 'standard',
]);

echo "✓ Registered Chain of Thought Agent\n";

// Register RAG Agent
$service->registerAgent('rag', $ragAgent, [
    'type' => 'rag',
    'strengths' => [
        'knowledge grounding',
        'source attribution',
        'fact accuracy',
    ],
    'best_for' => [
        'Q&A from documents',
        'policy questions',
        'documentation queries',
        'fact-based tasks',
    ],
    'complexity_level' => 'simple',
    'speed' => 'fast',
    'quality' => 'high',
]);

echo "✓ Registered RAG Agent\n\n";

echo "Agent fleet ready: " . implode(', ', $service->getRegisteredAgents()) . "\n\n";

// =================================================================
// Production Task Processing
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Processing Production Tasks\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

$tasks = [
    [
        'id' => 'task-001',
        'task' => 'Calculate the total cost if 3 items at $29.99 each with 8% sales tax',
        'priority' => 'normal',
    ],
    [
        'id' => 'task-002',
        'task' => 'Write a professional email responding to a customer complaint about shipping delays',
        'priority' => 'high',
    ],
    [
        'id' => 'task-003',
        'task' => 'What is our company policy on remote work?',
        'priority' => 'normal',
    ],
    [
        'id' => 'task-004',
        'task' => 'Explain why 0.1 + 0.2 does not exactly equal 0.3 in floating-point arithmetic',
        'priority' => 'normal',
    ],
];

$processedTasks = [];

$totalTasks = count($tasks);

foreach ($tasks as $i => $taskData) {
    $num = $i + 1;
    $taskId = $taskData['id'];
    $task = $taskData['task'];
    $priority = $taskData['priority'];

    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Task {$num}/{$totalTasks}: {$taskId} [{$priority}]\n";
    echo "─────────────────────────────────────────────────────────────────\n\n";

    echo "Task: {$task}\n\n";

    $startTime = microtime(true);

    try {
        // Execute with adaptive service
        $result = $service->run($task);

        $duration = microtime(true) - $startTime;

        if ($result->isSuccess()) {
            $metadata = $result->getMetadata();

            echo "✓ SUCCESS\n\n";
            echo "Answer: " . substr($result->getAnswer(), 0, 200);
            if (strlen($result->getAnswer()) > 200) {
                echo "...";
            }
            echo "\n\n";

            echo "Metadata:\n";
            echo "  Selected Agent: {$metadata['final_agent']}\n";
            echo "  Quality Score: {$metadata['final_quality']}/10\n";
            echo "  Attempts: {$result->getIterations()}\n";
            echo "  Duration: " . round($duration, 2) . "s\n";

            if (isset($metadata['task_analysis'])) {
                $analysis = $metadata['task_analysis'];
                echo "  Complexity: {$analysis['complexity']}\n";
                echo "  Domain: {$analysis['domain']}\n";
            }

            echo "\n";

            $processedTasks[] = [
                'task_id' => $taskId,
                'success' => true,
                'agent' => $metadata['final_agent'],
                'quality' => $metadata['final_quality'],
                'duration' => $duration,
                'attempts' => $result->getIterations(),
            ];

            if ($logger) {
                $logger->info("Task completed", [
                    'task_id' => $taskId,
                    'agent' => $metadata['final_agent'],
                    'quality' => $metadata['final_quality'],
                ]);
            }
        } else {
            echo "✗ FAILED\n";
            echo "Error: {$result->getError()}\n\n";

            $processedTasks[] = [
                'task_id' => $taskId,
                'success' => false,
                'error' => $result->getError(),
                'duration' => $duration,
            ];

            if ($logger) {
                $logger->error("Task failed", [
                    'task_id' => $taskId,
                    'error' => $result->getError(),
                ]);
            }
        }
    } catch (\Throwable $e) {
        $duration = microtime(true) - $startTime;

        echo "✗ EXCEPTION\n";
        echo "Error: {$e->getMessage()}\n\n";

        $processedTasks[] = [
            'task_id' => $taskId,
            'success' => false,
            'error' => $e->getMessage(),
            'duration' => $duration,
        ];

        if ($logger) {
            $logger->error("Task exception", [
                'task_id' => $taskId,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}

// =================================================================
// Production Metrics
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Production Metrics\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

// Agent performance
$performance = $service->getPerformance();

echo "Agent Performance:\n\n";

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 0) {
        $successRate = round(($stats['successes'] / $stats['attempts']) * 100, 1);
        $avgQuality = round($stats['average_quality'], 1);
        $avgDuration = round($stats['total_duration'] / $stats['attempts'], 2);

        echo "{$agentId}:\n";
        echo "  Attempts: {$stats['attempts']}\n";
        echo "  Success Rate: {$successRate}%\n";
        echo "  Avg Quality: {$avgQuality}/10\n";
        echo "  Avg Duration: {$avgDuration}s\n\n";
    }
}

// Learning stats
$historyStats = $service->getHistoryStats();

if ($historyStats['knn_enabled']) {
    echo "Learning System:\n";
    echo "  k-NN: enabled\n";
    echo "  Historical Records: {$historyStats['total_records']}\n";
    echo "  Unique Agents: {$historyStats['unique_agents']}\n";

    if (isset($historyStats['success_rate'])) {
        echo "  Overall Success Rate: " . round($historyStats['success_rate'] * 100, 1) . "%\n";
    }

    if (isset($historyStats['avg_quality'])) {
        echo "  Overall Avg Quality: " . round($historyStats['avg_quality'], 1) . "/10\n";
    }

    echo "\n";
}

// Task summary
$successCount = count(array_filter($processedTasks, fn($t) => $t['success']));
$failureCount = count($processedTasks) - $successCount;
$totalDuration = array_sum(array_column($processedTasks, 'duration'));
$avgDuration = $totalDuration / count($processedTasks);

echo "Task Summary:\n";
echo "  Total: " . count($processedTasks) . "\n";
echo "  Successful: {$successCount}\n";
echo "  Failed: {$failureCount}\n";
echo "  Success Rate: " . round(($successCount / count($processedTasks)) * 100, 1) . "%\n";
echo "  Total Duration: " . round($totalDuration, 2) . "s\n";
echo "  Avg Duration: " . round($avgDuration, 2) . "s\n\n";

// =================================================================
// Export Production Report
// =================================================================

$reportPath = __DIR__ . '/storage/production_report.json';

$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'service' => $service->getName(),
    'tasks_processed' => count($processedTasks),
    'success_rate' => round(($successCount / count($processedTasks)) * 100, 1),
    'agent_performance' => $performance,
    'history_stats' => $historyStats,
    'tasks' => $processedTasks,
];

@mkdir(dirname($reportPath), 0777, true);
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

echo "✓ Production report exported: {$reportPath}\n";
if ($logger) {
    echo "✓ Logs available: " . __DIR__ . "/storage/adaptive.log\n";
} else {
    echo "ℹ Install Monolog for production logging: composer require monolog/monolog\n";
}
echo "\n";

// =================================================================
// Production Summary
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Production System Summary\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

echo "✓ Multi-agent fleet operational\n";
echo "✓ Automatic agent selection based on task analysis\n";
echo "✓ Quality validation on all outputs\n";
echo "✓ Adaptive retry and reframing enabled\n";
echo "✓ k-NN learning system active\n";
echo "✓ Comprehensive logging and monitoring\n";
echo "✓ Performance metrics tracked\n";
echo "✓ Production reports generated\n\n";

echo "This system is ready for production deployment!\n\n";
