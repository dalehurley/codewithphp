---
title: "37: Migrate mbstring Regular Expressions to PCRE"
description: "Replace a multibyte match and split with `preg_match()` and `preg_split()`"
series: "php-8-6"
chapter: 37
order: 37
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 37
---

## Overview

Replace a multibyte match and split with `preg_match()` and `preg_split()`. PHP 8.6 deprecates the mbstring regex functions backed by Oniguruma.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/36-deprecated-identifiers/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`mbregex-to-pcre.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/37-mbregex-to-pcre/mbregex-to-pcre.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/37-mbregex-to-pcre/mbregex-to-pcre.php
```

```php
<?php

// filename: mbregex-to-pcre.php

declare(strict_types=1);

$text = "Montréal; Αθήνα, Cafe\u{0301}";
$tokens = preg_split('/[;,]\s*/u', $text);
if ($tokens === false) {
    throw new RuntimeException(preg_last_error_msg());
}

foreach ($tokens as $token) {
    $result = preg_match('/^\p{L}[\p{L}\p{M}]*$/u', $token);
    if ($result === false) {
        throw new RuntimeException(preg_last_error_msg());
    }
    echo $token, $result === 1 ? " OK\n" : " invalid\n";
}
```

### Expected Result

```text
Montréal OK
Αθήνα OK
Café OK
```

### Why It Works

PCRE’s `u` modifier interprets the subject and pattern as UTF-8. `\p{L}` matches Unicode letters and `\p{M}` matches combining marks. Check the pattern syntax, captured groups, and error handling during migration because PCRE and mbregex have different APIs.

## Exercise

Add an input with an ASCII digit and one with a decomposed accent. The digit-containing token should fail the letter test while the decomposed token should pass.

## Wrap-up

Continue with [Replace Deprecated Numeric Aliases](/series/php-8-6/chapters/38-numeric-aliases/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/eol-oniguruma)
