---
title: "09: Planning: From Tasks to Steps"
description: "Implement task decomposition using PlanExecuteLoop. Generate plans, track progress, and replan when tools fail or data changes."
series: "agentic-ai-php-developers"
chapter: 9
difficulty: "Advanced"
keywords: ["Task Planning PHP", "PlanExecuteLoop", "Task Decomposition", "Multi-Step Agents", "Plan Execution", "Replanning", "Systematic Execution", "Agent Planning"]
teaches: ["Plan-Execute pattern architecture", "Task decomposition strategies", "Step-by-step execution", "Progress tracking", "Dynamic replanning", "Plan quality optimization", "ML-enhanced planning", "Production planning systems"]
estimatedTime: "PT120M"
---

# Chapter 09: Planning: From Tasks to Steps

## Overview

In previous chapters, you've built agents that **react** to situations—they observe, think, act, repeat. But what happens when you have a complex task that requires systematic, sequential execution? What if you need to coordinate multiple steps, track progress, and adapt when things don't go as planned?

This is where **planning agents** come in. Unlike reactive agents that figure things out step-by-step, planning agents create a **complete plan upfront**, then execute it systematically. This pattern is ideal for multi-step workflows, complex operations, and tasks where dependencies between steps matter.

The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework provides `PlanExecuteLoop` and `PlanExecuteAgent` for this exact purpose—agents that separate planning from execution, monitor progress, and adapt when needed.

In this chapter you'll:

- Understand the **Plan-Execute pattern** and when to use it
- Implement **task decomposition** to break complex tasks into steps
- Use **PlanExecuteLoop** for systematic execution
- Build **progress tracking** systems
- Handle **dynamic replanning** when steps fail
- Optimize **plan quality** with ML-enhanced parameters
- Deploy **production planning systems**

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. We'll use the framework's PlanExecuteLoop and PlanExecuteAgent extensively.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`basic-plan-execute.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/basic-plan-execute.php) — Simple plan-execute workflow
- [`task-decomposition.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/task-decomposition.php) — Breaking tasks into steps
- [`step-execution-tracking.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/step-execution-tracking.php) — Tracking execution progress
- [`dynamic-replanning.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/dynamic-replanning.php) — Adapting plans when steps fail
- [`plan-with-tools.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/plan-with-tools.php) — Planning with tool execution
- [`ml-optimized-planning.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/ml-optimized-planning.php) — ML-enhanced plan optimization
- [`production-planning-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/production-planning-system.php) — Complete production system

All files are in [`code/09-planning-from-tasks-to-steps/`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/09-planning-from-tasks-to-steps/README.md).
:::

---

## Understanding the Plan-Execute Pattern

Before diving into implementation, let's understand what makes planning different from reactive execution.

### React vs Plan-Execute

**React Pattern (Chapter 02):**

```text
Task: "Organize a team offsite"

Iteration 1: "Let me research venues..."
Iteration 2: "Now I'll check availability..."
Iteration 3: "Let me estimate costs..."
Iteration 4: "I should coordinate schedules..."

❌ Unpredictable iteration count
❌ No clear roadmap
❌ Easy to miss steps
```

**Plan-Execute Pattern (This Chapter):**

```text
Task: "Organize a team offsite"

PLAN:
1. Research and shortlist 3 venues
2. Check availability for preferred dates
3. Estimate total costs (venue + travel + meals)
4. Coordinate team member schedules
5. Book venue and send confirmations

EXECUTE:
Step 1: ✅ Researched, found 3 venues
Step 2: ✅ Venue A available Oct 15-17
Step 3: ✅ Total cost: $12,400
Step 4: ⚠️  3 conflicts found → REPLAN
Step 5: ✅ Booked for Oct 22-24 instead

✅ Predictable structure
✅ Clear progress tracking
✅ Adapts to failures
```

### When to Use Plan-Execute

**Use Plan-Execute when:**

- ✅ Tasks have **clear sequential steps**
- ✅ You need **progress visibility**
- ✅ Steps have **dependencies** (step 2 needs step 1's output)
- ✅ Tasks benefit from **upfront structure**
- ✅ You need to **track and report** progress

**Use React when:**

- ✅ Tasks are **exploratory** (you don't know steps upfront)
- ✅ High **flexibility** required
- ✅ Steps **emerge dynamically**
- ✅ **Real-time adaptation** is critical

### Plan-Execute Architecture

```text
┌─────────────────────────────────────────────────────────────────┐
│                    PLAN-EXECUTE LOOP                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐   │
│  │  1. PLAN     │────▶│  2. EXECUTE  │────▶│  3. MONITOR  │   │
│  │              │     │              │     │              │   │
│  │ • Analyze    │     │ • Execute    │     │ • Check      │   │
│  │ • Decompose  │     │   step 1     │     │   progress   │   │
│  │ • Generate   │     │ • Execute    │     │ • Detect     │   │
│  │   steps      │     │   step 2     │     │   failures   │   │
│  │              │     │ • ...        │     │              │   │
│  └──────────────┘     └──────────────┘     └──────┬───────┘   │
│         ▲                                           │           │
│         │                                           ▼           │
│         │                                  ┌──────────────┐    │
│         │                                  │  Replan?     │    │
│         └──────────────────────────────────┤  Yes         │    │
│                                            └──────┬───────┘    │
│                                                   │ No          │
│                                                   ▼             │
│                                            ┌──────────────┐    │
│                                            │ 4. SYNTHESIZE│    │
│                                            │              │    │
│                                            │ • Combine    │    │
│                                            │   results    │    │
│                                            │ • Generate   │    │
│                                            │   answer     │    │
│                                            └──────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Basic Plan-Execute Workflow

Let's start with a simple plan-execute agent using the framework.

### Simple Planning Agent

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudePhp\ClaudePhp;

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create Plan-Execute loop
$loop = new PlanExecuteLoop(allowReplan: true);

// Track the planning phase
$loop->onPlanCreated(function ($steps, $context) {
    echo "=== Plan Created ===\n\n";
    echo "Total steps: " . count($steps) . "\n\n";
    
    foreach ($steps as $i => $step) {
        echo "  " . ($i + 1) . ". {$step}\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
});

// Track each step completion
$loop->onStepComplete(function ($stepNumber, $description, $result) {
    echo "✅ Step {$stepNumber} complete\n";
    echo "   {$description}\n";
    echo "   → " . substr($result, 0, 100) . "...\n\n";
});

// Create agent with plan-execute loop
$agent = Agent::create($client)
    ->withName('planning-agent')
    ->withLoopStrategy($loop)
    ->maxIterations(15);

// Run a multi-step task
echo "=== Task: Research and Compare PHP Frameworks ===\n\n";

$task = "Research Laravel, Symfony, and Slim frameworks. Compare their features, performance, " .
        "and use cases. Recommend which one to use for a REST API project.";

$result = $agent->run($task);

// Display result
echo "=== Final Answer ===\n\n";
echo $result->getAnswer() . "\n\n";

echo "=== Metadata ===\n";
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Iterations: " . $result->getIterations() . "\n";
echo "Tokens: " . json_encode($result->getTokenUsage()) . "\n";

$metadata = $result->getMetadata();
if (isset($metadata['plan_steps'])) {
    echo "Plan steps: {$metadata['plan_steps']}\n";
}
if (isset($metadata['replan_count'])) {
    echo "Replans: {$metadata['replan_count']}\n";
}
```

**Output:**

```
=== Task: Research and Compare PHP Frameworks ===

=== Plan Created ===

Total steps: 5

  1. Research Laravel framework features, performance characteristics, and typical use cases
  2. Research Symfony framework features, performance characteristics, and typical use cases
  3. Research Slim framework features, performance characteristics, and typical use cases
  4. Compare the three frameworks across key dimensions relevant to REST API development
  5. Provide a recommendation based on the comparison for a REST API project

------------------------------------------------------------

✅ Step 1 complete
   Research Laravel framework features, performance characteristics, and typical use cases
   → Laravel is a full-featured PHP framework with extensive documentation, built-in authentication, Eloquent ORM...

✅ Step 2 complete
   Research Symfony framework features, performance characteristics, and typical use cases
   → Symfony is a set of reusable PHP components and a framework. It's highly modular and flexible...

✅ Step 3 complete
   Research Slim framework features, performance characteristics, and typical use cases
   → Slim is a micro-framework focused on simplicity and minimal overhead. Perfect for small to medium APIs...

✅ Step 4 complete
   Compare the three frameworks across key dimensions relevant to REST API development
   → Comparison complete. Laravel excels in developer experience, Symfony in enterprise flexibility, Slim in performance...

✅ Step 5 complete
   Provide a recommendation based on the comparison for a REST API project
   → For a REST API project, I recommend Laravel for teams prioritizing developer productivity and Slim for...

=== Final Answer ===

For your REST API project, I recommend **Laravel** if you want maximum developer productivity, 
excellent documentation, and a rich ecosystem. Laravel API Resources make building REST APIs 
straightforward, and the framework includes authentication, validation, and testing tools out of the box.

If your API needs to be extremely lightweight and you have experienced developers, **Slim** offers 
the best performance and minimal overhead.

Avoid Symfony for a simple REST API—it's best for large enterprise applications requiring 
maximum flexibility and customization.

=== Metadata ===
Success: Yes
Iterations: 7
Tokens: {"input":2340,"output":1580,"total":3920}
Plan steps: 5
Replans: 0
```

### How It Works

1. **Planning Phase:** Agent analyzes the task and creates a detailed step-by-step plan
2. **Execution Phase:** Each step is executed sequentially, with context from previous steps
3. **Monitoring Phase:** After each step, check if replanning is needed
4. **Synthesis Phase:** Combine all step results into final answer

---

## Task Decomposition Strategies

The quality of your plan depends on effective task decomposition. Let's explore different strategies.

### Decomposition Patterns

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

/**
 * Helper function to generate a task decomposition plan.
 */
function decomposeTask(ClaudePhp $client, string $task, string $strategy): array
{
    $strategyPrompts = [
        'sequential' => 'Break this into sequential steps where each step depends on the previous one.',
        'parallel' => 'Break this into independent steps that can be executed in parallel.',
        'hierarchical' => 'Break this into major phases, then break each phase into sub-steps.',
        'iterative' => 'Break this into an iterative cycle of steps that repeat until complete.',
    ];
    
    $prompt = "Task: {$task}\n\n" .
              "{$strategyPrompts[$strategy]}\n\n" .
              "Format each step as:\n1. [Step description]\n2. [Step description]\n...";
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'system' => 'You are a systematic task planner.',
        'messages' => [['role' => 'user', 'content' => $prompt]],
    ]);
    
    $plan = '';
    foreach ($response->content as $block) {
        if (isset($block['type']) && $block['type'] === 'text') {
            $plan .= $block['text'];
        }
    }
    
    // Parse steps
    $steps = [];
    foreach (explode("\n", $plan) as $line) {
        if (preg_match('/^\d+\.\s+(.+)$/', trim($line), $matches)) {
            $steps[] = trim($matches[1]);
        }
    }
    
    return $steps;
}

echo "=== Task Decomposition Strategies ===\n\n";

$task = "Build a user authentication system with email verification";

// Strategy 1: Sequential Decomposition
echo "1. Sequential Decomposition (steps depend on each other):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'sequential');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Strategy 2: Parallel Decomposition
echo "2. Parallel Decomposition (independent steps):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'parallel');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Strategy 3: Hierarchical Decomposition
echo "3. Hierarchical Decomposition (phases → substeps):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'hierarchical');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Strategy 4: Iterative Decomposition
echo "4. Iterative Decomposition (repeating cycles):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'iterative');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Best practices
echo "=== Choosing the Right Strategy ===\n\n";
echo "• Sequential: When steps have strict dependencies (A → B → C)\n";
echo "• Parallel: When steps are independent (A, B, C can happen simultaneously)\n";
echo "• Hierarchical: When task has distinct phases with sub-tasks\n";
echo "• Iterative: When task requires repeated cycles (build → test → refine)\n";
```

### Plan Quality Criteria

**Good plans are:**

1. **Specific:** Clear, actionable steps (not vague goals)
2. **Complete:** Cover all aspects of the task
3. **Ordered:** Steps in logical sequence
4. **Atomic:** Each step is a single, focused action
5. **Testable:** You can verify if each step succeeded

**Example:**

```
❌ BAD PLAN:
1. Set up the system
2. Make it work
3. Test everything

✅ GOOD PLAN:
1. Create database schema with users and email_verifications tables
2. Build registration endpoint with password hashing
3. Implement email verification token generation
4. Create email sending service with verification template
5. Build verification endpoint to confirm email tokens
6. Add login endpoint with email verification check
```

---

## Step Execution and Progress Tracking

Let's build a system that tracks execution progress in detail.

### Detailed Progress Tracking

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

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
```

**Output:**

```
=== Task: Calculate Project Budget ===

📋 Plan recorded with 5 steps

✅ Step 1: Calculate development cost (200 hours × $150/hour)
Progress: [1/5] ████░░░░░░░░░░░░░░░░ 20%

✅ Step 2: Calculate design cost (40 hours × $120/hour)
Progress: [2/5] ████████░░░░░░░░░░░░ 40%

✅ Step 3: Calculate testing cost (30 hours × $100/hour)
Progress: [3/5] ████████████░░░░░░░░ 60%

✅ Step 4: Calculate subtotal and add 15% contingency
Progress: [4/5] ████████████████░░░░ 80%

✅ Step 5: Provide final total budget
Progress: [5/5] ████████████████████ 100%

=== Execution Summary ===
Total steps: 5
Completed: 5
Duration: 23.45s
Avg per step: 4.69s

=== Final Answer ===
Development: $30,000
Design: $4,800
Testing: $3,000
Subtotal: $37,800
Contingency (15%): $5,670
**Final Total: $43,470**
```

---

## Dynamic Replanning

One of PlanExecuteLoop's most powerful features is **dynamic replanning**—adapting the plan when steps fail or circumstances change.

### Replanning When Steps Fail

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a file reader tool that can fail
$fileReader = Tool::create('read_file')
    ->description('Read contents of a file')
    ->parameter('path', 'string', 'File path to read')
    ->required('path')
    ->handler(function (array $input): string {
        $path = $input['path'];
        
        // Simulate some files being unavailable
        if (str_contains($path, 'unavailable')) {
            return "ERROR: File not found or access denied: {$path}";
        }
        
        return "File contents of {$path}: [Sample data from file]";
    });

// Create API call tool that can fail
$apiCall = Tool::create('call_api')
    ->description('Make an API call to external service')
    ->parameter('endpoint', 'string', 'API endpoint to call')
    ->parameter('method', 'string', 'HTTP method (GET, POST, etc.)', required: false)
    ->required('endpoint')
    ->handler(function (array $input): string {
        $endpoint = $input['endpoint'];
        
        // Simulate API failures
        if (str_contains($endpoint, 'legacy-api')) {
            return "ERROR: API endpoint deprecated and no longer available";
        }
        
        return "API response from {$endpoint}: {success: true, data: [...]}";
    });

// Create loop with replanning enabled
$loop = new PlanExecuteLoop(allowReplan: true);

$replanCount = 0;

$loop->onPlanCreated(function ($steps, $context) use (&$replanCount) {
    if ($replanCount === 0) {
        echo "📋 Initial Plan Created\n\n";
    } else {
        echo "🔄 Plan Revised (Replan #{$replanCount})\n\n";
    }
    
    foreach ($steps as $i => $step) {
        echo "  " . ($i + 1) . ". {$step}\n";
    }
    echo "\n";
});

$loop->onStepComplete(function ($stepNumber, $description, $result) use (&$replanCount) {
    // Check if step failed
    if (str_contains(strtolower($result), 'error')) {
        echo "❌ Step {$stepNumber} FAILED\n";
        echo "   {$description}\n";
        echo "   → {$result}\n";
        echo "   🔄 Replanning required...\n\n";
        $replanCount++;
    } else {
        echo "✅ Step {$stepNumber} complete\n";
        echo "   {$description}\n\n";
    }
});

// Create agent
$agent = Agent::create($client)
    ->withTool($fileReader)
    ->withTool($apiCall)
    ->withLoopStrategy($loop)
    ->maxIterations(20);

// Run task that will require replanning
echo "=== Task: Data Migration with Failures ===\n\n";

$task = "Migrate customer data:\n" .
        "1. Read from legacy-api.unavailable.com\n" .
        "2. Transform the data\n" .
        "3. Write to new database\n" .
        "If any step fails, find an alternative approach.";

$result = $agent->run($task);

// Display result
echo "=== Final Result ===\n\n";
echo $result->getAnswer() . "\n\n";

echo "=== Metadata ===\n";
$metadata = $result->getMetadata();
echo "Replans: {$metadata['replan_count']}\n";
echo "Total iterations: " . $result->getIterations() . "\n";
```

**Output:**

```
=== Task: Data Migration with Failures ===

📋 Initial Plan Created

  1. Call the legacy API to retrieve customer data
  2. Transform the retrieved data into the new format
  3. Write the transformed data to the new database

✅ Step 1 complete
   Call the legacy API to retrieve customer data

❌ Step 2 FAILED
   Call legacy-api endpoint to retrieve data
   → ERROR: API endpoint deprecated and no longer available
   🔄 Replanning required...

🔄 Plan Revised (Replan #1)

  1. Read customer data from local backup file instead of API
  2. Transform the data from backup into new format
  3. Write transformed data to new database
  4. Verify migration succeeded

✅ Step 1 complete
   Read customer data from local backup file instead of API

✅ Step 2 complete
   Transform the data from backup into new format

✅ Step 3 complete
   Write transformed data to new database

✅ Step 4 complete
   Verify migration succeeded

=== Final Result ===

Customer data successfully migrated using backup file approach after legacy API was unavailable.
Migration completed with 4 steps and 1 replan. All records verified in new database.

=== Metadata ===
Replans: 1
Total iterations: 9
```

### Replanning Triggers

PlanExecuteLoop automatically replans when it detects failure indicators in step results:

```php
// From PlanExecuteLoop source
private function shouldReplan(string $step, string $result): bool
{
    $failureIndicators = ['error', 'failed', 'unable', 'cannot', 'impossible'];
    
    $resultLower = strtolower($result);
    foreach ($failureIndicators as $indicator) {
        if (str_contains($resultLower, $indicator)) {
            return true;
        }
    }
    
    return false;
}
```

---

## Planning with Tools

Plans become powerful when combined with tool execution.

### Multi-Tool Planning

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agents\PlanExecuteAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a rich tool set
$tools = [
    // Database query tool
    Tool::create('query_database')
        ->description('Query the database for information')
        ->parameter('query', 'string', 'SQL-like query description')
        ->required('query')
        ->handler(fn ($input) => "Query results: [Sample data for: {$input['query']}]"),
    
    // Email sending tool
    Tool::create('send_email')
        ->description('Send an email notification')
        ->parameter('to', 'string', 'Recipient email')
        ->parameter('subject', 'string', 'Email subject')
        ->parameter('body', 'string', 'Email body')
        ->required('to', 'subject', 'body')
        ->handler(fn ($input) => "Email sent to {$input['to']}: {$input['subject']}"),
    
    // Report generation tool
    Tool::create('generate_report')
        ->description('Generate a formatted report')
        ->parameter('data', 'string', 'Data to include in report')
        ->parameter('format', 'string', 'Report format (PDF, HTML, etc.)')
        ->required('data', 'format')
        ->handler(fn ($input) => "Report generated in {$input['format']} format"),
    
    // File storage tool
    Tool::create('store_file')
        ->description('Store a file in the file system')
        ->parameter('filename', 'string', 'Name of file to store')
        ->parameter('content', 'string', 'File content')
        ->required('filename', 'content')
        ->handler(fn ($input) => "File stored: {$input['filename']}"),
];

// Create PlanExecuteAgent with tools
$agent = new PlanExecuteAgent($client, [
    'tools' => $tools,
    'allow_replan' => true,
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
]);

echo "=== Task: Weekly Sales Report Automation ===\n\n";

$task = "Generate and distribute the weekly sales report:\n" .
        "1. Query last 7 days of sales data\n" .
        "2. Calculate key metrics (total revenue, top products, growth %)\n" .
        "3. Generate a PDF report\n" .
        "4. Email the report to sales@company.com\n" .
        "5. Store a copy in the reports archive";

$result = $agent->run($task);

echo "=== Result ===\n\n";
echo $result->getAnswer() . "\n\n";

echo "=== Execution Details ===\n";
$metadata = $result->getMetadata();

echo "Steps executed: " . count($metadata['step_results']) . "\n";
echo "Tools used:\n";

foreach ($metadata['step_results'] as $step) {
    // Extract tool usage from results
    if (str_contains($step['result'], 'Query results') || 
        str_contains($step['result'], 'Email sent') || 
        str_contains($step['result'], 'Report generated') ||
        str_contains($step['result'], 'File stored')) {
        echo "  • Step {$step['step']}: Used tools\n";
    }
}

echo "\nToken usage: " . json_encode($result->getTokenUsage()) . "\n";
```

---

## ML-Optimized Planning

The framework's `PlanExecuteAgent` includes ML-enhanced features that learn optimal planning strategies over time.

### Enabling ML Optimization

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agents\PlanExecuteAgent;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create ML-optimized agent
$agent = new PlanExecuteAgent($client, [
    'enable_ml_optimization' => true,
    'ml_history_path' => __DIR__ . '/plan_history.json',
    'allow_replan' => true,
]);

echo "=== ML-Optimized Planning Demo ===\n\n";

// Run multiple similar tasks
$tasks = [
    "Research and compare 3 PHP frameworks for REST APIs",
    "Research and compare 3 JavaScript frameworks for web apps",
    "Research and compare 3 Python frameworks for data science",
];

foreach ($tasks as $i => $task) {
    echo "Task " . ($i + 1) . ": {$task}\n";
    echo str_repeat('-', 60) . "\n";
    
    $result = $agent->run($task);
    
    $metadata = $result->getMetadata();
    
    echo "Plan steps: {$metadata['plan_steps']}\n";
    echo "Detail level: {$metadata['detail_level']}\n";
    echo "ML enabled: " . ($metadata['ml_enabled'] ? 'Yes' : 'No') . "\n";
    echo "Iterations: " . $result->getIterations() . "\n\n";
}

echo "=== ML Learning Summary ===\n\n";
echo "The agent learns:\n";
echo "• Optimal plan detail level (high/medium/low) per task type\n";
echo "• Ideal number of steps for different task complexities\n";
echo "• When replanning is beneficial vs. wasteful\n";
echo "• Cost/quality trade-offs for planning granularity\n\n";

echo "Over time, the agent becomes 15-25% more efficient by:\n";
echo "✓ Avoiding overly detailed plans for simple tasks\n";
echo "✓ Adding more detail for complex tasks that benefit from it\n";
echo "✓ Reducing unnecessary replanning attempts\n";
echo "✓ Optimizing token usage while maintaining quality\n";
```

### ML Optimization Benefits

From the `PlanExecuteAgent` source:

```php
/**
 * **ML-Enhanced Features:**
 * - Learns optimal plan granularity (detail level)
 * - Learns when replanning is beneficial
 * - Optimizes step count per task type
 * - Reduces unnecessary planning overhead by 15-25%
 */
```

The agent tracks:

- **Plan detail level:** High, medium, or low granularity
- **Max steps:** Optimal number of steps for task type
- **Replan effectiveness:** When replanning helps vs. wastes tokens
- **Quality scores:** Success rates and result quality

---

## Production Planning System

Let's build a complete production-ready planning system.

### Complete Production Implementation

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agents\PlanExecuteAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ProductionPlanningSystem
{
    private ClaudePhp $client;
    private Logger $logger;
    private array $tools = [];
    
    public function __construct(string $apiKey)
    {
        $this->client = new ClaudePhp(apiKey: $apiKey);
        
        // Setup logger
        $this->logger = new Logger('planning-system');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        
        $this->setupTools();
        
        $this->logger->info('Production planning system initialized');
    }
    
    private function setupTools(): void
    {
        // Add production tools
        $this->tools[] = Tool::create('execute_query')
            ->description('Execute a database query')
            ->parameter('query', 'string', 'Query to execute')
            ->required('query')
            ->handler(function (array $input): string {
                $this->logger->info('Executing query', ['query' => substr($input['query'], 0, 100)]);
                // Simulate query execution
                return json_encode(['success' => true, 'rows' => 42]);
            });
        
        $this->tools[] = Tool::create('send_notification')
            ->description('Send notification via email, SMS, or Slack')
            ->parameter('channel', 'string', 'Notification channel (email, sms, slack)')
            ->parameter('message', 'string', 'Notification message')
            ->required('channel', 'message')
            ->handler(function (array $input): string {
                $this->logger->info('Sending notification', [
                    'channel' => $input['channel'],
                    'message' => substr($input['message'], 0, 50),
                ]);
                return json_encode(['sent' => true, 'channel' => $input['channel']]);
            });
        
        $this->tools[] = Tool::create('log_metric')
            ->description('Log a metric for monitoring')
            ->parameter('metric_name', 'string', 'Name of the metric')
            ->parameter('value', 'string', 'Metric value')
            ->required('metric_name', 'value')
            ->handler(function (array $input): string {
                $this->logger->info('Logging metric', [
                    'metric' => $input['metric_name'],
                    'value' => $input['value'],
                ]);
                return json_encode(['logged' => true]);
            });
    }
    
    public function executePlan(string $task, array $options = []): array
    {
        $startTime = microtime(true);
        
        $this->logger->info('Starting plan execution', ['task' => substr($task, 0, 100)]);
        
        // Create agent with all features
        $agent = new PlanExecuteAgent($this->client, array_merge([
            'tools' => $this->tools,
            'allow_replan' => true,
            'enable_ml_optimization' => true,
            'ml_history_path' => __DIR__ . '/production_plan_history.json',
            'logger' => $this->logger,
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
        ], $options));
        
        // Execute
        $result = $agent->run($task);
        
        $duration = microtime(true) - $startTime;
        
        // Build response
        $response = [
            'success' => $result->isSuccess(),
            'answer' => $result->getAnswer(),
            'metadata' => [
                'duration' => round($duration, 3),
                'iterations' => $result->getIterations(),
                'token_usage' => $result->getTokenUsage(),
                'plan_metadata' => $result->getMetadata(),
            ],
        ];
        
        if (!$result->isSuccess()) {
            $response['error'] = $result->getError();
            $this->logger->error('Plan execution failed', ['error' => $result->getError()]);
        } else {
            $this->logger->info('Plan execution completed', [
                'duration' => $duration,
                'iterations' => $result->getIterations(),
            ]);
        }
        
        return $response;
    }
    
    public function executeBatch(array $tasks): array
    {
        $results = [];
        
        foreach ($tasks as $i => $task) {
            $this->logger->info("Executing batch task " . ($i + 1) . "/" . count($tasks));
            $results[] = $this->executePlan($task);
        }
        
        return $results;
    }
}

// Usage
$system = new ProductionPlanningSystem(getenv('ANTHROPIC_API_KEY'));

echo "=== Production Planning System ===\n\n";

// Execute a complex task
$task = "Process monthly sales report:\n" .
        "1. Query sales data for last 30 days\n" .
        "2. Calculate key metrics (revenue, growth, top products)\n" .
        "3. Generate summary report\n" .
        "4. Send notifications to stakeholders\n" .
        "5. Log completion metrics";

$result = $system->executePlan($task);

echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
echo "Duration: {$result['metadata']['duration']}s\n";
echo "Iterations: {$result['metadata']['iterations']}\n";
echo "Tokens: " . json_encode($result['metadata']['token_usage']) . "\n\n";

echo "Answer:\n";
echo $result['answer'] . "\n\n";

// Execute batch tasks
echo "=== Batch Execution ===\n\n";

$batchTasks = [
    "Generate Q1 financial summary",
    "Process customer feedback survey results",
    "Create inventory restock recommendations",
];

$batchResults = $system->executeBatch($batchTasks);

echo "Completed " . count($batchResults) . " tasks\n";
$successCount = count(array_filter($batchResults, fn($r) => $r['success']));
echo "Success rate: " . round(($successCount / count($batchResults)) * 100) . "%\n";
```

---

## Summary

In this chapter, you learned how to build planning agents that systematically decompose and execute complex tasks:

✅ **Plan-Execute pattern** — Separate planning from execution for structured workflows  
✅ **Task decomposition** — Break complex tasks into actionable steps  
✅ **PlanExecuteLoop** — Framework loop for systematic execution  
✅ **Progress tracking** — Monitor execution and report status  
✅ **Dynamic replanning** — Adapt plans when steps fail  
✅ **Tool integration** — Execute plans with tool capabilities  
✅ **ML optimization** — Learn optimal planning strategies over time  
✅ **Production systems** — Deploy robust planning agents

With planning agents, you can now tackle complex multi-step workflows that require structure, progress visibility, and adaptability.

---

## Practice Exercises

### Exercise 1: Build a Deployment Pipeline Agent

Create a planning agent for software deployment:

- Generate deployment plan (build → test → deploy → verify)
- Execute each step with appropriate tools
- Replan if tests fail or deployment errors occur
- Track progress and send notifications

### Exercise 2: Implement Parallel Step Execution

Extend the planning system to execute independent steps in parallel:

- Analyze plan to identify parallel-safe steps
- Execute parallel steps concurrently
- Wait for all parallel steps before proceeding
- Aggregate results from parallel execution

### Exercise 3: Add Plan Validation

Build a plan validator that checks plans before execution:

- Verify all required resources are available
- Check for missing dependencies between steps
- Estimate execution time and cost
- Suggest plan improvements

### Exercise 4: Create a Learning Planner

Build an agent that improves planning over time:

- Track which plans succeed vs. fail
- Learn optimal step counts for task types
- Identify common failure patterns
- Suggest better decomposition strategies

---

## Next Steps

Now that you have planning agents that can decompose and execute complex tasks, you're ready to add **self-improvement through reflection**. In **Chapter 10: Reflection and Self-Review Loops**, you'll implement reflection patterns where agents critique and refine their own outputs, using `ReflectionLoop` for quality-focused tasks.

Continue to [Chapter 10](/series/agentic-ai-php-developers/chapters/10-reflection-and-self-review-loops) →

---

## Further Reading

- [`claude-php/claude-php-agent` PlanExecuteLoop](https://github.com/claude-php/claude-php-agent/blob/master/src/Loops/PlanExecuteLoop.php)
- [`claude-php/claude-php-agent` PlanExecuteAgent](https://github.com/claude-php/claude-php-agent/blob/master/src/Agents/PlanExecuteAgent.php)
- [Loop Strategies Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/loop-strategies.md)
- [ReAct vs Plan-Execute Patterns](https://www.promptingguide.ai/techniques/react)
- [Task Decomposition in AI Agents](https://lilianweng.github.io/posts/2023-06-23-agent/)
