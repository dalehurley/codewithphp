---
title: "10: Can PHP Do Concurrency?"
description: "Trace a PHP Fiber, compare serial and concurrent supplier requests, and decide which checkout work can use a queue."
series: "php-misconceptions"
chapter: 10
estimatedTime: "PT25M"
sidebar:
  order: 10
---

## Overview

A checkout needs two supplier prices before it can show a combined quote. The customer can finish checkout without waiting for a receipt to be sent. At the same time, other customers are hitting the app. Each of these tasks has a different place to run.

| Work | Where it runs | What the customer waits for |
| --- | --- | --- |
| Serve several customers at once | Separate web slots, such as PHP-FPM child processes | A slot and that customer’s own request. |
| Get the two prices for one quote | Overlapping network calls made by a client inside the request | Both results or the request deadlines. |
| Send the receipt | A separate queue worker after the order commits | Checkout and job submission. The receipt can arrive later. |

The number of web slots limits how many PHP-FPM requests are active. An HTTP client can overlap independent waits inside one slot. CPU-heavy PHP work needs a separate execution resource if it must run in parallel. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) · [Web slots and shared capacity](/series/php-misconceptions/chapters/09-scaling-php/) · [Laravel HTTP client concurrency](https://laravel.com/docs/13.x/http-client#concurrent-requests)

## Trace a Fiber

From the checkout root, use the series baseline PHP 8.6. The examples were checked on PHP 8.5.0. PHP introduced Fibers in 8.1. [Fiber example source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/fibers.php) · [PHP Fibers](https://www.php.net/manual/en/language.fibers.php)

```bash
php code/php-misconceptions/fibers.php
```

```text
1. Fiber starts its work
2. Caller runs while the fiber is paused
3. Fiber finishes its work
```

| Call | What runs next |
| --- | --- |
| The caller calls `$fiber->start()` | The Fiber runs up to `Fiber::suspend()` and prints line 1. |
| The Fiber suspends | Control returns to the caller, which prints line 2. |
| The caller calls `$fiber->resume()` | The Fiber continues just after `suspend()` and prints line 3. |

Try the same script with a 200 ms blocking sleep inside the Fiber before it suspends.

```bash
php code/php-misconceptions/fibers.php --blocking
```

`Blocking call starts` and `Blocking call returns` both appear before line 2. The caller gets control only after the sleep returns and the Fiber suspends. An asynchronous library can pair a Fiber with an event loop and non-blocking I/O. The loop can run other ready work while a socket waits, then resume the task when its I/O can progress. [PHP Fibers](https://www.php.net/manual/en/language.fibers.php) · [ReactPHP event loop](https://reactphp.org/event-loop/)

## Compare two supplier calls

Use Bash or Zsh, a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp), a current Composer 2 release, and PHP with cURL, PDO SQLite and Laravel’s other required extensions. cURL lets the client overlap the transfers. The fixture uses a disposable Laravel 13 app, two loopback servers and synthetic prices. [Laravel server requirements](https://laravel.com/docs/13.x/deployment#server-requirements) · [Supplier lab source](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/concurrency)

Open two terminals at the checkout root. Run the first server in one terminal and the second server in the other. Each server handles one supplier so the default single-process development server can simulate an independent service. Keep both running while you make the comparisons.

```bash
php -S 127.0.0.1:8031 -t code/php-misconceptions/concurrency code/php-misconceptions/concurrency/supplier.php
```

```bash
php -S 127.0.0.1:8032 -t code/php-misconceptions/concurrency code/php-misconceptions/concurrency/supplier.php
```

North waits about 350 ms and returns 1,250 cents. South waits about 550 ms and returns 1,850 cents. If a port is occupied, use a free loopback port and match it in the copied `routes/supplier-quote.php` before running the command. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php) · [Supplier stub](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/concurrency/supplier.php)

In a third terminal at the checkout root, set up the demo. The setup stops if a command fails and prints the disposable app path. Change into that directory for the remaining commands.

```bash
(
    set -eu
    fixture="$PWD/code/php-misconceptions/concurrency"
    sandbox="$(mktemp -d)"
    composer create-project laravel/laravel "$sandbox/php-suppliers" '^13.0' --no-interaction
    cd "$sandbox/php-suppliers"
    cp "$fixture/supplier-quote.php" routes/
    printf '\nrequire __DIR__ . "/supplier-quote.php";\n' >> routes/console.php
    pwd
)
```

```bash
php artisan suppliers:quote serial
php artisan suppliers:quote pool
```

Both print `north: 1250 cents`, `south: 1850 cents`, a `Pool elapsed` duration and `Combined quote: 3100 cents`. The console displays the suppliers by name in a fixed order. The arrival order of the responses can vary. [Quote command](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/concurrency/supplier-quote.php)

The command uses `Http::pool()` with `concurrency: 1` in serial mode and `concurrency: 2` in pool mode. The output times the request group with `hrtime()`. This includes starting and waiting for the requests and leaves out Artisan startup before the group. In the controlled lab, expect serial time near the sum of the mock waits and pooled time near the longer wait, plus overhead. Alternate modes and compare several runs on your machine. Production workloads add network, application and supplier limits. [Laravel request pooling](https://laravel.com/docs/13.x/http-client#request-pooling) · [PHP monotonic timer](https://www.php.net/manual/en/function.hrtime.php)

In one loopback run on an Apple M3 Pro with macOS 26.6.2, PHP 8.5.0 with cURL and Laravel 13.43.0, the median was 927 ms serial and 566 ms pooled. Each median uses three trials with the same 350 ms and 550 ms mock waits. Both modes produced the same quote in every trial.

### See how the quote fails

```bash
php artisan suppliers:quote pool --south=error
php artisan suppliers:quote pool --south=slow
```

Each request has a one-second connection timeout and a one-second overall timeout. In the first command, South returns HTTP 503. In the second, its mock response takes about two seconds, so the client reports a connection or timeout failure. Both runs keep the North price, print `Quote incomplete. No combined total.` and exit with status 1. A response with unexpected price data also leaves the combined quote incomplete. [Laravel HTTP timeouts](https://laravel.com/docs/13.x/http-client#timeout)

The timed-out stub can still be sleeping after the client gives up. Give it two seconds to finish before trying the same deadline in serial mode.

```bash
php artisan suppliers:quote serial --south=slow
```

Expect the same incomplete quote. In serial mode North uses about 350 ms before South starts its one-second wait, so the group takes around 1,350 ms plus overhead. In pool mode the group takes around one second plus overhead. Set an end-to-end deadline too when the full response has a time budget. Stop each server with Ctrl+C when done.

Pool independent operations whose results must be ready for the response and set a concurrency limit the suppliers can handle. If the second call needs an ID from the first response, sequence the calls. Each call gets its own deadline, so the serial group can take longer than one call’s timeout. [Laravel HTTP client concurrency](https://laravel.com/docs/13.x/http-client#concurrent-requests)

## Put receipt delivery on a queue

Save the order and a pending receipt record in the same database transaction. After commit, submit the receipt ID to an asynchronous queue serviced by a separate worker. If the application stops after committing and before dispatching, a scheduled sweep can submit the pending receipt later. Have the worker atomically claim the receipt and record that delivery is in progress before contacting the provider. That lets recovery distinguish a receipt awaiting dispatch from an interrupted send. The checkout can return without waiting for the delivery.

Use a unique delivery identity such as the order ID plus the receipt type. Test a worker that sends the receipt and stops before recording success. If the delivery service accepts an idempotency key, confirm that retrying with the same key records the result without sending another receipt. If it offers a delivery lookup, check the first attempt before trying again. If neither is available, mark the outcome for reconciliation and hold automatic resends until it is resolved. [Laravel queue transactions](https://laravel.com/docs/13.x/queues#jobs-and-database-transactions) · [Queue retries and shared capacity](/series/php-misconceptions/chapters/09-scaling-php/)

## Exercise

With the two servers running, compare serial and pooled durations and verify the combined value. Suppose a supplier contract permits just one active request from the entire app. Would setting `concurrency: 1` on each checkout’s pool meet that contract? What can the customer see if the page needs both prices and South times out? Which part can leave the response path?

<details>
<summary>Check your reasoning</summary>

The combined value is 3,100 cents either way. The pooled requests can overlap. A limit of one applies to each pool call. Several checkout requests can still reach the same supplier concurrently. A shared in-flight cap across requests and replicas addresses the one-active-request rule. A requests-per-second limit can still allow overlap. A client timeout may leave work running at the supplier, as it does in the slow stub. Check how the supplier signals completion before allowing a new request when the previous outcome is uncertain.

Keep the North price available for display and mark the combined quote unavailable until you have both results. The receipt can use the queue after an order has committed. Verify a retry uses the same delivery identity even when the first attempt’s outcome was uncertain.

</details>

## Next chapter

[Does PHP Have Serious Developer Tools?](/series/php-misconceptions/chapters/11-developer-tooling/)
