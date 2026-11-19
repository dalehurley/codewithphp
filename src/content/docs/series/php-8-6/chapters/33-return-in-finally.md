---
title: "33: Move Returns Out of finally Blocks"
description: "Keep resource cleanup in `finally` and return the result on the success path"
series: "php-8-6"
chapter: 33
order: 33
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 33
---

## Overview

Keep resource cleanup in `finally` and return the result on the success path. Returning from `finally` is deprecated in PHP 8.6 because it can replace an earlier return or suppress an exception.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/32-json-encoding-performance/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`return-in-finally.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/33-return-in-finally/return-in-finally.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/33-return-in-finally/return-in-finally.php
```

```php
<?php

// filename: return-in-finally.php

declare(strict_types=1);

function writeNote(string $note, bool $simulateFailure = false): int
{
    $stream = fopen('php://temp', 'w+');
    if ($stream === false) {
        throw new RuntimeException('Cannot open temp stream');
    }

    try {
        if ($simulateFailure) {
            throw new RuntimeException('Write rejected');
        }
        $written = fwrite($stream, $note);
        if ($written === false || $written !== strlen($note)) {
            throw new RuntimeException('Incomplete write');
        }
        return $written;
    } finally {
        fclose($stream);
    }
}

echo writeNote('hello'), " bytes\n";
try {
    writeNote('hello', true);
} catch (RuntimeException $error) {
    echo $error->getMessage(), PHP_EOL;
}
```

### Expected Result

```text
5 bytes
Write rejected
```

### Why It Works

PHP executes `finally` as control leaves `try`, including when an exception is propagating. With cleanup alone in that block, callers still receive the successful result or the original exception.

## Exercise

Pass an empty string as valid input. Confirm that it prints a zero byte count on that path and still propagates exceptions when explicitly asked to fail.

## Wrap-up

Continue with [Move Results and Generators Out of Object Lifecycles](/series/php-8-6/chapters/34-constructor-destructor-returns/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_returning_from_a_finally_block)
