<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

/**
 * Step Execution and Progress Tracking
 * 
 * Demonstrates detailed progress tracking during plan execution:
 * - Visual progress bars
 * - Step timing information
 * - Execution summaries
 * - Performance metrics
 */

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

/**
 * Progress tracker for plan execution.
 */
class ExecutionTracker
{
    private array $plan = [];
    private array $stepResults = [];
    private float $startTime;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
    }
    
    public function recordPlan(array $steps): void
    {
        $this->plan = $steps;
        
        echo "📋 Plan recorded with " . count($steps) . " steps\n\n";
    }
    
    public function recordStepComplete(int $stepNumber, string $description, string $result): void
    {
        $duration = microtime(true) - $this->startTime;
        
        $this->stepResults[] = [
            'step' => $stepNumber,
            'description' => $description,
            'result' => $result,
            'timestamp' => $duration,
            'status' => 'completed',
        ];
        
        $this->displayProgress();
    }
    
    private function displayProgress(): void
    {
        $completed = count($this->stepResults);
        $total = count($this->plan);
        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        echo "Progress: [{$completed}/{$total}] ";
        echo str_repeat('█', (int)($percentage / 5));
        echo str_repeat('░', 20 - (int)($percentage / 5));
        echo " {$percentage}%\n\n";
    }
    
    public function getSummary(): array
    {
        $duration = microtime(true) - $this->startTime;
        
        return [
            'total_steps' => count($this->plan),
            'completed_steps' => count($this->stepResults),
            'duration' => round($duration, 2),
            'avg_step_time' => count($this->stepResults) > 0 
                ? round($duration / count($this->stepResults), 2) 
                : 0,
            'results' => $this->stepResults,
        ];
    }
}

// Create tracker
$tracker = new ExecutionTracker();

// Create loop with tracking
$loop = new PlanExecuteLoop(allowReplan: true);

$loop->onPlanCreated(function ($steps, $context) use ($tracker) {
    $tracker->recordPlan($steps);
});

$loop->onStepComplete(function ($stepNumber, $description, $result) use ($tracker) {
    echo "✅ Step {$stepNumber}: {$description}\n";
    $tracker->recordStepComplete($stepNumber, $description, $result);
});

// Create a simple calculation tool
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate (e.g., "10 * 5 + 20")')
    ->required('expression')
    ->handler(function (array $input): string {
        try {
            // Safe evaluation using a simple parser
            $expr = $input['expression'];
            // Remove any non-math characters for safety
            $expr = preg_replace('/[^0-9+\-*\/\(\)\.\s]/', '', $expr);
            $result = eval("return {$expr};");
            return "Result: " . $result;
        } catch (\Throwable $e) {
            return "Error: " . $e->getMessage();
        }
    });

// Create agent
$agent = Agent::create($client)
    ->withTool($calculator)
    ->withLoopStrategy($loop)
    ->maxIterations(15);

// Run task
echo "=== Task: Calculate Project Budget ===\n\n";

$task = "Calculate the total project budget:\n" .
        "- Development: 200 hours at \$150/hour\n" .
        "- Design: 40 hours at \$120/hour\n" .
        "- Testing: 30 hours at \$100/hour\n" .
        "- Add 15% contingency buffer\n" .
        "- Calculate final total";

$result = $agent->run($task);

// Display summary
echo "\n=== Execution Summary ===\n";
$summary = $tracker->getSummary();

echo "Total steps: {$summary['total_steps']}\n";
echo "Completed: {$summary['completed_steps']}\n";
echo "Duration: {$summary['duration']}s\n";
echo "Avg per step: {$summary['avg_step_time']}s\n\n";

echo "=== Final Answer ===\n";
echo $result->getAnswer() . "\n";
