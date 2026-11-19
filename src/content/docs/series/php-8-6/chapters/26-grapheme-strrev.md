---
title: "26: Reverse Text by Grapheme Cluster"
description: "Reverse a label containing a decomposed accent and an emoji with a skin-tone modifier"
series: "php-8-6"
chapter: 26
order: 26
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 26
---

## Overview

Reverse a label containing a decomposed accent and an emoji with a skin-tone modifier. A grapheme cluster is a sequence of code points that a user typically sees as one character.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `intl` extension.
- Review the [previous chapter](/series/php-8-6/chapters/25-error-include-args/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`grapheme-strrev.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/26-grapheme-strrev/grapheme-strrev.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/26-grapheme-strrev/grapheme-strrev.php
```

```php
<?php

// filename: grapheme-strrev.php

declare(strict_types=1);

$label = "A:e\u{0301}:👍🏽";
echo $label, PHP_EOL;
echo grapheme_strrev($label), PHP_EOL;
```

### Expected Result

```text
A:é:👍🏽
👍🏽:é:A
```

### Why It Works

`grapheme_strrev()` moves whole clusters, preserving the combining accent and modifier with their base characters. This helps with user-visible text encoded in UTF-8.

## Exercise

Replace the emoji with a two-code-point flag and compare the output before and after reversal. Both regional indicators should stay together.

## Wrap-up

Continue with [Format Localized Number Ranges](/series/php-8-6/chapters/27-intl-number-range/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/grapheme_strrev)
