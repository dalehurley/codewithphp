---
title: "51: Hierarchical Agents"
description: "Build hierarchical agents with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 51
order: 51
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/50-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![51: Hierarchical Agents](/images/claude-php/chapter-51-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 51</span>
</div>

# Chapter 51: Hierarchical Agents

## Overview

This chapter is based on Tutorial 11 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 60 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 50** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Build master-worker agent architectures
- Implement task delegation strategies
- Create specialized sub-agents for different domains
- Aggregate and synthesize results from multiple agents
- Handle inter-agent communication
- Optimize parallel vs sequential execution
- Design agent hierarchies for real-world problems

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



Hierarchical agent systems organize multiple specialized agents under a coordinator, enabling efficient task delegation, parallel execution, and domain-specific expertise. This pattern is essential for complex, multi-domain tasks.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Build master-worker agent architectures
- Implement task delegation strategies
- Create specialized sub-agents for different domains
- Aggregate and synthesize results from multiple agents
- Handle inter-agent communication
- Optimize parallel vs sequential execution
- Design agent hierarchies for real-world problems

### 🏗️ What We're Building

A hierarchical system with:

1. **Master Agent (Coordinator)** - Analyzes tasks and delegates
2. **Specialized Workers** - Domain experts (math, research, writing, coding)
3. **Task Router** - Intelligent agent selection
4. **Result Synthesizer** - Combines worker outputs
5. **Error Handler** - Manages worker failures

### 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 10: Reflection](../10-reflection/)
- Understanding of agent patterns from previous tutorials
- PHP 8.1+ installed
- Claude PHP SDK configured

### 🤔 What is Hierarchical Architecture?

Hierarchical systems organize agents in layers:

```
                 ┌─────────────────┐
                 │  Master Agent   │
                 │  (Coordinator)  │
                 └────────┬────────┘
                          │
         ┌────────────────┼────────────────┐
         │                │                │
    ┌────▼────┐      ┌────▼────┐     ┌────▼────┐
    │  Math   │      │Research │     │ Writing │
    │  Agent  │      │ Agent   │     │  Agent  │
    └─────────┘      └─────────┘     └─────────┘
```

#### Why Hierarchical?

**Advantages:**
- ✅ **Specialization** - Each agent excels in its domain
- ✅ **Scalability** - Add agents without redesigning system
- ✅ **Parallel Execution** - Multiple agents work simultaneously
- ✅ **Clear Responsibility** - Each agent has defined role
- ✅ **Maintainability** - Isolated components

**Disadvantages:**
- ❌ **Complexity** - More components to manage
- ❌ **Coordination Overhead** - Master must orchestrate
- ❌ **Potential Bottleneck** - Master can be limiting factor

### 🔑 Key Concepts

#### 1. Task Decomposition

Master agent breaks complex tasks into subtasks:

```php
$masterPrompt = "Complex task: {$task}\n\n" .
                "Available specialized agents:\n" .
                "- math_agent: Calculations, statistics, formulas\n" .
                "- research_agent: Information lookup, fact-checking\n" .
                "- writing_agent: Composition, editing, formatting\n" .
                "- code_agent: Programming, algorithms, debugging\n\n" .
                "Decompose into subtasks. For each subtask specify:\n" .
                "1. Which agent should handle it\n" .
                "2. What the subtask is\n" .
                "3. Any dependencies on other subtasks\n" .
                "4. Expected output";
```

#### 2. Agent Specialization

Each worker has specific expertise:

```php
class MathAgent {
    private $system = "You are a mathematics expert. " .
                     "Solve calculations precisely. " .
                     "Provide step-by-step solutions.";
    private $tools = [$calculatorTool, $statisticsTool];
}

class ResearchAgent {
    private $system = "You are a research specialist. " .
                     "Find accurate information. " .
                     "Cite sources.";
    private $tools = [$searchTool, $webFetchTool];
}

class WritingAgent {
    private $system = "You are a professional writer. " .
                     "Create clear, engaging content. " .
                     "Use proper structure.";
    private $tools = []; // Pure language work
}
```

#### 3. Delegation Strategy

Route tasks to appropriate agents:

```php
function selectAgent($subtask, $agents) {
    // Analyze subtask requirements
    $keywords = extractKeywords($subtask);
    
    // Match to agent specialties
    foreach ($agents as $agent) {
        $matchScore = calculateMatch($keywords, $agent->specialty);
        if ($matchScore > 0.7) {
            return $agent;
        }
    }
    
    return $defaultAgent;
}
```

#### 4. Result Aggregation

Combine outputs from multiple agents:

```php
function synthesizeResults($task, $results) {
    $synthesisPrompt = "Original task: {$task}\n\n";
    
    foreach ($results as $agent => $output) {
        $synthesisPrompt .= "{$agent} result:\n{$output}\n\n";
    }
    
    $synthesisPrompt .= "Synthesize these results into a coherent final answer.";
    
    return synthesize($synthesisPrompt);
}
```

### 💡 Implementation Patterns

#### Basic Hierarchical System

```php
class HierarchicalSystem {
    private $master;
    private $workers = [];
    
    public function __construct($client) {
        $this->master = new MasterAgent($client);
        $this->workers['math'] = new MathAgent($client);
        $this->workers['research'] = new ResearchAgent($client);
        $this->workers['writing'] = new WritingAgent($client);
    }
    
    public function execute($task) {
        // 1. Decompose
        $subtasks = $this->master->decompose($task, $this->workers);
        
        // 2. Delegate and execute
        $results = [];
        foreach ($subtasks as $subtask) {
            $agent = $this->workers[$subtask->agent];
            $results[$subtask->agent] = $agent->execute($subtask->task);
        }
        
        // 3. Synthesize
        return $this->master->synthesize($task, $results);
    }
}
```

#### Worker Agent Implementation

```php
class WorkerAgent {
    private $client;
    private $system;
    private $tools;
    
    public function __construct($client, $system, $tools = []) {
        $this->client = $client;
        $this->system = $system;
        $this->tools = $tools;
    }
    

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 52](/series/claude-php-developers/chapters/52-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 11 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/11-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="51"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 52](/series/claude-php-developers/chapters/52-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 11 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/11-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/11-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
