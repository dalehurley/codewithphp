<?php

/**
 * Production Reflection System
 * 
 * Complete production-grade reflection system with quality profiles,
 * monitoring, cost controls, and metrics collection.
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

echo "Production Reflection System\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * ReflectionOrchestrator
 * 
 * Manages reflection execution with quality profiles, monitoring,
 * and cost controls for production use.
 */
class ReflectionOrchestrator
{
    private ClaudePhp $client;
    private array $metrics = [];
    
    /**
     * Quality profiles for different task types
     */
    private array $qualityProfiles = [
        'critical' => [
            'maxRefinements' => 5,
            'qualityThreshold' => 9,
            'maxTokenBudget' => 20000,
            'description' => 'Mission-critical content (legal, medical, financial)',
        ],
        'production' => [
            'maxRefinements' => 3,
            'qualityThreshold' => 8,
            'maxTokenBudget' => 12000,
            'description' => 'Production user-facing content',
        ],
        'standard' => [
            'maxRefinements' => 2,
            'qualityThreshold' => 7,
            'maxTokenBudget' => 8000,
            'description' => 'Internal documentation, standard tasks',
        ],
        'draft' => [
            'maxRefinements' => 1,
            'qualityThreshold' => 6,
            'maxTokenBudget' => 5000,
            'description' => 'Quick drafts, prototypes',
        ],
    ];

    public function __construct(ClaudePhp $client)
    {
        $this->client = $client;
    }

    /**
     * Execute task with specified quality profile
     */
    public function execute(
        string $task,
        string $profile = 'standard',
        ?string $criteria = null,
        ?string $systemPrompt = null
    ): array {
        if (!isset($this->qualityProfiles[$profile])) {
            throw new \InvalidArgumentException("Unknown profile: {$profile}");
        }

        $config = $this->qualityProfiles[$profile];
        $startTime = microtime(true);
        
        echo "Executing with '{$profile}' profile:\n";
        echo "  - Max Refinements: {$config['maxRefinements']}\n";
        echo "  - Quality Threshold: {$config['qualityThreshold']}/10\n";
        echo "  - Token Budget: {$config['maxTokenBudget']}\n";
        echo "  - Description: {$config['description']}\n\n";

        // Create reflection loop with profile settings
        $loop = new ReflectionLoop(
            maxRefinements: $config['maxRefinements'],
            qualityThreshold: $config['qualityThreshold'],
            criteria: $criteria ?? 'correctness, completeness, clarity, and quality'
        );

        // Monitor refinements
        $refinementMetrics = [];
        $loop->onReflection(function (int $ref, int $score, string $feedback) use (&$refinementMetrics, $profile) {
            $refinementMetrics[] = [
                'number' => $ref,
                'score' => $score,
                'feedback_length' => strlen($feedback),
                'timestamp' => microtime(true),
            ];
            
            echo "  Refinement {$ref}: {$score}/10\n";
        });

        // Create and run agent
        $agent = Agent::create($this->client)
            ->withLoopStrategy($loop)
            ->withSystemPrompt($systemPrompt ?? 'You are a helpful assistant that creates high-quality outputs.')
            ->maxIterations(20);

        $result = $agent->run($task);
        
        $duration = microtime(true) - $startTime;
        $tokenUsage = $result->getTokenUsage();
        $metadata = $result->getMetadata();

        // Check if we exceeded token budget
        $totalTokens = $tokenUsage['total'] ?? 0;
        $budgetExceeded = $totalTokens > $config['maxTokenBudget'];

        // Collect comprehensive metrics
        $executionMetrics = [
            'profile' => $profile,
            'task_preview' => substr($task, 0, 100),
            'success' => $result->isSuccess(),
            'final_score' => $metadata['final_score'],
            'refinements_performed' => count($metadata['reflections']),
            'max_refinements' => $config['maxRefinements'],
            'threshold_met' => $metadata['final_score'] >= $config['qualityThreshold'],
            'duration_seconds' => round($duration, 2),
            'tokens_used' => $totalTokens,
            'token_budget' => $config['maxTokenBudget'],
            'budget_exceeded' => $budgetExceeded,
            'iterations' => $result->getIterations(),
            'refinement_history' => $refinementMetrics,
        ];

        $this->metrics[] = $executionMetrics;

        return [
            'result' => $result,
            'metrics' => $executionMetrics,
            'profile' => $profile,
        ];
    }

    /**
     * Get profile recommendations based on task type
     */
    public function getRecommendedProfile(string $taskType): string
    {
        return match(strtolower($taskType)) {
            'legal', 'medical', 'financial', 'security' => 'critical',
            'user_facing', 'public_content', 'api_response' => 'production',
            'internal_doc', 'analysis', 'report' => 'standard',
            'prototype', 'draft', 'internal_note' => 'draft',
            default => 'standard',
        };
    }

    /**
     * Get aggregate metrics across all executions
     */
    public function getAggregateMetrics(): array
    {
        if (empty($this->metrics)) {
            return [];
        }

        $totalTasks = count($this->metrics);
        $successfulTasks = count(array_filter($this->metrics, fn($m) => $m['success']));
        $thresholdMet = count(array_filter($this->metrics, fn($m) => $m['threshold_met']));
        
        return [
            'total_tasks' => $totalTasks,
            'successful_tasks' => $successfulTasks,
            'success_rate' => round(($successfulTasks / $totalTasks) * 100, 1),
            'threshold_met_rate' => round(($thresholdMet / $totalTasks) * 100, 1),
            'avg_final_score' => round(array_sum(array_column($this->metrics, 'final_score')) / $totalTasks, 2),
            'avg_refinements' => round(array_sum(array_column($this->metrics, 'refinements_performed')) / $totalTasks, 2),
            'avg_duration' => round(array_sum(array_column($this->metrics, 'duration_seconds')) / $totalTasks, 2),
            'total_tokens' => array_sum(array_column($this->metrics, 'tokens_used')),
            'avg_tokens' => round(array_sum(array_column($this->metrics, 'tokens_used')) / $totalTasks, 0),
            'budget_exceeded_count' => count(array_filter($this->metrics, fn($m) => $m['budget_exceeded'])),
        ];
    }

    /**
     * Print detailed metrics report
     */
    public function printReport(): void
    {
        $aggregate = $this->getAggregateMetrics();
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "REFLECTION SYSTEM METRICS REPORT\n";
        echo str_repeat("=", 80) . "\n\n";

        echo "Aggregate Metrics:\n";
        echo "  - Total Tasks: {$aggregate['total_tasks']}\n";
        echo "  - Success Rate: {$aggregate['success_rate']}%\n";
        echo "  - Threshold Met: {$aggregate['threshold_met_rate']}%\n";
        echo "  - Avg Final Score: {$aggregate['avg_final_score']}/10\n";
        echo "  - Avg Refinements: {$aggregate['avg_refinements']}\n";
        echo "  - Avg Duration: {$aggregate['avg_duration']}s\n";
        echo "  - Total Tokens: " . number_format($aggregate['total_tokens']) . "\n";
        echo "  - Avg Tokens: " . number_format($aggregate['avg_tokens']) . "\n";
        echo "  - Budget Exceeded: {$aggregate['budget_exceeded_count']} tasks\n\n";

        echo "Task Breakdown:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-12s | %-10s | %-10s | %-10s | %-10s | %s\n",
            'Profile', 'Score', 'Refine', 'Duration', 'Tokens', 'Threshold'
        );
        echo str_repeat("-", 80) . "\n";

        foreach ($this->metrics as $m) {
            printf("%-12s | %8s/10 | %10d | %8ss | %10s | %s\n",
                $m['profile'],
                $m['final_score'],
                $m['refinements_performed'],
                $m['duration_seconds'],
                number_format($m['tokens_used']),
                $m['threshold_met'] ? '✅' : '❌'
            );
        }
    }
}

/**
 * Demonstration: Using the Production Reflection System
 */
$orchestrator = new ReflectionOrchestrator($client);

/**
 * Task 1: User-Facing Content (Production Profile)
 */
echo "Task 1: User-Facing Content\n";
echo str_repeat("-", 80) . "\n";

$task1 = 'Write a clear, friendly error message for when a user tries to upload a file ' .
         'that exceeds the 10MB size limit. Explain the limit and suggest solutions.';

$result1 = $orchestrator->execute(
    task: $task1,
    profile: 'production',
    criteria: 'clarity, helpfulness, tone appropriateness, and actionable guidance',
    systemPrompt: 'You are a UX writer creating user-friendly error messages.'
);

echo "\nResult:\n{$result1['result']->getAnswer()}\n";
echo "\nScore: {$result1['metrics']['final_score']}/10\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * Task 2: Internal Documentation (Standard Profile)
 */
echo "Task 2: Internal Documentation\n";
echo str_repeat("-", 80) . "\n";

$task2 = 'Document the internal API endpoint POST /api/users for our team wiki. ' .
         'Include required parameters, response format, and example request.';

$result2 = $orchestrator->execute(
    task: $task2,
    profile: 'standard',
    criteria: 'completeness, accuracy, and clarity for developers',
    systemPrompt: 'You are documenting internal APIs for the development team.'
);

echo "\nResult preview:\n" . substr($result2['result']->getAnswer(), 0, 300) . "...\n";
echo "\nScore: {$result2['metrics']['final_score']}/10\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * Task 3: Quick Draft (Draft Profile)
 */
echo "Task 3: Quick Draft\n";
echo str_repeat("-", 80) . "\n";

$task3 = 'Write a quick summary of the main differences between GET and POST HTTP methods.';

$result3 = $orchestrator->execute(
    task: $task3,
    profile: 'draft',
    systemPrompt: 'You are creating a quick reference note.'
);

echo "\nResult:\n{$result3['result']->getAnswer()}\n";
echo "\nScore: {$result3['metrics']['final_score']}/10\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * Print comprehensive report
 */
$orchestrator->printReport();

/**
 * Profile Recommendation Demo
 */
echo "\n\nProfile Recommendations:\n";
echo str_repeat("-", 80) . "\n";

$taskTypes = ['legal', 'user_facing', 'internal_doc', 'prototype', 'security'];
foreach ($taskTypes as $type) {
    $recommended = $orchestrator->getRecommendedProfile($type);
    echo sprintf("%-20s → %s\n", $type, $recommended);
}

echo "\n✅ Production reflection system complete!\n";
echo "\n💡 Production Features:\n";
echo "   - Quality profiles for different task criticality\n";
echo "   - Token budget controls to prevent cost overruns\n";
echo "   - Comprehensive metrics collection\n";
echo "   - Aggregate reporting for optimization\n";
echo "   - Profile recommendations based on task type\n";
