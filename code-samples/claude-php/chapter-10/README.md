# Chapter 10: Error Handling and Rate Limiting

This chapter demonstrates production-ready error handling, retry logic, circuit breakers, rate limiting, and error monitoring for Claude API integration.

## Features

- **Retry Logic**: Exponential backoff with jitter for transient failures
- **Circuit Breaker**: Prevent cascading failures with automatic recovery
- **Rate Limiter**: Respect API rate limits with token bucket algorithm
- **Error Monitor**: Track and alert on error patterns

## Installation

```bash
composer install
cp .env.example .env
# Edit .env and add your API key
```

## Examples

### 1. Retry Logic
```bash
php examples/retry-logic.php
```

Demonstrates:
- Exponential backoff
- Jitter to prevent thundering herd
- Configurable retry strategies
- Error classification (retryable vs fatal)

### 2. Circuit Breaker
```bash
php examples/circuit-breaker.php
```

Features:
- Three states: Closed, Open, Half-Open
- Automatic failure detection
- Configurable thresholds
- Recovery testing

### 3. Rate Limiter
```bash
php examples/rate-limiter.php
```

Implements:
- Token bucket algorithm
- Sliding window rate limiting
- Distributed rate limiting with Redis
- Per-user and global limits

### 4. Error Monitor
```bash
php examples/error-monitor.php
```

Includes:
- Error logging and aggregation
- Alert thresholds
- Webhook notifications
- Error pattern detection

## Usage

### Basic Retry Example

```php
use ClaudePHP\ErrorHandling\RetryHandler;

$retry = new RetryHandler(
    maxAttempts: 3,
    initialDelay: 1000,
    maxDelay: 30000,
    multiplier: 2
);

$result = $retry->execute(function() {
    return callClaudeAPI();
});
```

### Circuit Breaker Pattern

```php
use ClaudePHP\ErrorHandling\CircuitBreaker;

$breaker = new CircuitBreaker(
    threshold: 5,
    timeout: 60,
    resetTimeout: 300
);

if ($breaker->isAvailable()) {
    try {
        $result = callClaudeAPI();
        $breaker->recordSuccess();
    } catch (Exception $e) {
        $breaker->recordFailure();
        throw $e;
    }
}
```

### Rate Limiting

```php
use ClaudePHP\ErrorHandling\RateLimiter;

$limiter = new RateLimiter(
    requestsPerMinute: 60,
    requestsPerHour: 1000
);

if ($limiter->allowRequest('user-123')) {
    $result = callClaudeAPI();
} else {
    throw new Exception('Rate limit exceeded');
}
```

## Testing

```bash
composer test
```

## Configuration

All settings can be configured via environment variables:

- `RETRY_MAX_ATTEMPTS`: Maximum retry attempts (default: 3)
- `CIRCUIT_BREAKER_THRESHOLD`: Failures before opening circuit (default: 5)
- `RATE_LIMIT_REQUESTS_PER_MINUTE`: Requests per minute (default: 60)

See `.env.example` for all available options.

## Best Practices

1. **Always use retry logic** for production applications
2. **Implement circuit breakers** to prevent cascading failures
3. **Monitor error rates** and set up alerts
4. **Use distributed rate limiting** for multi-server deployments
5. **Log all errors** with context for debugging

## Learn More

- [Anthropic API Rate Limits](https://docs.anthropic.com/claude/reference/rate-limits)
- [Error Handling Best Practices](https://docs.anthropic.com/claude/reference/errors)
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html)
