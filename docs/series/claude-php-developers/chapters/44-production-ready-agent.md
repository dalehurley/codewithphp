---
title: "44: Production-Ready Agent"
description: "Build production-ready agent with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 44
order: 44
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/43-multi-tool-agent"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![44: Production-Ready Agent](/images/claude-php/chapter-44-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 44</span>
</div>

# Chapter 44: Production-Ready Agent

## Overview

You've built agents that work in ideal conditions. But production systems need to handle errors, failures, retries, and persistent state. This chapter teaches you to transform your agent into a robust, production-grade system that gracefully handles real-world failures.

You'll learn to implement comprehensive error handling, add retry logic with exponential backoff, handle tool execution failures gracefully, integrate server-side tools, implement logging and monitoring, and build graceful degradation strategies.

**Estimated Time**: 60-75 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 43** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`
- ✓ **API Key** configured in environment

## What You'll Build

By the end of this chapter, you will have created:

- **Error Handling System** - Catch and handle all failure modes (API errors, tool failures, agent errors)
- **Retry Logic** - Automatically retry transient failures with exponential backoff
- **Server-Side Tools** - Integrate tools like Web Search that require external APIs
- **Logging System** - Track agent behavior, errors, and performance metrics
- **Graceful Degradation** - Continue working when tools fail, providing partial results
- **Production-Ready Agent** - Robust agent that handles real-world production scenarios

## Objectives

By completing this chapter, you will:

- **Implement** comprehensive error handling for all failure modes
- **Add** retry logic with exponential backoff for transient failures
- **Handle** tool execution failures gracefully without breaking the agent
- **Integrate** server-side tools that require external API calls
- **Implement** logging and monitoring for production debugging
- **Build** graceful degradation strategies when tools fail
- **Test** error scenarios to ensure robustness

## Production Failure Modes

Production agents face many failure scenarios that don't occur in ideal conditions:

### Common Failures

1. **API Errors**
   - Rate limiting (429) - Too many requests
   - Temporary outages (503) - Service unavailable
   - Authentication issues (401) - Invalid API key
   - Timeout errors - Network or processing delays

2. **Tool Execution Errors**
   - Invalid input parameters
   - External API failures
   - Calculation errors (division by zero, etc.)
   - Resource unavailable (database down, etc.)

3. **Agent Errors**
   - Infinite loops (iteration limits exceeded)
   - Context window overflow (too many tokens)
   - Malformed responses from Claude
   - Unexpected stop reasons

4. **State Management Issues**
   - Lost conversation history
   - Memory inconsistencies
   - Token limit exceeded

## Step 1: Comprehensive Error Handling (~15 min)

Handle all exception types properly:

```php
<?php
# filename: examples/01-error-handling.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\RateLimitError;
use ClaudePhp\Exceptions\APIConnectionError;
use ClaudePhp\Exceptions\AuthenticationError;
use ClaudePhp\Exceptions\APIStatusError;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

function callClaudeWithErrorHandling(ClaudePhp $client, array $params): object {
    try {
        return $client->messages()->create($params);
    } catch (RateLimitError $e) {
        // Handle rate limiting
        $retryAfter = $e->response->getHeaderLine('retry-after') ?? 60;
        echo "Rate limited. Waiting {$retryAfter}s...\n";
        sleep((int)$retryAfter);
        // Retry once after waiting
        return $client->messages()->create($params);
    } catch (APIConnectionError $e) {
        // Network/timeout issue
        error_log("Connection failed: " . $e->getMessage());
        throw new Exception("Network error: " . $e->getMessage());
    } catch (AuthenticationError $e) {
        // Invalid API key - don't retry
        error_log("Authentication failed: " . $e->getMessage());
        throw new Exception("Invalid API key");
    } catch (APIStatusError $e) {
        // Other API errors
        error_log("API error {$e->status_code}: {$e->message}");
        
        // Retry if 5xx (server error), fail if 4xx (client error)
        if ($e->status_code >= 500) {
            sleep(2); // Wait before retry
            return $client->messages()->create($params);
        }
        throw $e;
    } catch (Exception $e) {
        // Unexpected error
        error_log("Unexpected error: " . $e->getMessage());
        throw $e;
    }
}
```

**Why It Works**: Different error types require different handling strategies. Rate limits need waiting, network errors need retries, authentication errors should fail immediately, and server errors (5xx) can be retried while client errors (4xx) should not.

## Step 2: Retry Logic with Exponential Backoff (~15 min)

Implement retry logic for transient failures:

```php
<?php
# filename: examples/02-retry-logic.php
declare(strict_types=1);

function retryWithBackoff(callable $fn, int $maxAttempts = 3): mixed {
    $attempt = 0;
    $delay = 1000; // Start with 1 second (in milliseconds)

    while ($attempt < $maxAttempts) {
        try {
            return $fn();
        } catch (\ClaudePhp\Exceptions\RateLimitError $e) {
            $attempt++;
            if ($attempt >= $maxAttempts) {
                throw $e;
            }

            $retryAfter = $e->response->getHeaderLine('retry-after') ?? ($delay / 1000);
            echo "Rate limited. Waiting {$retryAfter}s (attempt {$attempt}/{$maxAttempts})...\n";
            sleep((int)$retryAfter);
            $delay *= 2; // Exponential backoff
        } catch (\ClaudePhp\Exceptions\APIConnectionError $e) {
            $attempt++;
            if ($attempt >= $maxAttempts) {
                throw $e;
            }

            $waitSeconds = $delay / 1000;
            echo "Connection error. Retrying in {$waitSeconds}s (attempt {$attempt}/{$maxAttempts})...\n";
            sleep((int)$waitSeconds);
            $delay *= 2; // Exponential backoff
        }
    }
    
    throw new Exception("Max retry attempts reached");
}

// Usage in ReAct loop
$response = retryWithBackoff(function() use ($client, $messages, $tools) {
    return $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => $tools
    ]);
});
```

**Why It Works**: Exponential backoff prevents overwhelming the API during outages. Each retry waits longer, giving the service time to recover. Rate limit errors use the `retry-after` header when available.

## Step 3: Tool Error Handling (~10 min)

Handle tool execution failures gracefully:

```php
<?php
# filename: examples/03-tool-error-handling.php
declare(strict_types=1);

function executeToolSafely(string $toolName, array $input): array {
    try {
        $result = executeTool($toolName, $input);
        return [
            'success' => true,
            'content' => $result
        ];
    } catch (InvalidArgumentException $e) {
        // Invalid input - don't retry
        error_log("Tool {$toolName} invalid input: " . $e->getMessage());
        return [
            'success' => false,
            'content' => "Error: Invalid input - " . $e->getMessage(),
            'is_error' => true
        ];
    } catch (Exception $e) {
        // Other errors - log and return error message
        error_log("Tool {$toolName} failed: " . $e->getMessage());
        return [
            'success' => false,
            'content' => "Error: " . $e->getMessage(),
            'is_error' => true
        ];
    }
}

// Use in ReAct loop
foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use') {
        $result = executeToolSafely($block['name'], $block['input']);

        $toolResults[] = [
            'type' => 'tool_result',
            'tool_use_id' => $block['id'],
            'content' => $result['content'],
            'is_error' => $result['is_error'] ?? false
        ];
    }
}
```

**Why It Works**: Tool failures shouldn't crash the agent. By returning error messages as tool results, Claude can see what went wrong and adapt its approach, potentially trying a different tool or explaining the limitation to the user.

## Step 4: Logging and Monitoring (~10 min)

Add comprehensive logging for production debugging:

```php
<?php
# filename: examples/04-logging.php
declare(strict_types=1);

class AgentLogger {
    private string $logFile;
    
    public function __construct(string $logFile = 'agent.log') {
        $this->logFile = $logFile;
    }
    
    public function logIteration(int $iteration, object $response, array $context = []): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'iteration' => $iteration,
            'stop_reason' => $response->stop_reason,
            'input_tokens' => $response->usage->input_tokens,
            'output_tokens' => $response->usage->output_tokens,
            'context' => $context
        ];
        
        file_put_contents(
            $this->logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND
        );
    }
    
    public function logError(string $message, array $context = []): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'ERROR',
            'message' => $message,
            'context' => $context
        ];
        
        file_put_contents(
            $this->logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND
        );
    }
    
    public function logToolExecution(string $toolName, array $input, mixed $result): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tool' => $toolName,
            'input' => $input,
            'result' => is_string($result) ? $result : json_encode($result)
        ];
        
        file_put_contents(
            $this->logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND
        );
    }
}

// Usage
$logger = new AgentLogger();

while ($iteration < $maxIterations) {
    $iteration++;
    
    try {
        $response = $client->messages()->create([...]);
        $logger->logIteration($iteration, $response);
    } catch (Exception $e) {
        $logger->logError($e->getMessage(), ['iteration' => $iteration]);
        throw $e;
    }
}
```

**Why It Works**: Production systems need observability. Logging each iteration, tool execution, and error helps debug issues in production and understand agent behavior patterns.

## Step 5: Graceful Degradation (~10 min)

Continue working when tools fail:

```php
<?php
# filename: examples/05-graceful-degradation.php
declare(strict_types=1);

$toolFailureCount = [];
$maxToolFailures = 3;

foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use') {
        $toolName = $block['name'];
        
        // Check if tool has failed too many times
        if (($toolFailureCount[$toolName] ?? 0) >= $maxToolFailures) {
            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $block['id'],
                'content' => "Tool temporarily unavailable. Please try again later."
            ];
            continue;
        }
        
        try {
            $result = executeTool($toolName, $block['input']);
            $toolFailureCount[$toolName] = 0; // Reset on success
            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $block['id'],
                'content' => $result
            ];
        } catch (Exception $e) {
            $toolFailureCount[$toolName] = ($toolFailureCount[$toolName] ?? 0) + 1;
            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $block['id'],
                'content' => "Error: Tool temporarily unavailable. Please try again later."
            ];
        }
    }
}

// Optionally disable unreliable tools
foreach ($toolFailureCount as $toolName => $count) {
    if ($count >= $maxToolFailures) {
        echo "Disabling unreliable tool: {$toolName}\n";
        $tools = array_filter($tools, fn($t) => $t['name'] !== $toolName);
    }
}
```

**Why It Works**: When a tool fails repeatedly, disabling it prevents wasted API calls and allows the agent to work with available tools. Claude can adapt and use alternative approaches.

## Production Checklist

Before deploying your agent to production:

- [ ] All errors caught and handled appropriately
- [ ] Retry logic implemented for transient failures
- [ ] Tool execution wrapped in try-catch blocks
- [ ] Logging configured and tested
- [ ] Server-side tools configured (if needed)
- [ ] Iteration limits set appropriately
- [ ] Token usage monitored and limited
- [ ] Graceful degradation strategies in place
- [ ] Error scenarios tested thoroughly
- [ ] Documentation for operators and monitoring

## Best Practices

### 1. Always Set Timeouts

```php
$client = new ClaudePhp(
    apiKey: $apiKey,
    timeout: 30.0,  // 30 seconds
    maxRetries: 2
);
```

### 2. Validate Tool Input

```php
function validateCalculatorInput(string $expression): bool {
    // Only allow safe characters
    if (!preg_match('/^[0-9+\-*\/().\s]+$/', $expression)) {
        throw new InvalidArgumentException("Invalid expression");
    }
    return true;
}
```

### 3. Monitor Token Usage

```php
$totalTokens = 0;
$maxTokens = 100000; // Set appropriate limit

while ($iteration < $maxIterations) {
    $response = $client->messages()->create([...]);
    $totalTokens += $response->usage->input_tokens + $response->usage->output_tokens;
    
    if ($totalTokens > $maxTokens) {
        echo "Token limit exceeded\n";
        break;
    }
}
```

## Next Steps

Now that you have a production-ready agent, explore advanced patterns:

- **[Chapter 45: Advanced ReAct Patterns](/series/claude-php-developers/chapters/45-advanced-react-patterns)** - Advanced reasoning techniques
- **[Chapter 46: Complete Agentic Framework](/series/claude-php-developers/chapters/46-complete-agentic-framework)** - Full framework implementation
- **[Chapter 43: Multi-Tool Agent](/series/claude-php-developers/chapters/43-multi-tool-agent)** - Review multi-tool patterns

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 4 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/04-production-ready) - Original tutorial with code examples
- [Error Handling Best Practices](https://docs.anthropic.com/en/docs/build-with-claude/error-handling) - Official error handling guide

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="44"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 45](/series/claude-php-developers/chapters/45-advanced-react-patterns) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 44 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-44)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-44
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-error-handling.php
```

For the original tutorial code:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/04-production-ready
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php production_agent.php
```
