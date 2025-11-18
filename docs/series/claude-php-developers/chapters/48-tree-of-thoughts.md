---
title: "48: Tree of Thoughts (ToT)"
description: "Build tree of thoughts (tot) with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 48
order: 48
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/47-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![48: Tree of Thoughts (ToT)](/images/claude-php/chapter-48-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 48</span>
</div>

# Chapter 48: Tree of Thoughts (ToT)

## Overview

This chapter is based on Tutorial 8 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 60 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 47** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Understand the Tree of Thoughts pattern and its advantages
- Implement multi-path exploration strategies
- Evaluate and score different reasoning paths
- Implement backtracking when paths lead to dead ends
- Choose between breadth-first and depth-first exploration
- Apply ToT to complex problems like puzzles and optimization

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



Tree of Thoughts (ToT) is an advanced reasoning pattern that explores multiple reasoning paths simultaneously, evaluates each path, and can backtrack when needed. Think of it as "exploring a maze" rather than following a single path.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Understand the Tree of Thoughts pattern and its advantages
- Implement multi-path exploration strategies
- Evaluate and score different reasoning paths
- Implement backtracking when paths lead to dead ends
- Choose between breadth-first and depth-first exploration
- Apply ToT to complex problems like puzzles and optimization

### 🏗️ What We're Building

We'll implement Tree of Thoughts agents that:

1. **Generate multiple solution paths** - Explore different approaches
2. **Evaluate each path** - Score and rank possibilities
3. **Select best paths** - Choose most promising directions
4. **Backtrack when needed** - Abandon dead ends
5. **Combine insights** - Synthesize the best solution

### 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 7: Chain of Thought](../07-chain-of-thought/)
- Understanding of recursive algorithms helpful
- PHP 8.1+ installed
- Claude PHP SDK configured

### 🌳 What is Tree of Thoughts?

Unlike Chain of Thought which follows a linear path, Tree of Thoughts explores multiple reasoning paths in parallel, creating a tree structure.

#### Chain of Thought (Linear)

```
Problem → Step 1 → Step 2 → Step 3 → Answer
```

#### Tree of Thoughts (Branching)

```
Problem
  ├─ Approach A → Step A1 → Step A2 → Solution A
  ├─ Approach B → Step B1 ✗ (Dead end)
  └─ Approach C → Step C1 → Step C2 → Solution C ✓ (Best)
```

### 🔑 Key Concepts

#### 1. Thought Generation

Generate multiple possible next steps:

```php
"Generate 3 different approaches to solve this problem.
For each approach, explain the strategy and first step."
```

#### 2. Thought Evaluation

Score each possibility:

```php
"Rate each approach from 1-10 based on:
- Likelihood of success
- Simplicity
- Efficiency"
```

#### 3. Thought Selection

Choose the best path(s) to explore:

```php
// Select top N paths
$selectedPaths = array_slice($rankedPaths, 0, 2);
```

#### 4. Backtracking

Abandon unsuccessful paths:

```php
if ($pathScore < $threshold) {
    echo "Path unsuccessful, backtracking...\n";
    continue; // Try different branch
}
```

### 📊 ToT Algorithm

The basic Tree of Thoughts loop:

```
1. Generate N possible next thoughts
2. Evaluate each thought (score 1-10)
3. Select top K thoughts
4. For each selected thought:
   a. If goal reached: return solution
   b. If dead end: backtrack
   c. Otherwise: goto step 1 (recurse)
```

### 🎮 Classic Example: Game of 24

Use 4 numbers and basic operations (+, -, ×, ÷) to make 24.

**Problem**: Use 4, 6, 7, 8 to make 24

**ToT Exploration**:

```
Initial: [4, 6, 7, 8]

Branch 1: Try (8 - 6) × 7 + 4
  Step 1: 8 - 6 = 2, remaining [2, 7, 4]
  Step 2: 2 × 7 = 14, remaining [14, 4]
  Step 3: 14 + 4 = 18 ✗ (Not 24, backtrack)

Branch 2: Try (7 + 6) × 8 - 4
  Step 1: 7 + 6 = 13, remaining [13, 8, 4]
  Step 2: 13 × 8 = 104, remaining [104, 4]
  Step 3: 104 - 4 = 100 ✗ (Not 24, backtrack)

Branch 3: Try (8 / 4) × (7 - 6)
  Step 1: 8 / 4 = 2, remaining [2, 7, 6]
  Step 2: 7 - 6 = 1, remaining [2, 1]
  Step 3: 2 × 1 = 2 ✗ (Not 24, backtrack)

Branch 4: Try (6 - 4) × (8 + 7)
  Step 1: 6 - 4 = 2, remaining [2, 8, 7]
  Step 2: 8 + 7 = 15, remaining [2, 15]
  Step 3: 2 × 15 = 30 ✗ (Close! Try variations)

Branch 5: Try 6 × (8 - 4) + 7
  Step 1: 8 - 4 = 4, remaining [6, 4, 7]
  Step 2: 6 × 4 = 24, remaining [24, 7]
  Wait! We have 24, ignore 7? ✗

Branch 6: Try (6 + 7 - 8) × 4
  Step 1: 6 + 7 = 13, remaining [13, 8, 4]
  Step 2: 13 - 8 = 5, remaining [5, 4]
  Step 3: 5 × 4 = 20 ✗

Branch 7: Try 6 × (7 - 4) + 8
  Step 1: 7 - 4 = 3, remaining [6, 3, 8]
  Step 2: 6 × 3 = 18, remaining [18, 8]
  Step 3: 18 + 8 = 26 ✗ (Very close)

Branch 8: Try 8 × (7 - 4) + 6
  Step 1: 7 - 4 = 3, remaining [8, 3, 6]
  Step 2: 8 × 3 = 24, remaining [24, 6]
  Need to use all numbers...

Branch 9: Try (8 - 4) × (7 - 6)
  This gives 4, not helpful

Branch 10: Try 6 ÷ (8 - 7) × 4
  Step 1: 8 - 7 = 1, remaining [6, 1, 4]
  Step 2: 6 ÷ 1 = 6, remaining [6, 4]
  Step 3: 6 × 4 = 24 ✓ Success!

Solution: 6 ÷ (8 - 7) × 4 = 24
```

### 🔀 Search Strategies

#### Breadth-First Search (BFS)

Explore all paths at each level before going deeper:

```php
$queue = [$initialState];

while (!empty($queue)) {
    $state = array_shift($queue); // FIFO

    $nextStates = generateThoughts($state);

    foreach ($nextStates as $next) {
        if (isGoal($next)) return $next;
        $queue[] = $next;
    }
}
```

**Pros**: Finds shortest solution
**Cons**: High memory usage

#### Depth-First Search (DFS)

Explore one path fully before trying others:

```php

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 49](/series/claude-php-developers/chapters/49-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 8 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/08-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="48"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 49](/series/claude-php-developers/chapters/49-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 8 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/08-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/08-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
