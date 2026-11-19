---
title: "49: Migrate SplFileObject CSV Calls"
description: "Write a CSV row and read it back using the standalone CSV functions"
series: "php-8-6"
chapter: 49
order: 49
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 49
---

## Overview

Write a CSV row and read it back using the standalone CSV functions. PHP 8.6 deprecates the four CSV-specific methods on `SplFileObject`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/48-mysqli-charset-and-statements/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`splfileobject-csv.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/49-splfileobject-csv/splfileobject-csv.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/49-splfileobject-csv/splfileobject-csv.php
```

```php
<?php

// filename: splfileobject-csv.php

declare(strict_types=1);

$stream = fopen('php://temp', 'w+');
if ($stream === false) {
    throw new RuntimeException('Cannot open CSV stream');
}

try {
    $fields = ['42', 'Dinner, Hobart', 'Said "hello"'];
    if (fputcsv($stream, $fields, escape: '') === false || !rewind($stream)) {
        throw new RuntimeException('Cannot write and rewind CSV');
    }
    $row = fgetcsv($stream, escape: '');
    echo 'Round trip: ', $fields === $row ? 'yes' : 'no', PHP_EOL;
    echo json_encode($row, JSON_THROW_ON_ERROR), PHP_EOL;
} finally {
    fclose($stream);
}
```

### Expected Result

```text
Round trip: yes
["42","Dinner, Hobart","Said \"hello\""]
```

### Why It Works

Use `fputcsv()` and `fgetcsv()` with a stream resource and pass the CSV controls explicitly. Set an empty escape character for standard doubled-quote escaping. Update readers that previously relied on `setCsvControl()` or returned those settings with `getCsvControl()`.

## Exercise

Add a field containing a newline. Confirm that the reader returns one logical row with the newline inside the field.

## Wrap-up

Continue with [Replace Deprecated ArrayIterator Methods](/series/php-8-6/chapters/50-arrayiterator-deprecations/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_splfileobject_csv_methods)
