---
title: "14: Identify URI and Host Types"
description: "Classify URL components for a report"
series: "php-8-6"
chapter: 14
order: 14
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 14
---

## Overview

Classify URL components for a report. The URI extension exposes distinct methods for the URI form, host form, and whether a WHATWG scheme is special.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `uri` extension.
- Review the [previous chapter](/series/php-8-6/chapters/13-uri-builders/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`uri-types.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/14-uri-types/uri-types.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/14-uri-types/uri-types.php
```

```php
<?php

// filename: uri-types.php

declare(strict_types=1);

use Uri\Rfc3986\Uri;
use Uri\WhatWg\Url;

$uri = new Uri('//assets.example/logo.svg');
echo 'URI type: ', $uri->getUriType()->name, PHP_EOL;
echo 'RFC host type: ', $uri->getHostType()->name, PHP_EOL;

$url = new Url('https://192.0.2.4/logo.svg');
echo 'WHATWG host type: ', $url->getHostType()->name, PHP_EOL;
echo 'Special scheme: ', $url->isSpecialScheme() ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
URI type: NetworkPathReference
RFC host type: RegisteredName
WHATWG host type: IpV4
Special scheme: yes
```

### Why It Works

RFC 3986 separates absolute URIs from several kinds of references. WHATWG special schemes, such as HTTPS, use rules that differ from opaque schemes. A nullable host type lets you account for input without a host.

## Exercise

Inspect `https://[2001:db8::1]/` and `docs/readme`. Confirm that the first has an IPv6 host type and the second has a relative path reference with no host.

## Wrap-up

Continue with [Percent-Encode WHATWG URL Components](/series/php-8-6/chapters/15-url-percent-encoding/).

## Further Reading

- [URI type detection RFC](https://wiki.php.net/rfc/uri_followup#uri_type_detection)
- [Host type detection RFC](https://wiki.php.net/rfc/uri_followup#host_type_detection)
