<?php

declare(strict_types=1);

/**
 * 04 - Platform Orchestrator
 * 
 * Master coordinator that routes tasks, selects agents, manages execution,
 * and aggregates results from the entire agentic AI platform.
 */

// Load autoloader - use the claude-php-agent repo
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';


use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Load dependencies from previous examples
require_once __DIR__ . '/01-tool-registry-system.php';
require_once __DIR__ . '/02-memory-rag-integration.php';
require_once __DIR__ . '/03-agent-hub-registry.php';

/**
 * Platform Orchestrator - Master coordinator for the platform
 */
class PlatformOrchestrator
{
    private AgentHub $agentHub;
    private ToolRegistry $toolRegistry;
    private MemoryRAGSystem $memory;
    private array $executionLog = [];

    public function __construct(
        AgentHub $agentHub,
        ToolRegistry $toolRegistry,
        MemoryRAGSystem $memory
    ) {
        $this->agentHub = $agentHub;
        $this->toolRegistry = $toolRegistry;
        $this->memory = $memory;
    }

    /**
     * Execute a task using the platform
     */
    public function executeTask(
        string $taskDescription,
        string $sessionId,
        array $options = []
    ): array {
        $executionId = uniqid('exec_', true);
        $startTime = microtime(true);
        
        $this->log($executionId, 'started', [
            'task' => $taskDescription,
            'session' => $sessionId,
        ]);
        
        try {
            // 1. Analyze task
            $taskAnalysis = $this->analyzeTask($taskDescription);
            $this->log($executionId, 'analyzed', $taskAnalysis);
            
            // 2. Retrieve relevant context
            $context = $this->memory->retrieveContext(
                $taskDescription,
                $sessionId,
                maxResults: 3
            );
            $this->log($executionId, 'context_retrieved', [
                'conversation_items' => count($context['conversation']['items'] ?? []),
                'knowledge_items' => count($context['knowledge']['items'] ?? []),
            ]);
            
            // 3. Select appropriate agent
            $agentSelection = $this->agentHub->selectAgent(
                $taskAnalysis['type'],
                $taskAnalysis['requirements']
            );
            
            if (!$agentSelection) {
                throw new \RuntimeException('No suitable agent available');
            }
            
            $this->log($executionId, 'agent_selected', [
                'agent_id' => $agentSelection['id'],
                'score' => $agentSelection['score'],
            ]);
            
            // 4. Get tools for this agent
            $tools = $this->toolRegistry->getToolsForAgent($agentSelection['id']);
            
            // 5. Configure agent with tools
            $agent = $agentSelection['agent'];
            foreach ($tools as $tool) {
                $agent->withTool($tool);
            }
            
            // 6. Build enriched prompt with context
            $enrichedPrompt = $this->buildPromptWithContext(
                $taskDescription,
                $context
            );
            
            // 7. Execute
            $this->log($executionId, 'executing', [
                'prompt_length' => strlen($enrichedPrompt),
            ]);
            
            $result = $agent->run($enrichedPrompt);
            
            // 8. Store in memory
            $this->memory->storeConversation(
                $sessionId,
                'user',
                $taskDescription
            );
            $this->memory->storeConversation(
                $sessionId,
                'assistant',
                $result->getAnswer()
            );
            
            // 9. Track performance
            $duration = microtime(true) - $startTime;
            $cost = $result->getTotalTokens() * 0.000015; // Estimate
            
            $this->agentHub->trackPerformance(
                $agentSelection['id'],
                true,
                $duration,
                $cost
            );
            
            // Track tool usage
            foreach ($result->getToolCalls() as $toolCall) {
                $this->toolRegistry->trackUsage(
                    $toolCall['tool_name'],
                    true,
                    0.1
                );
            }
            
            $this->log($executionId, 'completed', [
                'duration' => $duration,
                'tokens' => $result->getTotalTokens(),
                'tool_calls' => count($result->getToolCalls()),
            ]);
            
            return [
                'success' => true,
                'execution_id' => $executionId,
                'result' => $result->getAnswer(),
                'metadata' => [
                    'agent_used' => $agentSelection['id'],
                    'duration' => $duration,
                    'tokens' => $result->getTotalTokens(),
                    'tool_calls' => count($result->getToolCalls()),
                    'cost' => $cost,
                ],
            ];
            
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            $this->log($executionId, 'failed', [
                'error' => $e->getMessage(),
                'duration' => $duration,
            ]);
            
            return [
                'success' => false,
                'execution_id' => $executionId,
                'error' => $e->getMessage(),
                'metadata' => [
                    'duration' => $duration,
                ],
            ];
        }
    }

    /**
     * Execute multi-step workflow
     */
    public function executeWorkflow(
        array $tasks,
        string $sessionId
    ): array {
        $workflowId = uniqid('workflow_', true);
        $results = [];
        $startTime = microtime(true);
        
        echo "🔄 Starting workflow {$workflowId} with " . count($tasks) . " tasks\n\n";
        
        foreach ($tasks as $index => $task) {
            $taskNum = $index + 1;
            echo "  Task {$taskNum}/" . count($tasks) . ": {$task['description']}\n";
            
            $result = $this->executeTask(
                $task['description'],
                $sessionId,
                $task['options'] ?? []
            );
            
            $results[] = $result;
            
            if ($result['success']) {
                echo "    ✅ Completed in " . number_format($result['metadata']['duration'], 2) . "s\n";
            } else {
                echo "    ❌ Failed: {$result['error']}\n";
                
                // Stop on first failure if not configured to continue
                if (!($task['continue_on_failure'] ?? false)) {
                    echo "    Stopping workflow due to failure\n";
                    break;
                }
            }
            
            echo "\n";
        }
        
        $duration = microtime(true) - $startTime;
        $successful = count(array_filter($results, fn($r) => $r['success']));
        
        return [
            'workflow_id' => $workflowId,
            'workflow_completed' => true,
            'total_tasks' => count($tasks),
            'successful_tasks' => $successful,
            'failed_tasks' => count($tasks) - $successful,
            'total_duration' => $duration,
            'results' => $results,
        ];
    }

    /**
     * Analyze task to determine type and requirements
     */
    private function analyzeTask(string $task): array
    {
        $task = strtolower($task);
        
        // Code-related tasks
        if (preg_match('/\b(code|implement|function|class|refactor|debug)\b/', $task)) {
            return [
                'type' => 'code_generation',
                'requirements' => ['programming'],
                'complexity' => 'medium',
            ];
        }
        
        // Research tasks
        if (preg_match('/\b(research|find|search|discover|investigate)\b/', $task)) {
            return [
                'type' => 'research',
                'requirements' => ['web_search', 'analysis'],
                'complexity' => 'medium',
            ];
        }
        
        // QA tasks
        if (preg_match('/\b(review|check|test|validate|verify)\b/', $task)) {
            return [
                'type' => 'quality_assurance',
                'requirements' => ['validation'],
                'complexity' => 'low',
            ];
        }
        
        // Analysis tasks
        if (preg_match('/\b(analyze|compare|evaluate|assess)\b/', $task)) {
            return [
                'type' => 'analysis',
                'requirements' => ['data_analysis'],
                'complexity' => 'medium',
            ];
        }
        
        // Default to general
        return [
            'type' => 'general',
            'requirements' => [],
            'complexity' => 'low',
        ];
    }

    /**
     * Build prompt enriched with context
     */
    private function buildPromptWithContext(
        string $task,
        array $context
    ): string {
        $prompt = $task;
        
        // Add conversation context
        if (!empty($context['conversation']['items'])) {
            $prompt .= "\n\n## Recent Conversation:\n";
            foreach ($context['conversation']['items'] as $turn) {
                $role = ucfirst($turn['role']);
                $preview = substr($turn['content'], 0, 100);
                $prompt .= "{$role}: {$preview}...\n";
            }
        }
        
        // Add knowledge context
        if (!empty($context['knowledge']['items'])) {
            $prompt .= "\n\n## Relevant Knowledge:\n";
            foreach ($context['knowledge']['items'] as $item) {
                $preview = substr($item['content'], 0, 150);
                $prompt .= "• {$preview}...\n";
            }
        }
        
        return $prompt;
    }

    private function log(
        string $executionId,
        string $event,
        array $data
    ): void {
        $this->executionLog[] = [
            'execution_id' => $executionId,
            'event' => $event,
            'data' => $data,
            'timestamp' => microtime(true),
        ];
    }

    public function getExecutionLog(?string $executionId = null): array
    {
        if ($executionId) {
            return array_filter(
                $this->executionLog,
                fn($log) => $log['execution_id'] === $executionId
            );
        }
        
        return $this->executionLog;
    }

    public function getStats(): array
    {
        $total = count($this->executionLog);
        $events = array_count_values(
            array_column($this->executionLog, 'event')
        );
        
        return [
            'total_events' => $total,
            'event_breakdown' => $events,
            'started' => $events['started'] ?? 0,
            'completed' => $events['completed'] ?? 0,
            'failed' => $events['failed'] ?? 0,
        ];
    }
}

// Example usage
if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Please set ANTHROPIC_API_KEY environment variable\n");
}

echo "=== Platform Orchestrator ===\n\n";

// Initialize components
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$agentHub = new AgentHub();
$toolRegistry = new ToolRegistry();
$memorySystem = new MemoryRAGSystem();

// Register tools
$calculator = Tool::create('calculate')
    ->description('Perform calculations')
    ->parameter('expression', 'string', 'Math expression')
    ->required('expression')
    ->handler(fn($input) => eval("return {$input['expression']};"));

$toolRegistry->register($calculator);

// Register agents
$generalAgent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant.')
    ->withTool($calculator);

$agentHub->registerAgent(
    'general_agent',
    $generalAgent,
    ['general', 'calculation', 'conversation'],
    []
);

$codeAgent = Agent::create($client)
    ->withSystemPrompt('You are an expert code generator.');

$agentHub->registerAgent(
    'code_generator',
    $codeAgent,
    ['code_generation', 'refactoring'],
    ['php', 'python']
);

// Create orchestrator
$orchestrator = new PlatformOrchestrator(
    $agentHub,
    $toolRegistry,
    $memorySystem
);

echo "✅ Platform initialized\n\n";

// Test single task execution
echo "📝 Executing single task...\n\n";

$sessionId = 'session_' . uniqid();
$result = $orchestrator->executeTask(
    'What is 25 * 17 + 100?',
    $sessionId
);

if ($result['success']) {
    echo "✅ Task completed successfully!\n";
    echo "   Agent used: {$result['metadata']['agent_used']}\n";
    echo "   Duration: " . number_format($result['metadata']['duration'], 2) . "s\n";
    echo "   Tokens: {$result['metadata']['tokens']}\n";
    echo "   Cost: $" . number_format($result['metadata']['cost'], 4) . "\n";
    echo "   Result: {$result['result']}\n";
} else {
    echo "❌ Task failed: {$result['error']}\n";
}

echo "\n";

// Test workflow execution
echo "🔄 Executing multi-task workflow...\n\n";

$workflow = [
    [
        'description' => 'Calculate 150 * 23',
        'continue_on_failure' => true,
    ],
    [
        'description' => 'Write a PHP function to calculate factorial',
        'continue_on_failure' => true,
    ],
    [
        'description' => 'Explain what we just did',
        'continue_on_failure' => false,
    ],
];

$workflowResult = $orchestrator->executeWorkflow($workflow, $sessionId);

echo "📊 Workflow Results:\n";
echo "  Workflow ID: {$workflowResult['workflow_id']}\n";
echo "  Total tasks: {$workflowResult['total_tasks']}\n";
echo "  Successful: {$workflowResult['successful_tasks']}\n";
echo "  Failed: {$workflowResult['failed_tasks']}\n";
echo "  Total duration: " . number_format($workflowResult['total_duration'], 2) . "s\n";

echo "\n";

// Display orchestrator statistics
echo "📊 Orchestrator Statistics:\n";
$stats = $orchestrator->getStats();
echo "  Total events: {$stats['total_events']}\n";
echo "  Tasks started: {$stats['started']}\n";
echo "  Tasks completed: {$stats['completed']}\n";
echo "  Tasks failed: {$stats['failed']}\n";

echo "\n✅ Platform Orchestrator demonstration complete!\n";
echo "\n💡 Key features:\n";
echo "   • Task analysis and classification\n";
echo "   • Intelligent agent selection\n";
echo "   • Context-aware execution\n";
echo "   • Multi-step workflows\n";
echo "   • Comprehensive logging\n";
echo "   • Performance tracking\n";
echo "\n💡 Production enhancements:\n";
echo "   • Add task queue for async execution\n";
echo "   • Implement parallel task execution\n";
echo "   • Add circuit breakers\n";
echo "   • Implement retry logic\n";
echo "   • Add distributed tracing\n";
