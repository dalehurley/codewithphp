---
title: "PHP Misconceptions"
description: "Put thirteen common claims about PHP and Laravel to the test with runnable examples, a local performance lab, and the trade-offs that still matter."
---

If you last used PHP to put a contact form on a website, you might be surprised by what you can write today. PHP can enforce a typed function signature, and Laravel can keep a booted application in memory between requests. The dollar signs are still there. The runtime is still free.

Start with a function that accepts or rejects a numeric string depending on the calling file. Then measure the same Laravel app in two serving modes and watch one change to the database query remove forty queries from a request. Along the way, you’ll see why some old criticisms still matter and how to recognise them in the code you maintain.

## What you’ll need

Read the chapters in order or jump to a claim you’ve heard. Run the examples from a local checkout of Code with PHP. PHP 8.6 is the series baseline, and each chapter lists the requirements for its lab. The Laravel labs use a disposable Laravel 13 app with Composer and SQLite. [Series examples](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions)

Chapter 2’s OPcache lab uses PHP 8.5 or later and curl. Chapter 3’s Laravel benchmark also uses FrankenPHP and ApacheBench. [OPcache lab](/series/php-misconceptions/chapters/02-php-performance/) · [Laravel benchmark](/series/php-misconceptions/chapters/03-laravel-performance/)

## Types, speed and code quality

1. [PHP can’t be typed](/series/php-misconceptions/chapters/01-php-types/) — Compare runtime checks, caller-side strictness and static analysis.
2. [PHP is always slow](/series/php-misconceptions/chapters/02-php-performance/) — Work out where OPcache, JIT and PHP-FPM fit into a request.
3. [Laravel is too slow for production](/series/php-misconceptions/chapters/03-laravel-performance/) — Measure Octane with FrankenPHP and change the shape of a database query.
4. [PHP code is always messy](/series/php-misconceptions/chapters/04-clean-laravel-code/) — Follow a Laravel quote through validation, pricing and tests.

## Age, cost and use cases

5. [PHP is old and dead](/series/php-misconceptions/chapters/05-php-is-still-evolving/) — Check the support calendar and run an enum with a property hook.
6. [PHP is expensive because it’s full of dollar signs](/series/php-misconceptions/chapters/06-dollar-signs-and-cost/) — Read PHP’s variable syntax and calculate the cost of running an app.
7. [PHP is only for WordPress](/series/php-misconceptions/chapters/07-beyond-wordpress/) — Run one inventory report as a command and as a JSON endpoint.

## Production and maintenance

8. [PHP is inherently insecure](/series/php-misconceptions/chapters/08-php-security/) — Trace a request through validation, authorisation, the database and the response.
9. [PHP can’t scale](/series/php-misconceptions/chapters/09-scaling-php/) — Follow a Laravel application as it grows from one server to three.
10. [PHP can’t do concurrency](/series/php-misconceptions/chapters/10-php-concurrency/) — Run a Fiber and choose between concurrent requests, parallel I/O and a queue.
11. [PHP has no serious developer tools](/series/php-misconceptions/chapters/11-developer-tooling/) — Check a Laravel feature with Composer, Pint, Larastan and PHPUnit.
12. [PHP’s old quirks explain everything about it](/series/php-misconceptions/chapters/12-language-quirks/) — Try the comparisons, return values and arrays that still surprise developers.
13. [Legacy PHP always needs a rewrite](/series/php-misconceptions/chapters/13-upgrading-legacy-php/) — Capture existing behaviour and plan a PHP upgrade in stages.
