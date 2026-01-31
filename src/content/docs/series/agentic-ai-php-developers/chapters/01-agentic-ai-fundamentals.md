---
title: "01: Agentic AI Fundamentals"
description: "Understand agentic AI vs. plain LLM calls and define agents, tools, memory, and control loops"
series: "agentic-ai-php-developers"
chapter: 1
difficulty: "Intermediate"
---

# Chapter 01: Agentic AI Fundamentals

## Overview

Before we build production-grade agents with [`claude-php/claude-php/agent`](https://github.com/claude-php/claude-php/agent), we need a shared mental model. "Agentic AI" isn't magic — it's a disciplined way of wrapping an LLM with **tools**, **memory**, and a **control loop** so it can plan, act, and recover like a real system.

The `claude-php/agent` framework implements these patterns as first-class PHP abstractions. In this chapter, you'll learn the concepts that power the framework, then see how they map to real code.

In this chapter you'll:

- Contrast **plain LLM calls** with **agentic workflows**
- Define the core building blocks: **agents, tools, memory, control loops**
- Learn how `claude-php/agent` implements the **ReAct loop** pattern
- See how agentic systems handle **state, reliability, and observability**
- Understand the framework's architecture before using it in Chapter 02

**Estimated time:** ~60 minutes

::: info Code examples
Complete, runnable examples for this chapter:

- [`plain-llm.php`](../code/01-agentic-ai-fundamentals/plain-llm.php) — Simple one-shot simulation
- [`agentic-loop.php`](../code/01-agentic-ai-fundamentals/agentic-loop.php) — Deterministic agent loop with tracing

All files are in [`code/01-agentic-ai-fundamentals/`](../code/01-agentic-ai-fundamentals/README.md).
:::

---

## Plain LLM Calls vs. Agentic Systems

A plain LLM call is a single request/response cycle:

1. You send a prompt.
2. The model returns text.
3. The process ends.

That's enough for short answers, but it breaks down for tasks that require **multiple steps**, **external data**, or **reliability**. You can't ask a single prompt to:

- Check a database
- Validate an API response
- Retry a failed call
- Maintain state across multiple steps

That's where **agentic AI** comes in. Agentic systems wrap the model in **structure** that lets it *plan* and *act*.

### Plain LLM Call (One Shot)

```text
Prompt → LLM → Text
```

### Agentic Loop (Multi-Step)

```text
Prompt → LLM → Tool Call → Tool Result → LLM → … → Final Response
```

### Comparison Table

| Capability | Plain LLM Call | Agentic System |
| --- | --- | --- |
| External data access | ❌ | ✅ (tools) |
| State across steps | ❌ | ✅ (memory) |
| Reliability / retries | ❌ | ✅ (control loop) |
| Auditability | ⚠️ limited | ✅ (logs + traces) |
| Multi-step workflows | ❌ | ✅ |

---

## The Four Core Building Blocks

### 1. Agents

An **agent** is an LLM wrapped in runtime logic. It's not just "the model" — it's the model plus rules, memory, and a loop that decides what to do next.

**In `claude-php/agent`:**

The `Agent` class is the core abstraction. You create agents using the fluent builder pattern:

```php
use ClaudePhp\Agent\Agent;

$agent = Agent::make(apiKey: getenv('ANTHROPIC_API_KEY'))
    ->withSystemPrompt('You are a helpful assistant.')
    ->withTools([$searchTool, $calculatorTool])
    ->withMemory($memory)
    ->maxIterations(10);
```

**Agent responsibilities:**

- Interpret goals
- Decide which tools to use
- Track state
- Produce a final answer

### 2. Tools

A **tool** is a function the agent can call to perform actions in the real world: API requests, database queries, file reads, calculations, etc.

**In `claude-php/agent`:**

Tools are defined using the `Tool` class with a fluent API:

```php
use ClaudePhp\Agent\Tools\Tool;

$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input): string {
        return (string) eval("return {$input['expression']};");
    });
```

**Why tools matter:**

- LLMs can't access real data on their own
- Tools provide grounding and reliability
- Tool results can be validated and retried

The framework handles:
- Schema validation
- Error handling
- Execution tracking
- Result formatting

### 3. Memory

**Memory** is any state the agent can access across turns or steps.

- **Short-term memory**: the current conversation context
- **Long-term memory**: stored facts or history across sessions
- **Working memory**: intermediate results inside a loop (tool outputs, progress)

**In `claude-php/agent`:**

Memory is managed through the `Memory` interface:

```php
use ClaudePhp\Agent\Memory\Memory;
use ClaudePhp\Agent\Memory\FileMemory;

// In-memory state
$memory = new Memory();
$memory->set('user_preference', 'dark_mode');

// Persistent file-based memory
$memory = new FileMemory('/path/to/state.json');

$agent = Agent::make()->withMemory($memory);
```

Memory is what lets agents stay coherent over longer tasks.

### 4. Control Loops

A **control loop** is the orchestration logic that runs the agent until a task is complete. A typical loop looks like this:

1. **Plan**: decide the next action
2. **Act**: call a tool (if needed)
3. **Observe**: read tool output
4. **Reflect**: update memory
5. **Repeat** until done

**In `claude-php/agent`:**

The framework provides multiple loop strategies:

- **ReactLoop** (default): Reason-Act-Observe pattern
- **PlanExecuteLoop**: Plan first, then execute systematically
- **ReflectionLoop**: Generate, reflect, and refine
- **StreamingLoop**: Real-time progress updates

```php
use ClaudePhp\Agent\Loops\ReactLoop;
use ClaudePhp\Agent\Loops\PlanExecuteLoop;

// Use the ReAct loop (default)
$agent = Agent::make()
    ->withLoopStrategy(new ReactLoop())
    ->maxIterations(10);

// Or use Plan-Execute for complex tasks
$agent = Agent::make()
    ->withLoopStrategy(new PlanExecuteLoop(allowReplan: true));
```

We'll build loops that are safe (bounded), observable (logged), and recoverable (retries + fallbacks).

---

## The Agentic Mental Model (We'll Use This All Series)

When you hear "agent," think **loop + tools + memory**. Every system we build later will fit this model:

```text
Goal → Plan → Tool → Observation → Memory Update → Next Plan → … → Final Output
```

**Key idea:** The LLM is *not* the system — it's just one component inside a loop.

This is exactly how `claude-php/agent` structures agents:

```php
// The Agent orchestrates everything
$agent = Agent::make($client)
    ->withTools($tools)        // What the agent can do
    ->withMemory($memory)      // What the agent remembers
    ->withLoopStrategy($loop)  // How the agent thinks
    ->run($task);              // Execute until complete
```

---

## Agent Lifecycle (From Input to Outcome)

When you run an agent, there's a predictable lifecycle even if the model output changes:

1. **Initialize** — load tools, memory, and policies
2. **Plan** — decide the next action based on the goal + state
3. **Act** — call tools or generate a response
4. **Observe** — capture tool results or intermediate output
5. **Update memory** — store summaries or facts
6. **Finalize** — produce the user-facing response

**In `claude-php/agent`:**

You can hook into every stage:

```php
$agent = Agent::make($client)
    ->onToolExecution(function (string $tool, array $input, ToolResult $result) {
        // Monitor tool usage
    })
    ->onUpdate(function (AgentUpdate $update) {
        // Track progress in real-time
    })
    ->onError(function (Throwable $e, int $attempt) {
        // Handle errors
    })
    ->run($task);
```

Thinking in lifecycle stages helps you pinpoint failures (tool errors, bad memory, or unclear plans) and add targeted safeguards later.

---

## Minimal Agentic Loop Example (Deterministic)

Let's make it real. The example in this chapter uses a **simple deterministic loop** to show how an agent chooses tools, stores results in memory, and finishes a response. We'll connect this to real LLM calls in Chapter 02.

### What the Example Demonstrates

- A **plain call** that can't fetch real data
- An **agent loop** that calls a `get_date` tool
- Memory used to store the tool result
- A max-iteration limit to prevent infinite loops
- A trace of steps for observability

```php
<?php

declare(strict_types=1);

$task = 'Explain agentic AI and include today\'s date.';

$tools = [
    'get_date' => fn (): string => date('Y-m-d'),
];

$memory = [
    'date' => null,
    'final' => null,
    'trace' => [],
];

for ($step = 1; $step <= 3; $step++) {
    $memory['trace'][] = "Step {$step}: plan next action.";

    if ($memory['date'] === null) {
        $memory['trace'][] = 'Action: call tool get_date';
        $memory['date'] = $tools['get_date']();
        $memory['trace'][] = "Observation: stored date {$memory['date']}";
        continue;
    }

    $memory['trace'][] = 'Action: compose final response';
    $memory['final'] = "Agentic AI wraps LLMs with tools, memory, and control loops. Today is {$memory['date']}.";
    break;
}

echo $memory['final'] . PHP_EOL;
```

You'll implement the full example in the code folder and run it locally.

**This simple pattern is exactly what `claude-php/agent` does internally**, but with:
- Real LLM decision-making
- Robust error handling
- Production-grade observability
- Type-safe tool schemas

---

## Anatomy of an Agent in PHP

Think in layers:

1. **Model interface** — the LLM API client (Claude)
2. **Prompt + policy** — system instructions and role constraints
3. **Tool registry** — structured list of tools and schemas
4. **Runtime loop** — plan → act → observe → reflect
5. **Memory store** — session state, summaries, and long-term facts
6. **Telemetry** — logs, traces, and usage metrics

**This is the `claude-php/agent` architecture:**

```php
// 1. Model interface
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// 2. Prompt + policy
$agent = Agent::make($client)
    ->withSystemPrompt('You are a helpful assistant.')
    
    // 3. Tool registry
    ->withTools([$searchTool, $calculatorTool, $databaseTool])
    
    // 4. Runtime loop
    ->withLoopStrategy(new ReactLoop())
    ->maxIterations(10)
    
    // 5. Memory store
    ->withMemory(new FileMemory('/path/to/state.json'))
    
    // 6. Telemetry
    ->withLogger($psrLogger)
    ->onUpdate(function ($update) { /* track progress */ });

// Execute
$result = $agent->run($task);
```

A minimal loop can be sketched as:

```text
while not done:
  plan = model.decide(next_action, memory)
  if plan.action == "tool":
    result = tools.execute(plan.tool_name, plan.input)
    memory.update(result)
  else:
    done = true
    output = plan.response
```

---

## Tool Contracts and Validation Basics

Tools must be more than "callable functions." They need **contracts**:

- **Input validation** (schema checks, required parameters)
- **Output shape guarantees** (consistent keys, predictable data types)
- **Timeouts + retries** for reliability
- **Error wrapping** so the agent can reason about failures

**`claude-php/agent` enforces tool contracts:**

```php
$weatherTool = Tool::create('get_weather')
    ->description('Get current weather for a location')
    ->parameter('city', 'string', 'City name')
    ->parameter('units', 'string', 'Temperature units (celsius/fahrenheit)', false)
    ->required('city')
    ->handler(function (array $input): string {
        // Framework validates $input['city'] exists and is a string
        // Your code just implements the logic
        return json_encode(['temp' => 72, 'conditions' => 'sunny']);
    });
```

When tools behave predictably, the LLM can make safer plans. When they don't, agents produce unstable or misleading outputs. We'll implement full validation and error handling later, but the mental model starts here.

---

## Memory: Persistence, Summaries, and Context Windows

Memory isn't just "more tokens." Real systems combine:

- **Session memory** (short-term conversation context)
- **Summaries** (compressed history after context grows)
- **Persistent memory** (facts stored in a database or vector store)

This prevents context windows from overflowing and keeps the agent grounded across sessions. Later chapters will show how to build a PHP-backed memory store.

---

## Policies and Instructions: Keeping Agents Aligned

Even the best tools and memory won't help if the agent doesn't follow instructions. Policies (system prompts, role constraints, or safety rules) are how you keep an agent aligned with product goals.

Think of policies as **guardrails** that shape the plan step before any tool is called.

**In `claude-php/agent`:**

```php
$agent = Agent::make($client)
    ->withSystemPrompt(
        'You are a customer service assistant. ' .
        'Always be polite and professional. ' .
        'Never share customer data with unauthorized users.'
    )
    ->run($task);
```

---

## Observability and Debuggability

Agentic systems must be observable, or you won't know *why* a response failed. At minimum, log:

- The plan the model chose
- Which tools were called and with what inputs
- Tool outputs (or errors)
- Final response + latency

**`claude-php/agent` provides production-grade observability:**

```php
use ClaudePhp\Agent\Progress\AgentUpdate;

$agent = Agent::make($client)
    ->withLogger($psrLogger)  // PSR-3 compatible logging
    ->onUpdate(function (AgentUpdate $update) {
        // Real-time progress updates
        echo "Type: {$update->getType()}\n";
        print_r($update->getData());
    })
    ->onToolExecution(function ($tool, $input, $result) {
        // Monitor every tool call
    })
    ->run($task);
```

This chapter's example prints a trace to show the concept; later chapters will formalize tracing and metrics.

---

## Boundaries and Failure Modes

Agents fail in predictable ways. Recognizing boundaries early helps prevent production issues:

- **Model limitations** — LLMs don't have live data without tools
- **Tool failures** — network timeouts, invalid inputs, bad auth
- **Memory drift** — outdated facts or hallucinated context
- **Infinite loops** — missing exit conditions or bad planning

Each failure maps to a fix: tools + validation, memory pruning, max-step guards, and explicit stop criteria.

**`claude-php/agent` handles common failures:**

```php
$agent = Agent::make($client)
    ->withRetry(maxAttempts: 3, delayMs: 1000)
    ->maxIterations(10)  // Prevent infinite loops
    ->onError(function (Throwable $e, int $attempt) {
        // Custom error handling
    })
    ->run($task);
```

---

## Mini Scenario: Agentic vs. Plain LLM

**Task:** "Summarize today's open orders and flag any overdue shipments."

### Plain LLM (non-agentic)

The model can only guess. It has no database access, so it hallucinates order details.

### Agentic Workflow with `claude-php/agent`

```php
$getOrders = Tool::create('get_open_orders')
    ->description('Fetch open orders from database')
    ->handler(function () {
        return json_encode(DB::query('SELECT * FROM orders WHERE status = "open"'));
    });

$getOverdue = Tool::create('get_overdue_shipments')
    ->description('Find shipments past their due date')
    ->handler(function () {
        return json_encode(DB::query('SELECT * FROM shipments WHERE due_date < NOW()'));
    });

$agent = Agent::make($client)
    ->withTools([$getOrders, $getOverdue])
    ->run('Summarize open orders and flag overdue shipments');
```

1. Tool call: `get_open_orders` (database query)
2. Tool call: `get_overdue_shipments`
3. Compose summary with real data

This is the exact structure you'll build later in PHP: tools do the data work, the LLM does the reasoning, and the control loop keeps everything safe and observable.

---

## Reliability: What Breaks Without Agent Structure

Agentic design isn't just a fancy architecture — it prevents common failures:

- **Hallucinated data** → fixed by tool calls + validation
- **Infinite loops** → fixed by max steps and safe exit conditions
- **Unclear errors** → fixed by structured logs and error handling
- **State loss** → fixed by memory and summaries

We'll turn these into concrete PHP patterns in later chapters using `claude-php/agent`.

---

## Understanding the Framework Patterns

The `claude-php/agent` framework provides multiple agent patterns for different use cases:

| Pattern | Use Case | Complexity | Example |
| --- | --- | --- | --- |
| **ReactAgent** | General-purpose tasks | Medium | Research, calculations |
| **HierarchicalAgent** | Multi-domain tasks | High | Master-worker delegation |
| **ReflectionAgent** | Quality-critical output | Medium | Code generation, writing |
| **MakerAgent** | Million-step tasks | Very High | Complex sequences |

You'll learn to implement these patterns in later chapters.

---

## Chapter Checklist

By the end of this chapter, you should be able to:

- Explain the difference between a plain LLM call and an agentic loop
- Define agent, tool, memory, and control loop
- Describe how `claude-php/agent` implements these concepts
- Explain why tools and memory make LLMs reliable
- Run the starter example and understand each step
- Recognize the framework's architecture patterns

---

## Next Steps

In **Chapter 02: Setting Up `claude-php/agent`**, we'll install the framework, build a "hello agent" that uses real tools, and inspect the full agent lifecycle with actual Claude API calls.

---

## Further Reading

To deepen your understanding of the topics covered in this chapter:

- [Claude PHP Agent GitHub](https://github.com/claude-php/claude-php/agent) — Framework source code and documentation
- [Anthropic: Agents Overview](https://docs.anthropic.com/en/docs/agents-overview) — Conceptual guide to building agents
- [Claude PHP Agent Quick Start](https://github.com/claude-php/claude-php/agent/blob/master/QUICKSTART.md) — Get started in 5 minutes
- [Framework Documentation Index](https://github.com/claude-php/claude-php/agent/tree/master/docs) — Complete guide to all features
