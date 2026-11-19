---
title: "02: Elapsed Time with Time\\Duration"
description: "Build a retry-delay calculator using seconds and milliseconds"
series: "php-8-6"
chapter: 2
order: 2
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 2
---

## Overview

Build a retry-delay calculator using seconds and milliseconds. A duration measures an amount of elapsed time and carries no calendar date or time zone.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/01-partial-function-application/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`duration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/02-duration/duration.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/02-duration/duration.php
```

```php
<?php

// filename: duration.php

declare(strict_types=1);

use Time\Duration;

$baseDelay = Duration::fromMilliseconds(750);
$attemptDelay = $baseDelay->multiplyBy(3);
$delay = min($attemptDelay, Duration::fromSeconds(2));

printf("Base: %d s %d ns\n", $baseDelay->seconds, $baseDelay->nanoseconds);
printf("Capped delay: %d s %d ns\n", $delay->seconds, $delay->nanoseconds);
```

### Expected Result

```text
Base: 0 s 750000000 ns
Capped delay: 2 s 0 ns
```

### Why It Works

Factories make the unit explicit. Arithmetic produces a new duration, so a shared base delay remains intact. The seconds and nanoseconds properties expose normalized components, and durations can be compared directly.

## Exercise

Calculate the delay for attempts zero through four with a cap of two seconds. Check that the final delay stays at the cap.

## Wrap-up

Continue with [Wait for I/O with Io\Poll](/series/php-8-6/chapters/03-polling-api/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/duration_class)
