---
title: "42: Drop the Third Argument to define()"
description: "Register a constant with its canonical spelling"
series: "php-8-6"
chapter: 42
order: 42
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 42
---

## Overview

Register a constant with its canonical spelling. PHP 8.6 deprecates passing the old case-insensitivity argument, even when it is `false`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/41-locale-collation/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`define-third-argument.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/42-define-third-argument/define-third-argument.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/42-define-third-argument/define-third-argument.php
```

```php
<?php

// filename: define-third-argument.php

declare(strict_types=1);

define('DEFAULT_CURRENCY', 'AUD');
echo constant('DEFAULT_CURRENCY'), PHP_EOL;
echo 'Lowercase alias exists: ', defined('default_currency') ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
AUD
Lowercase alias exists: no
```

### Why It Works

Use two arguments and read the constant with its declared casing. If your old code passed `true`, audit consumers for case mismatches as part of the migration.

## Exercise

Check `defined('default_currency')` after the declaration. Then check `defined('DEFAULT_CURRENCY')` and compare the results.

## Wrap-up

Continue with [Handle Class Names Explicitly in Type Checks](/series/php-8-6/chapters/43-class-string-guards/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_define_with_case_insensitive_being_specified)
