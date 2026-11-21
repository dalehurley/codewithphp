---
title: "03: How Fast Can Laravel Be?"
description: "Measure what changes when a Laravel endpoint replaces 41 queries with one join or serves the same requests with Octane workers."
series: "php-misconceptions"
chapter: 3
estimatedTime: "PT25M"
sidebar:
  order: 3
---

<span id="overview"></span>

## What the lab compares

Your Laravel endpoint lists 40 orders and runs 41 queries to include the customer names. How much faster would it be with one query? How much could you gain by keeping Laravel booted between requests?

Change one thing for each comparison. To measure the query rewrite, compare the two order routes on the same listener with the same OPcache setting. To measure serving mode, compare the same route across listeners. The ping route gives you a starting point because its handler does no database work before returning a small JSON response.

Both listeners use FrankenPHP. In classic mode Laravel boots for each request. In Octane worker mode each worker keeps a booted application in memory between requests. OPcache reuses compiled PHP code in either mode. [Laravel Octane](https://laravel.com/docs/13.x/octane) · [FrankenPHP workers](https://frankenphp.dev/docs/worker/) · [PHP OPcache](https://www.php.net/manual/en/book.opcache.php)

| Route | What it does | What you can compare |
| --- | --- | --- |
| `/_benchmark/ping` | Returns the same small JSON response with no database query. | Compare classic and worker mode without database work in the handler. |
| `/_benchmark/orders/per-row` | Loads 40 orders, then looks up a customer for each order. | Use it as the baseline for the order workload in a given mode. |
| `/_benchmark/orders/join` | Returns the same 40 orders and customer names from one joined query. | Compare it with the per-row route in the same mode. |

You can follow the worked comparison in [Local results](#local-results) or run the quick check below. The full timing runs are optional.

## Set up the lab

Use Bash on Linux or Apple Silicon macOS with PHP 8.5.x on the command line, Composer 2.2 or later, curl and ApacheBench (`ab`). The archived run used CLI PHP 8.5.0. If you already use PHP 8.6 for this series, it also satisfies the lab’s recorded Composer requirements.

Your command-line PHP needs PDO SQLite and Laravel’s required extensions. On a minimal Linux install, check for `mbstring`, DOM, XML and XMLWriter as well. Run `php --version`, `php --ri pdo_sqlite`, `composer --version` and `ab -V` to check the tools. This PHP runs Composer, Artisan and the feature test. FrankenPHP bundles the PHP runtime that serves HTTP. [Laravel server extensions](https://laravel.com/docs/13.x/deployment#server-requirements) · [Octane server prerequisites](https://laravel.com/docs/13.x/octane#frankenphp)

Open a Bash terminal at the root of the [Code with PHP checkout](https://github.com/dalehurley/codewithphp). Use this terminal for setup and to run the server. The commands create a disposable Laravel app with the [recorded Composer dependencies](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel/results/dependencies).

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

Keep the printed app path for the second terminal. If you already have a compatible FrankenPHP on your `PATH`, you can use that installation. To download from the v1.13.6 release, choose the asset for your platform. Use `frankenphp-mac-arm64` for Apple Silicon macOS, `frankenphp-linux-x86_64` for Linux x86-64 or `frankenphp-linux-aarch64` for Linux arm64. Run `uname -sm` to check your system. Release assets can be rebuilt under the same tag, so save the version and checksum if you collect results to share. [FrankenPHP v1.13.6 release assets](https://github.com/php/frankenphp/releases/expanded_assets/v1.13.6) · [FrankenPHP binary builds](https://frankenphp.dev/docs/production/)

For a download into the demo app, set `asset` to the matching filename and put that binary first on this terminal’s `PATH`.

```bash
asset=frankenphp-mac-arm64
curl -fL "https://github.com/php/frankenphp/releases/download/v1.13.6/$asset" -o frankenphp
chmod +x frankenphp
export PATH="$PWD:$PATH"
```

Check the binary path and confirm that its bundled PHP can open the SQLite database. The check uses a PHP file so it also works with the archived FrankenPHP build’s PHP 8.5.

```bash
command -v frankenphp
frankenphp version
cat > "$sandbox/check-sqlite.php" <<'PHP'
<?php
new PDO('sqlite::memory:');
echo "PDO SQLite is available\n";
PHP
frankenphp php-cli "$sandbox/check-sqlite.php"
```

If the script reports a missing driver, choose a FrankenPHP build with PDO SQLite before continuing. Keep the binary path for the results. Then install the worker entry point and copy the [benchmark fixtures](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel) into this app.

```bash
php artisan octane:install --server=frankenphp --no-interaction
cp "$fixture/routes/benchmark.php" routes/web.php
cp "$fixture/database/seeders/BenchmarkSeeder.php" database/seeders/
cp "$fixture/tests/Feature/BenchmarkTest.php" tests/Feature/
cp "$fixture/Caddyfile.benchmark" "$fixture/run-benchmark.sh" .

DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= php artisan test --filter=BenchmarkTest
```

Expect one passing feature test. It verifies that the per-row route runs 41 queries, the join runs one query, and their JSON bodies match for 40 orders.

The per-row route orders the rows by ID, then calls `DB::table('benchmark_customers')->where('id', $order->customer_id)->value('name')` for each order. The join matches `benchmark_orders.customer_id` to `benchmark_customers.id` in one query. It aliases the customer’s name as `customer` and keeps the same order by ID. Every seeded order has a customer. If your application allows orders without a customer and returns them with `"customer": null`, use a left join to preserve those rows and test that case. [Benchmark routes](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/routes/benchmark.php) · [Laravel query builder joins](https://laravel.com/docs/13.x/queries#joins)

If your application uses Eloquent models, try eager loading the customer relationship before the loop. Compare query count and verify the response data as you did here. [Ask the Database for Less](/series/bang-for-buck/chapters/05-ask-the-database-for-less/) · [Eloquent eager loading](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading)

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

In the first terminal, start Octane with the [benchmark Caddyfile](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/Caddyfile.benchmark). It serves classic mode at `127.0.0.1:8010` and worker mode at `127.0.0.1:8011`, with Caddy’s admin listener on loopback port 2026. Both sites use the same binary, cached configuration and SQLite file. FrankenPHP starts four classic threads and four worker threads, with a total cap of eight. ApacheBench targets one listener at a time with up to four requests in flight. [FrankenPHP configuration](https://frankenphp.dev/docs/config/)

```bash
BENCH_OPCACHE=1 php artisan octane:start --server=frankenphp \
  --host=127.0.0.1 --port=8011 --admin-port=2026 \
  --workers=4 --max-requests=10000 --caddyfile=Caddyfile.benchmark
```

Leave the server running. Open a second Bash terminal, change to the demo app path printed during setup, and inspect the effective settings on both listeners.

```bash
for port in 8010 8011; do
  curl --noproxy '*' -fsS "http://127.0.0.1:$port/_benchmark/runtime"
  printf '\n'
done
```

Both responses should report the same `php` version, `sapi` set to `frankenphp`, `opcache_available` and `opcache` set to `true`, and `jit` set to `false`. The classic entry point is `index.php`. The worker entry point is `frankenphp-worker.php`. The runner checks the runtime, the seeded order data and the response bodies on both listeners before it sends load.

### Run a quick check or collect full results

Set up a results directory in the second terminal before running either profile. Keep that terminal open so the `results` variable stays available.

```bash
results="$(mktemp -d)"
```

Use the quick run to check setup. The first argument is the OPcache state you expect, and the second is a new output directory.

```bash
bash run-benchmark.sh 1 "$results/quick-on" --quick
```

The quick run sends 10 warm-up requests and 50 measured requests per route and mode, for one trial. Expect six data rows in `summary.tsv`. If you only want to verify the setup, stop the server with Ctrl+C in the first terminal and continue to [Local results](#local-results).

For repeated measurements, run the default profile. Record the version and SHA-256 checksum of the binary you started in the first terminal. For a system installation, set `binary` to the absolute path printed by `command -v frankenphp`.

```bash
binary=./frankenphp
bash run-benchmark.sh 1 "$results/opcache-on"
{
  "$binary" version
  php -r 'echo "FrankenPHP SHA-256: ", hash_file("sha256", $argv[1]), PHP_EOL;' "$binary"
} >> "$results/opcache-on/environment.txt"
```

The full profile runs three trials with 200 warm-up requests and 2,500 measured requests for each route and mode. All three trials use one server session and keep OPcache, concurrency and the dataset fixed. Every trial loads ping, per-row orders, then the join. Trials one and three start with classic mode, and trial two starts with worker mode. ApacheBench opens a new connection for each request with these options. The runner rejects incomplete runs, failed requests and non-2xx responses. [ApacheBench options](https://httpd.apache.org/docs/2.4/programs/ab.html)

Throughput is the number of requests completed per second. The p50 is the median latency, and the p95 is the time within which 95% of requests finished. Read both alongside the raw request counts.

| File | What to inspect |
| --- | --- |
| `summary.tsv` | Throughput in requests per second, p50 and p95 in milliseconds for every route, mode and trial. A full run has 18 data rows. |
| `apachebench.txt` | Warm-up and measurement reports, including completed and failed requests. |
| `environment.txt`, `composer.json` and `composer.lock` | Runtime flags, load profile, tool versions and installed dependency specification. |

### Repeat with OPcache disabled

To explore the cache setting, stop the server with Ctrl+C in the first terminal and rerun the same startup command with `BENCH_OPCACHE=0`. Both runtime responses should report `opcache: false`. Keep the binary, routes, fixtures and load profile fixed. Run the quick check from the second terminal. The full run is optional and can take several minutes with OPcache disabled in classic mode.

```bash
bash run-benchmark.sh 0 "$results/quick-off" --quick
```

If you ran the full profile with OPcache on, use the same profile here to record another session.

```bash
bash run-benchmark.sh 0 "$results/opcache-off"
{
  "$binary" version
  php -r 'echo "FrankenPHP SHA-256: ", hash_file("sha256", $argv[1]), PHP_EOL;' "$binary"
} >> "$results/opcache-off/environment.txt"
```

If a listener cannot bind, check whether another process is using port 8010, 8011 or 2026. For a runtime mismatch, confirm you stopped the previous server and loaded `Caddyfile.benchmark` with the matching `BENCH_OPCACHE` value. If the CLI test passes but the HTTP routes report a missing SQLite driver, check the PHP build bundled with FrankenPHP. If the runner reports unexpected order data, confirm that you seeded the demo database and cached the configuration with that file path. Stop the server when finished.

## Local results

The archived runs used an Apple M3 Pro on macOS 26.6.2, Laravel 13.43.0, Octane 2.25.0 and FrankenPHP 1.13.6 with bundled PHP 8.5.13. They measured warm HTTP requests over loopback without TLS, using a small SQLite dataset and no application data cache. For each trial, ApacheBench reports p95, the time within which 95% of requests finished, in whole milliseconds. The table shows the median throughput and median p95 across the three trials with OPcache enabled.

| Endpoint | Classic requests/s | Worker requests/s | Classic p95 | Worker p95 |
| --- | ---: | ---: | ---: | ---: |
| Ping | 981 | 2,946 | 8 ms | 3 ms |
| Orders with per-row lookups | 525 | 1,116 | 16 ms | 6 ms |
| Orders with one join | 648 | 2,055 | 15 ms | 4 ms |

Start with the order routes in classic mode. The join removed 40 queries and median throughput rose from 525 to 648 requests per second. The median p95 moved from 16 ms to 15 ms. Query count describes the work removed, while response times include the rest of the request.

The individual trials show how throughput and latency can move independently. In worker trial one, the join raised throughput from about 1,100 to 1,511 requests/s while p95 rose from 6 to 7 ms. Across all three trials, the worker join’s median p95 was 4 ms and its per-trial p95 ranged from 2 to 7 ms. If your goal were a p95 of at most 5 ms in every repeated run, you would investigate the 7 ms trial before deciding the join meets that goal. Use your own latency target, throughput needs and error counts together. [OPcache-on summary](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/results/opcache-on.tsv)

Next compare the same route across modes. Ping served about three times as many requests per second in worker mode with OPcache on. That comparison shows how the two serving modes behave without SQL in the handler. The order timings cover the whole HTTP request, and reuse of a SQLite connection could contribute to the difference across modes.

The cache settings ran in separate server sessions. Even within the OPcache-on session, classic ping throughput ranged from 648 to 1,059 requests/s. With OPcache off, the archived classic ping median was 49 requests/s with a median p95 of 143 ms. The worker ping median was 4,039 requests/s with a median p95 of 1 ms. Treat those figures as observations from the two sessions. To estimate the effect of the cache setting on your system, alternate fresh on and off sessions with the same profile and compare the variation across sessions. [OPcache-off summary](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/results/opcache-off.tsv) · [Original reports and environment details](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel/results)

To make a decision for your application, repeat the relevant comparison with representative routes, data and concurrency.

## Run workers in production

Baseline p95 latency, error rate and memory for representative routes. Before testing throughput in staging, check that a worker behaves correctly across different requests. Suppose a shared formatter reads the current request’s locale when Laravel creates it as a singleton. That locale could carry over to the next request. Read the locale in the controller and pass it to the formatter on each call with `$formatter->format($amount, $request->getLocale())`. [Octane request injection](https://laravel.com/docs/13.x/octane#request-injection)

In a staging instance with one worker, send two sequential requests that your endpoint resolves to different locales, such as `en` and `fr`. Check that each returns the expected language or format, and repeat with the order reversed after restarting the worker. The lab sends the same payloads repeatedly, so its response checks exercise equivalence across modes. Run the staging check with inputs that exercise your own request-specific state.

If the staging checks pass, compare latency, errors and memory on a small share of live traffic while holding application changes constant. Stop and investigate if errors rise or if worker memory keeps growing. Reload workers during deploys. [Octane worker lifecycle](https://laravel.com/docs/13.x/octane)

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
