---
title: "47: Move Phonetic Matching Out of Core"
description: "Isolate a phonetic encoder behind a callable so you can replace the deprecated `metaphone()` implementation and verify your search behavior"
series: "php-8-6"
chapter: 47
order: 47
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 47
---

## Overview

Isolate a phonetic encoder behind a callable so you can replace the deprecated `metaphone()` implementation and verify your search behavior.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/46-custom-session-handlers/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`metaphone-migration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/47-metaphone-migration/metaphone-migration.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/47-metaphone-migration/metaphone-migration.php
```

```php
<?php

// filename: metaphone-migration.php

declare(strict_types=1);

function groupNames(array $names, callable $encode): array
{
    $groups = [];
    foreach ($names as $name) {
        $groups[$encode($name)][] = $name;
    }
    return $groups;
}

$names = ['Smith', 'Smyth', 'Jones'];
echo json_encode(groupNames($names, soundex(...)), JSON_THROW_ON_ERROR), PHP_EOL;
```

### Expected Result

```text
{"S530":["Smith","Smyth"],"J520":["Jones"]}
```

### Why It Works

Persisted phonetic keys depend on the chosen algorithm, its input normalization, and its version. The demo uses the built-in English-oriented `soundex()` to show the callable boundary. Choose your userland encoder with representative fixtures before generating replacement search keys.

## Exercise

Add two spelling variants from your own dataset. Record whether each algorithm groups them together and keep the result as a migration fixture.

## Wrap-up

Continue with [Replace Deprecated MySQLi Charset and Statement Calls](/series/php-8-6/chapters/48-mysqli-charset-and-statements/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_metaphone_function)
