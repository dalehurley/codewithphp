# Chapter 05: Tool Routing and Execution Pipelines - Code Examples

Complete, runnable examples demonstrating tool routing, execution pipelines, retry logic, logging, and production-ready patterns.

## Prerequisites

```bash
composer require claude-php/agent
composer require monolog/monolog
```

## Examples Overview

### 1. `tool-router.php`
**Basic tool routing and dispatching**

Demonstrates:
- Tool registry and routing
- Error handling for missing tools
- Input validation
- Logging tool executions

```bash
php tool-router.php
```

### 2. `execution-pipeline.php`
**Complete execution pipeline with hooks**

Demonstrates:
- Secure router with permissions
- Pre-execution validation hooks
- Post-execution callbacks
- Execution timing and metrics
- Rate limiting

```bash
php execution-pipeline.php
```

### 3. `error-standardization.php`
**Standardized error responses**

Demonstrates:
- Error code standards (INVALID_INPUT, RATE_LIMIT_EXCEEDED, etc.)
- Structured error format
- Retryable vs non-retryable errors
- Error details and context
- Creating errors from exceptions

```bash
php error-standardization.php
```

### 4. `retry-with-idempotency.php`
**Retry logic with idempotency keys**

Demonstrates:
- Idempotency key generation
- Caching execution results
- Safe retries without side effects
- Exponential backoff
- Retryable error detection

```bash
php retry-with-idempotency.php
```

### 5. `execution-logging.php`
**Comprehensive logging patterns**

Demonstrates:
- Structured logging with context
- Execution lifecycle logging
- Performance metrics collection
- Error tracking and debugging
- Log filtering and analysis

```bash
php execution-logging.php
```

### 6. `parallel-execution.php`
**Concurrent tool execution (simplified)**

Demonstrates:
- Parallel execution patterns
- Batched execution with concurrency limits
- Performance comparison (sequential vs parallel)
- Error handling in parallel execution

**Note:** This is a simplified demo. For true async execution, use `claude-php/agent`'s `ParallelToolExecutor` with AMPHP.

```bash
php parallel-execution.php
```

### 7. `production-pipeline.php`
**Complete production-ready setup**

Integrates all patterns:
- Router + Pipeline + Retry + Logging + Metrics
- Permission-based access control
- Rate limiting hooks
- Idempotency caching
- Comprehensive error handling
- Metrics reporting

```bash
php production-pipeline.php
```

## Running All Examples

```bash
# Run all examples
for file in *.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Key Concepts Demonstrated

### Tool Routing
- Finding tools by name
- Validating tool availability
- Permission checks
- Input validation

### Execution Pipeline
- Pre-execution hooks (validation, rate limiting)
- Tool execution with error handling
- Post-execution hooks (metrics, caching)
- Lifecycle logging

### Error Standardization
- Consistent error format with codes
- Retryable vs non-retryable classification
- Error details and context
- Exception handling

### Retry Logic
- Idempotency key generation
- Result caching
- Exponential backoff
- Selective retries based on error type

### Logging & Monitoring
- Structured logs with context
- Execution metrics (duration, success rate)
- Performance tracking
- Audit trails

### Production Patterns
- All patterns integrated
- Real-world usage examples
- Complete error handling
- Observable execution

## Integration with claude-php/agent

These examples demonstrate patterns that extend `claude-php/agent`:

```php
use ClaudeAgents\Tools\ToolRegistry;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use ClaudeAgents\Async\ParallelToolExecutor;

// Your production pipeline wraps agent tools
$registry = new ToolRegistry();

// Register framework tools
$registry->register($weatherTool);
$registry->register($databaseTool);

// Use framework's parallel executor
$executor = new ParallelToolExecutor(
    $registry->all(),
    ['logger' => $logger]
);
```

## Common Patterns

### Rate Limiting Hook
```php
$pipeline->addPreExecutionHook(function ($toolName, $input) use ($limiter) {
    if (!$limiter->allow($toolName)) {
        throw new \Exception("Rate limit exceeded");
    }
});
```

### Metrics Hook
```php
$pipeline->addPostExecutionHook(function ($toolName, $input, $result, $duration) use ($metrics) {
    $metrics->recordExecution($toolName, $result->isSuccess(), $duration * 1000);
});
```

### Idempotent Execution
```php
$key = hash('sha256', $toolName . json_encode($input));

if ($cached = $cache->get($key)) {
    return $cached;
}

$result = $tool->execute($input);
$cache->set($key, $result);
```

## Next Steps

After mastering these patterns:

1. **Chapter 06**: Implement stateful conversations with short-term memory
2. **Chapter 07**: Add long-term memory with datastores
3. **Chapter 08**: Build RAG pipelines for grounded responses

## Troubleshooting

**Composer autoload not found?**
```bash
composer install
```

**Permission errors?**
```bash
chmod +x *.php
```

**Want to see framework source?**
Check the `claude-php/agent` repository for actual implementations:
- `src/Tools/ToolRegistry.php`
- `src/Async/ParallelToolExecutor.php`
- `src/Helpers/ErrorHandler.php`

## Additional Resources

- [Chapter 05 Tutorial](/series/agentic-ai-php-developers/chapters/05-tool-routing-and-execution-pipelines)
- [claude-php/agent Framework](https://github.com/claude-php/claude-php-agent)
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html)
- [Exponential Backoff](https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/)
