---
title: "17: Encrypt IP Addresses with Sodium"
description: "Round-trip a documentation IP address through Sodium’s IP encryption functions"
series: "php-8-6"
chapter: 17
order: 17
difficulty: "Advanced"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 17
---

## Overview

Round-trip a documentation IP address through Sodium’s IP encryption functions. Select a mode based on which relationships the stored ciphertext needs to reveal.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `sodium` extension with IP encryption support.
- Review the [previous chapter](/series/php-8-6/chapters/16-sodium-kem/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`sodium-ipcrypt.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/17-sodium-ipcrypt/sodium-ipcrypt.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/17-sodium-ipcrypt/sodium-ipcrypt.php
```

```php
<?php

// filename: sodium-ipcrypt.php

declare(strict_types=1);

$ip = '192.0.2.15';

$key = sodium_crypto_ipcrypt_keygen();
echo 'Deterministic: ', sodium_crypto_ipcrypt_decrypt(sodium_crypto_ipcrypt_encrypt($ip, $key), $key), PHP_EOL;

$ndKey = sodium_crypto_ipcrypt_nd_keygen();
echo 'ND: ', sodium_crypto_ipcrypt_nd_decrypt(sodium_crypto_ipcrypt_nd_encrypt($ip, $ndKey), $ndKey), PHP_EOL;

$ndxKey = sodium_crypto_ipcrypt_ndx_keygen();
echo 'NDX: ', sodium_crypto_ipcrypt_ndx_decrypt(sodium_crypto_ipcrypt_ndx_encrypt($ip, $ndxKey), $ndxKey), PHP_EOL;

$prefixKey = sodium_crypto_ipcrypt_pfx_keygen();
echo 'PFX: ', sodium_crypto_ipcrypt_pfx_decrypt(sodium_crypto_ipcrypt_pfx_encrypt($ip, $prefixKey), $prefixKey), PHP_EOL;

echo 'Binary round trip: ', sodium_bin2ip(sodium_ip2bin($ip)), PHP_EOL;
```

### Expected Result

```text
Deterministic: 192.0.2.15
ND: 192.0.2.15
NDX: 192.0.2.15
PFX: 192.0.2.15
Binary round trip: 192.0.2.15
```

### Why It Works

The default mode preserves the address format and repeats the same output for the same address and key. The randomized ND and NDX modes use a fresh tweak and produce longer ciphertexts. The PFX mode preserves shared prefixes, which can support subnet analysis and also reveals subnet relationships.

## Exercise

Replace the IPv4 input with `2001:db8::5` and confirm all modes still round-trip. Compare `sodium_ip2bin()` output lengths for the IPv4 and IPv6 examples.

## Wrap-up

Continue with [Extend Hash Output with SHAKE and TurboSHAKE](/series/php-8-6/chapters/18-sodium-xof/).

## Further Reading

- [PHP 8.6.0 extension signatures](https://github.com/php/php-src/blob/php-8.6.0/ext/sodium/libsodium.stub.php)
