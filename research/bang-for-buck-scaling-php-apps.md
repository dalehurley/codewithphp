# Bang for Buck: Scaling PHP Apps

A practical Code with PHP series for developers who already ship PHP or Laravel applications. Readers start with a slow endpoint and a deployment bill. They learn to measure the problem, choose the smallest change that could solve it, and test whether it improves latency, capacity or cost while preserving the application's behaviour.

## The application and the measurement loop

Use one small Laravel 13 shop with a product catalogue, order submission and a background CSV export. Start with PHP 8.5, PHP-FPM, OPcache and PostgreSQL. Use a supported patched PHP build and pin the lab's container images, framework packages and tools when building the examples. A deterministic seed and a simulated upstream service make the same traffic mix replayable. Add Redis, Octane and extra instances in the chapters that test them. Each lab uses local containers or a disposable test environment. The costing exercises use example rates so readers can work through them without buying hosting. [PHP support schedule](https://www.php.net/supported-versions.php) · [Laravel release and PHP support matrix](https://laravel.com/docs/13.x/releases)

Start with three different promises to users. For the lab, catalogue p95 under 250 ms, order submission p95 under 500 ms, and 95% of accepted exports ready within 60 seconds are **illustrative targets**. Track unexpected HTTP failures, invalid outputs, lost or duplicate jobs, and saturated resources alongside latency. Record route-level p99 where the sample is large enough to make it useful. For queue jobs, measure from acceptance through completion and break that time into wait and execution. [Google SRE on latency, traffic, errors and saturation](https://sre.google/sre-book/monitoring-distributed-systems/)

Every lab uses the same sequence.

1. Write down the symptom, the user-facing target and the proposed change. Choose a measurement that can prove the change helped.
2. Capture latency, completed and dropped work, errors, resource use, and an output or data integrity check. Record the traffic mix, dataset, cache state, versions, resource limits, test duration and infrastructure assumptions.
3. Change one variable. Run repeatable baseline and candidate trials, alternating their order where practical. Separate cold and warm runs and keep raw results. Record enough repetitions and samples to interpret the spread. Check the load generator can sustain the offered rate.
4. Keep the change if it improves the target metric and meets correctness and reliability requirements. Record engineering effort, new operating costs, and the change you would roll back if the production result differs. Revisit the baseline after deployments, traffic shifts and cost changes. [k6 thresholds](https://grafana.com/docs/k6/latest/using-k6/thresholds/) · [k6 dropped iterations](https://grafana.com/docs/k6/latest/using-k6/scenarios/concepts/dropped-iterations/) · [Google SRE on canary releases](https://sre.google/workbook/canarying-releases/)

## Where to start

Use the first plausible experiment for the bottleneck you can see. The order below is a starting point for diagnosis. A confirmed bottleneck or a failing service target determines the next experiment.

| Evidence | First experiment | Decision metric |
| --- | --- | --- |
| SQL calls grow with the number of rows returned | Select and load the required data in fewer queries | Queries per request, database time, identical records |
| A small number of queries dominate database time | Inspect the plan and test a targeted index or query change | Read latency, rows examined, write cost |
| Identical reads repeatedly calculate the same result | Cache an eligible result or serve a cacheable HTTP response | Origin work avoided, freshness, hit rate, extra service cost |
| Requests spend most of their time waiting on another service | Set a deadline, overlap independent calls or defer work | End-to-end p95, upstream call volume, completion time |
| PHP consumes CPU or runs out of memory | Profile a representative request, then change the hot path or batch size | CPU time, peak memory, equal results |
| PHP workers queue with available database and memory capacity | Right-size workers or test a persistent runtime | Capacity at the same service target, memory, operating cost |
| A burst saturates a shared dependency | Cap concurrency or test a scaling rule against downstream limits | Accepted work, p95, queue age and recovery time |

## Part 1 Measure the app

### 01 Set a baseline and define what fast means

Instrument the shop's three workflows. Build a baseline card with p50 and p95 by route, offered and completed rates, errors, CPU, memory, database connection use and queue age. Define the maximum **tested** throughput that meets the targets over a named steady-load window. Record the monthly bill components and use the same traffic mix for every comparison. [Google SRE monitoring](https://sre.google/sre-book/monitoring-distributed-systems/) · [FinOps Foundation unit economics](https://www.finops.org/framework/capabilities/unit-economics/)

### 02 Reproduce steady traffic, bursts and cold starts

Write a k6 workload and response checks. Use a fixed arrival rate for comparisons at equal demand and a fixed pool of virtual users to show how a closed test can issue less traffic when responses slow. Test a steady plateau, a burst and a cold restart separately. Report dropped iterations alongside errors and successful throughput. Find the first load step that breaches a target and show how the app recovers. [k6 open and closed models](https://grafana.com/docs/k6/latest/using-k6/scenarios/concepts/open-vs-closed/) · [k6 dropped iterations](https://grafana.com/docs/k6/latest/using-k6/scenarios/concepts/dropped-iterations/)

### 03 Find out where a slow request spends its time

Trace one catalogue request across PHP execution, SQL and outbound HTTP. Use Laravel Pulse for trends, Laravel's database hooks for cumulative query time, and a triggered Xdebug profile to inspect a hot path in development. Introduce PHP's monotonic `hrtime()` for narrow timing. Measure the comparison without the diagnostic profiler so profiling overhead stays out of the result. Deliver a short list of bottlenecks supported by traces or profiles. [Laravel Pulse](https://laravel.com/docs/13.x/pulse) · [Laravel database monitoring](https://laravel.com/docs/13.x/database#monitoring-cumulative-query-time) · [Xdebug profiler](https://xdebug.org/docs/profiler) · [PHP hrtime](https://www.php.net/manual/en/function.hrtime.php)

## Part 2 Remove work on the current stack

### 04 Cut avoidable work on every deploy

Build an equivalent Laravel deployment with debug disabled and cached configuration, routes, events and views. Verify OPcache in the PHP runtime serving HTTP. Explain what Laravel's deploy caches and PHP's compiled bytecode cache each save. Compare cold startup and warm requests and rehearse cache and worker refresh during release. Read environment values in configuration files and use `config()` in application code. Account for the default cache keys cleared by `optimize:clear` when rehearsing rollback. [Laravel deployment optimisation](https://laravel.com/docs/13.x/deployment#optimization) · [PHP OPcache](https://www.php.net/manual/en/intro.opcache.php)

### 05 Ask the database for less

Turn an N+1 catalogue request into a bounded number of queries using eager loading or a join. Select the fields the response needs and compare limited pages, cursor pagination for a stable ordering, and oversized payloads. Compare database time, query count, response bytes and model hydration cost. Check records, ordering and access rules across variants. [Eloquent eager loading](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading) · [Laravel cursor pagination](https://laravel.com/docs/13.x/pagination#cursor-pagination)

### 06 Make the remaining SQL cheaper

Use a seeded, realistically sized orders table to compare PostgreSQL plans and row counts before and after one query or index change. Show how a composite index may help a selective read while adding storage and write work. Run `EXPLAIN ANALYZE` on safe test queries because it executes the statement. Add a concurrent-reader and writer check to surface lock waits and connection saturation. [PostgreSQL EXPLAIN](https://www.postgresql.org/docs/18/sql-explain.html) · [PostgreSQL index trade-offs](https://www.postgresql.org/docs/18/indexes-intro.html) · [PostgreSQL connection limits](https://www.postgresql.org/docs/18/runtime-config-connection.html)

### 07 Cache data with an expiry and an owner

Cache an expensive public category summary. Give it a clear key, freshness window and invalidation rule. Replay an expiry burst and compare direct regeneration with a Laravel atomic lock or stale-while-revalidate where stale data is acceptable. Measure hit ratio, misses at peak, fallback load, latency and Redis costs. Exercise eviction and cache loss, and check tenant boundaries for any private cache key. [Laravel cache and atomic locks](https://laravel.com/docs/13.x/cache) · [Redis eviction](https://redis.io/docs/latest/develop/reference/eviction/)

### 08 Move work beyond the response

Generate the same CSV inline and in a Laravel database queue worker. Record how long the POST takes, when the job starts and when the user receives the finished file. Commit the request and queue row in one transaction. Check idempotent retries, restart the worker with a pending job and quantify the polling load. [Laravel queues](https://laravel.com/docs/13.x/queues)

### 09 Serve fewer bytes and send fewer requests to PHP

Compare versioned static assets, public catalogue responses with explicit freshness, and conditional revalidation. Verify cache keys, `Vary`, and authenticated response handling before enabling a shared cache. Measure browser-facing latency, origin requests and transfer bytes. Show when a CDN, smaller payload or compression earns its cost, including compression CPU and cache-miss traffic. [RFC 9111 HTTP caching](https://www.rfc-editor.org/rfc/rfc9111.html)

### 10 Set deadlines for slow dependencies

Use a simulated shipping service to test connect and response timeouts, bounded retries with jitter, explicit failure behaviour, and a concurrency-limited request pool for independent calls. Replay a partial outage and compare tail latency, upstream call volume and correct orders. Tie retries to a time budget and to whether the operation can safely be repeated. [Laravel HTTP client](https://laravel.com/docs/13.x/http-client) · [Amazon Builders' Library on retries and backoff](https://aws.amazon.com/builders-library/timeouts-retries-and-backoff-with-jitter/)

## Part 3 Tune PHP and measure the runtime change

### 11 Profile CPU and bound memory in plain PHP

Extract the CSV transformation into a plain-PHP lab. Compare a materialised array against streaming or bounded batches and run a size sweep with equal output. Measure CPU, wall time, peak process memory and completed records per second. Trace memory beyond the PHP loop, including buffering by the driver and buffering at response boundaries. In the Laravel version, test `lazyById()` for large database scans and discuss how PDO buffering can limit the benefit of `cursor()`. [PHP generators](https://www.php.net/manual/en/language.generators.overview.php) · [Eloquent chunking and cursors](https://laravel.com/docs/13.x/eloquent#chunking-using-lazy-collections)

### 12 Right-size PHP-FPM and OPcache

Watch FPM's listen queue and the worker limit during a ramp. Estimate how many workers fit by measuring the pool's memory under load, including shared memory and headroom. Bound concurrency by CPU and database capacity as well. Check OPcache's status and compare warm, restart and deploy behaviour. Treat preloading or JIT as separate trials for a specific boot or CPU bottleneck, and record their memory and restart costs. [PHP-FPM pool configuration](https://www.php.net/manual/en/install.fpm.configuration.php) · [FPM status](https://www.php.net/manual/en/fpm.status.php) · [OPcache status](https://www.php.net/manual/en/function.opcache-get-status.php) · [Preloading](https://www.php.net/manual/en/opcache.preloading.php) · [JIT settings](https://www.php.net/manual/en/opcache.configuration.php)

### 13 Compare persistent workers with your baseline

Reproduce the classic and worker comparison within the same FrankenPHP build, then compare the viable deployment options on equal resource budgets. Use the full shop traffic mix, repeat warm runs and exercise deploys under load. Check response equivalence, cross-request state, memory growth, worker recycling and recovery. Record any ops burden from the switch alongside sustainable throughput and resource use. [Laravel Octane lifecycle](https://laravel.com/docs/13.x/octane) · [FrankenPHP performance tuning](https://frankenphp.dev/docs/performance/)

## Part 4 Scale within a budget

### 14 Find the next limit when you add instances

Compare one larger instance with multiple smaller ones. Verify sessions, uploads, caches and scheduled work where the app runs behind a load balancer. Budget database connections across maximum web replicas, queue workers and administrative headroom. Try pooling or replicas only for the bottleneck they address. Exercise a read after a write on a replica to demonstrate the need for a consistency decision. [Laravel sessions](https://laravel.com/docs/13.x/session) · [Laravel filesystem](https://laravel.com/docs/13.x/filesystem) · [PostgreSQL hot standby](https://www.postgresql.org/docs/18/hot-standby.html)

### 15 Test autoscaling, idle capacity and overload

Replay an expected peak and a larger burst against fixed capacity and autoscaling. Check whether new instances become available in time, whether the database and queue can absorb them, and what happens when requests exceed the intended limit. Include a demand drop and recovery. Estimate idle cost and cold-start delay when considering scale to zero. Use Laravel Cloud as a concrete managed example and price the same workload on each option, including minimum replicas, workers, storage and egress. [Laravel Cloud compute controls](https://laravel.com/cloud/docs/compute) · [Laravel Cloud pricing dimensions](https://laravel.com/cloud/pricing) · [Google SRE handling overload](https://sre.google/sre-book/handling-overload/)

### 16 Choose the cheapest configuration that meets the targets

Run the same benchmark on the starting deployment and the two best candidates. Use the cost model below to choose a configuration for the expected demand window and explain the stage at which it will need more capacity. Produce a short deployment decision with baseline and candidate results, an operating cost estimate, a rollback trigger and regression checks for future releases. Use deterministic checks for query counts and output correctness in regular CI, with comparative latency runs on a controlled environment. [FinOps Foundation unit economics](https://www.finops.org/framework/capabilities/unit-economics/) · [Google SRE canarying releases](https://sre.google/workbook/canarying-releases/)

## Cost model used throughout the series

Compare the same amount of correct work at the same service targets. Count only expected, correct responses for the successful-request denominator and report incorrect, failed and dropped work alongside it. Track completed orders or exports separately when they better reflect the app's value. Use the same month, demand and accounting scope for both configurations. Include web and worker compute, databases, caches, storage, backups, network and observability charges, plus ongoing engineering and support time. Track one-time changes separately for payback. [FinOps Foundation unit economics](https://www.finops.org/framework/capabilities/unit-economics/)

- **Operating cost per million successful requests** equals monthly operating cost divided by monthly successful requests, multiplied by one million.
- **Incremental cost of capacity** equals the difference in monthly operating cost divided by the gain in tested peak requests per second at the same targets. Use it when the decision is about capacity and the gain is positive.
- **Payback in months** equals the upfront engineering cost divided by verified monthly savings. If bills stay the same, show the extra headroom separately from realised savings.

For an illustrative month with 10 million successful requests, a deployment that costs AUD 150 for infrastructure and AUD 100 for staff time costs AUD 25 per million. Suppose a query change supports the same peak on a smaller database and a billing estimate confirms infrastructure can fall to AUD 110. If ongoing staff time rises to AUD 110, the new total is AUD 220, or AUD 22 per million. An upfront AUD 180 engineering change pays back in six months if the smaller database is adopted and the AUD 30 monthly savings persist. Keeping the original infrastructure would make the monthly total AUD 260. Its extra capacity may still be valuable, and its value depends on when it avoids a later upgrade.

## Existing material and editorial scope

Use the small [PHP Misconceptions performance lab](../src/content/docs/series/php-misconceptions/chapters/03-laravel-performance.md) to introduce controlled comparisons. Its observed throughput belongs to the local warm SQLite lab. The new course uses a database-backed traffic mix, explicit service and correctness targets, and a cost model. Point readers to [PHP Algorithms benchmarking](../src/content/docs/series/php-algorithms/chapters/02-benchmarking-performance-testing.md) for more on microbenchmarks. Build the shared app and traffic harness alongside the first two chapters so the later chapters can reuse their baseline evidence.
