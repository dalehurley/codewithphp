---
title: "04: Does PHP Have to Be Messy?"
description: "Build a readable Laravel feature using a route, Form Request, typed pricing code and a feature test."
series: "php-misconceptions"
chapter: 4
estimatedTime: "PT20M"
sidebar:
  order: 4
---

## Overview

Someone new to a Laravel codebase can often start in `routes/` and follow a request into `app/Http/` without knowing the business domain. That is one of the practical benefits of the Laravel way. Shared conventions give the web layer a shape, and PHP gives you the flexibility to put the business rule where it reads clearly. [Laravel directory structure](https://laravel.com/docs/13.x/structure)

## Prerequisites

Use PHP 8.6 or later, Composer 2, and a local Code with PHP checkout. Allow 20 minutes, including dependency installation. Use a fresh demo app for the fixture.

## Quick Start

From the Code with PHP checkout, make a separate Laravel app in a new temporary directory. Copy the quote files into it.

```bash
fixture="$PWD/code/php-misconceptions/laravel"
sandbox="$(mktemp -d)"
composer create-project laravel/laravel "$sandbox/php-quotes" '^13.0' --no-interaction
cd "$sandbox/php-quotes"

cp -R "$fixture/app/." app/
cp "$fixture/routes/quotes.php" routes/web.php
cp "$fixture/tests/Feature/QuoteTest.php" tests/Feature/
php artisan test --filter=QuoteTest
```

Expect two passing tests. `/quotes?quantity=3` returns a quantity of `3` and `total_cents` of `3750`. A quantity of `0` returns HTTP 422 for a JSON request, with a validation error on the quantity. [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests)

## Follow the price through the app

1. The route maps `GET /quotes` to a single-action controller, giving you one entry point to follow.
2. `QuoteRequest` checks that the quantity is a whole number between 1 and 100. It converts the validated query parameter into the integer the rest of the code expects.
3. `QuoteController` passes that integer to `SeatPrice` and returns the result as JSON. Laravel resolves the concrete pricing class through its container.
4. `SeatPrice` holds the price per seat and the calculation. It expects a validated quantity between 1 and 100. The Form Request enforces that range for HTTP requests. A console command should validate its own input before passing it to the same class. [Form Request validation](https://laravel.com/docs/13.x/validation#form-request-validation) · [Laravel service container](https://laravel.com/docs/13.x/container)

The HTTP test checks the behaviour visible to another developer. Pint can enforce a consistent formatting style, while the names and boundaries help someone decide where to make the next change. If the price is just a one-off calculation, putting it directly in a small controller can be a reasonable choice. Give it a home of its own when you need to share the rule or test it independently. [Laravel Pint](https://laravel.com/docs/13.x/pint)

## Exercise

Change the price to 1,500 cents per seat. Update the success test to expect `4500`, rerun the tests and confirm that the invalid-quantity case still passes.

## Next chapter

[Is PHP Old and Dead?](/series/php-misconceptions/chapters/05-php-is-still-evolving/)
