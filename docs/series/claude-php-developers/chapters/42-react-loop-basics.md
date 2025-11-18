---
title: "42: ReAct Loop Basics"
description: "Build react loop basics with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 42
order: 42
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/41-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![42: ReAct Loop Basics](/images/claude-php/chapter-42-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 42</span>
</div>

# Chapter 42: ReAct Loop Basics

## Overview

This chapter is based on Tutorial 2 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 41** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Implement the ReAct (Reason-Act-Observe) loop
- Handle multiple tool calls in sequence
- Maintain conversation state across iterations
- Implement proper stop conditions
- Debug agent reasoning steps
- Prevent infinite loops with iteration limits

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



In the previous tutorial, we built an agent that could make one tool call. But what about tasks that require multiple steps? That's where the **ReAct pattern** comes in. In this tutorial, we'll implement a proper ReAct loop that enables iterative reasoning and multi-step problem solving.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement the ReAct (Reason-Act-Observe) loop
- Handle multiple tool calls in sequence
- Maintain conversation state across iterations
- Implement proper stop conditions
- Debug agent reasoning steps
- Prevent infinite loops with iteration limits

### 🔄 What is ReAct?

**ReAct** stands for **Reason** → **Act** → **Observe**, and it's the fundamental pattern for autonomous agents.

#### The Loop

```
Start
  ↓
┌─────────────────────────────────┐
│  REASON                         │
│  "What do I need to do next?"   │
│  "What info is missing?"        │
└───────────┬─────────────────────┘
            ↓
┌─────────────────────────────────┐
│  ACT                            │
│  "Call tool X with params Y"    │
│  Or "I have enough to answer"   │
└───────────┬─────────────────────┘
            ↓
┌─────────────────────────────────┐
│  OBSERVE                        │
│  "Tool returned Z"              │
│  "Do I have what I need?"       │
└───────────┬─────────────────────┘
            ↓
        ┌───────┐
        │ Done? │
        └───┬───┘
            │
      No ───┴─── Yes
      │           │
      │           ↓
      │        [Return
      │         Answer]
      │
      └──> (Back to REASON)
```

#### Why It Matters

Without ReAct, agents can only:

- Answer questions with their training data
- Make ONE tool call per task

With ReAct, agents can:

- Gather information step-by-step
- Chain multiple tools together
- Adapt based on tool results
- Solve complex multi-step problems

### 🏗️ What We're Building

We'll build a ReAct agent that can:

1. Accept complex tasks requiring multiple steps
2. Reason about what to do next
3. Execute tools iteratively
4. Observe results and adapt
5. Continue until the task is complete
6. Respect iteration limits

#### Example Task

**Question**: "What is (50 × 30) + (100 - 25)?"

**Traditional Agent** (from Tutorial 1):

- Can only make ONE tool call
- Would fail or give incomplete answer

**ReAct Agent** (what we're building):

- Iteration 1: Calculate 50 × 30 = 1,500
- Iteration 2: Calculate 100 - 25 = 75
- Iteration 3: Calculate 1,500 + 75 = 1,575
- Final Answer: "1,575"

### 🔑 Core Components

#### 1. The Main Loop

```php
$messages = [/* initial message */];
$maxIterations = 10;  // Safety limit
$iteration = 0;

while ($iteration < $maxIterations) {
    $iteration++;

    // Call Claude
    $response = $client->messages()->create([
        'messages' => $messages,
        'tools' => $tools
    ]);

    // Add response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];

    // Check if done
    if ($response->stop_reason === 'end_turn') {
        // Task complete!
        break;
    }

    // Execute tools and continue
    if ($response->stop_reason === 'tool_use') {
        // Extract and execute tools
        // Add results to messages
        // Loop continues...
    }
}
```

#### 2. Stop Conditions

Your loop needs to exit when:

1. **Task Complete**: `stop_reason === 'end_turn'`
2. **Max Iterations**: `$iteration >= $maxIterations`
3. **Error**: Tool execution fails critically
4. **No Tools**: `stop_reason === 'tool_use'` but no tool uses found

#### 3. State Management

The conversation history is your state:

```php
$messages = [
    ['role' => 'user', 'content' => 'Task...'],           // Turn 1
    ['role' => 'assistant', 'content' => [/* tool use */]], // Turn 2
    ['role' => 'user', 'content' => [/* tool result */]],   // Turn 3
    ['role' => 'assistant', 'content' => [/* tool use */]], // Turn 4
    // ... continues until done
];
```

Each iteration adds to this history, giving Claude context about what's already been done.

### 📋 Implementation Steps

#### Step 1: Initialize the Loop

```php
$messages = [
    ['role' => 'user', 'content' => $userTask]
];

$maxIterations = 10;
$iteration = 0;
$finalResponse = null;
```

#### Step 2: Loop Until Done

```php
while ($iteration < $maxIterations) {
    $iteration++;

    echo "Iteration {$iteration}\n";

    // Call Claude with current conversation history
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => $tools
    ]);

    // Add Claude's response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];

    // Check stop condition
    if ($response->stop_reason === 'end_turn') {
        $finalResponse = $response;
        break;

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 43](/series/claude-php-developers/chapters/43-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 2 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/02-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="42"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 43](/series/claude-php-developers/chapters/43-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 2 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/02-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/02-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
