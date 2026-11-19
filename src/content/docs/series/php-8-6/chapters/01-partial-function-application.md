---
title: "01: PHP 8.6 Partial Function Application"
description: "Use PHP 8.6 partial function application to bind arguments with ? or ... and reuse the resulting Closure as a callback."
series: "php-8-6"
chapter: 1
order: 1
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 1
author: "Code with PHP"
dateModified: "2026-11-20"
---

## Overview

In PHP 8.6, call a function with `?` for each argument you want to supply later, or use a final `...` to leave the remaining arguments open. For example, `round(?, 2)` creates a closure that rounds any supplied number to two decimal places. This lesson uses the same pattern to configure a reusable log formatter.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`partial-function-application.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/01-partial-function-application/partial-function-application.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/01-partial-function-application/partial-function-application.php
```

```php
<?php

// filename: partial-function-application.php

declare(strict_types=1);

function formatLog(string $channel, string $level, string $message): string
{
    return "$level [$channel] $message";
}

$workerLog = formatLog('worker', ...);
echo $workerLog('INFO', 'Job started'), PHP_EOL;

$billingError = formatLog('billing', 'ERROR', ?);
echo $billingError('Payment declined'), PHP_EOL;

$amounts = [1.235, 7.5];
print_r(array_map(round(?, 2), $amounts));
```

### Expected Result

```text
INFO [worker] Job started
ERROR [billing] Payment declined
Array
(
    [0] => 1.24
    [1] => 7.5
)
```

### Why It Works

Each `?` leaves one parameter open. A final `...` leaves the remaining parameters open. PHP evaluates the supplied arguments when it creates the closure and calls the target only when you invoke that closure.

### Troubleshooting

- A parse error at `?` usually means your shell selected an older PHP binary. This syntax is parsed when the file loads, even inside a version check.

## Exercise

Create a formatter with both the channel and severity fixed to `worker` and `WARNING`. Pass it to `array_map()` over two job messages and check that both lines start with `WARNING [worker]`.

## Wrap-up

Continue with [Elapsed Time with Time\Duration](/series/php-8-6/chapters/02-duration/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/partial_function_application_v2)
