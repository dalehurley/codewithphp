---
title: "36: Rename Deprecated Identifiers"
description: "Replace names that PHP 8.6 has started to reserve"
series: "php-8-6"
chapter: 36
order: 36
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 36
---

## Overview

Replace names that PHP 8.6 has started to reserve. Public libraries should review declarations as well as namespace aliases and string-based registrations.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/35-debug-info-return-type/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`deprecated-identifiers.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/36-deprecated-identifiers/deprecated-identifiers.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/36-deprecated-identifiers/deprecated-identifiers.php
```

```php
<?php

// filename: deprecated-identifiers.php

declare(strict_types=1);

const DEFAULT_LANGUAGE = 'en';

final class MessageRules
{
    public const MESSAGE_NAMESPACE = 'billing';
}

function isStatus(string $state): bool
{
    return in_array($state, ['pending', 'complete'], true);
}

printf("%s.%s state=%s\n", MessageRules::MESSAGE_NAMESPACE, DEFAULT_LANGUAGE,
    isStatus('complete') ? 'valid' : 'invalid');
```

### Expected Result

```text
billing.en state=valid
```

### Why It Works

`let` and `is` are deprecated in declarations of types, functions and constants and in the corresponding aliases. Also rename `_` constants and affected compile-time aliases, class constants called `namespace`, and functions called `readonly`. Audit references when renaming a public symbol.

| Old declaration | Example replacement |
| --- | --- |
| A type or alias called `let` | `MessageRules` |
| A function called `is` | `isStatus()` |
| A constant called `_` | `DEFAULT_LANGUAGE` |
| A class constant called `namespace` | `MESSAGE_NAMESPACE` |
| A function called `readonly` | `loadReadOnlySettings()` |

## Exercise

Search a branch of your code for those exact identifiers and lint the affected files with `php -d error_reporting=-1 -l path.php`. Update references along with declarations.

## Wrap-up

Continue with [Migrate mbstring Regular Expressions to PCRE](/series/php-8-6/chapters/37-mbregex-to-pcre/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6)
