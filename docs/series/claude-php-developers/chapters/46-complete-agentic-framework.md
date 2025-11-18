---
title: "46: Complete Agentic Framework"
description: "Build complete agentic framework with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 46
order: 46
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/45-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![46: Complete Agentic Framework](/images/claude-php/chapter-46-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 46</span>
</div>

# Chapter 46: Complete Agentic Framework

## Overview

This chapter is based on Tutorial 6 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 90 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 45** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Design a complete agent architecture
- Implement task decomposition strategies
- Orchestrate multiple sub-agents
- Manage complex state across workflows
- Handle parallel tool execution
- Build reusable agent components
- Create production-grade agentic systems

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



Welcome to the final tutorial! We'll bring together everything you've learned to build a complete agentic framework with task decomposition, parallel execution, state management, and orchestration.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Design a complete agent architecture
- Implement task decomposition strategies
- Orchestrate multiple sub-agents
- Manage complex state across workflows
- Handle parallel tool execution
- Build reusable agent components
- Create production-grade agentic systems

### 🏗️ Framework Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     ORCHESTRATOR                            │
│  • Receives high-level goals                                │
│  • Decomposes into subtasks                                 │
│  • Coordinates execution                                    │
│  • Aggregates results                                       │
└────────────────┬────────────────────────────────────────────┘
                 │
      ┌──────────┴──────────┐
      │                     │
      ▼                     ▼
┌─────────────┐       ┌─────────────┐
│  SUB-AGENT  │       │  SUB-AGENT  │
│     #1      │       │     #2      │
│             │       │             │
│  • Focused  │       │  • Focused  │
│    task     │       │    task     │
│  • Tools    │       │  • Tools    │
│  • Memory   │       │  • Memory   │
└──────┬──────┘       └──────┬──────┘
       │                     │
       └──────────┬──────────┘
                  │
                  ▼
          ┌───────────────┐
          │ STATE MANAGER │
          │  • History    │
          │  • Memory     │
          │  • Context    │
          └───────────────┘
```

### 🧩 Core Components

#### 1. Task Decomposer

Breaks complex tasks into manageable subtasks:

```php
class TaskDecomposer {
    public function decompose($task) {
        // Use Claude to break down task
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'messages' => [[
                'role' => 'user',
                'content' => "Break down this task into 3-5 subtasks: {$task}"
            ]],
            'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
        ]);

        return $this->parseSubtasks($response);
    }
}
```

#### 2. Agent Pool

Manages multiple specialized agents:

```php
class AgentPool {
    private $agents = [];

    public function registerAgent($name, $agent) {
        $this->agents[$name] = $agent;
    }

    public function getAgent($name) {
        return $this->agents[$name] ?? null;
    }

    public function executeWithAgent($agentName, $task) {
        $agent = $this->getAgent($agentName);
        if (!$agent) {
            throw new Exception("Agent not found: {$agentName}");
        }

        return $agent->execute($task);
    }
}
```

#### 3. State Manager

Maintains context across the workflow:

```php
class StateManager {
    private $state = [];
    private $history = [];

    public function setState($key, $value) {
        $this->state[$key] = $value;
        $this->history[] = [
            'action' => 'set',
            'key' => $key,
            'timestamp' => time()
        ];
    }

    public function getState($key) {
        return $this->state[$key] ?? null;
    }

    public function getFullState() {
        return $this->state;
    }

    public function getHistory() {
        return $this->history;
    }
}
```

#### 4. Orchestrator

Coordinates the entire workflow:

```php
class Orchestrator {
    private $decomposer;
    private $agentPool;
    private $stateManager;

    public function execute($goal) {
        // 1. Decompose goal into subtasks
        $subtasks = $this->decomposer->decompose($goal);

        // 2. Execute each subtask
        $results = [];
        foreach ($subtasks as $subtask) {
            $agent = $this->selectAgent($subtask);
            $result = $this->agentPool->executeWithAgent($agent, $subtask);
            $results[] = $result;

            // Store in state
            $this->stateManager->setState(
                "subtask_{$subtask['id']}",
                $result
            );
        }

        // 3. Synthesize results
        return $this->synthesize($goal, $results);
    }
}
```

### 🎯 Complete Framework Implementation

#### Framework Class

```php
class AgenticFramework {
    private $client;
    private $tools = [];
    private $agents = [];
    private $state;

    public function __construct($client) {
        $this->client = $client;
        $this->state = new StateManager();
    }

    public function registerTool($tool) {
        $this->tools[] = $tool;
    }

    public function registerAgent($name, $config) {
        $this->agents[$name] = new Agent(
            $this->client,
            $config['tools'] ?? $this->tools,
            $config['system'] ?? '',
            $this->state
        );
    }

    public function execute($goal, $options = []) {
        // Decompose
        $subtasks = $this->decompose($goal);

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 47](/series/claude-php-developers/chapters/47-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 6 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/06-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="46"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 47](/series/claude-php-developers/chapters/47-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 6 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/06-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/06-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
