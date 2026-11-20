---
title: "05: Ask the Database for Less"
description: "Fix the catalogue’s N+1 with Eloquent, fetch narrower rows and compare offset and cursor pagination under the same workload."
series: "bang-for-buck"
chapter: 5
difficulty: "Intermediate"
estimatedTime: "PT30M"
sidebar:
  order: 5
---

## Overview

A catalogue response can look small while the server makes 22 database calls to build it. Start by making fewer calls. Then see how much data those calls pull into PHP. Finally, compare how much work PostgreSQL does to reach a deep page.

Each experiment keeps the product IDs, ordering, prices and category labels the same. The shop’s default endpoint now eager loads categories and selects the fields it needs. It keeps offset pagination for existing callers, and the lab keeps the original lazy path for comparison.

## Prerequisites

Use the Compose shop from [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/) and the request trace from [Profile a Slow Laravel Request](/series/bang-for-buck/chapters/03-profile-a-slow-request/). Run the commands from `code/bang-for-buck`. Allow about 30 minutes.

## Quick Start

Rebuild the application, run the additive migration and seed a separate category for this comparison. Category 21 has 25,000 products and a 4,096-byte description per product. That gives you wide rows and enough depth to test page 1,000. The seeder can be rerun and leaves the original 20 categories available for the earlier labs.

```bash
docker compose build php
docker compose run --rm php php artisan migrate --force
docker compose run --rm php php artisan db:seed --class=CatalogueBenchmarkSeeder --force
docker compose exec -T db psql -X -U shop -d shop -c 'ANALYZE products'
docker compose up -d --wait --force-recreate php web worker shipping
curl -fsS --retry 5 --retry-all-errors --retry-delay 1 "http://127.0.0.1:${SHOP_PORT:-8088}/api/lab"
```

Look for 25,000 products under `catalogue_benchmark`. Keep the same PHP-FPM, OPcache and database settings across the runs.

## Step 1 – Load the categories together

```bash
bash bench/trace.sh catalogue lazy
bash bench/trace.sh catalogue eager
```

The lazy trace shows 22 queries. One validates the category, one loads the product page, and the loop asks for the category again for each of the 20 products. Eloquent’s `with('category')` fetches the related category IDs in one query and attaches the loaded records to the products. The eager trace shows three queries, including validation, and the JSON body is identical. [Laravel eager loading](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading)

```php
$products = Product::query()
    ->where('category_id', $categoryId)
    ->orderBy('id')
    ->with('category')
    ->simplePaginate(20);
```

If you add a relationship to a response later, trace it again. Loading the first relationship does not automatically load other relationships the serializer may start reading.

## Step 2 – Select only the fields the response uses

The published JSON needs the product ID, title, price and category name. Select those alongside the keys Eloquent needs to match the models.

```php
$products = Product::query()
    ->where('category_id', $categoryId)
    ->orderBy('id')
    ->select('id', 'category_id', 'title', 'price_cents')
    ->with('category:id,name')
    ->simplePaginate(20);
```

Keep `category_id` on the product and `id` on the category so Eloquent can attach the category. Keep the product’s `id` for its identity and ordering. The `eager` variant selects full product rows, while `projected` selects these four columns. Both variants return the same JSON and run three queries. [Laravel eager loading specific columns](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading-specific-columns)

Check how much description text was in the original first-page query. `simplePaginate(20)` reads a 21st row to determine whether more items exist.

```bash
docker compose exec -T db psql -X -U shop -d shop <<'SQL'
SELECT count(*) AS rows_read, sum(octet_length(description)) AS description_bytes
FROM (
    SELECT description
    FROM products
    WHERE category_id = 21
    ORDER BY id
    LIMIT 21
) AS first_page;
SQL
```

Expect 21 rows and 86,016 bytes of description values before database protocol overhead. The projection leaves those values in PostgreSQL. The HTTP body size stays the same because both versions already omit the description when serialising the products.

## Step 3 – Seek from the last product

The original pagination query uses `OFFSET` to skip earlier products. The cursor query uses the last product’s ID as its starting boundary and asks for IDs above it. It can seek to that boundary using an index. The order must be unique and non-null, so this shop sorts by the primary key. Use the cursor with the same category and sort order. [Laravel cursor pagination](https://laravel.com/docs/13.x/pagination#cursor-pagination) · [PostgreSQL LIMIT and OFFSET](https://www.postgresql.org/docs/18/queries-limit.html)

```bash
catalogue="http://127.0.0.1:${SHOP_PORT:-8088}/api/catalogue?category=21&variant=cursor"
next=$(curl -fsS "$catalogue" | docker compose exec -T php php -r '
    $page = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
    echo $page["next_cursor"];
')
curl -fsS --get --data-urlencode "cursor=$next" "$catalogue"
```

The first response covers products 5001 through 5020. The second covers 5021 through 5040. Follow the returned token until `next_cursor` is null. Use offset pagination on screens that need numbered-page jumps. The benchmark alone constructs a cursor for page 1,000 from the known seed, so it can time the same window without walking the first 999 pages.

At page 1,000, compare the two queries directly. Both fetch the narrow columns and the same 20 products plus a lookahead row.

```bash
docker compose exec -T db psql -X -U shop -d shop <<'SQL'
EXPLAIN (ANALYZE, BUFFERS, TIMING OFF)
SELECT id, category_id, title, price_cents
FROM products
WHERE category_id = 21
ORDER BY id LIMIT 21 OFFSET 19980;

EXPLAIN (ANALYZE, BUFFERS, TIMING OFF)
SELECT id, category_id, title, price_cents
FROM products
WHERE category_id = 21 AND id > 24980
ORDER BY id LIMIT 21;
SQL
```

In the local plans, the index scan produced **20,001 rows** for the offset query and **21 rows** for the cursor query. The offset scan also filtered out 5,000 rows from other categories. Both plans used the primary-key index on this seed. `EXPLAIN ANALYZE` executes the statement, so the example uses read-only queries against the lab. [PostgreSQL EXPLAIN](https://www.postgresql.org/docs/18/using-explain.html)

## Step 4 – Measure the same pages under load

The `catalogue` workload uses only the opt-in fixture. It checks the ordered items, prices and category on each response, and checks the expected query count using the Chapter 3 trace. Run each case at the same offered rate.

```bash
for entry in lazy:1 eager:1 projected:1 cursor:1 projected:1000 cursor:1000; do
    variant=${entry%:*}
    page=${entry#*:}
    WORKLOAD=catalogue CATALOGUE_VARIANT="$variant" CATALOGUE_PAGE="$page" \
        RATE=30 DURATION_SECONDS=6 bash bench/run.sh steady || break
done
```

Compare `shop_catalogue_sql_ms` for the database time in the request and `shop_catalogue_ms` for client-observed HTTP latency. The harness also records PHP process CPU when available and JSON body bytes. PHP CPU includes driver and framework work. Use that combined signal alongside the projection and query evidence. The original mixed workload keeps its lazy variant so readers can reproduce the earlier chapters’ measurements.

## Local measurements

Each row is the median of three six-second runs at an offered rate of 30 requests per second. The local lab ran PHP-FPM with OPcache on an Apple M3 Pro. The retained runs completed 180 or 181 requests each with zero failures or drops. One response failed the harness checks in the first deep-page comparison, so both sides of that comparison were rerun. The original runs are marked in the sample data. [Measurements by trial](https://github.com/dalehurley/codewithphp/blob/main/code/bang-for-buck/bench/chapter-05-measurements.tsv)

| Variant | Page | Queries | SQL p50 | HTTP p95 median and range | JSON body |
| --- | ---: | ---: | ---: | ---: | ---: |
| Lazy | 1 | 22 | 8.88 ms | 15.60 ms (14.89–29.69) | 1,607 B |
| Eager | 1 | 3 | 6.18 ms | 16.59 ms (13.39–16.67) | 1,607 B |
| Projected offset | 1 | 3 | 6.09 ms | 16.10 ms (16.09–17.17) | 1,607 B |
| Projected cursor | 1 | 3 | 6.16 ms | 13.35 ms (13.33–13.67) | 1,674 B |
| Projected offset | 1,000 | 3 | 7.54 ms | 15.00 ms (13.78–18.19) | 1,647 B |
| Projected cursor | 1,000 | 3 | 5.97 ms | 12.31 ms (11.95–14.32) | 1,715 B |

Eager loading removed 19 queries per page. The narrower query removed about 84 KiB of description values from the first-page database result. Its HTTP body and query count stayed the same, and its measured SQL time was close to the full-row eager variant.

At page 1,000, both narrow variants still used three queries. The cursor saved work inside the product query, which is visible in the plan’s row counts and the lower SQL median. The HTTP p95 ranges still overlap. The cursor adds a small token to the JSON so the client can request the next page.

## Exercise

Choose the projected and cursor variants and repeat them at pages 1 and 1,000. Check that the rows match. Compare the query counts, SQL medians and HTTP p95 before deciding whether changing the pagination interface is worth it for this endpoint.

Continue with [Make the Remaining SQL Cheaper](/series/bang-for-buck/chapters/06-make-the-remaining-sql-cheaper/).
