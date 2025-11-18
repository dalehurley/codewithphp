---
title: "43: Multi-Tool Agent"
description: "Build multi-tool agent with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 43
order: 43
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/42-react-loop-basics"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![43: Multi-Tool Agent](/images/claude-php/chapter-43-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 43</span>
</div>

# Chapter 43: Multi-Tool Agent

## Overview

In Chapter 42, you built agents with a single tool. Real-world agents need multiple diverse tools to handle various tasks. This chapter teaches you to create agents with several tools and understand how Claude chooses the right tool for each situation.

You'll learn to define multiple tools with clear, distinct purposes, help Claude select the appropriate tool through good descriptions, handle different tool types (data retrieval, computation, actions), and debug tool selection decisions.

**Estimated Time**: 45-60 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 42** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`
- ✓ **API Key** configured in environment

## What You'll Build

By the end of this chapter, you will have created:

- A **Smart Assistant Agent** with multiple diverse tools:
  - Calculator tool for mathematical computations
  - Current Time tool for timezone queries
  - Weather tool for weather information (simulated)
  - Web Search tool for information retrieval (simulated)
- Tool executor pattern that routes to the correct function
- Multi-tool ReAct loop that handles different tool types
- Tool selection debugging utilities
- Production-ready patterns for managing multiple tools

## Objectives

By completing this chapter, you will:

- **Define** multiple tools with clear, distinct purposes
- **Help** Claude choose the right tool through good descriptions
- **Handle** different tool types (data retrieval, computation, actions)
- **Debug** tool selection decisions effectively
- **Optimize** tool definitions for clarity and accuracy
- **Manage** tool execution workflows with multiple tools
- **Build** agents that can handle diverse requests across tool categories

## Why Multiple Tools?

Real-world agents need diverse capabilities:

**Single-Tool Agent** (Chapter 42):
- Can only perform one type of task
- Limited to calculator operations
- Cannot handle varied user requests

**Multi-Tool Agent** (this chapter):
- Handles diverse requests: "What time is it in Tokyo?", "Calculate 25% of 480", "What's the weather in London?"
- Chooses the right tool for each situation
- Combines tools when needed: "What's the weather in Tokyo and what time is it there?"

## Tool Selection

Claude chooses tools based on:

1. **Tool Name**: Clear, descriptive names help Claude understand purpose
2. **Tool Description**: What it does and when to use it (most important!)
3. **Input Schema**: What parameters it needs
4. **Context**: The user's request and conversation history

### Good vs Bad Tool Definitions

**❌ Bad Example:**

```php
[
    'name' => 'tool1',
    'description' => 'Does stuff',
    'input_schema' => [...]
]
```

**✅ Good Example:**

```php
[
    'name' => 'get_weather',
    'description' => 'Get current weather conditions for a specific city. ' .
                     'Returns temperature, conditions, and humidity. ' .
                     'Use this when the user asks about weather or temperature.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => 'City name, e.g., "San Francisco" or "London, UK"'
            ]
        ],
        'required' => ['city']
    ]
]
```

**Why It Works**: Clear descriptions tell Claude exactly when to use each tool. The description should answer: "What does this tool do?" and "When should I use it?"

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

- **[Chapter 44](/series/claude-php-developers/chapters/44-production-ready-agent)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Chapter 40: Introduction to Agentic AI](/series/claude-php-developers/chapters/40-introduction-to-agentic-ai)** - Agent fundamentals

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 3 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/03-multi-tool-agent) - Original tutorial with code examples
- [ReAct Paper](https://arxiv.org/abs/2210.03629) - Original research paper

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="43"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 44](/series/claude-php-developers/chapters/44-production-ready-agent) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 3 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/03-multi-tool-agent)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/03-multi-tool-agent
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php react_agent.php
```
