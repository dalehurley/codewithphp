---
title: "06: Make the Remaining SQL Cheaper"
description: "Read a PostgreSQL query plan, build one index for the shop’s order history, and measure what it saves on reads and costs on writes."
series: "bang-for-buck"
chapter: 6
difficulty: "Intermediate"
estimatedTime: "PT30M"
sidebar:
  order: 6
---

## Overview

The shop’s order history runs one SQL query and returns 20 orders. PostgreSQL still scans a table with 200,000 historical orders and sorts the matches to find that page. You’ll read the plan, add an index that delivers the rows in the requested order, and check whether the faster read is worth the storage and write work.

## Prerequisites

Start the Compose shop from [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/). The benchmark uses the SQL timings from [Profile a Slow Laravel Request](/series/bang-for-buck/chapters/03-profile-a-slow-request/). Run the commands from `code/bang-for-buck` against your local lab. Allow about 30 minutes.

## Quick start

Rebuild the app to pick up the order history route. The fixture adds 200,000 orders across 500 products, keeps any orders already in the lab, and refreshes the planner’s table statistics. You can run it again without adding duplicate history.

```bash
docker compose build php
docker compose up -d --wait --force-recreate php web worker shipping
curl -fsS --retry 5 --retry-all-errors --retry-delay 1 "http://127.0.0.1:${SHOP_PORT:-8088}/api/lab"
docker compose exec -T db psql -X --set=ON_ERROR_STOP=1 -U shop -d shop < bench/orders-fixture.sql
```

Use one product and a fixed date range throughout. `from` includes midnight on 1 April, while `before` excludes midnight on 1 June. Both dates are UTC. Checkouts create orders at the current time, so they exercise writes to the same table without changing the history page during the test.

```bash
orders_url="http://127.0.0.1:${SHOP_PORT:-8088}/api/order-history?product_id=42&from=2025-04-01&before=2025-06-01"
comparison_dir=$(mktemp -d)
```

## Step 1 – Read the plan from the scan up

Start with the experiment index absent. The existing primary key and the unique index on `(run_id, reference)` still support order identity and retry handling.

```bash
bash bench/orders-index.sh off
curl -fsS "$orders_url" > "$comparison_dir/before.json"
docker compose exec -T db psql -X --set=ON_ERROR_STOP=1 -U shop -d shop < bench/orders-plan.sql
```

The file runs the same query as the Laravel route, with literal values for the three request parameters.

```sql
EXPLAIN (ANALYZE, BUFFERS, TIMING OFF)
SELECT id, reference, product_id, total_cents, created_at
FROM orders
WHERE product_id = 42
  AND created_at >= TIMESTAMPTZ '2025-04-01 00:00:00+00'
  AND created_at <  TIMESTAMPTZ '2025-06-01 00:00:00+00'
ORDER BY created_at DESC, id DESC
LIMIT 20;
```

Read the indented nodes from the bottom up. Each parent asks its children for rows. `cost` is the planner’s estimate in relative units. The plain `rows` estimate is how many rows a node expects to emit. `actual rows` with `loops` tells you how many rows it emitted per execution, averaged across executions. `ANALYZE` runs the statement. `TIMING OFF` skips per-node clocks while retaining row counts and total execution time. [PostgreSQL on reading EXPLAIN](https://www.postgresql.org/docs/18/using-explain.html) · [EXPLAIN options](https://www.postgresql.org/docs/18/sql-explain.html)

```text
Limit  (cost=5794.80..5794.85 rows=20 width=42) (actual rows=20.00 loops=1)
  Buffers: shared hit=2708
  ->  Sort  ... (actual rows=20.00 loops=1)
        Sort Method: top-N heapsort  Memory: 27kB
        ->  Gather  ... (actual rows=140.00 loops=1)
              ->  Parallel Seq Scan on orders  ... (actual rows=70.00 loops=2)
                    Rows Removed by Filter: 100314
```

In the local baseline, follow the rows through three stages.

1. `Parallel Seq Scan on orders` scanned the table and applied the product and time filters. The `actual rows=70.00 loops=2` line is about 140 matches in total. `Rows Removed by Filter` is also averaged per loop, so the scan discarded about 200,628 rows.
2. `Gather` collected the 140 matches. `Sort` used a top-N heapsort to keep the best 20 by timestamp and ID. PostgreSQL still had to examine all 140 matches to choose them.
3. `Limit` returned 20 rows. Read its `Buffers` line for the execution’s shared-buffer touches. `shared hit` means PostgreSQL already had the page in shared buffers. A parent includes its children’s counts, so adding the lines would count the same touches again.

## Step 2 – Build the index around this query

Put `product_id` first to find one product’s portion of the B-tree. Within that portion, PostgreSQL can seek to the time range and read entries in the order the query needs. `id` breaks ties when timestamps match. An ordered scan can stop as soon as it has found 20 visible matching rows. [PostgreSQL multicolumn indexes](https://www.postgresql.org/docs/18/indexes-multicolumn.html) · [Indexes and ORDER BY](https://www.postgresql.org/docs/18/indexes-ordering.html)

```sql
CREATE INDEX orders_product_recent_idx
    ON orders (product_id, created_at DESC, id DESC);
```

The script applies that definition to the local database. Run the same plan again and compare the response bodies.

```bash
bash bench/orders-index.sh on
docker compose exec -T db psql -X --set=ON_ERROR_STOP=1 -U shop -d shop < bench/orders-plan.sql
curl -fsS "$orders_url" > "$comparison_dir/after.json"
cmp "$comparison_dir/before.json" "$comparison_dir/after.json"
```

The indexed plan is `Limit → Index Scan using orders_product_recent_idx`. Look for the product and both date bounds in `Index Cond` and 20 rows emitted by the scan. The index emits rows in order, so PostgreSQL skips the sort. This is a regular index scan, so PostgreSQL still visits the table for values such as `reference` and `total_cents`. A sequential scan can still be the cheapest plan for a query that needs much of the table.

```text
Limit  (cost=0.42..80.24 rows=20 width=42) (actual rows=20.00 loops=1)
  Buffers: shared hit=26
  ->  Index Scan using orders_product_recent_idx on orders  ... (actual rows=20.00 loops=1)
        Index Cond: ((product_id = 42) AND (created_at >= ...) AND (created_at < ...))
```

On a live orders table, a plain `CREATE INDEX` blocks writes during the build. PostgreSQL offers `CREATE INDEX CONCURRENTLY` to allow ongoing writes. It does more work, takes longer and must run outside a transaction block. The lab builds and drops its experiment index between load runs. [PostgreSQL index builds](https://www.postgresql.org/docs/18/sql-createindex.html)

## Step 3 – Measure reads and writes

Run a pair of tests with the same warm-up, rate and duration on both sides. Keep the seed and container limits fixed. Repeat the comparison, alternating which case comes first to reduce order effects.

```bash
(
    set -e
    for mode in off on; do
        bash bench/orders-index.sh "$mode"
        curl -fsS "$orders_url" > /dev/null
        WORKLOAD=orders RATE=40 DURATION_SECONDS=8 bash bench/run.sh steady

        # Warm up the insert probe, then record the second plan for comparison.
        docker compose exec -T db psql -X --set=ON_ERROR_STOP=1 -U shop -d shop < bench/orders-write-probe.sql > /dev/null
        docker compose exec -T db psql -X --set=ON_ERROR_STOP=1 -U shop -d shop < bench/orders-write-probe.sql
    done
)
```

The harness schedules four history reads for each checkout and checks the rows, their order and the number of SQL queries. Each checkout goes through the usual shipping quote and saves a real order in the lab. Compare `shop_order_history_ms` and `shop_order_ms` for HTTP p95, and `shop_order_history_sql_ms` and `shop_order_sql_ms` for median SQL time. These Laravel trace timings include driver and database round-trip time. Check `persisted.json` against the number of confirmed orders and reject a run with failures or dropped iterations.

The SQL write probe measures a separate batch of 1,000 inserts into `orders`. Look for `Tuples Inserted: 1000`, execution time and `WAL` bytes. Write-ahead log bytes show how much log the statement generates. Compare repeat runs because full-page images and page splits can move that number. The probe executes the insert inside a transaction and rolls it back. It still does the write work, generates WAL and can advance the ID sequence. Its plan timing excludes commit. [PostgreSQL EXPLAIN ANALYZE and WAL](https://www.postgresql.org/docs/18/sql-explain.html)

`order-index.json` records fixture rows and index bytes. Check `connections`, `max_connections` and `lock_waits` in `database.tsv` to see whether sampled sessions were near the connection limit or waiting on locks during the concurrent load. This sampler can miss short waits. [PostgreSQL activity statistics](https://www.postgresql.org/docs/18/monitoring-stats.html)

## Local measurements

Three pairs ran on an Apple M3 Pro with PostgreSQL 18.4 and PHP-FPM limited to one CPU. Each eight-second run offered 40 workflows per second, completed 320 or 321 workflows and persisted all 64 checkouts. There were zero failures or drops, and the history responses were byte-identical. The per-run data is in `code/bang-for-buck/bench/chapter-06-measurements.tsv`.

| Measure | Index off | Index on |
| --- | ---: | ---: |
| History HTTP p95, median and range | 16.86 ms (16.42–17.56) | 9.81 ms (9.13–57.63) |
| History SQL p50, median | 11.51 ms | 5.00 ms |
| Checkout HTTP p95, median and range | 40.02 ms (38.99–40.35) | 38.08 ms (37.25–133.81) |
| Checkout SQL p50, median | 7.49 ms | 6.94 ms |
| Direct read plan execution, median | 6.904 ms | 0.059 ms |
| Direct read plan shared-buffer hits, median | 2,785 | 26 |
| Insert probe execution, median per 1,000 rows | 11.39 ms | 12.41 ms |
| Insert probe WAL, median per 1,000 rows | 335,101 B | 414,590 B |
| Extra index size, median | 0 MiB | 7.8 MiB |

The index cut the median history HTTP p95 by 42%. One indexed run had a much slower HTTP tail for both reads and checkouts. Use the full range when evaluating a tail-latency target. The six runs each had two database activity samples, with at most two sessions on the shop database and zero sampled lock waits.

Checkout timing includes shipping, the insert and other SQL. The controlled insert probe shows the write cost more directly. It took about 1 ms longer per 1,000 rows and generated about 24% more WAL with the extra index. Every new order adds an entry, so the read benefit comes with recurring write work and storage. [PostgreSQL index trade-offs](https://www.postgresql.org/docs/18/indexes-intro.html)

## Exercise

Run the comparison again at your shop’s busiest expected rate. Record the read p95, checkout p95, index size and WAL bytes for both cases. Decide whether the tail-latency savings on the read justify the index for the amount of write traffic you expect.

Continue with [Cache Data with an Expiry and an Owner](/series/bang-for-buck/chapters/07-cache-data-with-an-expiry-and-an-owner/).
