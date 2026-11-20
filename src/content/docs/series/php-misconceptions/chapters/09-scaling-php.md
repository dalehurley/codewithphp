---
title: "09: Can PHP Scale?"
description: "Share state across Laravel replicas, calculate a database-aware worker limit and decide what to measure before scaling."
series: "php-misconceptions"
chapter: 9
estimatedTime: "PT25M"
sidebar:
  order: 9
---

## Overview

Your Laravel app gets slow during the morning rush. You can add another copy of the same app behind the load balancer. Before deciding how many workers to run, check two things. Customers need to see the same data from any instance, and the shared database needs room for the extra connections. [Laravel deployment](https://laravel.com/docs/13.x/deployment) · [Laravel Cloud compute](https://cloud.laravel.com/docs/compute)

## Keep requests interchangeable

The load balancer can send a customer’s sign-in and upload to instance A, then send the next request with the same session cookie to instance B. Both copies run the same release. B needs to read the session and retrieve the upload. Deploy code and versioned public assets with each instance. Keep data written at runtime in services that every instance can reach.

| What needs to survive another request? | Where it can live | What to look for during a replica test |
| --- | --- | --- |
| Login and session data | A shared Redis or database session store | A signed-in customer stays signed in on B. |
| Uploaded files | Shared object storage through a Laravel disk | B can retrieve the file A wrote. |
| Durable application records | A shared database with the appropriate consistency for the request | B can read a record A just committed. |
| Cached results or shared locks | A common cache backend with matching keys and prefixes | An invalidation or shared lock on A also takes effect on B. |
| Report jobs and results | A shared queue, plus shared storage for finished reports | A job submitted on either instance can finish and its result is available on both. |

Use the same `APP_KEY` within a deployment so each replica can decrypt the encrypted session cookie. Keep the session cookie settings and shared store configuration consistent. For a shared cache, choose keys and expiry based on whose data can be reused and how fresh it must be. Preserve the `no-store` policy for the private invoice response from Chapter 8. [Laravel sessions](https://laravel.com/docs/13.x/session) · [Laravel encryption](https://laravel.com/docs/13.x/encryption) · [Laravel filesystem](https://laravel.com/docs/13.x/filesystem) · [Laravel cache](https://laravel.com/docs/13.x/cache) · [Private invoice response](/series/php-misconceptions/chapters/08-php-security/)

In staging, use the same public host and confirm which instance served each request through routing controls or request logs. Drive requests to both instances without relying on session affinity. Laravel’s health route checks that the app boots by default. Walk through these requests to check the shared services too. [Laravel’s health route](https://laravel.com/docs/13.x/deployment#the-health-route)

1. Sign in and upload a test file on A. With the same session, retrieve it on B and compare the bytes.
2. Open a private invoice as its owner on B. Expect HTTP 200 and the fixture invoice. Use a separate session for another signed-in customer and request the exact same URL on B. Expect 404 and no private invoice data, following Chapter 8’s policy.
3. If you share cache entries, write a test value on A and read it on B. Invalidate it on B, then check that it is absent on A. If you share locks, hold the same test lock on A and confirm an immediate acquisition on B fails until A releases it.
4. Submit a report from A and record its export ID. Wait for completion, then use that ID on A and B. Expect the same completed record and file from either instance.

## Run the capacity model

From the checkout root, run the standalone example with PHP 8.6, the series baseline. The script was also verified on PHP 8.5.0. It uses fictional inputs and calculates the estimates locally. [Capacity model source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/scaling-capacity.php)

```bash
php code/php-misconceptions/scaling-capacity.php
```

```text
Configured web slots: 24
Worker-only ceiling: 240.0 requests/s
Database budget for web: 19 connections
Configured slots above budget: 5
Equal cap within the budget: 6 workers per instance (18 slots)
Ceiling at that cap: 180.0 requests/s
```

The defaults are three instances, eight worker slots per instance, 100 milliseconds of average worker time per request, 25 total database connections and six reserved outside the web tier. In a single PHP-FPM pool, `pm.max_children` caps the number of requests handled at the same time. For the model, one request occupies one slot. Busy time includes the work and waits that occupy that slot after the worker begins. Client p95 latency can also include time spent waiting for a worker. [PHP-FPM pool configuration](https://www.php.net/manual/en/install.fpm.configuration.php)

1. Multiply instances by slots per instance. Three times eight gives 24 slots. Each idealised slot takes 0.1 seconds per request, so 24 ÷ 0.1 gives the worker-only ceiling of 240 requests per second.
2. Subtract connections reserved outside web. `25 − 6 = 19`. In this scenario a busy web slot may hold one database connection at peak. All 24 slots can therefore require five more connections than the 19 available. Size the real reserve for your database’s own reserved slots, administration, queue workers, scheduled work and other consumers. [PostgreSQL connection settings](https://www.postgresql.org/docs/current/runtime-config-connection.html)
3. Divide the web connection budget by the number of identical instances and round down. The equal cap is `floor(19 ÷ 3) = 6` workers per instance, up to the configured worker count. That uses 18 connections at full web concurrency and leaves one in the web budget. At 100 ms, the corresponding ceiling is `18 ÷ 0.1 = 180` requests per second.

To try a different setup, supply all five values in the order `instances workers_per_instance busy_ms total_connections reserved_connections`.

```bash
php code/php-misconceptions/scaling-capacity.php 3 6 100 25 6
php code/php-misconceptions/scaling-capacity.php 3 6 100 25 25
php code/php-misconceptions/scaling-capacity.php 3 6 100 25 23
```

The first run shows 18 configured slots, zero slots above budget and the same 180.0 requests/s at the cap. The second leaves zero database connections for web. The third leaves two, fewer than the three replicas. In both cases a cap of zero means there is no positive equal allocation under the model. Revisit the connection budget, replica count or connection use before configuring the workers. The script accepts decimal integers from 0 to 9999 without leading zeros. Use positive numbers for the first four inputs. The reserve may be zero through the total. Invalid input prints an error to standard error and exits with status 1.

The result assumes equal traffic, constant average busy time at that load and one database connection per busy web slot. Persistent connections, idle workers holding connections, requests using several databases and pools that multiplex connections can change how client connections translate to database sessions. Check the number of open and active connections at the database and pool. Use the calculated cap as a candidate for a staging test. A database can saturate on CPU, I/O or locks before its connection limit. [PostgreSQL monitoring statistics](https://www.postgresql.org/docs/current/monitoring-stats.html)

## Verify capacity before adding replicas

Suppose you need to sustain 140 offered HTTP requests per second with p95 below 250 ms and fewer than 1% failed requests. Use staging and test accounts. An example mix is 80% signed-in reads, 15% writes to fixtures and 5% export submissions. Keep data volume, dependencies and background queue workload comparable before and after changing the worker cap or replica count.

1. Configure a load tool such as k6 to start requests at 100, 120 and 140 per second in separate trials. With the `constant-arrival-rate` executor, use one HTTP request per measured iteration and prepare sign-ins before the run. The executor counts iterations, so requests that follow redirects or make several HTTP calls change the resulting request rate.
2. Warm up for one minute, then measure for two minutes at each rate. Repeat each rate three times. Let in-flight requests and test jobs drain before another trial. Keep the warm-up and measurement results separate.
3. Record requested and achieved start rates, completed requests (including their latency and errors) and dropped iterations separately. Use outcomes from the same measured group of requests. A run where the generator misses the target rate cannot establish a pass at that rate. Check that queued or in-flight work stays bounded during the test. Leave headroom for bursts and for a replica to be out of service.

Chapter 3 shows how to report a fixed-concurrency loopback benchmark. Its p95 includes end-to-end request latency and cannot supply the mean worker busy time in this model. Measure worker occupancy on your target stack. [k6 constant arrival rate](https://grafana.com/docs/k6/latest/using-k6/scenarios/executors/constant-arrival-rate/) · [Chapter 3 measurement lab](/series/php-misconceptions/chapters/03-laravel-performance/)

| Observation as traffic rises | What to check before changing capacity |
| --- | --- |
| Web slots fill and requests wait while the database has spare capacity | Check CPU and memory per web instance. Test more web capacity while keeping the shared connection budget in view. |
| Connections or pool waits climb with database load | Inspect slow queries and connection use. Test a lower web cap or reduce time spent holding a connection. Recheck database pressure before adding web replicas. |
| Reports wait longer while the web targets still pass | Measure how long the oldest job has waited and how fast jobs finish. Add queue workers only if the shared database and other services have room. |

For a report that can finish after the HTTP response, save an export record and submit its ID to the queue after the database transaction commits. A separate worker can generate it and publish the result to shared storage. Give each attempt a unique file key. After writing the file, update the export’s result pointer atomically only if it is still empty. Readers use the key on the export record. If another attempt already published a result, keep that result and discard the losing attempt’s own file once you know its key was never published.

Test a retry after the first attempt publishes its result and before it acknowledges the job. The retry should return the same result key and leave the original file intact. Give downstream effects such as email their own duplicate-handling strategy. This lets you scale queue workers separately while still counting their database use. [Laravel queues and database transactions](https://laravel.com/docs/13.x/queues#jobs-and-database-transactions)

## Exercise

Run the three commands below. For each, compare the capped result to the 140 requests/s staging target and identify what changed.

```bash
php code/php-misconceptions/scaling-capacity.php 4 6 100 25 6
php code/php-misconceptions/scaling-capacity.php 3 6 150 25 6
php code/php-misconceptions/scaling-capacity.php 2 6 100 25 6
```

<details>
<summary>Check your reasoning</summary>

1. With four replicas and the same budget, the equal cap falls to four slots on each instance. That gives 16 slots and a ceiling of 160.0 requests/s. Dividing the fixed budget into equal whole counts changes how much of it you use. Validate latency, errors and spare capacity before choosing this setup.
2. With three replicas and 150 ms per request, the cap still allows 18 slots, but the ceiling falls to 120.0 requests/s. At these inputs, the target exceeds the modelled ceiling. Find what lengthened the time on the worker before assuming another replica will help.
3. With one of the original three replicas out of service and six workers on each survivor, the ceiling also falls to 120.0 requests/s. Check that the service can handle its intended traffic during a rollout or outage. Also count old and new instances if they overlap during deployment, since both may have open database connections.

</details>

## Next chapter

[Can PHP Do Concurrency?](/series/php-misconceptions/chapters/10-php-concurrency/)
