---
title: "16: Establish a Shared Key with X-Wing and ML-KEM-768"
description: "Walk through key encapsulation and decapsulation for two recipients"
series: "php-8-6"
chapter: 16
order: 16
difficulty: "Advanced"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 16
---

## Overview

Walk through key encapsulation and decapsulation for two recipients. A key encapsulation mechanism lets a sender derive a shared secret using a recipient’s public key and send a ciphertext the recipient can decapsulate.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `sodium` extension built with libsodium 1.0.22 or later.
- Review the [previous chapter](/series/php-8-6/chapters/15-url-percent-encoding/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`sodium-kem.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/16-sodium-kem/sodium-kem.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/16-sodium-kem/sodium-kem.php
```

```php
<?php

// filename: sodium-kem.php

declare(strict_types=1);

$xWingKeypair = sodium_crypto_kem_keypair();
[$ciphertext, $sent] = sodium_crypto_kem_enc(sodium_crypto_kem_publickey($xWingKeypair));
$received = sodium_crypto_kem_dec($ciphertext, sodium_crypto_kem_secretkey($xWingKeypair));
echo 'X-Wing keys agree: ', hash_equals($sent, $received) ? 'yes' : 'no', PHP_EOL;

$mlKemKeypair = sodium_crypto_kem_mlkem768_keypair();
[$ciphertext, $sent] = sodium_crypto_kem_mlkem768_enc(sodium_crypto_kem_mlkem768_publickey($mlKemKeypair));
$received = sodium_crypto_kem_mlkem768_dec($ciphertext, sodium_crypto_kem_mlkem768_secretkey($mlKemKeypair));
echo 'ML-KEM-768 keys agree: ', hash_equals($sent, $received) ? 'yes' : 'no', PHP_EOL;
```

### Expected Result

```text
X-Wing keys agree: yes
ML-KEM-768 keys agree: yes
```

### Why It Works

The receiver holds the secret key and publishes the public key through an authenticated channel. Encapsulation returns the ciphertext and shared secret, in that order. X-Wing combines ML-KEM-768 and X25519. Use an established protocol for authentication and use of the shared key.

### Troubleshooting

- If either function is undefined even with Sodium enabled, check the library build with `php --ri sodium`.

## Exercise

Print the length of the ciphertext and secret for each suite. Compare the values with the matching `SODIUM_CRYPTO_KEM_*BYTES` constants without printing the secret itself.

## Wrap-up

Continue with [Encrypt IP Addresses with Sodium](/series/php-8-6/chapters/17-sodium-ipcrypt/).

## Further Reading

- [PHP 8.6.0 extension signatures](https://github.com/php/php-src/blob/php-8.6.0/ext/sodium/libsodium.stub.php)
