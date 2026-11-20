---
title: "10: Can PHP Do Concurrency?"
description: "Run a PHP Fiber and choose between concurrent requests, overlapping I/O and work dispatched to a queue."
series: "php-misconceptions"
chapter: 10
estimatedTime: "PT10M"
sidebar:
  order: 10
---

## Overview

A web server can handle multiple PHP requests concurrently through processes or threads. Inside a single request, the PHP code you write usually runs synchronously. For example, a blocking HTTP request waits before your function continues. You can overlap independent I/O with a suitable client, and you can send work to another worker through a queue. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) · [Laravel HTTP client concurrency](https://laravel.com/docs/13.x/http-client#concurrent-requests) · [Laravel queues](https://laravel.com/docs/13.x/queues)

## Quick Start

From the repository root, run the Fiber example with PHP 8.6 or later. PHP introduced Fibers in 8.1.

```bash
php code/php-misconceptions/fibers.php
```

```text
1. Fiber starts its work
2. Caller runs while the fiber is paused
3. Fiber finishes its work
```

`Fiber::suspend()` yields control to the caller. The caller resumes the suspended work explicitly. Wrapping a blocking function call in a Fiber still blocks the thread that runs it. Fiber-based async libraries pair suspension with an event loop and I/O that can be polled without blocking the loop. [PHP Fibers](https://www.php.net/manual/en/language.fibers.php) · [ReactPHP event loop](https://reactphp.org/event-loop/)

## Exercise

A checkout page needs prices from two independent suppliers and can send the receipt after returning. Use an HTTP client pool to overlap the supplier calls. The response still waits for their results, so decide what the page should do when one supplier times out. Dispatch the receipt to a database or Redis queue serviced by a separate queue worker. Decide how that worker recognises a retried job. [Laravel HTTP client concurrency](https://laravel.com/docs/13.x/http-client#concurrent-requests) · [Laravel queues](https://laravel.com/docs/13.x/queues)

## Next chapter

[Does PHP Have Serious Developer Tools?](/series/php-misconceptions/chapters/11-developer-tooling/)
