---
title: "08: Is PHP Inherently Insecure?"
description: "Build and test a private Laravel invoice route, from input and ownership checks to escaped output, caching and deployment."
series: "php-misconceptions"
chapter: 8
estimatedTime: "PT25M"
sidebar:
  order: 8
---

<span id="overview"></span>

## Who can view the invoice?

Maya opens her invoice and gets `200 OK`. Jordan, another signed-in customer, requests the same URL and gets `404 Not Found`. What changes when the invoice ID stays the same? The session identifies the customer. The app compares that customer's ID with the owner ID stored on the invoice.

PHP can serve a private invoice safely when the application enforces who may see it. PHP supplies tools for safe database queries and output. Laravel adds authentication and runs the app's authorisation policy. In this lab, you'll run five tests and follow that policy decision from the URL to the response. [Laravel authorisation](https://laravel.com/docs/13.x/authorization)

## Run the invoice lab

Use Bash or Zsh, a local [Code with PHP checkout](https://github.com/dalehurley/codewithphp) and Composer 2. The lab was verified on PHP 8.5.0 with Laravel 13.43.0. Use PHP 8.5 to match the tested branch. Laravel 13 itself requires PHP 8.3 or later and its listed extensions. The lab also needs PDO SQLite. Run `php --ri pdo_sqlite` to check that the CLI has the driver. [Laravel PHP requirements](https://laravel.com/docs/13.x/deployment#laravel-php-requirements) · [Quote lab setup](/series/php-misconceptions/chapters/04-clean-laravel-code/)

Start at the checkout root, where `code/php-misconceptions/invoice-lab/` is available. If the copy step can't find the fixture, return to that directory before retrying. The commands create a disposable app and run in a subshell that stops on failure and leaves your terminal in the checkout. The tests create their accounts and invoice in a separate in-memory SQLite database.

```bash
(
    set -eu
    fixture="$PWD/code/php-misconceptions/invoice-lab"
    sandbox="$(mktemp -d)"
    composer create-project laravel/laravel "$sandbox/php-invoices" '^13.0' --no-interaction
    cd "$sandbox/php-invoices"
    pwd
    cp -R "$fixture/." .
    printf '\nrequire __DIR__ . "/invoice-lab.php";\n' >> routes/web.php

    php artisan route:list --path=invoice-lab
    APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=InvoiceAccessTest
)
```

The route list should show two `GET|HEAD` routes, one for invoices and one for the lab's sign-in placeholder. Expect these five tests to pass. [Lab files](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/invoice-lab) · [Invoice access tests](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/tests/Feature/InvoiceAccessTest.php)

- **Owner opens the invoice.** `200` with escaped HTML and `Cache-Control` containing `private` and `no-store`.
- **Another customer requests JSON at the same URL.** `404` with the same JSON content as a missing invoice when debug is off. This denial includes `no-store`.
- **Owner opens an invoice URL ending in `abc`.** `404` because the path fails the route constraint.
- **Guest opens the invoice URL as a browser request.** `302` to the sign-in placeholder.
- **Guest requests JSON at the invoice URL.** `401` with `{"message":"Unauthenticated."}`.

The tests simulate requests inside Laravel and use `actingAs()` to supply signed-in identities. The placeholder gives the browser redirect a destination, and the successful response renders HTML. The assertions cover the app's responses and headers. Use your application's real sign-in flow and proxy to repeat the checks in a deployed test environment. [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests)

## Follow the invoice through the application

### Match the input and identify the customer

The invoice URL gives Laravel an ID to look up. The route matches positive decimal IDs of up to nine digits using `[1-9][0-9]{0,8}`. Once the route matches, the `auth` middleware requires a signed-in customer before the app looks up the invoice. The test with `abc` stops at the route constraint. The two guest tests reach the authentication check. [Route source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/routes/invoice-lab.php) · [Route constraints](https://laravel.com/docs/13.x/routing#regular-expression-constraints) · [Laravel authentication](https://laravel.com/docs/13.x/authentication)

### Bind the lookup and check ownership

For a signed-in customer, `whereKey($invoice)` binds the requested ID as a data value. The database returns the matching invoice if it exists. To decide whether that record can be shown, the route calls `Gate::allows('view', $record)`. Laravel finds `InvoicePolicy` by convention and passes in the signed-in user and the invoice. The policy makes the ownership rule explicit. [Laravel query builder](https://laravel.com/docs/13.x/queries) · [Policy discovery](https://laravel.com/docs/13.x/authorization#policy-discovery)

```php
public function view(User $user, Invoice $invoice): bool
{
    return $user->id === $invoice->user_id;
}
```

Maya passes the comparison, so the route renders her invoice. Jordan fails it, so the route returns `404`. A missing record takes the same `404` branch. Comparing the status and JSON content with debug off verifies what this route discloses in those two responses. Use `403 Forbidden` when callers are allowed to know the record exists and you want to report a denied action. Test the response formats your product promises, including HTML if the browser errors should match. [Policy source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/app/Policies/InvoicePolicy.php) · [HTTP semantics for 403](https://www.rfc-editor.org/rfc/rfc9110.html#section-15.5.4) · [Laravel policy responses](https://laravel.com/docs/13.x/authorization#policy-responses)

The same boundaries apply outside Laravel. Bind the ID with a PDO prepared statement, then apply the ownership rule using the authenticated identity and the stored owner. When you add an invoice list, scope the query to records the user may see. For an app with organisations, include the current tenant and the user's membership in the policy. If the list offers sorting, map choices such as `number` to a column and direction defined in your code. SQL placeholders bind values such as the invoice ID. [PDO statement parameters](https://www.php.net/manual/en/pdo.prepare.php)

### Render the name and protect the response

Once the policy allows the request, Blade escapes the stored customer name with `{{ $invoice->customer_name }}`. Blade uses PHP's `htmlspecialchars()` for escaped output. The HTML source contains `Design &amp; &quot;Review&quot; &lt;Partners&gt;`, which the browser displays as `Design & "Review" <Partners>`. Escape for the context at the point of output. For a user-provided hyperlink, also validate the URL and restrict its allowed schemes. Use Blade's `Js::from()` helper to pass a value to JavaScript in a script element. [Invoice view](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/invoice-lab/resources/views/invoice-lab.blade.php) · [Blade output and JSON](https://laravel.com/docs/13.x/blade#displaying-data) · [PHP HTML escaping](https://www.php.net/manual/en/function.htmlspecialchars.php)

The decision also has to hold when requests pass through a shared proxy. The route sends `Cache-Control: private, no-store` for the successful invoice and both `404` outcomes from the lookup. `private` bars shared HTTP caches from storing the response. `no-store` applies to both private and shared HTTP caches. [HTTP caching](https://www.rfc-editor.org/rfc/rfc9111.html#section-5.2.2.5) · [Shared infrastructure and cache boundaries](/series/php-misconceptions/chapters/09-scaling-php/)

## Check the deployment

1. Check the PHP version of the container or hosting runtime that serves web traffic. `php -v` reports the CLI binary you invoked, so check the web runtime separately through your hosting controls. Use the PHP support table to identify its branch and apply point releases from your host or package provider. [PHP supported versions](https://www.php.net/supported-versions.php)
2. Run `composer audit --locked` from the application root to check the exact package versions in `composer.lock` for reported advisories and other audit findings. Review the output and rerun your access tests after upgrades. [Composer audit](https://getcomposer.org/doc/03-cli.md#audit)
3. Serve the app over HTTPS with the web root set to `public/`. Keep secrets in the deployment environment, set `APP_DEBUG=false` and keep detailed logs available to operators. [Laravel deployment](https://laravel.com/docs/13.x/deployment)

In a test deployment with the production auth and proxy configuration, create one synthetic invoice and two test accounts. Use separate browser profiles for the owner, the other customer, and a signed-out guest. Open the same invoice URL as the owner first, then as the other customer and the guest. In the browser's Network panel, record the original request's status, the response headers and which invoice details appear. Expect the owner to see the invoice, the other customer to see a `404` without invoice details, and the guest to be redirected to sign-in.

Check that `no-store` reaches the browser on the invoice and on the lookup's `404`. Repeat the owner and other customer's requests. Use proxy or CDN cache logs or an available cache-status header to verify that each request bypassed the cache or went to the origin without storing the response. For an app that serves JSON errors, repeat the relevant JSON checks through the same proxy. [HTTP Cache-Status field](https://www.rfc-editor.org/rfc/rfc9211.html#section-2)

## Exercise

Suppose an invoice owner can request a correction through a form. Who may submit it? Write down the expected database result for a valid owner's request, a request from another customer, and an owner's request with invalid fields. Use those outcomes as acceptance criteria when you build the form.

To practise with the existing lab, enter the disposable app whose path the setup printed. In `tests/Feature/InvoiceAccessTest.php`, change `CUSTOMER_NAME` to `'Studio & <Drafts>'`. Which assertion should fail? Run the tests, update that assertion and run them again. Restore the original fixture and assertion when you finish.

```bash
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=InvoiceAccessTest
```

<details>
<summary>Check your reasoning</summary>

Require sign-in, check the invoice ID, and authorise the correction action against the specific invoice. Define the expected database state in your feature tests.

- A valid owner submission creates one correction request linked to the invoice.
- A submission from another customer creates zero correction requests and leaves the invoice as it was.
- An owner submission with invalid fields returns validation feedback, creates zero correction requests and leaves the invoice as it was.

Use `POST`, keep Laravel's request forgery middleware and include `@csrf` in the Blade form. Cross-site request forgery (CSRF) protection helps prevent another site from using a customer's browser session to submit a correction. Laravel skips this check during ordinary feature tests. With the middleware active in a local app, confirm a valid same-origin submission works. Laravel 13 can accept it based on the browser's `Sec-Fetch-Site` header alone. With default middleware settings, also verify the token fallback on a local submission without a qualifying origin signal. Check the request headers to confirm `Sec-Fetch-Site` is absent for that check. A valid token should allow the submission. A missing or invalid token should result in zero correction requests. [Laravel CSRF origin verification](https://laravel.com/docs/13.x/csrf#origin-verification)

In the lab exercise, the owner test fails where it checks for the old escaped name. Change that assertion to `Studio &amp; &lt;Drafts&gt;`. The denial tests derive their disclosure check from the first word of the fixture name, so they now look for `Studio`. Expect all five tests to pass again.

</details>

## Next chapter

[Can PHP Scale?](/series/php-misconceptions/chapters/09-scaling-php/)
