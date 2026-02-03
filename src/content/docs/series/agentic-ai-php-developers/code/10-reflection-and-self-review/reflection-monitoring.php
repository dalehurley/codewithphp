<?php

/**
 * Reflection Monitoring and Metrics Example
 * 
 * Demonstrates how to monitor reflection loop progress, track quality improvements,
 * and collect metrics for analysis and optimization.
 * 
 * From: Agentic AI for PHP Developers
 * Chapter 10: Reflection and Self-Review Loops
 * 
 * @link https://github.com/claude-php/claude-php-agent
 */

declare(strict_types=1);

// Adjust path to your vendor/autoload.php location
require __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "Reflection Monitoring and Metrics\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * ReflectionMonitor Class
 * 
 * Tracks detailed metrics about the reflection process including timing,
 * quality improvements, and token usage.
 */
class ReflectionMonitor
{
    private array $refinements = [];
    private float $startTime;
    private array $iterationTimings = [];

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->refinements = [];
        $this->iterationTimings = [];
    }

    public function recordRefinement(int $number, int $score, string $feedback): void
    {
        $this->refinements[] = [
            'number' => $number,
            'score' => $score,
            'feedback' => $feedback,
            'timestamp' => microtime(true),
            'elapsed' => round(microtime(true) - $this->startTime, 2),
        ];
        
        echo "📊 Refinement {$number}: Score {$score}/10\n";
        echo "   Elapsed: " . round(microtime(true) - $this->startTime, 2) . "s\n";
        
        // Show score improvement
        if (count($this->refinements) > 1) {
            $prevScore = $this->refinements[count($this->refinements) - 2]['score'];
            $improvement = $score - $prevScore;
            $arrow = $improvement > 0 ? '↑' : ($improvement < 0 ? '↓' : '→');
            echo "   Improvement: {$arrow} " . abs($improvement) . " points\n";
        }
        
        echo "\n";
    }

    public function recordIteration(int $iteration, $response): void
    {
        $this->iterationTimings[] = [
            'iteration' => $iteration,
            'timestamp' => microtime(true),
            'stop_reason' => $response->stop_reason ?? 'unknown',
        ];
    }

    public function getReport(): array
    {
        $scores = array_column($this->refinements, 'score');
        $totalDuration = microtime(true) - $this->startTime;
        
        return [
            'total_refinements' => count($this->refinements),
            'initial_score' => $scores[0] ?? 0,
            'final_score' => end($scores) ?: 0,
            'total_improvement' => (end($scores) ?: 0) - ($scores[0] ?? 0),
            'average_score' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0,
            'duration_seconds' => round($totalDuration, 2),
            'avg_refinement_time' => count($this->refinements) > 0 
                ? round($totalDuration / count($this->refinements), 2) 
                : 0,
            'refinements' => $this->refinements,
            'reached_threshold' => (end($scores) ?: 0) >= 8,
        ];
    }

    public function printReport(array $report): void
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "REFLECTION MONITORING REPORT\n";
        echo str_repeat("=", 80) . "\n\n";
        
        echo "Quality Metrics:\n";
        echo "  - Initial Score: {$report['initial_score']}/10\n";
        echo "  - Final Score: {$report['final_score']}/10\n";
        echo "  - Total Improvement: " . ($report['total_improvement'] >= 0 ? '+' : '') . 
             "{$report['total_improvement']} points\n";
        echo "  - Average Score: {$report['average_score']}/10\n";
        echo "  - Threshold Reached: " . ($report['reached_threshold'] ? 'Yes ✅' : 'No ❌') . "\n\n";
        
        echo "Performance Metrics:\n";
        echo "  - Total Duration: {$report['duration_seconds']}s\n";
        echo "  - Total Refinements: {$report['total_refinements']}\n";
        echo "  - Avg Time per Refinement: {$report['avg_refinement_time']}s\n\n";
        
        if (!empty($report['refinements'])) {
            echo "Refinement Timeline:\n";
            foreach ($report['refinements'] as $ref) {
                echo sprintf(
                    "  %s. Score: %d/10 @ %ss\n",
                    $ref['number'],
                    $ref['score'],
                    $ref['elapsed']
                );
            }
        }
    }
}

/**
 * Example 1: Basic Monitoring
 */
echo "Example 1: Monitoring a Standard Reflection Task\n";
echo str_repeat("-", 80) . "\n\n";

$monitor1 = new ReflectionMonitor();
$monitor1->start();

$loop1 = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'clarity, accuracy, completeness, and examples'
);

$loop1->onReflection([$monitor1, 'recordRefinement']);
$loop1->onIteration([$monitor1, 'recordIteration']);

$agent1 = Agent::create($client)
    ->withLoopStrategy($loop1)
    ->withSystemPrompt('You are a helpful technical writer.')
    ->maxIterations(15);

$task1 = 'Explain the benefits of using readonly properties in PHP 8.4 with practical examples.';
echo "Task: {$task1}\n\n";

$result1 = $agent1->run($task1);

$report1 = $monitor1->getReport();
$monitor1->printReport($report1);

echo "\nFinal Output Preview:\n";
echo substr($result1->getAnswer(), 0, 300) . "...\n";

/**
 * Example 2: Comparing Multiple Runs
 */
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "Example 2: Comparing Multiple Reflection Runs\n";
echo str_repeat("=", 80) . "\n\n";

$tasks = [
    'Explain array_map in PHP',
    'Describe try-catch-finally in PHP',
    'Explain dependency injection',
];

$allReports = [];

foreach ($tasks as $index => $task) {
    echo "Run " . ($index + 1) . ": {$task}\n";
    echo str_repeat("-", 80) . "\n";
    
    $monitor = new ReflectionMonitor();
    $monitor->start();
    
    $loop = new ReflectionLoop(
        maxRefinements: 3,
        qualityThreshold: 8,
        criteria: 'clarity and completeness'
    );
    
    $loop->onReflection([$monitor, 'recordRefinement']);
    
    $agent = Agent::create($client)
        ->withLoopStrategy($loop)
        ->withSystemPrompt('You are a helpful programming instructor.')
        ->maxIterations(15);
    
    $result = $agent->run($task);
    $report = $monitor->getReport();
    $allReports[] = array_merge($report, ['task' => $task]);
    
    echo "Result: Final score {$report['final_score']}/10 ";
    echo "in {$report['total_refinements']} refinements ";
    echo "({$report['duration_seconds']}s)\n\n";
}

/**
 * Aggregate Analysis
 */
echo "\n" . str_repeat("=", 80) . "\n";
echo "AGGREGATE ANALYSIS\n";
echo str_repeat("=", 80) . "\n\n";

$avgFinalScore = array_sum(array_column($allReports, 'final_score')) / count($allReports);
$avgImprovement = array_sum(array_column($allReports, 'total_improvement')) / count($allReports);
$avgDuration = array_sum(array_column($allReports, 'duration_seconds')) / count($allReports);
$avgRefinements = array_sum(array_column($allReports, 'total_refinements')) / count($allReports);

echo "Across " . count($allReports) . " tasks:\n";
echo "  - Average Final Score: " . round($avgFinalScore, 2) . "/10\n";
echo "  - Average Improvement: +" . round($avgImprovement, 2) . " points\n";
echo "  - Average Duration: " . round($avgDuration, 2) . "s\n";
echo "  - Average Refinements: " . round($avgRefinements, 2) . "\n";

$successRate = count(array_filter($allReports, fn($r) => $r['reached_threshold'])) / count($allReports) * 100;
echo "  - Success Rate (≥8/10): " . round($successRate, 1) . "%\n";

echo "\nDetailed Breakdown:\n";
printf("%-40s | %10s | %12s | %10s\n", 'Task', 'Final', 'Improvement', 'Duration');
echo str_repeat("-", 80) . "\n";

foreach ($allReports as $report) {
    printf(
        "%-40s | %8s/10 | %10s pts | %8ss\n",
        substr($report['task'], 0, 40),
        $report['final_score'],
        ($report['total_improvement'] >= 0 ? '+' : '') . $report['total_improvement'],
        $report['duration_seconds']
    );
}

echo "\n✅ Monitoring examples complete!\n";
echo "\n💡 Key Insights:\n";
echo "   - Track refinement progress in real-time with callbacks\n";
echo "   - Monitor quality improvements across iterations\n";
echo "   - Analyze aggregate metrics to optimize reflection settings\n";
echo "   - Use timing data to identify performance bottlenecks\n";
