---
title: "02: Is PHP Always Slow?"
description: "Understand where PHP-FPM, OPcache, preloading and JIT affect a web request, then identify the work your app spends time on."
series: "php-misconceptions"
chapter: 2
estimatedTime: "PT10M"
sidebar:
  order: 2
---

## Overview

When a PHP request takes 400 milliseconds, some of that time might be in PHP. It could also be waiting for a database connection, loading 40 related records one at a time, or calling an external service. The word *interpreted* cannot tell you which part to fix.

## What happens on a PHP request

| Layer | Work it changes | What to measure |
| --- | --- | --- |
| PHP-FPM | Manages a pool of PHP worker processes. A process can serve many requests, with ordinary application state reset between requests. | Busy workers, queued requests and memory per worker |
| OPcache | Stores compiled PHP bytecode in shared memory. Warm requests can reuse it and skip recompiling those scripts. | Cache status, warm latency and memory |
| Preloading | Loads selected code into shared memory at server startup. Changes to preloaded code require a restart. | Startup cost, warm latency and deploy behaviour |
| JIT | Optionally compiles eligible code to machine instructions. CPU-heavy work is a better candidate for an experiment than an endpoint dominated by I/O. | CPU time and endpoint latency with JIT enabled and disabled |
| Application data cache | Reuses data such as query results or computed prices. | Cache hit rate and time saved on a hit |
| Persistent application workers | Keep a booted application in memory across requests. | Boot time, worker memory and tail latency |

Each layer saves a specific kind of work. A warm opcode cache still leaves the request’s database and network calls to do. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) · [OPcache](https://www.php.net/manual/en/intro.opcache.php) · [Preloading](https://www.php.net/manual/en/opcache.preloading.php) · [JIT and OPcache settings](https://www.php.net/manual/en/opcache.configuration.php)

## Exercise

Imagine your profiler shows 8 ms in application boot, 80 ms in SQL and 290 ms waiting for a shipping quote, all on the same request. Start by figuring out whether the shipping quote can be cached within its freshness requirements, or deferred if the response can succeed without it. If you cut boot time in half, you save about 4 ms on that request. If you remove half of the shipping wait, you save about 145 ms.

For your own app, choose a representative endpoint and record p50, p95, throughput and errors. p95 is the time within which 95% of requests completed. Record the workload and what warmed up first, so the next result has a comparable baseline. The benchmark in the next chapter also checks that both versions deliver the same response.

## Next chapter

[How Fast Can Laravel Be?](/series/php-misconceptions/chapters/03-laravel-performance/)
