---
title: "19: Resume a TLS Stream Session"
description: "Connect twice to a local TLS server and inspect whether the second connection reuses the first session"
series: "php-8-6"
chapter: 19
order: 19
difficulty: "Advanced"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT15M"
sidebar:
  order: 19
---

## Overview

Connect twice to a local TLS server and inspect whether the second connection reuses the first session. Resumption can reduce the cost of opening another connection.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `openssl` extension with TLS 1.3, `proc_open()`, and access to loopback sockets. The `openssl` command generates a self-signed certificate trusted only by the lab client.
- Review the [previous chapter](/series/php-8-6/chapters/18-sodium-xof/) if you’re following the series in order.

Allow about 15 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`tls-session-resumption.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/19-tls-session-resumption/tls-session-resumption.php) example. This example calls the shared [loopback TLS lab](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/support/tls-lab.php).

## Step 1: Run the example (~10 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/19-tls-session-resumption/tls-session-resumption.php
```

```php
<?php

// filename: tls-session-resumption.php

declare(strict_types=1);

require __DIR__ . '/../support/tls-lab.php';

runTlsLab('resume');
```

### Expected Result

```text
Connection 1 resumed=no
Connection 2 resumed=yes
```

### Why It Works

The client captures a session ticket via `session_new_cb` while reading application data. It gives that session to a subsequent connection via `session_data`. Inspect `session_reused` in the stream’s crypto metadata because the server can decline to resume.

In `runTlsLabClient()`, follow the first connection through `fgets()` before checking the captured session. Reading from the stream gives the client time to receive the TLS 1.3 ticket. The server uses the same listener and session ID context for both connections, and the client validates the same certificate each time.

## Exercise

Set `session_data` to `null` for the second connection. Both connections should report `resumed=no`.

## Wrap-up

Continue with [Authenticate TLS Streams with External PSKs](/series/php-8-6/chapters/20-tls-pre-shared-keys/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/tls_session_resumption_api)
