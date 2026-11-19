---
title: "34: Move Results and Generators Out of Object Lifecycles"
description: "Create an object through a factory and iterate its data through an ordinary method"
series: "php-8-6"
chapter: 34
order: 34
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 34
---

## Overview

Create an object through a factory and iterate its data through an ordinary method. A constructor or destructor that returns a value or acts as a generator now triggers a deprecation.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/33-return-in-finally/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`constructor-destructor-returns.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/34-constructor-destructor-returns/constructor-destructor-returns.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/34-constructor-destructor-returns/constructor-destructor-returns.php
```

```php
<?php

// filename: constructor-destructor-returns.php

declare(strict_types=1);

final class Labels
{
    private function __construct(private array $labels)
    {
    }

    public static function fromCsv(string $csv): self
    {
        return new self(explode(',', $csv));
    }

    public function values(): Generator
    {
        foreach ($this->labels as $label) {
            yield trim($label);
        }
    }
}

foreach (Labels::fromCsv('tea, coffee')->values() as $label) {
    echo $label, PHP_EOL;
}
```

### Expected Result

```text
tea
coffee
```

### Why It Works

Initialize state in `__construct()`. Return the new object from a factory and put `yield` in a separate method so callers can drive iteration. A destructor is for cleanup and can use a bare `return;` for an early exit.

## Exercise

Add another label to the factory input and confirm it appears only when you iterate the generator.

## Wrap-up

Continue with [Return an Array from __debugInfo()](/series/php-8-6/chapters/35-debug-info-return-type/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecate-return-value-from-construct)
