---
title: "40: Introduction to Agentic AI"
description: "Understand what makes AI systems agentic, learn the ReAct pattern, and discover when to use agents vs simple API calls. Foundation for building autonomous AI systems."
series: "claude-php-developers"
chapter: 40
order: 40
difficulty: "Beginner"
prerequisites:
  - "Completed Chapter 11: Tool Use Fundamentals"
  - "Understanding of Claude's tool use capabilities"
  - "Basic PHP programming knowledge"
---

![40: Introduction to Agentic AI](/images/claude-php/chapter-40-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 40</span>
</div>

# Chapter 40: Introduction to Agentic AI

## Overview

Welcome to the world of agentic AI! This chapter introduces the fundamental concepts that transform Claude from a chatbot into an autonomous agent capable of reasoning, taking actions, and solving complex problems through iterative tool use.

You'll learn what makes AI systems "agentic," understand the ReAct (Reason-Act-Observe) pattern that powers autonomous behavior, and discover when to use agents versus simple API calls. This foundation prepares you for building sophisticated AI agents in subsequent chapters.

**What You'll Learn:**
- The difference between chatbots and AI agents
- What "agentic" behavior means in AI systems
- The ReAct pattern and how it enables autonomy
- When to use agents vs simple API calls
- How tool use enables agent capabilities

**Estimated Time**: 20-30 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Understanding of tool definitions and tool calls
- ✓ **Basic Claude API knowledge** - Familiarity with messages and responses
- ✓ **PHP 8.4+** with Composer installed

## What is an AI Agent?

### Chatbot vs Agent

Understanding the distinction between chatbots and agents is crucial:

**🤖 Chatbot** (Traditional LLM Use):
- You ask a question → It responds
- Single turn interaction
- Relies only on its training data
- Cannot take actions or gather new information
- Passive responder

**🧠 AI Agent** (Agentic System):
- You give a goal → It figures out how to achieve it
- Multi-turn autonomous operation
- Can use tools to gather information or take actions
- Makes decisions about next steps
- Active problem solver

### Example Comparison

**Chatbot Interaction:**

```
You: "What's the weather in San Francisco?"
Bot: "I don't have access to real-time weather data.
      I was last trained in [date]..."
```

**Agent Interaction:**

```
You: "What's the weather in San Francisco?"
Agent: [Thinks: I need current weather data]
        [Acts: Calls weather API for San Francisco]
        [Observes: API returns 68°F, sunny]
        [Responds: "It's currently 68°F and sunny in San Francisco"]
```

## The ReAct Pattern

ReAct (Reason-Act-Observe) is the fundamental pattern that powers agentic behavior. It's a loop that continues until the task is complete:

```
┌─────────────────────────────────────────┐
│  1. REASON (Think)                      │
│     "What do I need to do next?"        │
│     "What information do I need?"       │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  2. ACT (Execute)                       │
│     "Call a tool to get information"    │
│     "Perform an action"                 │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  3. OBSERVE (Analyze)                   │
│     "What did the tool return?"         │
│     "Do I have enough information?"       │
└──────────────┬──────────────────────────┘
               │
               ▼
        ┌──────────────┐
        │  Complete?   │
        └──────┬───────┘
               │
        ┌──────┴──────┐
        │             │
       No            Yes
        │             │
        │             ▼
        │      ┌──────────────┐
        │      │  Respond to  │
        │      │     User     │
        │      └──────────────┘
        │
        └──────> (Loop back to REASON)
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

## Demonstrating the Concepts

Let's see the difference between chatbot and agent behavior:

```php
<?php
# filename: examples/01-chatbot-vs-agent.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Example 1: Traditional Chatbot (No Tools)
echo "=== Example 1: Chatbot Behavior ===\n";
echo "Question: What is 1,234 × 5,678?\n\n";

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is 1,234 × 5,678?']
    ]
]);

foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo "Chatbot Response: {$block['text']}\n";
    }
}

echo "\n💡 Observation: The chatbot tries to calculate mentally but may be approximate.\n";
echo "   It's limited to its training and reasoning capabilities.\n\n";

// Example 2: Agent with Tool (Calculator)
echo "=== Example 2: Agent Behavior ===\n";
echo "Same question: What is 1,234 × 5,678?\n\n";

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

// Step 1: Send request with tool
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'tools' => [$calculatorTool],
    'messages' => [
        ['role' => 'user', 'content' => 'What is 1,234 × 5,678?']
    ]
]);

// Check if agent wants to use the tool
$toolUse = null;
foreach ($response1->content as $block) {
    if ($block['type'] === 'tool_use') {
        $toolUse = $block;
        echo "✓ Agent decided to use tool: '{$block['name']}'\n";
        echo "✓ With parameters: " . json_encode($block['input']) . "\n\n";
        break;
    }
}

if ($toolUse) {
    // Step 2: Execute the tool
    $expression = $toolUse['input']['expression'];
    // In production, use a proper math parser!
    $result = eval("return {$expression};");
    
    // Step 3: Return result to agent
    $response2 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [$calculatorTool],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 1,234 × 5,678?'],
            ['role' => 'assistant', 'content' => $response1->content],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUse['id'],
                        'content' => (string)$result
                    ]
                ]
            ]
        ]
    ]);
    
    echo "Agent Final Response:\n";
    foreach ($response2->content as $block) {
        if ($block['type'] === 'text') {
            echo "{$block['text']}\n";
        }
    }
    
    echo "\n💡 Observation: The agent used a tool to get the EXACT answer.\n";
    echo "   This is the power of agentic behavior!\n";
}
```

## Understanding Stop Reasons

The `stop_reason` field tells you what the agent wants to do next:

- **`end_turn`**: Agent has completed its response
- **`tool_use`**: Agent wants to execute a tool
- **`max_tokens`**: Response was cut off (increase max_tokens)

```php
<?php
# filename: examples/02-stop-reasons.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform mathematical calculations.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => ['type' => 'string']
        ],
        'required' => ['expression']
    ]
];

$testCases = [
    [
        'name' => 'Direct Answer (No Tool)',
        'message' => 'What is the capital of France?',
        'expected' => 'end_turn'
    ],
    [
        'name' => 'Tool Use Needed',
        'message' => 'What is 987 × 654?',
        'expected' => 'tool_use'
    ]
];

foreach ($testCases as $test) {
    echo "Test: {$test['name']}\n";
    echo "Question: \"{$test['message']}\"\n";
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [$calculatorTool],
        'messages' => [
            ['role' => 'user', 'content' => $test['message']]
        ]
    ]);
    
    $stopReason = $response->stop_reason;
    $match = $stopReason === $test['expected'] ? '✓' : '✗';
    
    echo "  Stop Reason: {$stopReason} {$match}\n";
    echo "  Expected: {$test['expected']}\n\n";
}
```

## When to Use Agents vs Simple API Calls

### Use Simple API Calls When:
- ✅ You need a single, direct response
- ✅ The task doesn't require external data or actions
- ✅ You can formulate the exact prompt needed
- ✅ No multi-step reasoning is required

**Example**: "Explain dependency injection in PHP"

### Use Agents When:
- ✅ The task requires multiple steps
- ✅ You need real-time data (APIs, databases)
- ✅ Actions need to be taken (file operations, API calls)
- ✅ The solution path isn't predetermined
- ✅ You want autonomous problem-solving

**Example**: "Find the cheapest flight from New York to London next week"

## Key Takeaways

1. **Chatbots respond → Agents take action**
   - Chatbots are limited to their training
   - Agents use tools to extend capabilities

2. **ReAct Loop = Reason → Act → Observe → Repeat**
   - Agents iterate until the task is complete
   - Each iteration builds on previous results

3. **Tools are the agent's superpowers**
   - They enable getting real-time data
   - Performing calculations
   - Taking actions in your application

4. **Stop reasons guide the loop**
   - `tool_use` = needs to execute a tool
   - `end_turn` = task complete

5. **Iteration limits prevent infinite loops**
   - Always set a maximum to avoid runaway execution

## Next Steps

Now that you understand the fundamentals, you're ready to build your first agent:

- **[Chapter 41: Your First Agent](/series/claude-php-developers/chapters/41-your-first-agent)** - Build a working calculator agent
- **[Chapter 42: ReAct Loop Basics](/series/claude-php-developers/chapters/42-react-loop-basics)** - Implement multi-step reasoning
- **[Chapter 11: Tool Use Fundamentals](/series/claude-php-developers/chapters/11-tool-use-fundamentals)** - Deep dive into tool definitions

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [ReAct Paper](https://arxiv.org/abs/2210.03629) - Original research paper on ReAct pattern
- [Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems) - Advanced agent coordination

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="40"
  label="I understand the fundamentals of agentic AI!"
/>

---

Continue to [Chapter 41: Your First Agent](/series/claude-php-developers/chapters/41-your-first-agent) to build your first working agent.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 40 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-40)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-40
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-chatbot-vs-agent.php
```
