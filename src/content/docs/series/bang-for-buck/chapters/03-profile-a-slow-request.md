---
title: "03: Profile a Slow Laravel Request"
description: "Trace one checkout through PHP, SQL and the shipping service, then use the load harness to reproduce the bottleneck."
series: "bang-for-buck"
chapter: 3
difficulty: "Intermediate"
estimatedTime: "PT25M"
sidebar:
  order: 3
---

## Overview

A slow order could be waiting for a shipping quote, running database queries, or spending CPU time in application code. Start with one correct response and break down where the time went. Then repeat the workload from the previous chapter with one controlled change to see whether it moves the same part of the request.

## Prerequisites

Use the running Docker Compose shop from [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/). Open a terminal in `code/bang-for-buck`. Budget about 25 minutes for the traces and controlled load run. The optional Xdebug profile needs time for a separate image build.

## Quick Start

Rebuild the lab image and apply it to the running stack. Warm the normal request paths with a smoke run. Then trace a fresh order.

```bash
docker compose build php
docker compose up -d --wait --force-recreate php web worker shipping
curl -fsS --retry 5 --retry-all-errors --retry-delay 1 "http://127.0.0.1:${SHOP_PORT:-8088}/api/lab"

bash bench/run.sh smoke
bash bench/trace.sh order
```

Look for HTTP 201 and `total_cents: 1523` in the JSON response. The script uses a fresh reference on every run. A replay of an existing order returns the saved result and skips the shipping call. The request opts in with `X-Shop-Trace: 1`, and the lab returns its measurements in the `Server-Timing` response header. [Server-Timing specification](https://www.w3.org/TR/server-timing/)

## Step 1 – Read the request in layers

The durations in `Server-Timing` are in milliseconds. The two figures printed by curl are in seconds.

| Reading | What it measures |
| --- | --- |
| `app` | Elapsed time inside the route middleware, including validation, the controller and creation of the response |
| `sql` | The sum of Laravel’s query event durations, with the number of queries observed during this request |
| `shipping` | Elapsed time around the outbound Laravel HTTP call, including client and network time while waiting for the quote |
| `other` | The elapsed time left inside the middleware after subtracting recorded SQL and shipping time |
| `cpu` | User and system CPU time used by the PHP-FPM process during the trace |
| Client time to first byte | Time from curl starting the transfer to receiving the first response byte |

The middleware uses PHP’s monotonic `hrtime()` for elapsed time. Laravel fires a `QueryExecuted` event for each recorded query. Its reported time is measured by the application’s database connection and can include driver and network wait. The collector adds durations and counts for the current request. [PHP hrtime](https://www.php.net/manual/en/function.hrtime.php) · [Laravel query events](https://laravel.com/docs/13.x/database#listening-for-query-events)

Treat `other` as unclassified elapsed time. It may include PHP execution, other waits or time when the operating system scheduled another process. `cpu` gives you a separate signal and overlaps the other readings. For example, the PHP database and HTTP clients use CPU inside their timed calls. Compare the client’s first byte with `app` to spot time outside the middleware, such as connection setup, time waiting for a PHP worker or framework boot. Check `fpm.tsv` from the load run before attributing the difference to a queue. [PHP getrusage](https://www.php.net/manual/en/function.getrusage.php) · [curl timing variables](https://curl.se/docs/manpage.html#-w)

Trace a catalogue request as a comparison.

```bash
bash bench/trace.sh catalogue
```

The trace command uses the original lazy catalogue variant. It skips shipping and the seeded first page records 22 queries. The route checks the category, reads the page, and then lazily loads a category for each returned product. In one local trace the database calls took 9.26 ms of the 12.22 ms measured inside the middleware. That makes the query pattern a good next experiment. Compare query count and cumulative time before changing the relationship loading. [Laravel eager loading](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading)

## Step 2 – Change only shipping and replay the load

Keep the standard stack and run a short control. Use the same host conditions for both runs. A concurrent build or test suite can change the timing of every route. Then add 250 ms to the simulated shipping service and rerun the same profile. Once you have the second trace, restore the shipping delay.

```bash
RATE=20 DURATION_SECONDS=15 bash bench/run.sh steady
bash bench/trace.sh order

SHOP_SHIPPING_EXTRA_MS=250 docker compose up -d --no-deps shipping
RATE=20 DURATION_SECONDS=15 bash bench/run.sh steady
bash bench/trace.sh order

SHOP_SHIPPING_EXTRA_MS=0 docker compose up -d --no-deps shipping
```

In each run’s `summary.json`, compare `shop_order_ms` and `shop_catalogue_ms` at p95. Check that the harness started comparable work, that workflows were correct, and whether any arrivals were dropped. The harness records the shipping delay setting in `environment.txt` and runs without the trace header. Keep the paired order traces alongside the summaries.

In one pair of local runs, all 300 workflows completed in each run with zero drops. Both order traces returned HTTP 201 and the same 1,523-cent total.

| Reading | Normal shipping | With 250 ms added |
| --- | ---: | ---: |
| Order p95 under load | 54.97 ms | 304.24 ms |
| Catalogue p95 under load | 19.42 ms | 19.94 ms |
| Shipping in one order trace | 20.99 ms | 275.11 ms |
| SQL in that order trace | 6.37 ms across 4 queries | 9.67 ms across 4 queries |
| PHP process CPU in that order trace | 5.66 ms | 5.49 ms |

The order’s shipping span should account for most of the added latency. SQL query count and the order’s response values should agree across the two traces. Catalogue requests avoid the shipping service, so they make a useful comparison. Under enough load, the catalogue can still slow down because it shares PHP-FPM workers with orders waiting on shipping. Compare the FPM listen queue and active processes before treating a catalogue slowdown as a separate database issue.

## Step 3 – Follow the strongest signal

| Signal | Next experiment |
| --- | --- |
| SQL accounts for most elapsed time or the query count grows with the page size | Check for repeated relationships, then compare query counts and plans with matching outputs |
| The shipping span grows with order latency while SQL stays similar | Check the upstream latency and errors, then test a timeout, concurrency limit or a different dependency strategy |
| CPU is high and the SQL and shipping spans explain little of the request | Profile a single representative request and inspect which functions use time or run repeatedly |
| Client time rises while the measured PHP span stays similar | Inspect FPM’s listen queue, proxy timings and framework startup |

Use [Laravel Pulse](https://laravel.com/docs/13.x/pulse) when you need trends across requests. Its slow-query and outgoing-request cards help you see which operations recur. For cumulative SQL time within one request, use [Laravel’s query-time threshold hook](https://laravel.com/docs/13.x/database#monitoring-cumulative-query-time).

## Practice – Inspect PHP with a triggered profile

Use the optional Compose override to build an Xdebug image for PHP-FPM. The shipping service, worker and database keep their existing configuration. Trigger one catalogue request and copy the Cachegrind file into the local results directory.

```bash
docker compose -f compose.yaml -f compose.profile.yaml up -d --build --force-recreate --no-deps php web
curl -fsS --retry 5 --retry-all-errors --retry-delay 1 "http://127.0.0.1:${SHOP_PORT:-8088}/api/lab"
XDEBUG_TRIGGER=shop-profile bash bench/trace.sh catalogue

mkdir -p results
profile_dir=$(mktemp -d "$PWD/results/xdebug-XXXXXX")
docker compose cp php:/tmp/shop-profiles/. "$profile_dir"
ls "$profile_dir"

docker compose up -d --force-recreate --no-deps php web
```

Open the `cachegrind.out.*` file in a viewer such as QCachegrind or PhpStorm. Start with self time to find work a function performs directly. Use inclusive time and call counts to follow nested work and identify repeated calls. Check the trace before interpreting a large inclusive time in a database wrapper. It may be elapsed time spent waiting. Use the restored standard image for benchmark comparisons because profiler overhead changes the measurements. [Xdebug profiling and triggers](https://xdebug.org/docs/profiler)

## Exercise

Pick the order or catalogue and write down the largest observed component, the evidence for it and one change you would test. Name a user-facing metric and a correctness check for that change. If you only have one trace, take several warm samples before ranking your options.

## Next chapter

[Cut Avoidable Work on Every Deploy](/series/bang-for-buck/chapters/04-cut-deployment-work/)
