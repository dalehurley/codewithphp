---
title: "07: DocComments on Function Parameters"
description: "Read parameter descriptions directly from reflection"
series: "php-8-6"
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 7
---

## Overview

Read parameter descriptions directly from reflection. This is useful for a command help generator that needs to display an explanation beside each argument.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/06-sort-direction/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`parameter-doccomments.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/07-parameter-doccomments/parameter-doccomments.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/07-parameter-doccomments/parameter-doccomments.php
```

```php
<?php

// filename: parameter-doccomments.php

declare(strict_types=1);

function importOrders(
    /** Local CSV path */
    string $path,
    /** Maximum rows in one batch */
    int $limit = 100,
    bool $verbose = false,
): array {
    return compact('path', 'limit', 'verbose');
}

foreach (new ReflectionFunction('importOrders')->getParameters() as $parameter) {
    printf("%s %s\n", $parameter->getName(), $parameter->getDocComment() ?: '(undocumented)');
}
```

### Expected Result

```text
path /** Local CSV path */
limit /** Maximum rows in one batch */
verbose (undocumented)
```

### Why It Works

The declaration can attach a `/** ... */` comment to each parameter. `getDocComment()` returns the raw comment or `false` when no comment exists. A doc generator can use that distinction to supply a fallback.

### Troubleshooting

- Use a documentation comment beginning with `/**`. Ordinary block comments never appear in reflection.

## Exercise

Add a documented `bool $dryRun = false` argument and verify that the report prints its name and comment.

## Wrap-up

Continue with [Inspect Property Read and Write Access](/series/php-8-6/chapters/08-property-access-reflection/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/parameter-doccomments)
