<?php

declare(strict_types=1);

/**
 * 07 - Complete Agentic AI Platform
 * 
 * Full integration of all platform components:
 * - Tool Registry
 * - Memory & RAG System
 * - Agent Hub
 * - Platform Orchestrator
 * - Evaluation & Monitoring
 * - Admin Management API
 * 
 * This is a production-ready agentic AI platform.
 */

// Load autoloader - use the claude-php-agent repo
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';


use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Load all components
require_once __DIR__ . '/01-tool-registry-system.php';
require_once __DIR__ . '/02-memory-rag-integration.php';
require_once __DIR__ . '/03-agent-hub-registry.php';
require_once __DIR__ . '/04-platform-orchestrator.php';
require_once __DIR__ . '/05-evaluation-monitoring-stack.php';
require_once __DIR__ . '/06-admin-management-api.php';

/**
 * Complete Agentic AI Platform
 */
class AgenticAIPlatform
{
    private ClaudePhp $client;
    private AgentHub $agentHub;
    private ToolRegistry $toolRegistry;
    private MemoryRAGSystem $memory;
    private PlatformOrchestrator $orchestrator;
    private EvaluationMonitoringStack $monitoring;
    private AdminManagementAPI $admin;
    private array $platformStats = [
        'started_at' => 0,
        'total_tasks' => 0,
        'successful_tasks' => 0,
        'failed_tasks' => 0,
    ];

    public function __construct(string $apiKey)
    {
        $this->client = new ClaudePhp(apiKey: $apiKey);
        
        // Initialize components
        $this->agentHub = new AgentHub();
        $this->toolRegistry = new ToolRegistry();
        $this->memory = new MemoryRAGSystem();
        $this->monitoring = new EvaluationMonitoringStack();
        
        $this->orchestrator = new PlatformOrchestrator(
            $this->agentHub,
            $this->toolRegistry,
            $this->memory
        );
        
        $this->admin = new AdminManagementAPI(
            $this->agentHub,
            $this->toolRegistry,
            $this->monitoring
        );
        
        $this->platformStats['started_at'] = time();
        
        // Bootstrap platform
        $this->bootstrap();
    }

    /**
     * Bootstrap platform with tools and agents
     */
    private function bootstrap(): void
    {
        echo "🚀 Bootstrapping Agentic AI Platform...\n\n";
        
        // Register tools
        $this->registerTools();
        
        // Register agents
        $this->registerAgents();
        
        // Store initial knowledge
        $this->seedKnowledge();
        
        echo "✅ Platform initialized successfully!\n";
        echo "   Version: 1.0.0\n";
        echo "   Agents: " . count($this->agentHub->listAgents()) . "\n";
        echo "   Tools: " . count($this->toolRegistry->listTools()) . "\n";
        echo "   Status: Ready\n\n";
    }

    /**
     * Register platform tools
     */
    private function registerTools(): void
    {
        echo "🛠️  Registering tools...\n";
        
        // Calculator tool
        $calculator = Tool::create('calculate')
            ->description('Perform mathematical calculations')
            ->parameter('expression', 'string', 'Math expression to evaluate')
            ->required('expression')
            ->handler(function (array $input) {
                $expression = $input['expression'];
                if (!preg_match('/^[\d\s\+\-\*\/\(\)\.]+$/', $expression)) {
                    throw new Exception('Invalid expression');
                }
                return eval("return {$expression};");
            });
        
        $this->toolRegistry->register($calculator, [], 100);
        echo "   ✓ Calculator (all agents, 100 calls/hr)\n";
        
        // Weather tool
        $weather = Tool::create('get_weather')
            ->description('Get weather information')
            ->parameter('location', 'string', 'City name')
            ->required('location')
            ->handler(function (array $input) {
                $temps = [
                    'San Francisco' => 65,
                    'New York' => 72,
                    'London' => 58,
                    'Tokyo' => 75,
                ];
                $location = $input['location'];
                return [
                    'location' => $location,
                    'temperature' => $temps[$location] ?? 70,
                    'conditions' => 'Partly cloudy',
                ];
            });
        
        $this->toolRegistry->register($weather);
        echo "   ✓ Weather (all agents)\n";
        
        // Database query tool (admin only)
        $database = Tool::create('query_database')
            ->description('Query the database')
            ->parameter('query', 'string', 'SQL query')
            ->required('query')
            ->handler(function (array $input) {
                return [
                    'rows' => [
                        ['id' => 1, 'name' => 'Item A'],
                        ['id' => 2, 'name' => 'Item B'],
                    ],
                    'count' => 2,
                ];
            });
        
        $this->toolRegistry->register($database, ['admin_agent'], 10);
        echo "   ✓ Database (admin only, 10 calls/hr)\n";
        
        echo "\n";
    }

    /**
     * Register specialized agents
     */
    private function registerAgents(): void
    {
        echo "🤖 Registering agents...\n";
        
        // Code generation agent
        $codeAgent = Agent::create($this->client)
            ->withSystemPrompt(
                'You are an expert code generator. Write clean, efficient, ' .
                'well-documented code following best practices. Use PHP 8.4 features.'
            );
        
        $this->agentHub->registerAgent(
            'code_generator',
            $codeAgent,
            ['code_generation', 'refactoring', 'debugging'],
            ['php', 'python', 'javascript']
        );
        echo "   ✓ Code Generator (PHP, Python, JavaScript)\n";
        
        // Research agent
        $researchAgent = Agent::create($this->client)
            ->withSystemPrompt(
                'You are a research specialist. Find accurate, relevant ' .
                'information and synthesize it into clear insights.'
            );
        
        $this->agentHub->registerAgent(
            'researcher',
            $researchAgent,
            ['research', 'analysis', 'fact_checking'],
            ['web_search', 'data_analysis']
        );
        echo "   ✓ Researcher (Web search, Analysis)\n";
        
        // General purpose agent
        $generalAgent = Agent::create($this->client)
            ->withSystemPrompt(
                'You are a helpful assistant. Handle requests with competence and clarity.'
            );
        
        $this->agentHub->registerAgent(
            'general_agent',
            $generalAgent,
            ['general', 'conversation', 'calculation'],
            []
        );
        echo "   ✓ General Assistant\n";
        
        echo "\n";
    }

    /**
     * Seed initial knowledge base
     */
    private function seedKnowledge(): void
    {
        echo "🧠 Seeding knowledge base...\n";
        
        $knowledge = [
            "PHP 8.4 was released in November 2024 with property hooks and asymmetric visibility.",
            "The claude-php/claude-php-agent framework provides tools for building AI agents in PHP.",
            "Agentic AI systems can autonomously plan, execute, and adapt to achieve goals.",
            "Production AI systems require monitoring, evaluation, and continuous improvement.",
        ];
        
        foreach ($knowledge as $fact) {
            $this->memory->storeKnowledge($fact, [
                'type' => 'fact',
                'source' => 'bootstrap',
            ]);
        }
        
        echo "   ✓ Added " . count($knowledge) . " knowledge items\n\n";
    }

    /**
     * Execute a task on the platform
     */
    public function execute(
        string $task,
        string $sessionId = 'default'
    ): array {
        $this->platformStats['total_tasks']++;
        
        // Execute through orchestrator
        $result = $this->orchestrator->executeTask($task, $sessionId);
        
        // Evaluate result
        $evaluation = $this->monitoring->evaluate($result);
        
        // Record metrics
        $this->monitoring->recordMetric(
            'task_execution_time',
            $result['metadata']['duration'] ?? 0
        );
        
        // Track success/failure
        if ($result['success']) {
            $this->platformStats['successful_tasks']++;
        } else {
            $this->platformStats['failed_tasks']++;
        }
        
        return [
            'result' => $result,
            'evaluation' => $evaluation,
        ];
    }

    /**
     * Execute a multi-task workflow
     */
    public function executeWorkflow(
        array $tasks,
        string $sessionId = 'default'
    ): array {
        return $this->orchestrator->executeWorkflow($tasks, $sessionId);
    }

    /**
     * Get platform health and statistics
     */
    public function getHealth(): array
    {
        return $this->admin->getHealth();
    }

    /**
     * Get comprehensive platform statistics
     */
    public function getStats(): array
    {
        $uptime = time() - $this->platformStats['started_at'];
        $successRate = $this->platformStats['total_tasks'] > 0
            ? $this->platformStats['successful_tasks'] / $this->platformStats['total_tasks']
            : 0;
        
        return [
            'platform' => [
                'uptime_seconds' => $uptime,
                'total_tasks' => $this->platformStats['total_tasks'],
                'successful_tasks' => $this->platformStats['successful_tasks'],
                'failed_tasks' => $this->platformStats['failed_tasks'],
                'success_rate' => $successRate,
            ],
            'agents' => $this->agentHub->listAgents(),
            'tools' => $this->toolRegistry->listTools(),
            'memory' => $this->memory->getStats(),
            'monitoring' => $this->monitoring->getStats(),
        ];
    }

    /**
     * Get admin interface
     */
    public function admin(): AdminManagementAPI
    {
        return $this->admin;
    }
}

// ============================================================================
// Example Usage
// ============================================================================

if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Please set ANTHROPIC_API_KEY environment variable\n");
}

echo str_repeat('=', 70) . "\n";
echo "   AGENTIC AI PLATFORM - COMPLETE DEMONSTRATION\n";
echo str_repeat('=', 70) . "\n\n";

// Initialize platform
$platform = new AgenticAIPlatform(getenv('ANTHROPIC_API_KEY'));

// Create a session
$sessionId = 'demo_' . uniqid();

echo "📝 Session ID: {$sessionId}\n\n";

// Test 1: Simple calculation task
echo str_repeat('-', 70) . "\n";
echo "TEST 1: Simple Calculation\n";
echo str_repeat('-', 70) . "\n\n";

$result1 = $platform->execute('What is 25 * 17 + 100?', $sessionId);

if ($result1['result']['success']) {
    echo "✅ Result: {$result1['result']['result']}\n";
    echo "📊 Evaluation:\n";
    echo "   Quality: " . number_format($result1['evaluation']['scores']['quality'], 2) . "\n";
    echo "   Safety: " . number_format($result1['evaluation']['scores']['safety'], 2) . "\n";
    echo "   Performance: " . number_format($result1['evaluation']['scores']['performance'], 2) . "\n";
    echo "   Overall: " . number_format($result1['evaluation']['overall'], 2) . "\n";
}

echo "\n";

// Test 2: Code generation task
echo str_repeat('-', 70) . "\n";
echo "TEST 2: Code Generation\n";
echo str_repeat('-', 70) . "\n\n";

$result2 = $platform->execute(
    'Write a PHP function to calculate the Fibonacci sequence up to n terms',
    $sessionId
);

if ($result2['result']['success']) {
    echo "✅ Code generated successfully\n";
    echo "📝 Preview: " . substr($result2['result']['result'], 0, 100) . "...\n";
    echo "📊 Metadata:\n";
    echo "   Agent: {$result2['result']['metadata']['agent_used']}\n";
    echo "   Duration: " . number_format($result2['result']['metadata']['duration'], 2) . "s\n";
    echo "   Tokens: {$result2['result']['metadata']['tokens']}\n";
}

echo "\n";

// Test 3: Multi-task workflow
echo str_repeat('-', 70) . "\n";
echo "TEST 3: Multi-Task Workflow\n";
echo str_repeat('-', 70) . "\n\n";

$workflow = [
    ['description' => 'Calculate 150 * 23'],
    ['description' => 'Get weather for San Francisco'],
    ['description' => 'Summarize what we discussed'],
];

$workflowResult = $platform->executeWorkflow($workflow, $sessionId);

echo "📊 Workflow completed:\n";
echo "   Total tasks: {$workflowResult['total_tasks']}\n";
echo "   Successful: {$workflowResult['successful_tasks']}\n";
echo "   Duration: " . number_format($workflowResult['total_duration'], 2) . "s\n";

echo "\n";

// Display comprehensive statistics
echo str_repeat('=', 70) . "\n";
echo "PLATFORM STATISTICS\n";
echo str_repeat('=', 70) . "\n\n";

$stats = $platform->getStats();

echo "🏥 Platform Health:\n";
$health = $platform->getHealth();
echo "   Status: {$health['status']}\n";
echo "   Version: {$health['version']}\n\n";

echo "📊 Platform Metrics:\n";
echo "   Uptime: " . gmdate("H:i:s", $stats['platform']['uptime_seconds']) . "\n";
echo "   Total tasks: {$stats['platform']['total_tasks']}\n";
echo "   Success rate: " . number_format($stats['platform']['success_rate'] * 100, 1) . "%\n\n";

echo "🤖 Agents:\n";
foreach ($stats['agents'] as $agent) {
    echo "   • {$agent['id']} - {$agent['status']}\n";
}
echo "\n";

echo "🛠️ Tools:\n";
foreach ($stats['tools'] as $tool) {
    echo "   • {$tool['name']}\n";
}
echo "\n";

echo "🧠 Memory:\n";
echo "   Sessions: {$stats['memory']['conversation_sessions']}\n";
echo "   Knowledge items: {$stats['memory']['long_term_items']}\n\n";

echo "📈 Monitoring:\n";
echo "   Evaluations: {$stats['monitoring']['total_evaluations']}\n";
echo "   Pass rate: " . number_format($stats['monitoring']['pass_rate'] * 100, 1) . "%\n";
echo "   Avg quality: " . number_format($stats['monitoring']['avg_scores']['quality'], 2) . "\n";
echo "   Avg safety: " . number_format($stats['monitoring']['avg_scores']['safety'], 2) . "\n\n";

// Admin operations
echo str_repeat('=', 70) . "\n";
echo "ADMIN OPERATIONS\n";
echo str_repeat('=', 70) . "\n\n";

echo "🔍 Recent Evaluations:\n";
$evaluations = $platform->admin()->getEvaluations(3);
foreach ($evaluations as $eval) {
    $status = $eval['passed'] ? '✅' : '❌';
    echo "   {$status} {$eval['execution_id']}: " . number_format($eval['overall'], 2) . "\n";
}
echo "\n";

echo "🚨 Alerts:\n";
$alerts = $platform->admin()->getAlerts(limit: 5);
if (empty($alerts)) {
    echo "   No alerts\n";
} else {
    foreach ($alerts as $alert) {
        echo "   [{$alert['level']}] {$alert['message']}\n";
    }
}
echo "\n";

// Final summary
echo str_repeat('=', 70) . "\n";
echo "✅ PLATFORM DEMONSTRATION COMPLETE\n";
echo str_repeat('=', 70) . "\n\n";

echo "💡 What you built:\n";
echo "   ✓ Tool Registry with permissions and rate limiting\n";
echo "   ✓ Memory system with RAG for context-aware responses\n";
echo "   ✓ Agent Hub with intelligent agent selection\n";
echo "   ✓ Platform Orchestrator for task routing\n";
echo "   ✓ Evaluation & Monitoring for quality assurance\n";
echo "   ✓ Admin API for platform management\n";
echo "   ✓ Complete production-ready agentic AI platform\n\n";

echo "🚀 Next steps:\n";
echo "   • Add more specialized agents\n";
echo "   • Integrate with your services\n";
echo "   • Deploy to production\n";
echo "   • Add web UI dashboard\n";
echo "   • Scale with queue-based processing\n";
echo "   • Implement fine-tuning\n\n";

echo "📚 You've completed the Agentic AI for PHP Developers series!\n";
echo "   Thank you for building with claude-php/claude-php-agent\n\n";
