---
title: "15: Percent-Encode WHATWG URL Components"
description: "Encode a filename as a single path segment and a phrase as form query data"
series: "php-8-6"
chapter: 15
order: 15
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 15
---

## Overview

Encode a filename as a single path segment and a phrase as form query data. Each component has its own percent-encoding rules.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `uri` extension.
- Review the [previous chapter](/series/php-8-6/chapters/14-uri-types/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`url-percent-encoding.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/15-url-percent-encoding/url-percent-encoding.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/15-url-percent-encoding/url-percent-encoding.php
```

```php
<?php

// filename: url-percent-encoding.php

declare(strict_types=1);

use Uri\WhatWg\UrlPercentEncodingMode;
use function Uri\WhatWg\url_percent_encode;

echo url_percent_encode('maps/Hobart north.svg', UrlPercentEncodingMode::PathSegment), PHP_EOL;
echo url_percent_encode('Hobart & coast', UrlPercentEncodingMode::FormQuery), PHP_EOL;
```

### Expected Result

```text
maps%2FHobart%20north.svg
Hobart+%26+coast
```

### Why It Works

`PathSegment` encodes a slash contained within the segment. `FormQuery` follows form encoding, including a plus sign for spaces. Encode each raw component once and join the resulting URL structure separately.

## Exercise

Encode `A+B C` with `FormQuery`. Check that the literal plus becomes `%2B` while the space becomes `+`.

## Wrap-up

Continue with [Establish a Shared Key with X-Wing and ML-KEM-768](/series/php-8-6/chapters/16-sodium-kem/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/uri_followup#percent-encoding_support)
