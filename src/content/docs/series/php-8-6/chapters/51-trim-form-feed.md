---
title: "51: Account for Form Feeds in trim()"
description: "Clean imported text fields with the new default trimming behavior"
series: "php-8-6"
chapter: 51
order: 51
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 51
---

## Overview

Clean imported text fields with the new default trimming behavior. PHP 8.6 adds the form-feed character `` to the default set for `trim()`, `ltrim()`, and `rtrim()`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/50-arrayiterator-deprecations/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`trim-form-feed.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/51-trim-form-feed/trim-form-feed.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/51-trim-form-feed/trim-form-feed.php
```

```php
<?php

// filename: trim-form-feed.php

declare(strict_types=1);

$value = "\fready\f";
echo 'Both: ', bin2hex(trim($value)), PHP_EOL;
echo 'Left: ', bin2hex(ltrim($value)), PHP_EOL;
echo 'Right: ', bin2hex(rtrim($value)), PHP_EOL;

$oldMask = " \n\r\t\v\0";
echo 'Explicit mask: ', bin2hex(trim($value, $oldMask)), PHP_EOL;
```

### Expected Result

```text
Both: 7265616479
Left: 72656164790c
Right: 0c7265616479
Explicit mask: 0c72656164790c
```

### Why It Works

The change applies when you omit the character mask. Pass an explicit mask for a format that uses form feeds as edge delimiters. The sample prints hexadecimal so the invisible bytes are easy to compare.

## Exercise

Trim a form feed from only the left side and verify that the hexadecimal output still ends in `0c`.

## Wrap-up

Continue with [Handle Stream Flush and Close Failures](/series/php-8-6/chapters/52-stream-close-failures/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/trim_form_feed)
