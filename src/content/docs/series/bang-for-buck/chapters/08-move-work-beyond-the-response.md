---
title: "08: Move Work Beyond the Response"
description: "Compare inline and queued CSV exports in Laravel. Measure the first HTTP response, time in the queue and how long the user waits for the complete file."
series: "bang-for-buck"
chapter: 8
difficulty: "Intermediate"
estimatedTime: "PT30M"
sidebar:
  order: 8
---

## Overview

A shopper asks for a CSV with 25,000 products. The synchronous endpoint keeps the request open and returns the CSV. The queued endpoint acknowledges the request, a worker builds the same CSV, and the client polls for it. The first response and the file have different deadlines. Measure both before deciding where the work belongs.

## Prerequisites

Use the Compose shop from [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/) and the wide category from [Ask the Database for Less](/series/bang-for-buck/chapters/05-ask-the-database-for-less/). Run the commands from `code/bang-for-buck`. Allow about 30 minutes.

## Quick start

Rebuild the app, add the export mode column and seed category 21. The migration gives existing export records the `queued` mode.

```bash
docker compose build php
docker compose up -d --wait redis
docker compose run --rm php php artisan migrate --force
docker compose run --rm php php artisan db:seed --class=CatalogueBenchmarkSeeder --force
docker compose up -d --wait --force-recreate php web worker shipping
curl -fsS --retry 5 --retry-all-errors --retry-delay 1 "http://127.0.0.1:${SHOP_PORT:-8088}/api/lab"
```

## Step 1 – Follow both requests to the file

`POST /api/exports/sync` runs `GenerateExport::dispatchSync()` in the PHP-FPM request. Once the job saves the file, the same request returns HTTP 200 and the CSV. The web worker stays busy until it finishes its part of producing and sending the response. [Laravel synchronous dispatching](https://laravel.com/docs/13.x/queues#synchronous-dispatching)

`POST /api/exports` saves the request and the database queue job in one PostgreSQL transaction. The worker can see both rows once that transaction commits. The first response is HTTP 202 and includes the status URL in `Location`. Keep that transaction boundary if you change queue drivers. With a separate broker, dispatching after commit prevents a worker from reading the job before the database row is visible. [Laravel jobs and database transactions](https://laravel.com/docs/13.x/queues#jobs-and-database-transactions) · [HTTP 202 Accepted](https://www.rfc-editor.org/rfc/rfc9110.html#name-202-accepted)

```bash
shop="http://127.0.0.1:${SHOP_PORT:-8088}"
run="chapter-08-$(date -u +%Y%m%dT%H%M%S)-$$"
files=$(mktemp -d)
inline="{\"run_id\":\"$run\",\"reference\":\"inline\",\"category_id\":21}"
queued="{\"run_id\":\"$run\",\"reference\":\"queued\",\"category_id\":21}"

curl -fsS -H 'Content-Type: application/json' -d "$inline" "$shop/api/exports/sync" \
    -D "$files/sync.headers" -o "$files/sync.csv" -w 'Sync POST HTTP %{http_code}, %{time_total} s\n'
curl -fsS -H 'Content-Type: application/json' -d "$queued" "$shop/api/exports" \
    -D "$files/accepted.headers" -o "$files/accepted.json" \
    -w 'Queued POST HTTP %{http_code}, %{time_total} s\n'
cat "$files/accepted.json"
status_path=$(awk 'tolower($1) == "location:" { sub(/\r$/, "", $2); print $2 }' "$files/accepted.headers")
status_url="$shop$status_path"
```

Poll until the state is `ready`, then download the file. `pending` means the job is waiting for the worker. `running` means an attempt has started. Treat `failed` as an outcome the client can show rather than polling indefinitely.

```bash
for attempt in {1..25}; do
    status=$(curl -fsS "$status_url") || break
    printf '%s\n' "$status"
    case "$status" in
        *'"state":"ready"'*|*'"state":"failed"'*) break ;;
    esac
    sleep 0.25
done
curl -fsS "$status_url/file" -o "$files/queued.csv"
cmp "$files/sync.csv" "$files/queued.csv"
wc -lc "$files/sync.csv"
```

Expect 25,001 lines including the header. Both paths use the same query, sort order, CSV writer and storage volume. The job writes a temporary file, renames it to the final path on the same local filesystem, then marks the record `ready`. The download endpoint serves the file once it reaches that state. A retry for a completed export returns the existing result. The same run ID and reference identify one export, and changing the category or mode with that identity returns HTTP 409.

## Step 2 – Keep three clocks

| Metric | Start and end | What it tells you |
| --- | --- | --- |
| `shop_export_initial_http_ms` | k6 sends the POST and receives the full response | How quickly the client gets an acknowledgement or an inline CSV |
| `shop_export_queue_wait_ms` | The app records the export request and the worker starts its first attempt | How long an accepted export waits to start. Applies to the queued path |
| `shop_export_file_wait_ms` | The client starts the POST and has received the last byte of the CSV | How long a person actually waits. Includes polling and download for the queued path |

The status response also includes `processing_ms` from first start to completion and `elapsed_ms` from receipt to completion on the server. Retries can make `processing_ms` include time between attempts. The synchronous status has `queue_wait_ms: null`. k6 reports HTTP duration without initial DNS or connection setup, while the file clock measures the full client workflow. Percentiles describe separate distributions, so subtracting the queue p95 from the file p95 will not give you the typical polling delay. [k6 HTTP timing metrics](https://grafana.com/docs/k6/latest/using-k6/metrics/reference/)

## Step 3 – Compare under the same traffic

Each ten-iteration block has eight catalogue reads, one checkout and one export. The harness schedules the same rate in both modes, waits 250 ms between queued status checks, and verifies the SHA-256 of every downloaded CSV against the seeded rows. The baseline workload records when it sees `ready`. This workload continues through the download and takes the file completion timestamp before doing the checksum. [k6 constant arrival rate](https://grafana.com/docs/k6/latest/using-k6/scenarios/executors/constant-arrival-rate/)

```bash
WORKLOAD=exports EXPORT_MODE=sync RATE=20 DURATION_SECONDS=12 bash bench/run.sh steady
WORKLOAD=exports EXPORT_MODE=queued RATE=20 DURATION_SECONDS=12 bash bench/run.sh steady
```

Repeat in alternating order with the same PHP-FPM and worker settings. Compare all three clocks, the status request count and the catalogue and checkout p95. Check that `exports.ready` in `persisted.json` agrees with `shop_export_files_verified`. The queue should drain by the end. The runner samples pending jobs, reserved jobs and the age in seconds of the oldest available job in `database.tsv`. Short bursts can happen between samples. The lab worker sleeps for one second when it finds an empty queue, which can put most of a second between acceptance and the next pickup even without a sustained backlog. [Laravel worker sleep duration](https://laravel.com/docs/13.x/queues#worker-sleep-duration)

## Local measurements

The lab ran on an Apple M3 Pro with PostgreSQL 18.4, four PHP-FPM workers limited to one CPU in total, and one database queue worker. The table shows the median and range of each mode’s three run-level p95s. Each 12-second run offered 20 workflows per second, verified 24 identical 661,561-byte CSVs, and finished with zero failures or drops and an empty queue. The per-run data is in `code/bang-for-buck/bench/chapter-08-measurements.tsv`.

| Measure | Synchronous | Queued |
| --- | ---: | ---: |
| Export POST p95 | 84.63 ms (71.66–258.42) | 16.09 ms (14.43–20.35) |
| Queue wait p95 | — | 931.28 ms (921.74–968.03) |
| Worker processing p95 | — | 34.23 ms (27.32–46.86) |
| Time to complete file p95 | 85.55 ms (71.80–260.70) | 1,077.70 ms (1,075.85–1,086.00) |
| Status requests per run, median and range | 0 | 82 (82–86) |

At this rate the queued POST returned in about a fifth of the time, and the synchronous path delivered the file sooner. Worker pickup accounted for much more waiting than generation. The queued path also added roughly 3.4 status checks per export plus one separate download. If you tune the worker sleep or polling interval, measure both user wait and the extra database or HTTP traffic.

The median catalogue p95 was 25.67 ms inline and 17.17 ms with the queue. Checkout was 58.57 ms inline and 49.78 ms with the queue. Both ranges overlapped. An earlier synchronous attempt at 30 workflows per second dropped 69 iterations and had a sampled PHP-FPM listen queue of 40. It appears in the data as a separate overload observation. The paired comparison uses the lower rate so both modes complete the offered work.

## Exercise – Restart the worker and replay a submission

With the lab idle, stop the worker and submit a fresh queued export twice with the same reference. Keep the status URL from the first response. Expect HTTP 202, then HTTP 200 with the same ID. The job stays pending during the stop, and the file endpoint returns HTTP 404 until the record is ready.

```bash
retry="{\"run_id\":\"$run\",\"reference\":\"restart\",\"category_id\":21}"
docker compose stop worker
for attempt in 1 2; do
    curl -fsS -H 'Content-Type: application/json' -d "$retry" "$shop/api/exports" \
        -D "$files/restart-$attempt.headers" -o "$files/restart-$attempt.json" -w 'HTTP %{http_code}\n'
    cat "$files/restart-$attempt.json"
done
status_path=$(awk 'tolower($1) == "location:" { sub(/\r$/, "", $2); print $2 }' "$files/restart-1.headers")
status_url="$shop$status_path"
curl -fsS "$status_url"
curl -sS -o /dev/null -w 'File HTTP %{http_code}\n' "$status_url/file"
docker compose up -d --wait worker
```

Repeat the polling and download commands to check that the queued request survives the restart and yields the same file as the inline request. For a job interrupted during processing, the database queue’s `retry_after` controls when its reservation can expire. The lab gives the worker 20 seconds to run a job and the reservation 45 seconds. Keep the worker timeout below the reservation timeout so a retry can pick up after the earlier attempt has stopped. [Laravel worker and job timeouts](https://laravel.com/docs/13.x/queues#job-expirations-and-timeouts)
