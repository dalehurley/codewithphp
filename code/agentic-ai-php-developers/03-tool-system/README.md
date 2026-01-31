# Chapter 03: Tool System Deep Dive - Code Examples

This directory contains all code examples from Chapter 03.

## Prerequisites

- PHP 8.4+
- Composer
- `claude-php/agent` framework installed
- Anthropic API key in environment: `ANTHROPIC_API_KEY`

## Installation

From the project root:

```bash
cd code/agentic-ai-php-developers/03-tool-system
composer install
```

## Files

- `basic-tool.php` — Simple tool definition and execution
- `parameter-types.php` — String, number, boolean, array parameters
- `validation-patterns.php` — Input validation and schema errors
- `error-handling.php` — ToolResult patterns and exception handling
- `production-tool.php` — Complete production-ready tool
- `tool-registry.php` — Managing multiple tools
- `builtin-tools.php` — Using framework built-in tools

## Running Examples

```bash
# Basic tool example
php basic-tool.php

# Parameter types demonstration
php parameter-types.php

# Validation patterns
php validation-patterns.php

# Error handling
php error-handling.php

# Production tool (requires SQLite)
php production-tool.php

# Tool registry
php tool-registry.php

# Built-in tools
php builtin-tools.php
```

## Notes

- All examples are self-contained and can run independently
- Some examples use simulated responses for demonstration
- Production tool example creates an in-memory SQLite database
- Built-in tools example demonstrates Calculator, HTTP (simulated), and FileSystem tools

## Learning Path

1. Start with `basic-tool.php` to understand tool structure
2. Explore `parameter-types.php` to see all parameter types
3. Review `validation-patterns.php` for validation strategies
4. Study `error-handling.php` for proper error patterns
5. Analyze `production-tool.php` for complete production example
6. Practice with `tool-registry.php` for tool management
7. Experiment with `builtin-tools.php` for framework tools

## Troubleshooting

**"Tool not found" errors:**
- Verify tool is registered before use
- Check tool name matches exactly (case-sensitive)

**Validation errors:**
- Review parameter schema in tool definition
- Ensure input matches expected types
- Check required vs optional parameters

**Handler errors:**
- Add try-catch blocks in handler
- Use ToolResult::error() for expected failures
- Use ToolResult::fromException() for unexpected errors

## Additional Resources

- [claude-php/agent Documentation](https://github.com/claude-php/claude-php-agent)
- [JSON Schema Guide](https://json-schema.org/understanding-json-schema/)
- Chapter 03 Tutorial: [Tool System Deep Dive](/series/agentic-ai-php-developers/chapters/03-tool-system-deep-dive)
