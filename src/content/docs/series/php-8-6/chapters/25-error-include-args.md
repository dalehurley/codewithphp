---
title: "25: Show Function Arguments in PHP Errors"
description: "Compare the same PHP warning with argument display turned off and on"
series: "php-8-6"
chapter: 25
order: 25
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 25
---

## Overview

Compare the same warning with argument display turned off and on. This setting can help you identify the inputs that reached a failing function.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/24-json-error-location/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`error-include-args.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/25-error-include-args/error-include-args.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/25-error-include-args/error-include-args.php
```

```php
<?php

// filename: error-include-args.php

declare(strict_types=1);

set_error_handler(static function (int $level, string $message): bool {
    echo $message, PHP_EOL;
    return true;
}, E_WARNING);

try {
    foreach (['0', '1'] as $enabled) {
        ini_set('error_include_args', $enabled);
        echo "Setting $enabled: ";
        unlink('php://memory');
    }
} finally {
    restore_error_handler();
}
```

### Expected Result

```text
Setting 0: unlink(): PHP does not allow unlinking
Setting 1: unlink('php://memory'): PHP does not allow unlinking
```

### Why It Works

The `php://memory` wrapper rejects the unlink operation, so PHP emits a warning without touching a disk file. The custom error handler prints the complete warning message. With the INI setting enabled, PHP inserts the supplied argument into the function signature. It uses the engine’s argument formatting, including truncation and sensitive-parameter redaction.

## Exercise

Run the script again with `php -d zend.exception_string_param_max_len=6`. Check that the enabled warning shows `'php://...'`.

## Wrap-up

Continue with [Reverse Text by Grapheme Cluster](/series/php-8-6/chapters/26-grapheme-strrev/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/display_error_function_args)
