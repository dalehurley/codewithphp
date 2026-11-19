---
title: "23: Set Byte Order with pack() and unpack()"
description: "Write a signed integer and a float in network byte order"
series: "php-8-6"
chapter: 23
order: 23
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 23
---

## Overview

Write a signed integer and a float in network byte order. Endianness tells the reader which end of a sequence of bytes holds the most significant part.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/22-mysqli-quote-string/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`binary-endianness.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/23-binary-endianness/binary-endianness.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/23-binary-endianness/binary-endianness.php
```

```php
<?php

// filename: binary-endianness.php

declare(strict_types=1);

$packet = pack('s>f>', -2, 1.5);
echo bin2hex($packet), PHP_EOL;

$decoded = unpack('s>status/f>scale', $packet);
printf("status=%d scale=%.1f\n", $decoded['status'], $decoded['scale']);
```

### Expected Result

```text
fffe3fc00000
status=-2 scale=1.5
```

### Why It Works

The `>` modifier selects big-endian and `<` selects little-endian. PHP 8.6 recognizes the modifier immediately after a supported format code. For a legacy element name starting with one of those characters, put the repetition count before the name, as in `s1<code`.

### Troubleshooting

- An 8-bit code such as `C` has no byte order. Remove the modifier from those codes.

## Exercise

Switch both formats to little-endian and run the round trip again. Confirm the bytes change to `feff0000c03f` and the decoded values stay the same.

## Wrap-up

Continue with [Locate a JSON Syntax Error](/series/php-8-6/chapters/24-json-error-location/).

## Further Reading

- [Integer endianness RFC](https://wiki.php.net/rfc/pack-unpack-endianness-signed-integers-support)
- [Floating-point endianness RFC](https://wiki.php.net/rfc/pack-unpack-float-endianness-modifier)
