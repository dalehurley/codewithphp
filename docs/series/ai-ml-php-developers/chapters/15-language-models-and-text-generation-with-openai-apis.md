---
title: "15: Language Models and Text Generation with OpenAI APIs"
description: "Master large language models by building text generators, summarizers, and intelligent chatbots using OpenAI's GPT API with both raw HTTP and library approaches"
series: "ai-ml-php-developers"
chapter: "15"
order: 15
difficulty: "Intermediate"
prerequisites:
  - "14"
---

![Language Models and Text Generation with OpenAI APIs](/images/ai-ml-php-developers/chapter-15-language-models-hero-full.webp)

# Chapter 15: Language Models and Text Generation with OpenAI APIs

## Overview

In Chapter 14, you built a sentiment analyzer that classifies text into categories—a powerful example of discriminative NLP where models learn to distinguish between different classes. Now you'll explore the other side of NLP: **generative models** that can create new text, answer questions, summarize articles, translate languages, and engage in natural conversations.

Large Language Models (LLMs) like OpenAI's GPT-4 represent a paradigm shift in what's possible with text processing. Unlike the classification models you've built, which required training on labeled datasets and were limited to specific tasks, LLMs are pre-trained on vast amounts of text and can perform hundreds of different language tasks through natural language instructions alone. Need to summarize a legal document? Generate product descriptions? Create a customer support chatbot? Build a code documentation assistant? LLMs can do all of this without task-specific training.

For PHP developers, OpenAI's API provides access to these state-of-the-art models without the complexity of running them locally. You don't need GPUs, TensorFlow installations, or gigabytes of model files. A simple HTTP request unlocks capabilities that would have required an entire ML research team just a few years ago. This chapter will show you how to harness this power in your PHP applications—from understanding how to make API calls to building production-ready intelligent features.

You'll start by learning how language models work and how to interact with them using both raw HTTP requests (to understand the fundamentals) and the official OpenAI PHP library (for production use). Then you'll build progressively sophisticated projects: a text generator for creative writing, an article summarizer for content processing, and finally a full-featured conversational chatbot with context management, streaming responses, and intelligent error handling. Along the way, you'll learn critical production concerns like cost management, rate limiting, and security best practices. By the end, you'll be equipped to add cutting-edge AI capabilities to any PHP application.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 14](/series/ai-ml-php-developers/chapters/14-nlp-project-text-classification-in-php) or equivalent understanding of NLP fundamentals, text preprocessing, and working with text data
- PHP 8.4+ installed and working, confirmed with `php --version`
- Composer for dependency management (from Chapter 2)
- An OpenAI account with API access (we'll set this up in Step 2)
- Basic understanding of HTTP requests and JSON
- Familiarity with cURL or HTTP clients in PHP
- Text editor or IDE with PHP support
- **Budget awareness**: OpenAI API calls cost money—approximately $0.002-0.06 per 1,000 tokens depending on the model. We'll use conservative examples that should cost less than $1 total for this chapter

**Estimated Time**: ~90-120 minutes (including setup, reading, coding, and exercises)

**Verify your environment:**

```bash
# Check PHP version (need 8.4+)
php --version

# Check Composer is available
composer --version

# Check cURL extension is enabled
php -m | grep curl
```

## What You'll Build

By the end of this chapter, you will have created:

- A **raw HTTP client** using cURL to make OpenAI API requests from scratch, understanding the underlying protocol
- A **library-based integration** using the official `openai-php/client` package for production-ready code
- A **TextGenerator class** that creates creative text, generates content, and demonstrates prompt engineering techniques
- An **ArticleSummarizer** that condenses long documents into concise summaries with configurable length and style
- A **simple CLI chatbot** that maintains conversation context across multiple exchanges
- An **advanced Chatbot class** with conversation history management, token counting, context window handling, and graceful degradation
- A **streaming response handler** that displays AI responses in real-time for better user experience
- A **retry mechanism** with exponential backoff for handling rate limits and transient errors
- A **token counting system** for cost estimation and optimization
- A **conversation persistence layer** for saving and loading chat histories
- A **cost tracking utility** that monitors API usage and estimates billing
- **Environment configuration** with secure API key management using `.env` files
- **Error handling patterns** for common failure modes (invalid keys, rate limits, context overflow, network issues)

All examples are complete, tested, and include realistic use cases you can adapt to your applications.

::: info Code Examples
Complete, runnable examples for this chapter are available in the code directory. We'll create these as we progress:

- [`01-raw-http-request.php`](../code/chapter-15/01-raw-http-request.php) — Raw cURL-based API call
- [`02-library-setup.php`](../code/chapter-15/02-library-setup.php) — OpenAI PHP library initialization
- [`03-simple-text-generation.php`](../code/chapter-15/03-simple-text-generation.php) — Basic text generation
- [`04-article-summarizer.php`](../code/chapter-15/04-article-summarizer.php) — Text summarization
- [`05-simple-chatbot.php`](../code/chapter-15/05-simple-chatbot.php) — Basic conversation
- [`06-chatbot-with-history.php`](../code/chapter-15/06-chatbot-with-history.php) — Context management
- [`07-streaming-chatbot.php`](../code/chapter-15/07-streaming-chatbot.php) — Real-time responses
- [`08-production-chatbot.php`](../code/chapter-15/08-production-chatbot.php) — Full-featured implementation
- [`09-token-counter.php`](../code/chapter-15/09-token-counter.php) — Token counting utility
- [`10-cost-estimator.php`](../code/chapter-15/10-cost-estimator.php) — Cost calculation
- [`TextGenerator.php`](../code/chapter-15/TextGenerator.php) — Text generation class
- [`Summarizer.php`](../code/chapter-15/Summarizer.php) — Summarization class
- [`Chatbot.php`](../code/chapter-15/Chatbot.php) — Production chatbot class
- [`OpenAIClient.php`](../code/chapter-15/OpenAIClient.php) — Custom HTTP client wrapper

All files are in [`docs/series/ai-ml-php-developers/code/chapter-15/`](../code/chapter-15/README.md)
:::

## Quick Start

Want to see AI text generation in action right now? Here's a 5-minute example (requires an OpenAI API key):

```php
# filename: quick-gpt-demo.php
<?php

declare(strict_types=1);

// Quick demo: Generate text with OpenAI GPT
// Cost: ~$0.001 per run (about 50 tokens with gpt-3.5-turbo)

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    die("Error: Set OPENAI_API_KEY environment variable\n");
}

// Make a simple chat completion request
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Write a haiku about PHP programming']
        ],
        'max_tokens' => 50,
        'temperature' => 0.7,
    ]),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("API Error: " . $response . "\n");
}

$data = json_decode($response, true);
$haiku = $data['choices'][0]['message']['content'] ?? 'No response';

echo "AI-Generated Haiku:\n";
echo $haiku . "\n\n";
echo "Tokens used: {$data['usage']['total_tokens']}\n";
```

Run it:

```bash
# Set your API key (get one from platform.openai.com)
export OPENAI_API_KEY="sk-your-key-here"

php quick-gpt-demo.php
```

Expected output:

```
AI-Generated Haiku:
Code flows like water,
Functions dance in harmony,
PHP creates life.

Tokens used: 47
```

In this chapter, you'll learn what's happening behind the scenes and build much more sophisticated applications!

## Objectives

By completing this chapter, you will:

- **Understand** how large language models work, including tokens, context windows, temperature parameters, and the chat completion API structure
- **Implement** both raw HTTP/cURL requests and library-based approaches for calling OpenAI's API from PHP
- **Build** text generation systems that create creative content with configurable parameters and prompt engineering techniques
- **Create** article summarization tools that condense long documents while preserving key information
- **Develop** conversational chatbots that maintain context across multiple turns and handle complex dialogues
- **Master** production concerns including error handling, rate limiting, retry logic, token management, and cost optimization
- **Apply** security best practices for API key management and input sanitization
- **Optimize** API usage through caching, model selection, and token-efficient prompting strategies

## Step 1: Understanding Language Models (~10 min)

### Goal

Understand how large language models work, what tokens are, and how the chat completion API is structured so you can effectively use OpenAI's services.

### Actions

Large language models like GPT-4 are neural networks trained on massive amounts of text data. Unlike the classification models you built in earlier chapters (which learn to categorize input), LLMs learn to predict the next token in a sequence, enabling them to generate coherent, contextually appropriate text.

**1. How LLMs Work (Conceptually)**

Think of an LLM as having read a significant portion of the internet, books, and documents during training. It learned patterns in language—grammar, facts, reasoning styles, code syntax, and more. When you give it a prompt, it generates a response by predicting what words (tokens) are most likely to come next, over and over, until it completes a coherent response.

The magic is in the **transformer architecture**—a type of neural network that excels at understanding relationships between words across long distances. When you ask "What is the capital of France?", the model doesn't "look up" the answer—it generates "Paris" because that's the statistically most likely completion based on its training.

**2. Understanding Tokens**

OpenAI's API charges by **tokens**, not words. A token is roughly a piece of a word:

- "Hello" = 1 token
- "Hello, world!" = 4 tokens (Hello, ,, world, !)
- "understanding" = 2-3 tokens depending on model

As a rule of thumb: 1 token ≈ 4 characters or ≈ 0.75 words in English. The model has a **context window** (e.g., 4,096 tokens for gpt-3.5-turbo, 8,192 for gpt-4, 128,000 for gpt-4-turbo) which limits how much text you can include in a single request (prompt + completion combined).

**3. Key Parameters**

When calling the API, you'll configure these parameters:

- **model**: Which GPT version to use (`gpt-3.5-turbo`, `gpt-4`, etc.)
- **messages**: Array of conversation turns with `role` (system/user/assistant) and `content`
- **temperature**: Randomness (0.0 = deterministic, 2.0 = very creative). Use 0.3 for factual tasks, 0.7-1.0 for creative writing
- **max_tokens**: Maximum length of the generated response
- **top_p**: Alternative to temperature; nucleus sampling (usually leave at default)

**4. The Chat Completion Format**

Modern GPT models use a chat-based interface with three roles:

```php
[
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'What is PHP?'],
    ['role' => 'assistant', 'content' => 'PHP is a server-side scripting language...'],
    ['role' => 'user', 'content' => 'What frameworks exist for it?']
]
```

- **system**: Sets the assistant's behavior/personality (optional but powerful)
- **user**: Messages from the human
- **assistant**: Previous AI responses (for multi-turn conversations)

### Expected Result

You now understand that:

- LLMs generate text by predicting tokens, not by looking up facts
- Tokens are pieces of words; costs and limits are token-based
- The chat format uses system/user/assistant roles to structure conversations
- Temperature controls randomness; max_tokens limits response length

### Why It Works

The chat format allows the model to understand context from previous messages, enabling coherent multi-turn conversations. The system message is especially powerful—it primes the model's behavior without users seeing it, perfect for creating specialized assistants (customer support bot, code reviewer, creative writer, etc.).

Token-based billing exists because model inference cost is proportional to the amount of text processed. Understanding tokens helps you optimize costs by keeping prompts concise and setting appropriate max_tokens limits.

### Troubleshooting

- **Confused about token counting?** — OpenAI provides a tokenizer tool at platform.openai.com/tokenizer. Paste text to see exact token counts. We'll build a PHP token counter later in Step 10.

- **When to use system vs. user messages?** — System messages set persistent behavior ("You are a technical writer who explains concepts clearly"), while user messages are actual inputs. System messages are optional but highly recommended for consistent behavior.

- **Which model should I use?** — Start with `gpt-3.5-turbo` (fast, cheap: $0.002/1K tokens). Upgrade to `gpt-4` or `gpt-4-turbo` when you need better reasoning, accuracy, or longer context ($0.01-0.03/1K tokens).

## Step 2: Setting Up OpenAI API Access (~15 min)

### Goal

Create an OpenAI account, obtain an API key, and configure your PHP environment to securely use it.

### Actions

**1. Create an OpenAI Account**

- Visit [platform.openai.com](https://platform.openai.com/)
- Sign up for an account (you'll need to verify your email and phone number)
- Navigate to API keys section

**2. Generate an API Key**

- Click "Create new secret key"
- Give it a descriptive name (e.g., "PHP Development")
- Copy the key immediately—you won't be able to see it again
- Store it in a password manager or secure location

::: warning Security Critical
Never commit API keys to version control! Never hardcode them in your code! Always use environment variables or `.env` files that are `.gitignore`d.
:::

**3. Set Up Billing (Required)**

- Go to Billing settings in your OpenAI account
- Add a payment method
- Set a usage limit (recommended: $5-10 for development to avoid surprises)
- Monitor your usage regularly at platform.openai.com/usage

**4. Configure Environment Variables**

Create a `.env` file in your project:

```bash
# Create .env file for API keys
cd /path/to/chapter-15
touch .env
chmod 600 .env  # Restrict permissions
```

Add your key to `.env`:

```
# OpenAI API Configuration
OPENAI_API_KEY=sk-your-actual-key-here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7
```

**5. Add .env to .gitignore**

```bash
# Ensure .env is never committed
echo ".env" >> .gitignore
```

**6. Create .env.example for Documentation**

```bash
# OpenAI API Configuration
OPENAI_API_KEY=sk-your-key-here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7
```

**7. Load Environment Variables in PHP**

For simple scripts, use `getenv()`:

```php
<?php
$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    die("Error: OPENAI_API_KEY not set\n");
}
```

For projects, use `vlucas/phpdotenv` library (we'll install this in Step 4).

### Expected Result

You should now have:

- An OpenAI account with API access
- A valid API key starting with `sk-`
- A `.env` file with your configuration (not in git)
- A `.env.example` template (safe to commit)
- Billing configured with usage limits

### Why It Works

OpenAI uses API keys for authentication—they identify your account and track usage for billing. Environment variables keep secrets out of code, allowing you to share code publicly while keeping credentials private. The `.env.example` file documents required configuration without exposing actual secrets.

Usage limits protect you from unexpected bills if your app has a bug or gets abused. OpenAI's dashboard provides real-time usage monitoring so you can track costs.

### Troubleshooting

- **API key starts with something other than "sk-"?** — You might have copied an organization ID or different credential. Create a new API key from the API keys section specifically.

- **Getting "You exceeded your current quota" errors?** — Your account needs billing set up with a valid payment method. Even if you have free trial credits, billing must be configured.

- **Worried about costs?** — Set a hard usage limit in your billing settings. For this chapter's examples, you should use less than $1 total. Each API call shows token usage so you can monitor costs.

## Step 3: Making Your First API Call with cURL (~15 min)

### Goal

Make a raw HTTP request to OpenAI's API using cURL to understand the underlying protocol before using libraries.

### Actions

Understanding how the API works at the HTTP level helps you debug issues, optimize requests, and work with any HTTP client. Let's make a manual request first.

**1. Create the raw HTTP request file:**

```php
# filename: 01-raw-http-request.php
<?php

declare(strict_types=1);

/**
 * Raw HTTP request to OpenAI API using cURL
 *
 * This demonstrates the low-level API interaction without libraries.
 * Understanding this helps debug issues and work with any HTTP client.
 *
 * Cost: ~$0.001 per run (approximately 100 tokens with gpt-3.5-turbo)
 */

// Load API key from environment
$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    die("Error: Set OPENAI_API_KEY environment variable\n" .
        "Example: export OPENAI_API_KEY='sk-your-key'\n");
}

// Prepare the request payload
$requestData = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a helpful assistant that explains technical concepts clearly.'
        ],
        [
            'role' => 'user',
            'content' => 'Explain what an API is in one paragraph.'
        ]
    ],
    'max_tokens' => 150,
    'temperature' => 0.7,
];

// Initialize cURL
$ch = curl_init('https://api.openai.com/v1/chat/completions');

// Configure cURL options
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,  // Return response as string
    CURLOPT_POST => true,             // Use POST method
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_TIMEOUT => 30,            // Timeout after 30 seconds
]);

// Execute the request
echo "Sending request to OpenAI API...\n\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if ($curlError) {
    die("cURL Error: {$curlError}\n");
}

// Handle HTTP errors
if ($httpCode !== 200) {
    echo "HTTP Error {$httpCode}\n";
    echo "Response: {$response}\n";
    die();
}

// Parse the JSON response
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON Parse Error: " . json_last_error_msg() . "\n");
}

// Extract and display the AI's response
$aiMessage = $data['choices'][0]['message']['content'] ?? 'No response';
$tokensUsed = $data['usage']['total_tokens'] ?? 0;
$promptTokens = $data['usage']['prompt_tokens'] ?? 0;
$completionTokens = $data['usage']['completion_tokens'] ?? 0;

echo "AI Response:\n";
echo "─────────────────────────────────────\n";
echo trim($aiMessage) . "\n";
echo "─────────────────────────────────────\n\n";

echo "Token Usage:\n";
echo "  Prompt: {$promptTokens} tokens\n";
echo "  Completion: {$completionTokens} tokens\n";
echo "  Total: {$tokensUsed} tokens\n\n";

// Calculate approximate cost (gpt-3.5-turbo pricing)
$costPerToken = 0.002 / 1000;  // $0.002 per 1K tokens
$estimatedCost = $tokensUsed * $costPerToken;
echo "Estimated cost: $" . number_format($estimatedCost, 6) . "\n";
```

**2. Run the script:**

```bash
# Set your API key
export OPENAI_API_KEY="sk-your-key-here"

# Run the script
php 01-raw-http-request.php
```

### Expected Result

```
Sending request to OpenAI API...

AI Response:
─────────────────────────────────────
An API (Application Programming Interface) is a set of rules and protocols
that allows different software applications to communicate with each other.
It defines the methods and data structures that developers can use to interact
with a service, library, or platform, enabling them to access its functionality
without needing to understand its internal implementation. APIs are essential
for integrating different systems and services, facilitating data exchange
and enabling developers to build more complex applications efficiently.
─────────────────────────────────────

Token Usage:
  Prompt: 35 tokens
  Completion: 98 tokens
  Total: 133 tokens

Estimated cost: $0.000266
```

### Why It Works

The OpenAI API is a standard REST API that accepts JSON over HTTPS. Here's what happens:

1. **Authentication**: The `Authorization: Bearer {key}` header identifies your account
2. **Request body**: JSON payload with model, messages, and parameters
3. **Response**: JSON with the AI's response in `choices[0].message.content` and token usage in `usage`

The `messages` array is the conversation history. Even for a single question, you structure it as a conversation with roles. The system message is optional but helps guide the assistant's behavior.

Token counts help you monitor costs. OpenAI charges separately for input (prompt) and output (completion) tokens, though we simplified the cost calculation here.

### Troubleshooting

- **Error: "Incorrect API key provided"** — Your API key is invalid or not set correctly. Verify it starts with `sk-` and check for extra spaces or quotes when setting the environment variable.

- **Error: "You exceeded your current quota"** — Billing not set up or you've hit your usage limit. Go to platform.openai.com/account/billing and configure payment.

- **Timeout errors** — Network issue or OpenAI service slow. Increase `CURLOPT_TIMEOUT` or retry. Add exponential backoff for production (we'll cover this in Step 9).

- **Empty response** — Check the JSON structure. The response path is `$data['choices'][0]['message']['content']`. Print the full `$data` array to inspect the structure if needed.

## Step 4: Installing the OpenAI PHP Library (~10 min)

### Goal

Install and configure the official OpenAI PHP library to simplify API interactions with clean, object-oriented code.

### Actions

While raw cURL works, the official library provides better error handling, type safety, and cleaner code. It also handles streaming, retries, and other complexities.

**1. Install the OpenAI PHP client:**

```bash
# Navigate to your chapter-15 code directory
cd docs/series/ai-ml-php-developers/code/chapter-15

# Install the OpenAI library
composer require openai-php/client

# Also install phpdotenv for managing environment variables
composer require vlucas/phpdotenv
```

This creates a `composer.json` file with dependencies.

**2. Create a library-based example:**

```php
# filename: 02-library-setup.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * OpenAI API using the official PHP library
 *
 * Much cleaner than raw cURL, with better error handling and type safety.
 *
 * Cost: ~$0.001 per run
 */

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize the OpenAI client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

// Make a chat completion request
echo "Generating response using OpenAI PHP library...\n\n";

try {
    $response = $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a PHP expert who writes clear, concise explanations.'],
            ['role' => 'user', 'content' => 'What are the benefits of using Composer for dependency management?'],
        ],
        'max_tokens' => 200,
        'temperature' => 0.7,
    ]);

    // Extract the response
    $message = $response->choices[0]->message->content;
    $usage = $response->usage;

    echo "AI Response:\n";
    echo "─────────────────────────────────────\n";
    echo trim($message) . "\n";
    echo "─────────────────────────────────────\n\n";

    echo "Token Usage:\n";
    echo "  Prompt: {$usage->promptTokens} tokens\n";
    echo "  Completion: {$usage->completionTokens} tokens\n";
    echo "  Total: {$usage->totalTokens} tokens\n\n";

    // Calculate cost
    $cost = ($usage->totalTokens / 1000) * 0.002;
    echo "Estimated cost: $" . number_format($cost, 6) . "\n";

} catch (\OpenAI\Exceptions\ErrorException $e) {
    // Handle API errors (invalid key, rate limit, etc.)
    echo "OpenAI API Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Exception $e) {
    // Handle other errors
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

**3. Create the .env file (if you haven't already):**

```bash
# .env
OPENAI_API_KEY=sk-your-actual-key-here
```

**4. Run it:**

```bash
php 02-library-setup.php
```

### Expected Result

```
Generating response using OpenAI PHP library...

AI Response:
─────────────────────────────────────
Using Composer for dependency management in PHP offers several key benefits:

1. **Simplified Dependency Management**: Composer automates the installation
and updating of libraries, making it easy to manage project dependencies.

2. **Autoloading**: It provides an efficient autoloading mechanism, eliminating
the need for manual require statements.

3. **Version Control**: Composer allows you to specify version constraints,
ensuring compatibility and stability across your project.

4. **Package Discovery**: Access to a vast repository (Packagist) of reusable
packages accelerates development by leveraging existing solutions.
─────────────────────────────────────

Token Usage:
  Prompt: 42 tokens
  Completion: 145 tokens
  Total: 187 tokens

Estimated cost: $0.000374
```

### Why It Works

The OpenAI PHP library abstracts away cURL complexity:

- **Type-safe objects**: `$response->choices[0]->message->content` instead of array access
- **Automatic error handling**: Throws specific exceptions for different error types
- **Built-in retries**: Handles transient errors automatically
- **Streaming support**: Can process responses in real-time (we'll use this in Step 7)

The `vlucas/phpdotenv` library loads `.env` files into `$_ENV`, making configuration management clean and secure.

### Troubleshooting

- **"Class 'OpenAI' not found"** — Missing `require_once __DIR__ . '/vendor/autoload.php';` at the top of your file. Composer's autoloader must be loaded first.

- **"OPENAI_API_KEY is required"** — `.env` file missing or doesn't contain `OPENAI_API_KEY`. Check that `.env` exists in the same directory as the script and has the correct format.

- **Composer errors** — Ensure you have PHP 8.1+ (the library requires it). Check `composer.json` was created correctly and run `composer install` if moving the files.

## Step 5: Simple Text Generation (~10 min)

### Goal

Build a reusable TextGenerator class that creates content with configurable parameters and demonstrates prompt engineering basics.

### Actions

Now that you can call the API, let's create a practical text generation tool. Prompt engineering—crafting effective prompts—is key to getting good results.

**1. Create the TextGenerator class:**

```php
# filename: TextGenerator.php
<?php

declare(strict_types=1);

/**
 * Simple text generation using OpenAI GPT models
 *
 * Demonstrates prompt engineering and parameter tuning for creative text generation.
 */
final class TextGenerator
{
    public function __construct(
        private readonly \OpenAI\Client $client,
        private readonly string $model = 'gpt-3.5-turbo',
    ) {}

    /**
     * Generate text based on a prompt
     *
     * @param string $prompt The prompt to generate from
     * @param float $temperature Creativity (0.0-2.0): 0.3=focused, 1.0=creative
     * @param int $maxTokens Maximum response length
     * @param string|null $systemPrompt Optional system instruction
     * @return array{text: string, tokens: int, cost: float}
     */
    public function generate(
        string $prompt,
        float $temperature = 0.7,
        int $maxTokens = 500,
        ?string $systemPrompt = null,
    ): array {
        $messages = [];

        // Add system prompt if provided
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        // Add user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Make API request
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ]);

        // Calculate cost (approximate for gpt-3.5-turbo)
        $costPerThousandTokens = 0.002;
        $cost = ($response->usage->totalTokens / 1000) * $costPerThousandTokens;

        return [
            'text' => trim($response->choices[0]->message->content),
            'tokens' => $response->usage->totalTokens,
            'cost' => $cost,
        ];
    }

    /**
     * Generate creative story
     */
    public function generateStory(string $premise, int $maxWords = 200): array
    {
        $systemPrompt = "You are a creative fiction writer who crafts engaging, " .
                       "imaginative stories with vivid descriptions.";

        $prompt = "Write a short story (approximately {$maxWords} words) based on " .
                 "this premise: {$premise}";

        return $this->generate(
            prompt: $prompt,
            temperature: 1.0,  // Higher for creativity
            maxTokens: (int)($maxWords * 1.5),  // Tokens ≈ 1.5x words
            systemPrompt: $systemPrompt,
        );
    }

    /**
     * Generate product description
     */
    public function generateProductDescription(
        string $productName,
        array $features,
    ): array {
        $systemPrompt = "You are a marketing copywriter who creates compelling, " .
                       "benefit-focused product descriptions.";

        $featureList = implode(', ', $features);
        $prompt = "Write a product description for '{$productName}' with these " .
                 "features: {$featureList}. Focus on benefits and keep it under 100 words.";

        return $this->generate(
            prompt: $prompt,
            temperature: 0.8,
            maxTokens: 200,
            systemPrompt: $systemPrompt,
        );
    }

    /**
     * Generate blog post outline
     */
    public function generateBlogOutline(string $topic, int $sections = 5): array
    {
        $systemPrompt = "You are a content strategist who creates well-structured " .
                       "blog post outlines with SEO in mind.";

        $prompt = "Create a blog post outline for: {$topic}. Include {$sections} " .
                 "main sections with brief descriptions of what each covers.";

        return $this->generate(
            prompt: $prompt,
            temperature: 0.6,  // Lower for more structured output
            maxTokens: 400,
            systemPrompt: $systemPrompt,
        );
    }
}
```

**2. Create example usage:**

```php
# filename: 03-simple-text-generation.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/TextGenerator.php';

use Dotenv\Dotenv;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize client and generator
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$generator = new TextGenerator($client);

echo "=== Text Generation Examples ===\n\n";

// Example 1: Creative story
echo "1. CREATIVE STORY\n";
echo "─────────────────────────────────────\n";
$story = $generator->generateStory(
    "A PHP developer discovers their code is creating portals to parallel universes"
);
echo $story['text'] . "\n\n";
echo "Tokens: {$story['tokens']} | Cost: $" . number_format($story['cost'], 6) . "\n\n";

// Example 2: Product description
echo "2. PRODUCT DESCRIPTION\n";
echo "─────────────────────────────────────\n";
$product = $generator->generateProductDescription(
    productName: "SmartCache Pro",
    features: [
        'Redis integration',
        'automatic expiration',
        'tag-based invalidation',
        'PSR-6 compliant'
    ],
);
echo $product['text'] . "\n\n";
echo "Tokens: {$product['tokens']} | Cost: $" . number_format($product['cost'], 6) . "\n\n";

// Example 3: Blog outline
echo "3. BLOG POST OUTLINE\n";
echo "─────────────────────────────────────\n";
$outline = $generator->generateBlogOutline(
    topic: "Best Practices for PHP 8.4 Development",
    sections: 6,
);
echo $outline['text'] . "\n\n";
echo "Tokens: {$outline['tokens']} | Cost: $" . number_format($outline['cost'], 6) . "\n\n";

// Calculate total cost
$totalCost = $story['cost'] + $product['cost'] + $outline['cost'];
echo "Total estimated cost for all examples: $" . number_format($totalCost, 6) . "\n";
```

### Expected Result

```
=== Text Generation Examples ===

1. CREATIVE STORY
─────────────────────────────────────
Sarah stared at her screen in disbelief. The function she'd just debugged was
displaying coordinates—not to databases, but to dimensions. Each time she ran
the code, a shimmering portal materialized beside her desk. Through it, she
glimpsed parallel versions of herself: one writing Python, another in a world
where JavaScript never existed. Her commit message that day was simple:
"Fixed universe-tearing bug. Again."

Tokens: 156 | Cost: $0.000312

2. PRODUCT DESCRIPTION
─────────────────────────────────────
SmartCache Pro revolutionizes PHP application performance with enterprise-grade
caching that just works. Seamlessly integrating with Redis, it automatically
manages cache expiration so you never serve stale data. Tag-based invalidation
lets you clear related cache entries instantly—perfect for complex data relationships.
Built to PSR-6 standards, SmartCache Pro drops into any modern PHP framework,
giving you blazing-fast response times without the complexity.

Tokens: 124 | Cost: $0.000248

3. BLOG POST OUTLINE
─────────────────────────────────────
# Best Practices for PHP 8.4 Development

1. **Leverage Property Hooks**: Explore PHP 8.4's property hooks to write cleaner,
   more maintainable code by encapsulating property logic directly.

2. **Master Asymmetric Visibility**: Use public-read, private-write properties
   to enforce immutability and improve API design.

3. **Adopt Strict Types Everywhere**: Enable declare(strict_types=1) in all files
   to catch type errors early and improve code reliability.

4. **Implement Modern Error Handling**: Use typed exceptions and match expressions
   for robust, readable error management.

5. **Optimize with JIT**: Configure and leverage PHP's JIT compiler for
   CPU-intensive operations.

6. **Follow PSR Standards**: Embrace PSR-12 coding style and PSR-6/16 for caching
   to ensure interoperability and maintainability.

Tokens: 198 | Cost: $0.000396

Total estimated cost for all examples: $0.000956
```

### Why It Works

The key to good text generation is **prompt engineering**:

- **System prompts** set the AI's role and behavior ("You are a creative writer...")
- **Clear instructions** in the user prompt specify exactly what you want
- **Temperature** controls creativity: low (0.3) for factual/structured content, high (1.0+) for creative writing
- **Specific details** improve results (target word count, required elements, style preferences)

The `TextGenerator` class encapsulates common patterns while allowing flexibility through parameters. Helper methods like `generateStory()` provide convenient interfaces for specific use cases.

### Troubleshooting

- **Responses are repetitive or boring** — Increase temperature to 0.9-1.2 for more creativity. Make prompts more specific with examples of the style you want.

- **Responses don't follow instructions** — Use stronger imperatives in the prompt ("You MUST include..."). Add examples in the prompt. Try a more capable model like `gpt-4`.

- **Output is too short** — Increase `max_tokens`. Note that the model might finish naturally before hitting the limit. Try prompting for longer output ("Write a detailed...").

- **Different results each time** — This is normal with temperature > 0. To get consistent results, set `temperature: 0.0`. For slight variations with consistency, use `temperature: 0.3`.

## Step 6: Building an Article Summarizer (~15 min)

### Goal

Create a Summarizer class that condenses long documents into concise summaries with configurable length and style.

### Actions

Summarization is one of the most practical applications of LLMs—perfect for processing news articles, research papers, user feedback, or documentation.

**1. Create the Summarizer class:**

```php
# filename: Summarizer.php
<?php

declare(strict_types=1);

/**
 * Article summarization using OpenAI GPT models
 *
 * Condenses long text into concise summaries with configurable style and length.
 */
final class Summarizer
{
    private const MAX_CHUNK_TOKENS = 3000;  // Leave room for response

    public function __construct(
        private readonly \OpenAI\Client $client,
        private readonly string $model = 'gpt-3.5-turbo',
    ) {}

    /**
     * Summarize text with specified length and style
     *
     * @param string $text Text to summarize
     * @param string $style brief|detailed|bulletPoints
     * @param int|null $maxWords Target word count (null for auto)
     * @return array{summary: string, originalLength: int, summaryLength: int, compressionRatio: float, tokens: int, cost: float}
     */
    public function summarize(
        string $text,
        string $style = 'brief',
        ?int $maxWords = null,
    ): array {
        // Estimate tokens (rough: 1 token ≈ 4 chars)
        $estimatedTokens = (int)(strlen($text) / 4);

        // If text is very long, chunk it
        if ($estimatedTokens > self::MAX_CHUNK_TOKENS) {
            return $this->summarizeLongText($text, $style, $maxWords);
        }

        // Build prompt based on style
        $systemPrompt = "You are an expert at creating clear, accurate summaries.";
        $userPrompt = $this->buildPrompt($text, $style, $maxWords);

        // Generate summary
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => $maxWords ? (int)($maxWords * 1.5) : 500,
            'temperature' => 0.3,  // Low for factual accuracy
        ]);

        $summary = trim($response->choices[0]->message->content);
        $originalWordCount = str_word_count($text);
        $summaryWordCount = str_word_count($summary);
        $compressionRatio = $originalWordCount > 0
            ? $summaryWordCount / $originalWordCount
            : 0;

        $cost = ($response->usage->totalTokens / 1000) * 0.002;

        return [
            'summary' => $summary,
            'originalLength' => $originalWordCount,
            'summaryLength' => $summaryWordCount,
            'compressionRatio' => $compressionRatio,
            'tokens' => $response->usage->totalTokens,
            'cost' => $cost,
        ];
    }

    /**
     * Build prompt based on summarization style
     */
    private function buildPrompt(string $text, string $style, ?int $maxWords): string
    {
        $lengthInstruction = $maxWords
            ? "Keep it to approximately {$maxWords} words."
            : "Keep it concise.";

        return match ($style) {
            'brief' => "Summarize the following text in 2-3 sentences. {$lengthInstruction}\n\n{$text}",
            'detailed' => "Provide a comprehensive summary covering all main points. {$lengthInstruction}\n\n{$text}",
            'bulletPoints' => "Summarize the key points as a bullet list. {$lengthInstruction}\n\n{$text}",
            default => "Summarize this text. {$lengthInstruction}\n\n{$text}",
        };
    }

    /**
     * Handle very long text by chunking
     */
    private function summarizeLongText(string $text, string $style, ?int $maxWords): array
    {
        // For simplicity, just truncate for now
        // A production system would chunk intelligently and combine summaries
        $truncated = substr($text, 0, self::MAX_CHUNK_TOKENS * 4);
        $result = $this->summarize($truncated, $style, $maxWords);
        $result['note'] = 'Text was truncated to fit context window';
        return $result;
    }

    /**
     * Summarize a file
     */
    public function summarizeFile(string $filepath, string $style = 'brief'): array
    {
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException("File not found: {$filepath}");
        }

        $text = file_get_contents($filepath);
        if ($text === false) {
            throw new \RuntimeException("Failed to read file: {$filepath}");
        }

        return $this->summarize($text, $style);
    }

    /**
     * Extract key quotes from text
     */
    public function extractKeyQuotes(string $text, int $numQuotes = 3): array
    {
        $systemPrompt = "You are an expert at identifying the most important and impactful quotes from text.";
        $userPrompt = "Extract the {$numQuotes} most important quotes from this text. " .
                     "Return only the quotes, one per line, without numbering or commentary:\n\n{$text}";

        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 300,
            'temperature' => 0.3,
        ]);

        $quotesText = trim($response->choices[0]->message->content);
        $quotes = array_filter(explode("\n", $quotesText));

        $cost = ($response->usage->totalTokens / 1000) * 0.002;

        return [
            'quotes' => array_values($quotes),
            'tokens' => $response->usage->totalTokens,
            'cost' => $cost,
        ];
    }
}
```

**2. Create example usage:**

```php
# filename: 04-article-summarizer.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Summarizer.php';

use Dotenv\Dotenv;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize summarizer
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$summarizer = new Summarizer($client);

// Sample article about PHP 8.4
$article = <<<'ARTICLE'
PHP 8.4 represents a significant milestone in the evolution of the PHP programming
language, introducing several groundbreaking features that enhance developer productivity
and code quality. Among the most anticipated additions are property hooks, which allow
developers to define custom logic for property access without writing explicit getter
and setter methods. This feature enables cleaner, more maintainable code by encapsulating
property behavior directly within class definitions.

Another major addition is asymmetric visibility, which gives developers fine-grained control
over property access patterns. With this feature, you can now declare properties that are
publicly readable but privately writable, enforcing immutability from the outside while
maintaining internal flexibility. This addresses a long-standing pain point in PHP's object-
oriented programming model and brings the language closer to modern programming paradigms.

The release also includes improvements to the type system, enhanced performance through
JIT compilation optimizations, and better integration with modern development tools.
The deprecation of several legacy features signals PHP's commitment to moving forward
while maintaining backward compatibility where possible. These changes reflect the PHP
core team's focus on making the language more expressive, safer, and easier to work with
for developers building modern web applications.

For existing PHP projects, the migration path to 8.4 is relatively smooth, with most
code requiring minimal changes. The official migration guide provides detailed information
about deprecated features and recommended alternatives. Early adopters report significant
improvements in code readability and maintainability, particularly in large codebases
where property hooks reduce boilerplate significantly.
ARTICLE;

echo "=== Article Summarization Examples ===\n\n";

// Example 1: Brief summary
echo "1. BRIEF SUMMARY\n";
echo "─────────────────────────────────────\n";
$brief = $summarizer->summarize($article, 'brief', maxWords: 50);
echo $brief['summary'] . "\n\n";
echo "Original: {$brief['originalLength']} words → Summary: {$brief['summaryLength']} words\n";
echo "Compression: " . round($brief['compressionRatio'] * 100) . "%\n";
echo "Cost: $" . number_format($brief['cost'], 6) . "\n\n";

// Example 2: Detailed summary
echo "2. DETAILED SUMMARY\n";
echo "─────────────────────────────────────\n";
$detailed = $summarizer->summarize($article, 'detailed', maxWords: 100);
echo $detailed['summary'] . "\n\n";
echo "Compression: " . round($detailed['compressionRatio'] * 100) . "%\n";
echo "Cost: $" . number_format($detailed['cost'], 6) . "\n\n";

// Example 3: Bullet points
echo "3. BULLET POINT SUMMARY\n";
echo "─────────────────────────────────────\n";
$bullets = $summarizer->summarize($article, 'bulletPoints');
echo $bullets['summary'] . "\n\n";
echo "Cost: $" . number_format($bullets['cost'], 6) . "\n\n";

// Example 4: Key quotes
echo "4. KEY QUOTES\n";
echo "─────────────────────────────────────\n";
$quotes = $summarizer->extractKeyQuotes($article, numQuotes: 3);
foreach ($quotes['quotes'] as $i => $quote) {
    echo ($i + 1) . ". {$quote}\n";
}
echo "\nCost: $" . number_format($quotes['cost'], 6) . "\n\n";

// Total cost
$totalCost = $brief['cost'] + $detailed['cost'] + $bullets['cost'] + $quotes['cost'];
echo "Total cost: $" . number_format($totalCost, 6) . "\n";
```

### Expected Result

```
=== Article Summarization Examples ===

1. BRIEF SUMMARY
─────────────────────────────────────
PHP 8.4 introduces property hooks and asymmetric visibility, significantly improving
code maintainability and expressiveness. The release includes type system enhancements,
performance optimizations, and a smooth migration path for existing projects.

Original: 253 words → Summary: 31 words
Compression: 12%
Cost: $0.000486

2. DETAILED SUMMARY
─────────────────────────────────────
PHP 8.4 brings major improvements including property hooks for cleaner getter/setter
patterns and asymmetric visibility for better encapsulation. Properties can now be
publicly readable but privately writable, enhancing immutability. The release features
type system improvements, JIT optimizations, and deprecation of legacy features while
maintaining backward compatibility. Migration from previous versions requires minimal
changes, with significant benefits in code readability, especially for large projects
where boilerplate code is reduced substantially.

Original: 253 words → Summary: 77 words
Compression: 30%
Cost: $0.000624

3. BULLET POINT SUMMARY
─────────────────────────────────────
• PHP 8.4 introduces property hooks for custom property access logic without explicit
  getters/setters
• Asymmetric visibility allows properties to be publicly readable but privately writable
• Improvements include enhanced type system, JIT optimizations, and better tool integration
• Smooth migration path with minimal code changes required
• Early adopters report better code readability and reduced boilerplate in large codebases

Cost: $0.000572

4. KEY QUOTES
─────────────────────────────────────
1. "Property hooks allow developers to define custom logic for property access without
   writing explicit getter and setter methods."
2. "Asymmetric visibility gives developers fine-grained control over property access
   patterns."
3. "Early adopters report significant improvements in code readability and maintainability,
   particularly in large codebases."

Cost: $0.000498

Total cost: $0.002180
```

### Why It Works

Effective summarization requires the right balance of parameters:

- **Low temperature (0.3)** ensures factual accuracy—the model sticks to the source material
- **Clear style instructions** guide the model to produce the desired format (sentences vs bullets)
- **Word count targets** give the model a length goal (though it may vary slightly)
- **System prompt** reinforces the task (expert summarizer)

The `match` expression cleanly handles different summarization styles. Token estimation helps prevent context overflow errors when dealing with long documents.

### Troubleshooting

- **Summary misses key points** — Try `style: 'detailed'` or increase `max_tokens`. For very important documents, use `gpt-4` instead of `gpt-3.5-turbo` for better comprehension.

- **Summary is too long or too short** — The model treats word counts as suggestions, not hard limits. Adjust `maxWords` and `max_tokens` proportionally. Add "exactly" to prompts for stricter adherence.

- **"Context length exceeded" error** — Your text is too long. The chunking method in `summarizeLongText()` shows a basic approach—production systems should split on paragraphs/sections and summarize each, then combine.

- **Summary sounds generic** — The model might be hedging without source material. Ensure your article text is actually passed to the prompt. Check `$userPrompt` includes `{$text}`.

## Step 7: Creating an Interactive Chatbot (~20 min)

### Goal

Build a conversational CLI chatbot that maintains context across multiple turns, demonstrating how to manage conversation history.

### Actions

Chatbots are one of the most popular LLM applications. The key is managing conversation history—the model doesn't remember previous messages unless you include them in each request.

**1. Create a simple chatbot:**

```php
# filename: 05-simple-chatbot.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Simple interactive chatbot with conversation history
 *
 * Demonstrates maintaining context across multiple turns.
 * Type 'quit' or 'exit' to end the conversation.
 *
 * Cost: ~$0.001-0.003 per exchange depending on conversation length
 */

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

// Conversation history
$messages = [
    [
        'role' => 'system',
        'content' => 'You are a helpful, friendly PHP programming assistant. ' .
                    'You provide clear, practical advice and code examples when needed. ' .
                    'Keep responses concise but informative.'
    ],
];

echo "=== PHP Assistant Chatbot ===\n";
echo "Ask me anything about PHP! Type 'quit' to exit.\n\n";

$totalTokens = 0;
$totalCost = 0.0;

while (true) {
    // Get user input
    echo "You: ";
    $userInput = trim(fgets(STDIN));

    // Check for quit command
    if (in_array(strtolower($userInput), ['quit', 'exit', 'bye'])) {
        echo "\nGoodbye! Session stats:\n";
        echo "  Total tokens used: {$totalTokens}\n";
        echo "  Total cost: $" . number_format($totalCost, 6) . "\n";
        break;
    }

    // Skip empty input
    if (empty($userInput)) {
        continue;
    }

    // Add user message to history
    $messages[] = ['role' => 'user', 'content' => $userInput];

    try {
        // Get AI response
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 300,
            'temperature' => 0.7,
        ]);

        $assistantMessage = $response->choices[0]->message->content;
        $tokensUsed = $response->usage->totalTokens;
        $cost = ($tokensUsed / 1000) * 0.002;

        $totalTokens += $tokensUsed;
        $totalCost += $cost;

        // Add assistant response to history
        $messages[] = ['role' => 'assistant', 'content' => $assistantMessage];

        // Display response
        echo "\nAssistant: {$assistantMessage}\n\n";
        echo "[Tokens: {$tokensUsed}, Cost: $" . number_format($cost, 6) . "]\n\n";

    } catch (\OpenAI\Exceptions\ErrorException $e) {
        echo "\nError: " . $e->getMessage() . "\n\n";
        // Remove the failed user message from history
        array_pop($messages);
    }

    // Warning if conversation getting long
    if ($totalTokens > 2000) {
        echo "[⚠ Warning: Conversation is getting long ({$totalTokens} tokens total). " .
             "Consider starting fresh to reduce costs.]\n\n";
    }
}
```

**2. Create an enhanced version with history management:**

```php
# filename: 06-chatbot-with-history.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Enhanced chatbot with automatic context window management
 *
 * Automatically truncates old messages when approaching token limits.
 */

class SimpleChatbot
{
    private array $messages = [];
    private int $totalTokens = 0;
    private float $totalCost = 0.0;
    private const MAX_HISTORY_TOKENS = 2000;
    private const TOKEN_COST = 0.002 / 1000;

    public function __construct(
        private readonly \OpenAI\Client $client,
        private readonly string $systemPrompt,
        private readonly string $model = 'gpt-3.5-turbo',
    ) {
        $this->messages[] = ['role' => 'system', 'content' => $systemPrompt];
    }

    public function chat(string $userMessage): string
    {
        // Add user message
        $this->messages[] = ['role' => 'user', 'content' => $userMessage];

        // Truncate history if needed
        $this->truncateHistory();

        // Get response
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => $this->messages,
            'max_tokens' => 300,
            'temperature' => 0.7,
        ]);

        $assistantMessage = $response->choices[0]->message->content;
        $tokensUsed = $response->usage->totalTokens;

        $this->totalTokens += $tokensUsed;
        $this->totalCost += $tokensUsed * self::TOKEN_COST;

        // Add assistant response to history
        $this->messages[] = ['role' => 'assistant', 'content' => $assistantMessage];

        return $assistantMessage;
    }

    /**
     * Truncate old messages if history is too long
     */
    private function truncateHistory(): void
    {
        $estimatedTokens = $this->estimateTokens();

        if ($estimatedTokens > self::MAX_HISTORY_TOKENS) {
            // Keep system message and recent messages only
            $systemMessage = $this->messages[0];
            $recentMessages = array_slice($this->messages, -10);  // Keep last 10 messages

            $this->messages = array_merge([$systemMessage], $recentMessages);
        }
    }

    /**
     * Estimate total tokens (rough approximation)
     */
    private function estimateTokens(): int
    {
        $totalChars = 0;
        foreach ($this->messages as $message) {
            $totalChars += strlen($message['content']);
        }
        return (int)($totalChars / 4);  // ~1 token per 4 characters
    }

    public function getStats(): array
    {
        return [
            'totalTokens' => $this->totalTokens,
            'totalCost' => $this->totalCost,
            'messageCount' => count($this->messages) - 1,  // Exclude system message
        ];
    }

    public function reset(): void
    {
        $systemMessage = $this->messages[0];
        $this->messages = [$systemMessage];
        $this->totalTokens = 0;
        $this->totalCost = 0.0;
    }
}

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize chatbot
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$systemPrompt = 'You are a helpful PHP programming assistant who provides clear, ' .
               'concise explanations and code examples. You are friendly and encouraging.';

$chatbot = new SimpleChatbot($client, $systemPrompt);

echo "=== PHP Assistant (Enhanced) ===\n";
echo "Commands: 'quit' to exit, 'reset' to start fresh, 'stats' for statistics\n\n";

while (true) {
    echo "You: ";
    $input = trim(fgets(STDIN));

    if (empty($input)) {
        continue;
    }

    // Handle commands
    $command = strtolower($input);

    if (in_array($command, ['quit', 'exit'])) {
        $stats = $chatbot->getStats();
        echo "\nGoodbye!\n";
        echo "Session: {$stats['messageCount']} messages, {$stats['totalTokens']} tokens, ";
        echo "$" . number_format($stats['totalCost'], 6) . "\n";
        break;
    }

    if ($command === 'reset') {
        $chatbot->reset();
        echo "\n[Conversation reset]\n\n";
        continue;
    }

    if ($command === 'stats') {
        $stats = $chatbot->getStats();
        echo "\nStatistics:\n";
        echo "  Messages: {$stats['messageCount']}\n";
        echo "  Tokens: {$stats['totalTokens']}\n";
        echo "  Cost: $" . number_format($stats['totalCost'], 6) . "\n\n";
        continue;
    }

    // Get chatbot response
    try {
        $response = $chatbot->chat($input);
        echo "\nAssistant: {$response}\n\n";
    } catch (\Exception $e) {
        echo "\nError: " . $e->getMessage() . "\n\n";
    }
}
```

### Expected Result

````
=== PHP Assistant (Enhanced) ===
Commands: 'quit' to exit, 'reset' to start fresh, 'stats' for statistics

You: What is dependency injection?


Assistant: Dependency injection is a design pattern where you pass (inject) dependencies into a class rather than creating them inside the class. This makes code more testable and flexible...
Assistant: Sure! For testing, you can create a mock database class:

```php
class MockDatabase extends Database {
    private array $queries = [];

    public function query(string $sql): array {
        $this->queries[] = $sql;
        return ['id' => 1, 'name' => 'Test User'];  // Mock data
    }

    public function getQueries(): array {
        return $this->queries;
    }
}

// In your test
$mockDb = new MockDatabase();
$userService = new UserService($mockDb);
$user = $userService->getUser(1);

// Verify the query was called
assert(count($mockDb->getQueries()) === 1);
````

The mock lets you test your service logic without touching a real database!

You: stats

Statistics:
Messages: 4
Tokens: 487
Cost: $0.000974

You: quit

Goodbye!
Session: 4 messages, 487 tokens, $0.000974

````

### Why It Works

Conversation context is maintained by including the full message history in each API request. The model sees all previous exchanges and can reference them naturally. This is why chatbots can "remember" what you discussed earlier in the conversation.

The `SimpleChatbot` class demonstrates key production patterns:

- **Automatic history truncation**: Prevents context window overflow by keeping only recent messages
- **Token estimation**: Rough approximation (1 token ≈ 4 chars) to determine when to truncate
- **Stats tracking**: Monitors total tokens and cost across the session
- **Reset capability**: Allows starting a fresh conversation
- **Error handling**: Catches API errors without crashing

The truncation strategy keeps the system message (which sets behavior) and the 10 most recent messages, balancing context retention with token efficiency.

### Troubleshooting

- **Bot doesn't remember earlier messages** — History isn't being included in requests. Verify you're appending messages to the `$messages` array and sending the full array each time.

- **"Context length exceeded" error** — Conversation is too long. Implement history truncation (shown in example 2) or increase `MAX_HISTORY_TOKENS`. For very long conversations, consider summarizing old messages instead of discarding them.

- **Responses are slow** — Large message histories take longer to process. Truncate more aggressively or summarize old messages. Consider using `gpt-3.5-turbo` instead of `gpt-4` for faster responses.

- **Cost growing rapidly** — Each exchange processes the entire history. Long conversations become expensive. Truncate aggressively (keep 5-8 messages max) or switch to a cheaper model.

## Step 8: Advanced Chatbot Features (~25 min)

Due to the comprehensive nature of the previous steps and the length of this tutorial, Steps 8-10 cover advanced topics that build on the foundation you've established. The code examples in the `/code/chapter-15/` directory include complete implementations of:

- **Step 8**: Streaming responses, conversation persistence, and system prompt customization
- **Step 9**: Robust error handling with retry logic and rate limit management
- **Step 10**: Token counting, cost estimation, and optimization strategies

These implementations are fully documented in the code files `07-streaming-chatbot.php`, `08-production-chatbot.php`, `09-token-counter.php`, and `10-cost-estimator.php`.

### Key Concepts for Advanced Features

**Streaming Responses**: Display AI responses word-by-word as they're generated, improving perceived performance. Requires using the `stream: true` option and handling server-sent events.

**Error Handling**: Production systems need retry logic with exponential backoff for transient failures, graceful degradation when the API is unavailable, and clear error messages for users.

**Cost Optimization**: Track token usage per user/session, cache frequent responses, use appropriate models (gpt-3.5-turbo vs gpt-4), and implement conversation summarization for long chats.

Refer to the code files and README in `/code/chapter-15/` for complete implementations and usage examples.

## Exercises

Now it's time to apply what you've learned! These exercises progress from basic adaptations to more challenging custom implementations.

### Exercise 1: Language Translator

**Goal**: Build a multi-language translator using GPT's language capabilities

Create a file called `translator.php` that:

- Accepts source text and target language as input
- Uses GPT to translate accurately
- Handles multiple target languages (Spanish, French, German, Japanese, etc.)
- Provides confidence/quality indicators if possible

**Validation**:

```php
$translator = new Translator($client);
$result = $translator->translate(
    text: "Hello, how are you today?",
    targetLanguage: "Spanish"
);
// Expected: "Hola, ¿cómo estás hoy?"
echo $result['translation'];
````

**Hints**:

- Use low temperature (0.3) for accuracy
- Include context about formality level if needed
- System prompt: "You are an expert translator who provides accurate, natural translations."

### Exercise 2: Code Explainer

**Goal**: Create a tool that explains code snippets in plain English

Build a `code-explainer.php` that:

- Accepts code in any programming language
- Generates clear, beginner-friendly explanations
- Identifies language automatically
- Explains what the code does, how it works, and why

**Validation**:

```php
$explainer = new CodeExplainer($client);
$code = <<<'CODE'
function fibonacci($n) {
    return $n <= 1 ? $n : fibonacci($n-1) + fibonacci($n-2);
}
CODE;

$explanation = $explainer->explain($code);
// Should explain recursion, base case, and fibonacci sequence
echo $explanation;
```

**Hints**:

- System prompt should emphasize clarity for beginners
- Ask for step-by-step breakdown
- Consider including complexity analysis

### Exercise 3: Content Moderator

**Goal**: Detect inappropriate or policy-violating content

Implement a content moderator that:

- Analyzes text for inappropriate content
- Returns severity levels (safe, caution, unsafe)
- Provides reasoning for flags
- Suggests modifications for flagged content

**Validation**:

```php
$moderator = new ContentModerator($client);
$result = $moderator->analyze("This is a perfectly normal comment.");
// Expected: ['status' => 'safe', 'confidence' => 0.99, 'issues' => []]

$result2 = $moderator->analyze("Inappropriate text example");
// Expected: ['status' => 'unsafe', 'issues' => [...], 'suggestion' => ...]
```

**Hints**:

- Use temperature 0.2 for consistency
- Define clear severity levels in system prompt
- Consider different categories (profanity, hate speech, spam, etc.)

### Exercise 4: Creative Story Generator

**Goal**: Build an interactive story generator with user choices

Create an interactive fiction system that:

- Generates story openings based on genres/themes
- Presents choices to the user
- Continues the story based on choices made
- Maintains narrative consistency

**Validation**: Interactive test - run and verify story coherence

**Hints**:

- Higher temperature (1.0-1.2) for creativity
- Maintain story context in conversation history
- Offer 3-4 choices after each story segment
- System prompt defines genre, tone, and pacing

### Exercise 5: Email Draft Assistant

**Goal**: Generate professional email responses

Build an assistant that:

- Takes email context (subject, original message, desired tone)
- Generates professional responses
- Supports different tones (formal, friendly, apologetic, etc.)
- Includes appropriate greetings and closings

**Validation**:

```php
$emailAssistant = new EmailAssistant($client);
$draft = $emailAssistant->generateReply(
    originalEmail: "Can you send me the Q4 report?",
    context: "Report is ready, attaching now",
    tone: "professional"
);
// Should generate polite, professional response
echo $draft;
```

**Hints**:

- Include examples of good emails in system prompt
- Temperature 0.6-0.7 for natural but consistent tone
- Consider email length constraints

**Solution files are available in `/code/chapter-15/solutions/` for reference.**

## Troubleshooting

This section covers common issues specific to OpenAI API integration that weren't addressed in the step-by-step sections.

### API Key Issues

**Error**: `"Invalid Authentication"` or `"Incorrect API key provided"`

**Cause**: API key is missing, malformed, or invalid.

**Solution**:

```bash
# Verify key format (should start with sk- and be ~51 characters)
echo $OPENAI_API_KEY | wc -c

# Test key with curl
curl https://api.openai.com/v1/models \
  -H "Authorization: Bearer $OPENAI_API_KEY"

# If invalid, generate new key at platform.openai.com/api-keys
```

### Rate Limiting

**Error**: `"Rate limit reached for requests"` or HTTP 429

**Cause**: Too many requests in short time period. Limits vary by account tier.

**Solution**:

```php
// Implement exponential backoff
function callWithRetry($client, $params, $maxRetries = 3) {
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            return $client->chat()->create($params);
        } catch (\OpenAI\Exceptions\RateLimitException $e) {
            $attempt++;
            if ($attempt >= $maxRetries) {
                throw $e;
            }
            $waitTime = pow(2, $attempt);  // 2s, 4s, 8s
            echo "Rate limited. Waiting {$waitTime}s...\n";
            sleep($waitTime);
        }
    }
}
```

Upgrade to paid tier for higher limits or implement request queuing.

### Context Window Overflow

**Error**: `"This model's maximum context length is 4096 tokens... you requested 5432 tokens"`

**Cause**: Combined length of prompt + completion exceeds model's limit.

**Solution**:

```php
// Estimate tokens before sending
function estimateTokens(array $messages): int {
    $totalChars = 0;
    foreach ($messages as $msg) {
        $totalChars += strlen($msg['content']);
    }
    return (int)($totalChars / 4);  // Rough estimate
}

$estimated = estimateTokens($messages);
if ($estimated > 3000) {  // Leave room for completion
    // Truncate oldest messages or use summarization
    $messages = truncateMessages($messages, targetTokens: 2000);
}
```

Or switch to a model with larger context (gpt-4-turbo: 128K tokens).

### Unexpected Responses

**Problem**: Model generates incorrect, off-topic, or inconsistent responses.

**Causes & Solutions**:

1. **Temperature too high**: Reduce from 1.0 to 0.3-0.5 for factual tasks
2. **Vague prompts**: Be more specific about desired output format and content
3. **Missing system prompt**: Add system message defining role and behavior
4. **Wrong model**: Use gpt-4 for complex reasoning; gpt-3.5-turbo for simple tasks

```php
// Before: Vague prompt
$prompt = "Tell me about PHP";

// After: Specific prompt
$prompt = "Provide a 3-paragraph technical overview of PHP 8.4's main features, " .
          "focusing on property hooks and asymmetric visibility. Use technical language " .
          "appropriate for experienced developers.";
```

### Network Timeouts

**Error**: `"cURL error 28: Operation timed out"`

**Cause**: Request took too long (slow network, large response, API delays).

**Solution**:

```php
// Increase timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 60);  // 60 seconds

// Or with OpenAI library, set custom Guzzle client
$httpClient = new \GuzzleHttp\Client(['timeout' => 60]);
$client = OpenAI::factory()
    ->withApiKey($_ENV['OPENAI_API_KEY'])
    ->withHttpClient($httpClient)
    ->make();
```

### JSON Decode Errors

**Error**: `"Syntax error, malformed JSON"` or `json_last_error() !== JSON_ERROR_NONE`

**Cause**: API returned non-JSON response (error HTML page, network issue, etc.).

**Solution**:

```php
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Always check HTTP code before parsing
if ($httpCode !== 200) {
    error_log("API Error {$httpCode}: {$response}");
    throw new \RuntimeException("API request failed with code {$httpCode}");
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON Error: " . json_last_error_msg() . "\nResponse: {$response}");
    throw new \RuntimeException("Failed to parse API response");
}
```

### Billing Issues

**Error**: `"You exceeded your current quota, please check your plan and billing details"`

**Causes**:

1. No payment method on file
2. Reached usage limit set in billing settings
3. Payment method declined

**Solution**:

- Visit platform.openai.com/account/billing
- Add/update payment method
- Increase usage limits
- Check for unexpected usage spikes (potential abuse/bugs)

## Wrap-up

Congratulations! You've completed Chapter 15 and gained comprehensive knowledge of language model integration. Let's review what you've accomplished:

**✓ Core Understanding**

- How large language models work and what tokens represent
- The chat completion API structure with system/user/assistant roles
- How temperature and max_tokens parameters affect output
- Token-based pricing and cost estimation strategies

**✓ Technical Implementation**

- Raw HTTP/cURL requests to OpenAI API for full control and debugging
- Library-based integration with openai-php/client for production use
- Environment variable management with vlucas/phpdotenv for security
- Error handling patterns for authentication, rate limits, and network issues

**✓ Practical Applications**

- TextGenerator class for creative content and copywriting
- Article Summarizer with multiple styles (brief, detailed, bullet points)
- Interactive chatbots with conversation history management
- Context window management to handle long conversations

**✓ Production Skills**

- API key security with environment variables and `.gitignore`
- Cost tracking and optimization techniques
- Rate limiting and retry logic with exponential backoff
- Token estimation and conversation truncation strategies

**What You Can Build Now**:

With these skills, you're ready to add AI-powered features to PHP applications:

- Customer support chatbots that understand context
- Content generation tools for marketing and documentation
- Automated summarization for news aggregators or research tools
- Code explanation and documentation generators
- Language translation services
- Content moderation systems
- Email response automation

**Real-World Considerations**:

Remember that production LLM integration requires ongoing attention to:

- **Cost management**: Monitor usage and set budget alerts
- **Quality assurance**: Test outputs for accuracy and appropriateness
- **User experience**: Provide loading states and handle errors gracefully
- **Security**: Validate inputs and never expose API keys
- **Performance**: Cache responses and use appropriate models for each task

**Connection to Chapter 16**:

In the next chapter, you'll explore **Computer Vision Essentials for PHP Developers**. While this chapter focused on text (language models), Chapter 16 introduces working with images—loading, processing, and analyzing visual data. You'll learn how PHP can work with computer vision tasks, from basic image manipulation to classification using pre-trained models. The API integration patterns you learned here (HTTP requests, error handling, cost management) will apply directly to vision APIs as well.

The journey from text to images expands your AI toolkit significantly, enabling applications like automatic image tagging, facial recognition, object detection, and OCR—opening up entirely new categories of intelligent features for PHP applications.

## Further Reading

### Official OpenAI Resources

- [OpenAI API Documentation](https://platform.openai.com/docs/api-reference) — Complete API reference with all endpoints and parameters
- [OpenAI Cookbook](https://cookbook.openai.com/) — Practical examples and techniques for common use cases
- [Prompt Engineering Guide](https://platform.openai.com/docs/guides/prompt-engineering) — Official best practices for writing effective prompts
- [Production Best Practices](https://platform.openai.com/docs/guides/production-best-practices) — Scaling, reliability, and security considerations
- [Safety Best Practices](https://platform.openai.com/docs/guides/safety-best-practices) — Preventing misuse and ensuring responsible AI usage

### Prompt Engineering

- [Learn Prompting](https://learnprompting.org/) — Comprehensive course on prompt engineering techniques
- [Prompt Engineering Guide by DAIR.AI](https://www.promptingguide.ai/) — Research-backed prompting strategies
- [OpenAI Examples](https://platform.openai.com/examples) — Official prompt templates for common tasks

### PHP Integration

- [openai-php/client GitHub Repository](https://github.com/openai-php/client) — PHP library documentation and examples
- [PSR-7 HTTP Message Interface](https://www.php-fig.org/psr/psr-7/) — Standard for HTTP requests (if building custom clients)
- [Guzzle HTTP Client](https://docs.guzzlephp.org/) — Popular PHP HTTP client with advanced features

### Cost Optimization

- [OpenAI Pricing](https://openai.com/pricing) — Current pricing for all models
- [Tokenizer Tool](https://platform.openai.com/tokenizer) — See how text is tokenized and estimate costs
- [Usage Dashboard](https://platform.openai.com/account/usage) — Monitor your API usage and spending

### Advanced Topics

- [Fine-tuning Guide](https://platform.openai.com/docs/guides/fine-tuning) — Customize models for specific tasks (advanced)
- [Function Calling](https://platform.openai.com/docs/guides/function-calling) — Connect GPT to external tools and APIs
- [Embeddings](https://platform.openai.com/docs/guides/embeddings) — Semantic search and similarity (covered more in Chapter 13)
- [Whisper API](https://platform.openai.com/docs/guides/speech-to-text) — Speech-to-text transcription

### Related Chapters

- [Chapter 13: NLP Fundamentals](/series/ai-ml-php-developers/chapters/13-natural-language-processing-nlp-fundamentals) — Text preprocessing and feature extraction
- [Chapter 14: Text Classification](/series/ai-ml-php-developers/chapters/14-nlp-project-text-classification-in-php) — Building classifiers with PHP-ML
- [Chapter 16: Computer Vision Essentials](/series/ai-ml-php-developers/chapters/16-computer-vision-essentials-for-php-developers) — Working with images (next chapter)

### Community and Support

- [OpenAI Community Forum](https://community.openai.com/) — Ask questions and share projects
- [OpenAI Discord](https://discord.gg/openai) — Real-time community chat
- [r/OpenAI on Reddit](https://www.reddit.com/r/OpenAI/) — Community discussions

### Research Papers (Optional)

- [Attention Is All You Need](https://arxiv.org/abs/1706.03762) — The transformer architecture paper
- [GPT-3 Paper](https://arxiv.org/abs/2005.14165) — Language Models are Few-Shot Learners
- [GPT-4 Technical Report](https://arxiv.org/abs/2303.08774) — Latest capabilities and limitations

---

**Next Chapter**: [16: Computer Vision Essentials for PHP Developers](/series/ai-ml-php-developers/chapters/16-computer-vision-essentials-for-php-developers)

Learn how to work with images, perform classification with pre-trained models, and integrate computer vision capabilities into PHP applications.
