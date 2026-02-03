<?php

declare(strict_types=1);

/**
 * 02: Agent Profiles and Registration
 *
 * This example demonstrates:
 * - Comprehensive agent profile design
 * - Registration of agents with different capabilities
 * - How profiles influence agent selection
 *
 * Profiles tell the AdaptiveAgentService about each agent's strengths,
 * complexity level, speed, and quality characteristics.
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
echo "Agent Profiles and Registration\n";
echo "=================================================================\n\n";

$client = new ClaudePhp(apiKey: $apiKey);

// =================================================================
// Create Diverse Specialized Agents
// =================================================================

echo "Creating specialized agents with distinct capabilities...\n\n";

// 1. React Agent - Tool usage specialist
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
]);

// 2. Reflection Agent - Quality specialist
$reflectionAgent = new ReflectionAgent($client, [
    'max_refinements' => 3,
    'quality_threshold' => 8,
]);

// 3. Chain of Thought Agent - Reasoning specialist
$cotAgent = new ChainOfThoughtAgent($client, [
    'mode' => 'zero_shot',
]);

// 4. RAG Agent - Knowledge specialist
$ragAgent = new RAGAgent($client);
$ragAgent->addDocument(
    'PHP Basics',
    'PHP is a server-side scripting language designed for web development. ' .
    'It was created by Rasmus Lerdorf in 1994. PHP stands for Hypertext Preprocessor. ' .
    'Variables start with $ symbol. Functions use the function keyword. ' .
    'Classes use the class keyword. PHP 8+ has modern features like property promotion, ' .
    'named arguments, match expressions, and union types.'
);
$ragAgent->addDocument(
    'PHP Frameworks',
    'Popular PHP frameworks include Laravel, Symfony, CodeIgniter, and Yii. ' .
    'Laravel is known for its elegant syntax and developer experience. ' .
    'Symfony provides reusable components and is highly modular. ' .
    'These frameworks follow MVC pattern and provide routing, ORM, and templating.'
);

echo "✓ Created 4 specialized agents\n\n";

// =================================================================
// Create Adaptive Service with Detailed Profiles
// =================================================================

echo "Creating Adaptive Agent Service...\n\n";

$service = new AdaptiveAgentService($client, [
    'max_attempts' => 3,
    'quality_threshold' => 7.0,
    'enable_reframing' => true,
    'enable_knn' => true,
    'history_store_path' => __DIR__ . '/storage/profile_history.json',
]);

// =================================================================
// Register React Agent - Tool Usage Specialist
// =================================================================

$service->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'strengths' => [
        'tool orchestration',
        'iterative problem solving',
        'multi-step workflows',
        'debugging and refinement',
    ],
    'best_for' => [
        'calculations',
        'API calls',
        'data processing',
        'multi-step tasks',
        'tasks requiring external tools',
    ],
    'complexity_level' => 'medium',  // Can handle medium complexity
    'speed' => 'medium',              // Moderate speed (tool calls add latency)
    'quality' => 'standard',          // Good quality, single pass
]);

echo "✓ Registered React Agent (tool usage specialist)\n";

// =================================================================
// Register Reflection Agent - Quality Specialist
// =================================================================

$service->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'strengths' => [
        'quality refinement',
        'self-improvement through critique',
        'careful thinking',
        'error detection and correction',
    ],
    'best_for' => [
        'writing tasks',
        'code generation',
        'critical outputs',
        'content where quality matters',
        'tasks requiring polish',
    ],
    'complexity_level' => 'medium',  // Can handle medium complexity
    'speed' => 'slow',                // Slower (multiple refinement passes)
    'quality' => 'high',              // High quality output
]);

echo "✓ Registered Reflection Agent (quality specialist)\n";

// =================================================================
// Register Chain of Thought Agent - Reasoning Specialist
// =================================================================

$service->registerAgent('cot', $cotAgent, [
    'type' => 'cot',
    'strengths' => [
        'step-by-step reasoning',
        'logical explanations',
        'transparency in thinking',
        'problem decomposition',
    ],
    'best_for' => [
        'math problems',
        'logic puzzles',
        'explanations',
        'teaching scenarios',
        'tasks requiring clear reasoning',
    ],
    'complexity_level' => 'medium',  // Good for medium complexity
    'speed' => 'fast',                // Fast (single pass with reasoning)
    'quality' => 'standard',          // Good quality, clear reasoning
]);

echo "✓ Registered Chain of Thought Agent (reasoning specialist)\n";

// =================================================================
// Register RAG Agent - Knowledge Specialist
// =================================================================

$service->registerAgent('rag', $ragAgent, [
    'type' => 'rag',
    'strengths' => [
        'knowledge grounding',
        'source attribution',
        'fact accuracy',
        'document retrieval',
    ],
    'best_for' => [
        'Q&A from documents',
        'documentation queries',
        'fact-based tasks',
        'research from known sources',
        'knowledge base lookups',
    ],
    'complexity_level' => 'simple',   // Best for simple lookups
    'speed' => 'fast',                 // Fast (direct retrieval)
    'quality' => 'high',               // High quality (grounded in sources)
]);

echo "✓ Registered RAG Agent (knowledge specialist)\n\n";

echo "All agents registered: " . implode(', ', $service->getRegisteredAgents()) . "\n\n";

// =================================================================
// Test Different Task Types
// =================================================================

$tasks = [
    [
        'task' => 'Calculate 15% of 240 and add 50',
        'expected' => 'react',
        'reason' => 'Requires calculator tool',
    ],
    [
        'task' => 'Write a professional apology email for missing a deadline',
        'expected' => 'reflection',
        'reason' => 'Quality-critical writing',
    ],
    [
        'task' => 'If all A are B, and all B are C, are all A definitely C? Explain step by step.',
        'expected' => 'cot',
        'reason' => 'Requires logical reasoning',
    ],
    [
        'task' => 'What is PHP and what are its key features?',
        'expected' => 'rag',
        'reason' => 'Knowledge-based query',
    ],
];

foreach ($tasks as $i => $testCase) {
    $num = $i + 1;
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "Test {$num}: {$testCase['reason']}\n";
    echo "─────────────────────────────────────────────────────────────────\n\n";

    echo "Task: {$testCase['task']}\n";
    echo "Expected Agent: {$testCase['expected']}\n\n";

    $result = $service->run($testCase['task']);

    if ($result->isSuccess()) {
        $metadata = $result->getMetadata();
        $selected = $metadata['final_agent'];
        $quality = $metadata['final_quality'];

        echo "✓ Selected: {$selected}\n";
        echo "  Quality: {$quality}/10\n";
        echo "  Match: " . ($selected === $testCase['expected'] ? '✓' : '✗') . "\n";

        if (isset($metadata['task_analysis'])) {
            $analysis = $metadata['task_analysis'];
            echo "\n  Task Analysis:\n";
            echo "    Complexity: {$analysis['complexity']}\n";
            echo "    Domain: {$analysis['domain']}\n";
            echo "    Requires tools: " . ($analysis['requires_tools'] ? 'yes' : 'no') . "\n";
            if (isset($analysis['requires_reasoning'])) {
                echo "    Requires reasoning: " . ($analysis['requires_reasoning'] ? 'yes' : 'no') . "\n";
            }
        }

        echo "\n  Answer (preview): " . substr($result->getAnswer(), 0, 80) . "...\n\n";
    } else {
        echo "✗ FAILED: {$result->getError()}\n\n";
    }
}

// =================================================================
// Show Agent Profile Details
// =================================================================

echo "=================================================================\n";
echo "Agent Profile Details\n";
echo "=================================================================\n\n";

foreach ($service->getRegisteredAgents() as $agentId) {
    $profile = $service->getAgentProfile($agentId);

    echo "{$agentId} Agent Profile:\n";
    echo "  Type: {$profile['type']}\n";
    echo "  Complexity: {$profile['complexity_level']}\n";
    echo "  Speed: {$profile['speed']}\n";
    echo "  Quality: {$profile['quality']}\n";
    echo "  Strengths: " . implode(', ', $profile['strengths']) . "\n";
    echo "  Best for: " . implode(', ', $profile['best_for']) . "\n\n";
}

// =================================================================
// Performance Summary
// =================================================================

echo "=================================================================\n";
echo "Performance Summary\n";
echo "=================================================================\n\n";

$performance = $service->getPerformance();

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 0) {
        $successRate = round(($stats['successes'] / $stats['attempts']) * 100, 1);

        echo "{$agentId}: {$stats['attempts']} attempts, {$successRate}% success\n";
    }
}

echo "\n";

// =================================================================
// Key Takeaways
// =================================================================

echo "=================================================================\n";
echo "Key Lessons:\n";
echo "=================================================================\n\n";

echo "1. Profiles Guide Selection\n";
echo "   - Task analysis matches against agent profiles\n";
echo "   - Complexity, quality, and capability matching\n\n";

echo "2. Differentiate Your Agents\n";
echo "   - Each agent should have unique strengths\n";
echo "   - Don't make all profiles identical\n\n";

echo "3. Be Realistic\n";
echo "   - Don't over-promise capabilities\n";
echo "   - Match profiles to actual agent behavior\n\n";

echo "4. Use Descriptive Fields\n";
echo "   - 'strengths' and 'best_for' help with matching\n";
echo "   - More detail = better selection\n\n";
