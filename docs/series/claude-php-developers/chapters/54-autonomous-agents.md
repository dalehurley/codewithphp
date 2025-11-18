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

Autonomous agents are self-directed systems that pursue goals independently, maintaining state across sessions and adapting to changing conditions. This chapter teaches you to build goal-directed autonomous agents with persistent state management, multi-session execution, safety conditions, and self-monitoring capabilities.

You'll learn to implement goal tracking, state persistence, progress monitoring, adaptive strategies, and critical safety safeguards for production autonomous systems.

**Estimated Time**: 90 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 53** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## What You'll Build

By the end of this chapter, you will have created:

- **Goal Tracking System** - Define and pursue primary and sub-goals with status tracking
- **State Persistence** - Save and load agent state between sessions
- **Progress Monitoring** - Track completion percentage and assess progress
- **Safety Limits** - Maximum iterations, cost limits, and duration bounds
- **Multi-Session Execution** - Resume agents across different runs
- **Adaptive Strategies** - Agents that adjust approach based on results
- **Checkpoint System** - Save intermediate results for recovery

## Objectives

By completing this chapter, you will:

- **Build** goal-directed autonomous agents that set their own sub-goals
- **Implement** persistent state management across sessions
- **Handle** multi-session agent execution with state recovery
- **Create** safety and termination conditions to prevent runaway execution
- **Design** self-monitoring systems that track progress toward goals
- **Implement** adaptive strategies that adjust based on results

## What Makes Agents Autonomous?

Autonomous agents:
- Set their own sub-goals
- Run independently over time
- Persist state between sessions
- Adapt strategies dynamically
- Monitor their own progress

## Step 1: Goal Management (~15 min)

Implement goal tracking with primary and sub-goals:

```php
<?php
# filename: examples/01-goal-management.php
declare(strict_types=1);

class GoalManager {
    private array $goals = [];

    public function setPrimaryGoal(string $goal): void {
        $this->goals['primary'] = $goal;
        $this->goals['sub_goals'] = [];
    }

    public function addSubGoal(string $id, string $description): void {
        $this->goals['sub_goals'][$id] = [
            'status' => 'pending',
            'progress' => 0.0,
            'description' => $description
        ];
    }

    public function updateSubGoalStatus(string $id, string $status, float $progress = 0.0): void {
        if (isset($this->goals['sub_goals'][$id])) {
            $this->goals['sub_goals'][$id]['status'] = $status;
            $this->goals['sub_goals'][$id]['progress'] = $progress;
        }
    }

    public function getGoals(): array {
        return $this->goals;
    }

    public function isComplete(): bool {
        foreach ($this->goals['sub_goals'] ?? [] as $subGoal) {
            if ($subGoal['status'] !== 'completed') {
                return false;
            }
        }
        return !empty($this->goals['sub_goals']);
    }
}

// Usage
$goalManager = new GoalManager();
$goalManager->setPrimaryGoal("Research and write article");
$goalManager->addSubGoal('research', 'Research PHP 8.4 features');
$goalManager->addSubGoal('outline', 'Create article outline');
$goalManager->addSubGoal('write', 'Write article draft');

$goalManager->updateSubGoalStatus('research', 'in_progress', 0.6);
```

**Why It Works**: Goal management provides structure for autonomous agents, allowing them to track progress and determine when objectives are complete.

## Step 2: State Persistence (~15 min)

Implement state persistence for multi-session execution:

```php
<?php
# filename: examples/02-state-persistence.php
declare(strict_types=1);

class StateManager {
    public function saveState(array $state, string $file = 'agent_state.json'): void {
        $state['last_updated'] = time();
        file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT));
    }

    public function loadState(string $file = 'agent_state.json'): array {
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        $state = json_decode($content, true);
        
        return is_array($state) ? $state : [];
    }

    public function hasState(string $file = 'agent_state.json'): bool {
        return file_exists($file);
    }
}

// Usage
$stateManager = new StateManager();

// Save state
$state = [
    'goals' => $goalManager->getGoals(),
    'iterations' => 5,
    'data' => ['research' => 'PHP 8.4 features...']
];
$stateManager->saveState($state);

// Load state in next session
if ($stateManager->hasState()) {
    $savedState = $stateManager->loadState();
    // Resume from saved state
}
```

**Why It Works**: State persistence enables agents to resume work across sessions, making long-running autonomous tasks feasible.

## Step 3: Progress Monitoring (~10 min)

Track and assess progress toward goals:

```php
<?php
# filename: examples/03-progress-monitoring.php
declare(strict_types=1);

function assessProgress(array $state): float {
    $subGoals = $state['goals']['sub_goals'] ?? [];
    if (empty($subGoals)) {
        return 0.0;
    }

    $total = count($subGoals);
    $completed = array_filter($subGoals, 
        fn($g) => $g['status'] === 'completed'
    );
    
    return count($completed) / $total;
}

function getProgressDetails(array $state): array {
    $subGoals = $state['goals']['sub_goals'] ?? [];
    $details = [];
    
    foreach ($subGoals as $id => $subGoal) {
        $details[$id] = [
            'status' => $subGoal['status'],
            'progress' => $subGoal['progress'] ?? 0.0
        ];
    }
    
    return $details;
}

// Usage
$progress = assessProgress($state);
echo "Overall progress: " . ($progress * 100) . "%\n";

$details = getProgressDetails($state);
foreach ($details as $id => $detail) {
    echo "{$id}: {$detail['status']} ({$detail['progress'] * 100}%)\n";
}
```

**Why It Works**: Progress monitoring allows agents to self-assess and make informed decisions about next steps.

## Step 4: Autonomous Agent Loop (~20 min)

Implement the complete autonomous agent loop:

```php
<?php
# filename: examples/04-autonomous-agent-loop.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

class AutonomousAgent {
    public function __construct(
        private ClaudePhp $client,
        private GoalManager $goalManager,
        private StateManager $stateManager,
        private string $stateFile = 'agent_state.json'
    ) {}

    public function run(string $primaryGoal): void {
        // Load existing state or initialize
        if ($this->stateManager->hasState($this->stateFile)) {
            $state = $this->stateManager->loadState($this->stateFile);
            echo "Resuming from saved state...\n";
        } else {
            $this->goalManager->setPrimaryGoal($primaryGoal);
            $state = ['goals' => $this->goalManager->getGoals(), 'iterations' => 0];
        }

        $safety = [
            'max_iterations' => 100,
            'max_cost' => 5.00,
            'max_duration' => 3600
        ];

        while (!$this->goalManager->isComplete() && !$this->checkTermination($state, $safety)) {
            // Assess current situation
            $assessment = $this->assess($state);
            
            // Decide next action
            $action = $this->decide($assessment);
            
            // Execute action
            $result = $this->execute($action);
            
            // Update state
            $state = $this->updateState($state, $result);
            $state['iterations']++;
            
            // Persist for next session
            $this->stateManager->saveState($state, $this->stateFile);
            
            // Check if we should pause
            if ($this->shouldPause($state)) {
                echo "Pausing... Resume later.\n";
                break;
            }
        }
    }

    private function checkTermination(array $state, array $safety): bool {
        if ($state['iterations'] >= $safety['max_iterations']) {
            echo "Max iterations reached\n";
            return true;
        }
        // Add other termination checks
        return false;
    }

    private function assess(array $state): array {
        $progress = assessProgress($state);
        return [
            'progress' => $progress,
            'goals' => $state['goals']
        ];
    }

    private function decide(array $assessment): string {
        // Use Claude to decide next action
        // Simplified for example
        return "continue";
    }

    private function execute(string $action): mixed {
        // Execute the action
        return "Action completed";
    }

    private function updateState(array $state, mixed $result): array {
        // Update state based on result
        return $state;
    }

    private function shouldPause(array $state): bool {
        // Check if we should pause (e.g., checkpoint reached)
        return false;
    }
}
```

**Why It Works**: The autonomous loop enables agents to work independently, making decisions and persisting state for resumption.

## Step 5: Safety and Termination Conditions (~15 min)

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

Implement critical safety safeguards:

```php
<?php
# filename: examples/05-safety-conditions.php
declare(strict_types=1);

class SafetyMonitor {
    private array $limits = [
        'max_iterations' => 100,
        'max_cost' => 5.00,
        'max_duration' => 3600,
        'termination_conditions' => ['goal_achieved', 'budget_exceeded', 'time_exceeded']
    ];

    public function checkSafety(array $state): bool {
        if ($state['iterations'] > $this->limits['max_iterations']) {
            throw new Exception("Max iterations exceeded");
        }
        
        if (isset($state['cost']) && $state['cost'] > $this->limits['max_cost']) {
            throw new Exception("Budget exceeded");
        }
        
        if (isset($state['start_time'])) {
            $duration = time() - $state['start_time'];
            if ($duration > $this->limits['max_duration']) {
                throw new Exception("Time limit exceeded");
            }
        }
        
        return true;
    }

    public function shouldTerminate(array $state): bool {
        if ($this->goalAchieved($state)) {
            return true;
        }
        
        try {
            $this->checkSafety($state);
        } catch (Exception $e) {
            return true;
        }
        
        return false;
    }

    private function goalAchieved(array $state): bool {
        $subGoals = $state['goals']['sub_goals'] ?? [];
        foreach ($subGoals as $subGoal) {
            if ($subGoal['status'] !== 'completed') {
                return false;
            }
        }
        return !empty($subGoals);
    }
}
```

**Why It Works**: Safety monitors prevent runaway execution, protecting against infinite loops, excessive costs, and unbounded runtime.

## Best Practices

### 1. Always Set Safety Limits

```php
$safety = [
    'max_iterations' => 100,
    'max_cost' => 5.00,
    'max_duration' => 3600
];
```

### 2. Save State Frequently

```php
// Save after each significant step
if ($milestoneReached) {
    $stateManager->saveState($state);
}
```

### 3. Implement Checkpoints

```php
$checkpoints = [
    'research_complete' => $researchData,
    'outline_done' => $outline,
    'draft_v1' => $draft
];
```

### 4. Monitor Resource Usage

```php
$budget = [
    'tokens_used' => 15000,
    'tokens_limit' => 100000,
    'cost_used' => 0.45,
    'cost_limit' => 5.00
];
```


## Next Steps

You've completed the autonomous agents chapter! Explore related topics:

- **[Chapter 46: Complete Agentic Framework](/series/claude-php-developers/chapters/46-complete-agentic-framework)** - Full framework implementation
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Chapter 51: Hierarchical Agents](/series/claude-php-developers/chapters/51-hierarchical-agents)** - Multi-level agents

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 14 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/14-autonomous-agents) - Original tutorial with code examples
- [Autonomous Agent Patterns](https://docs.anthropic.com/en/docs/build-with-claude/agentic-patterns) - Official documentation

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="54"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 55](/series/claude-php-developers/chapters/55-multi-tool-agent) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 54 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-54)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-54
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/04-autonomous-agent-loop.php
```

For the original tutorial code:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/14-autonomous-agents
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php autonomous_agent.php
```
