# Chapter 04: Agent Configuration Code Examples

This directory contains complete, runnable examples demonstrating production-ready agent configuration and best practices.

## Overview

These examples show you how to configure agents for production with retry logic, structured logging, error handling, monitoring, and fault tolerance patterns.

## Examples

### 1. basic-configuration.php
**Essential agent configuration options**

Demonstrates:
- Model selection (Sonnet, Opus, Haiku)
- Temperature and creativity settings
- Execution limits (iterations, timeouts)
- Token limits
- Configuration comparison

Run:
```bash
php basic-configuration.php
```

### 2. retry-strategies.php
**Retry logic with exponential backoff and jitter**

Demonstrates:
- Exponential backoff calculation
- Backoff with jitter (randomization)
- Selective retry policies
- Retry callbacks and monitoring

Run:
```bash
php retry-strategies.php
```

### 3. structured-logging.php
**PSR-3 logging integration and observability**

Demonstrates:
- Basic Monolog setup
- JSON structured logging
- Custom log processors (request ID, environment)
- Log level filtering
- Lifecycle event logging
- Performance metrics logging

Run:
```bash
php structured-logging.php
```

### 4. error-handling.php
**Robust error handling patterns**

Demonstrates:
- Try-catch patterns at agent level
- Exception type handling
- Tool-level error handling with ToolResult
- Graceful degradation and fallbacks
- Error categorization
- Error reporting and metrics

Run:
```bash
php error-handling.php
```

### 5. circuit-breaker.php
**Fault tolerance with circuit breakers**

Demonstrates:
- Basic circuit breaker implementation
- State management (closed, open, half-open)
- Failure threshold detection
- Recovery testing
- Per-service circuit breakers
- Circuit breaker registry
- Integration with agent execution

Run:
```bash
php circuit-breaker.php
```

### 6. production-agent.php
**Complete production-ready agent setup**

Demonstrates:
- Full agent configuration
- Structured logging with Monolog
- Safe execution wrapper
- Rate limiting per user
- Circuit breaker protection
- Error handling with graceful fallbacks
- Metrics collection

Run:
```bash
php production-agent.php
```

### 7. monitoring-integration.php
**Metrics collection and observability**

Demonstrates:
- Basic metrics collector (counters, gauges, histograms)
- Performance tracking and statistics
- Health check system
- Alert manager with rules
- Dashboard metrics formatting

Run:
```bash
php monitoring-integration.php
```

## Requirements

- PHP 8.4+
- Composer dependencies from `../00-environment-setup/`
- `ANTHROPIC_API_KEY` environment variable (for examples that use the agent)

## Installation

These examples use the shared dependencies from the environment setup:

```bash
cd ../00-environment-setup
composer install
cd ../04-agent-configuration
```

## Configuration Best Practices

Based on these examples, here are key configuration recommendations:

### Development
- Temperature: 0.3-0.7 (balanced)
- Max iterations: 15-20 (exploratory)
- Timeout: 180-300s (generous)
- Logging: DEBUG level
- Retries: 3-5 with backoff

### Production
- Temperature: 0.0-0.2 (consistent)
- Max iterations: 8-10 (controlled)
- Timeout: 60-120s (strict)
- Logging: INFO level (ERROR for critical)
- Retries: 5 with exponential backoff + jitter

### Key Metrics to Monitor

1. **Performance**
   - Response time (p50, p95, p99)
   - Token usage per request
   - Iteration count distribution

2. **Reliability**
   - Success rate
   - Error rate by type
   - Retry rate
   - Circuit breaker state

3. **Cost**
   - Total tokens consumed
   - Cost per request
   - Tool execution costs

4. **Health**
   - Memory usage
   - Disk space
   - API connectivity
   - Rate limit headroom

## Next Steps

After mastering agent configuration, continue to:

- **Chapter 05**: Tool Routing and Execution Pipelines
- Learn to build centralized tool registries
- Implement tool execution middleware
- Add tool-level observability

## Resources

- [Anthropic API Best Practices](https://docs.anthropic.com/en/docs/build-with-claude/best-practices)
- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html)
- [Exponential Backoff and Jitter](https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/)
