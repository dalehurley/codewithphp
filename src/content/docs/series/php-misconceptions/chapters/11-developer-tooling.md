---
title: "11: Does PHP Have Serious Developer Tools?"
description: "Reproduce a Laravel quote feature and use Composer, Pint, Larastan, PHPUnit and Xdebug to diagnose different failures."
series: "php-misconceptions"
chapter: 11
estimatedTime: "PT25M"
sidebar:
  order: 11
---

## Overview

The quote feature returns 3,750 cents for three seats. When a change breaks that result, you can check the project environment, look for the wrong argument type and compare the actual response with the expected one. This lab uses the same quote throughout so you can see which tool answers each question.

| Question | Tool and evidence |
| --- | --- |
| Can this PHP CLI install and load the application? | Composer resolves dependencies, checks platform requirements and loads classes. |
| Did a change break the formatting rules? | Pint checks the files against the project’s style. |
| Does the controller pass the right type to the calculator? | PHPStan with Larastan reports a bad call in the analysed source. |
| Does an actual request return the expected JSON and status? | PHPUnit runs a Laravel feature test with its own HTTP assertions. |
| Which values reach the calculator as the test runs? | Xdebug pauses the running PHP process so you can inspect them. |

## Set up the demo

Use Bash or Zsh, a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp), PHP 8.5 or later with PDO SQLite and Laravel’s required extensions, and a current Composer 2 release. The setup below creates a disposable Laravel 13 app with the same feature files as Chapter 4. Reuse a clean copy of that app if you already have one, and copy the two files under `laravel/tooling/` to its root before installing Larastan. [Laravel requirements](https://laravel.com/docs/13.x/deployment#server-requirements) · [Chapter 4 quote walkthrough](/series/php-misconceptions/chapters/04-clean-laravel-code/)

Run this block from the checkout root. It stops on failure and prints the new app path. Change into that directory for the remaining commands.

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

Inside the demo, add Larastan and check the environment. The Laravel skeleton already supplies Pint and PHPUnit as development dependencies. `composer require --dev` records the new dependency in `composer.json` and resolves it into `composer.lock`. Later, `composer install` uses that lock file to install the same package versions. [Composer dependencies and the lock file](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) · [Platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

```bash
composer require --dev 'larastan/larastan:^3.0' --no-interaction
php -v
php --ri pdo_sqlite
composer check-platform-reqs
```

`composer check-platform-reqs` compares installed packages’ declared requirements with the actual PHP CLI and its extensions. Check PDO SQLite directly because the starter app uses it to create its database. [Composer platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

The demo’s PSR-4 mapping associates `App\` with `app/`, so Composer can locate `App\Quotes\SeatPrice` in `app/Quotes/SeatPrice.php`. The skeleton’s optimised autoloader records that location in a generated classmap. The command below loads the class through Composer and checks the calculation directly. Expect `3750`. [Composer autoloading](https://getcomposer.org/doc/01-basic-usage.md#autoloading) · [Composer autoloader optimisation](https://getcomposer.org/doc/articles/autoloader-optimization.md) · [PHP-FIG PSR-4](https://www.php-fig.org/psr/psr-4/)

```bash
php -r 'require "vendor/autoload.php"; echo (new \App\Quotes\SeatPrice())->totalCents(3), PHP_EOL;'
```

## Run the checks

```bash
bash check-quote.sh
```

The script stops on the first failed check. Pint’s `--test` mode checks the feature files without editing them. PHPStan should report no errors. `phpstan.neon` selects level 5 and the controller, request and calculator files. Larastan extends PHPStan with Laravel-specific type information for the code it analyses. The two feature tests should pass. They check the exact quote JSON and verify that malformed or out-of-range quantities produce HTTP 422 with a validation error. [Check script](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/tooling/check-quote.sh) · [PHPStan configuration](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/tooling/phpstan.neon) · [Laravel Pint](https://laravel.com/docs/13.x/pint) · [PHPStan rule levels](https://phpstan.org/user-guide/rule-levels) · [Larastan](https://github.com/larastan/larastan) · [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests)

### Run the same gate in CI

Commit the application’s `composer.json`, `composer.lock`, `phpstan.neon` and `check-quote.sh` alongside its source. In a fresh checkout with PHP and Composer installed, keep development dependencies enabled and use a disposable test key. This lab’s quote tests need no application data. [Composer install command](https://getcomposer.org/doc/03-cli.md#install-i) · [Laravel testing environment](https://laravel.com/docs/13.x/testing#environment)

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

If the platform check fails, inspect the CLI version and its loaded configuration with `php -v` and `php --ini`. The CLI running Composer and the tests may differ from the PHP used by a web server. [Composer platform requirements](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

### Inspect one request with Xdebug

For this optional step, use a local PHP CLI with Xdebug installed. Confirm with `php --ri xdebug`. Have your IDE listen for a PHP debug connection on port 9003. Set a breakpoint on the multiplication inside `SeatPrice::totalCents()` in `app/Quotes/SeatPrice.php`, then run just the valid quote test. The command starts the debugger for this invocation. [Xdebug step debugging](https://xdebug.org/docs/step_debug) · [PHPUnit command-line options](https://phpunit.de/documentation.html)

```bash
XDEBUG_MODE=debug php \
    -d xdebug.start_with_request=yes \
    -d xdebug.client_host=127.0.0.1 \
    -d xdebug.client_port=9003 \
    vendor/bin/phpunit --filter=test_a_valid_quantity_gets_a_quote_in_cents
```

At the breakpoint, expect `$quantity` to be the integer `3`. Inspect the multiplication result of `3750`, then let the test complete. The debugger shows the values on this execution path. The assertions report whether the response matches its contract on future runs.

## Exercise

Try one change at a time in the disposable app. Run `bash check-quote.sh` after each change. Predict which check fails first, then restore the original before continuing.

1. In `app/Http/Controllers/QuoteController.php`, replace `$quantity = $request->quantity();` with `$quantity=$request->quantity();`.
2. In the same file, change `$price->totalCents($quantity)` to `$price->totalCents('three')`.
3. In `app/Quotes/SeatPrice.php`, change `CENTS_PER_SEAT` from `1250` to `1600`.

<details>
<summary>Check your reasoning</summary>

1. Pint fails on the spacing. Run `vendor/bin/pint app/Http/Controllers/QuoteController.php` to apply the style fix, then rerun the gate.
2. PHPStan reports a string passed to `SeatPrice::totalCents()`, where the first argument must be `int`. Restore `$quantity` as the argument.
3. Pint and PHPStan pass. The valid quote test fails because the response is 4,800 cents and the assertion expects 3,750. The validation test still passes. If 4,800 is the intended result, update the valid quote assertion to `4800` and rerun the gate. Restore the original price and expectation to keep the demo in sync with Chapter 4.

</details>

## Next chapter

[Which PHP Quirks Still Matter?](/series/php-misconceptions/chapters/12-language-quirks/)
