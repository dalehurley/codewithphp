---
title: "48: Replace Deprecated MySQLi Charset and Statement Calls"
description: "Check the connection charset and run a bound query without creating an empty statement"
series: "php-8-6"
chapter: 48
order: 48
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 48
---

## Overview

Check the connection charset and run a bound query without creating an empty statement. PHP 8.6 deprecates `get_charset()`, `stmt_init()`, their procedural counterparts and constructing `mysqli_stmt` without SQL.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `mysqli` extension and a reachable local MySQL or MariaDB server. Set `MYSQL_USER` for an account with permission to connect. Optionally set `MYSQL_HOST` (default `127.0.0.1`), `MYSQL_PORT` (default `3306`), and `MYSQL_PASSWORD` for that account.
- Review the [previous chapter](/series/php-8-6/chapters/47-metaphone-migration/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`mysqli-charset-and-statements.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/48-mysqli-charset-and-statements/mysqli-charset-and-statements.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/48-mysqli-charset-and-statements/mysqli-charset-and-statements.php
```

```php
<?php

// filename: mysqli-charset-and-statements.php

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
    echo $db->character_set_name(), PHP_EOL;
    $result = $db->execute_query('SELECT ? AS message', ["Today's specials"]);
    echo $result->fetch_assoc()['message'], PHP_EOL;
} finally {
    $db->close();
}
```

### Expected Result

```text
utf8mb4
Today's specials
```

### Why It Works

Use `character_set_name()` when the application needs the current charset name. It returns a string, so audit consumers that used other fields on the former object. Prepare SQL with `prepare()` or use `execute_query()` to prepare, bind and execute in one call.

## Exercise

Add a second placeholder for a number. Pass it as the second bound value and select both columns to verify the order.

## Wrap-up

Continue with [Migrate SplFileObject CSV Calls](/series/php-8-6/chapters/49-splfileobject-csv/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6)
