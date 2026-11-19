---
title: "53: Handle New ValueErrors When Upgrading"
description: "Probe three input checks that changed between PHP 8.5 and 8.6"
series: "php-8-6"
chapter: 53
order: 53
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 53
---

## Overview

Probe three input checks that changed between PHP 8.5 and 8.6. PHP now raises `ValueError` in more APIs when the argument has the right type but an invalid value.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- A 64-bit PHP 8.6 CLI for the out-of-range delay case.
- Review the [previous chapter](/series/php-8-6/chapters/52-stream-close-failures/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`stricter-value-errors.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/53-stricter-value-errors/stricter-value-errors.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/53-stricter-value-errors/stricter-value-errors.php
```

```php
<?php

// filename: stricter-value-errors.php

declare(strict_types=1);

if (PHP_VERSION_ID < 80600) {
    throw new RuntimeException('Run this example with PHP 8.6');
}

$examples = [
    'Invalid filter mode' => static fn () => array_filter([1], static fn (int $value): bool => $value > 0, -1),
    'NUL in file name' => static fn () => file_exists("report.csv\0ignored"),
    'Out-of-range delays' => static fn () => usleep(5_000_000_000),
];

foreach ($examples as $label => $run) {
    try {
        $run();
        echo "$label accepted\n";
    } catch (ValueError) {
        echo "$label rejected\n";
    }
}
```

### Expected Result

```text
Invalid filter mode rejected
NUL in file name rejected
Out-of-range delays rejected
```

### Why It Works

Validate external data before building flags, choosing numeric ranges, or passing strings to APIs that reject NUL bytes. At upgrade time, test failure paths as well as successful calls. The exact affected functions are listed by extension in the upgrade notes.

## Exercise

Add a closure calling `pathinfo('report.csv', -1)` and then replace `-1` with `PATHINFO_EXTENSION`. Verify that the invalid value is rejected and the valid one returns `csv`.

## Wrap-up

Return to the [series overview](/series/php-8-6/) to review the upgrade topics.

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
