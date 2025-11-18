---
title: "41: Your First Agent"
description: "Build your first working AI agent with a calculator tool. Learn the complete Request → Tool Call → Execute → Response cycle and master the fundamentals of agent development."
series: "claude-php-developers"
chapter: 41
order: 41
difficulty: "Beginner"
prerequisites:
  - "/series/claude-php-developers/chapters/40-introduction-to-agentic-ai"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
  - "PHP 8.4+ with Composer"
---

![41: Your First Agent](/images/claude-php/chapter-41-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 41</span>
</div>

# Chapter 41: Your First Agent

## Overview

Now that you understand the concepts of agentic AI, let's build your first working agent! In this chapter, you'll create a simple calculator agent with a single tool and walk through every step of the agent interaction cycle.

You'll learn to define tools with proper schemas, send requests with tools to Claude, handle tool use requests, execute tools, and return results. By the end, you'll have a complete understanding of the Request → Tool Call → Execute → Response flow that powers all AI agents.

**What You'll Build:**
- A Calculator Agent that recognizes when calculations are needed
- Complete tool definition with JSON schema
- Tool execution and result handling
- Full agent interaction cycle

**Estimated Time**: 30-45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 40: Introduction to Agentic AI** - Understanding of ReAct pattern
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Basic tool knowledge
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`
- ✓ **API Key** configured in environment

## The Tool Use Flow

Before we code, let's visualize what happens:

```
┌─────────────────────────────────────────────────────────────┐
│  1. User Request                                            │
│     "What is 157 × 89?"                                     │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  2. Your Code: Send to Claude with Tools                   │
│     POST /v1/messages                                       │
│     {                                                       │
│       "messages": [...],                                    │
│       "tools": [calculator_tool]                            │
│     }                                                       │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  3. Claude: Analyzes & Decides                              │
│     "I need to calculate 157 × 89"                          │
│     Returns: stop_reason='tool_use'                         │
│     Tool request: calculate("157 * 89")                     │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  4. Your Code: Execute Tool                                 │
│     result = 157 * 89 = 13,973                             │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  5. Your Code: Return Result to Claude                      │
│     POST /v1/messages                                       │
│     {                                                       │
│       "messages": [                                         │
│         ...previous messages...,                            │
│         { "role": "user",                                   │
│           "content": [{"type": "tool_result", ...}] }      │
│       ]                                                     │
│     }                                                       │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  6. Claude: Formulates Final Response                       │
│     "157 × 89 equals 13,973"                                │
│     Returns: stop_reason='end_turn'                         │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  7. Your Code: Display to User                              │
│     "157 × 89 equals 13,973"                                │
└─────────────────────────────────────────────────────────────┘
```

## Step 1: Define Your Tool

A tool definition tells Claude three things:

1. **Name**: What to call it
2. **Description**: What it does (helps Claude decide when to use it)
3. **Input Schema**: What parameters it needs

```php
<?php
# filename: examples/01-tool-definition.php
declare(strict_types=1);

$calculatorTool = [
    'name' => 'calculate',
    
    'description' => 'Perform precise mathematical calculations. ' .
                   'Supports basic arithmetic operations: ' .
                   'addition (+), subtraction (-), ' .
                   'multiplication (*), division (/), ' .
                   'and parentheses for order of operations.',
    
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'The mathematical expression to evaluate. ' .
                                'Examples: "2 + 2", "15 * 8", "(100 - 25) / 5"'
            ]
        ],
        'required' => ['expression']
    ]
];
```

### Key Points

- **Good descriptions** help Claude choose the right tool at the right time
- **Input schema** follows JSON Schema format
- **Required fields** ensure Claude provides all necessary parameters
- **Parameter descriptions** guide Claude on formatting

## Step 2: Make the First Request

Send the user's question along with available tools:

```php
<?php
# filename: examples/02-first-request.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is 157 × 89?']
    ],
    'tools' => [$calculatorTool]  // Provide the tool
]);
```

## Step 3: Check the Response

Claude's response will include a `stop_reason` that tells you what to do next:

```php
<?php
# filename: examples/03-check-response.php
declare(strict_types=1);

// Check what Claude wants to do
$stopReason = $response->stop_reason;

if ($stopReason === 'tool_use') {
    // Claude wants to use a tool!
} elseif ($stopReason === 'end_turn') {
    // Claude has a final answer (no tool needed)
} elseif ($stopReason === 'max_tokens') {
    // Response was truncated (increase max_tokens)
}
```

### Understanding `stop_reason`

| Value        | Meaning                        | Action                                       |
| ------------ | ------------------------------ | -------------------------------------------- |
| `tool_use`   | Claude wants to execute a tool | Extract tool use block and execute           |
| `end_turn`   | Claude finished its response   | Display response to user                     |
| `max_tokens` | Hit token limit                | Increase `max_tokens` or handle continuation |

## Step 4: Extract Tool Use

When `stop_reason === 'tool_use'`, extract the tool request:

```php
<?php
# filename: examples/04-extract-tool-use.php
declare(strict_types=1);

$toolUse = null;

foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use') {
        $toolUse = $block;
        // $toolUse contains:
        // - 'id': Unique identifier for this tool call
        // - 'name': The tool name ('calculate')
        // - 'input': Parameters (e.g., ['expression' => '157 * 89'])
        break;
    }
}
```

## Step 5: Execute the Tool

Now run the actual tool function:

```php
<?php
# filename: examples/05-execute-tool.php
declare(strict_types=1);

if ($toolUse) {
    $expression = $toolUse['input']['expression'];
    
    // WARNING: eval() is used here for demonstration only!
    // In production, use a proper math parser library
    try {
        $result = eval("return {$expression};");
    } catch (Exception $e) {
        $result = "Error: " . $e->getMessage();
    }
    
    echo "Expression: {$expression}\n";
    echo "Result: {$result}\n";
}
```

**⚠️ Security Note**: Never use `eval()` with untrusted input in production! Use a proper math parser library like `brick/math` or `math-php/math`.

## Step 6: Return Result to Claude

Send the tool result back to Claude:

```php
<?php
# filename: examples/06-return-result.php
declare(strict_types=1);

$messages = [
    ['role' => 'user', 'content' => 'What is 157 × 89?'],
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
];

$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => $messages,
    'tools' => [$calculatorTool]
]);
```

## Step 7: Display Final Answer

Extract and display Claude's final response:

```php
<?php
# filename: examples/07-display-answer.php
declare(strict_types=1);

foreach ($response2->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . "\n";
    }
}
```

## Complete Working Example

Here's the complete calculator agent:

```php
<?php
# filename: examples/08-complete-agent.php
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

// User question
$userQuestion = "What is 157 × 89?";

// Step 1: Send request with tool
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => $userQuestion]
    ],
    'tools' => [$calculatorTool]
]);

// Step 2: Check if Claude wants to use the tool
if ($response1->stop_reason === 'tool_use') {
    // Step 3: Extract tool use
    $toolUse = null;
    foreach ($response1->content as $block) {
        if ($block['type'] === 'tool_use') {
            $toolUse = $block;
            break;
        }
    }
    
    if ($toolUse) {
        // Step 4: Execute tool
        $expression = $toolUse['input']['expression'];
        $result = eval("return {$expression};"); // Demo only!
        
        // Step 5: Return result to Claude
        $response2 = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $userQuestion],
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
            ],
            'tools' => [$calculatorTool]
        ]);
        
        // Step 6: Display final answer
        foreach ($response2->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'] . "\n";
            }
        }
    }
} elseif ($response1->stop_reason === 'end_turn') {
    // Claude answered directly without tools
    foreach ($response1->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
}
```

## When Claude Doesn't Need Tools

Claude is smart about when to use tools. Simple questions might get direct answers:

```php
<?php
# filename: examples/09-direct-answer.php
declare(strict_types=1);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is 2 + 2?']
    ],
    'tools' => [$calculatorTool]
]);

if ($response->stop_reason === 'end_turn') {
    // Claude answered directly - no tool needed for simple math
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
}
```

## Debugging Tips

1. **Check `stop_reason`**: Always verify what Claude wants to do
2. **Log tool calls**: Print tool use blocks to see what Claude requested
3. **Validate inputs**: Check tool parameters before execution
4. **Handle errors**: Tools can fail; always wrap in try-catch
5. **Monitor tokens**: Track usage across multiple API calls

## Key Takeaways

1. **Tool definitions** tell Claude what capabilities are available
2. **Stop reasons** guide your code flow (`tool_use` vs `end_turn`)
3. **Tool execution** happens in your PHP code, not Claude
4. **Conversation history** must include all previous messages
5. **Tool results** are returned as `tool_result` content blocks

## Next Steps

Now that you've built your first agent, you're ready for:

- **[Chapter 42: ReAct Loop Basics](/series/claude-php-developers/chapters/42-react-loop-basics)** - Multi-step reasoning
- **[Chapter 43: Multi-Tool Agent](/series/claude-php-developers/chapters/43-multi-tool-agent)** - Agents with multiple tools
- **[Chapter 11: Tool Use Fundamentals](/series/claude-php-developers/chapters/11-tool-use-fundamentals)** - Advanced tool patterns

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tool Use Documentation](https://docs.anthropic.com/claude/docs/tool-use) - Official Anthropic docs
- [Chapter 40: Introduction to Agentic AI](/series/claude-php-developers/chapters/40-introduction-to-agentic-ai) - Agent fundamentals

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="41"
  label="I've built my first working agent!"
/>

---

Continue to [Chapter 42: ReAct Loop Basics](/series/claude-php-developers/chapters/42-react-loop-basics) to learn multi-step reasoning.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 41 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-41)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-41
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/08-complete-agent.php
```
