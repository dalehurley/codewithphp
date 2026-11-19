---
title: "52: Handle Stream Flush and Close Failures"
description: "Simulate a buffered write that fails during finalization"
series: "php-8-6"
chapter: 52
order: 52
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT15M"
sidebar:
  order: 52
---

## Overview

Simulate a buffered write that fails during finalization. PHP 8.6 propagates stream flush and close failures through `fclose()`, `file_put_contents()`, and `copy()`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- `allow_url_fopen=1` for the in-memory data URL used as the copy source.
- Review the [previous chapter](/series/php-8-6/chapters/51-trim-form-feed/) if you’re following the series in order.

Allow about 15 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`stream-close-failures.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/52-stream-close-failures/stream-close-failures.php) example.

## Step 1: Run the example (~10 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/52-stream-close-failures/stream-close-failures.php
```

```php
<?php

// filename: stream-close-failures.php

declare(strict_types=1);

final class BufferedSink
{
    public mixed $context;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return true;
    }

    public function stream_write(string $data): int
    {
        return strlen($data);
    }

    public function stream_flush(): bool
    {
        return false;
    }
}

if (!stream_wrapper_register('tutorialsink', BufferedSink::class)) {
    throw new RuntimeException('Cannot register test stream');
}
try {
    $stream = fopen('tutorialsink://output', 'w');
    if ($stream === false) {
        throw new RuntimeException('Cannot open test stream');
    }
    var_dump(fwrite($stream, 'sample'));
    var_dump(fclose($stream));
    var_dump(file_put_contents('tutorialsink://output', 'sample'));
    var_dump(copy('data://text/plain,sample', 'tutorialsink://output'));
} finally {
    stream_wrapper_unregister('tutorialsink');
}
```

### Expected Result

```text
int(6)
bool(false)
bool(false)
bool(false)
```

### Why It Works

A successful `fwrite()` confirms the stream accepted bytes. You must also check finalization before reporting success. Check `file_put_contents()` with `=== false` so a successful zero-byte result remains valid.

## Exercise

Make the wrapper’s `stream_flush()` return `true`. All three operations should then report success, and `file_put_contents()` should report the number of accepted bytes.

## Wrap-up

Continue with [Handle New ValueErrors When Upgrading](/series/php-8-6/chapters/53-stricter-value-errors/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
