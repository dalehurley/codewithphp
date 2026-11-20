# Chapter 16: The Claude PHP SDK

Stream text with the community SDK and test a Guzzle request wrapper with a mocked Claude response.

## Quick Start

Use PHP 8.6 and Composer. Run these commands from this directory.

```bash
composer install
php examples/custom-transport.php
php examples/testing.php
```

The mock examples run without an API key. To run the live examples, copy `.env.example` to `.env` and set `ANTHROPIC_API_KEY`. Override `ANTHROPIC_MODEL` if needed.

```bash
php examples/stream.php
php examples/sdk-advanced.php
php examples/middleware.php
```

- `stream.php` consumes text deltas from the community SDK.
- `sdk-advanced.php` logs the model and response ID with Guzzle middleware.
- `middleware.php` adds up input and output token counts across two requests.
- `custom-transport.php` injects a Guzzle client backed by a mock response.
- `testing.php` checks the actual wrapper’s request, middleware calls, and decoded response.

[Read the chapter](https://codewithphp.com/series/claude-php-developers/chapters/16-claude-php-sdk/) · [Community SDK](https://github.com/claude-php/Claude-PHP-SDK) · [Anthropic’s official PHP SDK](https://platform.claude.com/docs/en/cli-sdks-libraries/sdks/php)
