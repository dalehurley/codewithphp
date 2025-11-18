---
title: "49: Plan-and-Execute"
description: "Build plan-and-execute with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 49
order: 49
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/48-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![49: Plan-and-Execute](/images/claude-php/chapter-49-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 49</span>
</div>

# Chapter 49: Plan-and-Execute

## Overview

This chapter is based on Tutorial 9 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 48** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Understand the Plan-and-Execute pattern
- Separate planning from execution
- Create detailed action plans before executing
- Monitor execution and handle failures
- Revise plans based on execution results
- Compare Plan-and-Execute with ReAct

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



The Plan-and-Execute pattern separates planning from execution into two distinct phases. Unlike ReAct which interleaves thinking and action, Plan-and-Execute creates a complete plan upfront, then executes it systematically.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Understand the Plan-and-Execute pattern
- Separate planning from execution
- Create detailed action plans before executing
- Monitor execution and handle failures
- Revise plans based on execution results
- Compare Plan-and-Execute with ReAct

### 🏗️ What We're Building

We'll implement agents that:

1. **Plan Phase** - Analyze task and create detailed action plan
2. **Execute Phase** - Systematically execute each planned step
3. **Monitor Phase** - Track progress and detect issues
4. **Revise Phase** - Update plan if needed

### 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 8: Tree of Thoughts](../08-tree-of-thoughts/)
- Understanding of ReAct pattern
- PHP 8.1+ installed
- Claude PHP SDK configured

### 🤔 What is Plan-and-Execute?

Plan-and-Execute divides work into two phases:

#### React Pattern (Interleaved)

```
Think → Act → Observe → Think → Act → Observe → ...
```

#### Plan-and-Execute Pattern (Sequential)

```
PLAN: Analyze → Break down → Sequence steps
↓
EXECUTE: Step 1 → Step 2 → Step 3 → ...
```

### 🔑 Key Concepts

#### 1. Planning Phase

Create comprehensive plan before any action:

```php
$planPrompt = "Task: {$task}\n\n" .
              "Create a detailed step-by-step plan. For each step:\n" .
              "1. Describe the action\n" .
              "2. What tool to use\n" .
              "3. Expected outcome\n" .
              "4. Dependencies on previous steps";
```

#### 2. Execution Phase

Follow the plan systematically:

```php
foreach ($plan->steps as $step) {
    echo "Executing: {$step->action}\n";
    $result = executeTool($step->tool, $step->input);
    $step->result = $result;
    
    if ($result->isError) {
        // Handle failure
    }
}
```

#### 3. Monitoring

Track execution progress:

```php
$monitor = [
    'completed' => [],
    'current' => $currentStep,
    'remaining' => $remainingSteps,
    'failures' => []
];
```

#### 4. Plan Revision

Update plan if execution reveals issues:

```php
if ($executionFailed) {
    $revisedPlan = revisePlan($originalPlan, $executionResults);
}
```

### 📊 Plan-and-Execute vs ReAct

| Aspect | Plan-and-Execute | ReAct |
|--------|-----------------|-------|
| **Planning** | Upfront, complete | Interleaved with action |
| **Flexibility** | Less (follows plan) | High (adapts constantly) |
| **Efficiency** | Better (no wasted actions) | Can be exploratory |
| **Complexity** | Simpler execution | Complex loop |
| **Best For** | Well-defined tasks | Exploratory tasks |
| **Resource Use** | Predictable | Variable |

### 💡 Planning Implementation

#### Step 1: Task Analysis

```php
$analysisPrompt = "Task: {$task}\n\n" .
                  "Analyze this task:\n" .
                  "1. What is the end goal?\n" .
                  "2. What information do we need?\n" .
                  "3. What tools are available?\n" .
                  "4. What are the constraints?";
```

#### Step 2: Plan Generation

```php
$planningPrompt = "Task: {$task}\n\n" .
                  "Available tools: {$toolsList}\n\n" .
                  "Create a detailed plan with these sections:\n\n" .
                  "STEPS:\n" .
                  "1. [Action] - Tool: [tool_name] - Expected: [outcome]\n" .
                  "2. ...\n\n" .
                  "DEPENDENCIES:\n" .
                  "- Step 2 depends on Step 1 result\n\n" .
                  "RISKS:\n" .
                  "- Potential issues and mitigation";
```

#### Step 3: Plan Validation

```php
function validatePlan($plan) {
    // Check all dependencies are satisfied
    // Verify tools exist
    // Ensure steps are ordered correctly
    // Check for circular dependencies
}
```

### 🚀 Execution Implementation

#### Sequential Execution

```php
function executePlan($client, $plan, $tools) {
    $results = [];
    $context = [];
    
    foreach ($plan->steps as $i => $step) {
        echo "Step " . ($i + 1) . ": {$step->description}\n";
        
        // Execute with context from previous steps
        $result = executeStep($step, $context, $tools);
        
        if ($result->success) {
            $results[] = $result;
            $context[$step->id] = $result->data;
        } else {
            // Handle failure
            return handleFailure($plan, $i, $result);
        }
    }
    
    return $results;
}
```

#### Error Handling

```php
function handleFailure($plan, $failedStepIndex, $error) {
    // Options:
    // 1. Retry the step
    // 2. Skip and continue
    // 3. Revise plan
    // 4. Abort mission
    
    if ($error->isRecoverable) {
        return retryStep($plan->steps[$failedStepIndex]);
    } else {
        return revisePlan($plan, $failedStepIndex, $error);
    }
}
```

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 50](/series/claude-php-developers/chapters/50-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 9 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/09-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="49"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 50](/series/claude-php-developers/chapters/50-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 9 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/09-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/09-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
