---
title: "41: Replace strcoll() and SORT_LOCALE_STRING with Collator"
description: "Sort the same list of names under two locales"
series: "php-8-6"
chapter: 41
order: 41
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 41
---

## Overview

Sort the same list of names under two locales. PHP 8.6 deprecates `strcoll()` and the `SORT_LOCALE_STRING` flag.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `intl` extension.
- Review the [previous chapter](/series/php-8-6/chapters/40-objects-as-arrays/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`locale-collation.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/41-locale-collation/locale-collation.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/41-locale-collation/locale-collation.php
```

```php
<?php

// filename: locale-collation.php

declare(strict_types=1);

foreach (['de_DE', 'sv_SE'] as $locale) {
    $names = ['Zoe', 'Åsa', 'Anna'];
    $collator = new Collator($locale);
    if (!$collator->sort($names)) {
        throw new RuntimeException($collator->getErrorMessage());
    }
    printf("%s %s\n", $locale, implode(', ', $names));
}

$swedish = new Collator('sv_SE');
$order = $swedish->compare('Zoe', 'Åsa');
if ($order === false) {
    throw new RuntimeException($swedish->getErrorMessage());
}
echo 'Zoe comes first in Swedish: ', $order < 0 ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
de_DE Anna, Åsa, Zoe
sv_SE Anna, Zoe, Åsa
Zoe comes first in Swedish: yes
```

### Why It Works

A `Collator` keeps the locale on an instance, so two parts of an application can sort differently. Use `compare()` for pairwise ordering, `sort()` for reindexed values, and `asort()` when you need to preserve keys.

## Exercise

Use `asort()` with labeled country codes as keys and inspect the sorted keys. They should still be associated with their original country names.

## Wrap-up

Continue with [Drop the Third Argument to define()](/series/php-8-6/chapters/42-define-third-argument/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6)
