---
title: "04: Structured Stream Errors"
description: "Read a local configuration file and classify a failed open using the stream error API"
series: "php-8-6"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 4
---

## Overview

Read a local configuration file and classify a failed open using the stream error API. A structured error gives you a stable code and context for handling the failure.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/03-polling-api/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`stream-errors.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/04-stream-errors/stream-errors.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/04-stream-errors/stream-errors.php
```

```php
<?php

// filename: stream-errors.php

declare(strict_types=1);

$missingPath = sys_get_temp_dir() . '/settings-' . bin2hex(random_bytes(8)) . '.json';
$throwing = stream_context_create(['stream' => ['error_mode' => StreamErrorMode::Exception]]);

try {
    file_get_contents($missingPath, context: $throwing);
} catch (StreamException $exception) {
    foreach ($exception->getErrors() as $error) {
        printf("Caught %s from %s\n", $error->code->name, $error->wrapperName);
    }
}

stream_clear_errors();
$silent = stream_context_create([
    'stream' => ['error_mode' => StreamErrorMode::Silent, 'error_store' => StreamErrorStore::All],
]);
if (file_get_contents($missingPath, context: $silent) === false) {
    printf("Stored errors: %d\n", count(stream_last_errors()));
}
```

### Expected Result

```text
Caught OpenFailed from plainfile
Stored errors: 1
```

### Why It Works

Exception mode exposes the collected errors through `StreamException::getErrors()`. Silent mode still returns the operation’s failure value. Set `error_store` to `StreamErrorStore::All` you can inspect its errors with `stream_last_errors()`; `error_handler` is another context option for reporting errors to a callback.

## Exercise

Create an empty file with `tempnam()` and read it with the same exception context. Confirm that you get an empty string and no exception. Remove the file in a `finally` block.

## Wrap-up

Continue with [Limit Values with clamp()](/series/php-8-6/chapters/05-clamp/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/stream_errors)
