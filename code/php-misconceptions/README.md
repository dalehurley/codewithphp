# PHP Misconceptions examples

Run the standalone examples from the Code with PHP repository root using PHP 8.6 or later.

| Command | What to check |
| --- | --- |
| `php code/php-misconceptions/types.php` | The integer quantity returns 3750. The caller catches a `TypeError` for the numeric string. |
| `php code/php-misconceptions/types-coercive.php` | The same calls print 3750 twice. |
| `php code/php-misconceptions/quantity-list.php` | The array is accepted, then its string element triggers a `TypeError` in the pricing loop. [Chapter 1](https://codewithphp.com/series/php-misconceptions/chapters/01-php-types/) shows how PHPStan catches the problem before execution. |
| `php code/php-misconceptions/validate-quantity.php 3` | The raw input passes validation and the typed call prints 3750. |
| `php code/php-misconceptions/modern-php.php` | The computed order label changes from pending to paid. |
| `php code/php-misconceptions/order-status-input.php paid` | The script converts the raw string to an enum case and prints the order label. Unknown states exit with status 1. |
| `php code/php-misconceptions/cost-estimate.php` | The fictional prices and workloads produce A$140.00 managed and A$215.00 self-managed per month. |
| `php code/php-misconceptions/inventory.php` | The JSON report contains two inventory items. Add `--in-stock` for only the positive-stock row. Chapter 7 shows the matching local HTTP endpoint. |
| `php code/php-misconceptions/scaling-capacity.php` | The worker-only ceiling is 240.0 requests/s. With 19 database connections available to web, an equal cap of six workers per instance gives a modelled ceiling of 180.0 requests/s. |
| `php code/php-misconceptions/fibers.php` | The caller runs between the two parts of the Fiber. Add `--blocking` to see the caller wait until the blocking work returns and the Fiber suspends. |
| `php code/php-misconceptions/quirks.php` | A match can occur at offset zero, and reindexing a sparse array changes its JSON representation. |
| `php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 code/php-misconceptions/legacy-order-label.php` | PHP 8.5 prints `paid` and reports a dynamic-property deprecation. The upgrade chapter rehearses the fix. |

The four Chapter 1 scripts also run on PHP 8.2 or later.

The Chapter 5 order examples also run on PHP 8.4 or later.

The Chapter 9 capacity model was also checked on PHP 8.5.0.

The [Chapter 2 performance lab](https://codewithphp.com/series/php-misconceptions/chapters/02-php-performance/) serves `performance/index.php` on loopback with PHP 8.5 or later. It demonstrates opcode cache hits and measures a simulated dependency wait.

The `laravel/` directory contains files to copy into a fresh Laravel 13 app.

- The [Laravel performance lab](../../src/content/docs/series/php-misconceptions/chapters/03-laravel-performance.md) provides the setup, test and benchmark commands. The Caddyfile serves the two HTTP modes on loopback only. The [saved results](laravel/results/) include the TSV summaries, ApacheBench reports and dependency files.
- The [Laravel code quality chapter](../../src/content/docs/series/php-misconceptions/chapters/04-clean-laravel-code.md) installs the quote endpoint and its feature tests.
- The [developer tooling chapter](../../src/content/docs/series/php-misconceptions/chapters/11-developer-tooling.md) uses the quote app with `laravel/tooling/` to demonstrate Pint, Larastan and PHPUnit.

The [Chapter 8 invoice lab](../../src/content/docs/series/php-misconceptions/chapters/08-php-security.md) copies the `invoice-lab/` fixture into a fresh Laravel 13 app and tests private invoice access with in-memory SQLite.

The [Chapter 10 supplier lab](../../src/content/docs/series/php-misconceptions/chapters/10-php-concurrency.md) compares serial and pooled requests to two loopback PHP servers and checks what happens when one supplier fails.
