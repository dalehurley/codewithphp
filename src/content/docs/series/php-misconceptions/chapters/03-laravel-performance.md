---
title: "03: How Fast Can Laravel Be?"
description: "Benchmark the same Laravel app with FrankenPHP and Octane, then compare a per-row database lookup with a join."
series: "php-misconceptions"
chapter: 3
estimatedTime: "PT25M"
sidebar:
  order: 3
---

## Overview

Laravel already gives you several ways to reduce work inside your application. Cache production configuration and routes, inspect database queries and move work out of the response when it can finish later. Octane adds another option. With FrankenPHP in worker mode, it boots the application once per worker and keeps it in memory between requests. This saves a different kind of work from OPcache, which reuses compiled bytecode. [Laravel deployment](https://laravel.com/docs/13.x/deployment) · [Laravel Octane](https://laravel.com/docs/13.x/octane) · [FrankenPHP workers](https://frankenphp.dev/docs/worker/)

## Set up the lab

Use PHP 8.4 or later with PDO SQLite, Composer 2, curl and ApacheBench (`ab`). The setup below uses Linux or Apple Silicon macOS. Confirm your tools with `php --version`, `composer --version` and `ab -V`. Run the commands from the Code with PHP checkout. They create a separate, disposable Laravel app using the [benchmark fixtures](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel).

```bash
fixture="$PWD/code/php-misconceptions/laravel"
sandbox="$(mktemp -d)"
composer create-project laravel/laravel "$sandbox/php-speed" '^13.0' --no-interaction
cd "$sandbox/php-speed"

composer require laravel/octane '^2.0' --no-interaction
php artisan octane:install --server=frankenphp --no-interaction
cp "$fixture/routes/benchmark.php" routes/web.php
cp "$fixture/database/seeders/BenchmarkSeeder.php" database/seeders/
cp "$fixture/tests/Feature/BenchmarkTest.php" tests/Feature/
cp "$fixture/Caddyfile.benchmark" "$fixture/run-benchmark.sh" .

php artisan test --filter=BenchmarkTest
APP_ENV=production APP_DEBUG=false SESSION_DRIVER=array CACHE_STORE=array php artisan db:seed --class=BenchmarkSeeder --force
APP_ENV=production APP_DEBUG=false SESSION_DRIVER=array CACHE_STORE=array php artisan optimize
```

The test checks that the two order endpoints return exactly the same 40 records in the same order. The per-row endpoint runs one query to select orders and 40 more to retrieve customer names. The join endpoint returns the data in one query. This lab uses Laravel’s query builder. In an Eloquent app, eager loading is also an option when a loop reads a relationship on each model. [Eloquent eager loading](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading)

To use the recorded dependency versions, run the setup with PHP 8.5 and use `13.11.0` as the skeleton version in `create-project`. Replace the `composer require` step with the commands below, then continue at `octane:install`. The bundled PHP version comes from the FrankenPHP binary. Record its version as well when comparing timings. [Recorded dependency files](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel/results/dependencies)

```bash
cp "$fixture/results/dependencies/composer.json" composer.json
cp "$fixture/results/dependencies/composer.lock.json" composer.lock
composer install --no-interaction
```

## Measure one change at a time

Start the local server with OPcache enabled and JIT disabled. The bundled Caddyfile exposes classic mode on port 8010 and Octane worker mode on 8011. Both use the same FrankenPHP binary, route code, cached Laravel configuration and SQLite data. Each mode has four PHP threads or workers available for its requests. The ports are bound to loopback for the lab.

```bash
BENCH_OPCACHE=1 php artisan octane:start --server=frankenphp --host=127.0.0.1 --port=8011 --admin-port=2026 --workers=4 --max-requests=10000 --caddyfile=Caddyfile.benchmark
```

From a second terminal in the demo app, run the benchmark. The first argument is the OPcache state you expect. Before sending load, the script requires both ports to report the same FrankenPHP PHP version, the intended entry points and OPcache in the requested state. JIT must be inactive, and its mode must be `disable` on builds that provide it. The script checks ping and compares the order response bodies byte for byte.

```bash
results="$(mktemp -d)"
bash run-benchmark.sh 1 "$results/opcache-on"
./frankenphp version >> "$results/opcache-on/environment.txt"
```

Each of the three trials warms each endpoint with 200 requests, then measures 2,500 requests with up to four in flight. ApacheBench opens a fresh connection per request, and the runner rejects incomplete, failed and non-2xx responses. Trial two puts worker mode first. The output directory contains the environment details, dependency files, unedited ApacheBench reports and a summary table. [ApacheBench options](https://httpd.apache.org/docs/2.4/programs/ab.html)

If Octane found an installed FrankenPHP binary on your `PATH`, use `frankenphp version` for the version commands.

Use Ctrl+C in the server terminal, then restart the server with `BENCH_OPCACHE=0`. In the second terminal, run the second measurement. Classic mode with OPcache disabled can take several minutes.

```bash
bash run-benchmark.sh 0 "$results/opcache-off"
./frankenphp version >> "$results/opcache-off/environment.txt"
```

If a port is already in use, check 8010, 8011 and the admin port 2026 before starting. If the seed runs but the HTTP endpoints report a missing SQLite driver, check the PHP build bundled with FrankenPHP as well as the CLI that ran Artisan. If the runtime checks fail, confirm that the expected OPcache argument matches the server’s environment and that Octane loaded `Caddyfile.benchmark`.

## Local results

On an Apple M3 Pro running macOS 26.6.2, this lab used Laravel 13.43.0, Octane 2.25.0 and FrankenPHP 1.13.6 with PHP 8.5.13. The table shows the median throughput and the median of the three per-run p95 values with OPcache enabled. ApacheBench reports these percentiles at whole-millisecond resolution.

| Endpoint | Classic requests/s | Worker requests/s | Classic p95 | Worker p95 |
| --- | ---: | ---: | ---: | ---: |
| Ping | 981 | 2,946 | 8 ms | 3 ms |
| Orders with per-row lookups | 525 | 1,116 | 16 ms | 6 ms |
| Orders with one join | 648 | 2,055 | 15 ms | 4 ms |

With OPcache on, the worker handled about three times as many ping requests per second. The join raised median throughput in both serving modes. With OPcache off, classic ping throughput was 49 requests per second and median p95 was 143 ms. The warm worker still handled about 4,039 ping requests per second with its application already booted. The two OPcache settings ran in separate server sessions, so compare the broad pattern and keep the variation between trials in view. [OPcache-on summary](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/results/opcache-on.tsv) · [OPcache-off summary](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/results/opcache-off.tsv) · [Original reports and environment details](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel/results)

Ping uses no database queries. The order comparison between serving modes also reflects connection handling, since Octane can reuse its SQLite connection across requests. Compare the per-row lookup with the join within each mode to see the effect of changing the query shape.

These were warm runs with a small dataset, loopback HTTP, no TLS and no remote database or application data cache. With OPcache on, classic ping throughput ranged from 648 to 1,059 requests per second across the three trials. Treat the figures as measurements at concurrency four on this machine and use the same procedure for your own endpoint.

## Run workers in production

Long-lived workers keep application objects in memory. Keep user- and request-specific data out of static properties and long-lived singletons, monitor memory and reload workers during deploys so they pick up new code. Laravel Cloud offers a managed Octane runtime built on FrankenPHP. With Octane installed in your application, the app cluster’s **Use Octane as runtime** setting enables it on deployment. [Octane worker lifecycle and caveats](https://laravel.com/docs/13.x/octane) · [Laravel Cloud Octane configuration](https://cloud.laravel.com/docs/compute#laravel-octane)

## Exercise

Pick one endpoint in a demo app and count its database queries. Change one source of repeated work, repeat the benchmark and check that the body and status stay the same. Once you have a warm baseline on your current server, trial Octane as its own change.

## Next chapter

[Does PHP Have to Be Messy?](/series/php-misconceptions/chapters/04-clean-laravel-code/)
