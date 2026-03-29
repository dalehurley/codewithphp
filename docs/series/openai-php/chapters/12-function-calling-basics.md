---
title: "12: Function Calling Basics"
description: "Connect AI to your application logic with function calling, enabling dynamic actions based on user input"
series: "openai-php"
chapter: 12
order: 12
difficulty: "Advanced"
prerequisites:
  - "/series/openai-php/chapters/11-json-mode-structured-outputs"
  - "Understanding of PHP functions and callbacks"
  - "JSON schema knowledge"
---

![Function Calling Basics](/images/openai-php/chapter-12-function-calling-basics-hero-full.webp)

[Home](/series/openai-php) > [Chapter 11](/series/openai-php/chapters/11-json-mode-structured-outputs) > Function Calling Basics

# Chapter 12: Function Calling Basics

<span class="difficulty-badge difficulty-advanced">Advanced</span>
<span class="time-badge">75-90 min</span>

**⭐ Priority Chapter**: This chapter covers essential concepts used throughout the series.

## Overview

Function calling is one of OpenAI's most powerful features, allowing AI models to intelligently call your PHP functions based on user requests. Instead of just generating text, the AI can now take real actions—fetching data from databases, calling external APIs, performing calculations, or executing any custom logic in your application.

This transforms the AI from a passive text generator into an active agent that can interact with your entire application stack. When a user asks "What's the weather in Paris?", the AI doesn't hallucinate an answer—it calls your `getWeather()` function with the location parameter and uses the real data to respond.

In this chapter, you'll learn how to define functions for the AI to use, process function call requests, execute the functions safely, and return results back to the AI. We'll build practical examples including weather lookups, database queries, and API integrations. By the end, you'll be able to give the AI access to your entire application's capabilities while maintaining full control and security.

## What You'll Learn

- 🎯 **Function Calling Fundamentals** - How function calling works and when to use it
- 🛠️ **Function Schemas** - Defining functions the AI can call with proper parameter descriptions
- 💼 **Request Processing** - Detecting and handling function call requests from the AI
- ⚡ **Function Execution** - Safely executing PHP functions and handling results
- 🔒 **Security** - Preventing unauthorized access and validating inputs
- 📊 **Multi-Step Conversations** - Chaining function calls for complex workflows
- 🏗️ **Design Patterns** - Common patterns for function calling architectures

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed Chapters 01-11, especially JSON Mode (Chapter 11)
- ✅ Understanding of PHP functions, closures, and callbacks
- ✅ Familiarity with JSON and JSON Schema
- ✅ Knowledge of error handling in PHP
- ✅ Understanding of the Chat Completions API

---

## Quick Checklist

By the end of this chapter, you'll have:

- [ ] Understood how function calling works
- [ ] Defined function schemas for AI use
- [ ] Implemented a basic function calling system
- [ ] Created a weather lookup function
- [ ] Built a database query function
- [ ] Handled function call errors gracefully
- [ ] Implemented security validation
- [ ] Built a multi-step conversation with functions

---

## What is Function Calling?

### The Problem It Solves

Without function calling, if a user asks "What's the weather in Paris?", the AI can only:
1. Make up an answer (hallucinate)
2. Say it doesn't have access to current weather
3. Tell the user to check a weather website

This limits the AI's usefulness for real-world applications.

### The Solution: Function Calling

With function calling, here's what happens:

1. **You define** what functions the AI can call
2. **User asks** a question requiring real data
3. **AI decides** which function to call and what parameters to use
4. **You execute** the function in your PHP code
5. **You return** the result to the AI
6. **AI generates** a natural language response using the real data

**Example Flow:**

```
User: "What's the weather in Paris?"
  ↓
AI: "I need to call getWeather(location: 'Paris')"
  ↓
Your code: Calls weather API, gets 72°F, sunny
  ↓
AI: "The weather in Paris is currently 72°F and sunny!"
```

### When to Use Function Calling

✅ **Perfect For:**
- Fetching real-time data (weather, stocks, news)
- Database queries and CRUD operations
- Calling external APIs
- Performing calculations
- File operations
- User authentication/authorization checks
- Multi-step workflows

❌ **Not Needed For:**
- Simple conversations
- General knowledge questions
- Creative writing tasks
- Data already in the prompt

---

## How Function Calling Works

### The Function Calling Lifecycle

```php
┌─────────────────────────────────────────────────────────────┐
│ 1. Define Functions                                         │
│    You tell AI what functions exist and their parameters    │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. User Message                                             │
│    User asks a question: "What's the weather in Paris?"     │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. AI Decides                                               │
│    AI determines it needs to call getWeather("Paris")       │
│    Returns a function_call instead of text                  │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. Execute Function                                         │
│    Your code calls the actual PHP function                  │
│    Returns: {"temperature": 72, "condition": "sunny"}       │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. Return Result                                            │
│    Add function result to conversation                      │
│    Make another API call with the result                    │
└────────────────────┬────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. AI Responds                                              │
│    AI uses the real data to generate natural response       │
│    "The weather in Paris is 72°F and sunny!"                │
└─────────────────────────────────────────────────────────────┘
```

---

## Defining Function Schemas

Function schemas tell the AI what functions are available and how to use them.

### Basic Function Schema Structure

```php
$functions = [
    [
        'name' => 'get_weather',
        'description' => 'Get the current weather for a location',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The city name, e.g. "Paris" or "New York, NY"',
                ],
                'unit' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                    'description' => 'Temperature unit',
                ],
            ],
            'required' => ['location'],
        ],
    ],
];
```

### Schema Components Explained

**`name`** (required)
- Function identifier
- Must match your actual PHP function name
- Use snake_case convention

**`description`** (required)
- Tells the AI when to use this function
- Be specific and clear
- Include examples if helpful

**`parameters`** (required)
- JSON Schema object
- Describes expected parameters
- Type validation
- Required vs optional fields

### Good vs Bad Descriptions

**❌ Bad Description:**
```php
'description' => 'Gets weather'
```

**✅ Good Description:**
```php
'description' => 'Get current weather conditions for a specific location. Returns temperature, condition (sunny/cloudy/rainy), humidity, and wind speed. Use this when user asks about weather, temperature, or current conditions in a city.'
```

**Why it's better:**
- Explains what data is returned
- Specifies when to use it
- Provides context about capabilities

---

## Basic Implementation

Let's build a complete function calling system step by step.

### Step 1: Define Functions

Create `src/12-basic-function-calling.php`:

```php
<?php

declare(strict_types=1);

/**
 * Chapter 12: Basic Function Calling
 * Demonstrates core function calling concepts
 */

require_once __DIR__ . '/../vendor/autoload.php';

use OpenAI;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

// Define available functions
$availableFunctions = [
    [
        'name' => 'get_current_weather',
        'description' => 'Get the current weather in a given location',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The city and state, e.g. San Francisco, CA',
                ],
                'unit' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                ],
            ],
            'required' => ['location'],
        ],
    ],
];

echo "Function Calling Example\n";
echo str_repeat('=', 70) . "\n\n";

// User message
$messages = [
    ['role' => 'user', 'content' => 'What\'s the weather like in Boston?']
];

// First API call with functions
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'functions' => $availableFunctions,
    'function_call' => 'auto', // Let AI decide when to call functions
]);

$message = $response->choices[0]->message;

// Check if AI wants to call a function
if (isset($message->function_call)) {
    echo "🤖 AI wants to call a function!\n";
    echo "Function: {$message->function_call->name}\n";
    echo "Arguments: {$message->function_call->arguments}\n\n";

    // Add AI's function call to conversation
    $messages[] = [
        'role' => 'assistant',
        'content' => null,
        'function_call' => [
            'name' => $message->function_call->name,
            'arguments' => $message->function_call->arguments,
        ],
    ];

    // Execute the function
    $functionName = $message->function_call->name;
    $functionArgs = json_decode($message->function_call->arguments, true);

    echo "⚙️  Executing function...\n";
    $functionResult = call_user_func($functionName, ...$functionArgs);
    echo "Result: " . json_encode($functionResult) . "\n\n";

    // Add function result to conversation
    $messages[] = [
        'role' => 'function',
        'name' => $functionName,
        'content' => json_encode($functionResult),
    ];

    // Second API call with function result
    $secondResponse = $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
    ]);

    echo "💬 AI Response:\n";
    echo $secondResponse->choices[0]->message->content . "\n";

} else {
    // No function call, just regular response
    echo "💬 AI Response:\n";
    echo $message->content . "\n";
}

/**
 * Actual function implementation
 */
function get_current_weather(string $location, string $unit = 'fahrenheit'): array
{
    // In production, this would call a real weather API
    // For demo, return mock data
    return [
        'location' => $location,
        'temperature' => $unit === 'celsius' ? 22 : 72,
        'unit' => $unit,
        'condition' => 'sunny',
        'humidity' => 65,
    ];
}
```

### Step 2: Run the Example

```bash
php src/12-basic-function-calling.php
```

**Output:**

```
Function Calling Example
======================================================================

🤖 AI wants to call a function!
Function: get_current_weather
Arguments: {"location":"Boston","unit":"fahrenheit"}

⚙️  Executing function...
Result: {"location":"Boston","temperature":72,"unit":"fahrenheit","condition":"sunny","humidity":65}

💬 AI Response:
The weather in Boston is currently sunny with a temperature of 72°F and humidity at 65%.
```

---

## Function Execution Patterns

### Pattern 1: Function Registry

Create a registry to manage multiple functions:

```php
class FunctionRegistry
{
    private array $functions = [];
    private array $schemas = [];

    public function register(string $name, callable $callable, array $schema): void
    {
        $this->functions[$name] = $callable;
        $this->schemas[] = array_merge(['name' => $name], $schema);
    }

    public function execute(string $name, array $args): mixed
    {
        if (!isset($this->functions[$name])) {
            throw new \Exception("Function {$name} not found");
        }

        return call_user_func_array($this->functions[$name], $args);
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function has(string $name): bool
    {
        return isset($this->functions[$name]);
    }
}

// Usage
$registry = new FunctionRegistry();

$registry->register(
    'get_weather',
    fn($location) => ['temp' => 72, 'condition' => 'sunny'],
    [
        'description' => 'Get current weather',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'location' => ['type' => 'string']
            ],
            'required' => ['location']
        ]
    ]
);

$registry->register(
    'get_time',
    fn($timezone) => date('Y-m-d H:i:s', time()),
    [
        'description' => 'Get current time',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'timezone' => ['type' => 'string']
            ],
        ]
    ]
);

// Use in API call
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'functions' => $registry->getSchemas(),
]);
```

### Pattern 2: Class-Based Functions

Organize related functions in classes:

```php
class WeatherFunctions
{
    private WeatherAPI $api;

    public function __construct(WeatherAPI $api)
    {
        $this->api = $api;
    }

    public function getCurrentWeather(string $location, string $unit = 'fahrenheit'): array
    {
        return $this->api->getCurrentConditions($location, $unit);
    }

    public function getForecast(string $location, int $days = 3): array
    {
        return $this->api->getForecast($location, $days);
    }

    public static function getSchemas(): array
    {
        return [
            [
                'name' => 'getCurrentWeather',
                'description' => 'Get current weather conditions',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => ['type' => 'string'],
                        'unit' => ['type' => 'string', 'enum' => ['celsius', 'fahrenheit']],
                    ],
                    'required' => ['location'],
                ],
            ],
            [
                'name' => 'getForecast',
                'description' => 'Get weather forecast for upcoming days',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => ['type' => 'string'],
                        'days' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
                    ],
                    'required' => ['location'],
                ],
            ],
        ];
    }
}
```

---

## Real-World Examples

### Example 1: Database Query Function

```php
class DatabaseFunctions
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getProduct(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return ['error' => 'Product not found'];
        }

        return $product;
    }

    public function searchProducts(string $query, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, price, description
             FROM products
             WHERE name LIKE :query OR description LIKE :query
             LIMIT :limit'
        );

        $stmt->execute([
            'query' => "%{$query}%",
            'limit' => $limit,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSchemas(): array
    {
        return [
            [
                'name' => 'getProduct',
                'description' => 'Get detailed information about a specific product by its ID',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'productId' => [
                            'type' => 'integer',
                            'description' => 'The unique product ID',
                        ],
                    ],
                    'required' => ['productId'],
                ],
            ],
            [
                'name' => 'searchProducts',
                'description' => 'Search for products by name or description. Returns matching products with their details.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Search term to look for in product names and descriptions',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of results to return',
                            'default' => 10,
                        ],
                    ],
                    'required' => ['query'],
                ],
            ],
        ];
    }
}

// Usage
$dbFunctions = new DatabaseFunctions($pdo);
$registry->register('getProduct', [$dbFunctions, 'getProduct'], ...);
$registry->register('searchProducts', [$dbFunctions, 'searchProducts'], ...);
```

**Conversation Example:**

```
User: "Do you have any wireless headphones?"
AI: *calls searchProducts("wireless headphones")*
System: Returns 5 matching products
AI: "Yes! I found 5 wireless headphones. The top options are the Sony WH-1000XM5
     at $399.99 and the Bose QuietComfort at $349.99..."
```

### Example 2: External API Integration

```php
class CRMFunctions
{
    private HttpClient $http;
    private string $apiKey;

    public function getCustomer(string $email): array
    {
        $response = $this->http->get("https://api.crm.com/customers", [
            'query' => ['email' => $email],
            'headers' => ['Authorization' => "Bearer {$this->apiKey}"],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getOrders(string $customerId, int $limit = 10): array
    {
        $response = $this->http->get("https://api.crm.com/orders", [
            'query' => [
                'customer_id' => $customerId,
                'limit' => $limit,
            ],
            'headers' => ['Authorization' => "Bearer {$this->apiKey}"],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public static function getSchemas(): array
    {
        return [
            [
                'name' => 'getCustomer',
                'description' => 'Retrieve customer information from CRM by email address',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'description' => 'Customer email address',
                        ],
                    ],
                    'required' => ['email'],
                ],
            ],
            [
                'name' => 'getOrders',
                'description' => 'Get recent orders for a customer',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'customerId' => [
                            'type' => 'string',
                            'description' => 'Unique customer ID from CRM',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 50,
                        ],
                    ],
                    'required' => ['customerId'],
                ],
            ],
        ];
    }
}
```

### Example 3: Calculation Functions

```php
class MathFunctions
{
    public function calculate(string $expression): float
    {
        // Safely evaluate mathematical expressions
        // WARNING: In production, use a proper math parser, not eval()!
        $allowed = '/^[0-9+\-*\/().\s]+$/';

        if (!preg_match($allowed, $expression)) {
            throw new \InvalidArgumentException('Invalid expression');
        }

        // Use a safe math evaluation library in production
        return eval("return {$expression};");
    }

    public function convertCurrency(
        float $amount,
        string $from,
        string $to
    ): array {
        // Call currency API
        $rate = $this->getExchangeRate($from, $to);

        return [
            'original_amount' => $amount,
            'original_currency' => $from,
            'converted_amount' => $amount * $rate,
            'target_currency' => $to,
            'exchange_rate' => $rate,
        ];
    }

    private function getExchangeRate(string $from, string $to): float
    {
        // In production, call a real exchange rate API
        return 1.18; // Mock rate
    }

    public static function getSchemas(): array
    {
        return [
            [
                'name' => 'calculate',
                'description' => 'Perform mathematical calculations. Supports +, -, *, /, parentheses.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'expression' => [
                            'type' => 'string',
                            'description' => 'Mathematical expression to evaluate, e.g. "2 + 2" or "(10 * 5) / 2"',
                        ],
                    ],
                    'required' => ['expression'],
                ],
            ],
            [
                'name' => 'convertCurrency',
                'description' => 'Convert amount from one currency to another using current exchange rates',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'amount' => [
                            'type' => 'number',
                            'description' => 'Amount to convert',
                        ],
                        'from' => [
                            'type' => 'string',
                            'description' => 'Source currency code (USD, EUR, GBP, etc.)',
                        ],
                        'to' => [
                            'type' => 'string',
                            'description' => 'Target currency code',
                        ],
                    ],
                    'required' => ['amount', 'from', 'to'],
                ],
            ],
        ];
    }
}
```

---

## Error Handling

### Handling Function Call Errors

```php
class FunctionExecutor
{
    private FunctionRegistry $registry;
    private LoggerInterface $logger;

    public function execute(string $name, array $args): array
    {
        try {
            // Validate function exists
            if (!$this->registry->has($name)) {
                return [
                    'error' => true,
                    'message' => "Function '{$name}' not found",
                ];
            }

            // Validate arguments
            $this->validateArguments($name, $args);

            // Execute function
            $result = $this->registry->execute($name, $args);

            return [
                'success' => true,
                'data' => $result,
            ];

        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Invalid arguments for {$name}", [
                'args' => $args,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'message' => 'Invalid arguments: ' . $e->getMessage(),
            ];

        } catch (\Exception $e) {
            $this->logger->error("Function execution failed: {$name}", [
                'args' => $args,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => true,
                'message' => 'Function execution failed: ' . $e->getMessage(),
            ];
        }
    }

    private function validateArguments(string $name, array $args): void
    {
        // Get function schema
        $schema = $this->registry->getSchema($name);

        // Validate required parameters
        foreach ($schema['parameters']['required'] ?? [] as $required) {
            if (!isset($args[$required])) {
                throw new \InvalidArgumentException(
                    "Missing required parameter: {$required}"
                );
            }
        }

        // Validate parameter types
        foreach ($args as $key => $value) {
            $expectedType = $schema['parameters']['properties'][$key]['type'] ?? null;
            if ($expectedType && !$this->validateType($value, $expectedType)) {
                throw new \InvalidArgumentException(
                    "Parameter '{$key}' must be of type {$expectedType}"
                );
            }
        }
    }

    private function validateType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_numeric($value),
            'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value) || is_array($value),
            default => true,
        };
    }
}
```

### Graceful Error Responses

```php
// When function fails, inform the AI
if (isset($functionResult['error'])) {
    $messages[] = [
        'role' => 'function',
        'name' => $functionName,
        'content' => json_encode([
            'error' => $functionResult['message'],
            'suggestion' => 'Please try a different approach or ask for clarification.',
        ]),
    ];
} else {
    $messages[] = [
        'role' => 'function',
        'name' => $functionName,
        'content' => json_encode($functionResult['data']),
    ];
}
```

---

## Security Considerations

### 1. Input Validation

**Always validate function arguments:**

```php
class SecureFunctionExecutor
{
    public function execute(string $name, array $args): mixed
    {
        // Whitelist allowed functions
        $allowedFunctions = ['get_weather', 'search_products', 'get_time'];
        if (!in_array($name, $allowedFunctions)) {
            throw new SecurityException("Function not allowed: {$name}");
        }

        // Sanitize inputs
        foreach ($args as $key => $value) {
            $args[$key] = $this->sanitize($value);
        }

        // Rate limiting
        if (!$this->rateLimiter->allow($name)) {
            throw new RateLimitException("Too many calls to {$name}");
        }

        return $this->registry->execute($name, $args);
    }

    private function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            // Remove potential SQL injection attempts
            $value = strip_tags($value);
            // Limit length
            $value = substr($value, 0, 1000);
        }

        return $value;
    }
}
```

### 2. Permission Checks

```php
class PermissionAwareFunctionExecutor
{
    public function execute(
        string $name,
        array $args,
        User $user
    ): mixed {
        // Check if user has permission for this function
        if (!$this->permissions->can($user, "function.{$name}")) {
            throw new UnauthorizedException(
                "User not authorized to call {$name}"
            );
        }

        // Check data access permissions
        if ($name === 'getCustomer' && !$this->canAccessCustomer($user, $args['email'])) {
            throw new UnauthorizedException(
                "Cannot access customer data"
            );
        }

        return $this->registry->execute($name, $args);
    }

    private function canAccessCustomer(User $user, string $email): bool
    {
        // Implement your access control logic
        return $user->hasRole('admin') || $user->email === $email;
    }
}
```

### 3. Dangerous Functions to Avoid

**❌ NEVER expose these directly:**

```php
// DON'T DO THIS!
$dangerousFunctions = [
    'exec',
    'system',
    'passthru',
    'shell_exec',
    'eval',
    'file_get_contents', // Unless heavily restricted
    'unlink',
    'rmdir',
];
```

**✅ Instead, wrap them safely:**

```php
// Safe wrapper
public function readFile(string $filename): string
{
    // Whitelist allowed paths
    $allowedDir = '/var/www/app/uploads/';
    $realPath = realpath($allowedDir . $filename);

    // Prevent directory traversal
    if (!$realPath || !str_starts_with($realPath, $allowedDir)) {
        throw new SecurityException('Invalid file path');
    }

    return file_get_contents($realPath);
}
```

---

## Multi-Step Conversations

Sometimes the AI needs to call multiple functions:

```php
class MultiStepConversation
{
    public function chat(string $userMessage): string
    {
        $messages = [
            ['role' => 'user', 'content' => $userMessage]
        ];

        $maxIterations = 5; // Prevent infinite loops
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'functions' => $this->registry->getSchemas(),
            ]);

            $message = $response->choices[0]->message;

            // If no function call, we're done
            if (!isset($message->function_call)) {
                return $message->content;
            }

            // Add assistant message with function call
            $messages[] = [
                'role' => 'assistant',
                'content' => null,
                'function_call' => [
                    'name' => $message->function_call->name,
                    'arguments' => $message->function_call->arguments,
                ],
            ];

            // Execute function
            $functionName = $message->function_call->name;
            $functionArgs = json_decode($message->function_call->arguments, true);
            $result = $this->executor->execute($functionName, $functionArgs);

            // Add function result
            $messages[] = [
                'role' => 'function',
                'name' => $functionName,
                'content' => json_encode($result),
            ];

            $iteration++;
        }

        throw new \Exception('Max iterations reached');
    }
}

// Example conversation:
// User: "What's the weather in the city where the Eiffel Tower is?"
// AI calls: get_landmark_location("Eiffel Tower") -> "Paris, France"
// AI calls: get_weather("Paris, France") -> {temp: 18, condition: "cloudy"}
// AI responds: "The Eiffel Tower is in Paris, France, where it's currently 18°C and cloudy."
```

---

## Best Practices

### 1. Clear Function Descriptions

```php
// ❌ Bad
'description' => 'Gets stuff from DB'

// ✅ Good
'description' => 'Retrieves customer information from the database including name, email, purchase history, and preferences. Use when you need detailed customer data for personalization or support.'
```

### 2. Use Enums for Limited Options

```php
'unit' => [
    'type' => 'string',
    'enum' => ['celsius', 'fahrenheit'], // Limits choices
    'description' => 'Temperature unit for the response'
]
```

### 3. Provide Examples in Descriptions

```php
'location' => [
    'type' => 'string',
    'description' => 'City and state/country. Examples: "Paris, France", "New York, NY", "Tokyo, Japan"'
]
```

### 4. Return Structured Data

```php
// ❌ Bad - returns string
return "Temperature is 72F and sunny";

// ✅ Good - returns structured data
return [
    'temperature' => 72,
    'unit' => 'fahrenheit',
    'condition' => 'sunny',
    'humidity' => 65,
    'wind_speed' => 5,
    'timestamp' => time(),
];
```

### 5. Handle Missing Data Gracefully

```php
public function getProduct(int $id): array
{
    $product = $this->db->find($id);

    if (!$product) {
        return [
            'found' => false,
            'message' => 'Product not found',
            'suggestions' => $this->getSimilarProducts($id),
        ];
    }

    return [
        'found' => true,
        'data' => $product,
    ];
}
```

---

## Common Issues & Solutions

### Issue 1: AI Not Calling Functions

**Symptoms:**
AI responds with text instead of calling your function.

**Solutions:**
1. **Improve function description** - Make it clearer when to use
2. **Add examples** in the description
3. **Use `function_call` parameter:**

```php
// Force AI to call a specific function
'function_call' => ['name' => 'get_weather']

// Encourage but don't force
'function_call' => 'auto' // default
```

### Issue 2: Wrong Parameters

**Symptoms:**
AI calls function with incorrect or missing parameters.

**Solutions:**
1. **Better parameter descriptions:**

```php
'city' => [
    'type' => 'string',
    'description' => 'City name only, without country. Example: "Paris" not "Paris, France"'
]
```

2. **Use `required` array:**

```php
'required' => ['city', 'date']
```

3. **Provide defaults in function:**

```php
public function getWeather(string $city, string $unit = 'celsius'): array
```

### Issue 3: Function Call Loops

**Symptoms:**
AI keeps calling functions repeatedly.

**Solutions:**
1. **Limit iterations:**

```php
$maxCalls = 5;
$callCount = 0;

while ($callCount < $maxCalls) {
    // ... handle function calls
    $callCount++;
}
```

2. **Clear function results:**

```php
// Make results conclusive
return [
    'success' => true,
    'data' => $result,
    'complete' => true, // Signal completion
];
```

### Issue 4: Security Vulnerabilities

**Symptoms:**
Functions can be abused or access unauthorized data.

**Solutions:**
1. **Whitelist functions**
2. **Validate all inputs**
3. **Check user permissions**
4. **Rate limit function calls**
5. **Log all function executions**

---

## Exercises

### Exercise 1: Calculator Function

Build a safe calculator function that the AI can use.

**Requirements:**
- Handle basic operations (+, -, *, /)
- Validate expressions safely (no `eval`)
- Return structured results
- Handle errors gracefully

**Example Usage:**
```
User: "What's 15% of 230?"
AI: *calls calculate("230 * 0.15")*
Result: 34.5
AI: "15% of 230 is 34.5"
```

### Exercise 2: Database Search

Create a product search function.

**Requirements:**
- Search by name or description
- Support filters (price range, category)
- Return top 5 results
- Handle no results case

**Schema:**
```php
[
    'name' => 'search_products',
    'description' => 'Search products by keyword with optional filters',
    'parameters' => [
        // Define schema
    ]
]
```

### Exercise 3: Multi-Function Workflow

Build a system where AI can:
1. Search for a customer by email
2. Get their recent orders
3. Calculate total spending

**Flow:**
```
User: "How much has john@example.com spent?"
AI → getCustomer("john@example.com")
AI → getOrders(customerId)
AI → sum_totals(orders)
AI → "John has spent $1,234.56 total"
```

---

## Key Takeaways

- ✅ **Function calling** connects AI to your application's capabilities
- ✅ **Schemas** tell the AI what functions exist and how to use them
- ✅ **Two API calls** are needed: one to get function call, one to get final response
- ✅ **Security** is critical—validate inputs, check permissions, whitelist functions
- ✅ **Error handling** ensures graceful failures
- ✅ **Multi-step** conversations enable complex workflows
- ✅ **Clear descriptions** help AI choose the right function
- ✅ **Structured data** makes results easier to process

---

## What's Next?

You now understand the fundamentals of function calling. In the next chapter, you'll learn advanced function calling techniques including:

- Parallel function calling (call multiple functions at once)
- Dynamic function registration
- Complex function orchestration
- Building tool systems
- Real-time function updates

👉 **[Chapter 13: Advanced Function Calling](/series/openai-php/chapters/13-advanced-function-calling)**

---

## Additional Resources

- [OpenAI Function Calling Guide](https://platform.openai.com/docs/guides/function-calling)
- [JSON Schema Reference](https://json-schema.org/learn/getting-started-step-by-step)
- [PHP Callbacks Documentation](https://www.php.net/manual/en/language.types.callable.php)

---

## Code Repository

All code examples from this chapter are available at:
```
/code-samples/openai-php/chapter-12/
├── 01-basic-function-calling.php
├── 02-function-registry.php
├── 03-database-functions.php
├── 04-multi-step-conversation.php
└── README.md
```

---

[← Previous: Chapter 11](/series/openai-php/chapters/11-json-mode-structured-outputs) | [Next: Chapter 13 →](/series/openai-php/chapters/13-advanced-function-calling)
