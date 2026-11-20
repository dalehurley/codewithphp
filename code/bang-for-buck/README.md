# Bang for Buck shop and benchmark

This is the shared lab for [Bang for Buck: Scaling PHP Apps](https://codewithphp.com/series/bang-for-buck/). It runs Laravel on PHP-FPM with OPcache, PostgreSQL, a database queue worker, a simulated shipping service and a k6 load generator. Compose publishes the web port to loopback for this local fixture.

## Start the lab

Run from this directory with Docker Compose, Bash and curl.

```bash
docker compose build php
docker compose up -d --wait db redis
docker compose run --rm php php artisan migrate --seed --force
docker compose up -d --wait php web worker shipping
docker compose --profile bench pull k6
curl -fsS http://127.0.0.1:8088/api/lab
bash bench/run.sh smoke
```

Set `SHOP_PORT` for a different host port. The seed can be run again. Orders, completed exports and benchmark results are kept per run. For a fresh database and exports volume without erasing the existing one, use a different `COMPOSE_PROJECT_NAME` and `SHOP_PORT` consistently when bringing up and testing that lab.

The PHP image includes the app source. After editing it, rebuild and recreate the services that use it. Recreate the web container too so it resolves the PHP-FPM service again. The database and exports volumes are preserved.

```bash
docker compose build php
docker compose up -d --wait --force-recreate php web worker shipping
```

## Run a profile

| Command | Behaviour |
| --- | --- |
| `bash bench/run.sh smoke` | Brief mixed load to check the app and warm it up |
| `RATE=20 DURATION_SECONDS=30 bash bench/run.sh steady` | Open-model test at a fixed offered rate |
| `RATE=20 bash bench/run.sh burst` | One minute of scheduled traffic with a peak at three times the starting rate |
| `VUS=4 DURATION_SECONDS=30 bash bench/run.sh closed` | Fixed virtual users with a short pause between workflows |
| `bash bench/run.sh cold` | Two catalogue requests after restarting PHP-FPM |

`MAX_VUS` controls the ceiling for the open models. The benchmark container talks to the lab’s web service on the private Compose network. Each run writes its own ignored `results/` directory with k6 metrics, persisted counts, configuration and capacity samples. A failed load test returns a non-zero exit status and retains the report.

Use `bash bench/trace.sh order` or `bash bench/trace.sh catalogue` to trace one request. The [profiling chapter](https://codewithphp.com/series/bang-for-buck/chapters/03-profile-a-slow-request/) shows how to compare that breakdown against a load run and how to collect an optional Xdebug profile.

The [deployment cache lab](https://codewithphp.com/series/bang-for-buck/chapters/04-cut-deployment-work/) uses the same runner with `WORKLOAD=preview` and the `cold` or `steady` profile. Use `bash bench/cache-mode.sh` to set the Laravel and OPcache mode before each pair of runs. The script recreates the lab’s PHP and nginx containers. Run `bash bench/cache-mode.sh baseline` to restore the starting state.

The [catalogue query lab](https://codewithphp.com/series/bang-for-buck/chapters/05-ask-the-database-for-less/) uses the optional `CatalogueBenchmarkSeeder` and `WORKLOAD=catalogue`. Set `CATALOGUE_VARIANT` to `lazy`, `eager`, `projected` or `cursor`, and `CATALOGUE_PAGE` to the seeded window to compare. The normal mixed benchmark keeps the original lazy implementation for comparison with earlier chapters.

The [orders index lab](https://codewithphp.com/series/bang-for-buck/chapters/06-make-the-remaining-sql-cheaper/) adds a fixed history with `bench/orders-fixture.sql`. Use `bash bench/orders-index.sh off` or `on` before running `WORKLOAD=orders RATE=40 DURATION_SECONDS=8 bash bench/run.sh steady`. The workload runs four history reads for each real checkout. `bench/orders-plan.sql` shows the read plan and `bench/orders-write-probe.sql` measures 1,000 inserts inside a rolled-back transaction. The chapter shows how to run all three SQL files against the local PostgreSQL service.

The [Redis catalogue lab](https://codewithphp.com/series/bang-for-buck/chapters/07-cache-data-with-an-expiry-and-an-owner/) uses `CatalogueBenchmarkSeeder` and `WORKLOAD=cache`. Compare `CACHE_MODE=bypass` with `locked` under steady traffic. Add `CACHE_TRAFFIC=expiry VUS=16` with `CACHE_MODE=direct` or `locked` to compare one burst after expiry. Run `shop:product-price` in the PHP container to change a seeded product and invalidate its category after commit. Redis stores the summary and locks. PostgreSQL still backs orders and the queue.

The [CSV export lab](https://codewithphp.com/series/bang-for-buck/chapters/08-move-work-beyond-the-response/) uses `CatalogueBenchmarkSeeder` and `WORKLOAD=exports`. Compare `EXPORT_MODE=sync` and `EXPORT_MODE=queued` under the `steady` profile. The harness verifies every CSV and records the POST latency, queue wait and time to the complete file. Each benchmark export stores a separate CSV in the lab volume.

## Inspect the shop

| Request | Result |
| --- | --- |
| `GET /api/lab` | Runtime and seed characteristics |
| `GET /api/catalogue?category=1&page=1` | One page of seeded products with eager-loaded categories and selected fields |
| `GET /api/catalogue?category=1&variant=cursor` | The first cursor page and a `next_cursor` token to pass with the same category |
| `GET /api/catalogue-preview` | A Blade page with 20 seeded products and a heading supplied by a discovered listener |
| `GET /api/catalogue-summary?category=21` | A Redis-backed snapshot with product count, price range and 20 featured products |
| `GET /api/order-history?product_id=42&from=2025-04-01&before=2025-06-01` | Up to 20 recent orders for the product in the given UTC window |
| `POST /api/orders` | Save an order with `run_id`, `reference`, `product_id` and `quantity` |
| `POST /api/exports` | Request a CSV with `run_id`, `reference` and `category_id` |
| `POST /api/exports/sync` | Generate and download a CSV in the same request using the same fields |
| `GET /api/exports/{id}` | Read export status and timing |
| `GET /api/exports/{id}/file` | Download a ready export |

The `run_id` and `reference` identify a write. Repeating the same payload retrieves the same record. Reusing the identity for changed details returns HTTP 409.

Run the optional feature tests from `shop/` with PHP 8.5, PDO SQLite and Composer.

```bash
composer install
vendor/bin/phpunit
```

To stop the stack, run `docker compose down`. It preserves the lab’s named volumes.
