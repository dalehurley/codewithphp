---
title: "05: Tool Routing and Execution Pipelines"
description: "Create a tool router that dispatches safely, logs executions, and standardizes error responses. Introduce retries and idempotency for tools."
series: "agentic-ai-php-developers"
chapter: 5
difficulty: "Intermediate"
keywords: ["Tool Router PHP", "Execution Pipeline", "Tool Dispatcher", "Error Standardization", "Idempotency", "Tool Retry Logic"]
teaches: ["Tool routing and dispatch patterns", "Execution pipeline design", "Standardized error responses", "Retry logic with idempotency", "Tool execution logging and monitoring", "Safe tool dispatching with validation"]
estimatedTime: "PT120M"
---

# Chapter 05: Tool Routing and Execution Pipelines

## Overview

In Chapters 03 and 04, you learned to build production-ready tools and configure agents with retry logic. But what happens between an agent deciding to use a tool and that tool actually executing? This is where **tool routing and execution pipelines** come in—the critical infrastructure that safely dispatches tool calls, logs executions, standardizes errors, and ensures reliability through retries and idempotency.

A well-designed execution pipeline is the difference between tools that work in demos and tools that work in production. You need routing logic to find the right tool, validation to catch bad inputs early, logging to debug failures, error standardization for consistent handling, and retry mechanisms with idempotency to handle transient failures.

In this chapter you'll:

- Build a **tool router** that dispatches calls safely with validation and logging
- Design an **execution pipeline** with pre-execution checks, logging, and post-execution hooks
- Standardize **error responses** for consistent error handling across all tools
- Implement **retry logic with idempotency** to handle transient failures safely
- Add **execution monitoring** with metrics, traces, and audit logs
- Build **parallel execution** patterns for concurrent tool calls
- Apply **production patterns** for safe, observable tool execution

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. We'll build on top of the framework's existing patterns and show you how to extend them.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`tool-router.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/tool-router.php) — Tool routing and dispatching
- [`execution-pipeline.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/execution-pipeline.php) — Complete execution pipeline
- [`error-standardization.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/error-standardization.php) — Standardized error responses
- [`retry-with-idempotency.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/retry-with-idempotency.php) — Retry logic with idempotency keys
- [`execution-logging.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/execution-logging.php) — Comprehensive logging patterns
- [`parallel-execution.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/parallel-execution.php) — Concurrent tool execution
- [`production-pipeline.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/production-pipeline.php) — Complete production setup

All files are in [`code/agentic-ai-php-developers/05-tool-routing/`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/05-tool-routing/README.md).
:::

---

## The Tool Execution Pipeline

Before diving into code, let's understand the complete tool execution pipeline:

```text
┌─────────────────────────────────────────────────────────────────┐
│                    TOOL EXECUTION PIPELINE                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. ROUTING                                                      │
│     ├─ Find tool by name                                        │
│     ├─ Check tool exists                                        │
│     └─ Validate tool is enabled                                 │
│                                                                  │
│  2. PRE-EXECUTION                                                │
│     ├─ Validate input against schema                            │
│     ├─ Check permissions/auth                                   │
│     ├─ Check rate limits                                        │
│     ├─ Check idempotency (if retry)                             │
│     └─ Log execution start                                      │
│                                                                  │
│  3. EXECUTION                                                    │
│     ├─ Execute tool handler                                     │
│     ├─ Catch and wrap exceptions                                │
│     ├─ Apply timeout                                            │
│     └─ Track execution time                                     │
│                                                                  │
│  4. POST-EXECUTION                                               │
│     ├─ Standardize result format                                │
│     ├─ Log execution completion                                 │
│     ├─ Record metrics                                           │
│     ├─ Store for idempotency cache                              │
│     └─ Fire callbacks/events                                    │
│                                                                  │
│  5. ERROR HANDLING                                               │
│     ├─ Catch exceptions                                         │
│     ├─ Standardize error format                                 │
│     ├─ Log with context                                         │
│     ├─ Decide if retryable                                      │
│     └─ Apply retry logic                                        │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

Each stage adds reliability, observability, and safety to tool execution.

---

## Building a Tool Router

The router is responsible for finding and dispatching the right tool for a given request.

### Basic Router Implementation

```php
<?php

use ClaudeAgents\Tools\ToolRegistry;
use ClaudeAgents\Tools\ToolResult;
use Psr\Log\LoggerInterface;

class ToolRouter
{
    public function __construct(
        private ToolRegistry $registry,
        private ?LoggerInterface $logger = null,
    ) {}
    
    /**
     * Route a tool call to the appropriate handler.
     */
    public function route(string $toolName, array $input): ToolResult
    {
        // 1. Find tool
        $tool = $this->registry->get($toolName);
        
        if ($tool === null) {
            $this->logger?->warning("Tool not found: {$toolName}");
            return ToolResult::error("Unknown tool: {$toolName}");
        }
        
        // 2. Execute
        try {
            $this->logger?->info("Routing to tool: {$toolName}", [
                'input_keys' => array_keys($input),
            ]);
            
            $result = $tool->execute($input);
            
            $this->logger?->info("Tool executed successfully: {$toolName}");
            
            return $result;
            
        } catch (\Throwable $e) {
            $this->logger?->error("Tool execution failed: {$toolName}", [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            
            return ToolResult::error($e->getMessage());
        }
    }
}
```

### Router with Validation and Permissions

Add pre-execution checks:

```php
class SecureToolRouter
{
    private array $permissions = [];
    private array $disabledTools = [];
    
    public function __construct(
        private ToolRegistry $registry,
        private ?LoggerInterface $logger = null,
    ) {}
    
    /**
     * Set tool permissions (which users/roles can use which tools).
     */
    public function setPermissions(string $toolName, array $allowedRoles): self
    {
        $this->permissions[$toolName] = $allowedRoles;
        return $this;
    }
    
    /**
     * Disable a tool temporarily.
     */
    public function disableTool(string $toolName): self
    {
        $this->disabledTools[] = $toolName;
        return $this;
    }
    
    /**
     * Route with security checks.
     */
    public function route(
        string $toolName,
        array $input,
        ?string $userRole = null
    ): ToolResult {
        // 1. Check if tool is disabled
        if (in_array($toolName, $this->disabledTools)) {
            $this->logger?->warning("Attempted to use disabled tool: {$toolName}");
            return ToolResult::error("Tool '{$toolName}' is currently unavailable");
        }
        
        // 2. Check permissions
        if (isset($this->permissions[$toolName])) {
            $allowedRoles = $this->permissions[$toolName];
            
            if ($userRole === null || !in_array($userRole, $allowedRoles)) {
                $this->logger?->warning("Permission denied for tool: {$toolName}", [
                    'user_role' => $userRole,
                    'allowed_roles' => $allowedRoles,
                ]);
                return ToolResult::error("Permission denied for tool: {$toolName}");
            }
        }
        
        // 3. Find tool
        $tool = $this->registry->get($toolName);
        
        if ($tool === null) {
            return ToolResult::error("Unknown tool: {$toolName}");
        }
        
        // 4. Validate input against schema
        $schema = $tool->getInputSchema();
        $validation = $this->validateInput($input, $schema);
        
        if (!$validation['valid']) {
            $this->logger?->warning("Tool input validation failed: {$toolName}", [
                'errors' => $validation['errors'],
            ]);
            
            return ToolResult::error(
                "Validation failed: " . implode(', ', $validation['errors'])
            );
        }
        
        // 5. Execute
        try {
            $result = $tool->execute($input);
            return $result;
            
        } catch (\Throwable $e) {
            $this->logger?->error("Tool execution failed: {$toolName}", [
                'error' => $e->getMessage(),
            ]);
            
            return ToolResult::error($e->getMessage());
        }
    }
    
    /**
     * Validate input against JSON schema.
     */
    private function validateInput(array $input, array $schema): array
    {
        $errors = [];
        
        // Check required fields
        $required = $schema['required'] ?? [];
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }
        
        // Check types
        $properties = $schema['properties'] ?? [];
        foreach ($input as $key => $value) {
            if (isset($properties[$key]['type'])) {
                $expectedType = $properties[$key]['type'];
                $actualType = $this->getJsonType($value);
                
                if ($actualType !== $expectedType) {
                    $errors[] = "Field '{$key}' expected type '{$expectedType}', got '{$actualType}'";
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    private function getJsonType(mixed $value): string
    {
        return match (gettype($value)) {
            'boolean' => 'boolean',
            'integer' => 'integer',
            'double' => 'number',
            'string' => 'string',
            'array' => 'array',
            'object' => 'object',
            'NULL' => 'null',
            default => 'unknown',
        };
    }
}
```

---

## Building an Execution Pipeline

The pipeline wraps tool execution with logging, metrics, and error handling.

### Complete Pipeline Implementation

```php
<?php

use ClaudeAgents\Tools\ToolResult;
use Psr\Log\LoggerInterface;

class ToolExecutionPipeline
{
    private array $preExecutionHooks = [];
    private array $postExecutionHooks = [];
    
    public function __construct(
        private ToolRouter $router,
        private ?LoggerInterface $logger = null,
    ) {}
    
    /**
     * Add a pre-execution hook.
     * 
     * Hook signature: fn(string $toolName, array $input): void
     * Throw exception to abort execution.
     */
    public function addPreExecutionHook(callable $hook): self
    {
        $this->preExecutionHooks[] = $hook;
        return $this;
    }
    
    /**
     * Add a post-execution hook.
     * 
     * Hook signature: fn(string $toolName, array $input, ToolResult $result, float $duration): void
     */
    public function addPostExecutionHook(callable $hook): self
    {
        $this->postExecutionHooks[] = $hook;
        return $this;
    }
    
    /**
     * Execute a tool through the complete pipeline.
     */
    public function execute(string $toolName, array $input): ToolResult
    {
        $executionId = $this->generateExecutionId();
        $startTime = microtime(true);
        
        $this->logger?->info("Pipeline execution started", [
            'execution_id' => $executionId,
            'tool' => $toolName,
        ]);
        
        try {
            // 1. PRE-EXECUTION HOOKS
            foreach ($this->preExecutionHooks as $hook) {
                try {
                    $hook($toolName, $input);
                } catch (\Throwable $e) {
                    $this->logger?->error("Pre-execution hook failed", [
                        'execution_id' => $executionId,
                        'error' => $e->getMessage(),
                    ]);
                    
                    return ToolResult::error(
                        "Pre-execution check failed: {$e->getMessage()}"
                    );
                }
            }
            
            // 2. EXECUTE TOOL
            $result = $this->router->route($toolName, $input);
            
            // 3. CALCULATE DURATION
            $duration = microtime(true) - $startTime;
            
            // 4. POST-EXECUTION HOOKS
            foreach ($this->postExecutionHooks as $hook) {
                try {
                    $hook($toolName, $input, $result, $duration);
                } catch (\Throwable $e) {
                    $this->logger?->warning("Post-execution hook failed", [
                        'execution_id' => $executionId,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the execution if post-hook fails
                }
            }
            
            $this->logger?->info("Pipeline execution completed", [
                'execution_id' => $executionId,
                'tool' => $toolName,
                'duration_ms' => round($duration * 1000, 2),
                'success' => $result->isSuccess(),
            ]);
            
            return $result;
            
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            
            $this->logger?->error("Pipeline execution failed", [
                'execution_id' => $executionId,
                'tool' => $toolName,
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
            ]);
            
            return ToolResult::error($e->getMessage());
        }
    }
    
    private function generateExecutionId(): string
    {
        return bin2hex(random_bytes(8));
    }
}
```

### Using the Pipeline

```php
// Create pipeline
$router = new ToolRouter($registry, $logger);
$pipeline = new ToolExecutionPipeline($router, $logger);

// Add pre-execution rate limiting
$pipeline->addPreExecutionHook(function ($toolName, $input) use ($rateLimiter) {
    if (!$rateLimiter->allow($toolName)) {
        throw new \Exception("Rate limit exceeded for {$toolName}");
    }
});

// Add post-execution metrics
$pipeline->addPostExecutionHook(function ($toolName, $input, $result, $duration) use ($metrics) {
    $metrics->timing("tool.execution.{$toolName}", $duration * 1000);
    $metrics->increment("tool.calls.{$toolName}");
    
    if ($result->isError()) {
        $metrics->increment("tool.errors.{$toolName}");
    }
});

// Execute tool
$result = $pipeline->execute('database_query', ['query' => 'SELECT * FROM users']);
```

---

## Standardized Error Responses

Consistent error formats make debugging easier and enable better error handling.

### Error Response Standard

```php
<?php

/**
 * Standardized tool error with structured data.
 */
class ToolError
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly array $details = [],
        public readonly bool $retryable = false,
    ) {}
    
    public function toArray(): array
    {
        return [
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'details' => $this->details,
                'retryable' => $this->retryable,
            ],
        ];
    }
    
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
    
    public function toToolResult(): ToolResult
    {
        return ToolResult::error($this->toJson());
    }
}
```

### Error Code Standards

```php
class ToolErrorCode
{
    // Input errors (not retryable)
    public const INVALID_INPUT = 'INVALID_INPUT';
    public const MISSING_REQUIRED_FIELD = 'MISSING_REQUIRED_FIELD';
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    
    // Permission errors (not retryable)
    public const PERMISSION_DENIED = 'PERMISSION_DENIED';
    public const AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
    
    // Resource errors
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const RESOURCE_UNAVAILABLE = 'RESOURCE_UNAVAILABLE'; // retryable
    
    // Execution errors
    public const EXECUTION_TIMEOUT = 'EXECUTION_TIMEOUT'; // retryable
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED'; // retryable
    public const INTERNAL_ERROR = 'INTERNAL_ERROR'; // retryable
    
    // Network errors (retryable)
    public const NETWORK_ERROR = 'NETWORK_ERROR';
    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    
    /**
     * Check if an error code is retryable.
     */
    public static function isRetryable(string $code): bool
    {
        return in_array($code, [
            self::RESOURCE_UNAVAILABLE,
            self::EXECUTION_TIMEOUT,
            self::RATE_LIMIT_EXCEEDED,
            self::INTERNAL_ERROR,
            self::NETWORK_ERROR,
            self::SERVICE_UNAVAILABLE,
        ]);
    }
}
```

### Building Errors

```php
// Validation error
$error = new ToolError(
    code: ToolErrorCode::VALIDATION_FAILED,
    message: 'Input validation failed',
    details: [
        'fields' => ['email' => 'Invalid email format'],
    ],
    retryable: false
);

// Rate limit error (retryable)
$error = new ToolError(
    code: ToolErrorCode::RATE_LIMIT_EXCEEDED,
    message: 'Rate limit exceeded. Try again in 60 seconds.',
    details: [
        'retry_after' => 60,
        'limit' => 100,
        'window' => '1 minute',
    ],
    retryable: true
);

// Network error (retryable)
$error = new ToolError(
    code: ToolErrorCode::NETWORK_ERROR,
    message: 'Failed to connect to external service',
    details: [
        'service' => 'api.example.com',
        'timeout' => 30,
    ],
    retryable: true
);
```

---

## Retry Logic with Idempotency

Retries are essential for handling transient failures, but they must be safe—idempotency ensures repeated executions don't cause unintended side effects.

### Idempotency Key Generation

```php
class IdempotencyKeyGenerator
{
    /**
     * Generate an idempotency key from tool name and input.
     */
    public static function generate(string $toolName, array $input): string
    {
        // Normalize input for consistent hashing
        $normalized = self::normalizeInput($input);
        
        // Create hash
        $hash = hash('sha256', $toolName . json_encode($normalized));
        
        return substr($hash, 0, 32);
    }
    
    /**
     * Normalize input for consistent hashing.
     */
    private static function normalizeInput(array $input): array
    {
        // Sort keys
        ksort($input);
        
        // Recursively normalize nested arrays
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = self::normalizeInput($value);
            }
        }
        
        return $input;
    }
}
```

### Idempotency Cache

```php
class IdempotencyCache
{
    private array $cache = [];
    private int $ttl = 3600; // 1 hour
    
    /**
     * Check if we've seen this execution before and return cached result.
     */
    public function get(string $key): ?ToolResult
    {
        if (!isset($this->cache[$key])) {
            return null;
        }
        
        $entry = $this->cache[$key];
        
        // Check if expired
        if (time() > $entry['expires_at']) {
            unset($this->cache[$key]);
            return null;
        }
        
        return $entry['result'];
    }
    
    /**
     * Store execution result for idempotency.
     */
    public function set(string $key, ToolResult $result): void
    {
        $this->cache[$key] = [
            'result' => $result,
            'stored_at' => time(),
            'expires_at' => time() + $this->ttl,
        ];
    }
    
    /**
     * Clear expired entries.
     */
    public function cleanup(): void
    {
        $now = time();
        
        foreach ($this->cache as $key => $entry) {
            if ($now > $entry['expires_at']) {
                unset($this->cache[$key]);
            }
        }
    }
}
```

### Retry Pipeline with Idempotency

```php
class RetryableToolPipeline
{
    public function __construct(
        private ToolExecutionPipeline $pipeline,
        private IdempotencyCache $cache,
        private int $maxRetries = 3,
        private int $baseDelayMs = 1000,
        private ?LoggerInterface $logger = null,
    ) {}
    
    /**
     * Execute with retry logic and idempotency.
     */
    public function execute(string $toolName, array $input): ToolResult
    {
        // Generate idempotency key
        $idempotencyKey = IdempotencyKeyGenerator::generate($toolName, $input);
        
        // Check cache for previous execution
        $cached = $this->cache->get($idempotencyKey);
        if ($cached !== null) {
            $this->logger?->info("Returning cached result (idempotent)", [
                'tool' => $toolName,
                'idempotency_key' => $idempotencyKey,
            ]);
            
            return $cached;
        }
        
        // Execute with retries
        $attempt = 0;
        $delay = $this->baseDelayMs;
        
        while ($attempt < $this->maxRetries) {
            $attempt++;
            
            $result = $this->pipeline->execute($toolName, $input);
            
            // Success - cache and return
            if ($result->isSuccess()) {
                $this->cache->set($idempotencyKey, $result);
                return $result;
            }
            
            // Parse error to check if retryable
            $errorData = json_decode($result->getContent(), true);
            $isRetryable = false;
            
            if (isset($errorData['error']['code'])) {
                $isRetryable = ToolErrorCode::isRetryable($errorData['error']['code']);
            }
            
            // Not retryable - return error immediately
            if (!$isRetryable) {
                $this->logger?->info("Error not retryable, returning immediately", [
                    'tool' => $toolName,
                    'attempt' => $attempt,
                ]);
                
                return $result;
            }
            
            // Last attempt - return error
            if ($attempt >= $this->maxRetries) {
                $this->logger?->error("Max retries exceeded", [
                    'tool' => $toolName,
                    'attempts' => $attempt,
                ]);
                
                return $result;
            }
            
            // Retry with exponential backoff
            $this->logger?->warning("Tool execution failed, retrying", [
                'tool' => $toolName,
                'attempt' => $attempt,
                'max_retries' => $this->maxRetries,
                'delay_ms' => $delay,
            ]);
            
            usleep($delay * 1000);
            $delay *= 2; // Exponential backoff
        }
        
        // Should never reach here
        return ToolResult::error('Unexpected retry loop exit');
    }
}
```

---

## Execution Logging and Monitoring

Comprehensive logging makes debugging and monitoring practical.

### Structured Execution Log

```php
class ExecutionLogger
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}
    
    /**
     * Log execution start.
     */
    public function logStart(string $executionId, string $toolName, array $input): void
    {
        $this->logger->info("Tool execution started", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'input_size' => strlen(json_encode($input)),
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log execution completion.
     */
    public function logComplete(
        string $executionId,
        string $toolName,
        ToolResult $result,
        float $duration
    ): void {
        $this->logger->info("Tool execution completed", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'success' => $result->isSuccess(),
            'duration_ms' => round($duration * 1000, 2),
            'result_size' => strlen($result->getContent()),
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log execution error.
     */
    public function logError(
        string $executionId,
        string $toolName,
        \Throwable $error,
        float $duration
    ): void {
        $this->logger->error("Tool execution failed", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'error' => $error->getMessage(),
            'exception' => get_class($error),
            'duration_ms' => round($duration * 1000, 2),
            'trace' => $error->getTraceAsString(),
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log retry attempt.
     */
    public function logRetry(
        string $executionId,
        string $toolName,
        int $attempt,
        int $maxRetries,
        int $delayMs
    ): void {
        $this->logger->warning("Tool execution retry", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'attempt' => $attempt,
            'max_retries' => $maxRetries,
            'delay_ms' => $delayMs,
            'timestamp' => microtime(true),
        ]);
    }
}
```

### Metrics Collection

```php
class ToolMetrics
{
    private array $metrics = [];
    
    /**
     * Record a tool execution.
     */
    public function recordExecution(
        string $toolName,
        bool $success,
        float $durationMs,
        int $attempt = 1
    ): void {
        $this->metrics[] = [
            'type' => 'execution',
            'tool' => $toolName,
            'success' => $success,
            'duration_ms' => $durationMs,
            'attempt' => $attempt,
            'timestamp' => microtime(true),
        ];
    }
    
    /**
     * Get metrics for a specific tool.
     */
    public function getToolMetrics(string $toolName): array
    {
        $toolMetrics = array_filter(
            $this->metrics,
            fn($m) => $m['type'] === 'execution' && $m['tool'] === $toolName
        );
        
        $total = count($toolMetrics);
        $successful = count(array_filter($toolMetrics, fn($m) => $m['success']));
        $failed = $total - $successful;
        
        $durations = array_map(fn($m) => $m['duration_ms'], $toolMetrics);
        $avgDuration = !empty($durations) ? array_sum($durations) / count($durations) : 0;
        $maxDuration = !empty($durations) ? max($durations) : 0;
        $minDuration = !empty($durations) ? min($durations) : 0;
        
        return [
            'tool' => $toolName,
            'total_executions' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'avg_duration_ms' => round($avgDuration, 2),
            'max_duration_ms' => round($maxDuration, 2),
            'min_duration_ms' => round($minDuration, 2),
        ];
    }
    
    /**
     * Get overall metrics summary.
     */
    public function getSummary(): array
    {
        $executions = array_filter($this->metrics, fn($m) => $m['type'] === 'execution');
        
        $tools = array_unique(array_map(fn($m) => $m['tool'], $executions));
        $summary = [];
        
        foreach ($tools as $tool) {
            $summary[$tool] = $this->getToolMetrics($tool);
        }
        
        return $summary;
    }
}
```

---

## Parallel Tool Execution

Execute multiple tools concurrently for better performance.

### Simple Parallel Executor

```php
use ClaudeAgents\Async\ParallelToolExecutor;

// The framework provides ParallelToolExecutor
$executor = new ParallelToolExecutor($tools, ['logger' => $logger]);

// Execute multiple tool calls in parallel
$calls = [
    ['tool' => 'weather_api', 'input' => ['city' => 'San Francisco']],
    ['tool' => 'stock_api', 'input' => ['symbol' => 'AAPL']],
    ['tool' => 'news_api', 'input' => ['topic' => 'technology']],
];

$results = $executor->execute($calls);

foreach ($results as $result) {
    echo "Tool: {$result['tool']}\n";
    echo "Success: " . ($result['result']->isSuccess() ? 'Yes' : 'No') . "\n";
    echo "Content: {$result['result']->getContent()}\n\n";
}
```

### Batched Parallel Execution

```php
// Execute with concurrency limit
$executor = new ParallelToolExecutor($tools);

// Execute 20 tools with max 5 concurrent
$results = $executor->executeBatched($calls, concurrency: 5);
```

---

## Production-Ready Pipeline

Putting it all together—a complete production pipeline:

```php
<?php

use ClaudeAgents\Tools\ToolRegistry;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 1. Setup
$logger = new Logger('tools');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$registry = new ToolRegistry();
$metrics = new ToolMetrics();
$cache = new IdempotencyCache();

// 2. Register tools
$weatherTool = Tool::create('get_weather')
    ->description('Get current weather for a city')
    ->stringParam('city', 'City name', required: true)
    ->handler(function ($input) {
        // Simulate API call
        $city = $input['city'];
        return ToolResult::success("Weather in {$city}: 72°F, Sunny");
    });

$registry->register($weatherTool);

// 3. Build pipeline
$router = new SecureToolRouter($registry, $logger);

// Set permissions
$router->setPermissions('get_weather', ['user', 'admin']);

$pipeline = new ToolExecutionPipeline($router, $logger);

// Add rate limiting hook
$rateLimits = [];
$pipeline->addPreExecutionHook(function ($toolName, $input) use (&$rateLimits) {
    $key = $toolName;
    $now = time();
    
    // Allow 10 requests per minute
    $rateLimits[$key] = array_filter(
        $rateLimits[$key] ?? [],
        fn($timestamp) => $timestamp > $now - 60
    );
    
    if (count($rateLimits[$key]) >= 10) {
        throw new \Exception("Rate limit exceeded");
    }
    
    $rateLimits[$key][] = $now;
});

// Add metrics hook
$pipeline->addPostExecutionHook(function ($toolName, $input, $result, $duration) use ($metrics) {
    $metrics->recordExecution($toolName, $result->isSuccess(), $duration * 1000);
});

// 4. Create retryable pipeline
$retryablePipeline = new RetryableToolPipeline(
    pipeline: $pipeline,
    cache: $cache,
    maxRetries: 3,
    baseDelayMs: 1000,
    logger: $logger
);

// 5. Execute tools
$result = $retryablePipeline->execute('get_weather', ['city' => 'San Francisco']);

if ($result->isSuccess()) {
    echo "Result: {$result->getContent()}\n";
} else {
    echo "Error: {$result->getContent()}\n";
}

// 6. View metrics
$summary = $metrics->getSummary();
print_r($summary);
```

---

## Testing Your Pipeline

### Unit Testing Router

```php
use PHPUnit\Framework\TestCase;

class ToolRouterTest extends TestCase
{
    public function testRouteToExistingTool(): void
    {
        $registry = new ToolRegistry();
        $tool = Tool::create('test_tool')
            ->handler(fn($input) => ToolResult::success('success'));
        
        $registry->register($tool);
        
        $router = new ToolRouter($registry);
        $result = $router->route('test_tool', []);
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('success', $result->getContent());
    }
    
    public function testRouteToNonExistentTool(): void
    {
        $registry = new ToolRegistry();
        $router = new ToolRouter($registry);
        
        $result = $router->route('missing_tool', []);
        
        $this->assertTrue($result->isError());
        $this->assertStringContainsString('Unknown tool', $result->getContent());
    }
}
```

### Integration Testing Pipeline

```php
class PipelineTest extends TestCase
{
    public function testPipelineWithHooks(): void
    {
        $hookCalled = false;
        
        $registry = new ToolRegistry();
        $tool = Tool::create('test')
            ->handler(fn($input) => ToolResult::success('result'));
        $registry->register($tool);
        
        $router = new ToolRouter($registry);
        $pipeline = new ToolExecutionPipeline($router);
        
        $pipeline->addPostExecutionHook(function () use (&$hookCalled) {
            $hookCalled = true;
        });
        
        $result = $pipeline->execute('test', []);
        
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($hookCalled);
    }
}
```

---

## Summary

In this chapter, you learned how to build production-ready tool routing and execution pipelines:

✅ **Tool routing** — Find and dispatch tools with validation and permissions  
✅ **Execution pipeline** — Pre/post hooks, logging, metrics, error handling  
✅ **Error standardization** — Consistent error formats with codes and retryability  
✅ **Retry logic** — Safe retries with idempotency keys and exponential backoff  
✅ **Execution logging** — Comprehensive structured logging for debugging  
✅ **Parallel execution** — Concurrent tool calls with the framework's parallel executor  
✅ **Production patterns** — Complete pipeline with all best practices

With these patterns, your tool execution is reliable, observable, and production-ready.

---

## Practice Exercises

### Exercise 1: Build a Tool Router with Permissions

Create a router that:

- Checks user roles before executing tools
- Logs all permission checks
- Returns standardized errors

### Exercise 2: Implement Rate Limiting

Add rate limiting to the pipeline:

- Limit per tool (10 requests/minute)
- Return standardized rate limit error
- Include `retry_after` in error details

### Exercise 3: Add Idempotency to Database Tools

For a database update tool:

- Generate idempotency keys
- Cache successful executions
- Return cached results for retries

### Exercise 4: Build Metrics Dashboard

Create a metrics collector that tracks:

- Success/error rate per tool
- Average/min/max execution time
- Retry statistics
- Display as formatted report

---

## Next Steps

Now that you have production-ready tool execution, it's time to build stateful agents. In **Chapter 06: Stateful Conversations and Short-Term Memory**, you'll implement session storage, context windows, summarization, and transcript pruning to keep agents coherent over long interactions.

Continue to [Chapter 06](/series/agentic-ai-php-developers/chapters/06-stateful-conversations-and-short-term-memory) →

---

## Further Reading

- [`claude-php/claude-php-agent` ParallelToolExecutor](https://github.com/claude-php/claude-php-agent/blob/master/src/Async/ParallelToolExecutor.php)
- [`claude-php/claude-php-agent` ErrorHandler](https://github.com/claude-php/claude-php-agent/blob/master/src/Helpers/ErrorHandler.php)
- [Idempotency in Distributed Systems](https://www.ietf.org/rfc/rfc7231.html#section-4.2.2)
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html)
- [AMPHP: Asynchronous PHP](https://amphp.org/)
