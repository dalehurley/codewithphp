---
title: "40: Pass Arrays to Array and Compression APIs"
description: "Convert a data object at its API boundary"
series: "php-8-6"
chapter: 40
order: 40
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 40
---

## Overview

Convert a data object at its API boundary. PHP 8.6 deprecates passing objects to array walkers, query building, mbstring conversion, and several compression options.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `mbstring` and `zlib` extensions.
- Review the [previous chapter](/series/php-8-6/chapters/39-spl-object-and-extension-introspection/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`objects-as-arrays.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/40-objects-as-arrays/objects-as-arrays.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/40-objects-as-arrays/objects-as-arrays.php
```

```php
<?php

// filename: objects-as-arrays.php

declare(strict_types=1);

$request = (object) ['query' => '  live music  ', 'page' => 2];
$parameters = get_object_vars($request);
array_walk($parameters, static function (mixed &$value): void {
    if (is_string($value)) {
        $value = trim($value);
    }
});
echo http_build_query($parameters, encoding_type: PHP_QUERY_RFC3986), PHP_EOL;

$nested = ['filters' => get_object_vars((object) ['city' => ' Hobart '])];
array_walk_recursive($nested, static function (string &$value): void {
    $value = trim($value);
});
mb_convert_variables('UTF-8', 'UTF-8', $nested);
echo http_build_query($nested), PHP_EOL;

$options = get_object_vars((object) ['level' => 6]);
$compressor = deflate_init(ZLIB_ENCODING_RAW, $options);
if ($compressor === false) {
    throw new RuntimeException('Compression setup failed');
}
$compressed = deflate_add($compressor, 'ready', ZLIB_FINISH);
if ($compressed === false) {
    throw new RuntimeException('Compression failed');
}
$decompressor = inflate_init(ZLIB_ENCODING_RAW, []);
if ($decompressor === false) {
    throw new RuntimeException('Decompression setup failed');
}
echo inflate_add($decompressor, $compressed, ZLIB_FINISH), PHP_EOL;
```

### Expected Result

```text
query=live%20music&page=2
filters%5Bcity%5D=Hobart
ready
```

### Why It Works

`get_object_vars()` returns the properties visible from the calling scope. Convert nested objects explicitly for `http_build_query()` and recursive operations. If you walk the array by reference, the edits affect the array copy and you can deliberately write changes back to the object as needed. Use array options for `deflate_init()`, `inflate_init()`, `zlib.*`, and `bzip2.*` compression filters.

## Exercise

Add a nested object to a payload, map the nested value to an array and print the resulting query string. Check that the nested field appears in the encoded bracket notation.

## Wrap-up

Continue with [Replace strcoll() and SORT_LOCALE_STRING with Collator](/series/php-8-6/chapters/41-locale-collation/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6)
