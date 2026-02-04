---
title: "16: Observability — Logs, Traces, and Metrics"
description: "Instrument every agent step. Track tokens, tool calls, latency, and failure rates for real production monitoring."
series: "agentic-ai-php-developers"
chapter: 16
difficulty: "Advanced"
keywords: ["PHP Observability", "Distributed Tracing", "Metrics Collection", "Structured Logging", "OpenTelemetry PHP"]
teaches: ["Structured logging with PSR-3", "Distributed tracing with spans", "Metrics collection and aggregation", "OpenTelemetry integration", "Production monitoring patterns", "External tracing backends"]
estimatedTime: "PT120M"
---

# Chapter 16: Observability — Logs, Traces, and Metrics

## Overview

You've built intelligent agents. Now you need to understand what they're doing in production. **Observability** — the practice of instrumenting systems to expose their internal state — is what separates prototypes from production systems. Without it, you're flying blind: guessing at performance bottlenecks, missing critical errors, and unable to optimize costs.

In this chapter, you'll learn to instrument agents with production-grade observability using [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent)'s built-in observability infrastructure. You'll add structured logging with trace correlation, implement distributed tracing with parent-child spans, collect metrics for dashboards and alerts, and export telemetry to industry-standard backends like OpenTelemetry, LangSmith, and LangFuse.

In this chapter you'll:

- Implement **structured logging** with PSR-3 loggers and automatic context enrichment
- Build **distributed tracing** systems with spans, trace IDs, and parent-child relationships
- Collect **operational metrics** for requests, tokens, latency, and errors
- Integrate **OpenTelemetry** for industry-standard telemetry export
- Connect to **external observability platforms** (LangSmith, LangFuse, Arize Phoenix)
- Design **production monitoring** dashboards and alerting systems
- Apply **observability best practices** for cost, performance, and reliability

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. All observability features are built into the framework.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`01-basic-structured-logging.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics/01-basic-structured-logging.php) — PSR-3 logging with agents
- [`02-observability-logger.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics/02-observability-logger.php) — Trace-aware structured logging
- [`03-distributed-tracing.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics/03-distributed-tracing.php) — Hierarchical spans and traces
- [`04-metrics-collection.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics/04-metrics-collection.php) — Request, token, and latency metrics
- [`05-metrics-aggregator.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics/05-metrics-aggregator.php) — Advanced metrics with aggregation
- [`05-telemetry-service.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics/05-telemetry-service.php) — OpenTelemetry-style metrics
- [`06-comprehensive-observability.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics/06-comprehensive-observability.php) — Complete observability stack

All files are in [`code/16-observability-logs-traces-metrics/`](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/16-observability-logs-traces-metrics).
:::

---

## The Three Pillars of Observability

Modern observability is built on three pillars:

```text
┌─────────────────────────────────────────────────────────┐
│                  OBSERVABILITY                          │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  📝 LOGS          📊 TRACES          📈 METRICS         │
│                                                         │
│  What happened?   How did it flow?   How is it doing?  │
│  Discrete events  Request paths      Aggregated stats  │
│  Full context     Parent-child       Time series       │
│  Human-readable   Latency analysis   Alerting          │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### 1. Logs — What Happened

**Structured logs** record discrete events with context:

- Agent started/completed
- Tool executed successfully/failed
- Error occurred with stack trace
- User action triggered

**Key Properties:**
- Rich context (user ID, session, trace ID)
- Severity levels (DEBUG, INFO, ERROR)
- Searchable and filterable
- Retained for audit trails

### 2. Traces — How Did It Flow

**Distributed traces** show request paths through your system:

- Parent span: Agent execution
- Child span: Tool call
- Grandchild span: API request

**Key Properties:**
- Unique trace ID across all operations
- Parent-child span relationships
- Timing and duration for each span
- Critical path analysis

### 3. Metrics — How Is It Doing

**Metrics** are aggregated numerical data over time:

- Request count (counter)
- Active requests (gauge)
- Latency distribution (histogram)
- Token usage (counter)

**Key Properties:**
- Efficient storage (aggregated)
- Real-time dashboards
- Threshold-based alerting
- Trend analysis

---

## Structured Logging Fundamentals

### PSR-3 Logger Integration

The framework supports PSR-3 loggers out of the box:

```php
use ClaudeAgents\Support\LoggerFactory;
use Psr\Log\LogLevel;

// Console logger (development)
$logger = LoggerFactory::createConsole(LogLevel::INFO);

// File logger (production)
$logger = LoggerFactory::createFile('/var/log/agent.log', LogLevel::INFO);

// Memory logger (testing)
$logger = LoggerFactory::createMemory();
```

### Logging Agent Operations

Every significant operation should be logged:

```php
use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$logger = LoggerFactory::createConsole(LogLevel::INFO);

$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant.');

// Log agent creation
$logger->info('Agent created', [
    'agent_id' => 'math-assistant',
    'system_prompt' => 'You are a helpful assistant.',
]);

$startTime = microtime(true);

try {
    $result = $agent->run('What is 25 * 17?');
    $duration = (microtime(true) - $startTime) * 1000;

    $logger->info('Agent execution completed', [
        'agent_id' => 'math-assistant',
        'answer' => $result->getAnswer(),
        'duration_ms' => round($duration, 2),
        'tool_calls' => count($result->getToolCalls()),
    ]);
} catch (\Throwable $e) {
    $duration = (microtime(true) - $startTime) * 1000;

    $logger->error('Agent execution failed', [
        'agent_id' => 'math-assistant',
        'error' => $e->getMessage(),
        'duration_ms' => round($duration, 2),
    ]);
}
```

**Key Logging Principles:**

✅ **Use structured context** — Pass arrays, not string interpolation
✅ **Include timing** — Log duration for performance analysis
✅ **Add correlation IDs** — Link related operations (user ID, session ID, trace ID)
✅ **Log errors with context** — Include enough detail to debug
✅ **Respect log levels** — DEBUG for verbose, INFO for normal, ERROR for failures

❌ **Don't log sensitive data** — PII, API keys, passwords
❌ **Don't log excessively** — High-volume DEBUG logs hurt performance
❌ **Don't rely on logs alone** — Use metrics for aggregation

---

## ObservabilityLogger with Trace Context

The `ObservabilityLogger` automatically enriches logs with trace context:

```php
use ClaudeAgents\Observability\ObservabilityLogger;
use ClaudeAgents\Observability\Tracer;
use ClaudeAgents\Support\LoggerFactory;

$baseLogger = LoggerFactory::createConsole(LogLevel::INFO);
$tracer = new Tracer();

// Create observability logger with tracer
$logger = new ObservabilityLogger($baseLogger, $tracer);

// Set global context (added to all logs)
$logger->setGlobalContext([
    'service' => 'agent-api',
    'environment' => 'production',
    'version' => '2.0.0',
]);

// Start a trace
$traceId = $tracer->startTrace();
$logger->info('Operation started'); // Includes trace_id automatically

// Every log now includes:
// - trace_id: Current trace ID
// - span_id: Active span ID
// - timestamp: Microsecond precision
// - memory_usage: Current memory
// - service, environment, version: From global context
```

### Automatic Context Propagation

When you start a span, all logs automatically include its ID:

```php
$span = $tracer->startSpan('tool_execution', [
    'tool' => 'calculate',
]);

$logger->info('Executing tool'); // Includes span_id

$tracer->endSpan($span);
```

This makes it trivial to **correlate logs with traces** in your log aggregation system (Elasticsearch, Loki, Splunk).

---

## Distributed Tracing with Spans

### Understanding Spans and Traces

A **trace** represents a single request or operation. A **span** represents a unit of work within that trace:

```text
┌──────────────────────────────────────────────────┐
│ Trace ID: abc123                                 │
├──────────────────────────────────────────────────┤
│                                                  │
│  ┌─ agent_execution (Root Span)                 │
│  │                                               │
│  │  ┌─ tool:calculate (Child Span)              │
│  │  │                                            │
│  │  │  ┌─ api_request (Grandchild Span)         │
│  │  │  └─ 50ms                                   │
│  │  │                                            │
│  │  └─ 75ms                                      │
│  │                                               │
│  └─ 150ms                                        │
│                                                  │
└──────────────────────────────────────────────────┘
```

### Creating Spans

```php
use ClaudeAgents\Observability\Tracer;

$tracer = new Tracer();
$traceId = $tracer->startTrace();

// Root span for the entire operation
$rootSpan = $tracer->startSpan('agent_workflow', [
    'operation' => 'math_calculation',
]);

// Child span for tool execution
$toolSpan = $tracer->startSpan('tool_execution', [
    'tool' => 'calculate',
    'expression' => '25 * 17',
], $rootSpan); // Pass parent span

// Add attributes dynamically
$toolSpan->setAttribute('result', 425);

// Add events (annotations)
$toolSpan->addEvent('calculation_completed', [
    'result' => 425,
]);

// Set status
$toolSpan->setStatus('OK'); // or 'ERROR'

// End span
$tracer->endSpan($toolSpan);

// End root span and trace
$tracer->endSpan($rootSpan);
$tracer->endTrace();
```

### Span Attributes

Spans support rich metadata:

| Attribute Type | Purpose | Example |
|---------------|---------|---------|
| **Input** | Request parameters | `prompt`, `expression`, `query` |
| **Output** | Response data | `answer`, `result`, `tokens` |
| **Timing** | Performance markers | `start_time`, `end_time`, `duration` |
| **Context** | Correlation | `user_id`, `session_id`, `agent_id` |
| **Status** | Success/failure | `OK`, `ERROR` |

### Analyzing Traces

```php
// Get all completed spans
$spans = $tracer->getSpans();

// Get spans for specific trace
$traceSpans = $tracer->getSpansByTraceId($traceId);

// Build hierarchical tree
$tree = $tracer->buildSpanTree();

// Calculate total duration
$totalDuration = $tracer->getTotalDuration();

// Export to OpenTelemetry format
$otelData = $tracer->toOpenTelemetry();
```

---

## Metrics Collection

### The Metrics Class

The `Metrics` class tracks operational metrics:

```php
use ClaudeAgents\Observability\Metrics;

$metrics = new Metrics();

// Record a successful request
$metrics->recordRequest(
    success: true,
    tokensInput: 100,
    tokensOutput: 50,
    duration: 1500.0, // milliseconds
);

// Record a failed request
$metrics->recordRequest(
    success: false,
    tokensInput: 0,
    tokensOutput: 0,
    duration: 500.0,
    error: 'RateLimitError: Too many requests',
);

// Get summary
$summary = $metrics->getSummary();
/*
[
    'total_requests' => 2,
    'successful_requests' => 1,
    'failed_requests' => 1,
    'success_rate' => 0.5,
    'total_tokens' => ['input' => 100, 'output' => 50, 'total' => 150],
    'total_duration_ms' => 2000.0,
    'average_duration_ms' => 1000.0,
    'error_counts' => ['RateLimitError' => 1],
]
*/
```

### Key Metrics to Track

#### Agent Metrics

- **Request count** — Total agent invocations
- **Success rate** — % of successful completions
- **Latency** — p50, p95, p99 response times
- **Token usage** — Input, output, and total tokens
- **Tool calls** — Count and distribution by tool

#### Tool Metrics

- **Invocation count** — Per-tool usage
- **Execution time** — Per-tool latency
- **Failure rate** — Tool errors and retries
- **Result size** — Output data volume

#### System Metrics

- **Active requests** — Current concurrent operations
- **Memory usage** — Per-request memory footprint
- **Error rates** — By error type and severity
- **Cost** — Estimated API spend

---

## Telemetry Service Integration

### OpenTelemetry Support

The `TelemetryService` provides OpenTelemetry-compatible metrics:

```php
use ClaudeAgents\Services\Telemetry\TelemetryService;
use ClaudeAgents\Services\Settings\SettingsService;

// Configure telemetry
$settings = new SettingsService([
    'telemetry' => [
        'enabled' => true,
        'otlp' => [
            'endpoint' => 'http://localhost:4318/v1/metrics',
        ],
    ],
]);

$telemetry = new TelemetryService($settings);
$telemetry->initialize();
```

### Metric Types

#### Counters — Cumulative Metrics

Counters only increase (request count, error count):

```php
// Increment counter
$telemetry->recordCounter('agent.requests.total', 1, [
    'agent' => 'math-assistant',
]);

$telemetry->recordCounter('tool.executions', 1, [
    'tool' => 'calculate',
    'status' => 'success',
]);
```

#### Gauges — Current Values

Gauges represent current state (active requests, memory usage):

```php
// Set current value
$telemetry->recordGauge('agent.active_requests', 5.0, [
    'agent' => 'math-assistant',
]);

$telemetry->recordGauge('system.memory_mb', 256.5);
```

#### Histograms — Distributions

Histograms track value distributions (latency, token counts):

```php
// Record latency
$telemetry->recordHistogram('agent.duration.ms', 1500.0, [
    'agent' => 'math-assistant',
]);

// Record token usage
$telemetry->recordHistogram('agent.tokens.input', 150.0);
```

### Recording Agent Metrics

The `recordAgentRequest()` helper combines all metrics:

```php
$telemetry->recordAgentRequest(
    success: true,
    tokensInput: 100,
    tokensOutput: 50,
    duration: 1500.0,
);

// Equivalent to:
// - recordCounter('agent.requests.total')
// - recordCounter('agent.requests.success')
// - recordHistogram('agent.tokens.input', 100)
// - recordHistogram('agent.tokens.output', 50)
// - recordHistogram('agent.duration.ms', 1500)
```

### Flushing Telemetry

Periodically export metrics to your backend:

```php
// Flush to OTLP endpoint
$telemetry->flush();

// In production, flush on:
// - Periodic timer (every 60 seconds)
// - Request completion
// - Shutdown/teardown
```

---

## External Tracing Backends

### Supported Platforms

`claude-php-agent` includes integrations for popular AI observability platforms:

| Platform | Focus | Best For |
|----------|-------|----------|
| **[LangSmith](https://docs.smith.langchain.com/)** | LangChain ecosystem | Multi-agent workflows, chains |
| **[LangFuse](https://langfuse.com/docs)** | Open-source LLM observability | Self-hosted, cost tracking |
| **[Arize Phoenix](https://docs.arize.com/phoenix)** | ML observability | Model evaluation, debugging |

### LangSmith Integration

```php
use ClaudeAgents\Services\Tracing\LangSmithTracer;
use ClaudeAgents\Services\Tracing\TraceContext;

$tracer = new LangSmithTracer(
    apiKey: getenv('LANGSMITH_API_KEY'),
    projectName: 'php-agent-production',
);

// Start trace
$context = new TraceContext(
    traceId: bin2hex(random_bytes(16)),
    traceName: 'agent_calculation',
    inputs: ['query' => 'What is 25 * 17?'],
);

$tracer->startTrace($context);

// ... execute agent ...

// End trace with outputs
$context = new TraceContext(
    traceId: $context->traceId,
    traceName: $context->traceName,
    inputs: $context->inputs,
    outputs: ['answer' => '425', 'duration_ms' => 1500],
);

$tracer->endTrace($context);
```

### LangFuse Integration

```php
use ClaudeAgents\Services\Tracing\LangFuseTracer;

$tracer = new LangFuseTracer(
    publicKey: getenv('LANGFUSE_PUBLIC_KEY'),
    secretKey: getenv('LANGFUSE_SECRET_KEY'),
);

// Record spans and metrics
$span = new Span(/* ... */);
$tracer->recordSpan($span);

$metric = new Metric('agent.duration.ms', 1500.0);
$tracer->recordMetric($metric);
```

### Arize Phoenix Integration

```php
use ClaudeAgents\Services\Tracing\PhoenixTracer;

$tracer = new PhoenixTracer(
    endpoint: getenv('PHOENIX_ENDPOINT') ?? 'http://localhost:6006',
);

// Same API as other tracers
$tracer->startTrace($context);
$tracer->recordSpan($span);
$tracer->endTrace($context);
```

---

## Production Observability System

### Complete Observable Agent

Here's a production-ready agent wrapper with full observability:

```php
class ObservableAgent
{
    public function __construct(
        private Agent $agent,
        private ObservabilityLogger $logger,
        private Tracer $tracer,
        private Metrics $metrics,
        private TelemetryService $telemetry,
        private string $agentName
    ) {
    }

    public function run(string $prompt): mixed
    {
        // Start trace
        $traceId = $this->tracer->startTrace();
        $rootSpan = $this->tracer->startSpan('agent_execution', [
            'agent' => $this->agentName,
            'prompt_length' => strlen($prompt),
        ]);

        $this->logger->info('Agent execution started', [
            'agent' => $this->agentName,
            'prompt' => substr($prompt, 0, 100),
        ]);

        $this->telemetry->recordCounter('agent.executions.started', 1, [
            'agent' => $this->agentName,
        ]);

        $startTime = microtime(true);

        try {
            $result = $this->agent->run($prompt);
            $duration = (microtime(true) - $startTime) * 1000;

            // Get token usage
            $usage = $result->getTokenUsage();
            $inputTokens = $usage['input'];
            $outputTokens = $usage['output'];

            // Record metrics
            $this->metrics->recordRequest(
                success: true,
                tokensInput: $inputTokens,
                tokensOutput: $outputTokens,
                duration: $duration
            );

            // Record telemetry
            $this->telemetry->recordAgentRequest(
                success: true,
                tokensInput: $inputTokens,
                tokensOutput: $outputTokens,
                duration: $duration
            );

            // Update span
            $rootSpan->setAttribute('answer_length', strlen($result->getAnswer()));
            $rootSpan->setAttribute('tool_calls', count($result->getToolCalls()));
            $rootSpan->setStatus('OK');

            $this->logger->info('Agent execution completed', [
                'agent' => $this->agentName,
                'duration_ms' => round($duration, 2),
                'tokens' => ['input' => $inputTokens, 'output' => $outputTokens],
            ]);

            return $result;
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            // Record failures
            $this->metrics->recordRequest(
                success: false,
                tokensInput: 0,
                tokensOutput: 0,
                duration: $duration,
                error: get_class($e) . ': ' . $e->getMessage()
            );

            $this->telemetry->recordAgentRequest(
                success: false,
                tokensInput: 0,
                tokensOutput: 0,
                duration: $duration,
                error: get_class($e) . ': ' . $e->getMessage()
            );

            $rootSpan->setStatus('ERROR', $e->getMessage());
            $this->logger->logException($e, 'Agent execution failed');

            throw $e;
        } finally {
            $this->tracer->endSpan($rootSpan);
            $this->tracer->endTrace();
        }
    }
}
```

### Usage

```php
// Initialize observability stack
$baseLogger = LoggerFactory::createConsole(LogLevel::INFO);
$tracer = new Tracer();
$logger = new ObservabilityLogger($baseLogger, $tracer);
$metrics = new Metrics();
$telemetry = new TelemetryService($settings);

// Create observable agent
$baseAgent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

$agent = new ObservableAgent(
    agent: $baseAgent,
    logger: $logger,
    tracer: $tracer,
    metrics: $metrics,
    telemetry: $telemetry,
    agentName: 'math-assistant'
);

// Run with full observability
$result = $agent->run('What is 25 * 17?');
```

---

## Monitoring Dashboards

### Key Dashboard Panels

#### Request Volume and Health

```text
┌─────────────────────────────────────────────┐
│ Request Rate (requests/min)                 │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ Current: 45 req/min                         │
│ Peak: 72 req/min (14:23)                    │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ Success Rate (%)                            │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ Current: 98.5%                              │
│ Target: > 99.0%                             │
└─────────────────────────────────────────────┘
```

#### Latency Distribution

```text
┌─────────────────────────────────────────────┐
│ Response Time (ms)                          │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ p50:  1,200ms                               │
│ p95:  3,500ms                               │
│ p99:  5,800ms                               │
└─────────────────────────────────────────────┘
```

#### Token Usage and Cost

```text
┌─────────────────────────────────────────────┐
│ Token Usage (tokens/request)                │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ Input:  avg 150, total 45K                  │
│ Output: avg 75,  total 22.5K                │
│ Cost:   $12.50/hour                         │
└─────────────────────────────────────────────┘
```

#### Error Breakdown

```text
┌─────────────────────────────────────────────┐
│ Errors by Type                              │
│ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│ RateLimitError:     12  (60%)               │
│ TimeoutException:    5  (25%)               │
│ ValidationError:     3  (15%)               │
└─────────────────────────────────────────────┘
```

### Alert Rules

Configure alerts for critical thresholds:

| Metric | Threshold | Action |
|--------|-----------|--------|
| **Success Rate** | < 95% | Page on-call engineer |
| **p95 Latency** | > 5000ms | Investigate performance |
| **Error Rate** | > 5% | Check error logs |
| **Token Usage** | > 1M/hour | Review cost optimization |
| **Active Requests** | > 100 | Check for runaway processes |

---

## Observability Best Practices

### 1. Log Hygiene

✅ **Use appropriate log levels:**

```php
$logger->debug('Tool parameter validation passed'); // DEBUG
$logger->info('Agent execution started');           // INFO
$logger->warning('Retry attempt 2 of 3');           // WARNING
$logger->error('Tool execution failed');            // ERROR
$logger->critical('Database connection lost');      // CRITICAL
```

✅ **Include correlation IDs:**

```php
$logger->info('Request started', [
    'request_id' => $requestId,
    'user_id' => $userId,
    'trace_id' => $traceId,
]);
```

❌ **Don't log sensitive data:**

```php
// BAD
$logger->info('User authenticated', [
    'password' => $password, // NEVER log credentials
    'api_key' => $apiKey,    // NEVER log secrets
]);

// GOOD
$logger->info('User authenticated', [
    'user_id' => $userId,
    'auth_method' => 'password',
]);
```

### 2. Span Design

✅ **Create spans for significant operations:**

- Agent execution
- Tool calls
- External API requests
- Database queries
- File operations

✅ **Add meaningful attributes:**

```php
$span->setAttribute('tool', 'calculate');
$span->setAttribute('expression', '25 * 17');
$span->setAttribute('result', 425);
$span->setAttribute('cache_hit', true);
```

❌ **Don't create excessive spans:**

```php
// BAD: Too granular
$span1 = $tracer->startSpan('validate_input');
$span2 = $tracer->startSpan('parse_input');
$span3 = $tracer->startSpan('sanitize_input');

// GOOD: Appropriate granularity
$span = $tracer->startSpan('process_input');
```

### 3. Metric Selection

✅ **Track actionable metrics:**

- **Success rate** → Alerts for degradation
- **Latency percentiles** → Performance optimization
- **Token usage** → Cost management
- **Error rates by type** → Debugging priorities

✅ **Use correct metric types:**

```php
// Counter: Things that accumulate
$telemetry->recordCounter('requests.total');

// Gauge: Current state
$telemetry->recordGauge('active_requests', 5.0);

// Histogram: Distributions
$telemetry->recordHistogram('latency.ms', 1500.0);
```

❌ **Don't track vanity metrics:**

```php
// BAD: Not actionable
$telemetry->recordCounter('button_clicks');
$telemetry->recordGauge('favorite_color');
```

### 4. Performance Impact

✅ **Sample in high-volume scenarios:**

```php
// Sample 1% of traces in production
if (mt_rand(1, 100) === 1) {
    $traceId = $tracer->startTrace();
}
```

✅ **Use async logging:**

```php
// Queue logs for async processing
$logger->info('Event occurred', ['data' => $largeData]);
```

❌ **Don't block on observability:**

```php
// BAD: Synchronous export blocks request
$telemetry->flush(); // Blocks for network call

// GOOD: Async export in background
dispatch(fn() => $telemetry->flush());
```

---

## Production Architecture

### Recommended Stack

```text
┌──────────────────────────────────────────────────┐
│                 PHP Application                  │
│              (claude-php-agent)                  │
└────────┬──────────────┬──────────────┬───────────┘
         │              │              │
    Logs │         Traces │      Metrics │
         ▼              ▼              ▼
┌────────────┐  ┌────────────┐  ┌────────────┐
│    Loki    │  │   Tempo    │  │ Prometheus │
│    or      │  │   or       │  │    or      │
│ Elastic    │  │  Jaeger    │  │  Grafana   │
└────────────┘  └────────────┘  └────────────┘
         │              │              │
         └──────────────┴──────────────┘
                        │
                        ▼
         ┌─────────────────────────────┐
         │   Grafana Dashboard         │
         │   Alertmanager              │
         └─────────────────────────────┘
```

### OpenTelemetry Collector

For production, use the OpenTelemetry Collector as a central hub:

```yaml
# otel-collector-config.yaml
receivers:
  otlp:
    protocols:
      http:
        endpoint: 0.0.0.0:4318

processors:
  batch:
    timeout: 10s
    send_batch_size: 1000

exporters:
  prometheus:
    endpoint: "0.0.0.0:8889"
  jaeger:
    endpoint: "jaeger:14250"
  loki:
    endpoint: "http://loki:3100/loki/api/v1/push"

service:
  pipelines:
    metrics:
      receivers: [otlp]
      processors: [batch]
      exporters: [prometheus]
    traces:
      receivers: [otlp]
      processors: [batch]
      exporters: [jaeger]
    logs:
      receivers: [otlp]
      processors: [batch]
      exporters: [loki]
```

### Configuration

```php
// production.php
$settings = new SettingsService([
    'telemetry' => [
        'enabled' => true,
        'otlp' => [
            'endpoint' => getenv('OTLP_ENDPOINT'),
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('OTLP_TOKEN'),
            ],
        ],
        'sampling' => [
            'rate' => 0.1, // Sample 10% of traces
        ],
    ],
]);
```

---

## Debugging with Observability

### Finding Slow Requests

```php
// Query: Find requests > 5 seconds
// Prometheus: agent_duration_ms{quantile="0.95"} > 5000
// Loki: {agent="math-assistant"} | duration_ms > 5000
```

### Identifying Error Patterns

```php
// Query: Error rate by type
// Prometheus: rate(agent_requests_failed[5m]) by (error_type)
// Loki: {agent="math-assistant"} | level="error" | json | count by error_type
```

### Tracing Request Flow

```php
// Find trace by ID
$spans = $tracer->getSpansByTraceId($traceId);

// Analyze critical path
$tree = $tracer->buildSpanTree();
foreach ($tree as $node) {
    $span = $node['span'];
    echo "{$span->getName()}: {$span->getDuration()}ms\n";
    // Identify slowest span
}
```

---

## Exercise: Build Your Observability Dashboard

**Task:** Create a Grafana dashboard for your agent system.

**Requirements:**

1. **Request Metrics Panel:**
   - Request rate (requests/min)
   - Success rate (%)
   - Error rate (%)

2. **Latency Panel:**
   - p50, p95, p99 latency
   - Latency histogram

3. **Token Usage Panel:**
   - Input tokens/request
   - Output tokens/request
   - Estimated cost/hour

4. **Error Panel:**
   - Error count by type
   - Recent error logs

5. **Alerts:**
   - Success rate < 95%
   - p95 latency > 5s
   - Error rate > 5%

---

## Summary

In this chapter, you learned:

✅ **Structured Logging** — PSR-3 integration with context enrichment
✅ **Distributed Tracing** — Spans, traces, and parent-child relationships
✅ **Metrics Collection** — Counters, gauges, histograms for dashboards
✅ **OpenTelemetry** — Industry-standard telemetry export
✅ **External Platforms** — LangSmith, LangFuse, Arize Phoenix integration
✅ **Production Patterns** — Observable agents, monitoring, and alerting
✅ **Best Practices** — Log hygiene, span design, metric selection, performance

**Key Takeaways:**

1. **Observability is not optional** — Production systems need logs, traces, and metrics
2. **Instrument early** — Add observability from the start, not as an afterthought
3. **Use structured logging** — Context-rich logs beat string interpolation
4. **Correlate with traces** — Link logs to traces via trace_id
5. **Track what matters** — Focus on actionable metrics (success rate, latency, cost)
6. **Export to backends** — Use OpenTelemetry for vendor-neutral telemetry
7. **Monitor and alert** — Set thresholds for critical metrics

---

## Next Steps

With observability in place, you can now measure quality. In the next chapter, you'll build **evaluation harnesses** to systematically test agent accuracy, safety, and cost.

→ **[Chapter 17: Evaluation Harnesses and QA](./17-evaluation-harnesses-and-qa)**

Build offline evals, golden tests, and regression suites to measure accuracy, cost, and safety on real task sets.

---

## Additional Resources

### Documentation
- [OpenTelemetry PHP](https://opentelemetry.io/docs/languages/php/)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [Prometheus Best Practices](https://prometheus.io/docs/practices/)
- [Grafana Dashboards](https://grafana.com/docs/grafana/latest/dashboards/)

### External Platforms
- [LangSmith Documentation](https://docs.smith.langchain.com/)
- [LangFuse Documentation](https://langfuse.com/docs)
- [Arize Phoenix](https://docs.arize.com/phoenix)

### Tools
- [Jaeger](https://www.jaegertracing.io/) — Distributed tracing
- [Grafana Tempo](https://grafana.com/oss/tempo/) — Distributed tracing backend
- [Grafana Loki](https://grafana.com/oss/loki/) — Log aggregation
- [Prometheus](https://prometheus.io/) — Metrics and alerting
