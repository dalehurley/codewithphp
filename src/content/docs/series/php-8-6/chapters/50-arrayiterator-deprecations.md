---
title: "50: Replace Deprecated ArrayIterator Methods"
description: "Sort the backing collection before creating its iterator"
series: "php-8-6"
chapter: 50
order: 50
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 50
---

## Overview

Sort the backing collection before creating its iterator. PHP 8.6 deprecates `ArrayIterator`’s sorting, flag access, and legacy serialization methods.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/49-splfileobject-csv/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`arrayiterator-deprecations.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/50-arrayiterator-deprecations/arrayiterator-deprecations.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/50-arrayiterator-deprecations/arrayiterator-deprecations.php
```

```php
<?php

// filename: arrayiterator-deprecations.php

declare(strict_types=1);

$inventory = new ArrayObject(['large' => 12, 'small' => 3, 'medium' => 7]);
$inventory->asort();
foreach ($inventory->getIterator() as $size => $count) {
    printf("%s %d\n", $size, $count);
}
echo json_encode($inventory->getArrayCopy(), JSON_THROW_ON_ERROR), PHP_EOL;
```

### Expected Result

```text
small 3
medium 7
large 12
{"small":3,"medium":7,"large":12}
```

### Why It Works

Use `ArrayObject` for the collection’s sorting and flags or use array functions and create a new iterator when needed. The affected methods are `getFlags()`, `setFlags()`, `asort()`, `ksort()`, `uasort()`, `uksort()`, `natsort()`, `natcasesort()`, `serialize()`, and `unserialize()`. For application-owned data, store the underlying array or another explicit data format.

## Exercise

Sort by keys using `ksort()` on the collection. Check that the keys are preserved when you iterate.

## Wrap-up

Continue with [Account for Form Feeds in trim()](/series/php-8-6/chapters/51-trim-form-feed/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_arrayiterator_methods_that_inherit_arrayobject_implementation)
