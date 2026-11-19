---
title: "44: Set a Limit for php://filter Chains"
description: "Apply a known read filter with an explicit slot limit"
series: "php-8-6"
chapter: 44
order: 44
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 44
---

## Overview

Apply a known read filter with an explicit slot limit. PHP 8.6 deprecates a `php://filter` URL with more than 16 filters if no explicit limit is configured.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- `allow_url_fopen=1` for the in-memory data URL used as the input.
- Review the [previous chapter](/series/php-8-6/chapters/43-class-string-guards/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`stream-filter-limit.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/44-stream-filter-limit/stream-filter-limit.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/44-stream-filter-limit/stream-filter-limit.php
```

```php
<?php

// filename: stream-filter-limit.php

declare(strict_types=1);

$context = stream_context_create(['filter' => ['max_filter_count' => 1]]);
$stream = fopen('php://filter/read=string.toupper/resource=data://text/plain,order', 'r', context: $context);
if ($stream === false) {
    throw new RuntimeException('Cannot open the filtered input');
}
try {
    echo stream_get_contents($stream), PHP_EOL;
} finally {
    fclose($stream);
}
```

### Expected Result

```text
ORDER
```

### Why It Works

Pass `max_filter_count` in the `filter` group of the stream context. The configured limit makes exceeding it fail the open. For code-defined chains, `stream_filter_append()` lets you attach filters directly to an opened stream.

## Exercise

Set the limit to 0 and confirm that opening the stream with one filter now fails. Keep 1 to allow the filter used here.

## Wrap-up

Continue with [Audit PHP 8.6 Session Cookie Defaults](/series/php-8-6/chapters/45-session-defaults/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/limit-maximum-number-of-filter-chains)
