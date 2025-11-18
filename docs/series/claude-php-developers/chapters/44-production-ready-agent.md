---
title: "44: Production-Ready Agent"
description: "Build production-ready agent with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 44
order: 44
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/43-*"
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

This chapter is based on Tutorial 4 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 60 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 43** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Implement comprehensive error handling
- Add retry logic with exponential backoff
- Handle tool execution failures gracefully
- Integrate server-side tools (Web Search)
- Implement logging and monitoring
- Build graceful degradation strategies
- Test error scenarios

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



You've built agents that work in ideal conditions. But production systems need to handle errors, failures, retries, and persistent state. In this tutorial, we'll transform your agent into a robust, production-grade system.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement comprehensive error handling
- Add retry logic with exponential backoff
- Handle tool execution failures gracefully
- Integrate server-side tools (Web Search)
- Implement logging and monitoring
- Build graceful degradation strategies
- Test error scenarios

### 🏗️ What We're Building

We'll enhance our multi-tool agent with:

1. **Error Handling** - Catch and handle all failure modes
2. **Retry Logic** - Automatically retry transient failures
3. **Server-Side Tools** - Integrate tools like Web Search
4. **Logging** - Track agent behavior and issues
5. **Graceful Degradation** - Continue working when tools fail

### 🚨 Production Failure Modes

#### Common Failures

1. **API Errors**

   - Rate limiting (429)
   - Temporary outages (503)
   - Authentication issues (401)
   - Timeout errors

2. **Tool Execution Errors**

   - Invalid input
   - External API failures
   - Calculation errors
   - Resource unavailable

3. **Agent Errors**

   - Infinite loops
   - Context window overflow
   - Malformed responses
   - Unexpected stop reasons

4. **State Management**
   - Lost conversation history
   - Memory inconsistencies
   - Token limit exceeded

### 🛡️ Error Handling Strategy

#### 1. Catch All Exceptions

```php
try {
    $response = $client->messages()->create([...]);
} catch (\ClaudePhp\Exceptions\RateLimitError $e) {
    // Handle rate limiting
    $retryAfter = $e->response->getHeaderLine('retry-after');
    sleep($retryAfter);
    // Retry...
} catch (\ClaudePhp\Exceptions\APIConnectionError $e) {
    // Network/timeout issue
    log_error("Connection failed: " . $e->getMessage());
    // Retry with backoff...
} catch (\ClaudePhp\Exceptions\AuthenticationError $e) {
    // Invalid API key - don't retry
    log_error("Authentication failed");
    throw $e;
} catch (\ClaudePhp\Exceptions\APIStatusError $e) {
    // Other API errors
    log_error("API error {$e->status_code}: {$e->message}");
    // Retry if 5xx, fail if 4xx
} catch (Exception $e) {
    // Unexpected error
    log_error("Unexpected: " . $e->getMessage());
    throw $e;
}
```

#### 2. Retry with Exponential Backoff

```php
function retryWithBackoff(callable $fn, $maxAttempts = 3) {
    $attempt = 0;
    $delay = 1000; // Start with 1 second

    while ($attempt < $maxAttempts) {
        try {
            return $fn();
        } catch (\ClaudePhp\Exceptions\RateLimitError $e) {
            $attempt++;
            if ($attempt >= $maxAttempts) throw $e;

            $retryAfter = $e->response->getHeaderLine('retry-after');
            $waitTime = $retryAfter ?: ($delay / 1000);

            echo "Rate limited. Waiting {$waitTime}s...\n";
            sleep($waitTime);
            $delay *= 2; // Exponential backoff
        } catch (\ClaudePhp\Exceptions\APIConnectionError $e) {
            $attempt++;
            if ($attempt >= $maxAttempts) throw $e;

            echo "Connection error. Retrying in {$delay}ms...\n";
            usleep($delay * 1000);
            $delay *= 2;
        }
    }
}
```

#### 3. Tool Error Handling

```php
function executeToolSafely($toolName, $input) {
    try {
        $result = executeTool($toolName, $input);
        return [
            'success' => true,
            'content' => $result
        ];
    } catch (Exception $e) {
        log_error("Tool {$toolName} failed: " . $e->getMessage());
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
            'is_error' => !$result['success']
        ];
    }
}
```

### 🌐 Server-Side Tools

#### Using the Web Search Tool

The Web Search Tool gives Claude access to real-time information from the web. Unlike custom tools, web search is executed server-side by Claude automatically!

```php
$webSearchTool = [
    'type' => 'web_search_20250305',
    'name' => 'web_search',
    'max_uses' => 3  // Limit searches per request
];

// Add to your tools
$tools = [$calculator, $weather, $webSearchTool];
```

**Note:** Web search must be enabled in your organization's Claude Console.

#### Web Search Features

Claude can:

- **Search the web** for current information
- **Cite sources** automatically from search results
- **Answer questions** beyond its knowledge cutoff
- **Find real-time data** like weather, prices, or news

#### Example Usage

```php
// User: "What is the current version of PHP?"
// Claude will:
// - Decide to search based on the query
// - Execute web search automatically (server-side)
// - Provide answer with cited sources

// User: "What are the latest developments in quantum computing?"
// Claude will:
// - Perform web search
// - Synthesize information from multiple sources
// - Include citations in the response
```

### 📊 Logging and Monitoring


## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 45](/series/claude-php-developers/chapters/45-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 4 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/04-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="44"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 45](/series/claude-php-developers/chapters/45-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 4 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/04-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/04-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
