---
title: "03: How Fast Can Laravel Be?"
description: "Run a short or full Laravel benchmark, verify both serving modes, and measure the effect of replacing repeated queries with a join."
series: "php-misconceptions"
chapter: 3
estimatedTime: "PT25M"
sidebar:
  order: 3
---

## Overview

Two changes can make the same Laravel app faster. It can do less work per request, or it can reuse work it has already done. This lab compares both ideas using the same routes and data. Compare the two order routes within one serving mode to see what changes when you replace repeated queries. Compare the same route across both modes to see how it responds to the different request lifecycles.

In classic FrankenPHP mode, Laravel boots for each request. In Octane worker mode, each worker keeps a booted application in memory. OPcache saves script compilation work in either mode. Both sites in this lab use FrankenPHP. [Laravel deployment](https://laravel.com/docs/13.x/deployment) · [Laravel Octane](https://laravel.com/docs/13.x/octane) · [FrankenPHP workers](https://frankenphp.dev/docs/worker/)

| Route | What it does | What you can compare |
| --- | --- | --- |
| `/_benchmark/ping` | Returns the same small JSON response with no database query. | Compare classic and worker mode without database work in the handler. |
| `/_benchmark/orders/per-row` | Loads 40 orders, then looks up a customer for each order. | Use it as the baseline for the order workload in a given mode. |
| `/_benchmark/orders/join` | Returns the same 40 orders and customer names from one joined query. | Compare it with the per-row route in the same mode. |

## Set up the lab

Use Bash on Linux or Apple Silicon macOS, PHP 8.5 or later with PDO SQLite and Laravel’s required extensions, Composer 2.2 or later, curl and ApacheBench (`ab`). On a minimal Linux install, check for `mbstring`, DOM, XML and XMLWriter as well. Run `php --version`, `php --ri pdo_sqlite`, `composer --version` and `ab -V` to check the tools. The CLI runs Composer, Artisan and the feature test. FrankenPHP has its own PHP runtime for serving HTTP. You will check that runtime before taking measurements. [Laravel PHP requirements](https://laravel.com/docs/13.x/deployment#server-requirements)

Run the following from the root of the [Code with PHP checkout](https://github.com/dalehurley/codewithphp). It creates a disposable Laravel app and uses the [recorded Composer dependencies](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel/results/dependencies).

```bash
fixture="$PWD/code/php-misconceptions/laravel"
sandbox="$(mktemp -d)"
composer create-project laravel/laravel "$sandbox/php-speed" 13.11.0 \
  --no-install --no-scripts --no-interaction --prefer-dist
cd "$sandbox/php-speed"
pwd

cp "$fixture/results/dependencies/composer.json" composer.json
cp "$fixture/results/dependencies/composer.lock.json" composer.lock
composer install --no-interaction --prefer-dist
cp .env.example .env
php artisan key:generate --no-interaction
touch database/database.sqlite
```

Keep the printed app path for the second terminal. Put a compatible FrankenPHP binary on your `PATH` or save one as `frankenphp` in the demo app root and make it executable. The [v1.13.6 release](https://github.com/php/frankenphp/releases/tag/v1.13.6) has the assets used for the archived example. Choose the asset that matches your OS and architecture. On Apple Silicon, you can download it as follows.

```bash
curl -fL https://github.com/php/frankenphp/releases/download/v1.13.6/frankenphp-mac-arm64 -o frankenphp
chmod +x frankenphp
```

If you downloaded into the demo app on either platform, run `export PATH="$PWD:$PATH"` in the server terminal so Octane uses that binary. Run `command -v frankenphp` and `frankenphp version` in that same terminal. Keep the path and the reported FrankenPHP, PHP and Caddy versions for the result files. FrankenPHP can replace the assets attached to its latest release with newer builds. The archived environment files capture the binary versions from those runs. [FrankenPHP binary builds](https://frankenphp.dev/docs/production/) · [FrankenPHP and Laravel](https://frankenphp.dev/docs/laravel/)

Install the worker entry point and copy the [benchmark fixtures](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel) into this app.

```bash
php artisan octane:install --server=frankenphp --no-interaction
cp "$fixture/routes/benchmark.php" routes/web.php
cp "$fixture/database/seeders/BenchmarkSeeder.php" database/seeders/
cp "$fixture/tests/Feature/BenchmarkTest.php" tests/Feature/
cp "$fixture/Caddyfile.benchmark" "$fixture/run-benchmark.sh" .

DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= php artisan test --filter=BenchmarkTest
```

Expect one passing feature test. It asserts that the per-row route runs 41 queries, the join runs one query, and their response bodies match for 40 orders. Both routes use Laravel’s query builder. In an Eloquent application, eager loading is another way to avoid looking up a relationship inside a loop. [Eloquent eager loading](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading)

Seed the disk-backed SQLite database for the HTTP lab and cache the app configuration and routes. These commands address the demo app’s database explicitly.

```bash
lab_database="$PWD/database/database.sqlite"
APP_ENV=production APP_DEBUG=false SESSION_DRIVER=array CACHE_STORE=array \
  DB_CONNECTION=sqlite DB_DATABASE="$lab_database" DB_URL= \
  php artisan db:seed --class=BenchmarkSeeder --force
APP_ENV=production APP_DEBUG=false SESSION_DRIVER=array CACHE_STORE=array \
  DB_CONNECTION=sqlite DB_DATABASE="$lab_database" DB_URL= \
  php artisan optimize
```

## Measure one change at a time

### Check both serving modes

In the demo app, start Octane with the [benchmark Caddyfile](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/Caddyfile.benchmark). It exposes classic mode at `127.0.0.1:8010` and worker mode at `127.0.0.1:8011`, with Caddy’s admin listener on loopback port 2026. Both sites use the same binary, cached configuration and SQLite file. The Caddyfile configures four worker instances and global PHP thread settings of four at startup and up to eight. The load generator uses concurrency four in each trial. [FrankenPHP configuration](https://frankenphp.dev/docs/config/)

```bash
BENCH_OPCACHE=1 php artisan octane:start --server=frankenphp \
  --host=127.0.0.1 --port=8011 --admin-port=2026 \
  --workers=4 --max-requests=10000 --caddyfile=Caddyfile.benchmark
```

Open a second Bash terminal at the demo app root. Inspect the effective settings on both listeners.

```bash
for port in 8010 8011; do
  curl --noproxy '*' -fsS "http://127.0.0.1:$port/_benchmark/runtime"
  printf '\n'
done
```

Both responses should report the same `php` version, `sapi` set to `frankenphp`, `opcache` set to `true`, and `jit` set to `false`. Expect the classic listener to report `index.php` and the worker listener to report `frankenphp-worker.php`. The runner checks these fields and verifies the seeded order data and the response bodies on both listeners before it sends load.

### Run a quick check or collect full results

Set up a results directory in the second terminal before running either profile. Keep that terminal open so the `results` variable stays available.

```bash
results="$(mktemp -d)"
```

Use the quick run to check setup. The first argument is the OPcache state you expect, and the second is a new output directory.

```bash
bash run-benchmark.sh 1 "$results/quick-on" --quick
```

The quick run sends 10 warm-up requests and 50 measured requests per route and mode, for one trial. Expect six data rows in `summary.tsv`. These are enough to check both listeners and all three workloads.

When you want comparable measurements, run the default profile. Record the version of the serving binary. For a system installation, substitute the absolute path you recorded in the server terminal for `./frankenphp` in the version commands.

```bash
bash run-benchmark.sh 1 "$results/opcache-on"
./frankenphp version >> "$results/opcache-on/environment.txt"
```

The full profile runs three trials with 200 warm-up requests and 2,500 measured requests for each route and mode. It uses the same route order in each trial and reverses the order of the modes in trial two. ApacheBench opens a new connection for each request with these options. The runner rejects incomplete, failed and non-2xx responses. [ApacheBench options](https://httpd.apache.org/docs/2.4/programs/ab.html)

The output contains these records.

| File | What to inspect |
| --- | --- |
| `summary.tsv` | Throughput in requests per second, p50 and p95 in milliseconds for every route, mode and trial. A full run has 18 data rows. |
| `apachebench.txt` | Warm-up and measurement reports, including completed and failed requests. |
| `environment.txt`, `composer.json` and `composer.lock` | Runtime flags, load profile, tool versions and installed dependency specification. |

### Repeat with OPcache disabled

Stop the server with Ctrl+C. Run the same startup command with `BENCH_OPCACHE=0`. The `/runtime` responses should then report `opcache: false`. Keep the routes, fixtures and workload unchanged. A full run can take several minutes with OPcache disabled in classic mode.

```bash
bash run-benchmark.sh 0 "$results/quick-off" --quick
bash run-benchmark.sh 0 "$results/opcache-off"
./frankenphp version >> "$results/opcache-off/environment.txt"
```

If a listener cannot bind, check whether another process is using port 8010, 8011 or 2026. For a runtime mismatch, confirm you stopped the previous server and loaded `Caddyfile.benchmark` with the matching `BENCH_OPCACHE` value. If the CLI test passes but the HTTP routes report a missing SQLite driver, check the PHP build bundled with FrankenPHP. If the runner reports unexpected order data, confirm that you seeded the demo database and cached the configuration with that file path. Stop the server when finished.

## Local results

The archived runs used an Apple M3 Pro on macOS 26.6.2, Laravel 13.43.0, Octane 2.25.0 and FrankenPHP 1.13.6 with PHP 8.5.13. The table reports the median throughput and the median of the three per-trial p95 values with OPcache enabled. ApacheBench displays these percentiles in whole milliseconds.

| Endpoint | Classic requests/s | Worker requests/s | Classic p95 | Worker p95 |
| --- | ---: | ---: | ---: | ---: |
| Ping | 981 | 2,946 | 8 ms | 3 ms |
| Orders with per-row lookups | 525 | 1,116 | 16 ms | 6 ms |
| Orders with one join | 648 | 2,055 | 15 ms | 4 ms |

Start with the order routes in classic mode. The join removed 40 queries and median throughput rose from 525 to 648 requests per second. The median p95 changed from 16 ms to 15 ms. Query count describes the work removed. Response times include the rest of the request and can move by a different proportion.

The ping comparison shows a gain from changing the serving mode under this workload without database queries. The order comparison also reflects connection handling, since a booted worker can reuse its SQLite connection. With OPcache off, the archived classic ping median was 49 requests/s with a p95 of 143 ms. The worker ping median was 4,039 requests/s with a p95 of 1 ms. The cache settings ran in separate server sessions, and the OPcache-on classic ping rates ranged from 648 to 1,059 requests/s across the three trials. Repeat and alternate sessions if you want to estimate the effect of a cache setting on your system. [OPcache-on summary](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/results/opcache-on.tsv) · [OPcache-off summary](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/results/opcache-off.tsv) · [Original reports and environment details](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel/results)

These were warm loopback HTTP runs without TLS, using a small SQLite dataset and no application data cache. Use representative routes, data and concurrency when you repeat the comparison for your application.

## Run workers in production

Baseline p95 latency, error rate and memory for representative routes. Try the worker runtime in staging, then compare those measures on a small share of live traffic while holding the application changes constant. An increase in errors or memory that keeps growing gives you a reason to stop the rollout and investigate. Keep request-specific data out of long-lived objects and reload workers during deploys. [Octane worker lifecycle](https://laravel.com/docs/13.x/octane)

On Laravel Cloud, install Octane in the application, enable **Use Octane as runtime** on the App cluster, then save and deploy the environment. [Laravel Cloud Octane configuration](https://cloud.laravel.com/docs/compute#laravel-octane)

## Exercise

Your order endpoint runs the same 41-query pattern as the lab. A second endpoint spends most of its time waiting for a remote API. Which change would you test on each endpoint, and what would count as a successful result?

<details>
<summary>Check your reasoning</summary>

For orders, try the join or eager loading before changing the serving mode. Verify the status, data and ordering, then use the same workload to compare the query count, p95 and errors. In the lab, the feature test and the quick run are the checks you perform before collecting the longer measurements.

For the remote API route, use a request trace to see whether the dependency can be called less often, called sooner or moved out of the response. If you trial Octane, keep the external call unchanged and compare the whole request as well as time spent in application boot and in the dependency. Keep the change only if it helps under representative load without worsening correctness, errors or memory use.

</details>

## Next chapter

[Does PHP Have to Be Messy?](/series/php-misconceptions/chapters/04-clean-laravel-code/)
