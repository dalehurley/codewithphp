---
title: "Chapter 01: Agentic AI Fundamentals - Code Examples"
---

# Chapter 01: Agentic AI Fundamentals - Code Examples

This directory contains small runnable examples that contrast a plain LLM-style call with a minimal agentic control loop.

## Files

- `plain-llm.php` — Simulates a single-shot LLM response with no tools or memory.
- `agentic-loop.php` — Demonstrates a tool call, memory update, and loop-based control flow with a trace.

## Running the Examples

```bash
php plain-llm.php
php agentic-loop.php
```

Expected output (example):

```
Task: Explain agentic AI and include today's date.
Agentic AI uses tools, memory, and loops to solve tasks. (This plain call can't look up today's date.)
```

```
Task: Explain agentic AI and include today's date.
Agentic AI wraps LLMs with tools, memory, and control loops. Today is 2024-01-15.

Trace:
- Step 1: plan next action.
- Action: call tool get_date
- Observation: stored date 2024-01-15
- Step 2: plan next action.
- Action: compose final response
```

> Note: These are deterministic examples to make the agentic loop obvious. In Chapter 02, you’ll connect this loop to `claude-php-agent` for real LLM-driven planning.
