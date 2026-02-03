---
title: "03: Tool System Deep Dive"
description: "Master schema validation, parameter definitions, error handling, and building production-grade tool implementations"
series: "agentic-ai-php-developers"
chapter: 3
difficulty: "Intermediate"
keywords: ["Tool System PHP", "JSON Schema Validation", "Tool Parameters", "Error Handling", "Production Tools"]
teaches: ["Tool schema design and validation", "Parameter type system (string, number, boolean, array)", "Error handling patterns with ToolResult", "Production tool implementations", "Tool registry management"]
estimatedTime: "PT90M"
---

# Chapter 03: Tool System Deep Dive

## Overview

Tools are the hands and eyes of your agent—they let it interact with the real world: read files, query databases, call APIs, perform calculations. In [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent), tools are first-class citizens with a powerful fluent API, JSON schema validation, structured error handling, and production-ready patterns.

In Chapter 01, you learned that tools extend agent capabilities beyond text generation. In Chapter 02, you saw how loops orchestrate tool calls. Now we'll master the tool system itself: how to define robust schemas, validate inputs, handle errors gracefully, and build tools that are reliable enough for production.

In this chapter you'll:

- Master the **fluent tool builder API** for clean, expressive tool definitions
- Understand **JSON schema validation** and parameter types (string, number, boolean, array, object)
- Learn **error handling patterns** with `ToolResult` and exception management
- Build **production-grade tools** with guardrails, validation, and structured outputs
- Use the **ToolRegistry** for centralized tool management and discovery
- Explore **built-in tools** (Calculator, HTTP, FileSystem) and learn from their implementations

**Estimated time:** ~90 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. All code examples are tested against the actual framework source.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`basic-tool.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/basic-tool.php) — Simple tool definition and execution
- [`parameter-types.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/parameter-types.php) — String, number, boolean, array parameters
- [`validation-patterns.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/validation-patterns.php) — Input validation and schema errors
- [`error-handling.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/error-handling.php) — ToolResult patterns and exception handling
- [`production-tool.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/production-tool.php) — Complete production-ready tool
- [`tool-registry.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/tool-registry.php) — Managing multiple tools
- [`builtin-tools.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/builtin-tools.php) — Using framework built-in tools

All files are in [`code/agentic-ai-php-developers/03-tool-system/`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/03-tool-system/README.md).
:::

---

## The Tool Lifecycle

Every tool in `claude-php/claude-php-agent` follows a predictable lifecycle:

```text
1. DEFINITION → Tool::create('name')
                  ->description('...')
                  ->parameter('param', 'type', 'description')
                  ->handler(fn($input) => ...)

2. REGISTRATION → $agent->withTool($tool)
                  or $registry->register($tool)

3. INVOCATION → Agent decides to use tool
                LLM generates tool_use request

4. VALIDATION → Framework validates input against schema
                Types, required fields, enum values, ranges

5. EXECUTION → handler(array $input): ToolResultInterface
               Your code runs with validated input

6. RESULT → ToolResult::success(...) or ToolResult::error(...)
            Result is returned to agent's context

7. CONTINUATION → Agent observes result and decides next step
```

Understanding this flow helps you design tools that integrate seamlessly with the agent loop.

---

## Tool Definition: The Fluent Builder API

The `Tool` class provides a fluent, chainable API for building tool definitions. Every tool needs:

1. **Name** — Unique identifier (snake_case recommended)
2. **Description** — Clear explanation of what the tool does
3. **Parameters** — Input schema with types and constraints
4. **Handler** — PHP callable that executes the tool logic

### Basic Tool Structure

```php
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

$tool = Tool::create('tool_name')
    ->description('What this tool does and when to use it')
    ->parameter('param_name', 'type', 'Parameter description', $required = true)
    ->handler(function (array $input): ToolResult {
        // Your tool logic here
        return ToolResult::success(['result' => 'value']);
    });
```

### Why Fluent API?

The fluent pattern makes tool definitions:

- **Self-documenting** — Schema and logic live together
- **Type-safe** — IDE autocomplete and static analysis work
- **Testable** — Tools can be tested independently
- **Reusable** — Tools are objects you can pass around

---

## Parameter Types and Schema

`claude-php/claude-php-agent` supports the full JSON Schema type system. Each parameter type has validation rules and helper methods.

### String Parameters

```php
$tool = Tool::create('send_email')
    ->description('Send an email to a recipient')
    ->stringParam('to', 'Email address of recipient')
    ->stringParam('subject', 'Email subject line')
    ->stringParam('body', 'Email body content')
    ->stringParam(
        'priority',
        'Message priority level',
        required: true,
        enum: ['low', 'normal', 'high', 'urgent']
    )
    ->handler(function (array $input): ToolResult {
        // Input is guaranteed to have 'to', 'subject', 'body', and 'priority'
        // 'priority' is guaranteed to be one of the enum values
        
        // Validate email format
        if (!filter_var($input['to'], FILTER_VALIDATE_EMAIL)) {
            return ToolResult::error("Invalid email address: {$input['to']}");
        }
        
        // Send email (simplified)
        $sent = mail($input['to'], $input['subject'], $input['body']);
        
        return $sent 
            ? ToolResult::success(['sent' => true, 'to' => $input['to']])
            : ToolResult::error('Failed to send email');
    });
```

**String parameter options:**

- `required: bool` — Is this parameter required?
- `enum: array` — Allowed string values (like a dropdown)
- `minLength: int` — Minimum string length (via `extra` param)
- `maxLength: int` — Maximum string length (via `extra` param)
- `pattern: string` — Regex pattern (via `extra` param)

### Number Parameters

```php
$tool = Tool::create('generate_random_numbers')
    ->description('Generate random numbers within a range')
    ->numberParam('count', 'How many random numbers to generate', true, 1, 1000)
    ->numberParam('min', 'Minimum value (inclusive)', true, 0, 1000000)
    ->numberParam('max', 'Maximum value (inclusive)', true, 0, 1000000)
    ->handler(function (array $input): ToolResult {
        $count = (int)$input['count'];
        $min = (int)$input['min'];
        $max = (int)$input['max'];
        
        // Validation: min must be less than max
        if ($min >= $max) {
            return ToolResult::error("min ({$min}) must be less than max ({$max})");
        }
        
        $numbers = [];
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = rand($min, $max);
        }
        
        return ToolResult::success([
            'numbers' => $numbers,
            'count' => count($numbers),
            'min' => min($numbers),
            'max' => max($numbers),
            'avg' => array_sum($numbers) / count($numbers),
        ]);
    });
```

**Number parameter options:**

- `minimum: float` — Minimum allowed value
- `maximum: float` — Maximum allowed value
- `multipleOf: float` — Number must be multiple of this (via `extra`)

### Boolean Parameters

```php
$tool = Tool::create('fetch_user')
    ->description('Fetch user data from database')
    ->stringParam('user_id', 'User ID to fetch')
    ->booleanParam('include_deleted', 'Include soft-deleted users', required: false)
    ->booleanParam('include_profile', 'Include profile data', required: false)
    ->handler(function (array $input): ToolResult {
        $userId = $input['user_id'];
        $includeDeleted = $input['include_deleted'] ?? false;
        $includeProfile = $input['include_profile'] ?? false;
        
        // Query logic based on boolean flags
        $user = fetchUserFromDB($userId, $includeDeleted);
        
        if ($user === null) {
            return ToolResult::error("User not found: {$userId}");
        }
        
        $result = ['user' => $user];
        
        if ($includeProfile) {
            $result['profile'] = fetchProfileFromDB($userId);
        }
        
        return ToolResult::success($result);
    });
```

### Array Parameters

Arrays can contain primitives or complex structures:

```php
$tool = Tool::create('bulk_update_prices')
    ->description('Update prices for multiple products')
    ->arrayParam(
        'products',
        'Array of product updates',
        required: true,
        items: [
            'type' => 'object',
            'properties' => [
                'product_id' => ['type' => 'string'],
                'new_price' => ['type' => 'number', 'minimum' => 0],
            ],
            'required' => ['product_id', 'new_price'],
        ]
    )
    ->booleanParam('validate_only', 'Only validate without saving', required: false)
    ->handler(function (array $input): ToolResult {
        $products = $input['products'];
        $validateOnly = $input['validate_only'] ?? false;
        
        $updated = [];
        $errors = [];
        
        foreach ($products as $product) {
            $id = $product['product_id'];
            $price = $product['new_price'];
            
            // Validate product exists
            if (!productExists($id)) {
                $errors[] = "Product not found: {$id}";
                continue;
            }
            
            // Validate price is reasonable
            if ($price < 0.01) {
                $errors[] = "Invalid price for {$id}: {$price}";
                continue;
            }
            
            if (!$validateOnly) {
                updateProductPrice($id, $price);
            }
            
            $updated[] = ['product_id' => $id, 'new_price' => $price];
        }
        
        return ToolResult::success([
            'updated_count' => count($updated),
            'error_count' => count($errors),
            'updated' => $updated,
            'errors' => $errors,
            'validate_only' => $validateOnly,
        ]);
    });
```

### Object Parameters (via parameter())

For complex nested structures, use the generic `parameter()` method:

```php
$tool = Tool::create('create_event')
    ->description('Create a calendar event')
    ->stringParam('title', 'Event title')
    ->parameter('date', 'object', 'Event date and time', true, [
        'properties' => [
            'start' => ['type' => 'string', 'format' => 'date-time'],
            'end' => ['type' => 'string', 'format' => 'date-time'],
            'timezone' => ['type' => 'string', 'default' => 'UTC'],
        ],
        'required' => ['start', 'end'],
    ])
    ->arrayParam('attendees', 'List of email addresses', required: false)
    ->handler(function (array $input): ToolResult {
        $title = $input['title'];
        $date = $input['date'];
        $attendees = $input['attendees'] ?? [];
        
        // Validate date range
        $start = new \DateTime($date['start']);
        $end = new \DateTime($date['end']);
        
        if ($start >= $end) {
            return ToolResult::error('Event end time must be after start time');
        }
        
        // Create event
        $eventId = createCalendarEvent($title, $start, $end, $attendees);
        
        return ToolResult::success([
            'event_id' => $eventId,
            'title' => $title,
            'start' => $start->format('c'),
            'end' => $end->format('c'),
            'attendee_count' => count($attendees),
        ]);
    });
```

---

## Input Validation and Schema Errors

The framework automatically validates tool inputs against the schema before calling your handler. This means:

- ✅ **Type safety** — Numbers are numbers, strings are strings
- ✅ **Required fields** — Missing required params trigger errors before your code runs
- ✅ **Enum validation** — Only allowed values pass through
- ✅ **Range checks** — min/max constraints enforced

### What Validation Covers

1. **Required fields** — Missing required parameters rejected
2. **Type checking** — string, number, boolean, array, object
3. **Enum values** — Only listed values allowed
4. **Numeric ranges** — minimum, maximum constraints
5. **String length** — minLength, maxLength (if specified)
6. **Array structure** — items schema validation

### Manual Validation in Handler

Even with schema validation, you may need additional checks:

```php
$tool = Tool::create('transfer_funds')
    ->description('Transfer money between accounts')
    ->stringParam('from_account', 'Source account ID')
    ->stringParam('to_account', 'Destination account ID')
    ->numberParam('amount', 'Amount to transfer', true, 0.01, 1000000)
    ->stringParam('currency', 'Currency code', true, ['USD', 'EUR', 'GBP'])
    ->handler(function (array $input): ToolResult {
        // Schema validation already confirmed:
        // - All required fields present
        // - amount is between 0.01 and 1,000,000
        // - currency is USD, EUR, or GBP
        
        $from = $input['from_account'];
        $to = $input['to_account'];
        $amount = $input['amount'];
        $currency = $input['currency'];
        
        // Business logic validation (beyond schema)
        
        // 1. Accounts can't be the same
        if ($from === $to) {
            return ToolResult::error('Cannot transfer to the same account');
        }
        
        // 2. Check account existence
        $fromAccount = getAccount($from);
        if ($fromAccount === null) {
            return ToolResult::error("Source account not found: {$from}");
        }
        
        $toAccount = getAccount($to);
        if ($toAccount === null) {
            return ToolResult::error("Destination account not found: {$to}");
        }
        
        // 3. Check account currency matches
        if ($fromAccount['currency'] !== $currency) {
            return ToolResult::error(
                "Source account currency ({$fromAccount['currency']}) " .
                "does not match transfer currency ({$currency})"
            );
        }
        
        // 4. Check sufficient balance
        if ($fromAccount['balance'] < $amount) {
            return ToolResult::error(
                "Insufficient balance: {$fromAccount['balance']} {$currency} " .
                "(need {$amount} {$currency})"
            );
        }
        
        // 5. Perform transfer
        $transactionId = performTransfer($from, $to, $amount, $currency);
        
        return ToolResult::success([
            'transaction_id' => $transactionId,
            'from_account' => $from,
            'to_account' => $to,
            'amount' => $amount,
            'currency' => $currency,
            'timestamp' => date('c'),
        ]);
    });
```

**Validation layers:**

1. **Schema validation** (automatic) — Types, required, enums, ranges
2. **Business validation** (your code) — Domain rules, existence checks
3. **Security validation** (your code) — Permissions, rate limits, sanitization

---

## Error Handling with ToolResult

`ToolResult` is the framework's standard way to communicate tool outcomes. It has two states:

1. **Success** — Tool completed successfully
2. **Error** — Tool failed or encountered an issue

### Creating ToolResults

```php
use ClaudeAgents\Tools\ToolResult;

// ✅ Success with string content
return ToolResult::success('Operation completed successfully');

// ✅ Success with array (automatically JSON-encoded)
return ToolResult::success([
    'status' => 'completed',
    'records_processed' => 42,
    'duration_seconds' => 1.23,
]);

// ❌ Error with descriptive message
return ToolResult::error('Database connection failed: timeout after 30s');

// ❌ Error from exception
try {
    $result = riskyOperation();
    return ToolResult::success($result);
} catch (\Throwable $e) {
    return ToolResult::fromException($e);
}
```

### Error Handling Patterns

#### Pattern 1: Early Return for Invalid Input

```php
->handler(function (array $input): ToolResult {
    // Validate early, return immediately on errors
    if (empty($input['email'])) {
        return ToolResult::error('Email cannot be empty');
    }
    
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        return ToolResult::error("Invalid email format: {$input['email']}");
    }
    
    // Main logic only runs if validation passes
    $user = createUser($input['email']);
    
    return ToolResult::success(['user_id' => $user->id]);
});
```

#### Pattern 2: Try-Catch with Context

```php
->handler(function (array $input): ToolResult {
    try {
        $apiKey = $input['api_key'];
        $endpoint = $input['endpoint'];
        
        $response = callExternalAPI($endpoint, $apiKey);
        
        return ToolResult::success([
            'status' => $response->status,
            'data' => $response->body,
        ]);
    } catch (APIException $e) {
        // Specific error with context
        return ToolResult::error(
            "API call failed: {$e->getMessage()} " .
            "(endpoint: {$endpoint}, status: {$e->getStatusCode()})"
        );
    } catch (\Throwable $e) {
        // Generic fallback
        return ToolResult::fromException($e);
    }
});
```

#### Pattern 3: Partial Success Reporting

```php
->handler(function (array $input): ToolResult {
    $files = $input['files'];
    $processed = [];
    $errors = [];
    
    foreach ($files as $file) {
        try {
            $result = processFile($file);
            $processed[] = ['file' => $file, 'result' => $result];
        } catch (\Throwable $e) {
            $errors[] = ['file' => $file, 'error' => $e->getMessage()];
        }
    }
    
    // Return success even if some failed (include error details)
    return ToolResult::success([
        'total' => count($files),
        'processed' => count($processed),
        'failed' => count($errors),
        'results' => $processed,
        'errors' => $errors,
    ]);
});
```

#### Pattern 4: Validation with Error Accumulation

```php
->handler(function (array $input): ToolResult {
    $errors = [];
    
    // Collect all validation errors before failing
    if (strlen($input['username']) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (usernameExists($input['username'])) {
        $errors[] = 'Username already taken';
    }
    
    // Return all errors at once (better UX than one at a time)
    if (!empty($errors)) {
        return ToolResult::error(implode('; ', $errors));
    }
    
    // Create user
    $user = createUser($input['username']);
    
    return ToolResult::success(['user_id' => $user->id]);
});
```

### When to Use Error vs Exception

**Use `ToolResult::error()`** when:

- ✅ Input validation fails
- ✅ Business rule violated (insufficient funds, duplicate entry)
- ✅ External service returns error (API 404, database constraint)
- ✅ Expected failure condition

**Use exceptions (caught and converted)** when:

- ✅ Unexpected system error (out of memory, file permissions)
- ✅ Configuration problem (missing env var, invalid credentials)
- ✅ Programming error (null pointer, type error)

**Rule of thumb:** If you can describe the error in user-friendly terms, use `ToolResult::error()`. If it's a system-level problem, catch the exception and wrap it.

---

## Production-Grade Tool Example

Let's build a complete production tool with all best practices:

```php
<?php

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use Psr\Log\LoggerInterface;

/**
 * Production-grade tool for managing product inventory.
 * 
 * Features:
 * - Comprehensive validation
 * - Structured error handling
 * - Logging and observability
 * - Idempotency support
 * - Detailed result metadata
 */
function createInventoryTool(
    \PDO $db,
    LoggerInterface $logger,
    int $maxQuantity = 10000
): Tool {
    return Tool::create('update_inventory')
        ->description(
            'Update product inventory quantities. ' .
            'Supports both absolute values and relative adjustments. ' .
            'Includes stock validation and audit logging.'
        )
        ->stringParam('product_id', 'Product SKU or ID')
        ->numberParam('quantity', 'New quantity or adjustment', true, -10000, $maxQuantity)
        ->stringParam(
            'mode',
            'Update mode: "set" (absolute) or "adjust" (relative)',
            true,
            ['set', 'adjust']
        )
        ->stringParam('reason', 'Reason for inventory change', required: false)
        ->stringParam('idempotency_key', 'Unique key to prevent duplicate updates', required: false)
        ->handler(function (array $input) use ($db, $logger, $maxQuantity): ToolResult {
            $startTime = microtime(true);
            $productId = $input['product_id'];
            $quantity = (int)$input['quantity'];
            $mode = $input['mode'];
            $reason = $input['reason'] ?? 'no reason provided';
            $idempotencyKey = $input['idempotency_key'] ?? null;
            
            $logger->info('Inventory update requested', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'mode' => $mode,
            ]);
            
            // 1. Check idempotency (prevent duplicate operations)
            if ($idempotencyKey !== null) {
                $existing = checkIdempotencyKey($db, $idempotencyKey);
                if ($existing !== null) {
                    $logger->info('Duplicate request detected, returning cached result', [
                        'idempotency_key' => $idempotencyKey,
                    ]);
                    return ToolResult::success($existing);
                }
            }
            
            try {
                $db->beginTransaction();
                
                // 2. Fetch current product
                $stmt = $db->prepare('SELECT * FROM products WHERE id = ? FOR UPDATE');
                $stmt->execute([$productId]);
                $product = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($product === false) {
                    $db->rollBack();
                    $logger->warning('Product not found', ['product_id' => $productId]);
                    return ToolResult::error("Product not found: {$productId}");
                }
                
                // 3. Calculate new quantity
                $oldQuantity = (int)$product['quantity'];
                
                if ($mode === 'set') {
                    $newQuantity = $quantity;
                } else { // adjust
                    $newQuantity = $oldQuantity + $quantity;
                }
                
                // 4. Validate new quantity
                if ($newQuantity < 0) {
                    $db->rollBack();
                    $logger->warning('Negative inventory not allowed', [
                        'product_id' => $productId,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                    ]);
                    return ToolResult::error(
                        "Inventory cannot go negative: current={$oldQuantity}, adjustment={$quantity}"
                    );
                }
                
                if ($newQuantity > $maxQuantity) {
                    $db->rollBack();
                    $logger->warning('Max quantity exceeded', [
                        'product_id' => $productId,
                        'new_quantity' => $newQuantity,
                        'max_quantity' => $maxQuantity,
                    ]);
                    return ToolResult::error(
                        "Quantity {$newQuantity} exceeds maximum allowed: {$maxQuantity}"
                    );
                }
                
                // 5. Update inventory
                $updateStmt = $db->prepare('UPDATE products SET quantity = ?, updated_at = NOW() WHERE id = ?');
                $updateStmt->execute([$newQuantity, $productId]);
                
                // 6. Create audit log
                $auditStmt = $db->prepare(
                    'INSERT INTO inventory_audit (product_id, old_quantity, new_quantity, mode, reason, created_at) ' .
                    'VALUES (?, ?, ?, ?, ?, NOW())'
                );
                $auditStmt->execute([$productId, $oldQuantity, $newQuantity, $mode, $reason]);
                $auditId = $db->lastInsertId();
                
                // 7. Store idempotency result
                if ($idempotencyKey !== null) {
                    storeIdempotencyResult($db, $idempotencyKey, [
                        'product_id' => $productId,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'audit_id' => $auditId,
                    ]);
                }
                
                $db->commit();
                
                $duration = microtime(true) - $startTime;
                
                $logger->info('Inventory updated successfully', [
                    'product_id' => $productId,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'audit_id' => $auditId,
                    'duration_seconds' => round($duration, 3),
                ]);
                
                return ToolResult::success([
                    'product_id' => $productId,
                    'product_name' => $product['name'],
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'change' => $newQuantity - $oldQuantity,
                    'mode' => $mode,
                    'audit_id' => $auditId,
                    'timestamp' => date('c'),
                    'duration_seconds' => round($duration, 3),
                ]);
                
            } catch (\PDOException $e) {
                $db->rollBack();
                $logger->error('Database error during inventory update', [
                    'product_id' => $productId,
                    'error' => $e->getMessage(),
                ]);
                return ToolResult::error("Database error: {$e->getMessage()}");
            } catch (\Throwable $e) {
                $db->rollBack();
                $logger->error('Unexpected error during inventory update', [
                    'product_id' => $productId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return ToolResult::fromException($e);
            }
        });
}
```

**Production patterns demonstrated:**

- ✅ **Idempotency** — Duplicate requests return cached results
- ✅ **Transactions** — Database operations are atomic
- ✅ **Locking** — `FOR UPDATE` prevents race conditions
- ✅ **Validation** — Business rules enforced (no negative stock)
- ✅ **Audit logging** — All changes tracked with reason
- ✅ **Observability** — Structured logging at every step
- ✅ **Error context** — Errors include relevant data for debugging
- ✅ **Performance tracking** — Duration included in response

---

## Tool Registry

The `ToolRegistry` manages collections of tools and provides centralized registration, discovery, and execution.

### Basic Registry Usage

```php
use ClaudeAgents\Tools\ToolRegistry;
use ClaudeAgents\Tools\Tool;

$registry = new ToolRegistry();

// Register individual tools
$registry->register($calculatorTool);
$registry->register($weatherTool);

// Register many at once
$registry->registerMany([
    $emailTool,
    $databaseTool,
    $httpTool,
]);

// Check if tool exists
if ($registry->has('calculator')) {
    $tool = $registry->get('calculator');
}

// Get all registered tools
$allTools = $registry->all();

// Get just the names
$toolNames = $registry->names(); // ['calculator', 'weather', 'email', ...]

// Get tool definitions for API calls
$definitions = $registry->toDefinitions();

// Remove a tool
$registry->remove('deprecated_tool');

// Clear all tools
$registry->clear();

// Count tools
$count = $registry->count();
```

### Using Registry with Agents

```php
use ClaudeAgents\Agent;
use ClaudeAgents\Tools\ToolRegistry;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$registry = new ToolRegistry();
$registry->registerMany([
    $calculatorTool,
    $weatherTool,
    $databaseTool,
]);

$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant.')
    ->withTools($registry->all())  // Pass all registered tools
    ->maxIterations(10);

$result = $agent->run('What is the weather in Seattle?');
```

### Dynamic Tool Registration

```php
class DynamicToolRegistry extends ToolRegistry
{
    public function __construct(
        private array $config,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
        $this->registerDefaultTools();
    }
    
    private function registerDefaultTools(): void
    {
        // Always available
        $this->register(CalculatorTool::create());
        
        // Conditional registration based on config
        if ($this->config['enable_http'] ?? false) {
            $this->register(HTTPTool::create([
                'timeout' => $this->config['http_timeout'] ?? 30,
                'allowed_domains' => $this->config['allowed_domains'] ?? [],
            ]));
        }
        
        if ($this->config['enable_filesystem'] ?? false) {
            $this->register(FileSystemTool::create([
                'allowed_paths' => $this->config['filesystem_paths'] ?? [],
                'read_only' => $this->config['filesystem_readonly'] ?? true,
            ]));
        }
        
        if ($this->config['enable_database'] ?? false) {
            $this->register($this->createDatabaseTool());
        }
    }
    
    private function createDatabaseTool(): Tool
    {
        $dsn = $this->config['database_dsn'];
        $username = $this->config['database_username'];
        $password = $this->config['database_password'];
        
        return DatabaseTool::create([
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password,
            'allowed_operations' => ['SELECT'], // Read-only for safety
        ]);
    }
    
    public function registerUserTools(array $userConfig): void
    {
        // Allow runtime tool registration
        foreach ($userConfig['custom_tools'] ?? [] as $toolDef) {
            $tool = $this->buildToolFromConfig($toolDef);
            $this->register($tool);
            $this->logger->info('Registered custom tool', ['tool' => $tool->getName()]);
        }
    }
}
```

---

## Built-in Tools

The framework includes production-ready tools for common operations:

### CalculatorTool

```php
use ClaudeAgents\Tools\BuiltIn\CalculatorTool;

$calculator = CalculatorTool::create([
    'allow_functions' => true,  // Enable sqrt, sin, cos, etc.
    'max_precision' => 10,       // Decimal places
]);

// Usage by agent
$result = $calculator->execute([
    'expression' => '(25 * 17) + sqrt(144)',
]);

echo $result->getContent(); // {"result":437,"expression":"(25 * 17) + sqrt(144)"}
```

**Features:**

- Safe expression evaluation (no code injection)
- Support for math functions (optional)
- Precision control
- Validation and error handling

### HTTPTool

```php
use ClaudeAgents\Tools\BuiltIn\HTTPTool;

$http = HTTPTool::create([
    'timeout' => 30,
    'follow_redirects' => true,
    'allowed_domains' => ['api.example.com', 'example.com'],
    'max_response_size' => 1024 * 1024, // 1MB
    'user_agent' => 'MyAgent/1.0',
]);

// Usage by agent
$result = $http->execute([
    'url' => 'https://api.example.com/users',
    'method' => 'GET',
    'headers' => ['Authorization' => 'Bearer token123'],
]);
```

**Features:**

- Full HTTP method support (GET, POST, PUT, DELETE, PATCH)
- Custom headers and body
- Domain whitelisting for security
- Response size limits
- Timeout configuration
- Redirect handling

### FileSystemTool

```php
use ClaudeAgents\Tools\BuiltIn\FileSystemTool;

$filesystem = FileSystemTool::create([
    'allowed_paths' => ['/var/app/data', '/tmp/agent-scratch'],
    'read_only' => false,
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    'allowed_extensions' => ['txt', 'json', 'csv', 'log'],
]);

// Usage by agent
$result = $filesystem->execute([
    'operation' => 'read',
    'path' => '/var/app/data/config.json',
]);

$result = $filesystem->execute([
    'operation' => 'write',
    'path' => '/tmp/agent-scratch/output.txt',
    'content' => 'Generated by agent',
]);

$result = $filesystem->execute([
    'operation' => 'list',
    'path' => '/var/app/data',
    'recursive' => true,
]);
```

**Operations:**

- `read` — Read file contents
- `write` — Write file contents
- `list` — List directory contents
- `exists` — Check if file/directory exists
- `delete` — Delete file/directory
- `info` — Get file metadata
- `mkdir` — Create directory

**Features:**

- Path whitelisting for security
- Read-only mode option
- File size limits
- Extension restrictions
- Recursive operations

### Using Built-in Tools

```php
use ClaudeAgents\Agent;
use ClaudeAgents\Tools\BuiltIn\CalculatorTool;
use ClaudeAgents\Tools\BuiltIn\HTTPTool;
use ClaudeAgents\Tools\BuiltIn\FileSystemTool;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$agent = Agent::create($client)
    ->withSystemPrompt('You are a data analysis assistant.')
    ->withTools([
        CalculatorTool::create(['allow_functions' => true]),
        HTTPTool::create(['allowed_domains' => ['api.example.com']]),
        FileSystemTool::create([
            'allowed_paths' => [getcwd() . '/data'],
            'read_only' => true,
        ]),
    ])
    ->maxIterations(15);

$result = $agent->run(
    'Fetch user data from https://api.example.com/users/123, ' .
    'save it to data/user-123.json, and calculate the average age of their friends.'
);
```

---

## Testing Tools

Tools should be tested independently from agents:

```php
use PHPUnit\Framework\TestCase;

class InventoryToolTest extends TestCase
{
    private \PDO $db;
    private LoggerInterface $logger;
    
    protected function setUp(): void
    {
        $this->db = new \PDO('sqlite::memory:');
        $this->logger = new NullLogger();
        $this->createTestSchema();
    }
    
    public function test_set_inventory_success(): void
    {
        $tool = createInventoryTool($this->db, $this->logger);
        
        $result = $tool->execute([
            'product_id' => 'SKU123',
            'quantity' => 100,
            'mode' => 'set',
            'reason' => 'Initial stock',
        ]);
        
        $this->assertTrue($result->isSuccess());
        
        $data = json_decode($result->getContent(), true);
        $this->assertEquals('SKU123', $data['product_id']);
        $this->assertEquals(100, $data['new_quantity']);
    }
    
    public function test_adjust_inventory_success(): void
    {
        $this->insertProduct('SKU123', 50);
        
        $tool = createInventoryTool($this->db, $this->logger);
        
        $result = $tool->execute([
            'product_id' => 'SKU123',
            'quantity' => 25,
            'mode' => 'adjust',
            'reason' => 'Restocking',
        ]);
        
        $this->assertTrue($result->isSuccess());
        
        $data = json_decode($result->getContent(), true);
        $this->assertEquals(50, $data['old_quantity']);
        $this->assertEquals(75, $data['new_quantity']);
        $this->assertEquals(25, $data['change']);
    }
    
    public function test_negative_inventory_rejected(): void
    {
        $this->insertProduct('SKU123', 10);
        
        $tool = createInventoryTool($this->db, $this->logger);
        
        $result = $tool->execute([
            'product_id' => 'SKU123',
            'quantity' => -20,
            'mode' => 'adjust',
        ]);
        
        $this->assertTrue($result->isError());
        $this->assertStringContainsString('cannot go negative', $result->getContent());
    }
    
    public function test_idempotency(): void
    {
        $this->insertProduct('SKU123', 100);
        
        $tool = createInventoryTool($this->db, $this->logger);
        
        $idempotencyKey = 'unique-key-' . uniqid();
        
        // First request
        $result1 = $tool->execute([
            'product_id' => 'SKU123',
            'quantity' => 50,
            'mode' => 'set',
            'idempotency_key' => $idempotencyKey,
        ]);
        
        $this->assertTrue($result1->isSuccess());
        
        // Duplicate request (should return cached result without changing DB)
        $result2 = $tool->execute([
            'product_id' => 'SKU123',
            'quantity' => 50,
            'mode' => 'set',
            'idempotency_key' => $idempotencyKey,
        ]);
        
        $this->assertTrue($result2->isSuccess());
        $this->assertEquals($result1->getContent(), $result2->getContent());
        
        // Verify quantity didn't change twice
        $quantity = $this->getProductQuantity('SKU123');
        $this->assertEquals(50, $quantity);
    }
}
```

---

## Best Practices

### 1. Tool Naming

✅ **Good:**

- `get_weather` — Clear action + noun
- `send_email` — Imperative verb
- `calculate_tax` — Specific purpose
- `fetch_user_profile` — Descriptive

❌ **Bad:**

- `weather` — Too vague
- `doStuff` — Not descriptive
- `handleRequest` — Generic
- `tool1` — Meaningless

### 2. Descriptions

✅ **Good:**

> "Send an email to a recipient with subject and body. Supports HTML content and attachments up to 10MB."

❌ **Bad:**

> "Sends email"

**Include:**

- What the tool does
- When to use it
- Important constraints or limits
- Expected input format

### 3. Parameter Design

**Keep parameters flat when possible:**

```php
// ✅ Good - flat structure
->stringParam('user_id', 'User ID')
->stringParam('email', 'Email address')
->stringParam('name', 'Full name')

// ❌ Bad - unnecessary nesting
->parameter('user', 'object', 'User data', true, [
    'properties' => [
        'id' => ['type' => 'string'],
        'email' => ['type' => 'string'],
        'name' => ['type' => 'string'],
    ],
])
```

**Use nesting for truly related data:**

```php
// ✅ Good - address is a cohesive unit
->parameter('address', 'object', 'Mailing address', true, [
    'properties' => [
        'street' => ['type' => 'string'],
        'city' => ['type' => 'string'],
        'state' => ['type' => 'string'],
        'zip' => ['type' => 'string'],
    ],
])
```

### 4. Error Messages

✅ **Good:**

```php
return ToolResult::error(
    "Product not found: {$productId}. " .
    "Please verify the product ID and try again."
);
```

❌ **Bad:**

```php
return ToolResult::error('Error');
```

**Include:**

- What went wrong
- What was attempted
- Relevant context (IDs, values)
- How to fix it (if known)

### 5. Result Structure

**Be consistent with result format:**

```php
// ✅ Good - structured, predictable
return ToolResult::success([
    'status' => 'completed',
    'entity_id' => $id,
    'entity_type' => 'user',
    'timestamp' => date('c'),
    'metadata' => [
        'duration_seconds' => 0.123,
        'records_affected' => 1,
    ],
]);

// ❌ Bad - inconsistent structure
return ToolResult::success('User created: ' . $id);
```

### 6. Logging

```php
// Log at appropriate levels
$logger->debug('Tool input', ['input' => $input]);
$logger->info('Operation completed', ['result' => $summary]);
$logger->warning('Validation failed', ['errors' => $errors]);
$logger->error('Execution failed', ['exception' => $e->getMessage()]);

// Include structured context
$logger->info('Database query executed', [
    'tool' => 'fetch_user',
    'user_id' => $userId,
    'duration_ms' => $duration * 1000,
    'result_count' => count($results),
]);
```

---

## Troubleshooting

### Tool Not Executing

**Symptom:** Agent doesn't use your tool

**Causes:**

1. Tool description unclear or misleading
2. Tool name doesn't match task
3. Parameters too complex
4. Agent has simpler alternative

**Solutions:**

- Improve description with use cases
- Simplify parameter schema
- Test tool in isolation
- Check agent prompt mentions tool capability

### Validation Errors

**Symptom:** "Missing required field" or "Invalid type"

**Causes:**

1. Schema mismatch between definition and agent output
2. Required field not marked in schema
3. Type constraints too strict

**Solutions:**

```php
// Debug: log what agent is sending
$logger->debug('Raw tool input', ['input' => $input]);

// Check schema matches expectations
$schema = $tool->getInputSchema();
$logger->debug('Tool schema', ['schema' => $schema]);

// Make optional fields truly optional
->stringParam('optional_field', 'Description', required: false)
```

### Handler Exceptions

**Symptom:** Tool fails with generic error

**Causes:**

1. Unhandled exception in handler
2. Missing null checks
3. Type mismatches

**Solutions:**

```php
->handler(function (array $input): ToolResult {
    try {
        // Defensive null checks
        $value = $input['key'] ?? null;
        if ($value === null) {
            return ToolResult::error('Missing required data');
        }
        
        // Type coercion
        $quantity = (int)($input['quantity'] ?? 0);
        
        // Your logic
        $result = doSomething($value, $quantity);
        
        return ToolResult::success($result);
    } catch (\Throwable $e) {
        // Always catch at top level
        return ToolResult::fromException($e);
    }
});
```

---

## Wrap-up

Congratulations! You've mastered the tool system in `claude-php/claude-php-agent`. You now understand:

- ✓ How to define tools with the fluent builder API
- ✓ All parameter types and schema validation rules
- ✓ Error handling patterns with ToolResult
- ✓ Production tool design with validation, logging, and idempotency
- ✓ ToolRegistry for centralized tool management
- ✓ Built-in tools (Calculator, HTTP, FileSystem) and how to use them

Tools are the bridge between your agent's intelligence and the real world. With robust schemas, graceful error handling, and production patterns, your tools become reliable building blocks for complex agentic systems.

### What You've Achieved

You can now build tools that:

- Validate inputs automatically with JSON schema
- Handle errors gracefully and informatively
- Follow production patterns (idempotency, transactions, logging)
- Integrate seamlessly with agent loops
- Are testable and maintainable

### Next Steps

In **Chapter 04: Agent Configuration and Best Practices**, we'll configure agents for production:

- Retry logic and backoff strategies
- Logging and observability
- Error recovery patterns
- Performance optimization
- Cost management

You'll take the tools you've built and wrap them in a production-ready agent runtime.

---

## Further Reading

To deepen your understanding of tools and validation:

- [claude-php/claude-php-agent Tool Documentation](https://github.com/claude-php/claude-php-agent#tools) — Official framework docs
- [JSON Schema Specification](https://json-schema.org/) — Schema validation rules
- [JSON Schema Validation Guide](https://json-schema.org/understanding-json-schema/) — Understanding schema design
- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/) — Standard logging interface
- [Function Calling Best Practices (Anthropic)](https://docs.anthropic.com/en/docs/build-with-claude/tool-use) — Claude-specific tool guidance
