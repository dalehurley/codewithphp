# Chapter 00: Environment Setup and Preparation - Code Examples

This directory contains the runnable examples referenced in Chapter 00.

## Files

- `composer.json` — Project dependencies for `claude-php/agent` framework
- `env.example` — Environment variable template for local development
- `hello-agent.php` — Minimal agent script that verifies end-to-end connectivity
- `README.md` — This file

## Dependencies

This chapter uses:

- **[claude-php/agent](https://github.com/claude-php/claude-php-agent)** - Claude PHP Agents Framework  
  Install via: `composer require claude-php/agent`
- **[claude-php/claude-php-sdk](https://github.com/claude-php/Claude-PHP-SDK)** - Claude PHP SDK (required by the agent framework)  
  Install via: `composer require claude-php/claude-php-sdk`

## Running the Example

```bash
# 1. Install dependencies
composer install

# 2. Copy env template and add your key
cp env.example .env

# 3. Load environment variables (use your shell or direnv)
export $(grep -v '^#' .env | xargs)

# 4. Run the example
php hello-agent.php
```

Expected output (example):

```
Hello! PHP is a powerful language for building web applications.
```

## Getting an API Key

1. Sign up at [console.anthropic.com](https://console.anthropic.com)
2. Add a payment method (required for API access)
3. Generate an API key from Settings → API Keys
4. Copy the key (starts with `sk-ant-`)

## Troubleshooting

**Error: "Failed opening required vendor/autoload.php"**
- Run `composer install` first

**Error: "401 Unauthorized"**
- Check that `ANTHROPIC_API_KEY` is set correctly in your environment
- Verify the key starts with `sk-ant-`

**Error: Network timeout**
- Confirm outbound HTTPS is allowed on your network
- Check your firewall settings
