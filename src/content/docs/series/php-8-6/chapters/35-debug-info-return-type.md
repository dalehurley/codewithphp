---
title: "35: Return an Array from __debugInfo()"
description: "Supply debug fields with a concrete `array` return type"
series: "php-8-6"
chapter: 35
order: 35
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 35
---

## Overview

Supply debug fields with a concrete `array` return type. PHP 8.6 deprecates nullable return type declarations on `__debugInfo()`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/34-constructor-destructor-returns/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`debug-info-return-type.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/35-debug-info-return-type/debug-info-return-type.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/35-debug-info-return-type/debug-info-return-type.php
```

```php
<?php

// filename: debug-info-return-type.php

declare(strict_types=1);

final class Report
{
    public function __construct(private ?string $status)
    {
    }

    public function __debugInfo(): array
    {
        return $this->status === null ? [] : ['status' => $this->status];
    }
}

var_dump(new Report(null));
```

### Expected Result

```text
object(Report)#1 (0) {
}
```

### Why It Works

Return an empty array when the object has nothing to show. Use an associative array to select the fields you want exposed when data is available.

## Exercise

Set the status to `'ready'` and dump the object. Check that only the `status` key appears.

## Wrap-up

Continue with [Rename Deprecated Identifiers](/series/php-8-6/chapters/36-deprecated-identifiers/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
