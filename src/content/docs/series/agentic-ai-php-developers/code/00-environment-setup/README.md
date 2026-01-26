# Chapter 00: Environment Setup and Preparation - Code Examples

This directory contains the runnable examples referenced in Chapter 00.

## Files

- `env.example` — Environment variable template for local development.
- `hello-agent.php` — Minimal agent script that verifies end-to-end connectivity.

## Running the Example

```bash
# 1. Copy env template and add your key
cp env.example .env

# 2. Load environment variables (use your shell or direnv)
export $(grep -v '^#' .env | xargs)

# 3. Run the example
php hello-agent.php
```

Expected output (example):

```
Hello! PHP is a powerful language for building web applications.
```
