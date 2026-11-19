---
title: "30: Faster printf() with Simple Formats"
description: "Print a short inventory report using the format specifiers the compiler can optimize"
series: "php-8-6"
chapter: 30
order: 30
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 30
---

## Overview

Print a short inventory report using the format specifiers the compiler can optimize. The source remains ordinary `printf()`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/29-array-map-performance/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`printf-performance.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/30-printf-performance/printf-performance.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/30-printf-performance/printf-performance.php
```

```php
<?php

// filename: printf-performance.php

declare(strict_types=1);

$warehouse = 'South';
$count = 7;
printf("%s has %d orders\n", $warehouse, $count);
printf("Batch %04d\n", $count);
```

### Expected Result

```text
South has 7 orders
Batch 0007
```

### Why It Works

PHP 8.6 can compile eligible calls with literal format strings using `%s` and `%d` into equivalent string operations. Formatting features such as zero padding still have their normal output. Keep the format that expresses the output you need.

## Exercise

Change the format for the second line to `%d` and check that the leading zeroes disappear.

## Wrap-up

Continue with [Caching Stateless Closures](/series/php-8-6/chapters/31-stateless-closure-cache/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
