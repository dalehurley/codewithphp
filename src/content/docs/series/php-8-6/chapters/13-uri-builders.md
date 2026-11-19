---
title: "13: Build URLs and URI References from Components"
description: "Assemble an absolute HTTPS URL and resolve a relative URI reference against a base"
series: "php-8-6"
chapter: 13
order: 13
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 13
---

## Overview

Assemble an absolute HTTPS URL and resolve a relative URI reference against a base. The builders give each component its own setter.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `uri` extension.
- Review the [previous chapter](/series/php-8-6/chapters/12-constant-object-mutation/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`uri-builders.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/13-uri-builders/uri-builders.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/13-uri-builders/uri-builders.php
```

```php
<?php

// filename: uri-builders.php

declare(strict_types=1);

use Uri\Rfc3986\Uri;
use Uri\Rfc3986\UriBuilder;
use Uri\WhatWg\UrlBuilder;

$query = http_build_query(['city' => 'New Town'], encoding_type: PHP_QUERY_RFC3986);
$url = new UrlBuilder()
    ->setScheme('https')
    ->setHost('Travel.Example')
    ->setPort(443)
    ->setPath('/search')
    ->setQuery($query)
    ->build();

$reference = new UriBuilder()
    ->setPath('guides/tasmania')
    ->build(new Uri('https://travel.example/catalog/'));

echo $url->toAsciiString(), PHP_EOL;
echo $reference->toString(), PHP_EOL;
```

### Expected Result

```text
https://travel.example/search?city=New%20Town
https://travel.example/catalog/guides/tasmania
```

### Why It Works

`UriBuilder` follows RFC 3986 rules for URIs and references. `UrlBuilder` follows the WHATWG rules used by browsers, including normalization such as dropping HTTPS’s default port. Choose the model that matches the consumer of the result.

## Exercise

Change the HTTPS port to 8443. Check that the URL includes `:8443`. Resolve `images/map.png` against the same RFC 3986 base and check the resulting path.

## Wrap-up

Continue with [Identify URI and Host Types](/series/php-8-6/chapters/14-uri-types/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/uri_followup#uri_building)
