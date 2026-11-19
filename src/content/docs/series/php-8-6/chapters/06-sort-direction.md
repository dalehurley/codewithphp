---
title: "06: Type-Safe Sorting with SortDirection"
description: "Give a leaderboard function a type-safe way to accept a sort direction"
series: "php-8-6"
chapter: 6
order: 6
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 6
---

## Overview

Give a leaderboard function a type-safe way to accept a sort direction. Callers choose a case from the built-in enum.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/05-clamp/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`sort-direction.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/06-sort-direction/sort-direction.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/06-sort-direction/sort-direction.php
```

```php
<?php

// filename: sort-direction.php

declare(strict_types=1);

function orderScores(array $scores, SortDirection $direction = SortDirection::Ascending): array
{
    uasort($scores, static fn (int $left, int $right): int => match ($direction) {
        SortDirection::Ascending => $left <=> $right,
        SortDirection::Descending => $right <=> $left,
    });
    return $scores;
}

$scores = ['Ari' => 42, 'Jo' => 60];
echo implode(', ', array_keys(orderScores($scores))), PHP_EOL;
echo implode(', ', array_keys(orderScores($scores, SortDirection::Descending))), PHP_EOL;
```

### Expected Result

```text
Ari, Jo
Jo, Ari
```

### Why It Works

The signature accepts `Ascending` and `Descending` through a shared enum type. The sample maps the case to a comparator because `uasort()` expects a comparator. It preserves the player names as array keys.

## Exercise

Add Cara with 65 points. Confirm that she appears first only in descending order.

## Wrap-up

Continue with [DocComments on Function Parameters](/series/php-8-6/chapters/07-parameter-doccomments/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/sort_direction_enum)
