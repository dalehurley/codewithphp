---
title: "22: Quote SQL String Literals with MySQLi"
description: "Quote a name containing an apostrophe and round-trip it through a SELECT"
series: "php-8-6"
chapter: 22
order: 22
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 22
---

## Overview

Quote a name containing an apostrophe and round-trip it through a SELECT. Use the new API when building a string literal for SQL generation where binding parameters is unavailable.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `mysqli` extension and a reachable local MySQL or MariaDB server. Set `MYSQL_USER` for an account with permission to connect. Optionally set `MYSQL_HOST` (default `127.0.0.1`), `MYSQL_PORT` (default `3306`), and `MYSQL_PASSWORD` for that account.
- Review the [previous chapter](/series/php-8-6/chapters/21-tls-early-data/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`mysqli-quote-string.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/22-mysqli-quote-string/mysqli-quote-string.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/22-mysqli-quote-string/mysqli-quote-string.php
```

```php
<?php

// filename: mysqli-quote-string.php

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(
    getenv('MYSQL_HOST') ?: '127.0.0.1',
    getenv('MYSQL_USER') ?: 'root',
    getenv('MYSQL_PASSWORD') ?: '',
    port: (int) (getenv('MYSQL_PORT') ?: '3306'),
);
try {
    $db->set_charset('utf8mb4');
    $quoted = $db->quote_string("O'Reilly");
    $result = $db->query("SELECT $quoted AS label");
    echo $result->fetch_assoc()['label'], PHP_EOL;
} finally {
    $db->close();
}
```

### Expected Result

```text
O'Reilly
```

### Why It Works

Set the connection character set first so escaping follows the connection encoding. `quote_string()` returns the complete literal including its delimiters. For ordinary queries with user data, bind prepared statement parameters.

### Troubleshooting

- With a connection error, confirm the server is listening on the configured host and port and that the account permits connections from that host.

## Exercise

Try a Unicode name containing an apostrophe. Compare the value returned by the server with the original and confirm both match.

## Wrap-up

Continue with [Set Byte Order with pack() and unpack()](/series/php-8-6/chapters/23-binary-endianness/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/mysqli_quote_string)
