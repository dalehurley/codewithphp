---
title: "04: Does PHP Have to Be Messy?"
description: "Trace a three-seat Laravel quote and raise the seat price without changing input validation or the JSON fields."
series: "php-misconceptions"
chapter: 4
estimatedTime: "PT20M"
sidebar:
  order: 4
---

## The seat price change<span id="overview"></span>

A teammate asks you to raise the price of a seat from 1,250 to 1,500 cents. `GET /quotes?quantity=3` returns `{"quantity":3,"total_cents":3750}` today. After the change, it should return 4,500 cents for the same three seats. The endpoint should still accept quantities from 1 through 100 and return the same two JSON fields.

If all you know is the URL, where do you make that edit? Start with a passing test, follow the request to the line that sets the price, then use the same test to verify the new quote.

## Prerequisites

Use Bash or Zsh, a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp), and Composer 2.2 or later with internet access to download the app and its dependencies. This lab was tested on PHP 8.5. Laravel 13 requires PHP 8.3 or later. Use `php -v` to check your CLI version and `php -m` to check that it loads Laravel’s required extensions, plus `pdo_sqlite` for the starter database and `xmlwriter` for PHPUnit. The optional HTTP check also uses `curl`. [Laravel PHP requirements](https://laravel.com/docs/13.x/deployment#server-requirements) · [PHPUnit installation](https://docs.phpunit.de/en/12.5/installation.html#php-on-the-command-line)

## Quick Start

Run this block from the checkout root. It creates a disposable Laravel app and stops on the first failed command. The Laravel skeleton’s Composer setup creates and migrates a starter SQLite database. The quote calculation uses only the quantity and a price in cents. [Laravel skeleton setup](https://github.com/laravel/laravel/blob/13.x/composer.json)

```bash
(
    set -eu
    fixture="$PWD/code/php-misconceptions/laravel"
    sandbox="$(mktemp -d)"
    composer create-project laravel/laravel "$sandbox/php-quotes" '^13.0' --no-interaction
    cd "$sandbox/php-quotes"

    mkdir -p app/Http/Requests app/Quotes
    cp "$fixture/app/Http/Controllers/QuoteController.php" app/Http/Controllers/
    cp "$fixture/app/Http/Requests/QuoteRequest.php" app/Http/Requests/
    cp "$fixture/app/Quotes/SeatPrice.php" app/Quotes/
    cp "$fixture/routes/quotes.php" routes/web.php
    cp "$fixture/tests/Feature/QuoteTest.php" tests/Feature/
    pwd
)
```

The skeleton loads `routes/web.php` from `bootstrap/app.php`. Copying the fixture over the disposable app’s route file makes `/quotes` available at that URL. [Laravel skeleton routing setup](https://github.com/laravel/laravel/blob/13.x/bootstrap/app.php) · [Laravel routing](https://laravel.com/docs/13.x/routing)

Change into the `php-quotes` directory printed on the last line. Run the remaining Artisan commands from there.

```bash
php artisan route:list --path=quotes
php artisan test --filter=QuoteTest
```

Expect one `GET|HEAD` route for `quotes` pointing to `QuoteController` and two passing tests. The tests dispatch requests inside Laravel, so you can run them without starting a development server. [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests#making-requests)

## Follow the price through the app

1. **Find the handler.** In the demo’s `routes/web.php`, `Route::get('/quotes', QuoteController::class)` tells Laravel to call `QuoteController::__invoke()` for the request. The route gives you the next file to open. [Route source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/routes/quotes.php) · [Single-action controllers](https://laravel.com/docs/13.x/controllers#single-action-controllers) · [Laravel directory structure](https://laravel.com/docs/13.x/structure)
2. **Check what reaches the handler.** The controller’s `QuoteRequest` parameter tells Laravel to validate the input before running the action. The rules `['required', 'integer', 'between:1,100']` accept the query string value `"3"`. After validation, `quantity(): int` casts the validated value to the PHP integer `3`. An invalid quantity such as `0` never reaches the controller action. For a request that expects JSON, Laravel returns HTTP 422 with a validation error for `quantity`. [Request source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/app/Http/Requests/QuoteRequest.php) · [Form Request validation](https://laravel.com/docs/13.x/validation#form-request-validation) · [Integer validation](https://laravel.com/docs/13.x/validation#rule-integer)
3. **Find who calculates the total.** Laravel’s service container reads the action’s parameter types and supplies the request and a `SeatPrice` instance. `QuoteController` reads `$request->quantity()`, puts that integer in the JSON `quantity` field and uses `$price->totalCents($quantity)` for `total_cents`. Follow that call to find the price. [Controller source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/app/Http/Controllers/QuoteController.php) · [Laravel service container](https://laravel.com/docs/13.x/container#zero-configuration-resolution)
4. **Find the price to edit.** In `app/Quotes/SeatPrice.php`, `totalCents(3)` multiplies the typed `CENTS_PER_SEAT` constant by three. The calculation takes an integer and returns an integer in cents. Changing the constant updates the total the controller returns. [Pricing source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/app/Quotes/SeatPrice.php) · [PHP runtime types and validation](/series/php-misconceptions/chapters/01-php-types/)

Open `tests/Feature/QuoteTest.php` before editing. Its first test makes the same request, asks for JSON and checks both the HTTP 200 status and the complete response body.

```php
$this->getJson('/quotes?quantity=3')
    ->assertOk()
    ->assertExactJson(['quantity' => 3, 'total_cents' => 3750]);
```

The second test checks that missing, fractional and out-of-range values still yield HTTP 422 and an error for `quantity`. Together they let you check the new total and catch changes to the response fields or the rejection of those inputs. [QuoteTest source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/laravel/tests/Feature/QuoteTest.php) · [Laravel HTTP test assertions](https://laravel.com/docs/13.x/http-tests#available-assertions)

## Exercise

1. In the demo app’s `app/Quotes/SeatPrice.php`, change `CENTS_PER_SEAT` from `1250` to `1500`.
2. Run `php artisan test --filter=QuoteTest`. The valid quote test should fail because it expects `3750` and the response now contains `4500`. The validation test should pass.
3. In `tests/Feature/QuoteTest.php`, update the expected `total_cents` to `4500`, the agreed price of three seats at 1,500 cents each. Rerun the same command. Both tests should pass.

Restore `1250` in the calculator and `3750` in the assertion to reuse the original demo in the [developer tooling chapter](/series/php-misconceptions/chapters/11-developer-tooling/).

## Decide where the next change belongs

| Change | Where to start | What to check |
| --- | --- | --- |
| Allow a different quantity range on this HTTP endpoint | `QuoteRequest::rules()` | Both new limits and the validation response. |
| Adjust the seat price | `SeatPrice` | The total for a known order in cents. |
| Change the JSON fields | `QuoteController` | The response contract consumers expect. |

A small one-off calculation can live in the controller. A separate pricing class earns its place when a second entry point needs the same rule or you want to test the calculation on its own. Pint can format either version. [Laravel Pint](https://laravel.com/docs/13.x/pint)

Suppose you add a console command that uses the restored seat price. What would `GET /quotes?quantity=101` return for a client asking for JSON? What would `(new \App\Quotes\SeatPrice())->totalCents(101)` return? Where would you enforce the limit if it applied to every booking?

<details>
<summary>Check your reasoning</summary>

The HTTP request returns 422 because `QuoteRequest` enforces the range. The calculator accepts the integer `101` and returns 126,250 cents. Parse console input at the command boundary. If the limit applies to every booking, enforce it in shared pricing code or a quantity type used by both callers. Test that the shared rule rejects 101, then verify that the HTTP and console entry points each report the error in the format their callers expect.

</details>

## Try the endpoint over HTTP

For an optional check of the restored demo, start Laravel’s development server from the app root. The in-memory session and cache drivers let you run this check without using the starter database. `--no-reload` preserves these environment settings when Artisan starts the server.

```bash
SESSION_DRIVER=array CACHE_STORE=array php artisan serve --host=127.0.0.1 --port=8024 --no-reload
```

From a second terminal, send a valid and an invalid request. If port 8024 is busy, restart the server with a free `--port` and use that port in both URLs.

```bash
curl -sS -i -H 'Accept: application/json' 'http://127.0.0.1:8024/quotes?quantity=3'
curl -sS -i -H 'Accept: application/json' 'http://127.0.0.1:8024/quotes?quantity=0'
```

The first request returns HTTP 200 and `{"quantity":3,"total_cents":3750}`. The invalid request returns HTTP 422 and includes an `errors.quantity` entry. The `Accept` header asks Laravel for a JSON validation response. Stop the development server with Ctrl+C when you finish. [Laravel validation error response](https://laravel.com/docs/13.x/validation#validation-error-response-format)

## Next chapter

[Is PHP Old and Dead?](/series/php-misconceptions/chapters/05-php-is-still-evolving/)
