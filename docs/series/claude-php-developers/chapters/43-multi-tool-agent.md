---
title: "43: Multi-Tool Agent"
description: "Build multi-tool agent with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 43
order: 43
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/42-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![43: Multi-Tool Agent](/images/claude-php/chapter-43-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 43</span>
</div>

# Chapter 43: Multi-Tool Agent

## Overview

This chapter is based on Tutorial 3 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 42** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Define multiple tools with clear, distinct purposes
- Help Claude choose the right tool through good descriptions
- Handle different tool types (data retrieval, computation, actions)
- Debug tool selection decisions
- Optimize tool definitions for clarity
- Manage tool execution workflows

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



In the previous tutorials, we built agents with a single tool. Real-world agents need multiple diverse tools to handle various tasks. In this tutorial, we'll create an agent with several tools and learn how Claude chooses the right one for each situation.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Define multiple tools with clear, distinct purposes
- Help Claude choose the right tool through good descriptions
- Handle different tool types (data retrieval, computation, actions)
- Debug tool selection decisions
- Optimize tool definitions for clarity
- Manage tool execution workflows

### 🏗️ What We're Building

We'll create a **Smart Assistant Agent** with these tools:

1. **Calculator** - Mathematical computations
2. **Current Time** - Get time in any timezone
3. **Weather** - Get weather information (simulated)
4. **Web Search** - Search for information (simulated)

This agent can handle diverse requests like:

- "What time is it in Tokyo?"
- "Calculate 25% of 480"
- "What's the weather in London?"
- "Search for the population of Paris"

### 🔑 Key Concepts

#### Tool Selection

Claude chooses tools based on:

1. **Tool Name**: Clear, descriptive names
2. **Tool Description**: What it does and when to use it
3. **Input Schema**: What parameters it needs
4. **Context**: The user's request

#### Good vs Bad Tool Definitions

**❌ Bad Example:**

```php
[
    'name' => 'tool1',
    'description' => 'Does stuff',
    'input_schema' => [...]
]
```

**✅ Good Example:**

```php
[
    'name' => 'get_weather',
    'description' => 'Get current weather conditions for a specific city. ' .
                     'Returns temperature, conditions, and humidity. ' .
                     'Use this when the user asks about weather or temperature.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => 'City name, e.g., "San Francisco" or "London, UK"'
            ]
        ],
        'required' => ['city']
    ]
]
```

### 📋 Defining Multiple Tools

#### 1. Calculator Tool

```php
$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations including ' .
                     'arithmetic (+, -, *, /), percentages, and complex expressions. ' .
                     'Use this for any mathematical computation.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Math expression like "25 * 4" or "0.25 * 480"'
            ]
        ],
        'required' => ['expression']
    ]
];
```

#### 2. Time Tool

```php
$timeTool = [
    'name' => 'get_current_time',
    'description' => 'Get the current time in any timezone. ' .
                     'Returns time in 24-hour format. ' .
                     'Use this when user asks "what time is it" or needs current time.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'timezone' => [
                'type' => 'string',
                'description' => 'IANA timezone like "America/New_York", "Europe/London", "Asia/Tokyo"'
            ]
        ],
        'required' => ['timezone']
    ]
];
```

#### 3. Weather Tool

```php
$weatherTool = [
    'name' => 'get_weather',
    'description' => 'Get current weather conditions for a city. ' .
                     'Returns temperature, conditions (sunny/rainy/cloudy), and humidity. ' .
                     'Use this when user asks about weather or temperature.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => 'City name, can include country like "London, UK"'
            ]
        ],
        'required' => ['city']
    ]
];
```

#### 4. Search Tool

```php
$searchTool = [
    'name' => 'search',
    'description' => 'Search for factual information on any topic. ' .
                     'Returns relevant information from knowledge sources. ' .
                     'Use this for facts, statistics, definitions, or recent information.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'query' => [
                'type' => 'string',
                'description' => 'Search query'
            ]
        ],
        'required' => ['query']
    ]
];
```

### 🔧 Tool Implementation

#### Tool Executor Pattern

```php
function executeTool($toolName, $input) {
    return match($toolName) {
        'calculate' => executeCalculator($input['expression']),
        'get_current_time' => getCurrentTime($input['timezone']),
        'get_weather' => getWeather($input['city']),
        'search' => performSearch($input['query']),
        default => "Unknown tool: {$toolName}"
    };
}

function executeCalculator($expression) {
    // Use safe math parser in production!
    return (string)eval("return {$expression};");
}

function getCurrentTime($timezone) {
    try {
        $dt = new DateTime('now', new DateTimeZone($timezone));
        return $dt->format('Y-m-d H:i:s T');
    } catch (Exception $e) {
        return "Error: Invalid timezone";
    }
}

function getWeather($city) {
    // Simulated - in production, call real weather API
    $conditions = ['sunny', 'cloudy', 'rainy', 'partly cloudy'];
    $temp = rand(50, 85);
    $condition = $conditions[array_rand($conditions)];

    return json_encode([
        'city' => $city,
        'temperature' => $temp . '°F',
        'conditions' => $condition,

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 44](/series/claude-php-developers/chapters/44-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 3 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/03-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="43"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 44](/series/claude-php-developers/chapters/44-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 3 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/03-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/03-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
