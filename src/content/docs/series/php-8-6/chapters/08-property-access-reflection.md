---
title: "08: Inspect Property Read and Write Access"
description: "Check whether a form hydrator can read or write a property from public scope"
series: "php-8-6"
chapter: 8
order: 8
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 8
---

## Overview

Check whether a form hydrator can read or write a property from public scope. Visibility and the state of a readonly property both influence the answer.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/07-parameter-doccomments/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`property-access-reflection.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/08-property-access-reflection/property-access-reflection.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/08-property-access-reflection/property-access-reflection.php
```

```php
<?php

// filename: property-access-reflection.php

declare(strict_types=1);

final class Article
{
    public string $title = 'Feature tour';
    public private(set) string $slug = 'feature-tour';
    public readonly int $id;

    public function assignId(int $id): void
    {
        $this->id = $id;
    }
}

$article = new Article();
foreach (['title', 'slug', 'id'] as $name) {
    $property = new ReflectionProperty(Article::class, $name);
    printf("%s read=%s write=%s\n", $name,
        $property->isReadable(null, $article) ? 'yes' : 'no',
        $property->isWritable(null, $article) ? 'yes' : 'no');
}

$article->assignId(7);
$id = new ReflectionProperty(Article::class, 'id');
echo 'Initialized id readable: ', $id->isReadable(null, $article) ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
title read=yes write=yes
slug read=yes write=no
id read=no write=no
Initialized id readable: yes
```

### Why It Works

Pass `null` for global scope and pass the actual object to include its initialization state. A class scope can yield a different result for private setters. By default the tests account for property hooks and magic access.

## Exercise

Check `slug` with `Article::class` as the scope and compare it with the result from public scope.

## Wrap-up

Continue with [Default Values for Readonly Properties](/series/php-8-6/chapters/09-readonly-defaults/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/isreadable-iswriteable)
