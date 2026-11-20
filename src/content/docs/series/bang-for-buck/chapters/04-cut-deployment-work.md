---
title: "04: Cut Avoidable Work on Every Deploy"
description: "Measure Laravel’s config, route, event and Blade caches alongside OPcache on cold and warm PHP-FPM requests."
series: "bang-for-buck"
chapter: 4
difficulty: "Intermediate"
estimatedTime: "PT30M"
sidebar:
  order: 4
---

## Overview

Some work can happen once per deployment. Laravel can combine configuration, prepare the router, discover event listeners and compile Blade ahead of the first request. PHP’s OPcache can then reuse the compiled PHP bytecode, including bytecode for those generated files. The shop still reads products, dispatches its event and renders HTML for each request.

You’ll change one cache at a time and run the same catalogue preview. Then you’ll hold all four Laravel caches fixed and switch OPcache off to see what it contributes.

## Prerequisites

Use the running Compose shop and the k6 image from [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/). Work in `code/bang-for-buck` and allow about 30 minutes for the hands-on comparison. Run one profile at a time on the local lab.

## Quick Start

Rebuild the shared shop to get the HTML preview. The preview reads the first 20 seeded products, gets its heading from an auto-discovered listener and renders a Blade template. The ordinary JSON catalogue and mixed workload are still available.

```bash
docker compose build php
docker compose up -d --wait --force-recreate php web worker shipping

bash bench/cache-mode.sh baseline
WORKLOAD=preview bash bench/run.sh cold
WORKLOAD=preview RATE=20 DURATION_SECONDS=10 bash bench/run.sh steady
```

Look for two successful requests in the cold profile and roughly 200 in the steady profile. k6 checks that the event supplies the heading and that all 20 products appear in the expected order with the expected prices.

## Step 1 – See the work each cache moves

| Command or setting | Work it saves | When to look for a difference |
| --- | --- | --- |
| `php artisan config:cache` | Combines configuration into a generated PHP file. Laravel can load that file in place of loading and merging the individual configuration files and reading `.env`. | On application boot for both cold and warm PHP-FPM requests. |
| `php artisan route:cache` | Builds a cached route collection from the release’s route definitions. Laravel can load the prepared collection during boot. | On both kinds of request, especially when an app registers many routes. The matched handler still runs. |
| `php artisan event:cache` | Writes the event-to-listener discovery manifest. Laravel can load the mappings without repeating listener discovery. | On both kinds of request where the app would otherwise discover listeners during boot. Dispatch still calls the listener. |
| `php artisan view:cache` | Compiles Blade templates into PHP files ahead of time. | On the first render of a template without a current compiled file. Later requests can already reuse the compiled file in either mode. Rendering still uses the current request’s data. |
| `opcache.enable=1` | Stores compiled PHP bytecode in shared memory so requests can reuse it. | The first request after a fresh PHP-FPM start populates the cache. Warm requests benefit from the reused bytecode. |

Laravel’s `optimize` command runs the four Laravel cache commands for this shop. OPcache is configured in PHP. Use the HTTP lab response to check the setting on PHP-FPM, since this lab disables OPcache for the CLI. [Laravel deployment optimisation](https://laravel.com/docs/13.x/deployment#optimization) · [Laravel event discovery](https://laravel.com/docs/13.x/events#event-discovery) · [PHP OPcache](https://www.php.net/manual/en/intro.opcache.php)

## Step 2 – Compare the same responses

`cache-mode.sh` recreates the lab’s PHP and nginx containers so the previous mode’s generated files and opcodes are gone. It then runs the selected Artisan command inside the PHP container with the lab’s runtime environment. The database, exports and queue worker stay up. Run each condition on the same hardware, at the same offered rate.

```bash
for mode in baseline config routes events views all all-no-opcache; do
    printf '\n%s\n' "$mode"
    bash bench/cache-mode.sh "$mode" || break
    WORKLOAD=preview bash bench/run.sh cold || break
    WORKLOAD=preview RATE=20 DURATION_SECONDS=10 bash bench/run.sh steady || break
done

bash bench/cache-mode.sh baseline
```

The `cold` profile restarts PHP-FPM after cache preparation and checks the socket without making a Laravel request. It measures the first and second HTTP responses against the same endpoint. Laravel’s generated disk files survive that restart. The measurement starts after PHP-FPM is listening, and PostgreSQL, nginx and the host remain running. The steady profile then samples warm requests. k6 measures HTTP send, wait and receive time separately from initial connection setup. [k6 HTTP timing metrics](https://grafana.com/docs/k6/latest/using-k6/metrics/reference/)

Open `laravel-caches-before.json` in each cold run to see what existed on disk **before** the first HTTP request. Compare it with `runtime.json`, captured from PHP-FPM **after** the two samples. With `baseline`, Blade has compiled the preview by the time you fetch the runtime. With `views`, the compiler already had an up-to-date file before the first request. In `all-no-opcache`, the four Laravel caches are ready before the first request, and the HTTP runtime reports OPcache disabled with zero cached scripts. When OPcache is on, the script count reflects PHP’s shared cache at the time the runtime report is fetched. [PHP OPcache status](https://www.php.net/manual/en/function.opcache-get-status.php)

## Local measurements

These are medians from three trials per mode on an Apple M3 Pro with a two-vCPU Docker VM, four PHP-FPM workers and a one-CPU limit for PHP. The warm profile offered 20 requests per second for six seconds per trial. Each trial completed 120 or 121 warm requests with zero drops. All modes returned identical HTML. [Measurements by trial](https://github.com/dalehurley/codewithphp/blob/main/code/bang-for-buck/bench/chapter-04-measurements.tsv)

| Prepared Laravel caches | OPcache | First request | Second request | Warm p95 median and range |
| --- | --- | ---: | ---: | ---: |
| None | On | 114.0 ms | 9.8 ms | 12.6 ms (12.2–13.2) |
| Config | On | 110.8 ms | 9.8 ms | 13.9 ms (13.1–14.6) |
| Routes | On | 127.8 ms | 9.6 ms | 12.8 ms (12.5–13.2) |
| Events | On | 110.4 ms | 11.2 ms | 13.1 ms (12.6–14.2) |
| Views | On | 116.3 ms | 9.2 ms | 12.4 ms (12.3–13.9) |
| All four | On | 103.0 ms | 8.7 ms | 12.8 ms (12.6–29.1) |
| All four | Off | 52.8 ms | 49.8 ms | 59.9 ms (48.5–155.7) |

The shop has few routes, one discovered listener and a small view. The warm latency ranges for the individual Laravel caches overlap the baseline. The file checks still show what moved into cache preparation, including Blade compilation before the first request.

The last two rows hold Laravel’s caches fixed. The OPcache-enabled pool had work to do on the first request as it populated shared memory. Warm p95 was much lower once the bytecode could be reused. Use separate measurements for requests arriving after a restart and requests served under sustained traffic.

## Step 3 – Build and refresh caches at release time

Build config cache with the environment intended for the new release. Read environment values in config files and access them through `config()` in application code. The config cache captures the resolved values when it is built. In the lab you can see the difference by changing the environment for an individual Artisan process.

```bash
bash bench/cache-mode.sh baseline
docker compose exec -T -e APP_NAME='Preview release' php php artisan about --only=environment

bash bench/cache-mode.sh config
docker compose exec -T -e APP_NAME='Preview release' php php artisan about --only=environment

bash bench/cache-mode.sh baseline
```

The uncached probe reports `Preview release` as the application name. The cached probe reports `Bang for Buck`, the value when the cache was built. Rebuild the cache whenever release configuration changes. [Laravel configuration caching](https://laravel.com/docs/13.x/configuration#configuration-caching)

For a release, prepare the generated Laravel files before serving traffic and start new PHP processes against those files. In this lab, OPcache timestamp validation is disabled because the image contains the release’s source. Recreate the processes when the files change. Restart long-lived queue workers as part of the release as well so they load the new code and configuration. This lab uses an array cache, so use Compose to restart its worker. Laravel’s `queue:restart` relies on a cache signal the running workers can read. [PHP timestamp validation](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.validate-timestamps) · [Laravel queue workers and deployment](https://laravel.com/docs/13.x/queues#queue-workers-and-deployment)

For a narrow rollback, rebuild the previous release’s artifacts or clear the particular generated files with `config:clear`, `route:clear`, `event:clear` or `view:clear`. Laravel’s `optimize:clear` also clears keys in the default application cache store. Include that effect in your rollback decision. [Laravel deployment optimisation](https://laravel.com/docs/13.x/deployment#optimization)

## Exercise

Choose two modes and alternate their order over three trials. Compare the first response, the second response and the range of warm p95 readings. Check the pre-run cache state, successful response count and dropped iterations each time. If the latency ranges overlap, record the work the cache avoids and collect more samples before claiming a speed difference.

Leave the shop in `baseline` mode to use the same starting state for the next lab.

## Next chapter

[Ask the Database for Less](/series/bang-for-buck/chapters/05-ask-the-database-for-less/)
