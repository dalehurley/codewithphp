---
title: "02: Is PHP Always Slow?"
description: "Follow a PHP request, observe OPcache reuse in a local lab, and use timings to choose what to optimise."
series: "php-misconceptions"
chapter: 2
estimatedTime: "PT15M"
sidebar:
  order: 2
---

## Overview

PHP can serve a request quickly. When a request takes 400 milliseconds, the next question is where that time went. It may have waited for a free worker, loaded 40 related records one at a time, or waited for an external service. Find where the request spends its time before choosing a change to test.

## What happens on a PHP request

Consider a shipping quote handled by a web server and PHP-FPM.

1. **The request reaches a worker.** The web server sends the request to PHP-FPM, which manages a pool of PHP processes. If the pool is busy, the request may queue before the PHP script starts. During a slowdown, check the FPM status page for `listen queue`, active and idle processes, and `max children reached`. The last field is historical since the pool started, so compare it with the queue during the affected period. Keep the status endpoint private. [PHP-FPM status fields](https://www.php.net/manual/en/fpm.status.php)
2. **PHP loads code and the app starts.** On a cache miss, PHP parses and compiles the needed script into opcodes. OPcache can store and reuse those compiled opcodes on later requests. A PHP-FPM process can serve many requests, with ordinary request state reset and the framework typically booting for each request. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) · [OPcache](https://www.php.net/manual/en/book.opcache.php)
3. **The app handles the request.** The handler reads the order, runs pricing logic and waits for the carrier if it needs a fresh quote. It then produces the response. Reusing compiled code leaves the handler's I/O and calculations to run.

There are several ways to remove work from that path.

| Technique | What it changes | What to check |
| --- | --- | --- |
| Preloading | The PHP server loads selected functions and classes at startup so they are available to requests. | Measure startup and request behaviour. Restart the PHP server process that loaded the code when its preloaded definitions change. |
| JIT | PHP can compile eligible code to machine instructions. | If profiling shows significant CPU time in PHP code, compare the same workload with JIT enabled and disabled. Pin the setting during other experiments. From PHP 8.4, `opcache.jit` defaults to `disable`. |
| Application data cache | A repeat request can reuse a quote until it expires. | Include inputs that affect the quote in the cache key, such as destination and package weight. Measure hit rate and latency, and confirm that reuse meets freshness requirements. |
| Persistent application workers | A worker can reuse the booted application across requests. | Watch memory and tail latency. Keep request-specific data out of long-lived objects and reload workers during deploys. |

[PHP preloading](https://www.php.net/manual/en/opcache.preloading.php) · [PHP 8.4 JIT changes](https://www.php.net/manual/en/migration84.incompatible.php) · [Laravel Octane lifecycle](https://laravel.com/docs/13.x/octane)

Check the runtime that handles the measured requests. A terminal command such as `php --ini` reports the CLI configuration. On a deployed app, use private diagnostics in the serving runtime to confirm the effective OPcache and JIT settings. [OPcache settings](https://www.php.net/manual/en/opcache.configuration.php) · [OPcache status API](https://www.php.net/manual/en/function.opcache-get-status.php)

## Observe one request locally

Use PHP 8.5 or later, `curl`, and a terminal at the root of the [Code with PHP checkout](https://github.com/dalehurley/codewithphp). Check the runtime with `php --version`. The [lab script](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/performance/index.php) sleeps for about 80 ms to simulate waiting for another service, then adds two prices. It exposes timing and cache counters on a loopback server. [PHP's development server](https://www.php.net/manual/en/features.commandline.webserver.php) · [OPcache installation](https://www.php.net/manual/en/opcache.installation.php)

Start with OPcache disabled. The `-n` option skips local configuration files. The other flags hold the CLI and JIT settings constant. In the enabled run, `opcache.file_update_protection=0` lets OPcache cache a freshly checked-out script right away. [OPcache settings](https://www.php.net/manual/en/opcache.configuration.php)

```bash
php -n -d opcache.enable=0 -d opcache.enable_cli=1 \
  -d opcache.file_update_protection=0 -d opcache.jit=disable \
  -S 127.0.0.1:8020 -t code/php-misconceptions/performance
```

From a second terminal, send this request twice.

```bash
curl -sS -i --max-time 5 -w '\nclient_total=%{time_total}s\n' http://127.0.0.1:8020/
```

Expect HTTP 200, a body of `{"total_cents":2000}`, and `X-Lab-OPcache: off`. The `Server-Timing` header reports the simulated dependency wait and the pricing calculation separately in milliseconds. Multiply curl’s `client_total` by 1,000 to compare it with those timings. The client’s total covers the entire HTTP exchange, including work outside the two timed parts of the script. [Server Timing](https://www.w3.org/TR/server-timing/) · [curl write-out options](https://curl.se/docs/manpage.html#-w)

Use Ctrl+C in the server terminal. Start the same server with OPcache enabled, then repeat the two requests.

```bash
php -n -d opcache.enable=1 -d opcache.enable_cli=1 \
  -d opcache.file_update_protection=0 -d opcache.jit=disable \
  -S 127.0.0.1:8020 -t code/php-misconceptions/performance
```

In the enabled run, look for `X-Lab-OPcache: on` and an increase in `X-Lab-OPcache-Hits` as you repeat requests. The counter is cumulative for the server instance. The status and body stay the same, and the dependency wait still accounts for most of the measured time. Two requests per mode let you observe cache reuse. To estimate a latency effect, collect more samples under a repeatable workload. Stop the development server with Ctrl+C when finished. [OPcache status API](https://www.php.net/manual/en/function.opcache-get-status.php)

## Exercise

Imagine one request measures 8 ms in application boot, 80 ms in SQL and 290 ms waiting for a shipping quote. Assume the three intervals are sequential and do not overlap. Predict the savings if you halve boot time or halve the wait for shipping. When could the quote be generated after the response?

<details>
<summary>Check your reasoning</summary>

Halving boot saves about 4 ms. Halving the shipping wait saves about 145 ms. If the response can genuinely succeed with a pending quote, a background job can finish it later. If checkout must display a fresh quote before the customer can pay, look at the dependency itself or a cache with a freshness rule that preserves that requirement.

In a real trace, a parent span may already include the SQL or HTTP spans inside it. Look at the timeline before adding durations. Client latency can also include time before the PHP script starts, such as time spent waiting for a worker.

</details>

### Measure an endpoint you own

Pick a representative route and write down the PHP version and serving mode, dataset, request mix, concurrency, sample size and warm-up. Establish a baseline, change one thing and repeat the same workload several times. Keep cold-start measurements separate from warm requests and check that status and response data remain equivalent.

Record p50 and p95 response time, throughput and the number or rate of errors. p50 is the median. p95 is the response time at or below which roughly 95% of the measured requests finish. Throughput is work completed per unit of time, often successful responses per second. Report errors and timeouts explicitly so a faster error response does not look like an improvement.

## Next chapter

The next lab compares the same Laravel responses under different runtime and database query strategies. [How Fast Can Laravel Be?](/series/php-misconceptions/chapters/03-laravel-performance/)
