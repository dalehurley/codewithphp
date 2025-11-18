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

Tool use (also known as function calling) is one of Claude's most powerful capabilities. It allows Claude to interact with external systems, databases, APIs, and custom PHP functions - transforming Claude from a text generator into an intelligent agent that can take actions in your application.

In this chapter, you'll learn how to define tools, handle Claude's tool calls, return results back to Claude, and orchestrate complex multi-step workflows. By the end, you'll be able to build sophisticated AI agents that can query databases, call APIs, perform calculations, and much more.

## What You'll Build

By the end of this chapter, you will have created:

- A **calculator tool** demonstrating basic tool definition and execution
- A **complete tool call handler** that processes Claude's tool requests and returns results
- A **multi-tool customer support agent** with order status, refund processing, and customer lookup capabilities
- **Parallel tool call handling** for efficient multi-tool execution
- **Tool choice control** mechanisms for forcing or preventing tool usage
- A **conversational agent class** that maintains context across multiple tool interactions
- **Production-ready patterns** for error handling, iteration limits, and tool result formatting

You'll understand the complete tool use workflow from definition to execution, enabling you to build sophisticated AI agents that can interact with your PHP application's functionality.

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.4+** with strong typing familiarity
- ✓ **Understanding of callbacks** and function references
- ✓ **JSON schema knowledge** (basic understanding)
- ✓ **Completed Chapters 00-05** of this series

**Estimated Time**: 75-90 minutes (includes all 8 steps plus exercises)

## Objectives

By completing this chapter, you will:

- **Understand** how tool use transforms Claude from a text generator into an actionable AI agent
- **Define** tools using JSON schemas with proper descriptions and parameter validation
- **Handle** tool calls by processing `tool_use` content blocks and executing PHP functions
- **Implement** multi-step tool workflows with proper conversation history management
- **Process** parallel tool calls for efficient multi-tool execution
- **Control** tool usage with `tool_choice` parameters (auto, any, or specific tool)
- **Validate** tool inputs and handle errors with structured error responses
- **Secure** tool execution with permission checks and input sanitization
- **Test** tools comprehensively for both valid and invalid inputs
- **Build** production-ready tool systems with error handling, security, and testing

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

## Step 1: Defining Your First Tool (~10 min)

Tools are defined using JSON schemas that describe their purpose, parameters, and requirements:

```php
<?php
# filename: examples/01-simple-tool.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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

echo "Response type: {$response->stop_reason}\n";
print_r($response->content);
```

**Expected Output**: Claude will recognize it needs to use the calculator tool and return a `tool_use` content block.

**Why It Works**: Tools are defined as JSON schemas that Claude reads to understand what functions are available. The `name` identifies the tool, `description` tells Claude when to use it, and `input_schema` defines the required parameters. When Claude sees a request that matches a tool's purpose, it returns a `tool_use` block instead of generating text, signaling that it wants to call your PHP function.

## Step 2: Handling Tool Calls (~15 min)

When Claude wants to use a tool, it returns a `tool_use` content block. You need to execute the function and return the result:

```php
<?php
# filename: examples/02-handling-tool-calls.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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

echo "Initial response stop reason: {$response->stop_reason}\n\n";

// Process tool calls
while ($response->stop_reason === 'tool_use') {
    // Add assistant's response to conversation
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];

    // Process each tool use
    $toolResults = [];
    foreach ($response->content as $block) {
        if ($block['type'] === 'tool_use') {
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

    echo "Next response stop reason: {$response->stop_reason}\n\n";
}

// Final answer
echo "Final Answer:\n";
foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . "\n";
    }
}
```

**Why It Works**: The tool use workflow is a loop: Claude requests tools via `tool_use` blocks, you execute the corresponding PHP functions, return results as `tool_result` blocks, and Claude incorporates those results into its next response. The `stopReason` field tells you when Claude wants to use tools (`tool_use`) versus when it's ready to provide a final answer. By maintaining the conversation history in `$messages`, Claude can make multiple sequential tool calls, using results from previous calls to inform later ones.

## Step 3: Building a Multi-Tool Agent (~15 min)

Real applications use multiple tools. Here's a customer support agent with several tools:

```php
<?php
# filename: examples/03-customer-support-agent.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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

    while ($response->stop_reason === 'tool_use' && $iterations < $maxIterations) {
        $iterations++;

        // Add assistant's response to conversation
        $messages[] = [
            'role' => 'assistant',
            'content' => $response->content
        ];

        // Execute all tool calls
        $toolResults = [];
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
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
        if ($block['type'] === 'text') {
            $finalResponse .= $block['text'];
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

**Why It Works**: Real applications need multiple tools because Claude can't perform actions directly - it needs your PHP functions to interact with databases, APIs, and business logic. The `executeTool()` function acts as a router, mapping tool names to PHP functions. The iteration limit (`$maxIterations`) prevents infinite loops if Claude keeps requesting tools. System prompts guide Claude's behavior, ensuring it uses tools appropriately (e.g., asking for confirmation before processing refunds).

## Step 4: Parallel Tool Calls (~8 min)

Claude can request multiple tool calls simultaneously for efficiency:

```php
<?php
# filename: examples/04-parallel-tools.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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

if ($response->stop_reason === 'tool_use') {
    echo "Claude requested " . count(array_filter($response->content, fn($b) => $b->type === 'tool_use')) . " parallel tool calls:\n\n";

    $toolResults = [];
    foreach ($response->content as $block) {
        if ($block['type'] === 'tool_use') {
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

**Why It Works**: Claude can request multiple tools in a single response when it needs information from several sources simultaneously. This is more efficient than sequential calls because all tools execute in parallel, and Claude receives all results at once. The key is processing all `tool_use` blocks, executing them, and returning all `tool_result` blocks together before Claude's next response.

## Step 5: Tool Choice Control (~7 min)

You can control when Claude should use tools:

```php
<?php
# filename: examples/05-tool-choice.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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
echo "Stop reason: {$response->stop_reason}\n\n";

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
echo "Stop reason: {$response->stop_reason}\n\n";

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
echo "Stop reason: {$response->stop_reason}\n";
```

**Why It Works**: The `tool_choice` parameter gives you control over when tools are used. `auto` (default) lets Claude decide, `any` forces Claude to use at least one tool, and `tool` with a specific name forces that exact tool. This is useful for deterministic workflows where you know which tool should be called, or for testing specific tool behavior. When forcing a tool, Claude will use it even if it could answer without it.

## Step 6: Error Handling and Validation (~10 min)

Tool execution can fail for many reasons. Proper error handling and validation ensure your agents remain resilient:

```php
<?php
# filename: examples/07-error-handling-validation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Tool with comprehensive validation and error handling
function processPayment(string $customerId, float $amount, string $currency): array
{
    // Input validation
    if (empty($customerId)) {
        return [
            'error' => true,
            'code' => 'INVALID_CUSTOMER',
            'message' => 'Customer ID cannot be empty'
        ];
    }

    if ($amount <= 0) {
        return [
            'error' => true,
            'code' => 'INVALID_AMOUNT',
            'message' => 'Amount must be greater than 0'
        ];
    }

    if (!in_array($currency, ['USD', 'EUR', 'GBP'])) {
        return [
            'error' => true,
            'code' => 'INVALID_CURRENCY',
            'message' => 'Supported currencies: USD, EUR, GBP'
        ];
    }

    try {
        // Simulate payment processing with potential timeout
        $timeout = 5;
        $startTime = microtime(true);

        // Simulated API call with timeout check
        $processingTime = rand(1, 3);
        sleep($processingTime);

        if ((microtime(true) - $startTime) > $timeout) {
            return [
                'error' => true,
                'code' => 'TIMEOUT',
                'message' => 'Payment processing timed out'
            ];
        }

        return [
            'success' => true,
            'transaction_id' => 'TXN-' . uniqid(),
            'customer_id' => $customerId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'completed',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } catch (\Exception $e) {
        return [
            'error' => true,
            'code' => 'PROCESSING_ERROR',
            'message' => 'Payment processing failed: ' . $e->getMessage()
        ];
    }
}

// Define tool with clear parameter requirements
$tools = [
    [
        'name' => 'process_payment',
        'description' => 'Process a payment for a customer. Always validate amount and currency before calling.',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'customer_id' => [
                    'type' => 'string',
                    'description' => 'The unique customer identifier',
                    'minLength' => 1
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Payment amount (must be positive)',
                    'minimum' => 0.01
                ],
                'currency' => [
                    'type' => 'string',
                    'enum' => ['USD', 'EUR', 'GBP'],
                    'description' => 'Currency code'
                ]
            ],
            'required' => ['customer_id', 'amount', 'currency']
        ]
    ]
];

// Execute with error handling
$messages = [[
    'role' => 'user',
    'content' => 'Process a payment for customer ABC123 with amount 150.50 in USD'
]];

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'messages' => $messages
]);

// Process tool calls with error handling
if ($response->stop_reason === 'tool_use') {
    foreach ($response->content as $block) {
        if ($block['type'] === 'tool_use') {
            echo "Tool: {$block->name}\n";
            echo "Input: " . json_encode($block->input) . "\n";

            // Execute tool with error handling
            $result = processPayment(
                (string)$block->input['customer_id'],
                (float)$block->input['amount'],
                (string)$block->input['currency']
            );

            // Log the result
            if (isset($result['error']) && $result['error']) {
                echo "❌ Error: {$result['code']} - {$result['message']}\n";
            } else {
                echo "✓ Success: {$result['status']}\n";
            }

            echo "Result: " . json_encode($result) . "\n\n";
        }
    }
}
```

**Why It Works**: Input validation prevents invalid data from reaching your business logic. Error handling with specific error codes helps Claude understand what went wrong and potentially retry or adjust its approach. The schema's `minimum` and `enum` fields guide Claude toward providing valid inputs in the first place.

## Step 7: Security Best Practices (~10 min)

Secure tool execution prevents unauthorized access and data leaks:

```php
<?php
# filename: examples/08-security-best-practices.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

// Secure tool execution wrapper
class SecureToolExecutor
{
    private array $allowedTools = [];
    private array $userPermissions = [];
    private array $executionLog = [];

    public function __construct(string $userId, array $permissions = [])
    {
        $this->userPermissions = $permissions;
        $this->logExecution('INIT', ['user_id' => $userId]);
    }

    /**
     * Register allowed tools for this user
     */
    public function registerTool(string $toolName, callable $handler, array $requiredPermissions = []): void
    {
        $this->allowedTools[$toolName] = [
            'handler' => $handler,
            'required_permissions' => $requiredPermissions
        ];
    }

    /**
     * Safely execute a tool with permission checks
     */
    public function execute(string $toolName, array $input): array
    {
        // Check if tool exists
        if (!isset($this->allowedTools[$toolName])) {
            $this->logExecution('TOOL_NOT_FOUND', ['tool' => $toolName]);
            return ['error' => 'Tool not found'];
        }

        // Check permissions
        $tool = $this->allowedTools[$toolName];
        foreach ($tool['required_permissions'] as $permission) {
            if (!in_array($permission, $this->userPermissions)) {
                $this->logExecution('PERMISSION_DENIED', [
                    'tool' => $toolName,
                    'required' => $permission
                ]);
                return ['error' => 'Permission denied'];
            }
        }

        // Sanitize input
        $sanitized = $this->sanitizeInput($input);

        try {
            // Execute handler with sanitized input
            $this->logExecution('TOOL_STARTED', ['tool' => $toolName]);
            $result = call_user_func($tool['handler'], $sanitized);
            $this->logExecution('TOOL_SUCCESS', ['tool' => $toolName]);

            return $result;
        } catch (\Exception $e) {
            $this->logExecution('TOOL_ERROR', [
                'tool' => $toolName,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Tool execution failed',
                'message' => 'An error occurred during execution'
            ];
        }
    }

    /**
     * Sanitize tool inputs to prevent injection attacks
     */
    private function sanitizeInput(array $input): array
    {
        $sanitized = [];

        foreach ($input as $key => $value) {
            // Only allow alphanumeric keys
            if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $key)) {
                throw new \InvalidArgumentException("Invalid parameter name: {$key}");
            }

            if (is_string($value)) {
                // Prevent SQL injection-style attacks
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_numeric($value) || is_bool($value)) {
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                throw new \InvalidArgumentException("Unsupported parameter type: " . gettype($value));
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize string values
     */
    private function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Limit length to prevent DoS
        if (strlen($value) > 10000) {
            throw new \InvalidArgumentException('Parameter value too long');
        }

        return $value;
    }

    /**
     * Log tool execution for audit trail
     */
    private function logExecution(string $action, array $data): void
    {
        $this->executionLog[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'data' => $data
        ];
    }

    /**
     * Get execution log for auditing
     */
    public function getExecutionLog(): array
    {
        return $this->executionLog;
    }
}

// Usage example
$executor = new SecureToolExecutor('user123', ['read:customers', 'write:orders']);

// Register only the tools this user can access
$executor->registerTool('get_customer', function(array $input): array {
    return ['id' => $input['customer_id'], 'name' => 'John Doe'];
}, ['read:customers']);

$executor->registerTool('create_order', function(array $input): array {
    return ['order_id' => 'ORD-123', 'status' => 'created'];
}, ['write:orders']);

// Execute securely
$result = $executor->execute('get_customer', ['customer_id' => 'CUST-001']);
echo "Result: " . json_encode($result) . "\n";

// Attempt unauthorized access
$result = $executor->execute('delete_user', ['user_id' => 'usr123']);
echo "Unauthorized: " . json_encode($result) . "\n";

// View audit log
print_r($executor->getExecutionLog());
```

**Why It Works**: Permission checks ensure only authorized users can access specific tools. Input sanitization prevents injection attacks. Logging creates an audit trail for security monitoring. The wrapper pattern centralizes security logic, making it reusable across all tools.

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

use ClaudePhp\ClaudePhp;

class ConversationalAgent
{
    private array $messages = [];
    private array $tools;
    private ClaudePhp $client;

    public function __construct(array $tools)
    {
        $this->tools = $tools;
        $this->client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
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
        while ($response->stop_reason === 'tool_use') {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $response->content
            ];

            $toolResults = [];
            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
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
            if ($block['type'] === 'text') {
                $text .= $block['text'];
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

## Step 8: Testing Tool Use (~10 min)

Comprehensive testing ensures your tools work correctly and handle edge cases:

```php
<?php
# filename: examples/09-testing-tools.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Simple test framework for tool use
class ToolTestCase
{
    private array $testResults = [];

    /**
     * Test that tool handles valid input correctly
     */
    public function testValidInput(string $toolName, array $input, callable $tool): bool
    {
        try {
            $result = $tool(...array_values($input));

            if (isset($result['error'])) {
                $this->testResults[] = "❌ FAIL: {$toolName} - Unexpected error: {$result['message']}";
                return false;
            }

            $this->testResults[] = "✓ PASS: {$toolName} handled valid input";
            return true;
        } catch (\Exception $e) {
            $this->testResults[] = "❌ FAIL: {$toolName} - Exception: {$e->getMessage()}";
            return false;
        }
    }

    /**
     * Test that tool rejects invalid input
     */
    public function testInvalidInput(string $toolName, array $input, callable $tool): bool
    {
        try {
            $result = $tool(...array_values($input));

            if (!isset($result['error']) || !$result['error']) {
                $this->testResults[] = "❌ FAIL: {$toolName} - Should reject invalid input";
                return false;
            }

            $this->testResults[] = "✓ PASS: {$toolName} correctly rejected invalid input";
            return true;
        } catch (\Exception $e) {
            // Exception is acceptable for invalid input
            $this->testResults[] = "✓ PASS: {$toolName} threw exception for invalid input";
            return true;
        }
    }

    /**
     * Test tool output structure
     */
    public function testOutputStructure(string $toolName, array $input, callable $tool, array $expectedKeys): bool
    {
        try {
            $result = $tool(...array_values($input));

            foreach ($expectedKeys as $key) {
                if (!array_key_exists($key, $result)) {
                    $this->testResults[] = "❌ FAIL: {$toolName} - Missing key: {$key}";
                    return false;
                }
            }

            $this->testResults[] = "✓ PASS: {$toolName} has correct output structure";
            return true;
        } catch (\Exception $e) {
            $this->testResults[] = "❌ FAIL: {$toolName} - Exception: {$e->getMessage()}";
            return false;
        }
    }

    /**
     * Get all test results
     */
    public function getResults(): array
    {
        return $this->testResults;
    }

    /**
     * Print test results
     */
    public function printResults(): void
    {
        echo "\n=== Tool Test Results ===\n\n";
        foreach ($this->testResults as $result) {
            echo "{$result}\n";
        }
    }
}

// Example tool to test
function getInventory(string $productId): array
{
    if (empty($productId)) {
        return ['error' => true, 'message' => 'Product ID required'];
    }

    $inventory = [
        'PROD-001' => ['name' => 'Widget', 'stock' => 100],
        'PROD-002' => ['name' => 'Gadget', 'stock' => 50],
    ];

    if (!isset($inventory[$productId])) {
        return ['error' => true, 'message' => 'Product not found'];
    }

    return [
        'product_id' => $productId,
        'stock' => $inventory[$productId]['stock'],
        'name' => $inventory[$productId]['name']
    ];
}

// Run tests
$tester = new ToolTestCase();

// Test valid inputs
$tester->testValidInput('getInventory', ['PROD-001'], 'getInventory');
$tester->testValidInput('getInventory', ['PROD-002'], 'getInventory');

// Test invalid inputs
$tester->testInvalidInput('getInventory', [''], 'getInventory');
$tester->testInvalidInput('getInventory', ['INVALID'], 'getInventory');

// Test output structure
$tester->testOutputStructure('getInventory', ['PROD-001'], 'getInventory', ['product_id', 'stock', 'name']);

$tester->printResults();
```

**Why It Works**: Testing validates that tools handle both valid and invalid inputs correctly. Testing output structure ensures Claude receives expected data. Unit tests catch bugs early before they affect production. These patterns are reusable across all your tools.

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
- Implement client-side validation before calling the tool

**Tool execution fails with cryptic errors?**
- Return structured error responses with specific error codes
- Include context about what went wrong
- Log detailed error information for debugging
- Test your tools with various invalid inputs

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
- Validate tool output before returning it

**Permission denied errors?**
- Verify user has required permissions
- Check tool is registered with correct permissions
- Ensure permission strings match exactly
- Log permission denials for audit trail

**Tool timeout or slow execution?**
- Implement reasonable timeouts for long-running tools
- Consider async execution for heavy operations (Chapter 38)
- Cache tool results when appropriate (Chapter 18)
- Monitor tool performance in production (Chapter 37)

## Exercises

### Exercise 1: Weather Information Tool

Create a weather information tool that Claude can use to get current weather conditions.

**Requirements:**
- Define a tool called `get_weather` with city parameter
- Implement a PHP function that returns simulated weather data
- Handle tool calls and return formatted results to Claude
- Test with a query like "What's the weather like in New York?"

**Validation:** Claude should successfully call your tool and provide a natural language response about the weather.

### Exercise 2: Multi-Tool Shopping Assistant

Build a shopping assistant with three tools: `search_products`, `add_to_cart`, and `checkout`.

**Requirements:**
- Create tool definitions for all three operations
- Implement PHP functions for each tool (simulated data is fine)
- Build a conversation loop that handles multiple sequential tool calls
- Test with: "Search for laptops, add the first result to cart, then checkout"

**Validation:** The assistant should successfully execute all three tools in sequence and provide a final response.

### Exercise 3: Tool Choice Control

Create a system that forces Claude to use a specific tool based on user intent detection.

**Requirements:**
- Detect user intent (e.g., "calculate", "search", "convert")
- Dynamically set `tool_choice` based on detected intent
- Handle cases where no tool matches the intent
- Provide fallback behavior

**Validation:** The system should correctly route user requests to appropriate tools without Claude needing to decide.

<details>
<summary>Solution Hints</summary>

For Exercise 1, use a simple array-based weather database. For Exercise 2, maintain conversation state between tool calls. For Exercise 3, use keyword matching or a simple intent classifier to determine which tool to force.

</details>

## Wrap-up

Congratulations! You've completed Chapter 11 and mastered the fundamentals of tool use with Claude. Here's what you've accomplished:

- ✓ **Defined tools** using JSON schemas with proper structure and validation
- ✓ **Handled tool calls** by processing `tool_use` blocks and executing PHP functions
- ✓ **Built multi-tool agents** that can orchestrate complex workflows
- ✓ **Processed parallel tool calls** for efficient execution
- ✓ **Controlled tool usage** with `tool_choice` parameters
- ✓ **Validated inputs** with type checking and constraint validation
- ✓ **Handled errors** with structured error responses and specific error codes
- ✓ **Secured tool execution** with permission checks and input sanitization
- ✓ **Tested tools** with comprehensive test cases for valid/invalid inputs
- ✓ **Created reusable agent classes** for conversational tool interactions

You now understand how to transform Claude from a text generator into an intelligent agent that can take actions in your PHP applications. Tool use is one of Claude's most powerful features, enabling everything from database queries to API integrations to custom business logic.

**Production readiness**: You've learned not just how to build tools, but how to build them securely, reliably, and testably. You understand input validation, error handling patterns, permission systems, and testing strategies that production applications require.

In the next chapter, you'll learn to build custom tool libraries with enterprise-grade architecture, registry patterns, and integration with real databases and APIs - taking your tool use skills to the next level.

## Key Takeaways

- ✓ Tool use transforms Claude into an AI agent that can take actions
- ✓ Tools are defined with JSON schemas including name, description, and parameters
- ✓ Handle tool calls in a loop: Claude requests → Execute → Return results → Repeat
- ✓ Claude can make multiple parallel tool calls for efficiency
- ✓ Control tool usage with `tool_choice` parameter
- ✓ Good tool design requires clear names, descriptions, and type definitions
- ✓ Maintain conversation history to enable multi-turn tool interactions
- ✓ Always validate tool inputs with schemas and return structured error codes
- ✓ Implement permission checks and input sanitization for security
- ✓ Test tools thoroughly with both valid and invalid inputs
- ✓ Return clear, structured results that Claude can understand and act on

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="11"
  label="You've mastered tool use fundamentals!"
/>

---

Continue to [Chapter 12: Building Custom Tools in PHP](/series/claude-php-developers/chapters/12-building-custom-tools) to create production-ready tool libraries.

## Further Reading

- [Anthropic Tool Use Documentation](https://docs.claude.com/en/docs/agents-and-tools/tool-use) - Official guide to tool use capabilities
- [JSON Schema Specification](https://json-schema.org/) - Understanding JSON schemas for tool definitions
- [Chapter 12: Building Custom Tools](/series/claude-php-developers/chapters/12-building-custom-tools) - Next chapter on production tool architecture
- [Chapter 13: Advanced Tool Patterns](/series/claude-php-developers/chapters/13-advanced-tool-patterns) - Advanced tool use techniques
- [Anthropic API Reference - Tools](https://docs.claude.com/en/api/messages) - Complete API reference for tool parameters

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
