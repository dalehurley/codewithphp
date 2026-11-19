---
title: "28: Read and Write ZIP Archives in Memory"
description: "Build a tiny archive from generated text and read it back without creating a ZIP file on disk"
series: "php-8-6"
chapter: 28
order: 28
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 28
---

## Overview

Build a tiny archive from generated text and read it back without creating a ZIP file on disk.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `zip` extension.
- Review the [previous chapter](/series/php-8-6/chapters/27-intl-number-range/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`zip-in-memory.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/28-zip-in-memory/zip-in-memory.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/28-zip-in-memory/zip-in-memory.php
```

```php
<?php

// filename: zip-in-memory.php

declare(strict_types=1);

$archive = new ZipArchive();
if ($archive->openString(flags: ZipArchive::CREATE) !== true
    || !$archive->addFromString('notes.txt', "Pack light.\n")) {
    throw new RuntimeException('Could not build the ZIP archive');
}

$bytes = $archive->closeString();
if ($bytes === false) {
    throw new RuntimeException('Could not finalize the ZIP archive');
}

$reader = new ZipArchive();
if ($reader->openString($bytes, ZipArchive::RDONLY) !== true) {
    throw new RuntimeException('Could not reopen the ZIP archive');
}

try {
    $notes = $reader->getFromName('notes.txt');
    if ($notes === false) {
        throw new RuntimeException('Missing notes.txt');
    }
    echo $notes;
} finally {
    $reader->close();
}
```

### Expected Result

```text
Pack light.
```

### Why It Works

`openString()` opens an in-memory archive, and a successful `closeString()` returns its bytes. Opening returns `true` on success or a failure value or error code, so check it with `=== true` when branching. Budget memory for both the archive and any extracted data.

## Exercise

Add `summary.txt` and verify that you can read its text after reopening the ZIP bytes.

## Wrap-up

Continue with [Compiler Optimizations for array_map()](/series/php-8-6/chapters/29-array-map-performance/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
