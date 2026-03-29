---
title: "01: Environment Setup & First API Call"
description: "Set up your development environment and make your first successful API call to OpenAI using PHP"
series: "openai-php"
chapter: 1
order: 1
difficulty: "Beginner"
prerequisites:
  - "/series/openai-php/chapters/00-introduction-to-openai"
  - "PHP 8.1+ installed"
  - "Composer installed"
---

![Environment Setup & First API Call](/images/openai-php/chapter-01-environment-setup-hero-full.webp)

[Home](/series/openai-php) > [Chapter 00](/series/openai-php/chapters/00-introduction-to-openai) > Environment Setup & First API Call

# Chapter 01: Environment Setup & First API Call

<span class="difficulty-badge difficulty-beginner">Beginner</span>
<span class="time-badge">30-40 minutes</span>

## Overview

Making your first successful API call to OpenAI is an exciting milestone! In this chapter, you'll go from zero to a working OpenAI integration in your PHP environment. We'll walk through every step: creating your OpenAI account, securing your API key, setting up your PHP environment, and making that crucial first API call.

This isn't just about getting something to work—we'll establish best practices from day one. You'll learn secure API key management, proper error handling, and environment configuration that scales from development to production. By the end of this chapter, you'll have a solid foundation to build upon throughout the series.

We'll use both raw HTTP requests (to understand what's happening under the hood) and the official OpenAI PHP SDK (for production use). Understanding both approaches gives you the flexibility to choose the right tool for each situation and debug issues when they arise.

## What You'll Learn

- 🔑 Create an OpenAI account and obtain API keys securely
- 🛠️ Set up a PHP project with proper dependency management
- 🔒 Configure environment variables for secure API key storage
- 🌐 Make HTTP requests to OpenAI's API endpoints
- 📦 Install and use the official OpenAI PHP SDK
- ✅ Verify your setup with test calls
- 🐛 Handle common setup errors and issues
- 📝 Understand API request/response formats

## Prerequisites

Before starting, ensure you have:

- ✅ **PHP 8.1 or higher** installed (`php --version`)
- ✅ **Composer** for dependency management (`composer --version`)
- ✅ **Terminal/Command-line access**
- ✅ **Text editor or IDE** (VS Code, PhpStorm, etc.)
- ✅ **Credit card** for OpenAI account (required even for free tier)
- ✅ **Internet connection** for API calls

---

## Quick Checklist

By the end of this chapter, you'll have:

- [ ] Created OpenAI account
- [ ] Generated API key
- [ ] Set up PHP project structure
- [ ] Configured environment variables
- [ ] Installed dependencies (Guzzle or OpenAI SDK)
- [ ] Made first successful API call with raw HTTP
- [ ] Made API call using OpenAI PHP SDK
- [ ] Implemented basic error handling
- [ ] Verified setup works correctly

---

## Step 1: Create Your OpenAI Account

### Sign Up for OpenAI

1. **Navigate to OpenAI Platform**
   - Go to [platform.openai.com/signup](https://platform.openai.com/signup)

2. **Create Account**
   - Sign up with email, Google, or Microsoft account
   - Verify your email address
   - Complete phone verification (required for security)

3. **Add Payment Method**
   - Navigate to [platform.openai.com/account/billing](https://platform.openai.com/account/billing)
   - Add a credit card (required even for free tier)
   - Set spending limits to control costs (highly recommended!)

**💡 Cost Protection Tip:**
Set a monthly spending limit (e.g., $10-20) while learning:
```
Settings > Billing > Usage limits > Set hard limit
```

### Understanding Pricing

OpenAI uses pay-as-you-go pricing:

| Model | Input | Output |
|-------|--------|--------|
| GPT-4 Turbo | $10.00 / 1M tokens | $30.00 / 1M tokens |
| GPT-4 | $30.00 / 1M tokens | $60.00 / 1M tokens |
| GPT-3.5 Turbo | $0.50 / 1M tokens | $1.50 / 1M tokens |

**For this chapter:** You'll use ~100-200 tokens total (~$0.001-0.002)

---

## Step 2: Generate API Keys

### Create Your First API Key

1. **Navigate to API Keys**
   - Go to [platform.openai.com/api-keys](https://platform.openai.com/api-keys)

2. **Create New Key**
   - Click "Create new secret key"
   - Give it a descriptive name (e.g., "Development - Local Machine")
   - Click "Create secret key"

3. **⚠️ IMPORTANT: Copy and Save Immediately**
   - The key is shown only once
   - Copy it immediately: `sk-...` (starts with `sk-`)
   - Store it temporarily in a secure location
   - NEVER commit this to version control

### API Key Best Practices

**DO:**
- ✅ Use different keys for development, staging, and production
- ✅ Store keys in environment variables
- ✅ Set up spending limits for each key
- ✅ Rotate keys regularly (every 90 days)
- ✅ Name keys descriptively

**DON'T:**
- ❌ Commit keys to Git repositories
- ❌ Share keys in chat, email, or documentation
- ❌ Hardcode keys in source files
- ❌ Use production keys in development

---

## Step 3: Set Up Your PHP Project

### Create Project Structure

```bash
# Create project directory
mkdir openai-php-tutorial
cd openai-php-tutorial

# Initialize Composer
composer init --name="yourname/openai-tutorial" --type=project --no-interaction

# Create directory structure
mkdir src
mkdir tests
touch .env
touch .env.example
touch .gitignore
```

### Configure `.gitignore`

```bash
# Create .gitignore to protect API keys
cat > .gitignore << 'EOF'
# Dependencies
/vendor/

# Environment variables
.env

# IDE
.idea/
.vscode/
*.swp
*.swo

# Operating System
.DS_Store
Thumbs.db
EOF
```

### Set Up Environment Variables

**Create `.env.example` (safe to commit):**

```bash
cat > .env.example << 'EOF'
# OpenAI Configuration
OPENAI_API_KEY=your_api_key_here
OPENAI_ORG_ID=your_org_id_here (optional)

# Application Settings
APP_ENV=development
LOG_LEVEL=debug
EOF
```

**Create `.env` (NEVER commit):**

```bash
cat > .env << 'EOF'
# OpenAI Configuration
OPENAI_API_KEY=sk-your-actual-api-key-here

# Application Settings
APP_ENV=development
LOG_LEVEL=debug
EOF
```

⚠️ **Replace `sk-your-actual-api-key-here` with your real API key!**

### Install Environment Variable Loader

```bash
composer require vlucas/phpdotenv
```

This library loads `.env` files into PHP's environment:

```php
<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['OPENAI_API_KEY'];
```

---

## Step 4: Make Your First API Call (Raw HTTP)

Let's start by making a raw HTTP request to understand what's happening.

### Install Guzzle HTTP Client

```bash
composer require guzzlehttp/guzzle
```

### Create First API Call Script

Create `src/01-basic-api-call.php`:

```php
<?php

declare(strict_types=1);

/**
 * Chapter 01: First OpenAI API Call
 *
 * This script demonstrates making a basic API call to OpenAI's Chat Completions endpoint
 * using raw HTTP requests with Guzzle.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Verify API key exists
$apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
if (!$apiKey) {
    die("Error: OPENAI_API_KEY not found in .env file\n");
}

// Configuration
$endpoint = 'https://api.openai.com/v1/chat/completions';

// Create HTTP client
$client = new Client([
    'timeout' => 30.0,
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $apiKey,
    ],
]);

// Prepare request payload
$payload = [
    'model' => 'gpt-3.5-turbo',  // Fast and cost-effective model
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a helpful assistant for PHP developers.'
        ],
        [
            'role' => 'user',
            'content' => 'Say "Hello from OpenAI!" and explain what you can help with in one sentence.'
        ]
    ],
    'max_tokens' => 100,  // Limit response length
    'temperature' => 0.7,  // Moderate creativity
];

echo "Making API call to OpenAI...\n";
echo str_repeat('=', 70) . "\n\n";

try {
    // Make the API request
    $response = $client->post($endpoint, [
        'json' => $payload
    ]);

    // Get response body
    $body = json_decode($response->getBody()->getContents(), true);

    // Extract the assistant's message
    $message = $body['choices'][0]['message']['content'] ?? 'No response';

    // Display results
    echo "✅ SUCCESS!\n\n";
    echo "Response from OpenAI:\n";
    echo str_repeat('-', 70) . "\n";
    echo $message . "\n";
    echo str_repeat('-', 70) . "\n\n";

    // Display usage information
    echo "Token Usage:\n";
    echo "  Input tokens:  {$body['usage']['prompt_tokens']}\n";
    echo "  Output tokens: {$body['usage']['completion_tokens']}\n";
    echo "  Total tokens:  {$body['usage']['total_tokens']}\n\n";

    // Calculate approximate cost (GPT-3.5 Turbo pricing)
    $inputCost = ($body['usage']['prompt_tokens'] / 1000000) * 0.50;
    $outputCost = ($body['usage']['completion_tokens'] / 1000000) * 1.50;
    $totalCost = $inputCost + $outputCost;

    echo "Approximate Cost: $" . number_format($totalCost, 6) . "\n";

} catch (GuzzleException $e) {
    echo "❌ ERROR: API request failed\n\n";
    echo "Error message: " . $e->getMessage() . "\n";

    if ($e->hasResponse()) {
        $errorBody = $e->getResponse()->getBody()->getContents();
        $errorData = json_decode($errorBody, true);

        echo "\nAPI Error Details:\n";
        echo "  Type: " . ($errorData['error']['type'] ?? 'unknown') . "\n";
        echo "  Message: " . ($errorData['error']['message'] ?? 'unknown') . "\n";
    }

    exit(1);
} catch (Exception $e) {
    echo "❌ ERROR: Unexpected error\n\n";
    echo "Error message: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Setup complete! Your environment is ready to use OpenAI.\n";
```

### Run Your First API Call

```bash
php src/01-basic-api-call.php
```

**Expected Output:**

```
Making API call to OpenAI...
======================================================================

✅ SUCCESS!

Response from OpenAI:
----------------------------------------------------------------------
Hello from OpenAI! I can help you with PHP development questions,
code examples, debugging, best practices, and API integrations.
----------------------------------------------------------------------

Token Usage:
  Input tokens:  32
  Output tokens: 24
  Total tokens:  56

Approximate Cost: $0.000052

✅ Setup complete! Your environment is ready to use OpenAI.
```

---

## Step 5: Using the OpenAI PHP SDK

While raw HTTP requests work, the official SDK provides a better developer experience.

### Install OpenAI PHP SDK

```bash
composer require openai-php/client
```

### Create SDK Example

Create `src/02-sdk-api-call.php`:

```php
<?php

declare(strict_types=1);

/**
 * Chapter 01: Using OpenAI PHP SDK
 *
 * This script demonstrates using the official OpenAI PHP SDK
 * for a cleaner, more maintainable approach.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use OpenAI;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Verify API key
$apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
if (!$apiKey) {
    die("Error: OPENAI_API_KEY not found in .env file\n");
}

// Create OpenAI client
$client = OpenAI::client($apiKey);

echo "Using OpenAI PHP SDK\n";
echo str_repeat('=', 70) . "\n\n";

try {
    // Make API call using SDK
    $result = $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant for PHP developers.'
            ],
            [
                'role' => 'user',
                'content' => 'In one sentence, what makes PHP great for AI integration?'
            ],
        ],
        'max_tokens' => 100,
        'temperature' => 0.7,
    ]);

    // Extract response
    $message = $result->choices[0]->message->content;

    echo "✅ SUCCESS!\n\n";
    echo "Response:\n";
    echo str_repeat('-', 70) . "\n";
    echo $message . "\n";
    echo str_repeat('-', 70) . "\n\n";

    // Display usage
    echo "Token Usage:\n";
    echo "  Input tokens:  {$result->usage->promptTokens}\n";
    echo "  Output tokens: {$result->usage->completionTokens}\n";
    echo "  Total tokens:  {$result->usage->totalTokens}\n\n";

    // Model info
    echo "Model: {$result->model}\n";
    echo "Finish reason: {$result->choices[0]->finishReason}\n";

} catch (\OpenAI\Exceptions\ErrorException $e) {
    echo "❌ OpenAI API Error:\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  Code: {$e->getCode()}\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Unexpected Error:\n";
    echo "  Message: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ SDK setup verified!\n";
```

### Run SDK Example

```bash
php src/02-sdk-api-call.php
```

---

## Step 6: Verify Configuration

Create a comprehensive verification script.

Create `src/03-verify-setup.php`:

```php
<?php

declare(strict_types=1);

/**
 * Chapter 01: Environment Verification
 *
 * Verify that your environment is correctly configured for OpenAI development.
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "OpenAI PHP Environment Verification\n";
echo str_repeat('=', 70) . "\n\n";

$allGood = true;

// Check PHP version
echo "1. Checking PHP version... ";
$phpVersion = PHP_VERSION;
$required = '8.1.0';
if (version_compare($phpVersion, $required, '>=')) {
    echo "✅ PHP $phpVersion (>= $required required)\n";
} else {
    echo "❌ PHP $phpVersion (>= $required required)\n";
    $allGood = false;
}

// Check required extensions
echo "2. Checking required extensions...\n";
$required_extensions = ['curl', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    echo "   - $ext: ";
    if (extension_loaded($ext)) {
        echo "✅\n";
    } else {
        echo "❌ Missing\n";
        $allGood = false;
    }
}

// Check Composer
echo "3. Checking Composer autoload... ";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "✅\n";
} else {
    echo "❌ Run 'composer install'\n";
    $allGood = false;
}

// Check .env file
echo "4. Checking .env file... ";
if (file_exists(__DIR__ . '/../.env')) {
    echo "✅\n";
} else {
    echo "❌ Create .env file\n";
    $allGood = false;
}

// Load environment and check API key
echo "5. Checking OPENAI_API_KEY... ";
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
    if ($apiKey && str_starts_with($apiKey, 'sk-')) {
        echo "✅ Found (starts with 'sk-')\n";
    } else {
        echo "❌ Invalid or missing\n";
        $allGood = false;
    }
} catch (Exception $e) {
    echo "❌ Error loading .env\n";
    $allGood = false;
}

// Check required packages
echo "6. Checking required packages...\n";
$packages = [
    'guzzlehttp/guzzle' => 'GuzzleHttp\\Client',
    'vlucas/phpdotenv' => 'Dotenv\\Dotenv',
    'openai-php/client' => 'OpenAI',
];

foreach ($packages as $package => $class) {
    echo "   - $package: ";
    if (class_exists($class)) {
        echo "✅\n";
    } else {
        echo "❌ Run 'composer require $package'\n";
        $allGood = false;
    }
}

// Test API connectivity
echo "7. Testing API connectivity... ";
if ($apiKey && $allGood) {
    try {
        $client = OpenAI::client($apiKey);
        $result = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Hi']
            ],
            'max_tokens' => 5,
        ]);
        echo "✅ API call successful\n";
    } catch (Exception $e) {
        echo "❌ API call failed: " . $e->getMessage() . "\n";
        $allGood = false;
    }
} else {
    echo "⏭️  Skipped (fix other issues first)\n";
}

echo "\n" . str_repeat('=', 70) . "\n";
if ($allGood) {
    echo "✅ All checks passed! Your environment is ready.\n";
    exit(0);
} else {
    echo "❌ Some checks failed. Please fix the issues above.\n";
    exit(1);
}
```

### Run Verification

```bash
php src/03-verify-setup.php
```

**Expected output when everything is configured:**

```
OpenAI PHP Environment Verification
======================================================================

1. Checking PHP version... ✅ PHP 8.2.0 (>= 8.1.0 required)
2. Checking required extensions...
   - curl: ✅
   - json: ✅
   - mbstring: ✅
3. Checking Composer autoload... ✅
4. Checking .env file... ✅
5. Checking OPENAI_API_KEY... ✅ Found (starts with 'sk-')
6. Checking required packages...
   - guzzlehttp/guzzle: ✅
   - vlucas/phpdotenv: ✅
   - openai-php/client: ✅
7. Testing API connectivity... ✅ API call successful

======================================================================
✅ All checks passed! Your environment is ready.
```

---

## Understanding the API Response

Let's examine what OpenAI returns:

```json
{
  "id": "chatcmpl-8sK3jxQ...",
  "object": "chat.completion",
  "created": 1706789123,
  "model": "gpt-3.5-turbo-0125",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "Hello from OpenAI! I can help you..."
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 32,
    "completion_tokens": 24,
    "total_tokens": 56
  }
}
```

**Key fields:**

| Field | Description |
|-------|-------------|
| `id` | Unique identifier for this completion |
| `model` | Actual model version used |
| `choices[0].message.content` | The AI's response |
| `choices[0].finish_reason` | Why generation stopped (`stop`, `length`, `content_filter`) |
| `usage.prompt_tokens` | Tokens in your input |
| `usage.completion_tokens` | Tokens in AI's output |
| `usage.total_tokens` | Total tokens (determines cost) |

---

## Common Issues & Solutions

### Issue: "Invalid API Key"

**Symptoms:**
```
401 Unauthorized - Invalid API key
```

**Solutions:**
1. Verify key starts with `sk-`
2. Check `.env` file has correct key
3. Ensure no extra spaces or quotes
4. Confirm key hasn't been revoked

### Issue: "Rate Limit Exceeded"

**Symptoms:**
```
429 Too Many Requests - Rate limit reached
```

**Solutions:**
1. Wait a moment and retry
2. Check usage at platform.openai.com/usage
3. Verify spending limits aren't reached
4. Implement retry logic (covered in Chapter 05)

### Issue: "Model Not Found"

**Symptoms:**
```
404 Not Found - Model 'gpt-4' not found
```

**Solutions:**
1. Verify model name spelling
2. Check if you have access to the model
3. Use `gpt-3.5-turbo` if GPT-4 not available
4. Visit platform.openai.com/account/limits

### Issue: Environment Variables Not Loading

**Symptoms:**
```
Error: OPENAI_API_KEY not found
```

**Solutions:**
1. Verify `.env` file exists in project root
2. Check `phpdotenv` is installed
3. Ensure `Dotenv::createImmutable()` points to correct directory
4. Try absolute path: `Dotenv::createImmutable('/full/path/to/project')`

### Issue: Certificate Verification Errors

**Symptoms:**
```
cURL error 60: SSL certificate problem
```

**Solutions:**
1. Update CA certificates on your system
2. (Temporary workaround) Disable verification in Guzzle:
```php
$client = new Client([
    'verify' => false  // Not recommended for production!
]);
```

---

## Best Practices Established

In this chapter, you've established several best practices:

### Security
- ✅ Store API keys in environment variables
- ✅ Never commit `.env` to version control
- ✅ Use `.env.example` for documentation
- ✅ Set spending limits

### Project Structure
- ✅ Separate source code (`src/`) from dependencies (`vendor/`)
- ✅ Use Composer for dependency management
- ✅ Implement proper error handling
- ✅ Use type declarations (`declare(strict_types=1)`)

### Development Workflow
- ✅ Verify setup before building applications
- ✅ Track token usage and costs
- ✅ Test with simple requests first
- ✅ Use descriptive variable names

---

## Exercises

### Exercise 1: Personalize the Response

Modify `01-basic-api-call.php` to:
1. Ask the AI to introduce itself as a PHP expert named "PHPal"
2. Request a response about Laravel
3. Change temperature to 0.9 for more creative responses

### Exercise 2: Create a Helper Function

Create a reusable function:

```php
function askOpenAI(string $question, float $temperature = 0.7): string {
    // Your implementation here
}

// Usage:
echo askOpenAI("What is dependency injection?");
```

### Exercise 3: Multi-Turn Conversation

Build on the examples to have a 3-message conversation:
1. User: "Hello"
2. Assistant: (response)
3. User: "Tell me about PHP 8.2 features"

Hint: Add assistant's first response to messages array before second request.

### Exercise 4: Error Handling

Enhance error handling to:
1. Retry once if request fails
2. Log errors to a file
3. Return user-friendly error messages

### Challenge: Cost Calculator

Create a script that:
1. Takes a message as command-line argument
2. Estimates token count before making request
3. Makes the API call
4. Compares estimated vs actual token usage
5. Displays cost breakdown

---

## Key Takeaways

- ✅ OpenAI API requires authentication via API keys stored securely
- ✅ Environment variables keep sensitive data out of code
- ✅ Both raw HTTP and the SDK work—SDK is recommended for production
- ✅ Every API call returns usage information for cost tracking
- ✅ Proper error handling is essential from the start
- ✅ Setup verification prevents issues down the line
- ✅ Token usage determines costs—monitor from day one

---

## What's Next?

Congratulations! You've successfully:
- ✅ Created an OpenAI account
- ✅ Configured secure API key management
- ✅ Set up a PHP project with best practices
- ✅ Made successful API calls using HTTP and SDK
- ✅ Verified your environment is ready

In the next chapter, you'll dive deep into understanding OpenAI's different models, their capabilities, pricing, and how to choose the right model for your use case.

👉 **[Chapter 02: Understanding OpenAI Models](/series/openai-php/chapters/02-understanding-openai-models)**

---

## Additional Resources

- [OpenAI API Documentation](https://platform.openai.com/docs)
- [OpenAI PHP SDK GitHub](https://github.com/openai-php/client)
- [Guzzle Documentation](https://docs.guzzlephp.org)
- [PHP dotenv Documentation](https://github.com/vlucas/phpdotenv)
- [OpenAI Pricing](https://openai.com/pricing)

---

## Code Repository

All code examples from this chapter are available at:
```
/code-samples/openai-php/chapter-01/
```

Files:
- `01-basic-api-call.php` - Raw HTTP request example
- `02-sdk-api-call.php` - OpenAI SDK example
- `03-verify-setup.php` - Environment verification
- `.env.example` - Environment template
- `README.md` - Quick reference

---

[← Previous: Chapter 00](/series/openai-php/chapters/00-introduction-to-openai) | [Next: Chapter 02 →](/series/openai-php/chapters/02-understanding-openai-models)
