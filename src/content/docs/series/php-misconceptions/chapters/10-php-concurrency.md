---
title: "10: Can PHP Do Concurrency?"
description: "Trace a PHP Fiber, compare serial and concurrent supplier requests, and decide which checkout work can use a queue."
series: "php-misconceptions"
chapter: 10
estimatedTime: "PT25M"
sidebar:
  order: 10
---

<a id="overview"></a>

## What the checkout waits for

Alex clicks **Get quote**. Checkout needs prices from North and South before it can show the total. While Alex waits, Sam opens checkout too. Can PHP overlap the two supplier calls and serve Sam at the same time?

Yes. An HTTP client can overlap the supplier waits within Alex’s request, and PHP-FPM can serve Sam’s request in another child process. Think of each child as one web request slot. Suppose both supplier calls start together and Sam arrives 100 ms later. With one child, Sam still waits until the child finishes Alex’s request. With a second child free, Sam’s request can start immediately. PHP-FPM’s `pm.max_children` caps those slots in a pool. [PHP-FPM configuration](https://www.php.net/manual/en/install.fpm.configuration.php) · [Web slots and shared capacity](/series/php-misconceptions/chapters/09-scaling-php/) · [Laravel HTTP client concurrency](https://laravel.com/docs/13.x/http-client#concurrent-requests)

The quote stays in the response because Alex needs both prices to decide whether to buy. If Alex places the order, checkout can confirm it while a separate queue worker handles delivery of the receipt. To see what can overlap within one PHP process, start by tracing who has control.

## Set up the examples

Open a terminal at the root of a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp). The series targets PHP 8.6. Use PHP 8.5 for this chapter’s walkthrough, which was verified on PHP 8.5.0 with Laravel 13.43.0. The Fiber script requires PHP 8.1 or later. Laravel 13 lists support for PHP 8.3 through 8.5. [PHP Fibers](https://www.php.net/manual/en/language.fibers.php) · [Laravel support policy](https://laravel.com/docs/13.x/releases#support-policy)

For the supplier lab, use Bash or Zsh, Composer 2 and PHP with cURL, PDO SQLite and Laravel’s required extensions. The setup installs a disposable Laravel app and uses SQLite for its default migrations. Check the PHP CLI and extensions before creating the app. [Laravel server requirements](https://laravel.com/docs/13.x/deployment#server-requirements)

```bash
php -v
composer --version
php --ri curl
php --ri pdo_sqlite
```

## Trace a Fiber

A Fiber can pause its current call stack and resume from the same point. In the basic run, the script hands control back and forth like this. [Fiber example source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/fibers.php) · [PHP Fibers](https://www.php.net/manual/en/language.fibers.php)

```php
$fiber = new Fiber(static function (): void {
    echo '1. Fiber starts its work', PHP_EOL;
    Fiber::suspend();
    echo '3. Fiber finishes its work', PHP_EOL;
});

$fiber->start();
echo '2. Caller runs while the fiber is paused', PHP_EOL;
$fiber->resume();
```

```bash
php code/php-misconceptions/fibers.php
```

```text
1. Fiber starts its work
2. Caller runs while the fiber is paused
3. Fiber finishes its work
```

| Call | What runs next |
| --- | --- |
| The caller calls `$fiber->start()` | The Fiber runs up to `Fiber::suspend()` and prints line 1. |
| The Fiber suspends | Control returns to the caller, which prints line 2. |
| The caller calls `$fiber->resume()` | The Fiber continues just after `suspend()` and prints line 3. |

Try the same script with a 200 ms blocking sleep inside the Fiber before it suspends.

```bash
php code/php-misconceptions/fibers.php --blocking
```

`Blocking call starts` and `Blocking call returns` both appear before line 2. The caller gets control only after the sleep returns and the Fiber suspends. A library can pair Fibers with non-blocking I/O and an event loop that checks which connections can make progress. It can then resume the relevant Fiber and let other ready work run between checks. CPU-heavy PHP work needs separate execution resources to run in parallel. [PHP Fibers](https://www.php.net/manual/en/language.fibers.php) · [ReactPHP event loop](https://reactphp.org/event-loop/)

The supplier lab overlaps the network waits through Laravel’s HTTP client and its Guzzle cURL handler. The Fiber trace demonstrates the handoff explicitly. In the lab, the client manages the overlapping transfers and hands the results back to the command. [Laravel HTTP client concurrency](https://laravel.com/docs/13.x/http-client#concurrent-requests) · [Guzzle and cURL](https://docs.guzzlephp.org/en/stable/faq.html#can-guzzle-send-asynchronous-requests)

## Compare two supplier calls

The lab measures the supplier calls for one quote from an Artisan command. It uses synthetic prices and two servers on the same machine. [Supplier lab source](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/concurrency)

Open two terminals at the checkout root. Run the first server in one terminal and the second server in the other. Each server handles one supplier so the default single-process development server can simulate an independent service. Keep both running while you make the comparisons.

```bash
php -S 127.0.0.1:8031 -t code/php-misconceptions/concurrency code/php-misconceptions/concurrency/supplier.php
```

```bash
php -S 127.0.0.1:8032 -t code/php-misconceptions/concurrency code/php-misconceptions/concurrency/supplier.php
```

North waits about 350 ms and returns 1,250 cents. South waits about 550 ms and returns 1,850 cents. If either server reports that its address is in use, start that server on a free port on `127.0.0.1` and update the matching URL in the copied `routes/supplier-quote.php` after setup. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php) · [Supplier stub](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/concurrency/supplier.php)

In a third terminal at the checkout root, set up the demo. The setup stops if a command fails and prints the disposable app path. Change into that directory for the remaining commands.

```bash
(
    set -eu
    fixture="$PWD/code/php-misconceptions/concurrency"
    sandbox="$(mktemp -d)"
    composer create-project laravel/laravel "$sandbox/php-suppliers" '^13.0' --no-interaction
    cd "$sandbox/php-suppliers"
    cp "$fixture/supplier-quote.php" routes/
    printf '\nrequire __DIR__ . "/supplier-quote.php";\n' >> routes/console.php
    pwd
)
```

```bash
php artisan suppliers:quote serial
php artisan suppliers:quote pool
```

Both print `north: 1250 cents`, `south: 1850 cents`, a `Pool elapsed` duration and `Combined quote: 3100 cents`. The console displays the suppliers by name in a fixed order. The arrival order of the responses can vary. [Quote command](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/concurrency/supplier-quote.php)

Both modes run this same block from the quote command. The named requests let the command find each result by supplier.

```php
$results = Http::pool(static function (Pool $pool) use ($urls): void {
    foreach ($urls as $supplier => $url) {
        $pool->as($supplier)->acceptJson()->connectTimeout(1)->timeout(1)->get($url);
    }
}, concurrency: $mode === 'serial' ? 1 : 2);
```

With a limit of one, South starts after North settles. With a limit of two, both can be in flight together. PHP can inspect `$results` once the pool call returns. In a web checkout, Alex’s PHP-FPM child would still own the request for that entire wait. Because the calls are independent, overlapping them shortens the expected wait from roughly 900 ms to 550 ms, plus overhead. If South needed an ID from North’s response, the calls would have to run in sequence. [Laravel request pooling](https://laravel.com/docs/13.x/http-client#request-pooling)

The output uses `hrtime()` to measure the request group, including starting the requests and waiting for them. Artisan startup and result processing fall outside that interval. Alternate modes and compare several runs on your machine. Production workloads add network, application and supplier limits. [PHP monotonic timer](https://www.php.net/manual/en/function.hrtime.php)

In one loopback run on an Apple M3 Pro with macOS 26.6.2, PHP 8.5.0 with cURL and Laravel 13.43.0, the median was 927 ms serial and 566 ms pooled. Each median uses three trials with the same 350 ms and 550 ms mock waits. Both modes produced the same quote in every trial.

### See how the quote fails

```bash
php artisan suppliers:quote pool --south=error
php artisan suppliers:quote pool --south=slow
```

Each request has a one-second connection timeout and a one-second overall timeout. In the first command, South returns HTTP 503. In the second, its mock response takes about two seconds, so the client reports a connection or timeout failure. Both runs keep the North price, print `Quote incomplete. No combined total.` and exit with status 1. A response with unexpected price data also leaves the combined quote incomplete. [Laravel HTTP timeouts](https://laravel.com/docs/13.x/http-client#timeout)

After either `--south=slow` run, wait two seconds before running another quote command. The single-process South stub can still be sleeping after the client gives up, so a new request may wait behind it.

```bash
php artisan suppliers:quote serial --south=slow
```

Expect the same incomplete quote. In serial mode North uses about 350 ms before South starts its one-second wait, so the group takes around 1,350 ms plus overhead. In pool mode the group takes around one second plus overhead. Give South two seconds to finish before you run the exercise.

Suppose checkout has 800 ms for suppliers after reserving time to render the response. In sequence, North uses about 350 ms, leaving South about 450 ms for its usual 550 ms wait. With a pool of two, both suppliers start with roughly 800 ms available and their usual responses fit. To enforce that budget, set a deadline before the first call and calculate the time left when each transfer starts, including any wait for a pool slot. Cap the connection and overall transfer timeouts by that remainder. If time runs out, skip any call that has yet to start and show the combined quote as unavailable if a price is missing. The lab uses fixed per-call timeouts so you can observe the difference. [PHP monotonic timer](https://www.php.net/manual/en/function.hrtime.php) · [Laravel HTTP timeouts](https://laravel.com/docs/13.x/http-client#timeout)

## Put receipt delivery on a queue

Once both prices are available, Alex can accept the quote and place the order. Save the order and a pending receipt record in the same database transaction. After commit, submit the receipt ID to an asynchronous queue serviced by a separate worker. Checkout can confirm the order once the request’s work finishes, even if the worker has yet to pick up the job. [Laravel queue transactions](https://laravel.com/docs/13.x/queues#jobs-and-database-transactions)

If the app stops after committing and before dispatching, a scheduled sweep can submit the pending receipt later. The worker should atomically claim the receipt and record delivery as in progress before contacting the provider. An abandoned claim can mean the provider accepted the send before the worker recorded success.

Give each receipt a stable delivery identity, such as the order ID plus the receipt type. If the provider accepts an idempotency key, retry with that same key so it can deduplicate the send. If the provider offers delivery lookup, check the first attempt and retry only if it confirms the receipt was unsent. Hold automatic resends while an uncertain outcome is being reconciled. [Queue retries and shared capacity](/series/php-misconceptions/chapters/09-scaling-php/)

## Exercise

1. With both servers running, alternate serial and pool runs and compare the durations and totals. Using the stub delays, predict which mode can finish both calls within the hypothetical 800 ms budget.
2. Suppose South permits one active request from the entire app. Would setting `concurrency: 1` on each checkout’s pool meet that contract?
3. What can checkout display if South times out? Which work can finish after the order confirmation?

<details>
<summary>Check your reasoning</summary>

1. Both modes return 3,100 cents when both prices arrive. Pooling can fit the two usual waits into about 550 ms plus overhead. The roughly 900 ms serial wait would exceed the hypothetical budget.
2. The limit applies to each pool call. Several checkouts can still reach South concurrently. Enforce a shared in-flight cap across requests and replicas. A requests-per-second limit can still allow overlap. A client timeout may leave work running at South, as in the slow stub. Check how the supplier signals completion before freeing capacity when the previous outcome is uncertain.
3. Checkout can show North’s price and mark the total unavailable. Once a quote is accepted and an order is committed, the receipt can go to the queue. If a send outcome is uncertain, reuse the same key with an idempotent provider, check delivery status when lookup is available, or hold the resend for reconciliation.

</details>

Stop the two supplier servers with Ctrl+C in their terminals.

If you have a checkout with the receipt workflow implemented, try this acceptance check with an asynchronous queue and an idempotent fake delivery provider. Place an order with the worker stopped and verify that checkout confirms the order, the receipt remains pending and the provider sees no send. Start the worker with `php artisan queue:work` for the configured queue and verify that the receipt is delivered. Then use a fresh receipt and simulate a worker stop after provider acceptance and before the success record is saved. Let the job retry and verify that the provider logged two attempts with the same key and one delivery, and that the app records success. [Laravel queue workers](https://laravel.com/docs/13.x/queues#running-the-queue-worker)

## Next chapter

[Does PHP Have Serious Developer Tools?](/series/php-misconceptions/chapters/11-developer-tooling/)
