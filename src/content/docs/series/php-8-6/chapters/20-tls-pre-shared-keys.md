---
title: "20: Authenticate TLS Streams with External PSKs"
description: "Use an external pre-shared key on a loopback connection"
series: "php-8-6"
chapter: 20
order: 20
difficulty: "Advanced"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT15M"
sidebar:
  order: 20
---

## Overview

Use an external pre-shared key on a loopback connection. The client presents an identity and the server uses it to select the key.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `openssl` extension with TLS 1.3, `proc_open()`, and access to loopback sockets.
- Review the [previous chapter](/series/php-8-6/chapters/19-tls-session-resumption/) if you’re following the series in order.

Allow about 15 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`tls-pre-shared-keys.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/20-tls-pre-shared-keys/tls-pre-shared-keys.php) example. This example calls the shared [loopback TLS lab](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/support/tls-lab.php).

## Step 1: Run the example (~10 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/20-tls-pre-shared-keys/tls-pre-shared-keys.php
```

```php
<?php

// filename: tls-pre-shared-keys.php

declare(strict_types=1);

require __DIR__ . '/../support/tls-lab.php';

runTlsLab('psk');
```

### Expected Result

```text
PSK handshake succeeded
```

### Why It Works

PHP wraps each side’s key material in `OpenSSL\Psk`. The server returns `null` for unknown identities. This example generates an ephemeral 32-byte secret and shares it through a child-process pipe. Real peers need secure provisioning and scoped identities.

In the lab’s PSK branch, `psk_client_cb` returns the key alongside the identity `tutorial-client`. The server’s `psk_server_cb` receives that identity and supplies the corresponding key. The lab uses PSK authentication for this connection, so the server needs no certificate.

## Exercise

Change the client identity to `unknown` in the lab helper’s PSK branch and observe the handshake fail. Change it back to `tutorial-client` to restore the connection.

## Wrap-up

Continue with [Send TLS 1.3 Early Data](/series/php-8-6/chapters/21-tls-early-data/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
