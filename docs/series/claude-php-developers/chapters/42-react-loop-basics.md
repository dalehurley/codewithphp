---
title: "42: ReAct Loop Basics"
description: "Implement the ReAct (Reason-Act-Observe) pattern to build agents that can solve complex multi-step problems through iterative reasoning and tool execution."
series: "claude-php-developers"
chapter: 42
order: 42
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/41-your-first-agent"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![42: ReAct Loop Basics](/images/claude-php/chapter-42-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 42</span>
</div>

# Chapter 42: ReAct Loop Basics

## Overview

In Chapter 41, you built an agent that could make a single tool call. But what about tasks that require multiple steps? That's where the **ReAct pattern** comes in. This chapter teaches you to implement a proper ReAct loop that enables iterative reasoning and multi-step problem solving.

The ReAct (Reason-Act-Observe) pattern is the fundamental building block for autonomous agents. It allows agents to break down complex problems into steps, execute tools iteratively, observe results, and adapt their approach until the task is complete.

**Estimated Time**: 45-60 minutes

## What You'll Build

By the end of this chapter, you will have created:

- A complete ReAct agent implementation with iterative reasoning loops
- Tool execution handlers that process multiple sequential tool calls
- Conversation state management that maintains context across iterations
- Proper stop condition handling for task completion
- Debugging utilities to visualize agent reasoning steps
- Production-ready patterns for error handling and iteration limits
- A working calculator agent that solves multi-step math problems

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 41: Your First Agent** - Understanding of basic agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`
- ✓ **API Key** configured in environment

## Objectives

By completing this chapter, you will:

- **Understand** the ReAct pattern and its role in agentic AI
- **Implement** iterative reasoning loops with proper state management
- **Handle** multiple tool calls in sequence within a single task
- **Maintain** conversation history across iterations
- **Implement** proper stop conditions and iteration limits
- **Debug** agent reasoning steps effectively
- **Build** production-ready agents with error handling and safety limits

## What is ReAct?

**ReAct** stands for **Reason** → **Act** → **Observe**, and it's the fundamental pattern for autonomous agents.

### The ReAct Loop

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

**Without ReAct**, agents can only:
- Answer questions with their training data
- Make ONE tool call per task
- Fail on complex multi-step problems

**With ReAct**, agents can:
- Gather information step-by-step
- Chain multiple tools together
- Adapt based on tool results
- Solve complex multi-step problems autonomously

### Example: Single Tool Call vs ReAct

**Question**: "What is (50 × 30) + (100 - 25)?"

**Traditional Agent** (Chapter 41):
- Can only make ONE tool call
- Would fail or give incomplete answer
- Cannot break down the problem

**ReAct Agent** (this chapter):
- Iteration 1: Calculate 50 × 30 = 1,500
- Iteration 2: Calculate 100 - 25 = 75
- Iteration 3: Calculate 1,500 + 75 = 1,575
- Final Answer: "1,575"

## Step 1: Understanding the ReAct Loop Structure (~10 min)

The ReAct loop consists of three main components:

1. **The Main Loop**: Iterates until task completion
2. **Stop Conditions**: Determines when to exit
3. **State Management**: Maintains conversation history

### The Main Loop Pattern

```php
<?php
# filename: examples/01-react-loop-structure.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Initialize conversation
$messages = [
    ['role' => 'user', 'content' => 'Your task here']
];

$maxIterations = 10;  // Safety limit - always set this!
$iteration = 0;
$finalResponse = null;

// The ReAct Loop
while ($iteration < $maxIterations) {
    $iteration++;
    
    // REASON: Call Claude with current state
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => $tools  // Your tool definitions
    ]);
    
    // Add assistant response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];
    
    // Check if done
    if ($response->stop_reason === 'end_turn') {
        $finalResponse = $response;
        break;  // Task complete!
    }
    
    // ACT: Execute tools if requested
    if ($response->stop_reason === 'tool_use') {
        // Extract and execute tools
        // Add results to messages
        // Loop continues...
    }
}
```

**Why It Works**: The loop maintains conversation history across iterations. Each iteration, Claude reasons about what to do next, acts by requesting tools, and observes the results. The loop continues until Claude determines the task is complete (`stop_reason === 'end_turn'`).

## Step 2: Complete ReAct Implementation (~20 min)

Let's build a complete working example with a calculator tool:

```php
<?php
# filename: examples/02-complete-react-agent.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define calculator tool
$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations. Supports basic arithmetic operations.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Mathematical expression to evaluate (e.g., "50 * 30", "100 - 25")'
            ]
        ],
        'required' => ['expression']
    ]
];

// Tool executor function
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
    
    echo "╔════ Iteration {$iteration} ════╗\n";
    
    // REASON: Call Claude with current state
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 4096,
            'messages' => $messages,
            'tools' => [$calculatorTool]
        ]);
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        break;
    }
    
    echo "🧠 REASON: Stop reason = {$response->stop_reason}\n";
    echo "   Tokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    
    // Add assistant response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];
    
    // Check if done
    if ($response->stop_reason === 'end_turn') {
        echo "✅ COMPLETE: Agent finished!\n";
        $finalResponse = $response;
        break;
    }
    
    // ACT: Execute tools if requested
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "🔧 ACT: Using tool '{$block['name']}'\n";
                echo "   Input: {$block['input']['expression']}\n";
                
                // Execute the tool
                $result = executeCalculator($block['input']['expression']);
                
                echo "👁️  OBSERVE: Tool returned: {$result}\n";
                
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
        } else {
            echo "⚠️  Warning: No tool results to add\n";
            break;
        }
    } else {
        echo "⚠️  Unexpected stop reason: {$response->stop_reason}\n";
        $finalResponse = $response;
        break;
    }
    
    echo "╚════════════════════════════╝\n\n";
}

// Display final answer
if ($finalResponse) {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "📝 Final Answer:\n";
    echo "───────────────────────────────────────────────────────────\n";
    foreach ($finalResponse->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "───────────────────────────────────────────────────────────\n";
    echo "Completed in {$iteration} iteration(s)\n";
} else {
    echo "⚠️  Max iterations ({$maxIterations}) reached without completion\n";
}
```

**Expected Output**:
```
Task: What is (50 × 30) + (100 - 25)?

╔════ Iteration 1 ════╗
🧠 REASON: Stop reason = tool_use
   Tokens: 350 in, 120 out
🔧 ACT: Using tool 'calculate'
   Input: 50 * 30
👁️  OBSERVE: Tool returned: 1500
╚════════════════════════════╝

╔════ Iteration 2 ════╗
🧠 REASON: Stop reason = tool_use
   Tokens: 450 in, 120 out
🔧 ACT: Using tool 'calculate'
   Input: 100 - 25
👁️  OBSERVE: Tool returned: 75
╚════════════════════════════╝

╔════ Iteration 3 ════╗
🧠 REASON: Stop reason = tool_use
   Tokens: 550 in, 120 out
🔧 ACT: Using tool 'calculate'
   Input: 1500 + 75
👁️  OBSERVE: Tool returned: 1575
╚════════════════════════════╝

╔════ Iteration 4 ════╗
🧠 REASON: Stop reason = end_turn
   Tokens: 650 in, 80 out
✅ COMPLETE: Agent finished!
╚════════════════════════════╝

═══════════════════════════════════════════════════════════
📝 Final Answer:
───────────────────────────────────────────────────────────
(50 × 30) + (100 - 25) = 1,575
───────────────────────────────────────────────────────────
Completed in 4 iteration(s)
```

**Why It Works**: Each iteration adds to the conversation history. Claude sees previous tool results and can reason about what to do next. The `tool_use_id` in tool results must match the `id` from the tool use request, ensuring Claude can correlate results with requests.

## Step 3: Stop Conditions and Safety (~10 min)

Your ReAct loop needs to exit properly. Here are the stop conditions:

### Stop Condition 1: Task Complete

```php
if ($response->stop_reason === 'end_turn') {
    // Claude has completed the task
    $finalResponse = $response;
    break;
}
```

### Stop Condition 2: Max Iterations

```php
if ($iteration >= $maxIterations) {
    // Safety limit reached
    echo "Max iterations reached\n";
    break;
}
```

### Stop Condition 3: Errors

```php
try {
    $response = $client->messages()->create([...]);
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    break;
}
```

### Stop Condition 4: No Tool Results

```php
if ($response->stop_reason === 'tool_use') {
    $toolResults = [];
    // ... extract tools ...
    
    if (empty($toolResults)) {
        echo "Warning: No tool results\n";
        break;
    }
}
```

### Complete Stop Condition Handler

```php
<?php
# filename: examples/03-stop-conditions.php
declare(strict_types=1);

// ... setup code ...

while ($iteration < $maxIterations) {
    $iteration++;
    
    try {
        $response = $client->messages()->create([...]);
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        break;  // Stop on error
    }
    
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];
    
    // Handle all stop reasons
    if ($response->stop_reason === 'end_turn') {
        // ✅ Task complete
        $finalResponse = $response;
        break;
    } elseif ($response->stop_reason === 'tool_use') {
        // Execute tools and continue
        $toolResults = [];
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                $result = executeTool($block);
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }
        
        if (empty($toolResults)) {
            echo "⚠️  No tool results - stopping\n";
            break;  // Stop if no tools executed
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $toolResults
        ];
    } elseif ($response->stop_reason === 'max_tokens') {
        // Response truncated - increase max_tokens or stop
        echo "⚠️  Max tokens reached\n";
        break;
    } else {
        // Unexpected stop reason
        echo "⚠️  Unexpected stop reason: {$response->stop_reason}\n";
        break;
    }
}
```

## Step 4: State Management (~5 min)

The conversation history (`$messages` array) is your state. Each iteration adds to it:

```php
<?php
# filename: examples/04-state-management.php
declare(strict_types=1);

// Initial state
$messages = [
    ['role' => 'user', 'content' => 'Task...']  // Turn 1
];

// Iteration 1: Claude requests tool
$messages[] = [
    'role' => 'assistant',
    'content' => [/* tool_use block */]  // Turn 2
];

// Iteration 1: Tool result added
$messages[] = [
    'role' => 'user',
    'content' => [/* tool_result block */]  // Turn 3
];

// Iteration 2: Claude requests another tool
$messages[] = [
    'role' => 'assistant',
    'content' => [/* tool_use block */]  // Turn 4
];

// Iteration 2: Tool result added
$messages[] = [
    'role' => 'user',
    'content' => [/* tool_result block */]  // Turn 5
];

// Iteration 3: Claude completes
$messages[] = [
    'role' => 'assistant',
    'content' => [/* text block with answer */]  // Turn 6
];
```

**Key Points**:
- Always append to `$messages`, never replace
- Tool results must have `role: 'user'`
- Assistant responses have `role: 'assistant'`
- The `tool_use_id` must match between tool use and tool result

## Step 5: Debugging ReAct Loops (~10 min)

Add debugging to understand what's happening:

```php
<?php
# filename: examples/05-debugging.php
declare(strict_types=1);

function debugIteration(int $iteration, object $response): void {
    echo "\n╔════ Iteration {$iteration} ════╗\n";
    echo "Stop Reason: {$response->stop_reason}\n";
    echo "Tokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Text: " . substr($block['text'], 0, 100) . "...\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "Tool: {$block['name']}\n";
            echo "  ID: {$block['id']}\n";
            echo "  Input: " . json_encode($block['input'], JSON_PRETTY_PRINT) . "\n";
        }
    }
    echo "╚════════════════════════════╝\n";
}

// Use in loop
while ($iteration < $maxIterations) {
    $iteration++;
    
    $response = $client->messages()->create([...]);
    
    debugIteration($iteration, $response);  // Debug output
    
    // ... rest of loop ...
}
```

## Common Issues and Solutions

### Issue 1: Infinite Loop

**Symptom**: Agent keeps making tool calls without completing

**Causes**:
- Max iterations too high (or missing)
- Tool results not formatted correctly
- Tool always returns incomplete information

**Solution**:
```php
// Always set a reasonable limit
$maxIterations = 10;

// Check for progress
$previousToolCount = 0;
$hasProgressed = false;

while ($iteration < $maxIterations) {
    // ... execute loop ...
    
    $currentToolCount = count(array_filter(
        $response->content,
        fn($b) => $b['type'] === 'tool_use'
    ));
    
    if ($currentToolCount > $previousToolCount) {
        $hasProgressed = true;
    }
    
    if ($iteration >= 5 && !$hasProgressed) {
        echo "Warning: Agent may be stuck\n";
        break;
    }
}
```

### Issue 2: Loop Exits Too Early

**Symptom**: Agent stops before task is complete

**Causes**:
- Max iterations too low
- Misinterpreting stop_reason
- Tool result contains errors

**Solution**:
```php
// Increase iterations for complex tasks
$maxIterations = 15;  // Instead of 10

// Verify stop reason
if ($response->stop_reason !== 'end_turn' && $iteration >= $maxIterations) {
    echo "Task incomplete after {$maxIterations} iterations\n";
    // Consider retrying or increasing limit
}
```

### Issue 3: Tool Results Not Working

**Symptom**: Agent doesn't use tool results

**Causes**:
- `tool_use_id` doesn't match
- Results not added to conversation
- Results in wrong format

**Solution**:
```php
// Verify IDs match
foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use') {
        echo "Tool Use ID: {$block['id']}\n";
        
        $toolResults[] = [
            'type' => 'tool_result',
            'tool_use_id' => $block['id'],  // Must match!
            'content' => $result
        ];
    }
}

// Ensure results are added with correct role
$messages[] = [
    'role' => 'user',  // Must be 'user'!
    'content' => $toolResults
];
```

## Best Practices

### 1. Always Set Max Iterations

```php
// ✅ Good
$maxIterations = 10;

// ❌ Bad - potential infinite loop!
// No limit
```

### 2. Preserve Conversation History

```php
// ✅ Good - Keep all messages
$messages[] = ['role' => 'assistant', 'content' => $response->content];
$messages[] = ['role' => 'user', 'content' => $toolResults];

// ❌ Bad - Losing context
$messages = [['role' => 'user', 'content' => $toolResults]];
```

### 3. Handle All Stop Reasons

```php
// ✅ Good - Handle all cases
if ($response->stop_reason === 'end_turn') {
    // Complete
} elseif ($response->stop_reason === 'tool_use') {
    // Execute tools
} elseif ($response->stop_reason === 'max_tokens') {
    // Increase max_tokens or handle truncation
} else {
    // Unexpected - log and handle
}

// ❌ Bad - Only checking one
if ($response->stop_reason === 'end_turn') {
    // What about tool_use?
}
```

### 4. Log for Debugging

```php
// ✅ Good - Detailed logging
echo "Iteration {$iteration}: {$response->stop_reason}\n";
echo "Tokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";

// ❌ Bad - No visibility
// Silent execution
```

### 5. Validate Tool Results

```php
// ✅ Good - Check before adding
if (empty($toolResults)) {
    echo "Warning: No tool results\n";
    break;
}
$messages[] = ['role' => 'user', 'content' => $toolResults];

// ❌ Bad - Blindly add
$messages[] = ['role' => 'user', 'content' => $toolResults];
```

## Iteration Limits Guide

Choose iteration limits based on task complexity:

| Task Complexity | Suggested Limit | Example |
|----------------|-----------------|---------|
| Simple | 3-5 iterations | Single calculation |
| Medium | 5-10 iterations | Multi-step calculation |
| Complex | 10-15 iterations | Research + analysis |
| Very Complex | 15-25 iterations | Multi-stage workflows |

**Token Costs**: Each iteration uses tokens for tool definitions (~50-200), system prompt (~350), growing message history (100-1000+), and Claude's response (50-500+). A 10-iteration task might use 5,000-15,000 tokens total.

## Next Steps

Now that you understand ReAct loops, you're ready for more advanced patterns:

- **[Chapter 43: Multi-Tool Agent](/series/claude-php-developers/chapters/43-multi-tool-agent)** - Give your agent multiple diverse tools
- **[Chapter 44: Production-Ready Agent](/series/claude-php-developers/chapters/44-production-ready-agent)** - Build robust agents with error handling
- **[Chapter 11: Tool Use Fundamentals](/series/claude-php-developers/chapters/11-tool-use-fundamentals)** - Review tool definitions

## Further Reading

- [ReAct Paper](https://arxiv.org/abs/2210.03629) - Original research paper
- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 2 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/02-react-basics) - Original tutorial with code examples

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="42"
  label="I've implemented the ReAct loop pattern!"
/>

---

Continue to [Chapter 43: Multi-Tool Agent](/series/claude-php-developers/chapters/43-multi-tool-agent) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 42 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-42)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-42
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/02-complete-react-agent.php
```

For the original tutorial code:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/02-react-basics
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php react_agent.php
```
