---
title: "09: Default Values for Readonly Properties"
description: "Define a fixed export format in a readonly class"
series: "php-8-6"
chapter: 9
order: 9
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 9
---

## Overview

Define a fixed export format in a readonly class. A property default can satisfy a get-only interface property as soon as the object is created.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- Review the [previous chapter](/series/php-8-6/chapters/08-property-access-reflection/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`readonly-defaults.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/09-readonly-defaults/readonly-defaults.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/09-readonly-defaults/readonly-defaults.php
```

```php
<?php

// filename: readonly-defaults.php

declare(strict_types=1);

interface ExportFormat
{
    public string $extension { get; }
}

final readonly class CsvFormat implements ExportFormat
{
    public string $extension = 'csv';

    public function filename(string $base): string
    {
        return $base . '.' . $this->extension;
    }
}

$format = new CsvFormat();
echo $format->filename('orders'), PHP_EOL;

try {
    $format->extension = 'tsv';
} catch (Error) {
    echo "Readonly default already initialized\n";
}
```

### Expected Result

```text
orders.csv
Readonly default already initialized
```

### Why It Works

A declared default initializes the property before the constructor runs. If callers need to supply a value, use a constructor parameter. A promoted parameter’s default applies to that parameter and property initialization occurs through promotion.

## Exercise

Change `extension` to `'tsv'` and instantiate two exporters. Verify that they start with the same extension and that reassignment still raises an error.

## Wrap-up

Continue with [Override Attributes on Constants and Enum Cases](/series/php-8-6/chapters/10-override-constants/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/readonly_property_defaults)
