# Chapter 09: Token Management

Learn to count, track, and optimize token usage for cost-effective Claude API usage.

## 🎯 What You'll Learn

- Token counting techniques
- Budget management strategies
- Context pruning and optimization
- Token usage monitoring

## 📁 Examples

- `token-counter.php` - Count tokens in text
- `budget-manager.php` - Track and enforce token budgets
- `context-pruning.php` - Optimize conversation context

## 🚀 Quick Start

```bash
composer install
cp .env.example .env
php examples/token-counter.php
```

## 💡 Token Tips

- Average English word ≈ 1.3 tokens
- Code typically uses more tokens
- Longer conversations = more context tokens
- Monitor usage to control costs
## 📚 Resources

- **[Official Anthropic Documentation](https://docs.anthropic.com/)** — Complete API reference
- **[Official PHP SDK on GitHub](https://github.com/anthropics/anthropic-sdk-php)** — Anthropic's official PHP implementation
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples
- **[PHP SDK on Packagist](https://packagist.org/packages/claude-php/claude-php-sdk)** — Composer package
- **[Community Discord](https://discord.gg/anthropic)** — Get help and discuss
