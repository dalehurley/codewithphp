---
title: "24: Locate a JSON Syntax Error"
description: "Find the location of a syntax error in a multi-line JSON document"
series: "php-8-6"
chapter: 24
order: 24
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 24
---

## Overview

Find the location of a syntax error in a multi-line JSON document. PHP 8.6 includes position information in the diagnostic message.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/23-binary-endianness/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`json-error-location.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/24-json-error-location/json-error-location.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/24-json-error-location/json-error-location.php
```

```php
<?php

// filename: json-error-location.php

declare(strict_types=1);

$document = "{\n  \"items\": [1, 2,]\n}";

try {
    json_decode($document, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    echo $error->getMessage(), PHP_EOL;
}

json_decode($document);
echo json_last_error_msg(), PHP_EOL;
```

### Expected Result

```text
Syntax error near location 2:18
Syntax error near location 2:18
```

### Why It Works

Both the thrown `JsonException` and `json_last_error_msg()` can describe the location. Use the numeric error code for program logic because message wording can change. Read the last-error message immediately after the applicable call without `JSON_THROW_ON_ERROR`.

## Exercise

Move the trailing comma to another line and inspect the new diagnostic. Remove it and confirm decoding succeeds.

## Wrap-up

Continue with [Show Function Arguments in PHP Errors](/series/php-8-6/chapters/25-error-include-args/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
