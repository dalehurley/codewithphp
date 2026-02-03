---
title: "11: Multi-Stage Workflows and Agent Graphs"
description: "Build DAG-style workflows where agents execute steps in sequence or parallel. Add orchestration with state transitions."
series: "agentic-ai-php-developers"
chapter: 11
difficulty: "Advanced"
keywords: ["Workflow Orchestration PHP", "Agent Graphs", "DAG Workflows", "Sequential Chains", "Parallel Execution", "State Management", "Multi-Agent Orchestration", "Workflow Patterns"]
teaches: ["Sequential chain composition", "Parallel execution patterns", "Conditional routing with RouterChain", "State management and transitions", "Complex DAG workflows", "Multi-agent coordination", "Workflow monitoring and observability", "Production workflow systems"]
estimatedTime: "PT120M"
---

# Chapter 11: Multi-Stage Workflows and Agent Graphs

## Overview

You've mastered individual agent patterns: **React** (Chapter 02), **Plan-Execute** (Chapter 09), and **Reflection** (Chapter 10). But real-world applications often require **orchestrating multiple agents** in complex workflows—pipelines where different agents handle specialized tasks, execute in parallel, or route dynamically based on conditions.

This is where **multi-stage workflows** and **agent graphs** come in. Instead of a single agent trying to do everything, you compose specialized agents into **directed acyclic graphs (DAGs)** where each node is an agent or transformation, and edges define data flow and execution order.

The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework provides a powerful **Chain Composition System** for exactly this purpose: `SequentialChain`, `ParallelChain`, `RouterChain`, and `TransformChain` let you build sophisticated workflows that rival production orchestration systems like Airflow or Temporal—but designed specifically for agentic AI.

In this chapter you'll:

- Master **sequential chain composition** for multi-step pipelines
- Implement **parallel execution** for concurrent agent tasks
- Use **RouterChain** for conditional workflow routing
- Manage **workflow state** with `StateManager`
- Build **complex DAG workflows** with branching and merging
- Orchestrate **multi-agent systems** with specialized roles
- Add **monitoring and observability** for production workflows
- Create **production-grade workflow systems**

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. We'll use the Chain Composition System extensively throughout.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`basic-sequential-workflow.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/basic-sequential-workflow.php) — Simple sequential chain
- [`parallel-execution.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/parallel-execution.php) — Concurrent agent execution
- [`conditional-routing.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/conditional-routing.php) — Dynamic workflow routing
- [`state-management.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/state-management.php) — Workflow state transitions
- [`complex-dag-workflow.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/complex-dag-workflow.php) — Advanced DAG patterns
- [`multi-agent-orchestration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/multi-agent-orchestration.php) — Coordinating specialized agents
- [`workflow-monitoring.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/workflow-monitoring.php) — Observability and tracking
- [`production-workflow-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/production-workflow-system.php) — Complete production system

All files are in [`code/11-multi-stage-workflows/`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/README.md).
:::

---

## Understanding Workflows and Agent Graphs

Before implementing, let's understand what makes multi-stage workflows different from single-agent loops.

### From Single Agents to Workflows

| Aspect | Single Agent | Workflow/Graph |
| --- | --- | --- |
| **Structure** | Single loop | Multiple interconnected steps |
| **Specialization** | Generalist | Specialized agents per task |
| **Execution** | Linear/iterative | Sequential, parallel, or conditional |
| **State** | Internal context | Shared workflow state |
| **Complexity** | Single responsibility | Orchestrated system |
| **Best For** | Simple tasks | Complex multi-step processes |

### Workflow Patterns

The Chain Composition System provides four core building blocks:

1. **SequentialChain**: Execute steps in order (A → B → C)
2. **ParallelChain**: Execute steps concurrently (A, B, C at once)
3. **RouterChain**: Conditional routing (if X then A, else B)
4. **TransformChain**: Data transformation (map/filter/reduce)

These compose into sophisticated DAGs:

```text
                    ┌─────────────┐
                    │   Input     │
                    └──────┬──────┘
                           ↓
                    ┌──────────────┐
                    │ RouterChain  │
                    │ (classify)   │
                    └──┬────────┬──┘
                       ↓        ↓
              ┌────────┴──┐  ┌─┴─────────┐
              │ Agent A   │  │ Agent B   │
              │ (code)    │  │ (docs)    │
              └─────┬─────┘  └─────┬─────┘
                    │              │
                    └──────┬───────┘
                           ↓
                   ┌───────────────┐
                   │ ParallelChain │
                   │ (review +     │
                   │  format)      │
                   └───────┬───────┘
                           ↓
                   ┌───────────────┐
                   │    Output     │
                   └───────────────┘
```

---

## Sequential Workflows

Let's start with the simplest pattern: executing agents in sequence.

### Basic Sequential Chain

A sequential chain executes steps in order, passing outputs as inputs to subsequent steps.

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Chains\ChainInput;
use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Chains\SequentialChain;
use ClaudeAgents\Chains\TransformChain;
use ClaudeAgents\Prompts\PromptTemplate;
use ClaudePhp\ClaudePhp;

require 'vendor/autoload.php';

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Step 1: Extract entities from text
$extractionChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Extract all named entities (people, places, organizations) from this text: {text}'
    ))
    ->withMaxTokens(500);

// Step 2: Analyze sentiment of extracted entities
$analysisChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Analyze the sentiment and tone based on these entities: {entities}'
    ))
    ->withMaxTokens(500);

// Step 3: Format as structured JSON
$formatChain = TransformChain::create(function (array $input): array {
    return [
        'entities' => $input['entities'] ?? 'None',
        'analysis' => $input['analysis'] ?? 'None',
        'processed_at' => date('Y-m-d H:i:s'),
        'status' => 'complete',
    ];
});

// Compose into sequential workflow
$pipeline = SequentialChain::create()
    ->addChain('extract', $extractionChain)
    ->addChain('analyze', $analysisChain)
    ->addChain('format', $formatChain)
    // Map outputs between stages
    ->mapOutput('extract', 'result', 'analyze', 'entities')
    ->mapOutput('analyze', 'result', 'format', 'analysis')
    ->mapOutput('extract', 'result', 'format', 'entities');

// Execute the workflow
$result = $pipeline->invoke([
    'text' => 'Apple Inc. announced a new partnership with Microsoft. CEO Tim Cook praised the collaboration.',
]);

echo "Workflow Results:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";

// Access individual step outputs
echo "\nExtracted Entities: " . ($result['extract']['result'] ?? 'N/A') . "\n";
echo "Sentiment Analysis: " . ($result['analyze']['result'] ?? 'N/A') . "\n";
echo "Final Report: " . json_encode($result['format'], JSON_PRETTY_PRINT) . "\n";
```

### How It Works

1. **Stage Definition**: Each `addChain()` defines a stage
2. **Output Mapping**: `mapOutput()` connects stages via data
3. **Sequential Execution**: Stages run in order
4. **Result Structure**: Final output includes all stage results

### Conditional Execution

Skip stages based on conditions:

```php
$conditionalPipeline = SequentialChain::create()
    ->addChain('validate', $validationChain)
    ->addChain('process', $processingChain)
    ->addChain('enrich', $enrichmentChain)
    // Only process if validation passed
    ->setCondition('process', function (array $results): bool {
        return $results['validate']['is_valid'] ?? false;
    })
    // Only enrich if processing succeeded
    ->setCondition('enrich', function (array $results): bool {
        return isset($results['process']['result']);
    });
```

---

## Parallel Execution

Execute multiple agents concurrently for speed and efficiency.

### Concurrent Analysis

```php
<?php

use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Chains\ParallelChain;
use ClaudeAgents\Prompts\PromptTemplate;

// Create specialized analysis chains
$sentimentChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Rate the sentiment (positive/negative/neutral) with confidence: {text}'
    ))
    ->withMaxTokens(200);

$topicsChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Identify the 3 main topics discussed: {text}'
    ))
    ->withMaxTokens(200);

$keywordsChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Extract important keywords and phrases: {text}'
    ))
    ->withMaxTokens(200);

$summaryChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Provide a 2-sentence summary: {text}'
    ))
    ->withMaxTokens(200);

// Execute all analyses in parallel
$parallelAnalysis = ParallelChain::create()
    ->addChain('sentiment', $sentimentChain)
    ->addChain('topics', $topicsChain)
    ->addChain('keywords', $keywordsChain)
    ->addChain('summary', $summaryChain)
    ->withAggregation('all')  // Return all results
    ->withTimeout(30000);     // 30 second timeout

$result = $parallelAnalysis->invoke([
    'text' => 'Product review text here...',
]);

echo "Parallel Analysis Results:\n";
echo "Sentiment: " . ($result['results']['sentiment']['result'] ?? 'N/A') . "\n";
echo "Topics: " . ($result['results']['topics']['result'] ?? 'N/A') . "\n";
echo "Keywords: " . ($result['results']['keywords']['result'] ?? 'N/A') . "\n";
echo "Summary: " . ($result['results']['summary']['result'] ?? 'N/A') . "\n";

// Check for any errors
if (!empty($result['errors'])) {
    echo "\nErrors encountered:\n";
    foreach ($result['errors'] as $chain => $error) {
        echo "- {$chain}: {$error}\n";
    }
}
```

### Aggregation Strategies

`ParallelChain` supports three aggregation strategies:

1. **`merge`**: Merge all results into a single flat array
2. **`first`**: Return the first successful result
3. **`all`**: Return structured results + errors (default)

```php
// Strategy: merge
$parallelChain->withAggregation('merge');
// Result: ['sentiment_result' => '...', 'topics_result' => '...']

// Strategy: first
$parallelChain->withAggregation('first');
// Result: First successful chain's output

// Strategy: all
$parallelChain->withAggregation('all');
// Result: ['results' => [...], 'errors' => [...]]
```

---

## Conditional Routing

Use `RouterChain` to route dynamically based on input conditions.

### Content Classification Router

```php
<?php

use ClaudeAgents\Chains\RouterChain;
use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Prompts\PromptTemplate;

// Specialized chains for different content types
$codeReviewChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Review this code for bugs, security issues, and best practices:\n{content}'
    ))
    ->withMaxTokens(1000);

$documentationChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Explain this documentation clearly and identify any gaps:\n{content}'
    ))
    ->withMaxTokens(1000);

$questionAnswerChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Answer this question thoroughly:\n{content}'
    ))
    ->withMaxTokens(800);

$generalChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Process this request:\n{content}'
    ))
    ->withMaxTokens(800);

// Create router with conditions
$router = RouterChain::create()
    // Route 1: Code content
    ->addRoute(
        fn($input) => 
            str_contains($input['content'], '<?php') ||
            str_contains($input['content'], 'function ') ||
            str_contains($input['content'], 'class '),
        $codeReviewChain
    )
    // Route 2: Documentation
    ->addRoute(
        fn($input) =>
            str_contains(strtolower($input['content']), 'documentation') ||
            str_contains($input['content'], '/**') ||
            str_contains($input['content'], '# '),
        $documentationChain
    )
    // Route 3: Questions
    ->addRoute(
        fn($input) => str_ends_with(trim($input['content']), '?'),
        $questionAnswerChain
    )
    // Default: General processing
    ->setDefault($generalChain);

// Test routing
$testInputs = [
    'Code: <?php function test() { return 42; }',
    'Documentation: # API Reference for MyClass',
    'Question: What is dependency injection?',
    'General: Process this text',
];

foreach ($testInputs as $content) {
    $result = $router->invoke(['content' => $content]);
    
    echo "Input: " . substr($content, 0, 50) . "...\n";
    echo "Route: " . ($result->getMetadata()['route'] ?? 'unknown') . "\n";
    echo "Type: " . ($result->getMetadata()['type'] ?? 'unknown') . "\n";
    echo "Result: " . substr($result['result'] ?? 'N/A', 0, 100) . "...\n\n";
}
```

### Advanced Routing: Multi-Criteria

```php
// More sophisticated routing logic
$advancedRouter = RouterChain::create()
    ->addRoute(
        function ($input): bool {
            // Complex condition: check content type AND length
            $hasCode = str_contains($input['content'], '<?php');
            $isLong = strlen($input['content']) > 1000;
            $priority = $input['priority'] ?? 'normal';
            
            return $hasCode && $isLong && $priority === 'high';
        },
        $deepCodeReviewChain
    )
    ->addRoute(
        function ($input): bool {
            // Route based on user role + content type
            $role = $input['user_role'] ?? 'guest';
            $type = $input['type'] ?? 'unknown';
            
            return $role === 'admin' && $type === 'sensitive';
        },
        $secureProcessingChain
    )
    ->setDefault($standardChain);
```

---

## State Management

Manage workflow state across stages with `StateManager`.

### Workflow State Transitions

```php
<?php

use ClaudeAgents\State\StateManager;
use ClaudeAgents\State\AgentState;
use ClaudeAgents\State\Goal;
use ClaudeAgents\State\GoalStatus;

// Initialize state manager
$stateManager = new StateManager(
    stateFile: __DIR__ . '/workflow_state.json',
    options: [
        'atomic_writes' => true,
        'backup_retention' => 5,
    ]
);

// Create or load workflow state
$state = $stateManager->load() ?? AgentState::create(
    stateId: 'workflow-' . uniqid(),
    agentType: 'multi-stage-workflow'
);

// Define workflow goals
$state->addGoal(new Goal(
    id: 'extract-entities',
    description: 'Extract named entities from input',
    status: GoalStatus::PENDING,
    priority: 1
));

$state->addGoal(new Goal(
    id: 'analyze-sentiment',
    description: 'Analyze sentiment of extracted entities',
    status: GoalStatus::PENDING,
    priority: 2
));

$state->addGoal(new Goal(
    id: 'generate-report',
    description: 'Generate final analysis report',
    status: GoalStatus::PENDING,
    priority: 3
));

// Execute workflow with state tracking
echo "Starting workflow...\n";

// Stage 1: Extraction
$state->updateGoal('extract-entities', GoalStatus::IN_PROGRESS);
$stateManager->save($state);

$extractionResult = $extractionChain->invoke(['text' => $inputText]);

$state->updateGoal('extract-entities', GoalStatus::COMPLETED);
$state->storeData('entities', $extractionResult['result']);
$stateManager->save($state);

echo "✓ Extraction complete\n";

// Stage 2: Analysis
$state->updateGoal('analyze-sentiment', GoalStatus::IN_PROGRESS);
$stateManager->save($state);

$analysisResult = $analysisChain->invoke([
    'entities' => $state->getData('entities'),
]);

$state->updateGoal('analyze-sentiment', GoalStatus::COMPLETED);
$state->storeData('analysis', $analysisResult['result']);
$stateManager->save($state);

echo "✓ Analysis complete\n";

// Stage 3: Report generation
$state->updateGoal('generate-report', GoalStatus::IN_PROGRESS);
$stateManager->save($state);

$report = [
    'entities' => $state->getData('entities'),
    'analysis' => $state->getData('analysis'),
    'timestamp' => date('Y-m-d H:i:s'),
];

$state->updateGoal('generate-report', GoalStatus::COMPLETED);
$state->storeData('final_report', $report);
$stateManager->save($state);

echo "✓ Report generated\n";

// Check overall workflow status
$allGoals = $state->getGoals();
$completed = array_filter($allGoals, fn($g) => $g->status === GoalStatus::COMPLETED);

echo "\nWorkflow Progress: " . count($completed) . "/" . count($allGoals) . " goals completed\n";
```

### Checkpoint and Recovery

```php
// Save checkpoint before expensive operation
$stateManager->createBackup();

try {
    // Execute expensive workflow stage
    $result = $expensiveChain->invoke($input);
    $state->storeData('expensive_result', $result);
    $stateManager->save($state);
} catch (\Exception $e) {
    // Recover from checkpoint on failure
    echo "Error: {$e->getMessage()}\n";
    echo "Restoring from last checkpoint...\n";
    $state = $stateManager->restoreLatest();
}
```

---

## Complex DAG Workflows

Build sophisticated workflows with branching, merging, and conditional paths.

### Multi-Path Workflow

```php
<?php

use ClaudeAgents\Chains\SequentialChain;
use ClaudeAgents\Chains\ParallelChain;
use ClaudeAgents\Chains\RouterChain;
use ClaudeAgents\Chains\TransformChain;

/**
 * Complex workflow:
 * 1. Classify input (code, docs, or question)
 * 2. Route to specialized processing
 * 3. Parallel review + validation
 * 4. Merge results + generate report
 */

// Stage 1: Input classification
$classifyChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Classify this content as: CODE, DOCUMENTATION, QUESTION, or OTHER\n\n{content}\n\nReturn only the classification.'
    ))
    ->withMaxTokens(50);

// Stage 2a: Code processing
$codeChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Analyze this code:\n{content}\n\nProvide: 1) Purpose, 2) Issues, 3) Suggestions'
    ))
    ->withMaxTokens(1000);

// Stage 2b: Documentation processing
$docsChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Review this documentation:\n{content}\n\nProvide: 1) Clarity, 2) Completeness, 3) Improvements'
    ))
    ->withMaxTokens(1000);

// Stage 2c: Question answering
$qaChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Answer this question thoroughly:\n{content}'
    ))
    ->withMaxTokens(800);

// Stage 2: Router for specialized processing
$processingRouter = RouterChain::create()
    ->addRoute(
        fn($input) => str_contains(strtoupper($input['classification'] ?? ''), 'CODE'),
        $codeChain
    )
    ->addRoute(
        fn($input) => str_contains(strtoupper($input['classification'] ?? ''), 'DOCUMENTATION'),
        $docsChain
    )
    ->addRoute(
        fn($input) => str_contains(strtoupper($input['classification'] ?? ''), 'QUESTION'),
        $qaChain
    )
    ->setDefault($codeChain);

// Stage 3: Parallel review + validation
$reviewChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Review this analysis for accuracy and completeness:\n{processing_result}'
    ))
    ->withMaxTokens(500);

$validateChain = TransformChain::create(function (array $input): array {
    $processingResult = $input['processing_result'] ?? '';
    $wordCount = str_word_count($processingResult);
    
    return [
        'is_valid' => $wordCount > 10,
        'word_count' => $wordCount,
        'quality_score' => min(10, $wordCount / 10),
    ];
});

$parallelQA = ParallelChain::create()
    ->addChain('review', $reviewChain)
    ->addChain('validate', $validateChain)
    ->withAggregation('all');

// Stage 4: Final report generation
$reportChain = TransformChain::create(function (array $input): array {
    return [
        'classification' => $input['classification'] ?? 'unknown',
        'processing_result' => $input['processing_result'] ?? '',
        'review' => $input['review'] ?? '',
        'validation' => $input['validation'] ?? [],
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => ($input['validation']['is_valid'] ?? false) ? 'approved' : 'needs_review',
    ];
});

// Compose the complete DAG
$complexWorkflow = SequentialChain::create()
    ->addChain('classify', $classifyChain)
    ->addChain('process', $processingRouter)
    ->addChain('qa', $parallelQA)
    ->addChain('report', $reportChain)
    // Map outputs between stages
    ->mapOutput('classify', 'result', 'process', 'classification')
    ->mapOutput('process', 'result', 'qa', 'processing_result')
    ->mapOutput('classify', 'result', 'report', 'classification')
    ->mapOutput('process', 'result', 'report', 'processing_result')
    ->mapOutput('qa', 'results', 'report', 'qa_results');

// Execute the workflow
$input = [
    'content' => '<?php
function calculateTotal(array $items) {
    return array_reduce($items, fn($sum, $item) => $sum + $item["price"], 0);
}
',
];

echo "Executing complex workflow...\n\n";

$result = $complexWorkflow->invoke($input);

echo "Classification: " . ($result['classify']['result'] ?? 'N/A') . "\n";
echo "Processing: " . substr($result['process']['result'] ?? 'N/A', 0, 200) . "...\n";
echo "Review: " . substr($result['qa']['results']['review']['result'] ?? 'N/A', 0, 150) . "...\n";
echo "Validation: " . json_encode($result['qa']['results']['validate'] ?? []) . "\n";
echo "\nFinal Report:\n";
echo json_encode($result['report'], JSON_PRETTY_PRINT) . "\n";
```

---

## Multi-Agent Orchestration

Coordinate specialized agents with defined roles and handoffs.

### Agent Collaboration Pattern

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\MultiAgent\CollaborativeAgent;
use ClaudeAgents\MultiAgent\SharedMemory;

// Shared memory for agent collaboration
$sharedMemory = new SharedMemory();

// Agent 1: Researcher
$researcher = Agent::create($client)
    ->withSystemPrompt('You are a research specialist. Your role is to gather and analyze information.')
    ->withTool(Tool::create('store_findings')
        ->description('Store research findings in shared memory')
        ->parameter('key', 'string', 'Storage key')
        ->parameter('data', 'string', 'Research data')
        ->required('key', 'data')
        ->handler(function (array $input) use ($sharedMemory) {
            $sharedMemory->set($input['key'], $input['data']);
            return "Stored findings under: {$input['key']}";
        }))
    ->maxIterations(10);

// Agent 2: Analyzer
$analyzer = Agent::create($client)
    ->withSystemPrompt('You are an analysis specialist. Your role is to analyze research findings and identify insights.')
    ->withTool(Tool::create('get_findings')
        ->description('Retrieve research findings from shared memory')
        ->parameter('key', 'string', 'Storage key')
        ->required('key')
        ->handler(function (array $input) use ($sharedMemory) {
            $data = $sharedMemory->get($input['key']);
            return $data ?? "No findings found for: {$input['key']}";
        }))
    ->withTool(Tool::create('store_analysis')
        ->description('Store analysis results in shared memory')
        ->parameter('key', 'string', 'Storage key')
        ->parameter('analysis', 'string', 'Analysis results')
        ->required('key', 'analysis')
        ->handler(function (array $input) use ($sharedMemory) {
            $sharedMemory->set($input['key'], $input['analysis']);
            return "Stored analysis under: {$input['key']}";
        }))
    ->maxIterations(10);

// Agent 3: Report Writer
$writer = Agent::create($client)
    ->withSystemPrompt('You are a technical writer. Your role is to create comprehensive reports.')
    ->withTool(Tool::create('get_data')
        ->description('Retrieve data from shared memory')
        ->parameter('key', 'string', 'Storage key')
        ->required('key')
        ->handler(function (array $input) use ($sharedMemory) {
            return $sharedMemory->get($input['key']) ?? "No data found";
        }))
    ->maxIterations(10);

// Orchestrate multi-agent workflow
echo "=== Multi-Agent Workflow: Research → Analysis → Report ===\n\n";

// Stage 1: Research
echo "Stage 1: Research\n";
$researchResult = $researcher->run(
    'Research the benefits of PHP 8.4 property hooks. Store your findings under "php84_research".'
);
echo "Researcher: " . substr($researchResult->getAnswer(), 0, 200) . "...\n\n";

// Stage 2: Analysis
echo "Stage 2: Analysis\n";
$analysisResult = $analyzer->run(
    'Retrieve the research from "php84_research", analyze it, and store your analysis under "php84_analysis".'
);
echo "Analyzer: " . substr($analysisResult->getAnswer(), 0, 200) . "...\n\n";

// Stage 3: Report Writing
echo "Stage 3: Report Writing\n";
$reportResult = $writer->run(
    'Retrieve the analysis from "php84_analysis" and create a comprehensive technical report about PHP 8.4 property hooks.'
);
echo "Writer: " . $reportResult->getAnswer() . "\n\n";

// Show collaboration metrics
echo "=== Collaboration Metrics ===\n";
echo "Research iterations: " . $researchResult->getIterations() . "\n";
echo "Analysis iterations: " . $analysisResult->getIterations() . "\n";
echo "Writing iterations: " . $reportResult->getIterations() . "\n";
echo "Total tokens: " . (
    $researchResult->getTokenUsage()['total_tokens'] +
    $analysisResult->getTokenUsage()['total_tokens'] +
    $reportResult->getTokenUsage()['total_tokens']
) . "\n";
```

---

## Workflow Monitoring

Add observability to track workflow execution, performance, and errors.

### Workflow Callbacks and Logging

```php
<?php

class WorkflowMonitor
{
    private array $steps = [];
    private float $startTime;
    
    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->steps = [];
        echo "[MONITOR] Workflow started\n";
    }
    
    public function recordStep(string $name, string $status, ?array $metadata = null): void
    {
        $step = [
            'name' => $name,
            'status' => $status,
            'timestamp' => microtime(true) - $this->startTime,
            'metadata' => $metadata,
        ];
        
        $this->steps[] = $step;
        
        $duration = number_format($step['timestamp'], 3);
        echo "[MONITOR] {$name}: {$status} at {$duration}s\n";
    }
    
    public function getReport(): array
    {
        $duration = microtime(true) - $this->startTime;
        
        return [
            'total_duration' => $duration,
            'steps' => count($this->steps),
            'successful' => count(array_filter($this->steps, fn($s) => $s['status'] === 'complete')),
            'failed' => count(array_filter($this->steps, fn($s) => $s['status'] === 'failed')),
            'details' => $this->steps,
        ];
    }
}

// Use monitor with workflow
$monitor = new WorkflowMonitor();
$monitor->start();

$monitoredWorkflow = SequentialChain::create()
    ->addChain('step1', $step1Chain)
    ->addChain('step2', $step2Chain)
    ->addChain('step3', $step3Chain)
    ->onBefore(function (ChainInput $input) use ($monitor) {
        $monitor->recordStep('workflow', 'started', $input->all());
    })
    ->onAfter(function (ChainInput $input, $output) use ($monitor) {
        $monitor->recordStep('workflow', 'complete', ['output_keys' => array_keys($output->all())]);
    })
    ->onError(function (ChainInput $input, \Throwable $error) use ($monitor) {
        $monitor->recordStep('workflow', 'failed', ['error' => $error->getMessage()]);
    });

// Add step-level monitoring
foreach (['step1', 'step2', 'step3'] as $stepName) {
    $monitoredWorkflow->setCallback($stepName, 'before', function () use ($monitor, $stepName) {
        $monitor->recordStep($stepName, 'started');
    });
    
    $monitoredWorkflow->setCallback($stepName, 'after', function () use ($monitor, $stepName) {
        $monitor->recordStep($stepName, 'complete');
    });
}

// Execute and get report
$result = $monitoredWorkflow->invoke($input);

echo "\n=== Workflow Report ===\n";
$report = $monitor->getReport();
echo "Duration: " . number_format($report['total_duration'], 3) . "s\n";
echo "Steps: {$report['successful']}/{$report['steps']} successful\n";

if ($report['failed'] > 0) {
    echo "Failures: {$report['failed']}\n";
}
```

---

## Production Workflow System

Build a complete production-grade workflow orchestration system.

See the complete implementation in:
- [`production-workflow-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/11-multi-stage-workflows/production-workflow-system.php)

Key components:

```php
class WorkflowOrchestrator
{
    private ClaudePhp $client;
    private StateManager $stateManager;
    private array $workflows = [];
    
    public function registerWorkflow(string $name, callable $builder): void
    {
        $this->workflows[$name] = $builder;
    }
    
    public function execute(string $workflowName, array $input): WorkflowResult
    {
        // Load or create workflow state
        $state = $this->loadWorkflowState($workflowName, $input);
        
        // Build workflow
        $workflow = ($this->workflows[$workflowName])($this->client);
        
        // Execute with monitoring
        $monitor = new WorkflowMonitor();
        $monitor->start();
        
        try {
            $result = $workflow->invoke($input);
            
            // Update state
            $state->updateGoal('workflow-complete', GoalStatus::COMPLETED);
            $this->stateManager->save($state);
            
            return WorkflowResult::success($result, $monitor->getReport());
        } catch (\Exception $e) {
            // Handle failure
            $state->updateGoal('workflow-complete', GoalStatus::FAILED);
            $this->stateManager->save($state);
            
            return WorkflowResult::failure($e, $monitor->getReport());
        }
    }
}
```

---

## Best Practices

### ✅ DO

1. **Compose specialized agents**
   - Single responsibility per agent
   - Clear input/output contracts
   - Reusable chain components

2. **Use appropriate execution patterns**
   - Sequential for dependent stages
   - Parallel for independent operations
   - Router for conditional logic

3. **Manage state properly**
   - Persist workflow state
   - Checkpoint before expensive operations
   - Track goal progress

4. **Monitor and observe**
   - Log stage transitions
   - Track performance metrics
   - Alert on failures

5. **Handle errors gracefully**
   - Try/catch around stages
   - Fallback chains for failures
   - State recovery mechanisms

### ❌ DON'T

1. **Create monolithic workflows**
   - Break complex workflows into reusable chains
   - Don't put everything in one chain

2. **Ignore dependencies**
   - Map outputs correctly between stages
   - Don't assume data availability

3. **Skip error handling**
   - Workflows can fail at any stage
   - Plan for partial completion

4. **Forget about costs**
   - Monitor token usage across workflow
   - Parallel execution increases costs

5. **Over-optimize early**
   - Start with sequential, optimize later
   - Measure before parallelizing

---

## Common Patterns

### Pattern 1: Fan-Out, Fan-In

Process multiple items in parallel, then aggregate:

```php
$fanOutFanIn = SequentialChain::create()
    ->addChain('split', TransformChain::create(fn($input) => [
        'items' => explode(',', $input['text']),
    ]))
    ->addChain('process', ParallelChain::create()
        ->addChain('task1', $task1Chain)
        ->addChain('task2', $task2Chain)
        ->addChain('task3', $task3Chain)
        ->withAggregation('merge'))
    ->addChain('aggregate', $aggregationChain);
```

### Pattern 2: Try-Fallback

Try primary processing, fallback on failure:

```php
$tryFallback = ParallelChain::create()
    ->addChain('primary', $primaryChain)
    ->addChain('fallback', $fallbackChain)
    ->withAggregation('first')  // Use first successful result
    ->withTimeout(10000);       // Timeout for primary
```

### Pattern 3: Validation Pipeline

Validate at each stage:

```php
$validatedPipeline = SequentialChain::create()
    ->addChain('validate_input', $inputValidationChain)
    ->addChain('process', $processingChain)
    ->addChain('validate_output', $outputValidationChain)
    ->setCondition('process', fn($r) => $r['validate_input']['valid'])
    ->setCondition('validate_output', fn($r) => isset($r['process']));
```

---

## Key Takeaways

1. **Workflows orchestrate multiple agents**
   - Specialized agents for specific tasks
   - Chains compose agents into pipelines

2. **Four core chain types**
   - Sequential: A → B → C
   - Parallel: A, B, C concurrently
   - Router: Conditional routing
   - Transform: Data transformation

3. **State management is critical**
   - Persist workflow state
   - Track goal progress
   - Enable recovery

4. **Monitor and observe**
   - Track stage execution
   - Measure performance
   - Alert on failures

5. **Production patterns**
   - Error handling at every stage
   - Checkpointing for recovery
   - Metrics and logging

---

## What's Next?

In **Chapter 12: Guardrails, Policy, and Safety Layers**, we'll add critical safety mechanisms to our workflows. You'll learn to:

- Implement content filtering and moderation
- Add policy enforcement layers
- Build refusal logic for unsafe requests
- Validate outputs before returning to users
- Create audit trails for compliance

After mastering workflow orchestration, you'll be ready to make these systems production-safe with proper guardrails.

---

## Additional Resources

- [`claude-php/claude-php-agent` Chain Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/chains.md)
- [Chain Composition Examples](https://github.com/claude-php/claude-php-agent/tree/master/examples)
- [StateManager Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/state-management.md)

---

**Next:** [Chapter 12: Guardrails, Policy, and Safety Layers →](/series/agentic-ai-php-developers/chapters/12-guardrails-policy-and-safety-layers)
