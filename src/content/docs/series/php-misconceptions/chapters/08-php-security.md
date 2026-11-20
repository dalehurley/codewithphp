---
title: "08: Is PHP Inherently Insecure?"
description: "Build and test a private Laravel invoice route, from input and ownership checks to escaped output, caching and deployment."
series: "php-misconceptions"
chapter: 8
estimatedTime: "PT25M"
sidebar:
  order: 8
---

## Overview

An invoice belongs to one customer. The app must show it to the owner and keep it private from other customers. The URL supplies an invoice ID. The session supplies the customer's identity. This lab sends the same URL as the owner, as another signed-in customer and as a guest so you can see what the app decides in each case.

## Run the invoice lab

Use Bash or Zsh, a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp), PHP 8.5 or later with Laravel’s required extensions and PDO SQLite, and a current Composer 2 release. This lab uses a disposable Laravel 13 app and follows the same setup as the [quote lab](/series/php-misconceptions/chapters/04-clean-laravel-code/). [Laravel server requirements](https://laravel.com/docs/13.x/deployment#server-requirements)

Start at the checkout root. The commands run in a subshell that stops on failure and leaves your terminal in the checkout. The install may create a starter SQLite file in the demo. The feature tests use a separate in-memory SQLite database. They create their own accounts and invoice.

```bash
(
    set -eu
    fixture="$PWD/code/php-misconceptions/invoice-lab"
    sandbox="$(mktemp -d)"
    composer create-project laravel/laravel "$sandbox/php-invoices" '^13.0' --no-interaction
    cd "$sandbox/php-invoices"
    cp -R "$fixture/." .
    printf '\nrequire __DIR__ . "/invoice-lab.php";\n' >> routes/web.php

    php artisan route:list --path=invoice-lab
    APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=InvoiceAccessTest
    pwd
)
```

The route list should show two `GET|HEAD` routes, one for invoices and one for the lab’s sign-in placeholder. Expect five passing tests. `actingAs()` supplies a test user directly. The placeholder gives Laravel's browser redirect a destination in the fresh app. Use your application’s sign-in flow when adding a private route to a real project. [Lab files](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/invoice-lab) · [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests)

| Request | Expected result |
| --- | --- |
| Owner requests the fixture invoice | 200, escaped customer name and `Cache-Control` containing `private` and `no-store`. |
| Another signed-in customer requests the same invoice | 404 with the same JSON error as a missing invoice when debug is off. The response omits the customer name. |
| Owner requests `/invoice-lab/invoices/abc` | 404 because the path does not match the route. |
| Guest opens the fixture URL in a browser | 302 to the lab’s sign-in route. |
| Guest asks for JSON at the fixture URL | 401 with `{"message":"Unauthenticated."}`. The successful invoice route renders HTML. |

## Follow the invoice through the application

### Match the input and identify the customer

The route accepts a positive decimal invoice ID of up to nine digits. The pattern `[1-9][0-9]{0,8}` defines this lab’s ID format. Laravel’s `auth` middleware requires a signed-in customer on the matching route. The tests keep the invoice URL fixed when switching from one account to another. [Route source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/routes/invoice-lab.php) · [Route constraints](https://laravel.com/docs/13.x/routing#regular-expression-constraints) · [Laravel authentication](https://laravel.com/docs/13.x/authentication)

### Bind the lookup and check ownership

`Invoice::query()->whereKey($invoice)->first()` binds the requested ID as a data value in the lookup. Bind values in SQL. If an invoice list lets users pick a sort order, map each accepted option to a column your code defines. For example, map `number` to `id` ascending. SQL placeholders accept values such as the invoice ID. Column names and sort directions belong in the query your code builds. [Laravel query builder](https://laravel.com/docs/13.x/queries) · [PDO statement parameters](https://www.php.net/manual/en/pdo.prepare.php)

The route calls `Gate::allows('view', $record)` before building a response. Laravel discovers `InvoicePolicy` by its name and location. Its `view()` method compares the signed-in user’s ID with the stored `user_id`. For an app with organisations, include the current tenant and the user’s membership in the access decision. Scope list queries to the data the user may see. [Policy source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/app/Policies/InvoicePolicy.php) · [Laravel policies](https://laravel.com/docs/13.x/authorization#creating-policies)

The lab uses the same 404 response for a missing record and one owned by another customer. The test compares their JSON error bodies with debug off. A product where members are allowed to know a record exists can use 403 for a denied action. Pick the public behaviour you want and keep it in the tests. [Laravel policy responses](https://laravel.com/docs/13.x/authorization#policy-responses) · [Invoice access tests](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/tests/Feature/InvoiceAccessTest.php)

### Render the name and protect the response

The stored name is `Design & "Review" <Partners>`. The Blade view uses `{{ $invoice->customer_name }}` to escape it. The response source contains `Design &amp; &quot;Review&quot; &lt;Partners&gt;` and the browser displays the original name as text. Escape at the point of output. For a user-provided hyperlink, also validate the URL and restrict its allowed schemes. Use Blade’s `Js::from()` helper to pass a value to JavaScript in a script element. [Invoice view](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/resources/views/invoice-lab.blade.php) · [Blade output and JSON](https://laravel.com/docs/13.x/blade#displaying-data) · [PHP HTML escaping](https://www.php.net/manual/en/function.htmlspecialchars.php)

The route sends `Cache-Control: private, no-store` for the invoice and for the 404 it produces after looking up a record. `no-store` directs HTTP caches to avoid storing the response. Check that your proxy or CDN honours the rule for private routes. [HTTP caching](https://www.rfc-editor.org/rfc/rfc9111.html#section-5.2.2.5) · [Shared infrastructure and cache boundaries](/series/php-misconceptions/chapters/09-scaling-php/)

## Check the deployment

1. Check the PHP version of the container or hosting runtime that serves web traffic. `php -v` reports the CLI binary you invoked, so check the web runtime separately through your hosting controls. Use the PHP support table to identify its branch and apply point releases from your host or package provider. [PHP supported versions](https://www.php.net/supported-versions.php)
2. Run `composer audit --locked` from the application root to check the exact package versions in `composer.lock` for reported advisories and other audit findings. Review the output and rerun your access tests after upgrades. [Composer audit](https://getcomposer.org/doc/03-cli.md#audit)
3. Serve the app over HTTPS with the web root set to `public/`. Keep secrets in the deployment environment, set `APP_DEBUG=false` and keep detailed logs available to operators. Run the owner, non-owner and guest checks in a test environment that uses the same auth and proxy configuration as production. [Laravel deployment](https://laravel.com/docs/13.x/deployment)

## Exercise

In the disposable app whose path the setup printed, open `tests/Feature/InvoiceAccessTest.php` and change `CUSTOMER_NAME` to `'Studio & <Drafts>'`. Run the test command from the setup again, this time from the app directory. Which check should fail? Update the escaped string assertion and rerun the tests. Restore the original fixture and assertion when you finish.

Now suppose customers can request a corrected invoice through a form. Which checks from the view route carry over and which ones does the new action need?

<details>
<summary>Check your reasoning</summary>

The owner test should fail where it checks the old escaped name. Change that assertion to `Studio &amp; &lt;Drafts&gt;`. The other tests already derive their disclosure check from the first word of the fixture name, so they now look for `Studio`. Expect all five tests to pass again.

For the form, keep the input and sign-in checks, and validate the new correction fields. Define who may request a correction and authorise that action against the specific invoice. Use a state-changing HTTP method such as `POST` on the browser route, retain Laravel’s request forgery middleware and include `@csrf` in the Blade form. Laravel 13 checks the browser origin and can use the session token as a fallback. Add feature tests for allowed and denied correction requests. Laravel skips CSRF verification during ordinary feature tests, so also verify the form in a local app with the middleware active. A valid same-origin submission should work. When origin verification is unavailable, a valid token should work and a missing or invalid token should be rejected without applying a correction. [Laravel CSRF protection](https://laravel.com/docs/13.x/csrf)

</details>

## Next chapter

[Can PHP Scale?](/series/php-misconceptions/chapters/09-scaling-php/)
