---
title: "45: Advanced ReAct Patterns"
description: "Build advanced react patterns with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 45
order: 45
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/44-production-ready-agent"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![45: Advanced ReAct Patterns](/images/claude-php/chapter-45-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 45</span>
</div>

# Chapter 45: Advanced ReAct Patterns

## Overview

You've built production-ready agents. Now let's make them smarter with **planning**, **reflection**, and **extended thinking**. This chapter teaches you advanced ReAct patterns that enable agents to solve complex problems through deliberate reasoning.

You'll learn to implement the Plan-Execute-Reflect-Adjust pattern, use extended thinking for complex reasoning, enable agent self-correction, decompose complex tasks into subtasks, and balance thinking depth with costs.

**Estimated Time**: 60-75 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 44** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`
- ✓ **API Key** configured in environment

## What You'll Build

By the end of this chapter, you will have created:

- **Plan-Execute-Reflect-Adjust Pattern** - Advanced reasoning loop with planning and reflection
- **Extended Thinking Implementation** - Deep reasoning for complex problems using thinking tokens
- **Self-Correction System** - Agents that detect and fix their own mistakes
- **Task Decomposition** - Breaking complex tasks into manageable subtasks
- **Reflection Mechanisms** - Analyzing what worked and what didn't
- **Adaptive Strategy Selection** - Agents that adjust their approach based on results

## Objectives

By completing this chapter, you will:

- **Implement** the Plan-Execute-Reflect-Adjust pattern for advanced reasoning
- **Use** extended thinking for complex problem-solving
- **Enable** agent self-correction and mistake detection
- **Decompose** complex tasks into manageable subtasks
- **Implement** reflection and adaptation mechanisms
- **Balance** thinking depth with API costs
- **Build** smarter agents that plan before acting

## What is Advanced ReAct?

Standard ReAct: `Reason → Act → Observe → Repeat`

Advanced ReAct adds:

- **Planning**: Think ahead before acting
- **Reflection**: Analyze what worked and what didn't
- **Extended Thinking**: Deep reasoning for complex problems
- **Self-Correction**: Detect and fix mistakes

### The Advanced Pattern

```
┌────────────────────────────────────┐
│  1. PLAN                           │
│     "Break down the task"          │
│     "What steps are needed?"       │
│     "What could go wrong?"         │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  2. EXECUTE (Standard ReAct)       │
│     Reason → Act → Observe         │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  3. REFLECT                        │
│     "Did it work as expected?"     │
│     "What went well?"              │
│     "What needs improvement?"      │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  4. ADJUST                         │
│     "Change approach if needed"    │
│     "Try alternative strategy"     │
│     "Continue or complete"         │
└────────────┬───────────────────────┘
             │
      ┌──────┴──────┐
      │   Complete? │
      └──────┬──────┘
             │
       No────┴────Yes
       │           │
       └─→ PLAN    └─→ [Done]
```

## Step 1: Extended Thinking (~15 min)

Extended thinking gives Claude more "thinking tokens" for complex reasoning:

```php
<?php
# filename: examples/01-extended-thinking.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Enable extended thinking for complex problems
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 4096,
    'messages' => $messages,
    'tools' => $tools,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 10000  // Up to 32K for Opus
    ]
]);
```

### When to Use Extended Thinking

- **Complex Analysis**: Multi-step logical reasoning
- **Planning**: Breaking down complex tasks
- **Problem Solving**: Finding non-obvious solutions
- **Debugging**: Analyzing why something failed

### Cost Considerations

Thinking tokens are **priced differently**:
- Sonnet 4.5: Input rate for both cache hit/miss
- Opus: Input rate
- Budget wisely (1K-32K tokens)

**Why It Works**: Extended thinking allows Claude to reason deeply before acting, leading to better plans and fewer mistakes. Use it for complex problems where upfront planning saves iterations later.

## Step 2: Planning Pattern (~15 min)

Add planning phase before execution:

```php
<?php
# filename: examples/02-planning-pattern.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$planningSystem = "You are a meticulous planner. Before taking action:\n" .
                  "1. Break down the task into clear steps\n" .
                  "2. Identify what information is needed\n" .
                  "3. Anticipate potential issues\n" .
                  "4. Propose a strategy\n\n" .
                  "Only after planning, execute the plan step by step.";

// Phase 1: Planning
$messages = [
    ['role' => 'user', 'content' => "Task: {$task}\n\nCreate a detailed plan."]
];

$planResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'system' => $planningSystem,
    'messages' => $messages,
    'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
]);

$messages[] = ['role' => 'assistant', 'content' => $planResponse->content];

// Phase 2: Execution (standard ReAct loop)
$messages[] = ['role' => 'user', 'content' => 'Execute your plan step by step.'];
// ... continue with standard ReAct loop ...
```

**Why It Works**: Planning upfront helps Claude break down complex tasks and anticipate issues, leading to more efficient execution and fewer mistakes.

## Step 3: Reflection Pattern (~15 min)

Add reflection phase after execution:

```php
<?php
# filename: examples/03-reflection-pattern.php
declare(strict_types=1);

// After execution completes
$reflectionPrompt = "Reflect on what happened:\n" .
                    "1. Did it work as expected?\n" .
                    "2. What went well?\n" .
                    "3. What needs improvement?\n" .
                    "4. Are there any issues to address?";

$messages[] = ['role' => 'user', 'content' => $reflectionPrompt];

$reflectionResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'messages' => $messages,
    'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
]);

// Check if reflection reveals issues
function extractTextContent(array $content): string {
    $text = '';
    foreach ($content as $block) {
        if ($block['type'] === 'text') {
            $text .= $block['text'] . ' ';
        }
    }
    return $text;
}

$reflectionText = extractTextContent($reflectionResponse->content);
$issueKeywords = ['issue', 'problem', 'incorrect', 'wrong', 'error', 'failed'];

$hasIssues = false;
foreach ($issueKeywords as $keyword) {
    if (stripos($reflectionText, $keyword) !== false) {
        $hasIssues = true;
        break;
    }
}

if ($hasIssues) {
    echo "⚠️  Agent detected issues. Attempting correction...\n";
    $messages[] = ['role' => 'assistant', 'content' => $reflectionResponse->content];
    $messages[] = ['role' => 'user', 'content' => 'Please correct the identified issues.'];
    // Continue loop for correction...
}
```

**Why It Works**: Reflection allows the agent to self-assess and identify problems, enabling self-correction and continuous improvement.

## Step 4: Complete Advanced ReAct Implementation (~20 min)

Combine planning, execution, and reflection:

```php
<?php
# filename: examples/04-complete-advanced-react.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

function advancedReActAgent(ClaudePhp $client, string $task, array $tools): string {
    $system = "You are a thoughtful agent that plans before acting " .
              "and reflects after executing.";

    // Phase 1: Planning
    echo "Phase 1: Planning\n";
    $messages = [
        ['role' => 'user', 'content' => "Task: {$task}\n\nCreate a detailed plan."]
    ];

    $planResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'system' => $system,
        'messages' => $messages,
        'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
    ]);

    $messages[] = ['role' => 'assistant', 'content' => $planResponse->content];

    // Phase 2: Execution (standard ReAct)
    echo "\nPhase 2: Execution\n";
    $messages[] = ['role' => 'user', 'content' => 'Execute your plan step by step.'];
    
    $maxIterations = 10;
    $iteration = 0;
    
    while ($iteration < $maxIterations) {
        $iteration++;
        
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 4096,
            'system' => $system,
            'messages' => $messages,
            'tools' => $tools
        ]);
        
        $messages[] = ['role' => 'assistant', 'content' => $response->content];
        
        if ($response->stop_reason === 'end_turn') {
            break;
        }
        
        if ($response->stop_reason === 'tool_use') {
            $toolResults = [];
            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
                    $result = executeTool($block['name'], $block['input']);
                    $toolResults[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $block['id'],
                        'content' => $result
                    ];
                }
            }
            if (!empty($toolResults)) {
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }
        }
    }

    // Phase 3: Reflection
    echo "\nPhase 3: Reflection\n";
    $messages[] = [
        'role' => 'user',
        'content' => "Reflect on the execution:\n" .
                     "1. Did it work as expected?\n" .
                     "2. What went well?\n" .
                     "3. What needs improvement?"
    ];

    $reflectionResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => $messages,
        'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
    ]);

    // Extract final answer
    $finalAnswer = '';
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $finalAnswer .= $block['text'];
        }
    }
    
    return $finalAnswer;
}
```

**Why It Works**: The complete pattern combines planning (think ahead), execution (act), and reflection (learn), creating a more intelligent agent that improves over time.

## Best Practices

### 1. Balance Thinking Budget

```php
// Simple tasks: minimal thinking
'thinking' => ['type' => 'enabled', 'budget_tokens' => 1000]

// Complex tasks: more thinking
'thinking' => ['type' => 'enabled', 'budget_tokens' => 10000]
```

### 2. Use Planning for Complex Tasks

```php
// Simple calculation: skip planning
if (isSimpleTask($task)) {
    // Go straight to execution
} else {
    // Use planning phase
}
```

### 3. Reflect After Major Milestones

```php
// Reflect after completing major steps
if ($milestoneReached) {
    triggerReflection();
}
```

## Next Steps

Explore more advanced agent patterns:

- **[Chapter 46: Complete Agentic Framework](/series/claude-php-developers/chapters/46-complete-agentic-framework)** - Full framework implementation
- **[Chapter 47: Chain of Thought (CoT)](/series/claude-php-developers/chapters/47-chain-of-thought)** - Reasoning techniques
- **[Chapter 49: Plan-and-Execute](/series/claude-php-developers/chapters/49-plan-and-execute)** - Planning strategies

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 5 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/05-advanced-react) - Original tutorial with code examples
- [Extended Thinking Documentation](https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking) - Official guide

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="45"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 46](/series/claude-php-developers/chapters/46-complete-agentic-framework) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 45 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-45)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-45
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/04-complete-advanced-react.php
```

For the original tutorial code:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/05-advanced-react
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php advanced_react_agent.php
```
