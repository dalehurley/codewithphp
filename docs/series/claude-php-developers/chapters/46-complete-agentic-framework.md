---
title: "46: Complete Agentic Framework"
description: "Build complete agentic framework with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 46
order: 46
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/45-advanced-react-patterns"
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

Welcome to building a complete agentic framework! This chapter brings together everything you've learned to build a comprehensive agentic system with task decomposition, parallel execution, state management, and orchestration.

You'll learn to design a complete agent architecture, implement task decomposition strategies, orchestrate multiple sub-agents, manage complex state across workflows, handle parallel tool execution, and build reusable agent components for production-grade systems.

**Estimated Time**: 90 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 45** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`
- ✓ **API Key** configured in environment

## What You'll Build

By the end of this chapter, you will have created:

- **Complete Agent Architecture** - Full framework with orchestrator, sub-agents, and state management
- **Task Decomposer** - System that breaks complex goals into manageable subtasks
- **Agent Pool** - Registry and management system for multiple specialized agents
- **State Manager** - Centralized state management with history tracking
- **Orchestrator** - Coordination system that manages the entire workflow
- **Parallel Execution** - Ability to run multiple agents simultaneously
- **Reusable Components** - Production-grade agentic framework ready for deployment

## Objectives

By completing this chapter, you will:

- **Design** a complete agent architecture with clear separation of concerns
- **Implement** task decomposition strategies for complex goals
- **Orchestrate** multiple sub-agents working in parallel
- **Manage** complex state across workflows and agents
- **Handle** parallel tool execution efficiently
- **Build** reusable agent components for production use
- **Create** production-grade agentic systems ready for deployment

## Framework Architecture

A complete agentic framework consists of several key components working together:

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

### Core Components

1. **Orchestrator** - Coordinates the entire workflow
2. **Task Decomposer** - Breaks complex tasks into subtasks
3. **Agent Pool** - Manages multiple specialized agents
4. **State Manager** - Maintains context across workflows
5. **Sub-Agents** - Specialized agents for specific tasks

## Step 1: Task Decomposer (~15 min)

The Task Decomposer breaks complex goals into manageable subtasks:

```php
<?php
# filename: examples/01-task-decomposer.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

class TaskDecomposer {
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function decompose(string $task): array {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'messages' => [[
                'role' => 'user',
                'content' => "Break down this task into 3-5 subtasks. " .
                            "Return a JSON array of subtask descriptions: {$task}"
            ]],
            'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
        ]);

        return $this->parseSubtasks($response);
    }

    private function parseSubtasks(object $response): array {
        // Extract JSON from response
        $text = '';
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                $text .= $block['text'];
            }
        }

        // Try to extract JSON array
        if (preg_match('/\[.*\]/s', $text, $matches)) {
            $subtasks = json_decode($matches[0], true);
            return is_array($subtasks) ? $subtasks : [];
        }

        return [];
    }
}

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$decomposer = new TaskDecomposer($client);

$goal = "Research PHP 8.4 features and write a blog post";
$subtasks = $decomposer->decompose($goal);

echo "Goal: {$goal}\n\nSubtasks:\n";
foreach ($subtasks as $i => $subtask) {
    echo ($i + 1) . ". {$subtask}\n";
}
```

**Why It Works**: The decomposer uses Claude's reasoning to break complex goals into actionable subtasks, enabling parallel execution and better organization.

## Step 2: Agent Pool (~15 min)

The Agent Pool manages multiple specialized agents:

```php
<?php
# filename: examples/02-agent-pool.php
declare(strict_types=1);

class AgentPool {
    private array $agents = [];

    public function registerAgent(string $name, callable $agent): void {
        $this->agents[$name] = $agent;
    }

    public function getAgent(string $name): ?callable {
        return $this->agents[$name] ?? null;
    }

    public function executeWithAgent(string $agentName, string $task): mixed {
        $agent = $this->getAgent($agentName);
        if (!$agent) {
            throw new Exception("Agent not found: {$agentName}");
        }

        return $agent($task);
    }

    public function listAgents(): array {
        return array_keys($this->agents);
    }
}

// Usage
$pool = new AgentPool();

// Register specialized agents
$pool->registerAgent('researcher', function($task) {
    // Research agent implementation
    return "Research results for: {$task}";
});

$pool->registerAgent('writer', function($task) {
    // Writing agent implementation
    return "Written content for: {$task}";
});

$result = $pool->executeWithAgent('researcher', 'PHP 8.4 features');
```

**Why It Works**: The agent pool provides a centralized registry for managing multiple specialized agents, enabling easy delegation and parallel execution.

## Step 3: State Manager (~15 min)

The State Manager maintains context across workflows:

```php
<?php
# filename: examples/03-state-manager.php
declare(strict_types=1);

class StateManager {
    private array $state = [];
    private array $history = [];

    public function setState(string $key, mixed $value): void {
        $this->state[$key] = $value;
        $this->history[] = [
            'action' => 'set',
            'key' => $key,
            'timestamp' => time()
        ];
    }

    public function getState(string $key): mixed {
        return $this->state[$key] ?? null;
    }

    public function getFullState(): array {
        return $this->state;
    }

    public function getHistory(): array {
        return $this->history;
    }

    public function clear(): void {
        $this->state = [];
        $this->history = [];
    }
}

// Usage
$stateManager = new StateManager();
$stateManager->setState('subtask_1', 'Research completed');
$stateManager->setState('subtask_2', 'Draft written');

echo "Current state: " . json_encode($stateManager->getFullState(), JSON_PRETTY_PRINT) . "\n";
```

**Why It Works**: Centralized state management ensures all components have access to shared context and history, enabling coordination across agents.

## Step 4: Complete Orchestrator (~30 min)

The Orchestrator coordinates the entire workflow:

```php
<?php
# filename: examples/04-complete-orchestrator.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

class Orchestrator {
    public function __construct(
        private TaskDecomposer $decomposer,
        private AgentPool $agentPool,
        private StateManager $stateManager
    ) {}

    public function execute(string $goal): string {
        // 1. Decompose goal into subtasks
        echo "Decomposing goal...\n";
        $subtasks = $this->decomposer->decompose($goal);
        
        // 2. Execute each subtask
        $results = [];
        foreach ($subtasks as $i => $subtask) {
            echo "Executing subtask " . ($i + 1) . ": {$subtask}\n";
            
            $agentName = $this->selectAgent($subtask);
            $result = $this->agentPool->executeWithAgent($agentName, $subtask);
            $results[] = $result;
            
            // Store in state
            $this->stateManager->setState("subtask_" . ($i + 1), $result);
        }
        
        // 3. Synthesize results
        echo "Synthesizing results...\n";
        return $this->synthesize($goal, $results);
    }

    private function selectAgent(string $subtask): string {
        // Simple agent selection logic
        if (stripos($subtask, 'research') !== false) {
            return 'researcher';
        } elseif (stripos($subtask, 'write') !== false) {
            return 'writer';
        }
        return 'general';
    }

    private function synthesize(string $goal, array $results): string {
        // Use Claude to synthesize final result
        $client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
        
        $context = "Goal: {$goal}\n\nResults:\n";
        foreach ($results as $i => $result) {
            $context .= ($i + 1) . ". {$result}\n";
        }
        
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'messages' => [[
                'role' => 'user',
                'content' => "{$context}\n\nSynthesize these results into a final answer."
            ]]
        ]);
        
        $text = '';
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                $text .= $block['text'];
            }
        }
        
        return $text;
    }
}

// Usage
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$decomposer = new TaskDecomposer($client);
$pool = new AgentPool();
$stateManager = new StateManager();

$orchestrator = new Orchestrator($decomposer, $pool, $stateManager);
$result = $orchestrator->execute("Research PHP 8.4 and write a summary");
echo "\nFinal Result:\n{$result}\n";
```

**Why It Works**: The orchestrator coordinates all components, managing the workflow from decomposition through execution to synthesis, creating a complete agentic system.

## Best Practices

### 1. Error Handling

```php
try {
    $result = $orchestrator->execute($goal);
} catch (Exception $e) {
    // Log error and update state
    $stateManager->setState('error', $e->getMessage());
    // Retry or fallback
}
```

### 2. Parallel Execution

```php
// Execute independent subtasks in parallel
$promises = [];
foreach ($subtasks as $subtask) {
    $promises[] = asyncExecute($subtask);
}
$results = awaitAll($promises);
```

### 3. Result Caching

```php
// Cache expensive operations
$cacheKey = md5($subtask);
if ($cached = $cache->get($cacheKey)) {
    return $cached;
}
$result = $agent->execute($subtask);
$cache->set($cacheKey, $result);
```

## Next Steps

Explore more advanced agent patterns:

- **[Chapter 47: Chain of Thought (CoT)](/series/claude-php-developers/chapters/47-chain-of-thought)** - Reasoning techniques
- **[Chapter 48: Tree of Thoughts](/series/claude-php-developers/chapters/48-tree-of-thoughts)** - Advanced reasoning
- **[Chapter 51: Hierarchical Agents](/series/claude-php-developers/chapters/51-hierarchical-agents)** - Multi-level agents

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 6 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/06-agentic-framework) - Original tutorial with code examples
- [Agent Architecture Patterns](https://docs.anthropic.com/en/docs/build-with-claude/agentic-patterns) - Official documentation

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="46"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 47](/series/claude-php-developers/chapters/47-chain-of-thought) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 46 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-46)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-46
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/04-complete-orchestrator.php
```

For the original tutorial code:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/06-agentic-framework
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php framework.php
```
