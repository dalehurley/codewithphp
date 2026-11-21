---
title: "11: Does PHP Have Serious Developer Tools?"
description: "Trace a Laravel quote regression with Composer, Pint, Larastan, PHPUnit and Xdebug, then run the same checks in CI."
series: "php-misconceptions"
chapter: 11
estimatedTime: "PT25M"
sidebar:
  order: 11
---

<span id="overview"></span>

## A quote comes back too high

Three seats at 1,250 cents each should cost 3,750 cents. The contract for `GET /quotes?quantity=3` is HTTP 200 with the JSON below.

```json
{"quantity":3,"total_cents":3750}
```

Now suppose the same quote comes back as 4,800 cents. Is the controller passing the wrong quantity, or is the calculator using the wrong price? Build a passing baseline, introduce the regression, and follow the failing test to the calculation. Then see which earlier checks catch a spacing error or the wrong argument type.

## Set up the demo

Use Bash or Zsh, a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp), PHP 8.5 or later with PDO SQLite and Laravel’s required extensions, and Composer 2. Check the tools before creating the app. [Laravel requirements](https://laravel.com/docs/13.x/deployment#server-requirements)

```bash
php -v
composer --version
php --ri pdo_sqlite
```

If the PHP version is wrong, use `command -v php` to see which executable your shell found. If PDO SQLite is missing, use `php --ini` to find the CLI configuration, then install or enable the extension for that PHP distribution and repeat the checks. The Laravel skeleton uses SQLite during project creation.

From the checkout root, run the block below to create a disposable Laravel 13 app with the quote feature from Chapter 4. The block stops on failure and prints the new app path. You can also reuse a disposable copy of the Chapter 4 app. Copy `phpstan.neon` and `check-quote.sh` from `code/php-misconceptions/laravel/tooling/` to its root. [Chapter 4 quote walkthrough](/series/php-misconceptions/chapters/04-clean-laravel-code/)

```bash
(
    set -eu
    fixture="$PWD/code/php-misconceptions/laravel"
    sandbox="$(mktemp -d)"
    composer create-project laravel/laravel "$sandbox/php-quote-tools" '^13.0' --no-interaction
    cd "$sandbox/php-quote-tools"
    mkdir -p app/Http/Requests app/Quotes
    cp "$fixture/app/Http/Controllers/QuoteController.php" app/Http/Controllers/
    cp "$fixture/app/Http/Requests/QuoteRequest.php" app/Http/Requests/
    cp "$fixture/app/Quotes/SeatPrice.php" app/Quotes/
    cp "$fixture/routes/quotes.php" routes/web.php
    cp "$fixture/tests/Feature/QuoteTest.php" tests/Feature/
    cp "$fixture/tooling/phpstan.neon" "$fixture/tooling/check-quote.sh" .
    pwd
)
```

Change into the printed directory in your shell. It contains `artisan`, `composer.json` and `check-quote.sh`. Run the remaining lab commands there. The skeleton supplies Pint and PHPUnit as development dependencies. Add Larastan and then check the installed packages against the PHP CLI and its extensions. [Composer dependencies and the lock file](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) · [Composer platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

```bash
composer require --dev 'larastan/larastan:^3.0' --no-interaction
composer check-platform-reqs
```

The caret constraints allow fresh setups to resolve different compatible versions over time. Composer records the resolved versions for this app in `composer.lock`. CI can install those exact versions from that file. [Installing from a Composer lock file](https://getcomposer.org/doc/01-basic-usage.md#installing-from-composer-lock)

The demo’s PSR-4 mapping associates `App\` with `app/`, so Composer can load `App\Quotes\SeatPrice` from `app/Quotes/SeatPrice.php`. The skeleton enables an optimised classmap for faster lookup. Run the calculator directly to check the baseline total of `3750` before involving the route. [Composer autoloading](https://getcomposer.org/doc/01-basic-usage.md#autoloading) · [Composer autoloader optimisation](https://getcomposer.org/doc/articles/autoloader-optimization.md) · [PHP-FIG PSR-4](https://www.php-fig.org/psr/psr-4/)

```bash
php -r 'require "vendor/autoload.php"; echo (new \App\Quotes\SeatPrice())->totalCents(3), PHP_EOL;'
```

## Run the checks

```bash
bash check-quote.sh
```

This script runs the checks below in order and stops at the first failure. Expect Pint to pass, PHPStan to report zero errors, and both feature tests to pass. Output formatting can vary with tool versions and terminal settings. [Check script](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/tooling/check-quote.sh)

| Check | What it checks in this app |
| --- | --- |
| Pint | Checks formatting in the five fixture files. The script uses `--test` to leave them untouched. |
| PHPStan with Larastan | Level 5 checks argument types in the three configured PHP files, among other rules. Larastan supplies Laravel-specific type information. |
| PHPUnit | Laravel’s `getJson()` simulates the HTTP request in-process. One test checks the exact JSON and HTTP 200. The other expects HTTP 422 and a `quantity` validation error for missing, fractional or out-of-range quantities. |

[Laravel Pint](https://laravel.com/docs/13.x/pint) · [PHPStan configuration](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/tooling/phpstan.neon) · [PHPStan rule levels](https://phpstan.org/user-guide/rule-levels) · [Larastan](https://github.com/larastan/larastan) · [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests#making-requests)

### Reproduce the wrong total

In `app/Quotes/SeatPrice.php`, change `CENTS_PER_SEAT` from `1250` to `1600` and run the gate again. Pint accepts the formatting and PHPStan accepts the integer calculation. The validation test still passes, but the valid quote test reports `3750` expected and `4800` actual.

Run the filtered test to isolate the failing assertion.

```bash
php vendor/bin/phpunit --filter=test_a_valid_quantity_gets_a_quote_in_cents
```

The assertion in `tests/Feature/QuoteTest.php` shows `quantity` at `3` in both the expected and actual JSON. Only `total_cents` differs. Rerun the direct calculator command from setup. If it also prints `4800` for the integer `3`, you’ve reproduced the problem in `SeatPrice` independently of the route and controller.

### Inspect one request with Xdebug

This optional walkthrough assumes PHP and the IDE run on the same machine. Leave `1600` in place for this step. Check `php --ri xdebug` in the terminal that will run PHPUnit. If the extension is missing, [enable Xdebug for that PHP CLI](https://xdebug.org/docs/install) or [skip to restoring the quote](#restore-the-quote-contract).

Open the disposable app in your IDE. In PhpStorm, check that **Settings → PHP → Debug** includes port `9003`, then select **Run → Start Listening for PHP Debug Connections**. Set a breakpoint on the `return` line inside `SeatPrice::totalCents()`. [PhpStorm CLI debugging](https://www.jetbrains.com/help/phpstorm/debugging-a-php-cli-script.html) · [Xdebug step debugging](https://xdebug.org/docs/step_debug) · [PHPUnit documentation](https://phpunit.de/documentation.html)

```bash
XDEBUG_MODE=debug php \
    -d xdebug.start_with_request=yes \
    -d xdebug.client_host=127.0.0.1 \
    -d xdebug.client_port=9003 \
    -d "xdebug.log=$PWD/storage/logs/quote-xdebug.log" \
    vendor/bin/phpunit --filter=test_a_valid_quantity_gets_a_quote_in_cents
```

In the paused calculator frame, expect an integer `$quantity` of `3`. Evaluate `self::CENTS_PER_SEAT * $quantity` to see `4800` before the method returns, then resume to see the failed assertion. If the breakpoint is missed, read `storage/logs/quote-xdebug.log`. A failed connection points to the listener or port. If Xdebug connected, confirm the breakpoint is enabled in the same copy of `app/Quotes/SeatPrice.php` that the terminal runs. [Xdebug connection logs](https://xdebug.org/docs/step_debug#xdebug.log)

A local DBGp client verified the Xdebug connection, breakpoint and calculator values. The PhpStorm steps were checked against its documentation.

### Restore the quote contract

Restore `CENTS_PER_SEAT` to `1250`. Rerun the filtered test, then run `bash check-quote.sh` to confirm the full gate passes. The expected total stays at `3750` because the price in this exercise changed by mistake. An approved price change would require updating both the calculation and the expected quote.

## Exercise

Try one change at a time in the disposable app. Run `bash check-quote.sh` after each change. Predict which check fails first and restore the original before starting the next case.

1. In `app/Http/Controllers/QuoteController.php`, replace `$quantity = $request->quantity();` with `$quantity=$request->quantity();`.
2. In the same file, change `$price->totalCents($quantity)` to `$price->totalCents('three')`.
3. In `app/Http/Requests/QuoteRequest.php`, change `between:1,100` to `between:2,100`. Predict whether the existing tests cover a one-seat quote. Add successful HTTP and exact JSON assertions for `quantity=1` and `quantity=100` to `tests/Feature/QuoteTest.php`.

<details>
<summary>Check your reasoning</summary>

1. Run `vendor/bin/pint --test app/Http/Controllers/QuoteController.php` to see the controller and the `binary_operator_spaces` rule in the diagnostic. Apply the fix with `vendor/bin/pint app/Http/Controllers/QuoteController.php` and rerun the gate.
2. PHPStan reports `argument.type` on the controller call because `SeatPrice::totalCents()` expects an integer and received a string argument in the source. Rerun just analysis with `vendor/bin/phpstan analyse --configuration=phpstan.neon --no-progress`. Restore `$quantity` as the argument and rerun the gate.
3. The original tests stay green. They accept `3` and reject missing, `0`, `101` and `3.5`. In the new assertions, expect HTTP 200 and `{"quantity":1,"total_cents":1250}` for one seat, and HTTP 200 and `{"quantity":100,"total_cents":125000}` for 100 seats. With the minimum at `2`, one seat returns HTTP 422. Restore `between:1,100` and confirm the new assertions pass.

</details>

## Run the same gate in CI

Once the local gate is green, commit your application’s `composer.json`, `composer.lock`, `phpstan.neon` and `check-quote.sh` alongside its source. In a fresh checkout of the demo app, with PHP and Composer installed, install from the lock file with development dependencies enabled and generate a disposable test key. This lab’s quote tests need no application data. [Composer install command](https://getcomposer.org/doc/03-cli.md#install-i) · [Laravel testing environment](https://laravel.com/docs/13.x/testing#environment)

```bash
(
    set -eu
    composer install --no-interaction --prefer-dist
    composer check-platform-reqs
    export APP_ENV=testing
    APP_KEY="$(php artisan key:generate --show)"
    export APP_KEY
    bash check-quote.sh
)
```

If installation or the platform check fails, compare `php -v`, `composer --version` and `php --ini` in CI with the local baseline. The PHP CLI running Composer and the tests may differ from the PHP used by a web server.

## Next chapter

[Which PHP Quirks Still Matter?](/series/php-misconceptions/chapters/12-language-quirks/)
