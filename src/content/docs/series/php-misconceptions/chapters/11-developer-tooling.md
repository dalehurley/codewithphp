---
title: "11: Does PHP Have Serious Developer Tools?"
description: "Use Composer, Laravel Pint, Larastan and PHPUnit to check a Laravel feature, and see where Xdebug fits."
series: "php-misconceptions"
chapter: 11
estimatedTime: "PT15M"
sidebar:
  order: 11
---

## Overview

Composer installs dependencies and generates an autoloader. With `composer.lock` committed for an application, CI and developers can install the same dependency versions. PSR-4 gives Composer a convention for mapping a namespaced class such as `App\Quotes\SeatPrice` to its file. [Composer basic usage](https://getcomposer.org/doc/01-basic-usage.md) · [PHP-FIG PSR-4](https://www.php-fig.org/psr/psr-4/)

## Run the quote feature through four checks

Start in the demo app from [Does PHP Have to Be Messy?](/series/php-misconceptions/chapters/04-clean-laravel-code/). The Laravel 13 skeleton supplies Pint and PHPUnit as development dependencies. Add Larastan, which extends PHPStan with Laravel-specific type information.

```bash
composer install
composer require --dev 'larastan/larastan:^3.0'
```

Create `phpstan.neon` at the root of the demo app.

```yaml
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    level: 5
    paths:
        - app/Http/Controllers/QuoteController.php
        - app/Http/Requests/QuoteRequest.php
        - app/Quotes/SeatPrice.php
```

Run Pint on the feature files, then run the checks a CI job can repeat.

```bash
quote_files=(app/Quotes/SeatPrice.php app/Http/Requests/QuoteRequest.php app/Http/Controllers/QuoteController.php routes/web.php tests/Feature/QuoteTest.php)
vendor/bin/pint "${quote_files[@]}"
vendor/bin/pint --test "${quote_files[@]}"
vendor/bin/phpstan analyse --no-progress
php artisan test --filter=QuoteTest
```

After formatting, expect the style check to pass, PHPStan to report no errors and both HTTP cases to pass. Pint checks formatting. Larastan checks the declared and inferred types in the paths you supplied. The HTTP tests check the returned JSON and error status. Xdebug lets you step through code and inspect values when the behaviour still surprises you. [Laravel Pint](https://laravel.com/docs/13.x/pint) · [Larastan](https://github.com/larastan/larastan) · [PHPUnit](https://phpunit.de/documentation.html) · [Xdebug step debugging](https://xdebug.org/docs/step_debug)

## Exercise

In the controller, temporarily change the pricing call to `$price->totalCents('three')`. Rerun PHPStan and look for the string passed to an integer parameter. Revert that edit, then change the price constant alone. The code still has the expected types. The feature test detects the changed total.

## Next chapter

[Which PHP Quirks Still Matter?](/series/php-misconceptions/chapters/12-language-quirks/)
