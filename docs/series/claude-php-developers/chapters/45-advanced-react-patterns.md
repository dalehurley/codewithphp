---
title: "45: Advanced ReAct Patterns"
description: "Build advanced react patterns with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 45
order: 45
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/44-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![45: Advanced ReAct Patterns](/images/claude-php/chapter-45-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 45</span>
</div>

# Chapter 45: Advanced ReAct Patterns

## Overview

This chapter is based on Tutorial 5 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 60 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 44** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Implement the Plan-Execute-Reflect-Adjust pattern
- Use extended thinking for complex reasoning
- Enable agent self-correction
- Decompose complex tasks into subtasks
- Implement reflection and adaptation
- Balance thinking depth with costs

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



You've built production-ready agents. Now let's make them smarter with **planning**, **reflection**, and **extended thinking**. These advanced patterns enable agents to solve complex problems through deliberate reasoning.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement the Plan-Execute-Reflect-Adjust pattern
- Use extended thinking for complex reasoning
- Enable agent self-correction
- Decompose complex tasks into subtasks
- Implement reflection and adaptation
- Balance thinking depth with costs

### 🧠 What is Advanced ReAct?

Standard ReAct: `Reason → Act → Observe → Repeat`

Advanced ReAct adds:

- **Planning**: Think ahead before acting
- **Reflection**: Analyze what worked and what didn't
- **Extended Thinking**: Deep reasoning for complex problems
- **Self-Correction**: Detect and fix mistakes

### 🏗️ The Advanced Pattern

```
┌────────────────────────────────────┐
│  1. PLAN                           │
│     "Break down the task"          │
│     "What steps are needed?"       │
│     "What could go wrong?"         │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  2. EXECUTE (Standard ReAct)       │
│     Reason → Act → Observe         │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  3. REFLECT                        │
│     "Did it work as expected?"     │
│     "What went well?"              │
│     "What needs improvement?"      │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  4. ADJUST                         │
│     "Change approach if needed"    │
│     "Try alternative strategy"     │
│     "Continue or complete"         │
└────────────┬───────────────────────┘
             │
      ┌──────┴──────┐
      │   Complete? │
      └──────┬──────┘
             │
       No────┴────Yes
       │           │
       └─→ PLAN    └─→ [Done]
```

### 💭 Extended Thinking

Extended thinking gives Claude more "thinking tokens" for complex reasoning.

#### Configuration

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 4096,
    'messages' => $messages,
    'tools' => $tools,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 10000  // Up to 32K for Opus
    ]
]);
```

#### When to Use

- **Complex Analysis**: Multi-step logical reasoning
- **Planning**: Breaking down complex tasks
- **Problem Solving**: Finding non-obvious solutions
- **Debugging**: Analyzing why something failed

#### Cost Considerations

Thinking tokens are **priced differently**:

- Sonnet 4.5: Input rate for both cache hit/miss
- Opus: Input rate
- Budget wisely (1K-32K tokens)

### 📋 Planning Pattern

#### System Prompt for Planning

```php
$planningSystem = "You are a meticulous planner. Before taking action:\n" .
                  "1. Break down the task into clear steps\n" .
                  "2. Identify what information is needed\n" .
                  "3. Anticipate potential issues\n" .
                  "4. Propose a strategy\n\n" .
                  "Only after planning, execute the plan step by step.";
```

#### Implementation

```php
// Phase 1: Planning
$messages = [
    ['role' => 'user', 'content' => "Task: {$task}\n\nFirst, create a plan."]
];

$planResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'system' => $planningSystem,
    'messages' => $messages,
    'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
]);

// Extract the plan
$plan = extractTextContent($planResponse);
echo "📋 Plan:\n{$plan}\n\n";

// Phase 2: Execute with tools
$messages[] = ['role' => 'assistant', 'content' => $planResponse->content];
$messages[] = ['role' => 'user', 'content' => 'Now execute the plan.'];

// Continue with standard ReAct loop...
```

### 🔍 Reflection Pattern

#### System Prompt for Reflection

```php
$reflectionSystem = "After completing actions, reflect on:\n" .
                    "1. What worked well\n" .
                    "2. What didn't work as expected\n" .
                    "3. What could be improved\n" .
                    "4. Whether the task is truly complete\n\n" .
                    "If issues found, propose corrections.";
```

#### Implementation

```php
// After execution
$messages[] = [
    'role' => 'user',
    'content' => 'Reflect on what you just did. Did it achieve the goal?'
];

$reflectionResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'system' => $reflectionSystem,
    'messages' => $messages,
    'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
]);
```

### 🔄 Self-Correction Pattern

#### Detecting Mistakes

```php
// Check if reflection reveals issues
$reflection = extractTextContent($reflectionResponse);

if (containsWords($reflection, ['issue', 'problem', 'incorrect', 'wrong'])) {
    echo "⚠️  Agent detected issues. Attempting correction...\n";

    $messages[] = ['role' => 'assistant', 'content' => $reflectionResponse->content];
    $messages[] = [
        'role' => 'user',
        'content' => 'Please correct the identified issues.'
    ];

    // Continue loop for correction...
}
```

### 🎯 Complete Advanced ReAct Implementation

```php
function advancedReActAgent($client, $task, $tools) {
    $system = "You are a thoughtful agent that plans before acting " .
              "and reflects after executing.";

    // Phase 1: Planning
    echo "Phase 1: Planning\n";
    $messages = [

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 46](/series/claude-php-developers/chapters/46-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 5 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/05-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="45"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 46](/series/claude-php-developers/chapters/46-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 5 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/05-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/05-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
