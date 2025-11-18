---
title: "47: Chain of Thought (CoT)"
description: "Build chain of thought (cot) with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 47
order: 47
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/46-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![47: Chain of Thought (CoT)](/images/claude-php/chapter-47-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 47</span>
</div>

# Chapter 47: Chain of Thought (CoT)

## Overview

This chapter is based on Tutorial 7 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 46** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Understand what Chain of Thought prompting is and when to use it
- Implement zero-shot CoT ("Let's think step by step")
- Use few-shot CoT with examples
- Compare CoT with ReAct patterns
- Apply CoT to mathematical reasoning, logic puzzles, and analysis
- Recognize when CoT is more appropriate than tool use

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



Chain of Thought (CoT) prompting is a powerful technique that enables Claude to solve complex problems by breaking them down into explicit reasoning steps. Unlike ReAct which uses tools, CoT relies purely on reasoning to arrive at answers.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Understand what Chain of Thought prompting is and when to use it
- Implement zero-shot CoT ("Let's think step by step")
- Use few-shot CoT with examples
- Compare CoT with ReAct patterns
- Apply CoT to mathematical reasoning, logic puzzles, and analysis
- Recognize when CoT is more appropriate than tool use

### 🏗️ What We're Building

We'll explore three types of Chain of Thought agents:

1. **Zero-Shot CoT** - Simple prompting for step-by-step reasoning
2. **Few-Shot CoT** - Providing examples to guide reasoning
3. **Complex CoT** - Multi-step logical reasoning without tools

### 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 6: Agentic Framework](../06-agentic-framework/)
- Understanding of ReAct pattern from earlier tutorials
- PHP 8.1+ installed
- Claude PHP SDK configured

### 🤔 What is Chain of Thought?

Chain of Thought is a prompting technique where the model is encouraged to show its reasoning process explicitly before arriving at a final answer.

#### Traditional Approach

```
User: "What is 15% of 80?"
Claude: "12"
```

#### Chain of Thought Approach

```
User: "What is 15% of 80? Let's think step by step."
Claude: "Let me break this down:
1. First, I need to convert 15% to a decimal: 15% = 0.15
2. Then multiply by 80: 0.15 × 80
3. Calculating: 0.15 × 80 = 12
Therefore, 15% of 80 is 12."
```

### 🔑 Key Concepts

#### 1. Zero-Shot CoT

Simply add "Let's think step by step" to your prompt:

```php
$prompt = "A farmer has 15 chickens. Each chicken lays 2 eggs per day. " .
          "How many eggs does the farmer collect in a week? " .
          "Let's think step by step.";
```

#### 2. Few-Shot CoT

Provide examples showing the reasoning process:

```php
$systemPrompt = "You solve problems step by step. Here are examples:

Q: If a book costs $12 and is on 25% discount, what's the sale price?
A: Let me work through this:
1. Calculate the discount: 25% of $12 = $12 × 0.25 = $3
2. Subtract from original: $12 - $3 = $9
The sale price is $9.

Q: A train travels 60 km/h for 2.5 hours. How far does it travel?
A: Let me solve this step by step:
1. Use the formula: Distance = Speed × Time
2. Plug in values: Distance = 60 km/h × 2.5 h
3. Calculate: Distance = 150 km
The train travels 150 km.";
```

#### 3. Benefits of CoT

**When to Use CoT:**
- ✅ Mathematical word problems
- ✅ Logical reasoning tasks
- ✅ Multi-step calculations
- ✅ Transparency and explainability required
- ✅ Educational contexts where showing work is important
- ✅ No external tools needed

**When to Use ReAct Instead:**
- ❌ Need to query external APIs or databases
- ❌ Require real-time information
- ❌ Task involves actual computations (use calculator tool)
- ❌ Need to manipulate files or systems

### 📊 CoT vs ReAct Comparison

| Aspect | Chain of Thought | ReAct |
|--------|-----------------|-------|
| **Primary Use** | Pure reasoning | Action + reasoning |
| **External Tools** | None | Required |
| **Transparency** | High (shows thinking) | Moderate |
| **Accuracy** | Good for reasoning | Exact for calculations |
| **Complexity** | Simple implementation | More complex setup |
| **Best For** | Logic, analysis | API calls, computations |

### 💡 Zero-Shot CoT Implementation

The simplest form - just add the magic phrase:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'messages' => [
        [
            'role' => 'user',
            'content' => $problem . "\n\nLet's think step by step."
        ]
    ]
]);
```

#### Magic Phrases

Different phrasings work:
- "Let's think step by step."
- "Let's work this out step by step."
- "Let's approach this systematically."
- "Let's break this down."

### 🎓 Few-Shot CoT Implementation

Provide examples in the system prompt:

```php
$systemPrompt = "You are a logical reasoning expert. " .
                "Always show your reasoning step by step. " .
                "Here are examples of how to approach problems:\n\n" .
                $examples;

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'system' => $systemPrompt,
    'messages' => [
        ['role' => 'user', 'content' => $problem]
    ]
]);
```

### 🧩 Example: Math Word Problem

```php
$problem = "Sarah has 3 boxes. Each box contains 4 bags. " .
           "Each bag has 5 marbles. How many marbles does Sarah have in total?";

// Zero-shot CoT
$prompt = $problem . "\n\nLet's solve this step by step.";
```

Expected reasoning:
```
1. Calculate marbles per box: 4 bags × 5 marbles = 20 marbles
2. Calculate total marbles: 3 boxes × 20 marbles = 60 marbles
Therefore, Sarah has 60 marbles in total.
```

### 🎯 Example: Logic Puzzle

```php
$puzzle = "If all roses are flowers, and some flowers fade quickly, " .
          "can we conclude that some roses fade quickly?";

$prompt = $puzzle . "\n\nLet's think through this logically.";
```

Expected reasoning:
```
1. Premise 1: All roses are flowers (roses ⊂ flowers)
2. Premise 2: Some flowers fade quickly
3. Question: Do some roses fade quickly?

Analysis:
- We know roses are a subset of flowers
- We know some flowers fade quickly
- But we don't know if the flowers that fade quickly include roses
- The "some flowers" could be other types of flowers

Conclusion: No, we cannot conclude that some roses fade quickly.
This is a logic error - just because roses are flowers and some flowers
fade quickly doesn't mean those specific flowers are roses.

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 48](/series/claude-php-developers/chapters/48-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 7 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/07-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="47"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 48](/series/claude-php-developers/chapters/48-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 7 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/07-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/07-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
