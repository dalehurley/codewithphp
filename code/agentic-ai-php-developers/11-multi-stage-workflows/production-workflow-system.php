#!/usr/bin/env php
<?php
/**
 * Production Workflow System Example
 *
 * Demonstrates a complete production-grade workflow orchestration
 * system with:
 * - Workflow registration and execution
 * - State management
 * - Error handling and recovery
 * - Monitoring and metrics
 * - Modular workflow composition
 *
 * Part of: Agentic AI for PHP Developers
 * Chapter 11: Multi-Stage Workflows and Agent Graphs
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Chains\SequentialChain;
use ClaudeAgents\Chains\ParallelChain;
use ClaudeAgents\Chains\TransformChain;
use ClaudeAgents\Prompts\PromptTemplate;
use ClaudeAgents\State\AgentState;
use ClaudeAgents\State\Goal;
use ClaudeAgents\State\GoalStatus;
use ClaudeAgents\State\StateManager;
use ClaudePhp\ClaudePhp;

// Load environment
$dotenv = __DIR__ . '/../../../.env';
if (file_exists($dotenv)) {
    $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException('ANTHROPIC_API_KEY not set');
$client = new ClaudePhp(apiKey: $apiKey);

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Production Workflow System                                    ║\n";
echo "║              Complete orchestration with state, monitoring, recovery       ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Workflow Result Class
// ============================================================================

class WorkflowResult
{
    private function __construct(
        public readonly bool $success,
        public readonly mixed $data,
        public readonly ?string $error,
        public readonly array $metrics
    ) {}
    
    public static function success($data, array $metrics = []): self
    {
        return new self(true, $data, null, $metrics);
    }
    
    public static function failure(string $error, array $metrics = []): self
    {
        return new self(false, null, $error, $metrics);
    }
    
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'error' => $this->error,
            'metrics' => $this->metrics,
        ];
    }
}

// ============================================================================
// Workflow Orchestrator Class
// ============================================================================

class WorkflowOrchestrator
{
    private array $workflows = [];
    private StateManager $stateManager;
    
    public function __construct(
        private readonly ClaudePhp $client,
        string $stateDir = __DIR__
    ) {
        $this->stateManager = new StateManager(
            stateFile: $stateDir . '/production_workflow_state.json',
            options: [
                'atomic_writes' => true,
                'backup_retention' => 5,
            ]
        );
    }
    
    /**
     * Register a workflow builder function
     */
    public function registerWorkflow(string $name, callable $builder): void
    {
        $this->workflows[$name] = $builder;
        echo "✓ Registered workflow: {$name}\n";
    }
    
    /**
     * Execute a registered workflow
     */
    public function execute(string $workflowName, array $input): WorkflowResult
    {
        if (!isset($this->workflows[$workflowName])) {
            return WorkflowResult::failure("Workflow not found: {$workflowName}");
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "EXECUTING WORKFLOW: {$workflowName}\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $startTime = microtime(true);
        
        // Load or create workflow state
        $state = $this->loadWorkflowState($workflowName, $input);
        
        try {
            // Build workflow using registered builder
            $builder = $this->workflows[$workflowName];
            $workflow = $builder($this->client);
            
            // Execute workflow
            echo "[ORCHESTRATOR] Starting workflow execution...\n";
            
            $result = $workflow->invoke($input);
            
            $duration = microtime(true) - $startTime;
            
            // Update state
            $state->storeData('result', $result);
            $state->storeData('completed_at', date('Y-m-d H:i:s'));
            $this->stateManager->save($state);
            
            echo "[ORCHESTRATOR] Workflow completed successfully\n";
            
            $metrics = [
                'duration' => $duration,
                'workflow' => $workflowName,
                'timestamp' => date('Y-m-d H:i:s'),
            ];
            
            return WorkflowResult::success($result, $metrics);
            
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            echo "[ORCHESTRATOR] Workflow failed: {$e->getMessage()}\n";
            
            // Save error state
            $state->storeData('error', $e->getMessage());
            $state->storeData('failed_at', date('Y-m-d H:i:s'));
            $this->stateManager->save($state);
            
            // Create backup for recovery
            $this->stateManager->createBackup();
            
            $metrics = [
                'duration' => $duration,
                'workflow' => $workflowName,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s'),
            ];
            
            return WorkflowResult::failure($e->getMessage(), $metrics);
        }
    }
    
    /**
     * Load or create workflow state
     */
    private function loadWorkflowState(string $workflowName, array $input): AgentState
    {
        $state = $this->stateManager->load();
        
        if ($state === null) {
            $state = AgentState::create(
                stateId: $workflowName . '-' . uniqid(),
                agentType: 'workflow-orchestrator'
            );
            
            $state->storeData('workflow_name', $workflowName);
            $state->storeData('input', $input);
            $state->storeData('created_at', date('Y-m-d H:i:s'));
            
            $this->stateManager->save($state);
        }
        
        return $state;
    }
    
    /**
     * List registered workflows
     */
    public function listWorkflows(): array
    {
        return array_keys($this->workflows);
    }
}

// ============================================================================
// Register Production Workflows
// ============================================================================

$orchestrator = new WorkflowOrchestrator($client);

echo "Registering production workflows...\n\n";

// Workflow 1: Content Analysis Pipeline
$orchestrator->registerWorkflow('content-analysis', function (ClaudePhp $client) {
    $extractChain = LLMChain::create($client)
        ->withPromptTemplate(PromptTemplate::create(
            'Extract key entities from: {text}'
        ))
        ->withMaxTokens(300);
    
    $analyzeChain = LLMChain::create($client)
        ->withPromptTemplate(PromptTemplate::create(
            'Analyze sentiment of: {entities}'
        ))
        ->withMaxTokens(300);
    
    $formatChain = TransformChain::create(function (array $input): array {
        return [
            'entities' => $input['entities'] ?? '',
            'sentiment' => $input['sentiment'] ?? '',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    });
    
    return SequentialChain::create()
        ->addChain('extract', $extractChain)
        ->addChain('analyze', $analyzeChain)
        ->addChain('format', $formatChain)
        ->mapOutput('extract', 'result', 'analyze', 'entities')
        ->mapOutput('extract', 'result', 'format', 'entities')
        ->mapOutput('analyze', 'result', 'format', 'sentiment');
});

// Workflow 2: Parallel Review System
$orchestrator->registerWorkflow('parallel-review', function (ClaudePhp $client) {
    $qualityChain = LLMChain::create($client)
        ->withPromptTemplate(PromptTemplate::create(
            'Assess quality (1-10): {content}'
        ))
        ->withMaxTokens(200);
    
    $safetyChain = LLMChain::create($client)
        ->withPromptTemplate(PromptTemplate::create(
            'Check for safety issues: {content}'
        ))
        ->withMaxTokens(200);
    
    $validateChain = TransformChain::create(function (array $input): array {
        $content = $input['content'] ?? '';
        return [
            'word_count' => str_word_count($content),
            'char_count' => strlen($content),
            'is_valid' => str_word_count($content) > 10,
        ];
    });
    
    return ParallelChain::create()
        ->addChain('quality', $qualityChain)
        ->addChain('safety', $safetyChain)
        ->addChain('validate', $validateChain)
        ->withAggregation('all');
});

echo "\n";

// ============================================================================
// Execute Production Workflows
// ============================================================================

echo "Available workflows: " . implode(', ', $orchestrator->listWorkflows()) . "\n\n";

// Test 1: Content Analysis
$sampleText = 'PHP 8.4 introduces property hooks, a game-changing feature for clean object-oriented code.';

echo "\n--- Test 1: Content Analysis Workflow ---\n";
$result1 = $orchestrator->execute('content-analysis', ['text' => $sampleText]);

if ($result1->success) {
    echo "\n✓ Workflow succeeded\n";
    echo "Results:\n";
    echo json_encode($result1->data['format'] ?? [], JSON_PRETTY_PRINT) . "\n";
    echo "\nMetrics:\n";
    echo "Duration: " . number_format($result1->metrics['duration'], 3) . "s\n";
} else {
    echo "\n✗ Workflow failed: {$result1->error}\n";
}

// Small delay between workflows
sleep(2);

// Test 2: Parallel Review
echo "\n--- Test 2: Parallel Review Workflow ---\n";
$result2 = $orchestrator->execute('parallel-review', ['content' => $sampleText]);

if ($result2->success) {
    echo "\n✓ Workflow succeeded\n";
    echo "Results:\n";
    $reviewResults = $result2->data['results'] ?? [];
    echo "- Quality: " . substr($reviewResults['quality']['result'] ?? 'N/A', 0, 100) . "\n";
    echo "- Safety: " . substr($reviewResults['safety']['result'] ?? 'N/A', 0, 100) . "\n";
    $validation = $reviewResults['validate'] ?? [];
    echo "- Validation: " . ($validation['is_valid'] ? 'PASS' : 'FAIL') . 
         " (words: " . ($validation['word_count'] ?? 0) . ")\n";
    echo "\nMetrics:\n";
    echo "Duration: " . number_format($result2->metrics['duration'], 3) . "s\n";
} else {
    echo "\n✗ Workflow failed: {$result2->error}\n";
}

// ============================================================================
// Production Features Summary
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "PRODUCTION FEATURES\n";
echo str_repeat("=", 80) . "\n";
echo "✓ Workflow Registration: Modular workflow definitions\n";
echo "✓ State Management: Persistent workflow state with backups\n";
echo "✓ Error Handling: Graceful failure with error capture\n";
echo "✓ Recovery: Automatic state backup on failures\n";
echo "✓ Metrics Tracking: Duration and execution metadata\n";
echo "✓ Typed Results: WorkflowResult for consistent responses\n";
echo "✓ Orchestration: Centralized workflow execution\n";
echo "\n";

// ============================================================================
// Key Takeaways
// ============================================================================

echo str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. Orchestrator Pattern: Centralized workflow management\n";
echo "2. Workflow Registry: Register and execute named workflows\n";
echo "3. State Persistence: Track workflow state across executions\n";
echo "4. Error Recovery: Backup and restore on failures\n";
echo "5. Result Typing: Consistent result format with WorkflowResult\n";
echo "6. Metrics Collection: Track performance and success\n";
echo "7. Modular Composition: Builder pattern for workflow creation\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
