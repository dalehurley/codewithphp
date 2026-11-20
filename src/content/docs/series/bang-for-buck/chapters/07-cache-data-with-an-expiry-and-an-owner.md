---
title: "07: Cache Data with an Expiry and an Owner"
description: "Cache a Laravel shop catalogue summary in Redis, invalidate it on product changes, and benchmark latency, SQL load and expiry bursts."
series: "bang-for-buck"
chapter: 7
difficulty: "Intermediate"
estimatedTime: "PT30M"
sidebar:
  order: 7
---

## Overview

The shop’s category landing page needs the first 20 products, the number of products in the category and its price range. The summary returns a small JSON body, but PostgreSQL still reads the category’s rows to calculate those totals. If hundreds of shoppers ask for the same public summary, most requests can reuse the same result.

Cache that result in Redis for 60 seconds, refresh it when a product changes, and measure how much SQL the cache saves. Then replay a burst just after expiry to see what happens when requests need to refill the same key.

## Prerequisites

Use the Compose shop from [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/) and the category with 25,000 products from [Ask the Database for Less](/series/bang-for-buck/chapters/05-ask-the-database-for-less/). The commands run from `code/bang-for-buck`. Allow about 30 minutes.

## Quick start

The PHP image now includes PhpRedis. The Compose Redis service sets `maxmemory` to 32 MiB, uses `allkeys-lru` eviction and starts with an empty cache after a restart. The app uses Laravel’s named `redis` cache store for the summary. [Laravel Redis configuration](https://laravel.com/docs/13.x/redis) · [Redis eviction policies](https://redis.io/docs/latest/develop/reference/eviction/)

```bash
docker compose build php
docker compose up -d --wait redis
docker compose run --rm php php artisan db:seed --class=CatalogueBenchmarkSeeder --force
docker compose up -d --wait --force-recreate php web worker shipping
curl -fsS --retry 5 --retry-all-errors --retry-delay 1 "http://127.0.0.1:${SHOP_PORT:-8088}/api/lab"
```

## Step 1 – Trace a bypass, a refill and a hit

The `/api/catalogue-summary` route uses one SQL statement. Its window functions count the category’s products and find the minimum and maximum prices before `LIMIT 20` selects the featured items. The query and the JSON are the same across cache modes. Run these commands after seeding, which invalidates the existing summary.

```bash
summary="http://127.0.0.1:${SHOP_PORT:-8088}/api/catalogue-summary?category=21"
for mode in bypass locked locked; do
    curl -fsS -D - -o /dev/null -H 'X-Shop-Trace: 1' "$summary&cache=$mode" \
        | awk 'tolower($0) ~ /^(x-catalogue-cache|server-timing):/'
done
```

Expect `bypass`, `miss`, then `hit` in `X-Catalogue-Cache`. The first two requests report one SQL query. The hit reports zero. In normal use the route defaults to `locked`. The lab can also select `direct`, which refills after a miss without acquiring a lock.

The cache key `catalogue-summary:v1:21:<generation>` identifies category 21, the response schema version and that category’s current generation. A separate Redis key holds the generation for an hour. If that key goes missing, the service atomically creates a fresh random generation. The JSON-ready data array expires after 60 seconds. If you later cache private prices or availability, include the tenant and any audience or currency that changes the result in the key. [Laravel cache retrieval and storage](https://laravel.com/docs/13.x/cache#cache-usage)

## Step 2 – Invalidate after a product changes

Change the price of product 5001 through the shop command. The product observer waits for the PostgreSQL transaction to commit, then replaces the Redis generation for the category. The next request builds the summary at a new key. An in-flight refill that already captured the old generation can only write to the old key, which expires on its own. A product move invalidates both its previous and new category. Bulk seeders register their own invalidation after their writes because bulk queries bypass model events. [Laravel Eloquent events](https://laravel.com/docs/13.x/eloquent#events) · [Laravel transaction callbacks](https://api.laravel.com/docs/13.x/Illuminate/Database/Connection.html#method_afterCommit)

The observer passes both category IDs to the cache service. `invalidateAfterCommit()` deduplicates them and updates Redis inside the `DB::afterCommit()` callback.

```php
if ($product->wasRecentlyCreated || $product->wasChanged(['category_id', 'title', 'price_cents'])) {
    app(CatalogueSummary::class)->invalidateAfterCommit([
        $product->getOriginal('category_id') ?? $product->category_id,
        $product->category_id,
    ]);
}
```

```bash
curl -fsS -D - "$summary"
docker compose exec -T php php artisan shop:product-price 5001 99
curl -fsS -D - "$summary"
curl -fsS -D - "$summary"
docker compose exec -T php php artisan shop:product-price 5001 2424
```

The first read shows the seed price of 2,424 cents. After the write, expect a `miss` with `featured[0].price_cents` and `min_price_cents` both set to 99. The next request is a `hit` at the new price. The final command restores the seed so the benchmark can check exact prices on every response.

The database commit and Redis invalidation are separate operations. A request can read an earlier value between those operations, and a failed invalidation can leave a stale value until its TTL expires. The lab logs invalidation failures. Checkout still reads the current price from PostgreSQL.

## Step 3 – Measure steady traffic

Use the same URL, seeded rows and offered rate for both modes. The runner warms the SQL query before each case, then warms the Redis value for the cached case. k6 checks the category, count, price range and the 20 products on every measured response.

```bash
WORKLOAD=cache CACHE_MODE=bypass RATE=45 DURATION_SECONDS=8 bash bench/run.sh steady
WORKLOAD=cache CACHE_MODE=locked RATE=45 DURATION_SECONDS=8 bash bench/run.sh steady
```

Compare `shop_catalogue_ms` p95 and the total `shop_catalogue_cache_db_queries` in `summary.json`. The SQL counter counts statements from these measured HTTP requests. PostgreSQL also serves queue polling, the runner’s health checks and other traffic. Compute the application hit rate as `(hits + coalesced) / completed requests` for a cached run. `redis-before.txt` and `redis-after.txt` record server memory, commands, hits and misses. Redis counts key lookups, so a single HTTP request can contribute several lookups. Use the application counters when comparing requests. [Redis INFO fields](https://redis.io/docs/latest/commands/info/)

## Step 4 – Compare refills after expiry

The runner warms the same value, shortens its remaining TTL to one second and waits for it to expire. It then starts 16 virtual users with one request each. In `direct` mode, any concurrent misses may run the SQL query. In `locked` mode the first miss fills the cache while other requests wait. Each waiter checks Redis again after acquiring the lock and can reuse the result. [Laravel atomic locks](https://laravel.com/docs/13.x/cache#atomic-locks)

```php
return $cache->lock($key . ':lock', 5)->betweenBlockedAttemptsSleepFor(5)
    ->block(1, function () use ($cache, $key, $fill, &$summary): array {
        if (($summary = $cache->get($key)) !== null) {
            return [$summary, 'coalesced'];
        }

        return $fill();
    });
```

The lock has a five-second lease. The wait limit is one second and blocked requests retry every five milliseconds. Laravel releases the lock when the callback finishes. Different categories can refill concurrently because each value has its own lock. Under memory pressure, `allkeys-lru` can evict a lock as well as a value. [Redis eviction policies](https://redis.io/docs/latest/develop/reference/eviction/)

```bash
WORKLOAD=cache CACHE_MODE=direct CACHE_TRAFFIC=expiry VUS=16 bash bench/run.sh steady
WORKLOAD=cache CACHE_MODE=locked CACHE_TRAFFIC=expiry VUS=16 bash bench/run.sh steady
```

Compare the SQL query count, refills and p95. `coalesced` means a request found the rebuilt value after waiting. A refill that saves queries can still make some requests wait longer. With 16 requests, treat p95 as a rough description of the slow end of that batch. If Redis is unavailable or the lock wait runs out, the app queries PostgreSQL and reports `fallback`.

## Local measurements

The lab ran on an Apple M3 Pro with one CPU and four PHP-FPM workers for the app, PostgreSQL 18.4 and Redis 8.6.3. The warm comparison used three eight-second runs per case at 45 requests per second. Each finished 360 or 361 valid responses with zero failures or drops. The expiry comparison used three bursts per mode, each with 16 successful requests. The raw results are in `code/bang-for-buck/bench/chapter-07-measurements.tsv`.

| Case | HTTP p95, median and range | SQL queries per run | Cache refills | Requests served after waiting |
| --- | ---: | ---: | ---: | ---: |
| Warm, bypass | 16.39 ms (16.15–19.48) | 361 | — | — |
| Warm, Redis | 6.99 ms (6.83–7.11) | 0 | 0 | 0 |
| Expiry, direct | 57.72 ms (49.17–75.82) | 4 | 4 | 0 |
| Expiry, locked | 43.58 ms (41.37–47.41) | 1 | 1 | 3 |

Every measured warm Redis response was a hit. The warm p95 decreased by 57% and the category summary stopped issuing SQL during the timed runs. At expiry the lock cut four simultaneous regenerations to one in every burst. All 16 requests still went through the four PHP-FPM workers, so some requests queued before the app could check Redis.

Redis recorded about 720 key hits and 1,085 commands in each warm cached run, including Redis connection setup and service checks. After a restart and refill, `MEMORY USAGE` reported 2,160 bytes for the summary and 128 bytes for its generation. Redis reported about 1.5 MiB used and 22 MiB resident at that point. Budget for the service as well as the cached data. [Redis memory and command counters](https://redis.io/docs/latest/commands/info/)

With Redis stopped, a separate run served all 361 requests from PostgreSQL at 20.24 ms p95. The summary matched the cached response. This outage run shows the SQL load a cache can hide and the database has to absorb when the cache disappears.

## Exercise – Lose the cache and recover

Keep this exercise on the local shop. Remove just the category’s generation key to simulate Redis evicting it while the old payload still exists. Then stop Redis to see the database fallback. Both reads should still return the seeded catalogue data.

```bash
curl -fsS -o /dev/null "$summary"
docker compose exec -T redis redis-cli -n 1 UNLINK 'shop:cache:catalogue-summary:v1:21:generation'
curl -fsS -D - -o /dev/null -H 'X-Shop-Trace: 1' "$summary"
docker compose stop redis
curl -fsS -D - -o /dev/null -H 'X-Shop-Trace: 1' "$summary"
WORKLOAD=cache CACHE_MODE=locked RATE=45 DURATION_SECONDS=8 bash bench/run.sh steady
docker compose up -d --wait redis
curl -fsS -D - -o /dev/null -H 'X-Shop-Trace: 1' "$summary"
```

The first traced read should be a `miss`, the offline read a `fallback` and the restarted Redis read a `miss`. Each should run one SQL query. To see the resource cost, run `redis-cli INFO memory` in the Redis container. Use `redis-cli -n 1 --scan --pattern 'shop:cache:catalogue-summary:v1:21:*'` to find the keys and `MEMORY USAGE` to measure one key. Budget for both the cached objects and the Redis process. [Redis MEMORY USAGE](https://redis.io/docs/latest/commands/memory-usage/)

Continue with [Move Work Beyond the Response](/series/bang-for-buck/chapters/08-move-work-beyond-the-response/).
