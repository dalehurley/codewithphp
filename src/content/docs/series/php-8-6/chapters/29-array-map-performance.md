---
title: "29: Compiler Optimizations for array_map()"
description: "Produce the same formatted prices with a first-class callable and a partially applied callback"
series: "php-8-6"
chapter: 29
order: 29
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 29
---

## Overview

Produce the same formatted prices with a first-class callable and a partially applied callback. PHP 8.6 can compile eligible `array_map()` calls into loops.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/28-zip-in-memory/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`array-map-performance.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/29-array-map-performance/array-map-performance.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/29-array-map-performance/array-map-performance.php
```

```php
<?php

// filename: array-map-performance.php

declare(strict_types=1);

function formatPrice(float $price, float $factor = 1.2): string
{
    return sprintf('%.2f', $price * $factor);
}

$prices = ['tea' => 5.0, 'coffee' => 7.5];
$withCallable = array_map(formatPrice(...), $prices);
$withPartial = array_map(formatPrice(?, 1.2), $prices);

echo json_encode($withCallable, JSON_THROW_ON_ERROR), PHP_EOL;
echo 'Same prices: ', $withCallable === $withPartial ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
{"tea":"6.00","coffee":"9.00"}
Same prices: yes
```

### Why It Works

A first-class callable has the form `formatPrice(...)`. Partial application also exposes the target and fixed arguments at the call site. The optimization preserves the behavior of `array_map()`, including the index keys for a single input array.

## Exercise

Change the multiplier from 1.2 to 1.5 in both paths and confirm the output arrays remain equal. To measure a real benefit, benchmark your own workload with the same PHP configuration and inputs.

## Wrap-up

Continue with [Faster printf() with Simple Formats](/series/php-8-6/chapters/30-printf-performance/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
