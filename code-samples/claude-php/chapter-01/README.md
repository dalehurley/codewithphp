# Chapter 01: Introduction to Claude API

Deep dive into the Claude API, understanding different models, pricing, and API fundamentals.

## 🎯 What You'll Learn

- Understanding Claude model family (Haiku, Sonnet, Opus)
- Model capabilities and use cases
- Pricing structure and cost optimization
- API fundamentals and best practices
- Choosing the right model for your needs

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer installed
- Anthropic API key
- Completed Chapter 00 (Quick Start)

## 🚀 Quick Start

```bash
composer install
cp .env.example .env
# Edit .env with your API key
php examples/model-comparison.php
```

## 📁 Examples

### Model Comparison
```bash
php examples/model-comparison.php
```
Compare Haiku, Sonnet, and Opus models across different tasks to understand their strengths.

### Pricing Calculator
```bash
php examples/pricing-calculator.php
```
Calculate and compare costs for different models and usage patterns.

### API Basics
```bash
php examples/api-basics.php
```
Learn the fundamental API concepts including request/response structure, headers, and error handling.

## 🤖 Claude Model Family

### Claude Haiku 4
- **Best for**: Fast responses, high-volume tasks
- **Speed**: Fastest
- **Cost**: Most economical
- **Use cases**: Content moderation, simple classification, data extraction

### Claude Sonnet 4
- **Best for**: Balanced performance and cost
- **Speed**: Fast
- **Cost**: Moderate
- **Use cases**: Most general-purpose applications, code generation, analysis

### Claude Opus 4
- **Best for**: Complex reasoning and analysis
- **Speed**: Slower but most capable
- **Cost**: Premium
- **Use cases**: Advanced research, complex problem-solving, detailed analysis

## 💰 Pricing (2025)

| Model | Input (per 1M tokens) | Output (per 1M tokens) |
|-------|----------------------|------------------------|
| Haiku 4 | $0.25 | $1.25 |
| Sonnet 4 | $3.00 | $15.00 |
| Opus 4 | $15.00 | $75.00 |

## 🔑 Key Concepts

### API Request Structure

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',  // Required
    'max_tokens' => 1024,                     // Required
    'messages' => [                           // Required
        ['role' => 'user', 'content' => 'Hello']
    ],
    'system' => 'You are a helpful assistant', // Optional
    'temperature' => 1.0                       // Optional
]);
```

### Response Structure

```php
$response->id;              // Message ID
$response->content[0]->text; // Response text
$response->model;           // Model used
$response->usage;           // Token usage
$response->stopReason;      // Why generation stopped
```

## 💡 Choosing the Right Model

### Use Haiku when:
- Speed is critical
- Tasks are simple and well-defined
- Processing high volumes
- Cost is a major concern

### Use Sonnet when:
- Balanced performance needed
- General-purpose applications
- Code generation and analysis
- Most production applications

### Use Opus when:
- Complex reasoning required
- Highest quality output needed
- Research and analysis tasks
- Accuracy is paramount

## 🎓 Next Steps

After completing this chapter:

1. **Chapter 02** - Authentication and API key management
2. **Chapter 03** - Making your first production-ready request
3. **Chapter 04** - Multi-turn conversations

## 📚 Resources

- [Claude Model Documentation](https://docs.anthropic.com/claude/docs/models-overview)
- [API Reference](https://docs.anthropic.com/claude/reference/messages_post)
- [Pricing Page](https://www.anthropic.com/pricing)
