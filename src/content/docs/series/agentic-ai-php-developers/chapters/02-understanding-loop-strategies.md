---
title: "02: Understanding Loop Strategies"
description: "Master ReactLoop, PlanExecuteLoop, ReflectionLoop, and StreamingLoop patterns for different task types"
series: "agentic-ai-php-developers"
chapter: 2
difficulty: "Intermediate"
keywords: ["Agent Loop Strategies", "ReactLoop PHP", "PlanExecuteLoop", "ReflectionLoop", "StreamingLoop"]
teaches: ["ReAct pattern implementation", "Plan-Execute workflow design", "Self-reflection loops", "Streaming agent updates", "Loop strategy selection"]
estimatedTime: "PT90M"
---

# Chapter 02: Understanding Loop Strategies

## Overview

The control loop is the heartbeat of an agentic system. It decides *how* the agent thinks—whether it reasons step-by-step, plans upfront, reflects on outputs, or streams progress in real-time. The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework provides four powerful loop strategies, each optimized for different task patterns.

In Chapter 01, you learned the conceptual model: **agent = loop + tools + memory**. Now we'll make loops concrete. You'll understand when each loop strategy shines, how to configure and customize them, and how to choose the right pattern for your use case.

In this chapter you'll:

- Master **ReactLoop**: the default reason-act-observe pattern for general tasks
- Learn **PlanExecuteLoop**: upfront planning for complex multi-step workflows
- Understand **ReflectionLoop**: self-critique and refinement for quality-critical outputs
- Explore **StreamingLoop**: real-time progress updates for long-running tasks
- Compare loop strategies across dimensions like task complexity, latency, and observability
- Build custom loop behaviors with configuration and callbacks

**Estimated time:** ~90 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. API signatures and methods are verified against the actual framework source code. For the most current API documentation, visit the [GitHub repository](https://github.com/claude-php/claude-php-agent).
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`react-loop.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/02-loop-strategies/react-loop.php) — ReAct pattern with tool use
- [`plan-execute-loop.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/02-loop-strategies/plan-execute-loop.php) — Upfront planning workflow
- [`reflection-loop.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/02-loop-strategies/reflection-loop.php) — Self-critique and refinement
- [`streaming-loop.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/02-loop-strategies/streaming-loop.php) — Real-time progress updates
- [`loop-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/02-loop-strategies/loop-comparison.php) — Side-by-side comparison
- [`custom-loop-config.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/02-loop-strategies/custom-loop-config.php) — Advanced customization

All files are in [`code/agentic-ai-php-developers/02-loop-strategies/`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/02-loop-strategies/README.md).
:::

---

## The Four Loop Strategies

Every loop strategy in `claude-php/claude-php-agent` implements the same `LoopStrategy` interface but with different execution patterns. Understanding the trade-offs helps you choose the right tool for each job.

| Loop Strategy | Best For | Latency | Token Usage | Complexity |
| --- | --- | --- | --- | --- |
| **ReactLoop** | General tasks, tool orchestration | Low | Medium | Low |
| **PlanExecuteLoop** | Multi-step workflows, dependencies | Medium | High | Medium |
| **ReflectionLoop** | Quality-critical output, code generation | High | Very High | Medium |
| **StreamingLoop** | Long-running tasks, UX feedback | Low (perceived) | Medium | Low |

Let's explore each in detail.

---

## ReactLoop: Reason-Act-Observe

**ReactLoop** is the default loop strategy and implements the classic **ReAct pattern** (Reasoning + Acting). It's a flexible, general-purpose loop that works for most agentic tasks.

### How ReactLoop Works

1. **Reason**: The agent decides the next action based on the goal and current state
2. **Act**: If a tool is needed, call it; otherwise, generate the final response
3. **Observe**: Capture tool results and update internal state
4. **Repeat**: Continue until the agent decides the task is complete

### Conceptual Flow

```text
User Input
    ↓
[Reason] What should I do next?
    ↓
[Act] Call tool OR respond
    ↓
[Observe] Process tool output
    ↓
[Repeat or Finish]
```

### When to Use ReactLoop

- ✅ General-purpose tasks with 1–5 tool calls
- ✅ Tasks where tools can be called incrementally
- ✅ When you want low latency and flexible reasoning
- ✅ Exploratory tasks where the path isn't clear upfront

### Basic ReactLoop Example

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define tools
$getWeather = Tool::create('get_weather')
    ->description('Get current weather for a city')
    ->parameter('city', 'string', 'City name')
    ->required('city')
    ->handler(fn(array $input) => json_encode([
        'city' => $input['city'],
        'temp' => rand(60, 85),
        'conditions' => ['sunny', 'cloudy', 'rainy'][rand(0, 2)]
    ]));

$calculateTrip = Tool::create('calculate_trip_time')
    ->description('Calculate drive time between cities')
    ->parameter('from', 'string', 'Starting city')
    ->parameter('to', 'string', 'Destination city')
    ->required(['from', 'to'])
    ->handler(fn(array $input) => json_encode([
        'from' => $input['from'],
        'to' => $input['to'],
        'hours' => rand(2, 8)
    ]));

// Create agent with ReactLoop (default)
$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful travel assistant.')
    ->withTools([$getWeather, $calculateTrip])
    ->withLoopStrategy(new ReactLoop())
    ->maxIterations(5);

// Execute task
$result = $agent->run(
    'I want to drive from San Francisco to Los Angeles. ' .
    'What\'s the weather like and how long will it take?'
);

echo $result->getAnswer();
```

### ReactLoop Configuration

::: tip Framework Note
The actual `ReactLoop` constructor is simpler than the conceptual example below. In practice, `ReactLoop` only accepts an optional `LoggerInterface` parameter. The configuration aspects shown here represent common patterns you can implement through callbacks and agent configuration.
:::

```php
// Basic ReactLoop instantiation
$reactLoop = new ReactLoop($logger);

$agent = Agent::create($client)
    ->withLoopStrategy($reactLoop)
    ->maxIterations(10)  // Control loop via agent config
    ->run($task);
```

### Advantages of ReactLoop

- **Flexible**: Agent can adapt reasoning based on tool results
- **Low latency**: Minimal planning overhead
- **Transparent**: Easy to follow the reasoning chain
- **Efficient**: Only calls tools as needed

### Limitations

- Can be inefficient for complex multi-step workflows
- May make redundant tool calls without upfront planning
- Harder to enforce strict execution order

---

## PlanExecuteLoop: Plan First, Execute Systematically

**PlanExecuteLoop** separates planning from execution. The agent generates a complete plan upfront, then executes steps systematically. This is ideal for complex workflows with dependencies.

### How PlanExecuteLoop Works

1. **Plan Phase**: Agent generates a complete task breakdown
2. **Execute Phase**: Each step is executed in sequence
3. **Observe**: Track progress and capture results
4. **Replan (optional)**: If a step fails, regenerate the plan

### Conceptual Flow

```text
User Input
    ↓
[Plan] Generate complete task breakdown
    ↓
Step 1 → Execute → Observe
Step 2 → Execute → Observe
Step 3 → Execute → Observe
    ↓
[Final Response]
```

### When to Use PlanExecuteLoop

- ✅ Multi-step workflows with clear dependencies
- ✅ Tasks requiring specific execution order
- ✅ When you need a full plan before starting
- ✅ Complex tasks where upfront planning saves token costs

### PlanExecuteLoop Example

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define database and analytics tools
$queryDb = Tool::create('query_database')
    ->description('Run SQL queries on the orders database')
    ->parameter('query', 'string', 'SQL query to execute')
    ->required('query')
    ->handler(fn(array $input) => json_encode([
        'rows' => rand(100, 500),
        'data' => 'Sample order data...'
    ]));

$generateReport = Tool::create('generate_report')
    ->description('Generate a formatted report from data')
    ->parameter('data', 'string', 'Data to include in report')
    ->parameter('format', 'string', 'Report format (pdf, csv, html)')
    ->required(['data', 'format'])
    ->handler(fn(array $input) => "Report generated: {$input['format']}");

$sendEmail = Tool::create('send_email')
    ->description('Send email with attachment')
    ->parameter('recipient', 'string', 'Email recipient')
    ->parameter('subject', 'string', 'Email subject')
    ->parameter('body', 'string', 'Email body')
    ->parameter('attachment', 'string', 'File to attach', false)
    ->required(['recipient', 'subject', 'body'])
    ->handler(fn(array $input) => "Email sent to {$input['recipient']}");

// Create agent with PlanExecuteLoop
$agent = Agent::create($client)
    ->withSystemPrompt('You are a business intelligence assistant.')
    ->withTools([$queryDb, $generateReport, $sendEmail])
    ->withLoopStrategy(new PlanExecuteLoop(
        allowReplan: true,      // Allow replanning on failure
        showPlanInOutput: true  // Include plan in response
    ))
    ->maxIterations(10);

// Execute complex task
$result = $agent->run(
    'Create a monthly sales report: ' .
    '1) Query order data for October 2024, ' .
    '2) Generate a PDF report, ' .
    '3) Email it to manager@company.com'
);

echo $result->getAnswer();
```

### PlanExecuteLoop Configuration

::: tip Framework API
The `PlanExecuteLoop` constructor accepts: `__construct(?LoggerInterface $logger = null, bool $allowReplan = true)`. Callbacks are configured via the loop's fluent methods.
:::

```php
$planExecuteLoop = new PlanExecuteLoop(
    logger: $logger,
    allowReplan: true  // Regenerate plan if steps fail
);

$agent = Agent::create($client)
    ->withLoopStrategy($planExecuteLoop)
    ->onPlanGenerated(function (array $plan) {
        // Inspect the plan - callback supported by loop
        echo "Generated plan with " . count($plan) . " steps\n";
    })
    ->run($task);
```

### Advantages of PlanExecuteLoop

- **Systematic**: Clear execution order, easy to debug
- **Efficient**: Avoids redundant tool calls through upfront planning
- **Predictable**: You can inspect the plan before execution
- **Recoverable**: Replanning support when steps fail

### Limitations

- Higher initial latency (planning phase)
- May overplan simple tasks
- Less flexible if requirements change mid-execution

---

## ReflectionLoop: Self-Critique and Refinement

**ReflectionLoop** adds a self-review stage where the agent critiques its own output and refines it. This is essential for quality-critical tasks like code generation, technical writing, or financial analysis.

### How ReflectionLoop Works

1. **Generate**: Agent produces initial output
2. **Reflect**: Agent critiques the output against criteria
3. **Refine**: If issues are found, regenerate improved output
4. **Repeat**: Continue until quality criteria are met or max iterations reached

### Conceptual Flow

```text
User Input
    ↓
[Generate] Create initial output
    ↓
[Reflect] Self-critique: Is it correct? Complete? Clear?
    ↓
Issues found? → [Refine] Regenerate improved version
              ↓
           [Repeat]
    ↓
Quality OK → [Final Response]
```

### When to Use ReflectionLoop

- ✅ Code generation and technical writing
- ✅ Financial analysis or legal document review
- ✅ When output quality matters more than speed
- ✅ Tasks requiring validation or fact-checking

### ReflectionLoop Example

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define validation tools
$checkSyntax = Tool::create('check_php_syntax')
    ->description('Validate PHP code syntax')
    ->parameter('code', 'string', 'PHP code to validate')
    ->required('code')
    ->handler(function (array $input) {
        // Simplified validation
        $hasErrors = str_contains($input['code'], '<?php') === false;
        return json_encode([
            'valid' => !$hasErrors,
            'errors' => $hasErrors ? ['Missing PHP opening tag'] : []
        ]);
    });

$runTests = Tool::create('run_unit_tests')
    ->description('Execute unit tests for code')
    ->parameter('code', 'string', 'Code to test')
    ->required('code')
    ->handler(fn(array $input) => json_encode([
        'passed' => rand(0, 1) === 1,
        'failures' => rand(0, 3)
    ]));

// Create agent with ReflectionLoop
$agent = Agent::create($client)
    ->withSystemPrompt(
        'You are an expert PHP developer. ' .
        'Generate clean, tested, PSR-12 compliant code. ' .
        'Critique your output for: syntax errors, missing tests, code smells.'
    )
    ->withTools([$checkSyntax, $runTests])
    ->withLoopStrategy(new ReflectionLoop(
        maxReflections: 3,           // Maximum refinement iterations
        reflectionPrompt: 'Review this code for syntax errors, test coverage, and PSR-12 compliance. ' .
                         'List specific issues, then provide an improved version.'
    ))
    ->maxIterations(10);

// Execute task
$result = $agent->run(
    'Write a PHP class for validating email addresses with comprehensive unit tests.'
);

echo $result->getAnswer();
```

### ReflectionLoop Configuration

::: tip Framework API
The `ReflectionLoop` constructor: `__construct(?LoggerInterface $logger = null, int $maxRefinements = 3, int $qualityThreshold = 8, ?string $criteria = null)`.
:::

```php
$reflectionLoop = new ReflectionLoop(
    logger: $logger,
    maxRefinements: 3,         // How many refinement cycles
    qualityThreshold: 8,       // Stop when quality score >= 8/10
    criteria: 'Focus on code quality, test coverage, and documentation'
);

$agent = Agent::create($client)
    ->withLoopStrategy($reflectionLoop)
    ->onReflection(function (int $refinement, int $score, string $feedback) {
        echo "Reflection #{$refinement}: Score {$score}/10\n";
        echo "Feedback: {$feedback}\n";
    })
    ->run($task);
```

### Advantages of ReflectionLoop

- **Higher quality**: Self-critique catches errors
- **Self-correcting**: Agent improves without human intervention
- **Transparent**: Reflection provides reasoning trail
- **Flexible**: Works with any output validation tools

### Limitations

- Highest token usage (generate → reflect → refine)
- Longer latency (multiple passes)
- May over-optimize or introduce new issues

---

## StreamingLoop: Real-Time Progress Updates

**StreamingLoop** provides real-time progress updates as the agent works. Instead of waiting for the full response, you get incremental updates—ideal for long-running tasks and better UX.

### How StreamingLoop Works

1. **Start**: Begin task execution
2. **Stream**: Emit progress updates after each action
3. **Update**: Send tool calls, reasoning, and partial results
4. **Complete**: Final response when done

### Conceptual Flow

```text
User Input
    ↓
[Agent starts] → Emit "Starting task..."
    ↓
[Tool call 1] → Emit "Calling get_weather..."
    ↓
[Result 1] → Emit "Weather retrieved: sunny"
    ↓
[Tool call 2] → Emit "Calculating route..."
    ↓
[Final] → Emit complete response
```

### When to Use StreamingLoop

- ✅ Long-running tasks (>10 seconds)
- ✅ User-facing applications needing progress feedback
- ✅ Tasks with multiple slow tool calls
- ✅ When perceived performance matters

### StreamingLoop Example

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\StreamingLoop;
use ClaudeAgents\Progress\AgentUpdate;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define slow tools
$analyzeData = Tool::create('analyze_large_dataset')
    ->description('Process and analyze large datasets')
    ->parameter('dataset', 'string', 'Dataset identifier')
    ->required('dataset')
    ->handler(function (array $input) {
        sleep(2); // Simulate slow operation
        return json_encode(['records' => rand(10000, 50000), 'insights' => 'Analysis complete']);
    });

$generateVisuals = Tool::create('generate_visualizations')
    ->description('Create charts and graphs from data')
    ->parameter('data', 'string', 'Data to visualize')
    ->required('data')
    ->handler(function (array $input) {
        sleep(2); // Simulate rendering
        return json_encode(['charts' => ['bar', 'pie', 'line'], 'status' => 'generated']);
    });

// Create agent with StreamingLoop
$agent = Agent::create($client)
    ->withSystemPrompt('You are a data analysis assistant.')
    ->withTools([$analyzeData, $generateVisuals])
    ->withLoopStrategy(new StreamingLoop())
    ->onUpdate(function (AgentUpdate $update) {
        // Real-time progress callback
        $data = $update->getData();
        $message = $data['message'] ?? $data['text'] ?? $update->getType();
        echo "[{$update->getType()}] {$message}\n";
        
        if (!empty($data)) {
            echo "  Data: " . json_encode($data) . "\n";
        }
    })
    ->maxIterations(10);

// Execute task
$result = $agent->run('Analyze Q4 sales data and generate visual reports.');

echo "\n=== Final Result ===\n";
echo $result->getAnswer();
```

### StreamingLoop Configuration

::: tip Framework API
The `StreamingLoop` constructor: `__construct(?LoggerInterface $logger = null)`. Streaming is handled through the `onUpdate()` callback which receives `AgentUpdate` events.
:::

```php
$streamingLoop = new StreamingLoop($logger);

$agent = Agent::create($client)
    ->withLoopStrategy($streamingLoop)
    ->onUpdate(function (AgentUpdate $update) {
        // Send to frontend via WebSocket, SSE, etc.
        $data = $update->getData();
        $type = $update->getType();
        
        match ($type) {
            'tool_call' => sendToClient(['type' => 'tool', 'name' => $data['tool'] ?? '']),
            'tool_result' => sendToClient(['type' => 'result', 'data' => $data]),
            'thinking' => sendToClient(['type' => 'thinking', 'message' => $data['text'] ?? '']),
            default => null
        };
    })
    ->run($task);
```

### Advantages of StreamingLoop

- **Better UX**: Users see progress, not just a loading spinner
- **Perceived performance**: Feels faster even if actual latency is similar
- **Debugging**: Real-time insight into agent reasoning
- **Responsiveness**: Can cancel or adjust mid-execution

### Limitations

- Requires callback infrastructure (WebSockets, SSE, polling)
- More complex error handling
- May emit too many updates for simple tasks

---

## Choosing the Right Loop Strategy

Use this decision tree to select the appropriate loop:

```text
Is the task long-running or user-facing?
  ↓ YES → StreamingLoop (wrap any other loop)
  ↓ NO
    ↓
Does output quality require self-review?
  ↓ YES → ReflectionLoop
  ↓ NO
    ↓
Is this a complex multi-step workflow with dependencies?
  ↓ YES → PlanExecuteLoop
  ↓ NO
    ↓
Default → ReactLoop
```

### Quick Reference Table

| Use Case | Recommended Loop | Reason |
| --- | --- | --- |
| General research, Q&A | ReactLoop | Flexible, low latency |
| Data pipeline (ETL) | PlanExecuteLoop | Clear steps, order matters |
| Code generation | ReflectionLoop | Quality-critical output |
| Report generation | PlanExecuteLoop | Systematic, multi-step |
| Customer support bot | ReactLoop + Streaming | Responsive, adaptable |
| Financial analysis | ReflectionLoop | Validation required |
| API orchestration | ReactLoop | Dynamic tool selection |
| Complex automation | PlanExecuteLoop | Reliable execution order |

---

## Customizing Loop Behavior

All loop strategies support customization through configuration and callbacks. Here's how to build advanced loop behaviors:

### Example: Adaptive Loop with Fallback

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Loops\PlanExecuteLoop;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$tools = [...]; // Your tools

// Start with ReactLoop, switch to PlanExecuteLoop if needed
$agent = Agent::create($client)
    ->withTools($tools)
    ->withLoopStrategy(new ReactLoop())
    ->onError(function (Throwable $e, int $attempt) use ($agent) {
        if ($attempt > 2) {
            echo "Switching to PlanExecuteLoop for systematic retry...\n";
            $agent->withLoopStrategy(new PlanExecuteLoop(allowReplan: true));
        }
    })
    ->maxIterations(15)
    ->run($task);
```

### Example: Hybrid Reflection + Streaming

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudeAgents\Progress\AgentUpdate;

$agent = Agent::create($client)
    ->withTools($tools)
    ->withLoopStrategy(new ReflectionLoop(maxRefinements: 2))
    ->onUpdate(function (AgentUpdate $update) {
        // Stream reflection progress
        $data = $update->getData();
        if ($update->getType() === 'reflection') {
            $message = $data['message'] ?? $data['text'] ?? 'Reflecting...';
            echo "🔍 Reflecting: {$message}\n";
        }
    })
    ->onReflection(function (int $refinement, int $score, string $feedback) {
        echo "Critique #{$refinement}: Score {$score}/10\n";
        echo "Feedback: {$feedback}\n";
    })
    ->run('Generate a secure PHP authentication system.');
```

### Example: Loop with Custom Termination Logic

```php
<?php

$agent = Agent::create($client)
    ->withTools($tools)
    ->withLoopStrategy(new ReactLoop())
    ->onUpdate(function (AgentUpdate $update) {
        // Check for custom stop conditions
        if ($update->getType() === 'tool_result') {
            $result = $update->getData();
            if (isset($result['fatal_error'])) {
                throw new \RuntimeException('Fatal error detected, stopping agent.');
            }
        }
    })
    ->run($task);
```

---

## Loop Strategy Internals: How They Work

Understanding the internal mechanics helps you debug issues and extend loops.

### LoopStrategy Interface

All loops implement this interface:

```php
interface LoopStrategy
{
    public function execute(Agent $agent, string $task): AgentResult;
    
    public function shouldContinue(AgentState $state): bool;
    
    public function getNextAction(AgentState $state): AgentAction;
}
```

### ReactLoop Pseudocode

```php
function execute(Agent $agent, string $task): AgentResult
{
    $state = new AgentState($task);
    
    while ($this->shouldContinue($state)) {
        $action = $this->getNextAction($state);
        
        if ($action->isTool()) {
            $result = $agent->executeTool($action->tool, $action->input);
            $state->addObservation($result);
        } else {
            return $action->response;
        }
        
        $state->incrementIteration();
    }
    
    return $state->getFinalResponse();
}
```

### PlanExecuteLoop Pseudocode

```php
function execute(Agent $agent, string $task): AgentResult
{
    $plan = $agent->generatePlan($task);
    $state = new AgentState($task, $plan);
    
    foreach ($plan->steps as $step) {
        try {
            $result = $agent->executeStep($step);
            $state->markStepComplete($step, $result);
        } catch (Throwable $e) {
            if ($this->allowReplan) {
                $plan = $agent->replan($state);
            } else {
                throw $e;
            }
        }
    }
    
    return $agent->synthesizeFinalResponse($state);
}
```

---

## Performance Characteristics

Understanding latency and token usage helps optimize costs:

| Loop | Avg Iterations | Token Multiplier | Latency | Best For |
| --- | --- | --- | --- | --- |
| ReactLoop | 3–5 | 1x | ~2–5s | Most tasks |
| PlanExecuteLoop | 1 + N steps | 1.5x | ~5–10s | Complex workflows |
| ReflectionLoop | 2–4 passes | 2–3x | ~10–20s | Quality-critical |
| StreamingLoop | Same as base | 1x | Perceived ~50% faster | UX-sensitive |

### Cost Optimization Tips

1. **Use ReactLoop by default**: Switch only when needed
2. **Limit iterations**: Set `maxIterations` conservatively
3. **Cache tool results**: Avoid redundant calls
4. **Profile token usage**: Track per-loop costs
5. **Combine strategies**: Use StreamingLoop wrapper for UX, not token savings

---

## Common Loop Patterns

### Pattern 1: Try React, Fall Back to Plan-Execute

```php
// Note: This pattern would require custom retry logic
// The framework doesn't support fallbackLoop parameter directly
$agent = Agent::create($client)
    ->withTools($tools)
    ->withLoopStrategy(new ReactLoop())
    ->onError(function (Throwable $e) {
        // Handle error, potentially restart with different loop
        echo "Error occurred: {$e->getMessage()}\n";
    })
    ->run($task);
```

### Pattern 2: Reflection with Streaming Feedback

```php
$agent = Agent::create($client)
    ->withTools($tools)
    ->withLoopStrategy(new ReflectionLoop(maxRefinements: 3))
    ->onReflection(function (int $refinement, int $score, string $feedback) {
        broadcastToClient([
            'type' => 'reflection',
            'refinement' => $refinement,
            'score' => $score,
            'feedback' => $feedback
        ]);
    })
    ->run($task);
```

### Pattern 3: Multi-Stage Execution

```php
// Stage 1: Plan
$planner = Agent::create($client)
    ->withLoopStrategy(new PlanExecuteLoop())
    ->run('Create a plan for: ' . $task);

// Extract plan from result metadata or message
$planText = $planner->getAnswer();

// Stage 2: Execute with Reflection
$executor = Agent::create($client)
    ->withLoopStrategy(new ReflectionLoop())
    ->run('Execute this plan with high quality: ' . $planText);
```

---

## Debugging Loop Issues

### Common Problems and Solutions

**Problem: Agent loops forever**

Solution:
```php
$agent = Agent::create($client)
    ->maxIterations(10)  // Strict limit
    ->withLoopStrategy(new ReactLoop($logger))
    ->run($task);
```

**Problem: Plan-Execute fails mid-execution**

Solution:
```php
$loop = new PlanExecuteLoop(
    logger: $logger,
    allowReplan: true  // Regenerate plan on failure
);
```

**Problem: Reflection loop doesn't improve output**

Solution:
```php
$loop = new ReflectionLoop(
    logger: $logger,
    maxRefinements: 2,  // Limit passes to avoid diminishing returns
    qualityThreshold: 7  // Lower threshold if too strict
);
```

**Problem: Streaming updates too noisy**

Solution:
```php
$agent = Agent::create($client)
    ->withLoopStrategy(new StreamingLoop())
    ->onUpdate(function (AgentUpdate $update) {
        // Filter to only important event types
        if (in_array($update->getType(), ['tool_call', 'tool_result', 'error'])) {
            $data = $update->getData();
            echo "[{$update->getType()}] " . json_encode($data) . "\n";
        }
    });
```

---

## Chapter Checklist

By the end of this chapter, you should be able to:

- Explain how ReactLoop, PlanExecuteLoop, ReflectionLoop, and StreamingLoop work
- Choose the right loop strategy for different task types
- Configure loops with custom behavior and callbacks
- Understand token usage and latency trade-offs
- Debug common loop issues
- Combine loop strategies for advanced workflows

---

## Exercises

### Exercise 1: Implement a Research Agent with ReactLoop

Build an agent that researches a topic using Wikipedia and Arxiv tools. Use ReactLoop to dynamically decide which sources to check.

**Tools needed:**
- `search_wikipedia(query)`
- `search_arxiv(query)`
- `summarize_text(text)`

**Expected behavior:**
- Agent decides which sources are relevant
- Calls tools incrementally
- Synthesizes findings

### Exercise 2: Build a Data Pipeline with PlanExecuteLoop

Create an agent that processes customer data:
1. Query database for orders
2. Aggregate by region
3. Generate report
4. Email results

Use PlanExecuteLoop to ensure steps execute in order.

### Exercise 3: Code Review Agent with ReflectionLoop

Build an agent that generates a PHP class, then reviews it for:
- Syntax errors
- Test coverage
- PSR-12 compliance

Use ReflectionLoop with validation tools.

### Exercise 4: Long-Running Task with StreamingLoop

Create an agent that processes large datasets with progress updates. Stream updates to the console showing:
- Current step
- Tool being called
- Results

---

## Next Steps

In **Chapter 03: Tool System Deep Dive**, we'll build production-grade tools with schema validation, error handling, retries, and testing. You'll learn how to make tools reliable and secure for agentic workflows.

---

## Further Reading

To deepen your understanding of loop strategies:

- [Claude PHP Agent: Loop Strategies](https://github.com/claude-php/claude-php-agent/blob/master/docs/loop-strategies.md) — Complete documentation
- [ReAct Pattern Paper](https://arxiv.org/abs/2210.03629) — Original research on Reason + Act
- [Anthropic: Extended Thinking](https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking) — Deep reasoning patterns
- [Plan-Execute Pattern](https://langchain-ai.github.io/langgraph/concepts/agentic_concepts/#plan-and-execute) — LangChain's approach (Python, but concepts apply)
