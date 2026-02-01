---
title: "04: Agent Configuration and Best Practices"
description: "Configure agents with retry logic, logging, monitoring, and error handling. Set up production-ready patterns from the start."
series: "agentic-ai-php-developers"
chapter: 4
difficulty: "Intermediate"
keywords: ["Agent Configuration PHP", "Retry Logic", "Logging", "Monitoring", "Error Handling"]
teaches: ["Agent configuration patterns", "Retry strategies and backoff", "Structured logging and observability", "Error handling best practices", "Production-ready agent setup"]
estimatedTime: "PT90M"
---

# Chapter 04: Agent Configuration and Best Practices

## Overview

A well-configured agent is the difference between a prototype and a production system. In [`claude-php/agent`](https://github.com/claude-php/claude-php-agent), configuration controls everything from retry behavior and timeout limits to logging verbosity and model selection. Getting these settings right from the start saves debugging time and ensures your agents behave predictably under load.

In Chapters 01–03, you learned the fundamentals: agentic patterns, loop strategies, and tool design. Now we'll focus on making your agents production-ready. You'll master configuration APIs, implement robust retry logic, add structured logging, handle errors gracefully, and build agents that are observable, maintainable, and reliable.

In this chapter you'll:

- Master **agent configuration** with fluent builders and sensible defaults
- Implement **retry strategies** with exponential backoff and jitter
- Add **structured logging** for debugging, monitoring, and audit trails
- Handle **errors and failures** gracefully with circuit breakers and fallbacks
- Apply **production best practices** from day one: timeouts, rate limits, validation
- Build **observable agents** that emit metrics, traces, and diagnostic information

**Estimated time:** ~90 minutes

::: info Framework Version
This chapter is based on [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. Configuration patterns are tested against the actual framework.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`basic-configuration.php`](../../code/agentic-ai-php-developers/04-agent-configuration/basic-configuration.php) — Essential agent settings
- [`retry-strategies.php`](../../code/agentic-ai-php-developers/04-agent-configuration/retry-strategies.php) — Exponential backoff and jitter
- [`structured-logging.php`](../../code/agentic-ai-php-developers/04-agent-configuration/structured-logging.php) — PSR-3 logging integration
- [`error-handling.php`](../../code/agentic-ai-php-developers/04-agent-configuration/error-handling.php) — Exception handling patterns
- [`circuit-breaker.php`](../../code/agentic-ai-php-developers/04-agent-configuration/circuit-breaker.php) — Fault tolerance patterns
- [`production-agent.php`](../../code/agentic-ai-php-developers/04-agent-configuration/production-agent.php) — Full production-ready setup
- [`monitoring-integration.php`](../../code/agentic-ai-php-developers/04-agent-configuration/monitoring-integration.php) — Metrics and observability

All files are in [`code/agentic-ai-php-developers/04-agent-configuration/`](../../code/agentic-ai-php-developers/04-agent-configuration/README.md).
:::

---

## Agent Configuration Fundamentals

Every agent in `claude-php/agent` is built using a fluent configuration API. Understanding the available options and their defaults helps you make informed choices.

### Core Configuration Options

```php
use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$agent = Agent::create($client)
    // Model selection
    ->model('claude-sonnet-4-5')
    
    // Execution limits
    ->maxIterations(10)
    ->timeout(120) // seconds
    
    // System instructions
    ->withSystemPrompt('You are a helpful assistant.')
    
    // Temperature (0.0 = deterministic, 1.0 = creative)
    ->temperature(0.7)
    
    // Max tokens for response
    ->maxTokens(4096)
    
    // Loop strategy
    ->withLoopStrategy(new ReactLoop());
```

### Configuration Categories

Agent configuration falls into several key areas:

| Category | Options | Purpose |
| --- | --- | --- |
| **Execution** | `maxIterations`, `timeout`, `maxRetries` | Control loop limits and retries |
| **Model** | `model`, `temperature`, `maxTokens` | LLM behavior and constraints |
| **Tools** | `withTool()`, `withTools()`, `toolChoice` | Available tool set |
| **Memory** | `withMemory()`, context window | State management |
| **Observability** | Logger, callbacks, events | Debugging and monitoring |

---

## Model Selection and Parameters

Choosing the right model and parameters affects performance, cost, and output quality.

### Model Options

The `claude-php/agent` framework supports all Claude models:

```php
// Sonnet 4.5 (default) — Best balance of performance and cost
$agent->model('claude-sonnet-4-5');

// Opus 4.5 — Highest intelligence, best for complex reasoning
$agent->model('claude-opus-4-5');

// Haiku 4.5 — Fastest and cheapest, great for simple tasks
$agent->model('claude-haiku-4-5');
```

::: tip Model Selection Guide
- **Sonnet 4.5**: Default for most tasks, excellent balance
- **Opus 4.5**: Complex multi-step reasoning, code generation, analysis
- **Haiku 4.5**: Simple tool routing, classification, high-volume tasks
:::

### Temperature and Creativity

Temperature controls randomness in responses:

```php
// Deterministic, consistent outputs (0.0 - 0.3)
$agent->temperature(0.0); // Best for tool routing, classification

// Balanced creativity (0.4 - 0.7)
$agent->temperature(0.5); // General conversational tasks

// High creativity (0.8 - 1.0)
$agent->temperature(0.9); // Creative writing, brainstorming
```

::: warning Production Recommendation
For production agents, use **temperature 0.0–0.3** to ensure consistent, predictable behavior.
:::

### Token Limits

Control response length with `maxTokens`:

```php
// Short responses (chat, tool routing)
$agent->maxTokens(1024);

// Medium responses (default)
$agent->maxTokens(4096);

// Long-form content (essays, code generation)
$agent->maxTokens(8192);
```

---

## Execution Limits and Timeouts

Prevent runaway loops and unbounded execution with sensible limits.

### Maximum Iterations

Limit the number of loop iterations:

```php
$agent->maxIterations(5); // Simple tasks (1-3 tool calls)
$agent->maxIterations(10); // Complex workflows (5-8 tool calls)
$agent->maxIterations(20); // Multi-stage planning (rare)
```

**What happens when the limit is reached?**

The agent returns the best answer it has at that point, along with metadata indicating the iteration limit was hit.

```php
$result = $agent->run('Complex task...');

if ($result->hitIterationLimit()) {
    echo "Agent reached max iterations without fully completing task\n";
    // Handle partial completion
}
```

### Timeouts

Set overall execution time limits:

```php
// Quick tasks (API lookups, simple calculations)
$agent->timeout(30);

// Standard workflows
$agent->timeout(120);

// Long-running analysis
$agent->timeout(300);
```

::: danger Production Best Practice
**Always set timeouts** in production to prevent hanging requests and resource exhaustion.
:::

---

## Retry Logic and Backoff Strategies

Network failures, rate limits, and transient errors are inevitable. Robust retry logic makes agents resilient.

### Basic Retry Configuration

```php
use ClaudeAgents\Agent;
use ClaudeAgents\Retry\RetryStrategy;

$agent = Agent::create($client)
    ->maxRetries(3)
    ->retryStrategy(new ExponentialBackoff(
        baseDelay: 1000, // 1 second
        maxDelay: 30000, // 30 seconds
        multiplier: 2.0
    ));
```

### Exponential Backoff

The most common retry pattern—delays double after each failure:

```php
use ClaudeAgents\Retry\ExponentialBackoff;

// Retry 1: wait 1s
// Retry 2: wait 2s
// Retry 3: wait 4s
// Retry 4: wait 8s

$backoff = new ExponentialBackoff(
    baseDelay: 1000,
    maxDelay: 60000,
    multiplier: 2.0
);

$agent->retryStrategy($backoff);
```

### Adding Jitter

Jitter randomizes delays to prevent thundering herd problems:

```php
use ClaudeAgents\Retry\ExponentialBackoffWithJitter;

// Adds ±25% randomness to each delay
$jitter = new ExponentialBackoffWithJitter(
    baseDelay: 1000,
    maxDelay: 30000,
    multiplier: 2.0,
    jitterFactor: 0.25 // ±25%
);

$agent->retryStrategy($jitter);
```

**Example delays with jitter:**

```text
Retry 1: 750ms - 1250ms (1000ms ± 25%)
Retry 2: 1500ms - 2500ms (2000ms ± 25%)
Retry 3: 3000ms - 5000ms (4000ms ± 25%)
```

### Selective Retries

Only retry specific exceptions:

```php
use ClaudeAgents\Retry\RetryPolicy;
use ClaudePhp\Exceptions\RateLimitException;
use ClaudePhp\Exceptions\ServerException;

$policy = RetryPolicy::create()
    ->retryOn([
        RateLimitException::class,
        ServerException::class,
    ])
    ->dontRetryOn([
        InvalidRequestException::class, // Bad input, no point retrying
        AuthenticationException::class, // Auth failed, fix config
    ]);

$agent->retryPolicy($policy);
```

### Complete Retry Example

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Retry\ExponentialBackoffWithJitter;
use ClaudeAgents\Retry\RetryPolicy;
use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\RateLimitException;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$agent = Agent::create($client)
    ->maxRetries(5)
    ->retryStrategy(new ExponentialBackoffWithJitter(
        baseDelay: 1000,
        maxDelay: 60000,
        multiplier: 2.0,
        jitterFactor: 0.25
    ))
    ->retryPolicy(
        RetryPolicy::create()
            ->retryOn([RateLimitException::class])
            ->maxAttempts(5)
    )
    ->onRetry(function ($attempt, $exception, $delay) {
        echo sprintf(
            "Retry %d after %dms due to: %s\n",
            $attempt,
            $delay,
            $exception->getMessage()
        );
    });
```

---

## Structured Logging and Observability

Logs are your window into agent behavior. Structured logging makes debugging, monitoring, and auditing practical.

### PSR-3 Logger Integration

`claude-php/agent` supports [PSR-3](https://www.php-fig.org/psr/psr-3/) loggers (Monolog, etc.):

```php
use ClaudeAgents\Agent;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

// Create logger
$logger = new Logger('agent');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushHandler(new RotatingFileHandler('/var/log/agent.log', 7, Logger::INFO));

// Attach to agent
$agent = Agent::create($client)
    ->withLogger($logger);
```

### What Gets Logged

The framework logs key events at different levels:

| Level | Events |
| --- | --- |
| **DEBUG** | Tool input/output, loop iterations, LLM requests |
| **INFO** | Agent start/stop, tool execution, task completion |
| **WARNING** | Retries, rate limits, validation errors |
| **ERROR** | Tool failures, API errors, unhandled exceptions |
| **CRITICAL** | System failures, circuit breaker open |

### Structured Log Context

Add context to every log entry:

```php
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;

$logger = new Logger('agent');

// Add unique request ID
$logger->pushProcessor(new UidProcessor());

// Add web context ($_SERVER vars)
$logger->pushProcessor(new WebProcessor());

// Add custom context
$logger->pushProcessor(function ($record) {
    $record['extra']['environment'] = getenv('APP_ENV');
    $record['extra']['service'] = 'agentic-api';
    return $record;
});

$agent = Agent::create($client)->withLogger($logger);
```

### Log Output Example

```json
{
  "message": "Agent task completed",
  "context": {
    "task": "Calculate quarterly revenue",
    "iterations": 4,
    "tools_used": ["database_query", "calculator"],
    "duration_ms": 2341
  },
  "level": "INFO",
  "datetime": "2026-02-01T10:23:45.123456+00:00",
  "extra": {
    "uid": "f4a3b2c1",
    "environment": "production",
    "service": "agentic-api"
  }
}
```

### Custom Logging Callbacks

Hook into agent lifecycle events:

```php
$agent = Agent::create($client)
    ->onToolCall(function ($tool, $input) use ($logger) {
        $logger->debug('Tool called', [
            'tool' => $tool->name,
            'input' => $input,
        ]);
    })
    ->onToolResult(function ($tool, $result) use ($logger) {
        $logger->debug('Tool completed', [
            'tool' => $tool->name,
            'success' => $result->isSuccess(),
            'execution_time_ms' => $result->executionTime(),
        ]);
    })
    ->onIterationComplete(function ($iteration, $state) use ($logger) {
        $logger->info('Iteration completed', [
            'iteration' => $iteration,
            'tokens_used' => $state->tokensUsed(),
        ]);
    });
```

---

## Error Handling Patterns

Robust error handling prevents cascading failures and provides clear diagnostics.

### Try-Catch at the Agent Level

```php
use ClaudeAgents\Exceptions\AgentException;
use ClaudeAgents\Exceptions\ToolExecutionException;
use ClaudePhp\Exceptions\ApiException;

try {
    $result = $agent->run('Process user request');
    
    if ($result->isSuccess()) {
        return $result->getAnswer();
    } else {
        // Agent completed but didn't succeed
        $logger->warning('Agent task failed', [
            'reason' => $result->getError()
        ]);
    }
    
} catch (ToolExecutionException $e) {
    // A tool failed to execute
    $logger->error('Tool execution failed', [
        'tool' => $e->getToolName(),
        'error' => $e->getMessage(),
    ]);
    
} catch (ApiException $e) {
    // Claude API error (rate limit, network, etc.)
    $logger->error('API error', [
        'status_code' => $e->getStatusCode(),
        'error' => $e->getMessage(),
    ]);
    
} catch (AgentException $e) {
    // Generic agent framework error
    $logger->error('Agent error', ['error' => $e->getMessage()]);
    
} catch (\Exception $e) {
    // Unexpected error
    $logger->critical('Unexpected error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}
```

### Graceful Degradation

Provide fallback behavior when agents fail:

```php
function processRequest(string $userInput): string
{
    try {
        $result = $agent->run($userInput);
        return $result->getAnswer();
        
    } catch (RateLimitException $e) {
        // Rate limited—queue for later
        queueForRetry($userInput);
        return "Your request is being processed. Please check back in a moment.";
        
    } catch (ToolExecutionException $e) {
        // Tool failed—try simpler approach
        return fallbackSimpleResponse($userInput);
        
    } catch (\Exception $e) {
        // Unknown error—safe default
        logError($e);
        return "I'm having trouble processing your request. Please try again.";
    }
}
```

### Tool-Level Error Handling

Handle errors within tool handlers:

```php
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

$databaseTool = Tool::create('query_database')
    ->description('Query the user database')
    ->parameter('query', 'string', 'SQL query to execute')
    ->required('query')
    ->handler(function (array $input) use ($db, $logger) {
        try {
            $result = $db->query($input['query']);
            
            return ToolResult::success([
                'rows' => $result->fetchAll(),
                'count' => $result->rowCount(),
            ]);
            
        } catch (\PDOException $e) {
            $logger->error('Database query failed', [
                'query' => $input['query'],
                'error' => $e->getMessage(),
            ]);
            
            return ToolResult::error(
                'Database query failed: ' . $e->getMessage()
            );
        }
    });
```

---

## Circuit Breaker Pattern

Prevent cascading failures by temporarily disabling failing components.

### What is a Circuit Breaker?

A circuit breaker monitors failures and "opens" (stops trying) after a threshold, giving the system time to recover.

**States:**

1. **Closed** — Normal operation, requests flow through
2. **Open** — Too many failures, all requests fail fast
3. **Half-Open** — Test if the system has recovered

### Basic Circuit Breaker

```php
class CircuitBreaker
{
    private int $failures = 0;
    private string $state = 'closed'; // closed, open, half-open
    private ?int $openedAt = null;
    
    public function __construct(
        private int $failureThreshold = 5,
        private int $recoveryTimeout = 60, // seconds
    ) {}
    
    public function call(callable $fn): mixed
    {
        if ($this->state === 'open') {
            if (time() - $this->openedAt > $this->recoveryTimeout) {
                $this->state = 'half-open';
            } else {
                throw new \Exception('Circuit breaker is open');
            }
        }
        
        try {
            $result = $fn();
            $this->onSuccess();
            return $result;
            
        } catch (\Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }
    
    private function onSuccess(): void
    {
        $this->failures = 0;
        $this->state = 'closed';
    }
    
    private function onFailure(): void
    {
        $this->failures++;
        
        if ($this->failures >= $this->failureThreshold) {
            $this->state = 'open';
            $this->openedAt = time();
        }
    }
}
```

### Using Circuit Breaker with Agents

```php
$breaker = new CircuitBreaker(
    failureThreshold: 3,
    recoveryTimeout: 30
);

try {
    $result = $breaker->call(fn() => $agent->run($userInput));
    echo $result->getAnswer();
    
} catch (\Exception $e) {
    if ($e->getMessage() === 'Circuit breaker is open') {
        // Fail fast—don't even try
        return "Service temporarily unavailable";
    }
    throw $e;
}
```

---

## Rate Limiting and Throttling

Protect APIs and control costs by limiting request rates.

### Simple Rate Limiter

```php
class RateLimiter
{
    private array $requests = [];
    
    public function __construct(
        private int $maxRequests,
        private int $windowSeconds,
    ) {}
    
    public function allowRequest(string $key): bool
    {
        $now = time();
        $windowStart = $now - $this->windowSeconds;
        
        // Remove old requests
        $this->requests[$key] = array_filter(
            $this->requests[$key] ?? [],
            fn($timestamp) => $timestamp > $windowStart
        );
        
        // Check limit
        if (count($this->requests[$key]) >= $this->maxRequests) {
            return false;
        }
        
        // Allow request
        $this->requests[$key][] = $now;
        return true;
    }
}
```

### Rate-Limited Agent Wrapper

```php
function rateLimitedAgentRun(
    Agent $agent,
    string $input,
    RateLimiter $limiter,
    string $userId
): AgentResult {
    if (!$limiter->allowRequest($userId)) {
        throw new \Exception('Rate limit exceeded for user: ' . $userId);
    }
    
    return $agent->run($input);
}

// Usage
$limiter = new RateLimiter(maxRequests: 10, windowSeconds: 60);

try {
    $result = rateLimitedAgentRun($agent, $userInput, $limiter, $userId);
    echo $result->getAnswer();
    
} catch (\Exception $e) {
    echo "Rate limit exceeded. Please try again in a moment.";
}
```

---

## Production-Ready Agent Template

Putting it all together—a fully configured production agent:

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Retry\ExponentialBackoffWithJitter;
use ClaudeAgents\Retry\RetryPolicy;
use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\RateLimitException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\UidProcessor;

// Configure logger
$logger = new Logger('agent');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
$logger->pushHandler(new RotatingFileHandler(
    filename: '/var/log/agent.log',
    maxFiles: 30,
    level: Logger::DEBUG
));
$logger->pushProcessor(new UidProcessor());

// Create Claude client
$client = new ClaudePhp(
    apiKey: getenv('ANTHROPIC_API_KEY'),
    timeout: 120
);

// Configure agent
$agent = Agent::create($client)
    // Model and parameters
    ->model('claude-sonnet-4-5')
    ->temperature(0.1) // Low temperature for consistency
    ->maxTokens(4096)
    
    // Execution limits
    ->maxIterations(10)
    ->timeout(120)
    
    // Retry strategy
    ->maxRetries(5)
    ->retryStrategy(new ExponentialBackoffWithJitter(
        baseDelay: 1000,
        maxDelay: 60000,
        multiplier: 2.0,
        jitterFactor: 0.25
    ))
    ->retryPolicy(
        RetryPolicy::create()
            ->retryOn([RateLimitException::class])
            ->maxAttempts(5)
    )
    
    // Logging
    ->withLogger($logger)
    
    // Loop strategy
    ->withLoopStrategy(new ReactLoop())
    
    // System prompt
    ->withSystemPrompt(
        'You are a helpful assistant. Be concise and accurate. ' .
        'Use tools when needed. Always cite sources.'
    )
    
    // Lifecycle callbacks
    ->onToolCall(function ($tool, $input) use ($logger) {
        $logger->info('Tool called', [
            'tool' => $tool->name,
            'input_size' => strlen(json_encode($input)),
        ]);
    })
    ->onIterationComplete(function ($iteration, $state) use ($logger) {
        $logger->debug('Iteration completed', [
            'iteration' => $iteration,
            'tokens_used' => $state->tokensUsed(),
        ]);
    });

// Error handling wrapper
function runAgentSafely(Agent $agent, string $input): string
{
    global $logger;
    
    try {
        $result = $agent->run($input);
        
        if ($result->isSuccess()) {
            $logger->info('Agent task completed successfully', [
                'iterations' => $result->iterations(),
                'tokens' => $result->tokensUsed(),
            ]);
            return $result->getAnswer();
        } else {
            $logger->warning('Agent task failed', [
                'error' => $result->getError()
            ]);
            return "I couldn't complete that task: " . $result->getError();
        }
        
    } catch (\Exception $e) {
        $logger->error('Agent execution failed', [
            'error' => $e->getMessage(),
            'class' => get_class($e),
        ]);
        return "An error occurred. Please try again.";
    }
}

// Usage
$response = runAgentSafely($agent, 'What is the weather in San Francisco?');
echo $response . "\n";
```

---

## Configuration Best Practices

### Development vs. Production

Adjust settings based on environment:

```php
$isProduction = getenv('APP_ENV') === 'production';

$agent = Agent::create($client)
    ->temperature($isProduction ? 0.1 : 0.5)
    ->maxIterations($isProduction ? 10 : 20)
    ->timeout($isProduction ? 120 : 300)
    ->withLogger($isProduction ? $prodLogger : $devLogger);
```

### Configuration Management

Store configuration in environment variables or config files:

```php
// config/agent.php
return [
    'model' => getenv('AGENT_MODEL') ?: 'claude-sonnet-4-5',
    'temperature' => (float) getenv('AGENT_TEMPERATURE') ?: 0.1,
    'max_iterations' => (int) getenv('AGENT_MAX_ITERATIONS') ?: 10,
    'timeout' => (int) getenv('AGENT_TIMEOUT') ?: 120,
    'max_retries' => (int) getenv('AGENT_MAX_RETRIES') ?: 5,
];

// Usage
$config = require 'config/agent.php';

$agent = Agent::create($client)
    ->model($config['model'])
    ->temperature($config['temperature'])
    ->maxIterations($config['max_iterations'])
    ->timeout($config['timeout'])
    ->maxRetries($config['max_retries']);
```

### Configuration Validation

Validate configuration before creating agents:

```php
function validateAgentConfig(array $config): void
{
    assert($config['temperature'] >= 0.0 && $config['temperature'] <= 1.0, 'Invalid temperature');
    assert($config['max_iterations'] > 0, 'Max iterations must be positive');
    assert($config['timeout'] > 0, 'Timeout must be positive');
    assert(in_array($config['model'], [
        'claude-sonnet-4-5',
        'claude-opus-4-5',
        'claude-haiku-4-5',
    ]), 'Invalid model');
}

$config = require 'config/agent.php';
validateAgentConfig($config);
```

---

## Monitoring and Metrics

Track agent performance and health in production.

### Key Metrics to Monitor

| Metric | What it Measures | Alert Threshold |
| --- | --- | --- |
| **Latency** | Time to complete tasks | p95 > 10s |
| **Error Rate** | Failed tasks / total tasks | > 5% |
| **Token Usage** | Tokens per request | > 8000 |
| **Iteration Count** | Loops per task | > 15 |
| **Retry Rate** | Retries / total requests | > 10% |
| **Tool Failures** | Tool errors / tool calls | > 2% |

### Simple Metrics Collector

```php
class MetricsCollector
{
    private array $metrics = [];
    
    public function record(string $name, float $value, array $tags = []): void
    {
        $this->metrics[] = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => microtime(true),
        ];
    }
    
    public function timing(string $name, callable $fn, array $tags = []): mixed
    {
        $start = microtime(true);
        $result = $fn();
        $duration = (microtime(true) - $start) * 1000; // ms
        
        $this->record($name, $duration, $tags);
        return $result;
    }
    
    public function flush(): void
    {
        // Send to monitoring system (Prometheus, DataDog, etc.)
        foreach ($this->metrics as $metric) {
            // Implementation depends on your monitoring stack
        }
        
        $this->metrics = [];
    }
}
```

### Instrumented Agent Wrapper

```php
function monitoredAgentRun(
    Agent $agent,
    string $input,
    MetricsCollector $metrics
): AgentResult {
    return $metrics->timing('agent.run', function () use ($agent, $input, $metrics) {
        try {
            $result = $agent->run($input);
            
            // Record success metrics
            $metrics->record('agent.success', 1);
            $metrics->record('agent.iterations', $result->iterations());
            $metrics->record('agent.tokens', $result->tokensUsed());
            
            return $result;
            
        } catch (\Exception $e) {
            // Record error metrics
            $metrics->record('agent.error', 1, [
                'error_type' => get_class($e)
            ]);
            throw $e;
        }
    }, ['model' => $agent->getModel()]);
}
```

---

## Testing Configuration

Validate your configuration with automated tests.

### Configuration Tests

```php
use PHPUnit\Framework\TestCase;

class AgentConfigurationTest extends TestCase
{
    public function testProductionConfiguration(): void
    {
        $config = require __DIR__ . '/../config/agent.php';
        
        // Validate temperature
        $this->assertGreaterThanOrEqual(0.0, $config['temperature']);
        $this->assertLessThanOrEqual(1.0, $config['temperature']);
        
        // Validate limits
        $this->assertGreaterThan(0, $config['max_iterations']);
        $this->assertLessThanOrEqual(20, $config['max_iterations']);
        
        // Validate timeout
        $this->assertGreaterThan(0, $config['timeout']);
        $this->assertLessThanOrEqual(300, $config['timeout']);
    }
    
    public function testAgentCreationWithConfig(): void
    {
        $config = require __DIR__ . '/../config/agent.php';
        $client = new ClaudePhp(apiKey: 'test-key');
        
        $agent = Agent::create($client)
            ->model($config['model'])
            ->temperature($config['temperature'])
            ->maxIterations($config['max_iterations']);
        
        $this->assertInstanceOf(Agent::class, $agent);
    }
}
```

---

## Summary

In this chapter, you learned how to configure agents for production:

✅ **Configuration fundamentals** — Model selection, execution limits, temperature  
✅ **Retry strategies** — Exponential backoff, jitter, selective retries  
✅ **Structured logging** — PSR-3 integration, context, lifecycle callbacks  
✅ **Error handling** — Try-catch patterns, graceful degradation, tool errors  
✅ **Circuit breakers** — Preventing cascading failures  
✅ **Rate limiting** — Protecting APIs and controlling costs  
✅ **Production templates** — Complete agent setup with all best practices  
✅ **Monitoring** — Metrics, observability, performance tracking

With these patterns, your agents are ready for production workloads.

---

## Practice Exercises

### Exercise 1: Configure a Production Agent

Create a production-ready agent with:

- Model: `claude-sonnet-4-5`
- Temperature: `0.1`
- Max iterations: `10`
- Timeout: `120s`
- Retry strategy with exponential backoff
- Structured logging with Monolog
- Error handling with graceful fallbacks

**Starter code:**

```php
// Your implementation here
$agent = Agent::create($client)
    // Add configuration...
;
```

### Exercise 2: Implement Rate Limiting

Build a rate limiter that allows 10 requests per minute per user.

**Requirements:**

- Track requests by user ID
- Return `false` when limit exceeded
- Clean up old request timestamps

### Exercise 3: Add Circuit Breaker

Wrap agent execution with a circuit breaker:

- Open after 3 consecutive failures
- Recovery timeout: 30 seconds
- Test with a half-open state

### Exercise 4: Monitor Agent Performance

Track these metrics:

- Average response time
- Error rate
- Token usage per request
- Iteration count distribution

---

## Next Steps

Now that your agents are production-ready, it's time to build the core primitives. In **Chapter 05: Tool Routing and Execution Pipelines**, you'll create a tool router that safely dispatches tool calls, logs executions, standardizes errors, and implements retries.

Continue to [Chapter 05](/series/agentic-ai-php-developers/chapters/05-tool-routing-and-execution-pipelines) →

---

## Further Reading

- [Anthropic API Best Practices](https://docs.anthropic.com/en/docs/build-with-claude/best-practices)
- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html)
- [Exponential Backoff and Jitter](https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/)
