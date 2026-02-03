---
title: "00: Environment Setup and Preparation"
description: "Install PHP 8.4, Composer, and the Claude PHP Agent framework. Configure API keys, verify dependencies, and run your first agent"
series: "agentic-ai-php-developers"
chapter: 0
difficulty: "Advanced"
estimatedTime: "PT60M"
datePublished: "2026-01-31"
teaches: ["PHP 8.4 environment setup", "Claude Agent framework installation", "Dependency validation", "Basic agent execution"]
keywords: ["PHP 8.4 setup", "claude-php/claude-php-agent framework", "Agentic AI", "API configuration", "Redis for PHP"]
---

# Chapter 00: Environment Setup and Preparation

## Overview

Before we build production-grade agentic AI systems, we need a clean, repeatable development environment. In this chapter, you’ll install the core tooling, configure your API credentials, and verify that `claude-php/claude-php-agent` can run a minimal task.

The goal isn’t just “it works on my machine.” We’ll set up a baseline that’s safe, reproducible, and ready for queues, caching, and tool execution later in the series.

## Chapter Outline

1. **Install and verify PHP + Composer**
2. **Create a new project and install `claude-php/claude-php-agent`**
3. **Configure environment variables securely**
4. **Validate dependencies (Redis, database)**
5. **Run a minimal agent to verify end-to-end functionality**
6. **Optional: Docker setup for consistent environments**

## Prerequisites

- A local development machine or server
- Terminal access
- Basic familiarity with Composer

**Estimated Time:** ~60 minutes

## What You’ll Build

By the end of this chapter, you will have:

- A working PHP 8.4+ environment
- A fresh project with `claude-php/claude-php-agent` installed
- Secure API key configuration
- Verified Redis and database connectivity
- A runnable “hello agent” script

::: info Code examples
All runnable code for this chapter is available in the [agentic-ai-php-developers/00-environment-setup](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/00-environment-setup) directory.
:::

## Step 1: Install and Verify PHP + Composer (~10 min)

### Goal

Ensure you’re running PHP 8.4+ with Composer installed.

### Actions

1. **Check PHP version:**

```bash
php -v
```

2. **Check Composer version:**

```bash
composer -V
```

### Expected Result

You should see PHP 8.4+ and Composer 2.x installed.

### Troubleshooting

- **PHP version too old** — Update PHP via your package manager or use a tool like `phpbrew`.
- **Composer missing** — Install from [getcomposer.org](https://getcomposer.org/).

## Step 2: Create a Project and Install `claude-php/claude-php-agent` (~10 min)

### Goal

Create a clean project and install the agent package.

### Actions

```bash
mkdir agentic-ai && cd agentic-ai
composer init --no-interaction --name="yourname/agentic-ai"
composer require claude-php/claude-php-agent
composer require claude-php/claude-php-sdk
```

### Expected Result

A new `composer.json` with both `claude-php/claude-php-agent` (the agents framework) and `claude-php/claude-php-sdk` (the Claude API client) installed.

## Step 3: Configure Environment Variables (~10 min)

### Goal

Store your Anthropic API key safely.

### Actions

If you are using the provided code samples, copy the environment template:

```bash
cp env.example .env
```

Otherwise, create a `.env` file in your project root manually:

```bash
cat > .env << 'EOF'
ANTHROPIC_API_KEY=your-key-here
EOF
```

### Best Practice

Never hardcode keys in source files. Use `.env` files locally and secret managers in production. Ensure `.env` is added to your `.gitignore` file.

## Step 4: Validate Redis and Database Dependencies (~15 min)

### Goal

Confirm the dependencies you’ll use later are available.

### Actions

**Redis:**

```bash
redis-cli ping
```

Expected response: `PONG`

**Database (SQLite check):**

```bash
php -r "new PDO('sqlite:./agentic.sqlite'); echo 'OK';"
```

### Troubleshooting

- **Redis not running** — Start Redis or install it via your package manager.
- **PDO error** — Ensure the SQLite PDO extension is enabled.

## Step 5: Run a Minimal Agent (~10 min)

### Goal

Verify that the agent can call Claude successfully.

### Actions

Create `hello-agent.php`:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(
    apiKey: getenv('ANTHROPIC_API_KEY')
);

// Create agent with the client
$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant.');

// Run the agent
$result = $agent->run('Say hello in one sentence and mention PHP.');

echo $result->getAnswer() . PHP_EOL;
```

Run it:

```bash
# Load environment variables (or set manually)
export $(grep -v '^#' .env | xargs)

# Run the script
php hello-agent.php
```

### Expected Result

A short response from Claude, such as:

```
Hello! PHP is a powerful language for building web applications.
```

### Troubleshooting

- **401 Unauthorized** — Check that `ANTHROPIC_API_KEY` is set correctly.
- **Network error** — Confirm outbound HTTPS is allowed.

## Step 6 (Optional): Dockerized Dev Environment (~5 min)

If you prefer consistent environments, create a basic Docker setup later in the series. For now, local installs are sufficient.

## Wrap-up

You now have a complete environment for the rest of the series:

- PHP + Composer verified
- `claude-php/claude-php-agent` installed
- Credentials configured safely
- Redis + database connectivity confirmed
- A working agent script

In **Chapter 01**, we’ll build on this foundation by defining what makes an agent *agentic* and how to reason about tools, memory, and control loops.
