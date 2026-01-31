---
title: "01: Agentic AI Fundamentals"
description: "Understand agentic AI vs. plain LLM calls. Define agents, tools, memory, and control loops for the rest of the series."
---

# Chapter 01: Agentic AI Fundamentals

## Overview

Before we write advanced PHP agents, we need a shared mental model. "Agentic AI" isn’t magic — it’s a disciplined way of wrapping an LLM with **tools**, **memory**, and a **control loop** so it can plan, act, and recover like a real system.

In this chapter you’ll:

- Contrast **plain LLM calls** with **agentic workflows**
- Define the core building blocks: **agents, tools, memory, control loops**
- Learn the **agent loop** as a repeatable architecture pattern
- See how agentic systems handle **state, reliability, and observability**
- Run a deterministic example so the architecture is concrete

**Estimated time:** ~60 minutes

::: info Code examples
Use the runnable examples in [`code/01-agentic-ai-fundamentals`](../code/01-agentic-ai-fundamentals/README.md).
:::

---

## Plain LLM Calls vs. Agentic Systems

A plain LLM call is a single request/response cycle:

1. You send a prompt.
2. The model returns text.
3. The process ends.

That’s enough for short answers, but it breaks down for tasks that require **multiple steps**, **external data**, or **reliability**. You can’t ask a single prompt to:

- Check a database
- Validate an API response
- Retry a failed call
- Maintain state across multiple steps

That’s where **agentic AI** comes in. Agentic systems wrap the model in **structure** that lets it *plan* and *act*.

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

An **agent** is an LLM wrapped in runtime logic. It’s not just “the model” — it’s the model plus rules, memory, and a loop that decides what to do next.

**Agent responsibilities:**

- Interpret goals
- Decide which tools to use
- Track state
- Produce a final answer

### 2. Tools

A **tool** is a function the agent can call to perform actions in the real world: API requests, database queries, file reads, calculations, etc.

**Why tools matter:**

- LLMs can’t access real data on their own
- Tools provide grounding and reliability
- Tool results can be validated and retried

A typical tool is defined with a name, description, and input schema:

```json
{
  "name": "get_customer",
  "description": "Fetch a customer by email",
  "input_schema": {
    "type": "object",
    "properties": {
      "email": { "type": "string" }
    },
    "required": ["email"]
  }
}
```

### 3. Memory

**Memory** is any state the agent can access across turns or steps.

- **Short-term memory**: the current conversation context
- **Long-term memory**: stored facts or history across sessions
- **Working memory**: intermediate results inside a loop (tool outputs, progress)

Memory is what lets agents stay coherent over longer tasks.

### 4. Control Loops

A **control loop** is the orchestration logic that runs the agent until a task is complete. A typical loop looks like this:

1. **Plan**: decide the next action
2. **Act**: call a tool (if needed)
3. **Observe**: read tool output
4. **Reflect**: update memory
5. **Repeat** until done

We’ll build loops that are safe (bounded), observable (logged), and recoverable (retries + fallbacks).

---

## The Agentic Mental Model (We’ll Use This All Series)

When you hear “agent,” think **loop + tools + memory**. Every system we build later will fit this model:

```text
Goal → Plan → Tool → Observation → Memory Update → Next Plan → … → Final Output
```

**Key idea:** The LLM is *not* the system — it’s just one component inside a loop.

---

## Agent Lifecycle (From Input to Outcome)

When you run an agent, there’s a predictable lifecycle even if the model output changes:

1. **Initialize** — load tools, memory, and policies
2. **Plan** — decide the next action based on the goal + state
3. **Act** — call tools or generate a response
4. **Observe** — capture tool results or intermediate output
5. **Update memory** — store summaries or facts
6. **Finalize** — produce the user-facing response

Thinking in lifecycle stages helps you pinpoint failures (tool errors, bad memory, or unclear plans) and add targeted safeguards later.

---

## Minimal Agentic Loop Example (Deterministic)

Let’s make it real. The example in this chapter uses a **simple deterministic loop** to show how an agent chooses tools, stores results in memory, and finishes a response. We’ll connect this to real LLM calls in Chapter 02.

### What the Example Demonstrates

- A **plain call** that can’t fetch real data
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

You’ll implement the full example in the code folder and run it locally.

---

## Anatomy of an Agent in PHP

Think in layers:

1. **Model interface** — the LLM API client (Claude)
2. **Prompt + policy** — system instructions and role constraints
3. **Tool registry** — structured list of tools and schemas
4. **Runtime loop** — plan → act → observe → reflect
5. **Memory store** — session state, summaries, and long-term facts
6. **Telemetry** — logs, traces, and usage metrics

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

Tools must be more than “callable functions.” They need **contracts**:

- **Input validation** (schema checks, required parameters)
- **Output shape guarantees** (consistent keys, predictable data types)
- **Timeouts + retries** for reliability
- **Error wrapping** so the agent can reason about failures

When tools behave predictably, the LLM can make safer plans. When they don’t, agents produce unstable or misleading outputs. We’ll implement full validation and error handling later, but the mental model starts here.

---

## Memory: Persistence, Summaries, and Context Windows

Memory isn’t just “more tokens.” Real systems combine:

- **Session memory** (short-term conversation context)
- **Summaries** (compressed history after context grows)
- **Persistent memory** (facts stored in a database or vector store)

This prevents context windows from overflowing and keeps the agent grounded across sessions. Later chapters will show how to build a PHP-backed memory store.

---

## Policies and Instructions: Keeping Agents Aligned

Even the best tools and memory won’t help if the agent doesn’t follow instructions. Policies (system prompts, role constraints, or safety rules) are how you keep an agent aligned with product goals.

Think of policies as **guardrails** that shape the plan step before any tool is called.

---

## Observability and Debuggability

Agentic systems must be observable, or you won’t know *why* a response failed. At minimum, log:

- The plan the model chose
- Which tools were called and with what inputs
- Tool outputs (or errors)
- Final response + latency

This chapter’s example prints a trace to show the concept; later chapters will formalize tracing and metrics.

---

## Boundaries and Failure Modes

Agents fail in predictable ways. Recognizing boundaries early helps prevent production issues:

- **Model limitations** — LLMs don’t have live data without tools
- **Tool failures** — network timeouts, invalid inputs, bad auth
- **Memory drift** — outdated facts or hallucinated context
- **Infinite loops** — missing exit conditions or bad planning

Each failure maps to a fix: tools + validation, memory pruning, max-step guards, and explicit stop criteria.

---

## Mini Scenario: Agentic vs. Plain LLM

**Task:** “Summarize today’s open orders and flag any overdue shipments.”

### Plain LLM (non-agentic)

The model can only guess. It has no database access, so it hallucinates order details.

### Agentic Workflow

1. Tool call: `get_open_orders` (database query)
2. Tool call: `get_overdue_shipments`
3. Compose summary with real data

This is the exact structure you’ll build later in PHP: tools do the data work, the LLM does the reasoning, and the control loop keeps everything safe and observable.

---

## Reliability: What Breaks Without Agent Structure

Agentic design isn’t just a fancy architecture — it prevents common failures:

- **Hallucinated data** → fixed by tool calls + validation
- **Infinite loops** → fixed by max steps and safe exit conditions
- **Unclear errors** → fixed by structured logs and error handling
- **State loss** → fixed by memory and summaries

We’ll turn these into concrete PHP patterns in later chapters.

---

## Chapter Checklist

By the end of this chapter, you should be able to:

- Explain the difference between a plain LLM call and an agentic loop
- Define agent, tool, memory, and control loop
- Describe the loop-based mental model for agents
- Explain why tools and memory make LLMs reliable
- Run the starter example and understand each step

---

## Next Steps

In **Chapter 02**, we’ll connect this model to the real `claude-php-agent` runtime, build a “hello agent” that uses tools, and inspect the full agent lifecycle.

---

## Resources

- [Claude PHP Agent GitHub](https://github.com/claude-php/claude-php-agent)
- [Anthropic Agents Overview](https://docs.anthropic.com/en/docs/agents-overview)
