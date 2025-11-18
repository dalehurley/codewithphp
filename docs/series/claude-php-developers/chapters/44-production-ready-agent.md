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

## Step 1: Basic ReAct Loop Implementation

Let's start with a complete working example:

```php
<?php
# filename: examples/01-react-loop.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define calculator tool
$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Mathematical expression to evaluate'
            ]
        ],
        'required' => ['expression']
    ]
];

// Tool executor
function executeCalculator(string $expression): string {
    try {
        // WARNING: eval() for demo only! Use proper parser in production
        $result = eval("return {$expression};");
        return (string)$result;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

// User task requiring multiple steps
$task = "What is (50 × 30) + (100 - 25)?";
echo "Task: {$task}\n\n";

// Initialize conversation
$messages = [
    ['role' => 'user', 'content' => $task]
];

$maxIterations = 10;
$iteration = 0;
$finalResponse = null;

// ReAct Loop
while ($iteration < $maxIterations) {
    $iteration++;
    
    echo "Iteration {$iteration}\n";
    
    // REASON: Call Claude with current state
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => [$calculatorTool]
    ]);
    
    echo "  Stop Reason: {$response->stop_reason}\n";
    
    // Add assistant response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];
    
    // Check if done
    if ($response->stop_reason === 'end_turn') {
        $finalResponse = $response;
        break;
    }
    
    // ACT: Execute tools if requested
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "  Using tool: {$block['name']}\n";
                
                // Execute tool
                $result = executeCalculator($block['input']['expression']);
                echo "  Result: {$result}\n";
                
                // Format tool result
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }
        
        // OBSERVE: Add results to conversation
        if (!empty($toolResults)) {
            $messages[] = [
                'role' => 'user',
                'content' => $toolResults
            ];
        }
    }
}

// Display final answer
if ($finalResponse) {
    echo "\nFinal Answer:\n";
    foreach ($finalResponse->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} else {
    echo "\nMax iterations reached without completion\n";
}
```

**Why It Works**: The ReAct loop maintains conversation history across iterations. Each iteration, Claude reasons about what to do next, acts by requesting tools, and observes the results. The loop continues until Claude determines the task is complete (`stop_reason === 'end_turn'`).

## Step 2: Stop Conditions and Safety

Always implement proper stop conditions:

```php
<?php
# filename: examples/02-stop-conditions.php
declare(strict_types=1);

// Stop conditions
$stopReasons = [
    'end_turn' => 'Task complete',
    'max_tokens' => 'Response truncated',
    'tool_use' => 'Tool execution needed'
];

// Safety limits
$maxIterations = 10;
$maxTokens = 10000;
$totalTokens = 0;

while ($iteration < $maxIterations) {
    $iteration++;
    
    $response = $client->messages()->create([...]);
    
    $totalTokens += $response->usage->input_tokens + $response->usage->output_tokens;
    
    // Check token limit
    if ($totalTokens > $maxTokens) {
        echo "Token limit reached\n";
        break;
    }
    
    // Check stop reason
    if ($response->stop_reason === 'end_turn') {
        break; // Success
    }
    
    // Handle tool use...
}
```

## Step 3: Debugging Agent Reasoning

Add debugging to understand agent behavior:

```php
<?php
# filename: examples/03-debugging.php
declare(strict_types=1);

function debugIteration(int $iteration, object $response): void {
    echo "\n╔════ Iteration {$iteration} ════╗\n";
    echo "Stop Reason: {$response->stop_reason}\n";
    echo "Tokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Text: {$block['text']}\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "Tool: {$block['name']}\n";
            echo "  Input: " . json_encode($block['input']) . "\n";
        }
    }
}
```

## Common Issues and Solutions

### Issue: Infinite Loop

**Symptom**: Agent keeps making tool calls without completing

**Solution**: Always set iteration limits and check for progress

```php
$maxIterations = 10;
$hasProgressed = false;
$previousToolCount = 0;

while ($iteration < $maxIterations) {
    // ... execute loop ...
    
    $currentToolCount = count(array_filter($response->content, fn($b) => $b['type'] === 'tool_use'));
    
    if ($currentToolCount > $previousToolCount) {
        $hasProgressed = true;
    }
    
    if ($iteration >= 5 && !$hasProgressed) {
        echo "Warning: Agent may be stuck\n";
        break;
    }
}
```

## Next Steps

Continue to the next chapter in the agent series:

- **[Chapter 45](/series/claude-php-developers/chapters/45-advanced-react-patterns)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Chapter 40: Introduction to Agentic AI](/series/claude-php-developers/chapters/40-introduction-to-agentic-ai)** - Agent fundamentals

## Further Reading

- [Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials) - Complete tutorial series
- [Tutorial 4 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/04-production-ready) - Original tutorial with code examples
- [ReAct Paper](https://arxiv.org/abs/2210.03629) - Original research paper

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="44"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 45](/series/claude-php-developers/chapters/45-advanced-react-patterns) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 4 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/04-production-ready)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/04-production-ready
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php react_agent.php
```
