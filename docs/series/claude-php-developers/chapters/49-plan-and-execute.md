---
title: "49: Plan-and-Execute"
description: "Build plan-and-execute with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 49
order: 49
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/48-tree-of-thoughts"
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

Plan-and-Execute enables agents to solve complex multi-step problems through iterative reasoning and tool execution. This chapter teaches you to implement the ReAct (Reason-Act-Observe) pattern, handle multiple tool calls in sequence, maintain conversation state, and build production-ready agents that can tackle complex tasks autonomously.

By the end of this chapter, you'll understand how to build agents that can reason about problems, execute tools iteratively, observe results, and adapt their approach until the task is complete.

**Estimated Time**: 45-60 min

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 48** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`
- ✓ **API Key** configured in environment

## What You'll Build

By the end of this chapter, you will have created:

- A complete ReAct agent implementation with iterative reasoning
- Tool execution handlers for multi-step workflows
- Conversation state management across iterations
- Proper stop condition handling
- Debugging utilities for agent reasoning
- Production-ready patterns for error handling and iteration limits

## Objectives

By completing this chapter, you will:

- **Understand** the ReAct pattern and its role in agentic AI
- **Implement** iterative reasoning loops with proper state management
- **Handle** multiple tool calls in sequence
- **Maintain** conversation history across iterations
- **Implement** proper stop conditions and iteration limits
- **Debug** agent reasoning steps effectively
- **Build** production-ready agents with error handling

## The ReAct Pattern

**ReAct** stands for **Reason** → **Act** → **Observe**, and it's the fundamental pattern for autonomous agents.

### The Loop Flow

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
      │        [Return Answer]
      │
      └──> (Back to REASON)
```

### Why ReAct Matters

Without ReAct, agents can only:
- Answer questions with their training data
- Make ONE tool call per task

With ReAct, agents can:
- Gather information step-by-step
- Chain multiple tools together
- Adapt based on tool results
- Solve complex multi-step problems

## Step 1: Basic ReAct Loop Implementation

Let's start with a complete working example:

```php
<?php
# filename: examples/01-react-loop.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define calculator tool
$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Mathematical expression to evaluate'
            ]
        ],
        'required' => ['expression']
    ]
];

// Tool executor
function executeCalculator(string $expression): string {
    try {
        // WARNING: eval() for demo only! Use proper parser in production
        $result = eval("return {$expression};");
        return (string)$result;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

// User task requiring multiple steps
$task = "What is (50 × 30) + (100 - 25)?";
echo "Task: {$task}\n\n";

// Initialize conversation
$messages = [
    ['role' => 'user', 'content' => $task]
];

$maxIterations = 10;
$iteration = 0;
$finalResponse = null;

// ReAct Loop
while ($iteration < $maxIterations) {
    $iteration++;
    
    echo "Iteration {$iteration}\n";
    
    // REASON: Call Claude with current state
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => [$calculatorTool]
    ]);
    
    echo "  Stop Reason: {$response->stop_reason}\n";
    
    // Add assistant response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];
    
    // Check if done
    if ($response->stop_reason === 'end_turn') {
        $finalResponse = $response;
        break;
    }
    
    // ACT: Execute tools if requested
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "  Using tool: {$block['name']}\n";
                
                // Execute tool
                $result = executeCalculator($block['input']['expression']);
                echo "  Result: {$result}\n";
                
                // Format tool result
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }
        
        // OBSERVE: Add results to conversation
        if (!empty($toolResults)) {
            $messages[] = [
                'role' => 'user',
                'content' => $toolResults
            ];
        }
    }
}

// Display final answer
if ($finalResponse) {
    echo "\nFinal Answer:\n";
    foreach ($finalResponse->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} else {
    echo "\nMax iterations reached without completion\n";
}
```

**Why It Works**: The ReAct loop maintains conversation history across iterations. Each iteration, Claude reasons about what to do next, acts by requesting tools, and observes the results. The loop continues until Claude determines the task is complete (`stop_reason === 'end_turn'`).

## Step 2: Stop Conditions and Safety

Always implement proper stop conditions:

```php
<?php
# filename: examples/02-stop-conditions.php
declare(strict_types=1);

// Stop conditions
$stopReasons = [
    'end_turn' => 'Task complete',
    'max_tokens' => 'Response truncated',
    'tool_use' => 'Tool execution needed'
];

// Safety limits
$maxIterations = 10;
$maxTokens = 10000;
$totalTokens = 0;

while ($iteration < $maxIterations) {
    $iteration++;
    
    $response = $client->messages()->create([...]);
    
    $totalTokens += $response->usage->input_tokens + $response->usage->output_tokens;
    
    // Check token limit
    if ($totalTokens > $maxTokens) {
        echo "Token limit reached\n";
        break;
    }
    
    // Check stop reason
    if ($response->stop_reason === 'end_turn') {
        break; // Success
    }
    
    // Handle tool use...
}
```

## Step 3: Debugging Agent Reasoning

Add debugging to understand agent behavior:

```php
<?php
# filename: examples/03-debugging.php
declare(strict_types=1);

function debugIteration(int $iteration, object $response): void {
    echo "\n╔════ Iteration {$iteration} ════╗\n";
    echo "Stop Reason: {$response->stop_reason}\n";
    echo "Tokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Text: {$block['text']}\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "Tool: {$block['name']}\n";
            echo "  Input: " . json_encode($block['input']) . "\n";
        }
    }
}
```

## Common Issues and Solutions

### Issue: Infinite Loop

**Symptom**: Agent keeps making tool calls without completing

**Solution**: Always set iteration limits and check for progress

```php
$maxIterations = 10;
$hasProgressed = false;
$previousToolCount = 0;

while ($iteration < $maxIterations) {
    // ... execute loop ...
    
    $currentToolCount = count(array_filter($response->content, fn($b) => $b['type'] === 'tool_use'));
    
    if ($currentToolCount > $previousToolCount) {
        $hasProgressed = true;
    }
    
    if ($iteration >= 5 && !$hasProgressed) {
        echo "Warning: Agent may be stuck\n";
        break;
    }
}
```

## Next Steps

Continue to the next chapter in the agent series:

- **[Chapter 50](/series/claude-php-developers/chapters/50-reflection-self-critique)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Chapter 40: Introduction to Agentic AI](/series/claude-php-developers/chapters/40-introduction-to-agentic-ai)** - Agent fundamentals

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 9 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/09-plan-and-execute) - Original tutorial with code examples
- [ReAct Paper](https://arxiv.org/abs/2210.03629) - Original research paper

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="49"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 50](/series/claude-php-developers/chapters/50-reflection-self-critique) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 9 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/09-plan-and-execute)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/09-plan-and-execute
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php react_agent.php
```
