---
title: "32: Benchmark JSON Encoding on Your Data"
description: "Encode a consistent array of records to exercise the 8.6 JSON encoder"
series: "php-8-6"
chapter: 32
order: 32
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 32
---

## Overview

Encode a consistent array of records to exercise the 8.6 JSON encoder. Supply `--time` to time repeated encoding with PHP’s monotonic clock.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/31-stateless-closure-cache/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`json-encoding-performance.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/32-json-encoding-performance/json-encoding-performance.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/32-json-encoding-performance/json-encoding-performance.php
```

```php
<?php

// filename: json-encoding-performance.php

declare(strict_types=1);

$rows = [];
for ($index = 1; $index <= 2_000; $index++) {
    $rows[] = ['id' => $index, 'state' => 'ready', 'items' => [1, 2, 3]];
}

$json = json_encode($rows, JSON_THROW_ON_ERROR);
echo 'Decoded records: ', count(json_decode($json, true, flags: JSON_THROW_ON_ERROR)), PHP_EOL;
echo 'Compact bytes: ', strlen($json), PHP_EOL;
echo 'Pretty bytes: ', strlen(json_encode($rows, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)), PHP_EOL;

if (in_array('--time', $argv, true)) {
    $start = hrtime(true);
    for ($run = 0; $run < 100; $run++) {
        json_encode($rows, JSON_THROW_ON_ERROR);
    }
    printf("100 encodes: %.1f ms\n", (hrtime(true) - $start) / 1_000_000);
}
```

### Expected Result

```text
Decoded records: 2000
Compact bytes: 86894
Pretty bytes: 262895
```

### Why It Works

PHP 8.6 improves encoding for arrays and objects, including indentation generation with `JSON_PRETTY_PRINT`. The output and error-handling flags stay useful as before. For a fair timing comparison, run the same file using the 8.5 and 8.6 binaries with matching settings and repeat the runs.

## Exercise

Run the sample with `--time` several times, then add a separate timing for `JSON_PRETTY_PRINT`. Compare medians alongside the encoded byte sizes.

## Wrap-up

Continue with [Move Returns Out of finally Blocks](/series/php-8-6/chapters/33-return-in-finally/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
