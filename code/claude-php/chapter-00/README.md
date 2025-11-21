# Chapter 00: Quick Start Guide

Welcome to the Claude for PHP Developers series! This chapter gets you up and running with Claude API in PHP.

## 🎯 What You'll Learn

- Making your first API call to Claude
- Text generation and analysis
- Code analysis with Claude
- Data extraction from unstructured text
- Tracking API costs and token usage

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer installed
- Anthropic API key ([get one here](https://console.anthropic.com/))

## 🚀 Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure API Key

```bash
cp .env.example .env
```

Edit `.env` and add your Anthropic API key:

```
ANTHROPIC_API_KEY=sk-ant-your-actual-key-here
```

### 3. Run Examples

```bash
# Basic quickstart
php examples/quickstart.php

# Text generation
php examples/text-generation.php

# Code analysis
php examples/code-analysis.php

# Data extraction
php examples/data-extraction.php

# Cost tracking
php examples/cost-tracking.php
```

## 📁 Files Overview

### Examples

- **`quickstart.php`** - Your first Claude API call
- **`text-generation.php`** - Generate various types of content
- **`code-analysis.php`** - Analyze and explain code
- **`data-extraction.php`** - Extract structured data from text
- **`cost-tracking.php`** - Monitor API usage and costs

### Source Files

- **`src/ClaudeClient.php`** - Reusable Claude API client
- **`src/CostCalculator.php`** - Calculate API costs from token usage

## 💡 Key Concepts

### Making a Request

```php
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: getenv('ANTHROPIC_API_KEY')
);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, Claude!']
    ]
]);

echo $response->content[0]->text;
```

### Token Usage

Every API call returns token usage information:

```php
echo "Input tokens: " . $response->usage->inputTokens . "\n";
echo "Output tokens: " . $response->usage->outputTokens . "\n";
```

### Error Handling

Always wrap API calls in try-catch blocks:

```php
try {
    $response = $client->messages()->create([...]);
} catch (\ClaudePhp\Exceptions\ErrorException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
}
```

## 🎓 Next Steps

After completing this chapter:

1. **Chapter 01** - Deep dive into Claude API and models
2. **Chapter 02** - Authentication and security best practices
3. **Chapter 03** - Making production-ready requests

## 📊 Cost Management

Claude API pricing (as of 2025):

- **Claude Sonnet 4**: $3 per million input tokens, $15 per million output tokens
- **Claude Opus 4**: $15 per million input tokens, $75 per million output tokens
- **Claude Haiku 4**: $0.25 per million input tokens, $1.25 per million output tokens

Use the `cost-tracking.php` example to monitor your usage!

## 🐛 Troubleshooting

### "API key not found"
- Ensure `.env` file exists and contains your API key
- Check that the key starts with `sk-ant-`

### "Connection timeout"
- Check your internet connection
- Verify API status at [status.anthropic.com](https://status.anthropic.com)

### "Rate limit exceeded"
- You've hit your API rate limit
- Wait a moment and try again
- Consider implementing exponential backoff (covered in Chapter 10)

## 📚 Resources

- **[Official Anthropic Documentation](https://docs.anthropic.com/)** — Complete API reference
- **[Official PHP SDK on GitHub](https://github.com/anthropics/anthropic-sdk-php)** — Anthropic's official PHP implementation
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples
- **[PHP SDK on Packagist](https://packagist.org/packages/claude-php/claude-php-sdk)** — Composer package
- **[Community Discord](https://discord.gg/anthropic)** — Get help and discuss

## 💬 Support

Questions? Issues? Open a [GitHub issue](https://github.com/dalehurley/codewithphp/issues) or join the discussion!
