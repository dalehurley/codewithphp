<?php

declare(strict_types=1);

/**
 * 03 - Agent Hub & Registry
 * 
 * Registry of specialized agents with capability profiles, performance tracking,
 * and intelligent agent selection for task routing.
 */

// Load autoloader - use the claude-php-agent repo
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';


use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

/**
 * Agent Hub - Registry of specialized agents
 */
class AgentHub
{
    private array $agents = [];
    private array $profiles = [];
    private array $performanceStats = [];

    public function registerAgent(
        string $id,
        Agent $agent,
        array $capabilities,
        array $specializations = []
    ): void {
        $this->agents[$id] = $agent;
        
        $this->profiles[$id] = [
            'id' => $id,
            'capabilities' => $capabilities,
            'specializations' => $specializations,
            'registered_at' => time(),
            'status' => 'available',
        ];
        
        $this->performanceStats[$id] = [
            'tasks_completed' => 0,
            'tasks_failed' => 0,
            'avg_latency' => 0,
            'total_cost' => 0,
        ];
    }

    public function selectAgent(
        string $taskType,
        array $requirements = []
    ): ?array {
        $scores = [];
        
        foreach ($this->profiles as $id => $profile) {
            if ($profile['status'] !== 'available') {
                continue;
            }
            
            $score = $this->scoreAgent($id, $taskType, $requirements);
            
            if ($score > 0) {
                $scores[$id] = $score;
            }
        }
        
        if (empty($scores)) {
            return null;
        }
        
        // Select agent with highest score
        arsort($scores);
        $selectedId = array_key_first($scores);
        
        return [
            'id' => $selectedId,
            'agent' => $this->agents[$selectedId],
            'score' => $scores[$selectedId],
            'profile' => $this->profiles[$selectedId],
        ];
    }

    private function scoreAgent(
        string $agentId,
        string $taskType,
        array $requirements
    ): float {
        $profile = $this->profiles[$agentId];
        $stats = $this->performanceStats[$agentId];
        
        $score = 0;
        
        // Capability match (50 points)
        if (in_array($taskType, $profile['capabilities'])) {
            $score += 50;
        }
        
        // Specialization bonus (20 points per match)
        foreach ($requirements as $requirement) {
            if (in_array($requirement, $profile['specializations'])) {
                $score += 20;
            }
        }
        
        // Performance bonus (up to 30 points)
        if ($stats['tasks_completed'] > 0) {
            $successRate = $stats['tasks_completed'] 
                / ($stats['tasks_completed'] + $stats['tasks_failed']);
            $score += $successRate * 30;
        }
        
        return $score;
    }

    public function trackPerformance(
        string $agentId,
        bool $success,
        float $latency,
        float $cost
    ): void {
        if (!isset($this->performanceStats[$agentId])) {
            return;
        }
        
        $stats = &$this->performanceStats[$agentId];
        
        if ($success) {
            $stats['tasks_completed']++;
        } else {
            $stats['tasks_failed']++;
        }
        
        // Update moving average for latency
        $total = $stats['tasks_completed'] + $stats['tasks_failed'];
        $stats['avg_latency'] = (
            ($stats['avg_latency'] * ($total - 1)) + $latency
        ) / $total;
        
        $stats['total_cost'] += $cost;
    }

    public function getAgent(string $id): ?Agent
    {
        return $this->agents[$id] ?? null;
    }

    public function listAgents(?string $capability = null): array
    {
        if (!$capability) {
            return $this->profiles;
        }
        
        return array_filter(
            $this->profiles,
            fn($p) => in_array($capability, $p['capabilities'])
        );
    }

    public function getPerformanceStats(string $agentId): array
    {
        return $this->performanceStats[$agentId] ?? [];
    }

    public function setAgentStatus(string $agentId, string $status): void
    {
        if (isset($this->profiles[$agentId])) {
            $this->profiles[$agentId]['status'] = $status;
        }
    }
}

// Example usage
if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Please set ANTHROPIC_API_KEY environment variable\n");
}

echo "=== Agent Hub & Registry ===\n\n";

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create agent hub
$hub = new AgentHub();

echo "🤖 Creating specialized agents...\n\n";

// Create specialized tools
$codeAnalysisTool = Tool::create('analyze_code')
    ->description('Analyze code for quality and issues')
    ->parameter('code', 'string', 'Code to analyze')
    ->required('code')
    ->handler(function (array $input) {
        $code = $input['code'];
        $lines = count(explode("\n", $code));
        $complexity = strlen($code) > 500 ? 'high' : 'low';
        
        return [
            'lines' => $lines,
            'complexity' => $complexity,
            'issues' => [],
            'quality_score' => 85,
        ];
    });

$searchTool = Tool::create('web_search')
    ->description('Search the web for information')
    ->parameter('query', 'string', 'Search query')
    ->required('query')
    ->handler(function (array $input) {
        return [
            'results' => [
                ['title' => 'Result 1', 'url' => 'https://example.com/1'],
                ['title' => 'Result 2', 'url' => 'https://example.com/2'],
            ],
            'count' => 2,
        ];
    });

// 1. Code Generation Agent
$codeAgent = Agent::create($client)
    ->withSystemPrompt(
        'You are an expert code generator. Write clean, efficient, ' .
        'well-documented code following best practices.'
    )
    ->withTool($codeAnalysisTool);

$hub->registerAgent(
    'code_generator',
    $codeAgent,
    ['code_generation', 'refactoring', 'debugging'],
    ['php', 'python', 'javascript', 'testing']
);

echo "✅ Registered Code Generator Agent\n";
echo "   Capabilities: code_generation, refactoring, debugging\n";
echo "   Specializations: PHP, Python, JavaScript, Testing\n\n";

// 2. Research Agent
$researchAgent = Agent::create($client)
    ->withSystemPrompt(
        'You are a research specialist. Find accurate, relevant ' .
        'information and synthesize it into clear insights.'
    )
    ->withTool($searchTool);

$hub->registerAgent(
    'researcher',
    $researchAgent,
    ['research', 'analysis', 'fact_checking'],
    ['web_search', 'data_analysis', 'summarization']
);

echo "✅ Registered Research Agent\n";
echo "   Capabilities: research, analysis, fact_checking\n";
echo "   Specializations: Web search, Data analysis, Summarization\n\n";

// 3. QA Agent
$qaAgent = Agent::create($client)
    ->withSystemPrompt(
        'You are a quality assurance specialist. Review work for ' .
        'accuracy, completeness, and adherence to standards.'
    );

$hub->registerAgent(
    'qa_agent',
    $qaAgent,
    ['quality_assurance', 'testing', 'validation'],
    ['code_review', 'test_design', 'documentation_review']
);

echo "✅ Registered QA Agent\n";
echo "   Capabilities: quality_assurance, testing, validation\n";
echo "   Specializations: Code review, Test design, Docs review\n\n";

// 4. General Purpose Agent
$generalAgent = Agent::create($client)
    ->withSystemPrompt(
        'You are a helpful general-purpose assistant. Handle a wide ' .
        'variety of tasks with competence and clarity.'
    );

$hub->registerAgent(
    'general_agent',
    $generalAgent,
    ['general', 'conversation', 'help'],
    []
);

echo "✅ Registered General Agent\n";
echo "   Capabilities: general, conversation, help\n\n";

// List all agents
echo "📋 Registered agents:\n";
foreach ($hub->listAgents() as $profile) {
    echo "  • {$profile['id']} ({$profile['status']})\n";
    echo "    Capabilities: " . implode(', ', $profile['capabilities']) . "\n";
}
echo "\n";

// Test agent selection
echo "🎯 Testing intelligent agent selection...\n\n";

$testTasks = [
    [
        'type' => 'code_generation',
        'requirements' => ['php'],
        'description' => 'Write a PHP function',
    ],
    [
        'type' => 'research',
        'requirements' => ['web_search'],
        'description' => 'Find information about AI',
    ],
    [
        'type' => 'quality_assurance',
        'requirements' => ['code_review'],
        'description' => 'Review code quality',
    ],
    [
        'type' => 'general',
        'requirements' => [],
        'description' => 'Answer a general question',
    ],
];

foreach ($testTasks as $task) {
    echo "Task: {$task['description']}\n";
    echo "  Type: {$task['type']}\n";
    echo "  Requirements: " . implode(', ', $task['requirements']) . "\n";
    
    $selected = $hub->selectAgent($task['type'], $task['requirements']);
    
    if ($selected) {
        echo "  ✅ Selected: {$selected['id']} (score: {$selected['score']})\n";
        
        // Simulate task execution
        $startTime = microtime(true);
        sleep(1); // Simulate work
        $duration = microtime(true) - $startTime;
        
        // Track performance
        $hub->trackPerformance(
            $selected['id'],
            true,
            $duration,
            0.01
        );
        
        echo "  Executed in " . number_format($duration, 2) . "s\n";
    } else {
        echo "  ❌ No suitable agent found\n";
    }
    
    echo "\n";
}

// Display performance statistics
echo "📊 Performance Statistics:\n";
foreach ($hub->listAgents() as $profile) {
    $stats = $hub->getPerformanceStats($profile['id']);
    
    if ($stats['tasks_completed'] > 0 || $stats['tasks_failed'] > 0) {
        $total = $stats['tasks_completed'] + $stats['tasks_failed'];
        $successRate = ($stats['tasks_completed'] / $total) * 100;
        
        echo "  {$profile['id']}:\n";
        echo "    Tasks completed: {$stats['tasks_completed']}\n";
        echo "    Tasks failed: {$stats['tasks_failed']}\n";
        echo "    Success rate: " . number_format($successRate, 1) . "%\n";
        echo "    Avg latency: " . number_format($stats['avg_latency'], 2) . "s\n";
        echo "    Total cost: $" . number_format($stats['total_cost'], 4) . "\n";
    }
}

echo "\n";

// Test capability filtering
echo "🔍 Filtering agents by capability...\n";
$codeAgents = $hub->listAgents('code_generation');
echo "  Agents with 'code_generation' capability: " . count($codeAgents) . "\n";
foreach ($codeAgents as $profile) {
    echo "    • {$profile['id']}\n";
}

echo "\n✅ Agent Hub & Registry demonstration complete!\n";
echo "\n💡 Key features:\n";
echo "   • Specialized agent registration\n";
echo "   • Capability-based agent profiles\n";
echo "   • Intelligent agent selection\n";
echo "   • Performance tracking\n";
echo "   • Scoring algorithm for matching\n";
echo "\n💡 Production enhancements:\n";
echo "   • Add agent health checks\n";
echo "   • Implement load balancing\n";
echo "   • Add agent versioning\n";
echo "   • Implement failover strategies\n";
echo "   • Add cost-based selection\n";
