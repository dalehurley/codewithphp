# Chapter 03: Your First Claude Request

Make a request with the community [Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK) or Guzzle, read token usage, and validate a JSON response.

## Quick Start

Use PHP 8.6 and Composer. Run these commands from this directory.

```bash
composer install
cp .env.example .env
```

Set `ANTHROPIC_API_KEY` in `.env` or export it from your shell. `ANTHROPIC_MODEL` defaults to `claude-sonnet-5-5`.

```bash
php examples/sdk-request.php
php examples/guzzle-request.php
php examples/response-parsing.php
```

The SDK example prints text and token usage. The Guzzle example sends the JSON directly to the Messages API. The parsing example validates two string fields in a JSON object returned by the model.

- [Read the chapter](https://codewithphp.com/series/claude-php-developers/chapters/03-first-claude-request/)
- [Messages API reference](https://platform.claude.com/docs/en/api/messages/create)
- [Anthropic’s official PHP SDK](https://platform.claude.com/docs/en/cli-sdks-libraries/sdks/php)
