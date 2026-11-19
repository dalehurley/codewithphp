---
title: "05: PHP 8.6 clamp() Examples"
description: "Learn how PHP 8.6 clamp() bounds values, handles reversed limits, and works with a Time Duration example."
series: "php-8-6"
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 5
author: "Code with PHP"
dateModified: "2026-11-20"
---

## Overview

PHP 8.6 adds `clamp($value, $min, $max)`. It returns the lower bound for a value below the range, the upper bound for a value above it, and the original value inside the range. For example, `clamp(150, 1, 100)` returns `100`. Pass the lowest bound first to avoid a `ValueError`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/04-stream-errors/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`clamp.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/05-clamp/clamp.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/05-clamp/clamp.php
```

```php
<?php

// filename: clamp.php

declare(strict_types=1);

use Time\Duration;

foreach ([-5, 25, 150] as $requested) {
    printf("%d -> %d\n", $requested, clamp($requested, 1, 100));
}

$timeout = clamp(
    Duration::fromMilliseconds(300),
    Duration::fromSeconds(1),
    Duration::fromSeconds(5),
);
echo 'Timeout seconds: ', $timeout->seconds, PHP_EOL;

try {
    clamp(3, 10, 1);
} catch (ValueError) {
    echo "Rejected reversed limits\n";
}
```

### Expected Result

```text
-5 -> 1
25 -> 25
150 -> 100
Timeout seconds: 1
Rejected reversed limits
```

### Why It Works

PHP uses its ordinary comparison rules and returns the original value when it is within bounds. Outside the range you get the appropriate boundary value, including its original type. Reversed limits throw a `ValueError`.

## Exercise

Clamp requests of 0, 25, and 1,000 to the range 1–100. Verify that you obtain 1, 25, and 100.

## Wrap-up

Continue with [Type-Safe Sorting with SortDirection](/series/php-8-6/chapters/06-sort-direction/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/clamp_v2)
