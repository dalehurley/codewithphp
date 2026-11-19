---
title: "03: Wait for I/O with Io\\Poll"
description: "Watch one end of a local socket pair for incoming data"
series: "php-8-6"
chapter: 3
order: 3
difficulty: "Advanced"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT15M"
sidebar:
  order: 3
---

## Overview

Watch one end of a local socket pair for incoming data. Polling lets a program wait for readiness across many streams with a platform-specific backend.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- A PHP build that supports Unix-domain socket pairs.
- Review the [previous chapter](/series/php-8-6/chapters/02-duration/) if you’re following the series in order.

Allow about 15 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`polling-api.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/03-polling-api/polling-api.php) example.

## Step 1: Run the example (~10 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/03-polling-api/polling-api.php
```

```php
<?php

// filename: polling-api.php

declare(strict_types=1);

use Io\Poll\Context;
use Io\Poll\Event;
use Time\Duration;

$pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
if ($pair === false) {
    throw new RuntimeException('Cannot create local sockets');
}
[$reader, $writer] = $pair;
stream_set_blocking($reader, false);

$poll = new Context();
$watcher = $poll->add(new StreamPollHandle($reader), [Event::Read], data: 'inbox');

try {
    echo 'Initially ready: ', count($poll->wait(Duration::fromSeconds(0))), PHP_EOL;
    if (fwrite($writer, "job-ready\n") !== 10) {
        throw new RuntimeException('Cannot send the message');
    }
    foreach ($poll->wait(Duration::fromSeconds(1)) as $ready) {
        if ($ready->hasTriggered(Event::Read)) {
            printf("%s: %s", $ready->getData(), fgets($ready->getHandle()->getStream()));
        }
    }
} finally {
    $watcher->remove();
    fclose($reader);
    fclose($writer);
}
```

### Expected Result

```text
Initially ready: 0
inbox: job-ready
```

### Why It Works

The context returns ready watchers, which carry the registered handle and optional application data. A timeout bounds the wait and a zero duration probes immediately. Remove watchers before closing their streams. In an event loop, use nonblocking I/O and handle partial reads and writes.

## Exercise

After reading the message but before removing the watcher, call `wait(Duration::fromSeconds(0))` again. Check that the result is empty while the writer stays open.

## Wrap-up

Continue with [Structured Stream Errors](/series/php-8-6/chapters/04-stream-errors/).

## Further Reading

- [Feature RFC](https://wiki.php.net/rfc/poll_api)
