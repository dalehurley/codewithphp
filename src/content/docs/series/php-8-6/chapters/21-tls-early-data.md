---
title: "21: Send TLS 1.3 Early Data"
description: "Send a read-only status probe when resuming a loopback TLS session"
series: "php-8-6"
chapter: 21
order: 21
difficulty: "Advanced"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT15M"
sidebar:
  order: 21
---

## Overview

Send a read-only status probe when resuming a loopback TLS session. TLS 1.3 early data can reach the server before the handshake completes.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `openssl` extension with TLS 1.3, `proc_open()`, and access to loopback sockets. The `openssl` command generates a self-signed certificate trusted only by the lab client.
- Review the [previous chapter](/series/php-8-6/chapters/20-tls-pre-shared-keys/) if you’re following the series in order.

Allow about 15 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`tls-early-data.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/21-tls-early-data/tls-early-data.php) example. This example calls the shared [loopback TLS lab](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/support/tls-lab.php).

## Step 1: Run the example (~10 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/21-tls-early-data/tls-early-data.php
```

```php
<?php

// filename: tls-early-data.php

declare(strict_types=1);

require __DIR__ . '/../support/tls-lab.php';

runTlsLab('early-data');
```

### Expected Result

```text
Connection 1 resumed=no
Server received 0 early bytes
Client early data: not_sent
Connection 2 resumed=yes
Server received 6 early bytes
Client early data: accepted
```

### Why It Works

The server opts in with `max_early_data` and handles incoming bytes through `early_data_cb`. The client supplies `early_data` along with its saved session. Read the crypto status before deciding whether to resend. Early data can be replayed, so only send operations designed to tolerate replay.

In the lab, the first connection obtains a session ticket and sends no early data. The next connection uses that ticket and supplies the six bytes of `status`. The callback counts those bytes on the server side. The client's `accepted` status confirms that the server accepted the early data.

## Exercise

Remove the assignment to `$options['early_data']` in the lab client and run the example again. The second connection should resume, the server should receive zero early bytes, and the client should report `not_sent`.

## Wrap-up

Continue with [Quote SQL String Literals with MySQLi](/series/php-8-6/chapters/22-mysqli-quote-string/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
