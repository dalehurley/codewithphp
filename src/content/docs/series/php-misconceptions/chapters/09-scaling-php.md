---
title: "09: Can PHP Scale?"
description: "Share state across Laravel replicas, calculate a database-aware worker limit and decide what to measure before scaling."
series: "php-misconceptions"
chapter: 9
estimatedTime: "PT25M"
sidebar:
  order: 9
---

<span id="overview"></span>

## Would a fourth replica help?

Your Laravel invoicing app slows down during the morning rush. Three replicas run the same app behind a load balancer. The team needs to serve 140 incoming HTTP requests per second, even while one replica is out of rotation during a release. The goal is a p95 below 250 ms (95% of requests finish within that time) and fewer than 1% failed requests.

A fourth replica sounds like an easy win. It can also open more connections to the same database. First check that requests can move between replicas without losing state. Then work out how many requests the database budget can support at once and test a candidate with one replica drained. [Laravel deployment](https://laravel.com/docs/13.x/deployment) · [Laravel Cloud compute](https://cloud.laravel.com/docs/compute)

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

Use two routable staging replicas running the same release, seeded owner and non-owner accounts, and a persisted test invoice. Use your app’s sign-in flow. In each check, keep the same staging hostname and confirm which replica served the request through routing controls or request logs. Allow a customer’s requests to reach either replica. Laravel’s health route checks that the app boots by default. The following checks also cover the services both replicas use. [Laravel’s health route](https://laravel.com/docs/13.x/deployment#the-health-route)

1. Sign in and upload a test file on A. With the same session, retrieve it on B and compare the bytes.
2. Commit a new fixture record on A. Immediately fetch it by ID on B and check its fields. Use a route that promises the latest committed data.
3. Through the staging proxy, open a private invoice as its owner on B. Expect HTTP 200 and the fixture invoice. In a separate session, have another signed-in customer request the same URL on B. Expect 404 with no private invoice data. Check for both `private` and `no-store` in the `Cache-Control` header on each response.
4. If you share cache entries, write a test value on A and read it on B. Invalidate it on B, then check that it is absent on A. If you share locks, hold the same test lock on A and confirm an immediate acquisition on B fails until A releases it.
5. Submit a report from A and record its export ID. Wait for completion, then use that ID on A and B. Expect the same completed record and file from either instance.

## Run the capacity model

Once requests can move between replicas, work out how many PHP workers can share the database. For the local exercise, run the standalone calculator from the repository root with PHP 8.6, the series baseline. It also runs on PHP 8.5.0 and needs only the PHP CLI. The inputs are fictional and the program performs the arithmetic locally. [Capacity model source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/scaling-capacity.php)

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
2. Subtract connections reserved outside web. `25 − 6 = 19`. In this scenario a busy web slot may hold one database connection at peak. All 24 slots can therefore require five more connections than the 19 available. To set this input for your own deployment, count the database’s reserved slots, peak simultaneous open connections for non-web consumers such as queue workers and scheduled jobs, and a margin for unexpected demand. Count any administrative connections that need ordinary slots. [PostgreSQL connection settings](https://www.postgresql.org/docs/current/runtime-config-connection.html)
3. Divide the web connection budget by the number of identical instances and round down. The equal cap is `floor(19 ÷ 3) = 6` workers per instance, up to the configured worker count. That uses 18 connections at full web concurrency and leaves one in the web budget. At 100 ms, the corresponding ceiling is `18 ÷ 0.1 = 180` requests per second.

To try a different setup, supply all five values in the order `instances workers_per_instance busy_ms total_connections reserved_connections`.

```bash
php code/php-misconceptions/scaling-capacity.php 3 6 100 25 6
php code/php-misconceptions/scaling-capacity.php 3 6 100 25 25
php code/php-misconceptions/scaling-capacity.php 3 6 100 25 23
```

The first run shows 18 configured slots, zero slots above budget and the same 180.0 requests/s at the cap. The second leaves zero database connections for web. The third leaves two, fewer than the three replicas. In both cases a cap of zero means there is no positive equal allocation under the model. Revisit the connection budget, replica count or connection use before configuring the workers. The script accepts decimal integers from 0 to 9999 without leading zeros. Use positive numbers for the first four inputs. The reserve may be zero through the total. Invalid input prints an error to standard error and exits with status 1.

Starting a fourth replica at six workers each would put 24 slots against the same budget of 19. Even at four workers per replica, a four-replica layout drops to 12 slots when one is drained. At 100 ms, that models 120 requests/s, the same as the two survivors in the three-by-six layout.

The result assumes equal traffic, constant average busy time at that load and at most one database connection per web slot. Persistent connections, idle workers holding connections, requests using several databases and pools that multiplex connections can change how client connections translate to database sessions. Check the number of open and active connections at the database and pool. Use the calculated cap as a candidate for a staging test. A database can saturate on CPU, I/O or locks before its connection limit. [PostgreSQL monitoring statistics](https://www.postgresql.org/docs/current/monitoring-stats.html)

## Verify capacity before adding replicas

The three-replica layout at six workers per replica is a starting point. Try it in staging at 100, 120 and 140 requests/s. Keep the release, dataset, dependencies and background queue workload comparable when changing the worker cap or replica count. Record the exact configuration and repeat each rate three times.

### Send traffic at a fixed rate

The template below assumes three JSON endpoints in your staging app. Adapt them to read invoices, write a test record and submit an export. Set `BASE_URL` to the staging origin and `TEST_TOKEN` to a test account’s API token before running it. Use representative accounts and test data for a real trial, and keep writes isolated from customer data. The example treats HTTP 200, 201 and 202 as success for those three operations respectively. The export response also needs a JSON `id` containing a nonempty string. Adapt the checks to your app’s response contract. Have staging include the trial and request headers in its request logs and store them with the export record so you can follow a submission to its file. [k6 constant arrival rate](https://grafana.com/docs/k6/latest/using-k6/scenarios/executors/constant-arrival-rate/)

```javascript
import { check } from 'k6';
import exec from 'k6/execution';
import http from 'k6/http';
import { Counter } from 'k6/metrics';

const starts = new Counter('request_starts');
const { BASE_URL, TEST_TOKEN, RUN_ID } = __ENV;

if (!BASE_URL || !TEST_TOKEN || !RUN_ID) {
  throw new Error('Set BASE_URL, TEST_TOKEN and RUN_ID before running the trial.');
}

export const options = {
  scenarios: {
    invoices: {
      executor: 'constant-arrival-rate',
      rate: Number(__ENV.RATE || 140),
      timeUnit: '1s',
      duration: __ENV.DURATION || '2m',
      preAllocatedVUs: 80,
      gracefulStop: '10s',
    },
  },
};

export default function () {
  const iteration = exec.scenario.iterationInTest;
  const slot = iteration % 20;
  let request = ['GET', '/api/invoices', 200, null];

  if (slot === 19) {
    request = ['POST', '/api/exports', 202, '{}'];
  } else if (slot % 5 === 4) {
    request = ['POST', '/api/test-notes', 201, '{"text":"staging capacity check"}'];
  }

  const [method, path, status, body] = request;
  const tags = { route: path };
  starts.add(1, tags);
  const response = http.request(method, `${BASE_URL}${path}`, body, {
    tags,
    headers: {
      Authorization: `Bearer ${TEST_TOKEN}`,
      'X-Capacity-Run': RUN_ID,
      'X-Capacity-Request': String(iteration),
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    redirects: 0,
    timeout: '5s',
  });
  check(response, {
    'expected response for this route': (response) => {
      if (response.status !== status) return false;
      if (path !== '/api/exports') return true;
      try {
        const id = response.json('id');
        return typeof id === 'string' && id.length > 0;
      } catch {
        return false;
      }
    },
  }, tags);
}
```

Save it as `capacity.js`. Warm up for one minute and start the two-minute measurement immediately after, saving the outputs separately. Set `RATE` to 100 or 120 for the lower-rate trials. Use fresh run IDs and output filenames for each repeat. Let in-flight requests and jobs drain before the next trial.

```bash
RUN_ID=140-r1-warmup RATE=140 DURATION=1m k6 run capacity.js
RUN_ID=140-r1-measured RATE=140 DURATION=2m k6 run --out json=140-r1-measured.json capacity.js
```

This starts with 80 preallocated virtual users. A virtual user stays busy until its iteration completes. At 140 starts/s and a mean iteration duration of 0.5 seconds, about 70 virtual users would be busy on average. Check generator capacity and adjust the allocation after a pilot to allow for variation. Keep one HTTP attempt per iteration. With redirects disabled, a login redirect fails the expected-status check directly. [k6 virtual-user allocation](https://grafana.com/docs/k6/latest/using-k6/scenarios/concepts/arrival-rate-vu-allocation/) · [k6 HTTP request parameters](https://grafana.com/docs/k6/latest/javascript-api/k6-http/params/)

### Read the results

At 140 starts/s for 120 seconds, the nominal count is 16,800 starts. Use the timestamps of the custom `request_starts` samples to check the delivered rate and check `dropped_iterations` for scheduled work that k6 skipped. Check that every started request has an outcome, including requests that finish during the grace period, and that in-flight work stays bounded. Use p95 of `http_req_duration` for the 250 ms threshold in this lab. That metric covers sending the request, waiting and receiving the response. It excludes connection setup. There is one response check per start in a completed run. Count failed checks for timeouts, unexpected statuses and missing export IDs. In the JSON output, group `checks` samples by `data.tags.route` and divide the number with `data.value` equal to 0 by the total for each route. Report these rates alongside the overall rate so a low-volume route’s failures are visible. [k6 check tags](https://grafana.com/docs/k6/latest/javascript-api/k6/check/)

For example, a fictional trial with p95 of 210 ms and 0.4% failed checks still falls short if k6 drops 240 iterations and starts just 16,560 requests. Investigate whether the generator ran out of virtual users or the application slowed down and kept them busy. [k6 metrics](https://grafana.com/docs/k6/latest/using-k6/metrics/reference/) · [k6 dropped iterations](https://grafana.com/docs/k6/latest/using-k6/scenarios/concepts/dropped-iterations/) · [Worked traffic lab](/series/bang-for-buck/chapters/02-test-realistic-traffic/)

To replace the calculator’s 100 ms assumption, log PHP-FPM service time with `%{milliseconds}d` in the pool’s access log. Include `%{HTTP_X_CAPACITY_RUN}e` in the log format if your proxy forwards the trial header to FPM. Across replicas, filter entries by the measured run ID, sum their service times and divide by the number of matching entries. Round up to a whole millisecond before passing the mean to the calculator. Check the FPM listen queue separately. Requests can wait there before any PHP worker picks them up. Chapter 3 shows how to report a fixed-concurrency benchmark and interpret client latency. [PHP-FPM access log configuration](https://www.php.net/manual/en/install.fpm.configuration.php) · [PHP-FPM status metrics](https://www.php.net/manual/en/fpm.status.php) · [Chapter 3 measurement lab](/series/php-misconceptions/chapters/03-laravel-performance/)

| Observation as traffic rises | What to check before changing capacity |
| --- | --- |
| Web slots fill and requests wait while the database has spare capacity | Check CPU and memory per web instance. Test more web capacity while keeping the shared connection budget in view. |
| Connections or pool waits climb with database load | Inspect slow queries and connection use. Test a lower web cap or reduce time spent holding a connection. Recheck database pressure before adding web replicas. |
| Reports wait longer while the web targets still pass | Measure how long the oldest job has waited and how fast jobs finish. Add queue workers only if the shared database and other services have room. |

At the 5% export mix, 140 HTTP requests/s can submit seven jobs per second. If the queue completes five per second, the backlog grows by two jobs per second even when the HTTP targets pass. Give exports their own goal, such as 95% ready within one minute of submission. Use the measured run’s export-route start count as the denominator. Match each recorded submission to its export using the run and request IDs. Count it as ready only if its file is usable within one minute. Missing, failed and late exports count as misses. Allow the last submissions their full minute and check whether the oldest waiting job keeps getting older.

For a report that can finish after the HTTP response, save an export record and submit its ID to the queue after the database transaction commits. A separate worker can generate it and publish the result to shared storage. Give each attempt a unique file key. After writing the file, update the export’s result pointer atomically only if it is still empty. Readers use the key on the export record. If another attempt already published a result, keep that result and discard the losing attempt’s own file once you know its key was never published.

Test a retry after the first attempt publishes its result and before it acknowledges the job. The retry should return the same result key and leave the original file intact. Give downstream effects such as email their own duplicate-handling strategy. This lets you scale queue workers separately while still counting their database use. [Laravel queues and database transactions](https://laravel.com/docs/13.x/queues#jobs-and-database-transactions)

## Exercise

Run the commands below. Compare the four-replica proposal with the three-replica candidate. Can either meet the target when a replica is drained and the surviving worker caps stay fixed? Then use the last two runs to decide what change you would test before approving a rollout.

```bash
php code/php-misconceptions/scaling-capacity.php 4 6 100 25 6
php code/php-misconceptions/scaling-capacity.php 3 6 150 25 6
php code/php-misconceptions/scaling-capacity.php 2 6 100 25 6
php code/php-misconceptions/scaling-capacity.php 2 6 75 25 6
```

<details>
<summary>Check your reasoning</summary>

1. With four replicas and the same budget, the equal cap falls to four workers per replica. That gives 16 slots and a modelled ceiling of 160 requests/s. With one replica drained and four workers per survivor, 12 slots give 120 requests/s.
2. At 150 ms instead of 100 ms, even the three-replica layout at full strength models only 120 requests/s. Find what kept workers busy for longer before changing the replica count.
3. Two surviving replicas with six workers each also model 120 requests/s at 100 ms. Neither layout meets the rollout target at that busy time. Count old and new replicas against the connection budget whenever they can both hold connections.
4. If tuning brings mean busy time down to 75 ms, the two surviving replicas at six workers each model 160 requests/s. Keep three replicas at six workers as the candidate, drain one and stop its worker pool before starting the replacement. Run the 140 requests/s staging trial both at full strength and with one replica drained. Approve the candidate only if both setups meet the HTTP and export targets without a growing backlog or exceeding the connection budget. The 75 ms input is a target to verify in staging.

</details>

## Next chapter

[Can PHP Do Concurrency?](/series/php-misconceptions/chapters/10-php-concurrency/)
