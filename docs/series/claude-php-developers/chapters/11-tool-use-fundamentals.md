---
title: "11: Tool Use (Function Calling) Fundamentals"
description: "Master Claude's tool use capabilities in PHP. Learn to define tools, handle tool calls, return results, and build dynamic, multi-step workflows with function calling."
series: "claude-php-developers"
chapter: 11
order: 11
difficulty: "Expert"
prerequisites:
  - "Understanding of PHP functions and callbacks"
  - "Completed Chapters 00-05"
  - "Familiarity with JSON schemas"
---

![11: Tool Use (Function Calling) Fundamentals](/images/claude-php/chapter-11-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 11</span>
</div>

# Chapter 11: Tool Use (Function Calling) Fundamentals

## Overview

Tool use (also known as function calling) is one of Claude's most powerful capabilities. It allows Claude to interact with external systems, databases, APIs, and custom PHP functions—transforming Claude from a text generator into an intelligent agent that can take actions in your application.

In this chapter, you'll learn how to define tools, handle Claude's tool calls, return results back to Claude, and orchestrate complex multi-step workflows. By the end, you'll be able to build sophisticated AI agents that can query databases, call APIs, perform calculations, and much more.

**What You'll Build**: A customer support agent that can check order status, process refunds, and update customer records using real PHP functions.

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.2+** with strong typing familiarity
- ✓ **Understanding of callbacks** and function references
- ✓ **JSON schema knowledge** (basic understanding)
- ✓ **Completed Chapters 00-05** of this series

**Estimated Time**: 45-60 minutes

## What is Tool Use?

Tool use enables Claude to:

1. **Recognize when it needs external data** or actions
2. **Request specific function calls** with parameters
3. **Receive function results** and incorporate them into responses
4. **Make multiple tool calls** in sequence or parallel
5. **Reason about which tools** to use and when

Think of it as giving Claude a "remote control" for your PHP application.

## The Tool Use Flow

```
User: "What's the weather in London?"
  ↓
Claude: "I need to call get_weather tool"
  ↓
Your PHP code: Executes get_weather("London")
  ↓
Returns: {"temp": 15, "conditions": "Rainy"}
  ↓
Claude: "It's 15°C and rainy in London."
```

## Step 1: Defining Your First Tool

Tools are defined using JSON schemas that describe their purpose, parameters, and requirements:

```php
<?php
# filename: examples/01-simple-tool.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Define a simple calculator tool
$tools = [
    [
        'name' => 'calculator',
        'description' => 'Performs basic arithmetic operations. Use this when you need to calculate numbers.',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'operation' => [
                    'type' => 'string',
                    'enum' => ['add', 'subtract', 'multiply', 'divide'],
                    'description' => 'The mathematical operation to perform'
                ],
                'a' => [
                    'type' => 'number',
                    'description' => 'The first number'
                ],
                'b' => [
                    'type' => 'number',
                    'description' => 'The second number'
                ]
            ],
            'required' => ['operation', 'a', 'b']
        ]
    ]
];

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'messages' => [[
        'role' => 'user',
        'content' => 'What is 1,234 multiplied by 567?'
    ]]
]);

echo "Response type: {$response->stopReason}\n";
print_r($response->content);
```

**Expected Output**: Claude will recognize it needs to use the calculator tool and return a `tool_use` content block.

## Step 2: Handling Tool Calls

When Claude wants to use a tool, it returns a `tool_use` content block. You need to execute the function and return the result:

```php
<?php
# filename: examples/02-handling-tool-calls.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Actual implementation of calculator
function calculator(string $operation, float $a, float $b): float
{
    return match ($operation) {
        'add' => $a + $b,
        'subtract' => $a - $b,
        'multiply' => $a * $b,
        'divide' => $b != 0 ? $a / $b : throw new \DivisionByZeroError(),
    };
}

$tools = [
    [
        'name' => 'calculator',
        'description' => 'Performs basic arithmetic operations.',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'operation' => [
                    'type' => 'string',
                    'enum' => ['add', 'subtract', 'multiply', 'divide']
                ],
                'a' => ['type' => 'number'],
                'b' => ['type' => 'number']
            ],
            'required' => ['operation', 'a', 'b']
        ]
    ]
];

$messages = [[
    'role' => 'user',
    'content' => 'Calculate 234 * 567, then add 100 to the result.'
]];

// Initial request
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'messages' => $messages
]);

echo "Initial response stop reason: {$response->stopReason}\n\n";

// Process tool calls
while ($response->stopReason === 'tool_use') {
    // Add assistant's response to conversation
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];

    // Process each tool use
    $toolResults = [];
    foreach ($response->content as $block) {
        if ($block->type === 'tool_use') {
            echo "Claude wants to use tool: {$block->name}\n";
            echo "With input: " . json_encode($block->input) . "\n";

            // Execute the tool
            $result = calculator(
                $block->input['operation'],
                $block->input['a'],
                $block->input['b']
            );

            echo "Result: {$result}\n\n";

            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $block->id,
                'content' => (string)$result
            ];
        }
    }

    // Return results to Claude
    $messages[] = [
        'role' => 'user',
        'content' => $toolResults
    ];

    // Continue conversation
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'tools' => $tools,
        'messages' => $messages
    ]);

    echo "Next response stop reason: {$response->stopReason}\n\n";
}

// Final answer
echo "Final Answer:\n";
foreach ($response->content as $block) {
    if ($block->type === 'text') {
        echo $block->text . "\n";
    }
}
```

## Step 3: Building a Multi-Tool Agent

Real applications use multiple tools. Here's a customer support agent with several tools:

```php
<?php
# filename: examples/03-customer-support-agent.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Simulated database functions
function getOrderStatus(string $orderId): array
{
    // In real app, query database
    $orders = [
        'ORD-123' => [
            'id' => 'ORD-123',
            'status' => 'shipped',
            'tracking' => 'TRK-456789',
            'items' => ['Product A', 'Product B'],
            'total' => 149.99
        ],
        'ORD-456' => [
            'id' => 'ORD-456',
            'status' => 'processing',
            'tracking' => null,
            'items' => ['Product C'],
            'total' => 79.99
        ]
    ];

    return $orders[$orderId] ?? ['error' => 'Order not found'];
}

function processRefund(string $orderId, float $amount, string $reason): array
{
    // In real app, process refund through payment gateway
    return [
        'success' => true,
        'refund_id' => 'REF-' . uniqid(),
        'amount' => $amount,
        'order_id' => $orderId,
        'reason' => $reason,
        'processed_at' => date('Y-m-d H:i:s')
    ];
}

function getCustomerInfo(string $email): array
{
    // In real app, query customer database
    $customers = [
        'john@example.com' => [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'tier' => 'gold',
            'orders_count' => 24,
            'lifetime_value' => 2499.99
        ]
    ];

    return $customers[$email] ?? ['error' => 'Customer not found'];
}

// Define all available tools
$tools = [
    [
        'name' => 'get_order_status',
        'description' => 'Retrieves the current status and details of a customer order by order ID.',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'order_id' => [
                    'type' => 'string',
                    'description' => 'The order ID (format: ORD-XXX)'
                ]
            ],
            'required' => ['order_id']
        ]
    ],
    [
        'name' => 'process_refund',
        'description' => 'Processes a refund for a customer order. Only use this after confirming the customer wants a refund.',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'order_id' => [
                    'type' => 'string',
                    'description' => 'The order ID to refund'
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'The refund amount in USD'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'The reason for the refund'
                ]
            ],
            'required' => ['order_id', 'amount', 'reason']
        ]
    ],
    [
        'name' => 'get_customer_info',
        'description' => 'Retrieves customer information and history by email address.',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'description' => 'Customer email address'
                ]
            ],
            'required' => ['email']
        ]
    ]
];

// Execute a tool call
function executeTool(string $toolName, array $input): array
{
    return match ($toolName) {
        'get_order_status' => getOrderStatus($input['order_id']),
        'process_refund' => processRefund($input['order_id'], $input['amount'], $input['reason']),
        'get_customer_info' => getCustomerInfo($input['email']),
        default => ['error' => 'Unknown tool']
    };
}

// Agent conversation loop
function runSupportAgent(string $userMessage): string
{
    global $client, $tools;

    $messages = [[
        'role' => 'user',
        'content' => $userMessage
    ]];

    $systemPrompt = <<<SYSTEM
You are a helpful customer support agent for an e-commerce store.
You have access to tools to check order status, process refunds, and look up customer information.

Guidelines:
- Always be polite and professional
- Verify order details before processing refunds
- Ask for confirmation before taking actions like refunds
- Provide tracking numbers when available
SYSTEM;

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 2048,
        'system' => $systemPrompt,
        'tools' => $tools,
        'messages' => $messages
    ]);

    $iterations = 0;
    $maxIterations = 10; // Prevent infinite loops

    while ($response->stopReason === 'tool_use' && $iterations < $maxIterations) {
        $iterations++;

        // Add assistant's response to conversation
        $messages[] = [
            'role' => 'assistant',
            'content' => $response->content
        ];

        // Execute all tool calls
        $toolResults = [];
        foreach ($response->content as $block) {
            if ($block->type === 'tool_use') {
                echo "[TOOL] {$block->name}(" . json_encode($block->input) . ")\n";

                $result = executeTool($block->name, (array)$block->input);

                echo "[RESULT] " . json_encode($result) . "\n\n";

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block->id,
                    'content' => json_encode($result)
                ];
            }
        }

        // Return results to Claude
        $messages[] = [
            'role' => 'user',
            'content' => $toolResults
        ];

        // Continue conversation
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'system' => $systemPrompt,
            'tools' => $tools,
            'messages' => $messages
        ]);
    }

    // Extract final text response
    $finalResponse = '';
    foreach ($response->content as $block) {
        if ($block->type === 'text') {
            $finalResponse .= $block->text;
        }
    }

    return $finalResponse;
}

// Example conversations
echo "=== Conversation 1: Order Status ===\n\n";
$response = runSupportAgent("Hi, can you check the status of order ORD-123?");
echo "Agent: {$response}\n\n";

echo "=== Conversation 2: Customer Lookup ===\n\n";
$response = runSupportAgent("What's the account status for john@example.com?");
echo "Agent: {$response}\n\n";
```

## Step 4: Parallel Tool Calls

Claude can request multiple tool calls simultaneously for efficiency:

```php
<?php
# filename: examples/04-parallel-tools.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Define tools for weather and news
$tools = [
    [
        'name' => 'get_weather',
        'description' => 'Gets current weather for a city',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'city' => ['type' => 'string', 'description' => 'City name']
            ],
            'required' => ['city']
        ]
    ],
    [
        'name' => 'get_news',
        'description' => 'Gets latest news for a topic',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'topic' => ['type' => 'string', 'description' => 'News topic']
            ],
            'required' => ['topic']
        ]
    ]
];

function getWeather(string $city): array
{
    // Simulated weather API
    return [
        'city' => $city,
        'temperature' => rand(10, 30),
        'conditions' => ['Sunny', 'Cloudy', 'Rainy'][rand(0, 2)],
        'humidity' => rand(30, 90)
    ];
}

function getNews(string $topic): array
{
    // Simulated news API
    return [
        'topic' => $topic,
        'articles' => [
            ['title' => "Breaking: {$topic} update", 'source' => 'News Corp'],
            ['title' => "{$topic} analysis", 'source' => 'Tech Daily']
        ]
    ];
}

$messages = [[
    'role' => 'user',
    'content' => 'What\'s the weather in London and Paris? Also get me tech news.'
]];

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'messages' => $messages
]);

if ($response->stopReason === 'tool_use') {
    echo "Claude requested " . count(array_filter($response->content, fn($b) => $b->type === 'tool_use')) . " parallel tool calls:\n\n";

    $toolResults = [];
    foreach ($response->content as $block) {
        if ($block->type === 'tool_use') {
            echo "Tool: {$block->name}\n";
            echo "Input: " . json_encode($block->input) . "\n";

            $result = match ($block->name) {
                'get_weather' => getWeather($block->input['city']),
                'get_news' => getNews($block->input['topic']),
                default => ['error' => 'Unknown tool']
            };

            echo "Result: " . json_encode($result) . "\n\n";

            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $block->id,
                'content' => json_encode($result)
            ];
        }
    }

    // Return all results at once
    $messages[] = ['role' => 'assistant', 'content' => $response->content];
    $messages[] = ['role' => 'user', 'content' => $toolResults];

    // Get final response
    $finalResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'tools' => $tools,
        'messages' => $messages
    ]);

    echo "Final Response:\n";
    echo $finalResponse->content[0]->text . "\n";
}
```

## Step 5: Tool Choice Control

You can control when Claude should use tools:

```php
<?php
# filename: examples/05-tool-choice.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$tools = [
    [
        'name' => 'search_database',
        'description' => 'Searches the product database',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string']
            ],
            'required' => ['query']
        ]
    ]
];

// Example 1: Force tool use
echo "=== Force Tool Use ===\n";
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'tool_choice' => ['type' => 'any'], // Force Claude to use a tool
    'messages' => [[
        'role' => 'user',
        'content' => 'Find products related to PHP'
    ]]
]);
echo "Stop reason: {$response->stopReason}\n\n";

// Example 2: Force specific tool
echo "=== Force Specific Tool ===\n";
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'tool_choice' => [
        'type' => 'tool',
        'name' => 'search_database'
    ],
    'messages' => [[
        'role' => 'user',
        'content' => 'Laravel'
    ]]
]);
echo "Stop reason: {$response->stopReason}\n\n";

// Example 3: Auto (default - Claude decides)
echo "=== Auto Mode ===\n";
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'tool_choice' => ['type' => 'auto'], // Claude decides
    'messages' => [[
        'role' => 'user',
        'content' => 'Hello, how are you?'
    ]]
]);
echo "Stop reason: {$response->stopReason}\n";
```

## Best Practices for Tool Design

### 1. Clear, Descriptive Names

```php
// ❌ Bad
'name' => 'get_data'

// ✅ Good
'name' => 'get_customer_order_history'
```

### 2. Detailed Descriptions

```php
// ❌ Bad
'description' => 'Gets orders'

// ✅ Good
'description' => 'Retrieves a customer\'s complete order history including status, dates, and totals. Use this when the customer asks about their past purchases or order details.'
```

### 3. Strong Type Definitions

```php
'input_schema' => [
    'type' => 'object',
    'properties' => [
        'date' => [
            'type' => 'string',
            'description' => 'Date in ISO 8601 format (YYYY-MM-DD)',
            'pattern' => '^\d{4}-\d{2}-\d{2}$'
        ],
        'amount' => [
            'type' => 'number',
            'minimum' => 0,
            'description' => 'Amount in USD (must be positive)'
        ]
    ],
    'required' => ['date', 'amount']
]
```

### 4. Error Handling in Tools

```php
function executeTool(string $toolName, array $input): array
{
    try {
        return match ($toolName) {
            'get_order' => getOrder($input['order_id']),
            default => throw new \Exception("Unknown tool: {$toolName}")
        };
    } catch (\Exception $e) {
        return [
            'error' => true,
            'message' => $e->getMessage(),
            'type' => get_class($e)
        ];
    }
}
```

## Advanced Pattern: Conversational Tool Use

Allow back-and-forth conversations while using tools:

```php
<?php
# filename: examples/06-conversational-tools.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

class ConversationalAgent
{
    private array $messages = [];
    private array $tools;
    private Anthropic $client;

    public function __construct(array $tools)
    {
        $this->tools = $tools;
        $this->client = Anthropic::factory()
            ->withApiKey(getenv('ANTHROPIC_API_KEY'))
            ->make();
    }

    public function chat(string $userMessage): string
    {
        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'tools' => $this->tools,
            'messages' => $this->messages
        ]);

        // Process tool uses
        while ($response->stopReason === 'tool_use') {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $response->content
            ];

            $toolResults = [];
            foreach ($response->content as $block) {
                if ($block->type === 'tool_use') {
                    $result = $this->executeTool($block->name, (array)$block->input);
                    $toolResults[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $block->id,
                        'content' => json_encode($result)
                    ];
                }
            }

            $this->messages[] = [
                'role' => 'user',
                'content' => $toolResults
            ];

            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 2048,
                'tools' => $this->tools,
                'messages' => $this->messages
            ]);
        }

        // Add assistant response to history
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response->content
        ];

        // Extract text
        $text = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        return $text;
    }

    private function executeTool(string $name, array $input): array
    {
        // Implement your tool execution logic
        return ['result' => 'Tool executed'];
    }

    public function getHistory(): array
    {
        return $this->messages;
    }
}

// Usage
$tools = [
    [
        'name' => 'check_inventory',
        'description' => 'Checks product inventory',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'product_id' => ['type' => 'string']
            ],
            'required' => ['product_id']
        ]
    ]
];

$agent = new ConversationalAgent($tools);

echo "Bot: " . $agent->chat("Hi, do you have product ABC in stock?") . "\n\n";
echo "Bot: " . $agent->chat("What about product XYZ?") . "\n\n";
echo "Bot: " . $agent->chat("Great, I'll take both.") . "\n";
```

## Troubleshooting

**Tool not being called?**
- Ensure your tool description clearly explains when to use it
- Check that the user's request matches the tool's purpose
- Try using `tool_choice: ['type' => 'any']` to force tool use
- Verify your tool schema is valid JSON

**Invalid parameters passed to tool?**
- Add detailed descriptions to each parameter
- Use enums for fields with limited options
- Include examples in descriptions
- Add validation patterns for strings

**Infinite tool loop?**
- Implement iteration limits in your tool processing loop
- Return clear, structured results from tools
- Ensure tool results actually answer Claude's query
- Check for errors in tool execution

**Tool results not understood?**
- Return results as structured JSON
- Include relevant context in results
- Avoid returning too much data (summarize when needed)
- Use descriptive field names in result objects

## Key Takeaways

- ✓ Tool use transforms Claude into an AI agent that can take actions
- ✓ Tools are defined with JSON schemas including name, description, and parameters
- ✓ Handle tool calls in a loop: Claude requests → Execute → Return results → Repeat
- ✓ Claude can make multiple parallel tool calls for efficiency
- ✓ Control tool usage with `tool_choice` parameter
- ✓ Good tool design requires clear names, descriptions, and type definitions
- ✓ Maintain conversation history to enable multi-turn tool interactions

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="11"
  label="You've mastered tool use fundamentals!"
/>

---

Continue to [Chapter 12: Building Custom Tools in PHP](/series/claude-php-developers/chapters/12-building-custom-tools) to create production-ready tool libraries.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 11 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-11)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-11
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-simple-tool.php
```
