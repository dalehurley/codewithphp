---
title: "46: Validate IDs in Custom Session Handlers"
description: "Check the ID hooks on a file-backed session handler"
series: "php-8-6"
chapter: 46
order: 46
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT15M"
sidebar:
  order: 46
---

## Overview

Check the ID hooks on a file-backed session handler. PHP 8.6 deprecates session handler objects without both `create_sid()` and `validateId()`.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `session` extension and a writable temporary directory.
- Review the [previous chapter](/series/php-8-6/chapters/45-session-defaults/) if you’re following the series in order.

Allow about 15 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`custom-session-handlers.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/46-custom-session-handlers/custom-session-handlers.php) example.

## Step 1: Run the example (~10 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/46-custom-session-handlers/custom-session-handlers.php
```

```php
<?php

// filename: custom-session-handlers.php

declare(strict_types=1);

ini_set('session.save_handler', 'files');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_cookies', '0');
session_cache_limiter('');

$storage = sys_get_temp_dir() . '/php86-sessions-' . bin2hex(random_bytes(8));
if (!mkdir($storage, 0700)) {
    throw new RuntimeException('Cannot create session storage');
}

final class FileSessionHandler extends SessionHandler
{
    public function validateId(string $id): bool
    {
        return parent::validateId($id);
    }
}

try {
    session_save_path($storage);
    session_set_save_handler(new FileSessionHandler(), false);
    $unknown = 'discard-this-unknown-id';
    session_id($unknown);
    if (!session_start()) {
        throw new RuntimeException('Could not start the session');
    }
    $replaced = session_id() !== $unknown;
    $_SESSION['visits'] = 1;
    session_destroy();
    echo 'Unknown ID replaced: ', $replaced ? 'yes' : 'no', PHP_EOL;
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    foreach (glob($storage . '/sess_*') ?: [] as $path) {
        unlink($path);
    }
    rmdir($storage);
}
```

### Expected Result

```text
Unknown ID replaced: yes
```

### Why It Works

`SessionHandler` provides both hooks for the configured underlying handler. For a custom backend, implement ID generation and test for an existing valid ID in the backend. Keep the methods’ return types and review `updateTimestamp()` too if the handler uses lazy writes.

## Exercise

Run a request twice with the ID created by the first one and verify that it can load the saved session. Test a fresh valid-shaped ID to confirm that strict mode issues a different ID.

## Wrap-up

Continue with [Move Phonetic Matching Out of Core](/series/php-8-6/chapters/47-metaphone-migration/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/deprecations_php_8_6#deprecate_passing_a_sessionhandler_object_to_session_set_save_handler_which_does_not_contain_the_create_sid_and_validateid)
