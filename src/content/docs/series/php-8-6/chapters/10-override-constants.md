---
title: "10: Override Attributes on Constants and Enum Cases"
description: "Use `#[Override]` to catch a renamed protocol constant or enum case"
series: "php-8-6"
chapter: 10
order: 10
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 10
---

## Overview

Use `#[Override]` to catch a renamed protocol constant or enum case. The engine checks that the name matches a constant on a parent class or interface.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/09-readonly-defaults/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`override-constants.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/10-override-constants/override-constants.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/10-override-constants/override-constants.php
```

```php
<?php

// filename: override-constants.php

declare(strict_types=1);

class VersionOne
{
    public const int VERSION = 1;
}

final class VersionTwo extends VersionOne
{
    #[Override]
    public const int VERSION = 2;
}

interface ServiceState
{
    public const Ready = 'ready';
}

enum WorkerState: string implements ServiceState
{
    #[Override]
    case Ready = 'ready';
}

echo 'Version ', VersionTwo::VERSION, PHP_EOL;
echo WorkerState::Ready->value, PHP_EOL;
```

### Expected Result

```text
Version 2
ready
```

### Why It Works

A backed enum case can implement an interface constant of the same name, subject to the usual constant override rules. A missing target is a compile-time error. Apply the attribute at the point where you intend to keep the inherited contract.

## Exercise

Change `VersionTwo::VERSION` to `REVISION` in a copy while keeping the attribute. Run `php -l` and confirm PHP rejects the declaration.

## Wrap-up

Continue with [Customize Enum Dumps with __debugInfo()](/series/php-8-6/chapters/11-enum-debug-info/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/override_constants)
