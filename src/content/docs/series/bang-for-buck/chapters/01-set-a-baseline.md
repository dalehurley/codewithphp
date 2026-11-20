---
title: "01: Set a Baseline for a Laravel App"
description: "Run a small Laravel shop and measure latency, correct completed work and resource use before you optimise it."
series: "bang-for-buck"
chapter: 1
difficulty: "Intermediate"
estimatedTime: "PT25M"
sidebar:
  order: 1
---

## Overview

The catalogue, checkout and exports make different promises to users. A catalogue page should arrive quickly. An order should save the right charge and shipping cost. An accepted export should become a usable file within a known window. Your baseline should capture all three.

You’ll start a repeatable shop, check each workflow, and measure it at a fixed offered rate. Keep the figures and the environment details together. A later change has a useful comparison only if you know what ran on each side. [Google SRE on latency, traffic, errors and saturation](https://sre.google/sre-book/monitoring-distributed-systems/)

## Prerequisites

Open a terminal at your local Code with PHP checkout on Linux or macOS. Install Docker with the Compose plugin, Bash and curl. Verify the engine is running.

```bash
docker info --format '{{.ServerVersion}}'
docker compose version
curl --version
```

Allow about 25 minutes, including the first image build and downloads. Laravel, PHP-FPM, PostgreSQL and k6 run in containers.

## Quick Start

From the repository root, enter the [shop lab directory](https://github.com/dalehurley/codewithphp/tree/main/code/bang-for-buck), build the image, and seed the database before starting the web app and worker.

```bash
cd code/bang-for-buck
docker compose build php
docker compose up -d --wait db redis
docker compose run --rm php php artisan migrate --seed --force
docker compose up -d --wait php web worker shipping
docker compose --profile bench pull k6
curl -fsS http://127.0.0.1:8088/api/lab
```

The JSON should identify the lab as `bang-for-buck-shop` and report `fpm-fcgi`, `opcache: true`, and `pgsql`. The standard seed starts with 20 categories and 5,000 products. The query lab in Chapter 5 adds one optional category. The web port is bound to loopback. If port 8088 is occupied, export `SHOP_PORT=8089` before starting the stack and use that port for curl.

The product IDs, categories and prices come from a deterministic seed. The checkout calls a simulated shipping service inside Compose. A single database queue worker builds each CSV and saves it in a shared local volume.

## Step 1 – Define a pass

Use these illustrative targets for the shop. p95 is the latency at or below which 95% of the measured responses finish. Track unexpected responses, incorrect results and skipped work alongside latency.

| Workflow | Target | Correctness check |
| --- | --- | --- |
| Catalogue | HTTP p95 below 250 ms | The expected 20 products, in order, with matching categories and prices |
| Order | HTTP p95 below 500 ms | A new saved order with the expected product, quantity, shipping charge and total |
| Export | At least 95% observed ready within 60 seconds | An accepted export finishes with all 250 products in that category |

The harness also checks that fewer than 1% of workflows fail overall and on orders and exports individually. An export that finishes after 60 seconds but within the observation window can be correct and late. Open-model runs fail their gate if k6 drops any scheduled iterations. [k6 thresholds](https://grafana.com/docs/k6/latest/using-k6/thresholds/)

## Step 2 – Check the workflows and save the baseline

Run the smoke profile once to exercise all three workflows and warm the application. Then schedule about 20 workflows per second for 30 seconds.

```bash
bash bench/run.sh smoke
RATE=20 DURATION_SECONDS=30 bash bench/run.sh steady
```

The workload cycles through 16 catalogue reads, three order submissions and one export request with status polling. An export workflow makes several HTTP requests. Use workflow counts for the traffic mix and the HTTP request count for network demand. The smoke profile shows that all three paths work. Use the longer run to establish latency.

Both commands print the results directory. The steady run should produce roughly 600 workflows. Check the actual count and the thresholds in the summary. Open the saved results for that run.

| File | What to record |
| --- | --- |
| `summary.json` | Started and successful workflows, dropped iterations, HTTP calls, per-workflow latency, export wait and processing time, threshold outcomes |
| `persisted.json` | Saved orders and export states for this run ID, plus current queue depth and failed jobs for the lab |
| `fpm.tsv` and `database.tsv` | FPM queue and active processes, PostgreSQL connections and the queue backlog at each sample |
| `resources.tsv` and `environment.txt` | Container CPU and memory samples, versions, limits and workload settings |

Compare the `shop_orders_confirmed` counter with the saved order count. Compare `shop_exports_accepted` and `shop_exports_seen_ready` with the export states. If a client timed out after the database committed, the persisted count can be higher than the client-confirmed count. That difference tells you which part of the workflow needs investigation.

One local 30-second run at an offered rate of 20 workflows per second completed 601 workflows with zero drops. PostgreSQL recorded 90 orders and 31 ready exports. Catalogue p95 was 20 ms and order p95 was 56 ms. The Docker VM ran on an Apple M3 Pro with two virtual CPUs, and the PHP-FPM container had a one-CPU limit and four workers. Use these figures to see how the measurements fit together, and record the results from your machine.

## Exercise

Run the same steady test twice more. Write down the median and range of the three catalogue p95 values, the actual successful and dropped counts, and the highest FPM queue depth you observed. Keep all three result paths. Your baseline is for this traffic mix, machine and application state.

List what you pay per month for web compute, the worker, database, storage and operating time. Divide the total by your monthly successful requests and multiply by one million. On the same workload, a lower p95 may create headroom. Count it as a bill saving once you can reduce what you pay for. [FinOps Foundation unit economics](https://www.finops.org/framework/capabilities/unit-economics/)

## Troubleshooting

- If the lab endpoint returns a database error, check `docker compose logs db php` and confirm the migration and seed completed on this Compose project.
- If export requests stay pending, check `docker compose logs worker` and confirm the worker is running. A working web process can accept an export while the queue grows.
- If the harness reports a threshold failure, keep the results directory. The failed run is evidence of what happened at that load.

## Next chapter

[Run traffic profiles and find your first limit](/series/bang-for-buck/chapters/02-test-realistic-traffic/)
