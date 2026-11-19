---
title: "31: Caching Stateless Closures"
description: "Compare two closure factories"
series: "php-8-6"
chapter: 31
order: 31
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 31
---

## Overview

Compare two closure factories. One creates a static closure without captured or persistent state. The other captures the requested increment.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/30-printf-performance/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`stateless-closure-cache.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/31-stateless-closure-cache/stateless-closure-cache.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/31-stateless-closure-cache/stateless-closure-cache.php
```

```php
<?php

// filename: stateless-closure-cache.php

declare(strict_types=1);

function incrementer(): Closure
{
    return static fn (int $value): int => $value + 1;
}

function stepper(int $step): Closure
{
    return static fn (int $value): int => $value + $step;
}

$first = incrementer();
$second = incrementer();
echo 'Stateless reused: ', $first === $second ? 'yes' : 'no', PHP_EOL;
echo 'Captured reused: ', stepper(2) === stepper(2) ? 'yes' : 'no', PHP_EOL;
echo 'Stepped value: ', stepper(3)(4), PHP_EOL;
```

### Expected Result

```text
Stateless reused: yes
Captured reused: no
Stepped value: 7
```

### Why It Works

PHP 8.6 can cache a stateless closure at its declaration site. A closure that captures a variable needs its own state. Behavior should remain your public contract, especially when supporting different PHP releases.

## Exercise

Add a static local counter inside the first closure and check the identity result again. Each closure needs its own counter.

## Wrap-up

Continue with [Benchmark JSON Encoding on Your Data](/series/php-8-6/chapters/32-json-encoding-performance/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/closure-optimizations#stateless_closure_caching)
