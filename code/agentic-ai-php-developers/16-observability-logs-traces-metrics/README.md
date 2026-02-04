# Chapter 16: Observability — Logs, Traces, and Metrics

Complete code examples for instrumenting agents with production-grade observability.

## Examples

### 1. Basic Structured Logging
**File:** [`01-basic-structured-logging.php`](./01-basic-structured-logging.php)

Demonstrates PSR-3 logging integration with agents. Shows how to log agent operations with structured context and metadata.

```bash
php 01-basic-structured-logging.php
```

**Key Concepts:**
- PSR-3 logger integration
- Structured logging with context
- Tool execution logging
- Duration tracking

### 2. ObservabilityLogger with Trace Context
**File:** [`02-observability-logger.php`](./02-observability-logger.php)

Uses `ObservabilityLogger` which automatically enriches logs with trace IDs, span IDs, and global context.

```bash
php 02-observability-logger.php
```

**Key Concepts:**
- Automatic trace context propagation
- Global context for all logs
- Span-aware logging
- Memory and timestamp enrichment

### 3. Distributed Tracing with Spans
**File:** [`03-distributed-tracing.php`](./03-distributed-tracing.php)

Demonstrates hierarchical span relationships for detailed performance profiling and distributed tracing.

```bash
php 03-distributed-tracing.php
```

**Key Concepts:**
- Parent-child span relationships
- Span events and attributes
- Trace tree visualization
- OpenTelemetry export format

### 4. Metrics Collection
**File:** [`04-metrics-collection.php`](./04-metrics-collection.php)

Shows how to collect and report operational metrics: requests, tokens, latency, and errors.

```bash
php 04-metrics-collection.php
```

**Key Concepts:**
- Request counting and success rates
- Token usage tracking
- Latency measurements
- Error categorization

### 5. MetricsAggregator
**File:** [`05-metrics-aggregator.php`](./05-metrics-aggregator.php)

Demonstrates using `MetricsAggregator` for advanced metrics with aggregation and percentiles.

```bash
php 05-telemetry-service.php
```

**Key Concepts:**
- Counter metrics (cumulative)
- Gauge metrics (current values)
- Histogram metrics (distributions)
- OTLP export

### 6. Comprehensive Observability
**File:** [`06-comprehensive-observability.php`](./06-comprehensive-observability.php)

Combines logging, tracing, and metrics for complete observability.

```bash
php 06-comprehensive-observability.php
```

**Key Concepts:**
- Full observability stack
- Integrated logging, tracing, and metrics
- Production monitoring patterns
- Dashboard-ready data

## Quick Start

```bash
# Install dependencies
composer install

# Set API key
export ANTHROPIC_API_KEY="your-key-here"

# Run any example
php 01-basic-structured-logging.php
```

## Observability Components

### Logging
- **PSR-3 Logger**: Standard logging interface
- **ObservabilityLogger**: Trace-aware structured logger
- **LoggerFactory**: Creates console, file, and memory loggers

### Tracing
- **Tracer**: Manages spans and traces
- **Span**: Represents a unit of work with timing and attributes
- **OpenTelemetry**: Export to OTLP-compatible backends

### Metrics
- **Metrics**: Collects request counts, tokens, duration, errors
- **TelemetryService**: OpenTelemetry-compatible metrics service
- **Counter**: Cumulative metrics that only increase
- **Gauge**: Metrics that can go up and down
- **Histogram**: Distribution of values

### External Backends
- **LangSmith**: LangChain's observability platform
- **LangFuse**: Open-source LLM observability
- **Arize Phoenix**: ML observability and monitoring

## Environment Variables

```bash
# Required
ANTHROPIC_API_KEY=your-anthropic-api-key

# Optional (for external tracers)
LANGSMITH_API_KEY=your-langsmith-key
LANGFUSE_PUBLIC_KEY=your-langfuse-public-key
LANGFUSE_SECRET_KEY=your-langfuse-secret-key
PHOENIX_ENDPOINT=http://localhost:6006

# Optional (for telemetry)
OTLP_ENDPOINT=http://localhost:4318/v1/metrics
```

## Production Setup

For production observability, consider:

1. **Centralized Logging**
   - Elasticsearch + Kibana
   - Loki + Grafana
   - AWS CloudWatch Logs

2. **Distributed Tracing**
   - Jaeger
   - Zipkin
   - Grafana Tempo

3. **Metrics Collection**
   - Prometheus
   - Grafana
   - Datadog
   - New Relic

4. **OpenTelemetry Collector**
   - Receive OTLP exports
   - Process and filter data
   - Export to multiple backends

## Best Practices

### Logging
- Use structured logging with context
- Include trace IDs for correlation
- Log at appropriate levels (DEBUG, INFO, ERROR)
- Avoid logging sensitive data (PII, secrets)

### Tracing
- Create spans for all significant operations
- Add meaningful attributes to spans
- Track parent-child relationships
- Export to centralized tracing backend

### Metrics
- Track success rates and error rates
- Monitor token usage and costs
- Measure latency (p50, p95, p99)
- Set up alerts for anomalies

### Telemetry
- Use counters for events (requests, errors)
- Use gauges for current state (active requests)
- Use histograms for distributions (latency, tokens)
- Export regularly to avoid data loss

## Key Metrics to Monitor

### Agent Performance
- `agent.requests.total` - Total agent requests
- `agent.requests.success` - Successful requests
- `agent.requests.failed` - Failed requests
- `agent.duration.ms` - Request latency
- `agent.tokens.input` - Input tokens used
- `agent.tokens.output` - Output tokens generated

### Tool Performance
- `tool.invocations` - Tool call count
- `tool.executions.success` - Successful tool calls
- `tool.executions.failed` - Failed tool calls
- `tool.duration.ms` - Tool execution time

### System Health
- `agent.active_requests` - Current active requests
- Memory usage
- Error rates by type
- Request throughput

## Troubleshooting

### Logs not showing trace IDs
- Ensure `ObservabilityLogger` has a `Tracer` instance
- Verify trace was started before logging
- Check that logger is using the correct tracer

### Spans not appearing
- Verify `endSpan()` is called for all started spans
- Check span status is set (OK/ERROR)
- Ensure trace is ended with `endTrace()`

### Metrics not exporting
- Check telemetry is enabled in settings
- Verify OTLP endpoint is reachable
- Call `flush()` to force export
- Check logger for export errors

### High memory usage
- Limit span retention
- Clear completed traces periodically
- Use streaming/sampling for high-volume scenarios

## Learn More

- [OpenTelemetry PHP](https://opentelemetry.io/docs/languages/php/)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [LangSmith Documentation](https://docs.smith.langchain.com/)
- [LangFuse Documentation](https://langfuse.com/docs)
- [Arize Phoenix](https://docs.arize.com/phoenix)

## Next Chapter

→ [Chapter 17: Evaluation Harnesses and QA](../17-evaluation-harnesses-and-qa/)

Build offline evals, golden tests, and regression suites to measure accuracy, cost, and safety.
