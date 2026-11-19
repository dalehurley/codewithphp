---
title: "38: Replace Deprecated Numeric Aliases"
description: "Migrate type checks and conversions to `is_float()`, `is_int()`, and `floatval()`"
series: "php-8-6"
chapter: 38
order: 38
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 38
---

## Overview

Migrate type checks and conversions to `is_float()`, `is_int()`, and `floatval()`. The aliases `is_double()`, `is_long()`, `is_integer()`, and `doubleval()` are deprecated.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/37-mbregex-to-pcre/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`numeric-aliases.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/38-numeric-aliases/numeric-aliases.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/38-numeric-aliases/numeric-aliases.php
```

```php
<?php

// filename: numeric-aliases.php

declare(strict_types=1);

foreach ([42, 4.25, '4.25'] as $reading) {
    printf("%s int=%s float=%s cast=%.2f\n", get_debug_type($reading),
        is_int($reading) ? 'yes' : 'no',
        is_float($reading) ? 'yes' : 'no',
        floatval($reading));
}
```

### Expected Result

```text
int int=yes float=no cast=42.00
float int=no float=yes cast=4.25
string int=no float=no cast=4.25
```

### Why It Works

Each replacement preserves the corresponding alias’s behavior. A numeric string can be converted but still fails an integer or float type check. Keep validation separate when user input may be malformed.

## Exercise

Add the string `'42'` to the inputs, alongside the existing integer `42`. Confirm that only the integer value passes `is_int()` and both can convert to a float.

## Wrap-up

Continue with [Replace spl_object_hash() and spl_classes()](/series/php-8-6/chapters/39-spl-object-and-extension-introspection/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6)
