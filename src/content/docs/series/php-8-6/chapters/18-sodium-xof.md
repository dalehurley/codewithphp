---
title: "18: Extend Hash Output with SHAKE and TurboSHAKE"
description: "Generate digests at a length chosen by your application"
series: "php-8-6"
chapter: 18
order: 18
difficulty: "Advanced"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 18
---

## Overview

Generate digests at a length chosen by your application. An extendable-output function can produce any requested output length within the API’s limits.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `sodium` extension with SHAKE and TurboSHAKE support.
- Review the [previous chapter](/series/php-8-6/chapters/17-sodium-ipcrypt/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`sodium-xof.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/18-sodium-xof/sodium-xof.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/18-sodium-xof/sodium-xof.php
```

```php
<?php

// filename: sodium-xof.php

declare(strict_types=1);

$data = 'order-42';
$digest = sodium_crypto_xof_shake128(32, $data);

$state = sodium_crypto_xof_shake128_init();
sodium_crypto_xof_shake128_update($state, 'order-');
sodium_crypto_xof_shake128_update($state, '42');
echo 'SHAKE128 chunks agree: ', hash_equals($digest, sodium_crypto_xof_shake128_squeeze($state, 32))
    ? 'yes' : 'no', PHP_EOL;

echo 'SHAKE256 bytes: ', strlen(sodium_crypto_xof_shake256(32, $data)), PHP_EOL;
echo 'TurboSHAKE128 bytes: ', strlen(sodium_crypto_xof_turboshake128(16, $data)), PHP_EOL;
echo 'TurboSHAKE256 bytes: ', strlen(sodium_crypto_xof_turboshake256(64, $data)), PHP_EOL;
```

### Expected Result

```text
SHAKE128 chunks agree: yes
SHAKE256 bytes: 32
TurboSHAKE128 bytes: 16
TurboSHAKE256 bytes: 64
```

### Why It Works

SHAKE and TurboSHAKE return raw bytes, so use `bin2hex()` for textual storage. The stateful API accepts chunks through `update()` and then reads output through `squeeze()`. Pick the algorithm and output length required by the protocol or file format and keep them fixed for comparisons.

## Exercise

Change the requested output to 16 bytes in both SHAKE128 calls. Confirm that they still agree and that the hex representation has 32 characters.

## Wrap-up

Continue with [Resume a TLS Stream Session](/series/php-8-6/chapters/19-tls-session-resumption/).

## Further Reading

- [PHP 8.6.0 extension signatures](https://github.com/php/php-src/blob/php-8.6.0/ext/sodium/libsodium.stub.php)
