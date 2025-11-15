# Shared Utilities for Claude PHP Series

Common utilities used across all chapters of the Claude for PHP Developers series.

## Installation

In any chapter directory, you can use these shared utilities:

```bash
composer require codewithphp/claude-php-shared
```

Or add to your chapter's composer.json:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../shared"
        }
    ],
    "require": {
        "codewithphp/claude-php-shared": "@dev"
    }
}
```

## Available Utilities

### BaseClaudeClient

Base client with common functionality:

```php
use CodeWithPHP\ClaudeShared\BaseClaudeClient;

$client = new BaseClaudeClient();
$response = $client->generate('Your prompt here');
```

### CostTracker

Track API costs across requests:

```php
use CodeWithPHP\ClaudeShared\CostTracker;

$tracker = new CostTracker();
$tracker->trackRequest($response, 'claude-sonnet-4-20250514');
$totalCost = $tracker->getTotalCost();
```

### RetryHelper

Retry failed requests with exponential backoff:

```php
use CodeWithPHP\ClaudeShared\RetryHelper;

$result = RetryHelper::retry(
    fn() => $client->messages()->create([...]),
    maxAttempts: 3
);
```

### ResponseParser

Parse and extract data from responses:

```php
use CodeWithPHP\ClaudeShared\ResponseParser;

$json = ResponseParser::extractJSON($response->content[0]->text);
$markdown = ResponseParser::extractMarkdown($response->content[0]->text);
```

### TokenCounter

Estimate token counts for prompts:

```php
use CodeWithPHP\ClaudeShared\TokenCounter;

$tokens = TokenCounter::estimate($prompt);
$withinBudget = TokenCounter::fitsInContext($tokens, 200000);
```

### Logger

Structured logging for Claude requests:

```php
use CodeWithPHP\ClaudeShared\Logger;

Logger::logRequest($prompt, $options);
Logger::logResponse($response, $duration);
Logger::logError($exception);
```

## Usage in Chapters

Each chapter can import these utilities to avoid code duplication and maintain consistency.

Example:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CodeWithPHP\ClaudeShared\BaseClaudeClient;
use CodeWithPHP\ClaudeShared\CostTracker;

$client = new BaseClaudeClient();
$tracker = new CostTracker();

$response = $client->generate('Hello, Claude!');
$tracker->trackRequest($response);

echo "Response: {$response->content[0]->text}\n";
echo "Cost: $" . number_format($tracker->getLastCost(), 6) . "\n";
```

## Testing

```bash
cd shared
composer install
vendor/bin/phpunit
```

## License

MIT License - Same as main series
