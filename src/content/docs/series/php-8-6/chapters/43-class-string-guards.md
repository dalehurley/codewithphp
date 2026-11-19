---
title: "43: Handle Class Names Explicitly in Type Checks"
description: "Check an instance and a class name with `is_a()` and `is_subclass_of()`"
series: "php-8-6"
chapter: 43
order: 43
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 43
---

## Overview

Check an instance and a class name with `is_a()` and `is_subclass_of()`. PHP 8.6 deprecates passing a class string when the allow-string flag is false.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/42-define-third-argument/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`class-string-guards.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/43-class-string-guards/class-string-guards.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/43-class-string-guards/class-string-guards.php
```

```php
<?php

// filename: class-string-guards.php

declare(strict_types=1);

class Handler
{
}

final class CsvHandler extends Handler
{
}

$handler = new CsvHandler();
$name = CsvHandler::class;
echo 'Instance: ', $handler instanceof Handler ? 'yes' : 'no', PHP_EOL;
echo 'Class is a handler: ', is_a($name, Handler::class, true) ? 'yes' : 'no', PHP_EOL;
echo 'Class extends handler: ', is_subclass_of($name, Handler::class, true) ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
Instance: yes
Class is a handler: yes
Class extends handler: yes
```

### Why It Works

Use `instanceof` or pass an object when checking a live instance. Pass `true` explicitly for trusted class names. The class-string path can invoke autoloading, so restrict class names before passing through values from users.

## Exercise

Replace the class name with the base class and compare the results. `is_a()` accepts the base class itself while `is_subclass_of()` requires a stricter relationship.

## Wrap-up

Continue with [Set a Limit for php://filter Chains](/series/php-8-6/chapters/44-stream-filter-limit/).

## Further Reading

- [Class strings in is_a()](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_is_a_with_string_when_allow_string_is_false)
- [Class strings in is_subclass_of()](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_is_subclass_of_with_string_when_allow_string_is_false)
