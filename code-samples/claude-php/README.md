# Claude for PHP Developers - Code Samples

This directory contains all runnable code examples from the "Claude for PHP Developers" series.

## 📁 Directory Structure

Each chapter has its own directory with complete, runnable examples:

```
claude-php/
├── chapter-00/          # Quick Start Guide
├── chapter-01/          # Introduction to Claude API
├── chapter-02/          # Authentication and API Keys
├── chapter-03/          # Your First Claude Request
├── chapter-04/          # Messages and Conversations
├── chapter-05/          # Prompt Engineering Basics
├── chapter-06/          # Streaming Responses
├── chapter-07/          # System Prompts and Roles
├── chapter-08/          # Temperature and Sampling
├── chapter-09/          # Token Management
├── chapter-10/          # Error Handling and Rate Limiting
├── chapter-11/          # Tool Use Fundamentals
├── chapter-12/          # Building Custom Tools
├── chapter-13/          # Vision - Working with Images
├── chapter-14/          # Document Processing
├── chapter-15/          # Structured Outputs
├── chapter-16/          # Official PHP SDK
├── chapter-17/          # Claude Service Class
├── chapter-18/          # Caching Strategies
├── chapter-19/          # Queue Processing with Laravel
├── chapter-20/          # Real-time Chat with WebSockets
├── chapter-21/          # Laravel Integration Patterns
├── chapter-22/          # Building a Chatbot with Laravel
├── chapter-23/          # AI Form Validation
├── chapter-24/          # Content Generation API
├── chapter-25/          # Admin Panel with AI Features
├── chapter-26/          # Code Review Assistant
├── chapter-27/          # Documentation Generator
├── chapter-28/          # Customer Support Bot
├── chapter-29/          # Content Moderation System
├── chapter-30/          # Data Extraction and Analysis
├── chapter-31/          # Retrieval Augmented Generation
├── chapter-32/          # Vector Databases in PHP
├── chapter-33/          # Multi-Agent Systems
├── chapter-34/          # Prompt Chaining and Workflows
├── chapter-35/          # Fine-tuning Strategies
├── chapter-36/          # Security Best Practices
├── chapter-37/          # Monitoring and Observability
├── chapter-38/          # Scaling Applications
└── chapter-39/          # Cost Optimization
```

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Anthropic API key

### Installation

1. **Clone the repository:**
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/claude-php
```

2. **Choose a chapter:**
```bash
cd chapter-00  # Start with Quick Start Guide
```

3. **Install dependencies:**
```bash
composer install
```

4. **Configure your API key:**
```bash
# Copy example environment file
cp .env.example .env

# Edit .env and add your API key
# ANTHROPIC_API_KEY=sk-ant-your-key-here
```

5. **Run the examples:**
```bash
php quickstart.php
```

## 📖 Chapter-by-Chapter Guide

### Foundation Chapters (00-05)

**Chapter 00: Quick Start Guide**
```bash
cd chapter-00
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php quickstart.php
```

Examples included:
- Basic API calls
- Text generation
- Code analysis
- Data extraction
- Cost tracking

**Chapter 01-05**: Follow the same pattern for authentication, message formatting, conversations, and prompt engineering.

### Core Concepts (06-10)

**Chapter 06: Streaming Responses**
```bash
cd chapter-06
composer install
php streaming-example.php
```

Includes:
- Server-Sent Events (SSE)
- Real-time streaming
- Progress indicators
- Streaming chatbot

**Chapter 07-10**: System prompts, sampling parameters, token management, and error handling.

### Advanced Features (11-15)

**Chapter 11: Tool Use**
```bash
cd chapter-11
composer install
php tool-use-example.php
```

Demonstrates:
- Function calling
- Tool definitions
- Multi-tool orchestration
- Database tool integration

**Chapter 12-15**: Custom tools, vision API, document processing, structured outputs.

### PHP Integration (16-20)

**Chapter 16-20**: SDK deep dive, service classes, caching, queues, and WebSockets.

### Laravel Applications (21-25)

**Chapter 21: Laravel Integration**
```bash
cd chapter-21
composer install
php artisan migrate
php artisan serve
```

Complete Laravel applications with:
- Service providers
- Facades
- Controllers
- Livewire components

**Chapter 22-25**: Chatbot, form validation, content API, admin panels.

### Real-World Projects (26-30)

Each chapter contains a complete, production-ready application:
- Chapter 26: Code Review Assistant
- Chapter 27: Documentation Generator
- Chapter 28: Customer Support Bot
- Chapter 29: Content Moderation
- Chapter 30: Data Extraction Pipeline

### Advanced Techniques (31-35)

**Chapter 31: RAG Implementation**
```bash
cd chapter-31
composer install
# Set up vector database credentials
php rag-example.php
```

Includes:
- Document chunking
- Embedding generation
- Semantic search
- Context retrieval

**Chapter 32-35**: Vector databases, multi-agent systems, workflows, fine-tuning.

### Production Deployment (36-39)

**Chapter 36-39**: Security, monitoring, scaling, and cost optimization with production-ready code.

## 🛠️ Common Setup

### Environment Variables

All examples support these environment variables:

```bash
# Required
ANTHROPIC_API_KEY=sk-ant-your-key-here

# Optional
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=4096
ANTHROPIC_TEMPERATURE=1.0

# For Laravel chapters
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=claude_php
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Dependencies

Most chapters require:
```json
{
    "require": {
        "php": "^8.2",
        "anthropic-ai/sdk": "^1.0",
        "vlucas/phpdotenv": "^5.5"
    }
}
```

Laravel chapters additionally require:
```json
{
    "require": {
        "laravel/framework": "^11.0",
        "livewire/livewire": "^3.0"
    }
}
```

## 📝 Code Standards

All examples follow:
- **PHP 8.2+** features and syntax
- **PSR-12** coding standards
- **Strict types** (`declare(strict_types=1)`)
- **Type hints** on all parameters and returns
- **PHPDoc** comments for clarity
- **Error handling** best practices
- **Security** considerations

## 🧪 Testing

Many chapters include PHPUnit tests:

```bash
cd chapter-XX
composer install
vendor/bin/phpunit
```

## 📚 Documentation

Each chapter directory contains:
- `README.md` - Overview and setup instructions
- `composer.json` - Dependencies
- `.env.example` - Environment variable template
- `src/` - Source code
- `tests/` - Unit tests (where applicable)
- `examples/` - Runnable examples

## 💡 Tips

### Cost Management

Monitor your API usage when running examples:
```php
// All examples include usage tracking
echo "Tokens used: {$response->usage->inputTokens} + {$response->usage->outputTokens}\n";
```

### API Rate Limits

If you hit rate limits:
1. Add delays between requests
2. Implement exponential backoff (see Chapter 10)
3. Use caching (see Chapter 18)

### Debugging

Enable detailed logging:
```bash
export LOG_LEVEL=debug
php example.php
```

## 🤝 Contributing

Found an issue or have an improvement?

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add/update tests
5. Submit a pull request

## 📄 License

All code samples are released under the MIT License.

## 💬 Support

- **Documentation**: [Full Series](../../docs/series/claude-php-developers/)
- **Issues**: [GitHub Issues](https://github.com/dalehurley/codewithphp/issues)
- **Discussions**: [GitHub Discussions](https://github.com/dalehurley/codewithphp/discussions)
- **Community**: [Anthropic Discord](https://discord.gg/anthropic)

## 🎯 What's Next?

1. Start with **chapter-00** for a quick overview
2. Progress through **chapters 01-05** for foundations
3. Explore **chapters 06-15** for core features
4. Build with **chapters 16-30** for real applications
5. Master **chapters 31-39** for advanced techniques and production

Happy coding with Claude and PHP! 🚀
