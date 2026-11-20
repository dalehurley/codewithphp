---
title: "04: Does PHP Have to Be Messy?"
description: "Trace a Laravel quote from route to validation, typed pricing and JSON, then change the price with a test."
series: "php-misconceptions"
chapter: 4
estimatedTime: "PT20M"
sidebar:
  order: 4
---

## Overview

A teammate needs to change the price of a seat. Start at `GET /quotes?quantity=3` and follow the request until you find the calculation. The response has two fields, the validated `quantity` and `total_cents`. Three seats at 1,250 cents each cost 3,750 cents.

Laravel’s directory conventions help you find the HTTP entry point. The example gives input validation, price calculation and the response their own places in the code. [Laravel directory structure](https://laravel.com/docs/13.x/structure)

## Prerequisites

Use Bash or Zsh, a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp), PHP 8.5 or later with Laravel’s required extensions and PDO SQLite, and Composer 2.2 or later. This demo was checked on PHP 8.5. The default Laravel installer uses SQLite to create and migrate its starter database. The quote calculation itself has no database dependency. Use `curl` for the optional HTTP check. [Laravel PHP requirements](https://laravel.com/docs/13.x/deployment#server-requirements)

## Quick Start

From the checkout root, create a fresh Laravel app in a temporary directory. The fixture replaces the new app’s `routes/web.php`, so use this separate app for the quote lesson. Keep the path printed by `pwd` for the second terminal.

```bash
fixture="$PWD/code/php-misconceptions/laravel"
sandbox="$(mktemp -d)"
composer create-project laravel/laravel "$sandbox/php-quotes" '^13.0' --no-interaction
cd "$sandbox/php-quotes"
pwd

mkdir -p app/Http/Requests app/Quotes
cp "$fixture/app/Http/Controllers/QuoteController.php" app/Http/Controllers/
cp "$fixture/app/Http/Requests/QuoteRequest.php" app/Http/Requests/
cp "$fixture/app/Quotes/SeatPrice.php" app/Quotes/
cp "$fixture/routes/quotes.php" routes/web.php
cp "$fixture/tests/Feature/QuoteTest.php" tests/Feature/

php artisan route:list --path=quotes
php artisan test --filter=QuoteTest
```

The route list should show `GET|HEAD` for `quotes` and point to `QuoteController`. Expect two passing HTTP tests. One checks the exact quote for three seats. The other sends missing, fractional and out-of-range quantities and checks for HTTP 422 with a validation error on `quantity`. Laravel’s `getJson()` helper asks for JSON. [Laravel routing](https://laravel.com/docs/13.x/routing) · [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests)

### Try the endpoint over HTTP

Start Laravel’s development server from the demo app root. The in-memory session and cache drivers keep this check separate from the starter database. `--no-reload` preserves these environment settings when Artisan starts the server.

```bash
SESSION_DRIVER=array CACHE_STORE=array php artisan serve --host=127.0.0.1 --port=8024 --no-reload
```

From a second terminal, send a valid and an invalid request. If port 8024 is busy, restart the server with a free `--port` and use that port in both URLs.

```bash
curl -sS -i -H 'Accept: application/json' 'http://127.0.0.1:8024/quotes?quantity=3'
curl -sS -i -H 'Accept: application/json' 'http://127.0.0.1:8024/quotes?quantity=0'
```

The first request returns HTTP 200 and `{"quantity":3,"total_cents":3750}`. The invalid request returns HTTP 422 and includes an `errors.quantity` entry. The `Accept` header asks Laravel for a JSON validation response. Stop the development server with Ctrl+C when you finish. [Laravel validation error response](https://laravel.com/docs/13.x/validation#validation-error-response-format)

## Follow the price through the app

1. **Route.** The fixture route maps `GET /quotes` to the controller class. The method name `__invoke()` gives that single-action class its entry point. [Route source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/routes/quotes.php)
2. **Form Request.** `QuoteRequest` requires a value, checks it with `integer` and constrains the range to 1–100 inclusive. Laravel validates the Form Request before it runs the controller action. The HTTP query value `"3"` passes the integer rule. The `quantity(): int` method converts that validated value to the integer `3`. [Request source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/app/Http/Requests/QuoteRequest.php) · [Form Request validation](https://laravel.com/docs/13.x/validation#form-request-validation) · [Integer validation](https://laravel.com/docs/13.x/validation#rule-integer)
3. **Controller.** Laravel’s service container supplies the request and resolves the concrete `SeatPrice` class from the parameter types. `QuoteController` passes the integer to the calculator and assembles the JSON response. [Controller source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/app/Http/Controllers/QuoteController.php) · [Laravel service container](https://laravel.com/docs/13.x/container)
4. **Price calculation.** `SeatPrice` multiplies the supplied `int` by a typed price constant. It expects a valid seat quantity from the caller. A console command that reuses it must parse and validate its own input before passing the integer. [Pricing source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/app/Quotes/SeatPrice.php) · [PHP runtime types and validation](/series/php-misconceptions/chapters/01-php-types/)

### Decide where the next change belongs

| Change | Where to start | What to check |
| --- | --- | --- |
| Allow a different quantity range on this HTTP endpoint | `QuoteRequest::rules()` | Both new limits and the validation response. |
| Adjust the seat price | `SeatPrice` | The total for a known order in cents. |
| Change the JSON fields | `QuoteController` | The response contract consumers expect. |

A small one-off calculation can live in the controller. A separate pricing class earns its place when another entry point needs the same rule or you want to test the calculation on its own. Pint handles formatting. The route, names and tests help you find the behaviour that needs to change. [Laravel Pint](https://laravel.com/docs/13.x/pint)

## Exercise

In the demo app, change `CENTS_PER_SEAT` in `app/Quotes/SeatPrice.php` from `1250` to `1500`. Run `php artisan test --filter=QuoteTest`. The exact JSON assertion should fail for the old total of `3750`. The invalid-quantity test should still pass.

Update `tests/Feature/QuoteTest.php` to expect `4500` and rerun the tests. Expect both to pass. Restore `1250` and the `3750` assertion to reuse the original demo in the [developer tooling chapter](/series/php-misconceptions/chapters/11-developer-tooling/).

Now imagine adding a console command that quotes seats. Which code would you share? Where would you put the 1–100 rule if every type of booking had to respect it?

<details>
<summary>Check your reasoning</summary>

Parse the console input at the command boundary and reuse `SeatPrice` for the calculation. If 1–100 is a rule for every booking, enforce it in the shared pricing code or a quantity type used by both callers. Keep the Form Request responsible for reporting invalid HTTP input in Laravel’s JSON error format. A direct test of the shared pricing rule can check the limit independently of either entry point.

</details>

## Next chapter

[Is PHP Old and Dead?](/series/php-misconceptions/chapters/05-php-is-still-evolving/)
