---
title: "45: Audit PHP 8.6 Session Cookie Defaults"
description: "Inspect the new defaults for session ID validation and cookie handling"
series: "php-8-6"
chapter: 45
order: 45
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 45
---

## Overview

Inspect the new defaults for session ID validation and cookie handling. Review your application’s explicit configuration before upgrading.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `session` extension. Run this example with `php -n` to see the built-in defaults.
- Review the [previous chapter](/series/php-8-6/chapters/44-stream-filter-limit/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`session-defaults.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/45-session-defaults/session-defaults.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php -n code/php-8-6/45-session-defaults/session-defaults.php
```

```php
<?php

// filename: session-defaults.php

declare(strict_types=1);

printf("Strict mode: %s\n", ini_get('session.use_strict_mode'));
$cookie = session_get_cookie_params();
printf("HttpOnly: %s\n", $cookie['httponly'] ? 'yes' : 'no');
printf("SameSite: %s\n", $cookie['samesite']);
```

### Expected Result

```text
Strict mode: 1
HttpOnly: yes
SameSite: Lax
```

### Why It Works

Strict mode rejects uninitialized IDs when the handler supports validation. HttpOnly prevents script access to the session cookie. SameSite=Lax affects cross-site requests such as embedded pages and cross-site POSTs. If a flow intentionally needs cross-site cookies, use `SameSite=None` together with `Secure` and HTTPS and test it in a browser.

## Exercise

Run the sample on PHP 8.5 with `-n`, if available, and compare the built-in values. Then check the values under the actual web SAPI and its INI configuration.

## Wrap-up

Continue with [Validate IDs in Custom Session Handlers](/series/php-8-6/chapters/46-custom-session-handlers/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/session_security_defaults)
