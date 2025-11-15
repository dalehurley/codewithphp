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
