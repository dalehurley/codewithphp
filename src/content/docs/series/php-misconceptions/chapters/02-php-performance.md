---
title: "02: Is PHP Always Slow?"
description: "Trace a slow shipping quote, run the same PHP request with OPcache off and on, and use the timings to choose what to improve."
series: "php-misconceptions"
chapter: 2
estimatedTime: "PT15M"
sidebar:
  order: 2
---

<span id="overview"></span>

## A slow shipping quote

A customer enters their postcode at checkout and clicks **Get shipping quote**. The price comes back correct, but the request takes about 400 milliseconds. A teammate wonders whether OPcache would make the quote appear sooner.

Start by following the quote through PHP. Then run a small version of the handler with OPcache off and on. The comparison will help you decide where to spend time on the real checkout.

<span id="what-happens-on-a-php-request"></span>

## Follow the quote through PHP

Suppose the checkout runs behind a web server with PHP-FPM, which manages a pool of PHP processes. The customer's request goes through three stages.

1. **Wait for a worker.** The web server hands the request to PHP-FPM. If all workers are busy, the request can wait before the PHP script begins. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php)
2. **Load the code and start the app.** PHP compiles uncached scripts into instructions called opcodes. OPcache can reuse those instructions on later requests. A PHP-FPM worker can serve many requests, with ordinary request state reset and the framework typically starting up for each one. [OPcache](https://www.php.net/manual/en/book.opcache.php)
3. **Get the quote.** The app reads the order, asks the carrier for a rate, calculates the total and sends the response. The carrier can take time to answer even when PHP already has the compiled code for the handler.

OPcache changes how PHP loads code. To see that change clearly, we’ll strip the quote down to a short calculation and a simulated carrier wait, then compare requests with the cache off and on.

<span id="observe-one-request-locally"></span>

## Test what OPcache changes

Use PHP 8.5 or later, `curl` and two terminals. In the first terminal, go to the root of the Code with PHP checkout and run `php --version`. The lab asks PHP to sleep for 80 ms to simulate waiting for the carrier. It then adds two fixed prices and returns the total. PHP 8.5 and later have OPcache built in, so the lab can skip local configuration with `-n` and still turn it on. Both commands bind PHP’s development server to loopback. [Code with PHP repository](https://github.com/dalehurley/codewithphp) · [Lab source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/performance/index.php) · [PHP’s development server](https://www.php.net/manual/en/features.commandline.webserver.php) · [OPcache installation](https://www.php.net/manual/en/opcache.installation.php)

### Establish a baseline

Start the server with OPcache disabled. Both runs use the same flags for the CLI, JIT and file-age protection. In the enabled run, `opcache.file_update_protection=0` lets OPcache cache the freshly checked-out fixture immediately. [OPcache settings](https://www.php.net/manual/en/opcache.configuration.php)

```bash
php -n -d opcache.enable=0 -d opcache.enable_cli=1 \
  -d opcache.file_update_protection=0 -d opcache.jit=disable \
  -S 127.0.0.1:8020 -t code/php-misconceptions/performance
```

From a second terminal, send this request twice.

```bash
curl -sS -i --max-time 5 -w '\nclient_total=%{time_total}s\n' http://127.0.0.1:8020/
```

Expect HTTP 200, `{"total_cents":2000}` and `X-Lab-OPcache: off`. In `Server-Timing`, `dependency` is the simulated carrier wait and `pricing` is the addition. Both durations are in milliseconds. Curl reports the whole HTTP exchange in seconds, so a `client_total` of `0.091s` is 91 ms. That total includes work outside the two timings in the script. [Server Timing](https://www.w3.org/TR/server-timing/) · [curl write-out options](https://curl.se/docs/manpage.html#-w)

### Turn OPcache on and repeat the quote

Stop the server with Ctrl+C. Change `opcache.enable` to `1`, restart it and send the same two curl requests from the second terminal.

```bash
php -n -d opcache.enable=1 -d opcache.enable_cli=1 \
  -d opcache.file_update_protection=0 -d opcache.jit=disable \
  -S 127.0.0.1:8020 -t code/php-misconceptions/performance
```

The response should still have the same status and JSON. This time, look for `X-Lab-OPcache: on` and an increase in `X-Lab-OPcache-Hits` on the second request. That counter is cumulative for this server instance. [OPcache status API](https://www.php.net/manual/en/function.opcache-get-status.php)

Here’s the second request from each run on macOS with PHP 8.5.0. The measured wait ran a little longer than the requested 80 ms sleep. Timings are rounded to one decimal place.

| Setting | OPcache hits | Simulated carrier wait | Client total |
| --- | --- | --- | --- |
| Off | Header absent | 90.0 ms | 90.9 ms |
| On | 1 | 90.0 ms | 91.0 ms |

The hit shows that PHP reused compiled code. In both runs, the simulated carrier wait took up most of the exchange. Two requests per setting are enough to see reuse. A latency estimate needs more samples under a repeatable workload. Stop the server with Ctrl+C when you’re done.

## Back at checkout

Suppose a trace of the 400 ms checkout shows 8 ms of app boot, 80 ms of SQL and 290 ms waiting for the carrier. The carrier is the first place to investigate. OPcache can save compilation work. Put an estimate on a faster app boot before making it the priority.

### Exercise

Assume those three intervals are sequential. Predict how much time you’d save by halving app boot or halving the carrier wait. Which change would you investigate first? When could the app finish the quote after sending the response?

<details>
<summary>Check your reasoning</summary>

Halving boot saves about 4 ms. Halving the carrier wait saves about 145 ms. If checkout can show a pending quote, a background job can finish it later. If the customer needs a price before paying, check the carrier’s response time and whether checkout can safely reuse a recent quote. A quote cache needs a freshness rule and a key that includes the inputs that affect the price, such as destination and package weight.

The three intervals add up to 378 ms. That leaves about 22 ms to account for, which could include queue time, another part of the app or the network. Look at the trace before assigning that time to a stage. In a real trace, the total for a parent span may already include the SQL or HTTP spans inside it.

</details>

## Measure an endpoint you own

Use the same sequence on a route you own. Start with a trace, pick the stage that accounts for most of the delay and change one thing. The evidence may point to the worker pool, the database or the PHP runtime.

| What the trace shows | What to investigate |
| --- | --- |
| Most of the time goes into an external HTTP call | Time the dependency separately, including retries. Check the freshness requirement before testing reuse of a recent result. |
| Requests wait before PHP starts | Check `listen queue` and the number of active and idle workers in the private FPM status page. `max children reached` is historical since the pool started. Compare it with the queue during the slowdown. [PHP-FPM status fields](https://www.php.net/manual/en/fpm.status.php) |
| SQL time grows with the number of items in the order | Count queries and see whether the app fetches related records one by one. Compare with a query that fetches the same data together. |
| PHP boot or computation takes much of the time | Check the serving runtime’s effective OPcache and JIT settings through private diagnostics. `php --ini` in your terminal describes the CLI. Choose the next experiment based on whether the time goes into loading code, starting the app or doing the calculation. [OPcache settings](https://www.php.net/manual/en/opcache.configuration.php) |

Record the PHP version and serving mode, dataset, request mix, concurrency, sample size and warm-up period. Repeat the same workload several times before and after your change. Keep cold-start measurements separate from warm requests and check that the route still returns equivalent status and data.

Record p50 and p95 response time, throughput and the number or rate of errors. p50 is the median. p95 is the response time at or below which roughly 95% of the measured requests finish. Throughput is work completed per unit of time, often successful responses per second. Report errors and timeouts explicitly so a faster error response does not look like an improvement.

### If the trace points to PHP work

- **Loading code.** Preloading can make selected functions and classes available from server startup. Measure the startup cost and restart the PHP server process when preloaded definitions change. [PHP preloading](https://www.php.net/manual/en/opcache.preloading.php)
- **Booting the framework.** A persistent application worker can reuse the booted app. Keep request-specific data out of long-lived objects, watch memory and slow responses, and reload workers during deploys. [Laravel Octane lifecycle](https://laravel.com/docs/13.x/octane)
- **Running a calculation.** Profile before testing JIT, which can compile eligible PHP code to machine instructions. `opcache.jit` defaults to `disable` from PHP 8.4. [PHP 8.4 JIT changes](https://www.php.net/manual/en/migration84.incompatible.php)

## Next chapter

The next lab applies the same method to Laravel. You’ll compare the same responses after changing how the app boots or how it fetches related data. [How Fast Can Laravel Be?](/series/php-misconceptions/chapters/03-laravel-performance/)
