# Chapter 11: Tool Use Fundamentals

Learn how to use Claude's powerful tool use (function calling) capabilities to enable Claude to interact with external systems, databases, and APIs.

## Features

- **Basic Tools**: Simple tool definitions and usage
- **Multi-Tool**: Multiple tools in one conversation
- **Tool Orchestration**: Complex workflows with multiple tool calls
- **Database Tool**: Database queries via Claude

## Installation

```bash
composer install
cp .env.example .env
# Edit .env and add your API key
```

## Examples

### 1. Basic Tools
```bash
php examples/basic-tools.php
```

### 2. Multi-Tool Usage
```bash
php examples/multi-tool.php
```

### 3. Tool Orchestration
```bash
php examples/tool-orchestration.php
```

### 4. Database Tool
```bash
php examples/database-tool.php
```

## Tool Definition

Tools are defined with:
- **name**: Unique identifier
- **description**: What the tool does
- **input_schema**: JSON Schema for parameters

## Learn More

- [Claude Tool Use Documentation](https://docs.anthropic.com/claude/docs/tool-use)
- [Tool Use Best Practices](https://docs.anthropic.com/claude/docs/tool-use-best-practices)
## 📚 Resources

- **[Official Anthropic Documentation](https://docs.anthropic.com/)** — Complete API reference
- **[Official PHP SDK on GitHub](https://github.com/anthropics/anthropic-sdk-php)** — Anthropic's official PHP implementation
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples
- **[PHP SDK on Packagist](https://packagist.org/packages/claude-php/claude-php-sdk)** — Composer package
- **[Community Discord](https://discord.gg/anthropic)** — Get help and discuss
