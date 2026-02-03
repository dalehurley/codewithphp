<?php

declare(strict_types=1);

/**
 * 06: Performance Monitoring
 *
 * This example demonstrates:
 * - Performance tracking per agent
 * - Success rates and quality metrics
 * - Identifying best and worst performers
 * - Using performance data to optimize
 *
 * Learn how to monitor and improve your agent fleet.
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
echo "Performance Monitoring\n";
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
    'max_attempts' => 3,
    'quality_threshold' => 7.0,
    'enable_knn' => false,  // Disable for cleaner performance demo
]);

$service->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'quality' => 'standard',
]);

$service->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'complexity_level' => 'medium',
    'quality' => 'high',
]);

$service->registerAgent('cot', $cotAgent, [
    'type' => 'cot',
    'complexity_level' => 'medium',
    'quality' => 'standard',
]);

echo "Registered agents: " . implode(', ', $service->getRegisteredAgents()) . "\n\n";

// =================================================================
// Run Diverse Tasks
// =================================================================

echo "Running diverse tasks to build performance data...\n\n";

$tasks = [
    ['Calculate 15% of 240', 'calculation'],
    ['Calculate 20% of 500', 'calculation'],
    ['Write a thank you email', 'writing'],
    ['If all A are B and all B are C, are all A also C?', 'reasoning'],
    ['Calculate 10% of 150', 'calculation'],
    ['Write a professional introduction', 'writing'],
    ['Explain the transitive property', 'reasoning'],
    ['Find 25% of 800', 'calculation'],
    ['Draft an apology for a delay', 'writing'],
    ['Is 1+1=2 always true? Explain.', 'reasoning'],
];

$results = [];

foreach ($tasks as $i => $taskData) {
    [$task, $category] = $taskData;
    $num = $i + 1;

    echo "[{$num}/10] {$category}: " . substr($task, 0, 40) . "...\n";

    $result = $service->run($task);

    if ($result->isSuccess()) {
        $metadata = $result->getMetadata();
        echo "  ✓ Agent: {$metadata['final_agent']}, Quality: {$metadata['final_quality']}/10\n";

        $results[] = [
            'task' => $task,
            'category' => $category,
            'agent' => $metadata['final_agent'],
            'quality' => $metadata['final_quality'],
            'success' => true,
        ];
    } else {
        echo "  ✗ Failed\n";
        $results[] = [
            'task' => $task,
            'category' => $category,
            'success' => false,
        ];
    }
}

echo "\n";

// =================================================================
// Performance Metrics
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Performance Metrics\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

$performance = $service->getPerformance();

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 0) {
        $successRate = round(($stats['successes'] / $stats['attempts']) * 100, 1);
        $avgQuality = round($stats['average_quality'], 1);
        $avgDuration = round($stats['total_duration'] / $stats['attempts'], 2);

        echo "{$agentId} Agent:\n";
        echo "  Attempts: {$stats['attempts']}\n";
        echo "  Successes: {$stats['successes']}\n";
        echo "  Failures: {$stats['failures']}\n";
        echo "  Success Rate: {$successRate}%\n";
        echo "  Average Quality: {$avgQuality}/10\n";
        echo "  Average Duration: {$avgDuration}s\n";
        echo "  Total Duration: " . round($stats['total_duration'], 1) . "s\n\n";
    }
}

// =================================================================
// Performance Analysis
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Performance Analysis\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

// Find best performer
$bestAgent = null;
$bestScore = 0;

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 0) {
        $score = $stats['average_quality'];
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestAgent = $agentId;
        }
    }
}

if ($bestAgent) {
    echo "Best Performer (by quality):\n";
    echo "  Agent: {$bestAgent}\n";
    echo "  Average Quality: " . round($bestScore, 1) . "/10\n\n";
}

// Find fastest agent
$fastestAgent = null;
$fastestTime = PHP_FLOAT_MAX;

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 0) {
        $avgTime = $stats['total_duration'] / $stats['attempts'];
        if ($avgTime < $fastestTime) {
            $fastestTime = $avgTime;
            $fastestAgent = $agentId;
        }
    }
}

if ($fastestAgent) {
    echo "Fastest Agent:\n";
    echo "  Agent: {$fastestAgent}\n";
    echo "  Average Duration: " . round($fastestTime, 2) . "s\n\n";
}

// Analyze by category
echo "Performance by Task Category:\n\n";

$categories = [];
foreach ($results as $result) {
    if ($result['success']) {
        $cat = $result['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = [
                'count' => 0,
                'agents' => [],
                'avg_quality' => 0,
            ];
        }

        $categories[$cat]['count']++;
        $categories[$cat]['avg_quality'] += $result['quality'];

        if (!isset($categories[$cat]['agents'][$result['agent']])) {
            $categories[$cat]['agents'][$result['agent']] = 0;
        }
        $categories[$cat]['agents'][$result['agent']]++;
    }
}

foreach ($categories as $category => $data) {
    $avgQuality = round($data['avg_quality'] / $data['count'], 1);

    echo "{$category}:\n";
    echo "  Tasks: {$data['count']}\n";
    echo "  Average Quality: {$avgQuality}/10\n";
    echo "  Agents used:\n";

    foreach ($data['agents'] as $agent => $count) {
        echo "    - {$agent}: {$count} times\n";
    }

    echo "\n";
}

// =================================================================
// Recommendations
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Recommendations\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 5) {
        $successRate = ($stats['successes'] / $stats['attempts']) * 100;
        $avgQuality = $stats['average_quality'];

        // Underperforming agent
        if ($successRate < 70 || $avgQuality < 6.5) {
            echo "⚠ Warning: {$agentId} agent underperforming\n";
            echo "  Success rate: " . round($successRate, 1) . "%\n";
            echo "  Average quality: " . round($avgQuality, 1) . "/10\n";
            echo "  → Consider adjusting profile or replacing agent\n\n";
        }

        // High performer
        if ($successRate >= 90 && $avgQuality >= 8.0) {
            echo "✓ Excellent: {$agentId} agent performing very well\n";
            echo "  Success rate: " . round($successRate, 1) . "%\n";
            echo "  Average quality: " . round($avgQuality, 1) . "/10\n";
            echo "  → Consider using for critical tasks\n\n";
        }
    }
}

// =================================================================
// Export Performance Data
// =================================================================

echo "═════════════════════════════════════════════════════════════════\n";
echo "Performance Export\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

$exportPath = __DIR__ . '/storage/performance_report.json';
$exportData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_tasks' => count($results),
    'agents' => $service->getRegisteredAgents(),
    'performance' => $performance,
    'categories' => $categories,
];

@mkdir(dirname($exportPath), 0777, true);
file_put_contents($exportPath, json_encode($exportData, JSON_PRETTY_PRINT));

echo "✓ Performance report exported to:\n";
echo "  {$exportPath}\n\n";

// =================================================================
// Key Takeaways
// =================================================================

echo "=================================================================\n";
echo "Key Lessons:\n";
echo "=================================================================\n\n";

echo "1. Track Everything\n";
echo "   - Attempts, successes, failures\n";
echo "   - Average quality scores\n";
echo "   - Average duration\n\n";

echo "2. Identify Patterns\n";
echo "   - Which agents excel at which task types?\n";
echo "   - Are any agents underperforming?\n";
echo "   - What's the quality vs speed tradeoff?\n\n";

echo "3. Optimize Fleet\n";
echo "   - Remove or replace underperformers\n";
echo "   - Adjust profiles based on actual performance\n";
echo "   - Balance quality, speed, and cost\n\n";

echo "4. Regular Monitoring\n";
echo "   - Check metrics after batches of tasks\n";
echo "   - Export reports for analysis\n";
echo "   - Adjust thresholds and profiles\n\n";
