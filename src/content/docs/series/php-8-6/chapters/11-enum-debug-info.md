---
title: "11: Customize Enum Dumps with __debugInfo()"
description: "Show a human-readable status and an enum’s machine value when inspecting it with `var_dump()`"
series: "php-8-6"
chapter: 11
order: 11
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 11
---

## Overview

Show a human-readable status and an enum’s machine value when inspecting it with `var_dump()`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/10-override-constants/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`enum-debug-info.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/11-enum-debug-info/enum-debug-info.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/11-enum-debug-info/enum-debug-info.php
```

```php
<?php

// filename: enum-debug-info.php

declare(strict_types=1);

enum JobState: string
{
    case Queued = 'queued';
    case Complete = 'complete';

    public function __debugInfo(): array
    {
        return ['state' => $this->value, 'finished' => $this === self::Complete];
    }
}

var_dump(JobState::Queued);
echo json_encode(JobState::Queued, JSON_THROW_ON_ERROR), PHP_EOL;
```

### Expected Result

```text
enum(JobState::Queued) (2) {
  ["state"]=>
  string(6) "queued"
  ["finished"]=>
  bool(false)
}
"queued"
```

### Why It Works

PHP calls `__debugInfo()` to obtain the fields shown by `var_dump()`. The method returns an array. Backed values and enum identity are unaffected by the debug view.

## Exercise

Add a `Failed` case and include a `retryable` field that is true only for that case. Dump both cases and compare the field.

## Wrap-up

Continue with [Change Properties on Objects Held in Constants](/series/php-8-6/chapters/12-constant-object-mutation/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/debugable-enums)
