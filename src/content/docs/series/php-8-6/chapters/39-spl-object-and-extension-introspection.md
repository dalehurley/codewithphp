---
title: "39: Replace spl_object_hash() and spl_classes()"
description: "Inspect object identity and list SPL classes using supported APIs"
series: "php-8-6"
chapter: 39
order: 39
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 39
---

## Overview

Inspect object identity and list SPL classes using supported APIs. PHP 8.6 deprecates both older helper functions.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/38-numeric-aliases/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`spl-object-and-extension-introspection.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/39-spl-object-and-extension-introspection/spl-object-and-extension-introspection.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/39-spl-object-and-extension-introspection/spl-object-and-extension-introspection.php
```

```php
<?php

// filename: spl-object-and-extension-introspection.php

declare(strict_types=1);

$first = new stdClass();
$alias = $first;
$second = new stdClass();

echo 'Alias shares ID: ', spl_object_id($first) === spl_object_id($alias) ? 'yes' : 'no', PHP_EOL;
echo 'Second shares ID: ', spl_object_id($first) === spl_object_id($second) ? 'yes' : 'no', PHP_EOL;
$classes = new ReflectionExtension('SPL')->getClassNames();
echo 'ArrayObject is in SPL: ', in_array(ArrayObject::class, $classes, true) ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
Alias shares ID: yes
Second shares ID: no
ArrayObject is in SPL: yes
```

### Why It Works

`spl_object_id()` returns an integer unique among live objects in the process. IDs may be reused once objects are destroyed. If you need an object-keyed map, `WeakMap` can tie entry lifetime to the key. For extension discovery, reflection returns a list of class names, while `spl_classes()` returned names as keys as well.

## Exercise

Allocate another `stdClass` with the same properties and verify that its ID differs while both objects are alive.

## Wrap-up

Continue with [Pass Arrays to Array and Compression APIs](/series/php-8-6/chapters/40-objects-as-arrays/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6)
