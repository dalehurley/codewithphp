---
title: "12: Change Properties on Objects Held in Constants"
description: "Update a mutable object held by a class constant"
series: "php-8-6"
chapter: 12
order: 12
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 12
---

## Overview

Update a mutable object held by a class constant. In PHP 8.6 you can write through the constant expression directly.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/11-enum-debug-info/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`constant-object-mutation.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/12-constant-object-mutation/constant-object-mutation.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/12-constant-object-mutation/constant-object-mutation.php
```

```php
<?php

// filename: constant-object-mutation.php

declare(strict_types=1);

final class WorkerSettings
{
    public function __construct(public int $timeoutSeconds)
    {
    }
}

const DEFAULT_WORKER_SETTINGS = new WorkerSettings(5);

final class WorkerConfiguration
{
    public const DEFAULTS = DEFAULT_WORKER_SETTINGS;
}

$sameSettings = WorkerConfiguration::DEFAULTS;
echo 'Before: ', $sameSettings->timeoutSeconds, PHP_EOL;
WorkerConfiguration::DEFAULTS->timeoutSeconds = 12;
echo 'After: ', $sameSettings->timeoutSeconds, PHP_EOL;
```

### Expected Result

```text
Before: 5
After: 12
```

### Why It Works

The global constant constructs the shared object and the class constant references it. Every reference to that object can observe the new property value, so shared defaults in long-lived workers can affect later jobs. The regular property visibility and readonly rules still apply.

## Exercise

Clone the settings into a local variable, change the clone’s timeout, and print both timeouts. The shared instance should retain its previous value.

## Wrap-up

Continue with [Build URLs and URI References from Components](/series/php-8-6/chapters/13-uri-builders/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/const_object_property_write)
