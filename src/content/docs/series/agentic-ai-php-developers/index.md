---
title: "Agentic AI for PHP Developers"
description: "Build production-grade AI agents with PHP 8.4—master planning, memory, tool orchestration, and multi-agent systems."
series: "agentic-ai-php-developers"
difficulty: "Advanced"
keywords: ["Agentic AI PHP", "AI Agents PHP", "Claude PHP Agent", "PHP AI Framework"]
teaches: ["Agent architecture design", "Tool execution pipelines", "Short and long-term memory systems", "Retrieval-Augmented Generation (RAG)", "Multi-agent orchestration"]
---

# Agentic AI for PHP Developers <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Welcome to **Agentic AI for PHP Developers** — a comprehensive, hands-on series that teaches you to build production-grade AI agents using the [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework. This powerful PHP library provides everything you need to create intelligent agents with ReAct loops, tool orchestration, memory management, and advanced agentic patterns.

The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework includes:

- **🔄 Loop Strategies** — ReactLoop, PlanExecuteLoop, ReflectionLoop, and StreamingLoop for different task types
- **🛠️ Tool System** — Easy tool definition, registration, and execution with JSON schema validation
- **🧠 Memory Management** — Persistent state across agent iterations with file-based and in-memory storage
- **🏗️ Agent Patterns** — 20+ pre-built patterns including ReAct, Hierarchical, MAKER, and Adaptive agents
- **⚡ Production Ready** — Retry logic, error handling, logging, monitoring, and AMPHP-powered async execution

This series is designed for **intermediate PHP developers** who want to master agentic AI development. You'll start with the framework fundamentals (agent setup, tool use, loop strategies), then progress to advanced patterns: stateful agents, retrieval pipelines, reflection loops, multi-agent orchestration, and production deployment.

By the end, you will have built a complete agent platform using `claude-php/claude-php-agent`'s powerful primitives, including custom tool registries, memory systems, RAG pipelines, evaluation harnesses, and multi-agent coordination—all production-ready and ready to power real products.

## Quick Start

Want to see an agentic loop in action right now? Here is a simple example using the `claude-php/claude-php-agent` framework to perform a calculation with tool use:

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a simple calculator tool
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(fn (array $input) => eval("return {$input['expression']};"));

// Create an agent with the tool
$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful assistant that can perform calculations.');

// Run the agent
$result = $agent->run('What is 25 * 17 + 100?');

echo $result->getAnswer(); // 525
```

Head to [Chapter 00](/series/agentic-ai-php-developers/chapters/00-environment-setup-and-preparation) to set up your full environment.

## Who This Is For

This series is for:

- **Intermediate PHP developers** who can build web APIs and services
- **Laravel/Symfony developers** who want to add agentic AI to production apps
- **Product engineers** building AI features on top of existing services
- **Developers already using LLM APIs** who want to graduate to agent architectures

You should be comfortable with PHP 8.4+, REST APIs, Composer, and basic async/background job concepts.

## Prerequisites

**Software Requirements:**

- **PHP 8.4+**
- **Composer**
- **Redis** (for queues and caching)
- **SQLite/MySQL/PostgreSQL** (for memory and audit logs)
- **Anthropic API Key**
- **Docker** (optional, for deployment chapters)

**Time Commitment:**

- **Estimated total**: 35–50 hours
- **Per chapter**: 60–120 minutes
- **Capstone**: 4–6 hours

## What You'll Build

Throughout the series, you'll build:

1. A **production-ready agent runtime** using `claude-php/claude-php-agent`
2. A **tool registry** with schema validation and permissioning
3. A **conversation memory system** (short-term + long-term)
4. A **RAG pipeline** with embeddings + retrieval
5. A **planning + reflection loop** for multi-step tasks
6. A **multi-agent orchestration layer** with roles and handoffs
7. A **full agentic application** shipped with observability and evals

## Learning Objectives

By the end of this series, you will be able to:

- Master the `claude-php-agent` framework and its agent patterns
- Design agent architectures that go beyond single LLM calls
- Build reliable tool execution with validation, retries, and guardrails
- Implement memory systems and retrieval augmentation in PHP
- Orchestrate multi-agent workflows with specialized roles
- Evaluate agent quality, safety, and cost in production
- Ship and maintain agentic AI applications in real systems

## How This Series Works

Each chapter explores a core concept of the [`claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework and shows you how to apply it in production. You'll start with foundations (agent setup, tool use, loop strategies), build core primitives (tools, memory, plans), then assemble those into full agent workflows. Every part includes implementation guidance, architecture diagrams, and practical exercises.

---

## Series Outline (Chapters + Content)

### Part 0: Setup (Chapter 00)

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/00-environment-setup-and-preparation">00 — Environment Setup and Preparation</a></h4>
    <p style="margin-bottom: 0;">Install PHP, Composer, and <code>claude-php/claude-php-agent</code>. Configure API keys, verify dependencies, and run a minimal agent to confirm your environment.</p>
  </div>
</div>

### Part 1: Foundations (Chapters 01–04)

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/01-agentic-ai-fundamentals">01 — Agentic AI Fundamentals</a></h4>
    <p style="margin-bottom: 0;">Introduce agentic AI vs. plain LLM calls. Define agents, tools, memory, and control loops. Establish the mental model you'll use throughout the series.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/02-understanding-loop-strategies">02 — Understanding Loop Strategies</a></h4>
    <p style="margin-bottom: 0;">Learn ReactLoop, PlanExecuteLoop, ReflectionLoop, and StreamingLoop. Understand when to use each pattern and how to customize loop behavior.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/03-tool-system-deep-dive">03 — Tool System Deep Dive</a></h4>
    <p style="margin-bottom: 0;">Master schema validation, parameter definitions, error handling, and building production-grade tool implementations.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/04-agent-configuration-and-best-practices">04 — Agent Configuration and Best Practices</a></h4>
    <p style="margin-bottom: 0;">Configure agents with retry logic, logging, monitoring, and error handling. Set up production-ready patterns from the start.</p>
  </div>
</div>

### Part 2: Building Core Agent Primitives (Chapters 05–08)

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/05-tool-routing-and-execution-pipelines">05 — Tool Routing and Execution Pipelines</a></h4>
    <p style="margin-bottom: 0;">Create a tool router that dispatches safely, logs executions, and standardizes error responses. Introduce retries and idempotency for tools.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/06-stateful-conversations-and-short-term-memory">06 — Stateful Conversations and Short-Term Memory</a></h4>
    <p style="margin-bottom: 0;">Implement session storage, context windows, summarization, and transcript pruning. Keep agents coherent over long interactions.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/07-long-term-memory-with-datastores">07 — Long-Term Memory with Datastores</a></h4>
    <p style="margin-bottom: 0;">Design long-term memory tables, embeddings, and relevance scoring. Decide what to store and when to retrieve.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/08-retrieval-augmented-generation-for-agents">08 — Retrieval-Augmented Generation (RAG) for Agents</a></h4>
    <p style="margin-bottom: 0;">Add a retrieval layer for grounded responses. Cover chunking, indexing, and citation-style responses to reduce hallucinations.</p>
  </div>
</div>

### Part 3: Planning and Reasoning Systems (Chapters 09–12)

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/09-planning-from-tasks-to-steps">09 — Planning: From Tasks to Steps</a></h4>
    <p style="margin-bottom: 0;">Implement task decomposition using PlanExecuteLoop. Generate plans, track progress, and replan when tools fail or data changes.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/10-reflection-and-self-review-loops">10 — Reflection and Self-Review Loops</a></h4>
    <p style="margin-bottom: 0;">Use ReflectionLoop for self-improvement. Check answers, validate tool outputs, and reduce mistakes with critique stages.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/11-multi-stage-workflows-and-agent-graphs">11 — Multi-Stage Workflows and Agent Graphs</a></h4>
    <p style="margin-bottom: 0;">Build DAG-style workflows where agents execute steps in sequence or parallel. Add orchestration with state transitions.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h4 style="margin-top: 0;"><a href="/series/agentic-ai-php-developers/chapters/12-guardrails-policy-safety">12 — Guardrails, Policy, and Safety Layers</a></h4>
    <p style="margin-bottom: 0;">Add filtering, redaction, and policy enforcement. Build refusal logic and safe output validation for high-risk tasks.</p>
  </div>
</div>

### Part 4: Multi-Agent Systems (Chapters 13–15)

**13 — Hierarchical Agent Architectures**
Build master-worker patterns using `claude-php-agent`'s HierarchicalAgent. Define responsibilities and handoff rules.

**14 — Communication Protocols and Handoff Patterns**
Standardize inter-agent messaging, structured outputs, and contract-driven collaboration.

**15 — Adaptive Agent Selection**
Use AdaptiveAgentService for intelligent agent selection, validation, and auto-adaptation based on task analysis.

### Part 5: Production Engineering (Chapters 16–19)

**16 — Observability: Logs, Traces, and Metrics**
Instrument every agent step. Track tokens, tool calls, latency, and failure rates for real monitoring.

**17 — Evaluation Harnesses and QA**
Build offline evals, golden tests, and regression suites. Measure accuracy, cost, and safety on real task sets.

**18 — Performance and Cost Optimization**
Implement caching, batching, and model routing. Use smaller models for sub-tasks and optimize token spend.

**19 — Async & Concurrent Execution**
Leverage AMPHP for parallel tool execution, batch processing, and promise-based workflows with `claude-php-agent`.

### Part 6: Capstone (Chapter 20)

**20 — Capstone: Build an Agentic AI Platform**
Combine everything into a full system: tool registry, memory, RAG, planning, and multi-agent orchestration. Build a real product-ready agentic application with evals and monitoring.

---

## Next Steps

Ready to start building production-grade AI agents? Head to [Chapter 00: Environment Setup and Preparation](/series/agentic-ai-php-developers/chapters/00-environment-setup-and-preparation) to install `claude-php/claude-php-agent` and configure your development environment.

## Related Series

- **[Claude for PHP Developers](/series/claude-php-developers/)** — Claude API fundamentals and production integration
- **[AI/ML for PHP Developers](/series/ai-ml-php-developers/)** — Broader AI + ML concepts and integrations

<style>
:root {
  --primary-agentic: #0f766e;
  --primary-agentic-dark: #0b5f56;
  --agentic-teal: #14b8a6;
  --agentic-blue: #38bdf8;
  --neutral-gray: #64748b;
  --bg-light: #f8fafc;
}

div[style*="display: flex"][style*="align-items: flex-start"] {
  transition: all 0.3s ease;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--primary-agentic);
}

div[style*="display: flex"][style*="align-items: flex-start"]:hover {
  background: var(--bg-light);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(15, 118, 110, 0.15);
  border-left-color: var(--primary-agentic-dark);
}

div[style*="display: flex"] h4 a {
  color: var(--primary-agentic);
  transition: color 0.2s ease;
}

div[style*="display: flex"] h4 a:hover {
  color: var(--primary-agentic-dark);
}
</style>
