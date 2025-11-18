---
title: "54: Autonomous Agents"
description: "Build autonomous agents with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 54
order: 54
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/53-rag-pattern"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![54: Autonomous Agents](/images/claude-php/chapter-54-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 54</span>
</div>

# Chapter 54: Autonomous Agents

## Overview

This chapter is based on Tutorial 14 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 90 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 53** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Build goal-directed autonomous agents
- Implement persistent state management
- Handle multi-session agent execution
- Create safety and termination conditions
- Design self-monitoring systems

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



Autonomous agents are self-directed systems that pursue goals independently, maintaining state across sessions and adapting to changing conditions.

### 🎯 Learning Objectives

- Build goal-directed autonomous agents
- Implement persistent state management
- Handle multi-session agent execution
- Create safety and termination conditions
- Design self-monitoring systems

### 🏗️ What We're Building

An autonomous agent with:
1. **Goal Tracking** - Define and pursue objectives
2. **State Persistence** - Save progress between runs
3. **Self-Monitoring** - Track progress toward goals
4. **Adaptation** - Adjust strategy based on results

### 📋 Prerequisites

- Completed [Tutorial 13: RAG Pattern](../13-rag-pattern/)
- Understanding of all previous patterns

### 🤔 What Makes Agents Autonomous?

Autonomous agents:
- Set their own sub-goals
- Run independently over time
- Persist state between sessions
- Adapt strategies dynamically
- Monitor their own progress

### 🔑 Key Concepts

#### Goal Management

```php
$goals = [
    'primary' => "Research and write article",
    'sub_goals' => [
        'research' => ['status' => 'in_progress', 'progress' => 0.6],
        'outline' => ['status' => 'pending', 'progress' => 0],
        'write' => ['status' => 'pending', 'progress' => 0]
    ]
];
```

#### State Persistence

```php
function saveState($state, $file = 'agent_state.json') {
    file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT));
}

function loadState($file = 'agent_state.json') {
    return json_decode(file_get_contents($file), true);
}
```

#### Progress Monitoring

```php
function assessProgress($state) {
    $total = count($state['sub_goals']);
    $completed = array_filter($state['sub_goals'], 
        fn($g) => $g['status'] === 'completed'
    );
    return count($completed) / $total;
}
```

#### Safety Limits

```php
$safety = [
    'max_iterations' => 100,
    'max_cost' => 5.00, // dollars
    'max_duration' => 3600, // seconds
    'termination_conditions' => ['goal_achieved', 'budget_exceeded']
];
```

### 💡 Autonomous Agent Loop

```php
while (!goalAchieved() && !terminationCondition()) {
    $state = loadState();
    
    // Assess current situation
    $assessment = assess($state);
    
    // Decide next action
    $action = decide($assessment);
    
    // Execute action
    $result = execute($action);
    
    // Update state
    $state = updateState($state, $result);
    
    // Persist for next session
    saveState($state);
    
    // Check if we should pause
    if (shouldPause($state)) {
        echo "Pausing... Resume later.\n";
        break;
    }
}
```

### 🎯 Example: Multi-Session Agent

Session 1:
```
Goal: Research topic (30% complete)
State saved: research_data.json
```

Session 2:
```
Load state: research_data.json
Continue: Research topic (60% complete)
New goal: Outline article
State saved: research_data.json
```

Session 3:
```
Load state: research_data.json
Complete: Outline article
Begin: Write draft
```

### ⚙️ Advanced Features

#### Checkpoint System

```php
$checkpoints = [
    'research_complete' => $researchData,
    'outline_done' => $outline,
    'draft_v1' => $draft
];
```

#### Adaptive Strategies

```php
if ($progress < 0.2 && $iterations > 20) {
    // Strategy not working, try different approach
    $strategy = 'alternative_method';
}
```

#### Resource Budgeting

```php
$budget = [
    'tokens_used' => 15000,
    'tokens_limit' => 100000,
    'cost_used' => 0.45,
    'cost_limit' => 5.00
];
```

### ⚠️ Safety Considerations

**Critical safeguards:**

1. **Maximum iterations** - Prevent infinite loops
2. **Cost limits** - Avoid runaway expenses
3. **Time limits** - Bound execution time
4. **Human oversight** - Approval for critical actions
5. **Rollback capability** - Undo if needed

```php
function checkSafety($state) {
    if ($state['iterations'] > MAX_ITERATIONS) {
        throw new Exception("Max iterations exceeded");
    }
    if ($state['cost'] > MAX_COST) {
        throw new Exception("Budget exceeded");
    }
    return true;
}
```

### ✅ Checkpoint

- [ ] Understand autonomous agent architecture
- [ ] State persistence techniques
- [ ] Progress monitoring
- [ ] Safety and termination conditions
- [ ] Multi-session execution

### 💻 Try It Yourself


## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 55](/series/claude-php-developers/chapters/55-multi-tool-agent)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 14 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/14-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="54"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 55](/series/claude-php-developers/chapters/55-multi-tool-agent) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 14 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/14-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/14-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
