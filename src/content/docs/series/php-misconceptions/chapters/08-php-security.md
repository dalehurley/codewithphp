---
title: "08: Is PHP Inherently Insecure?"
description: "Understand how PHP and Laravel support defence in depth, and check the security decisions your application still owns."
series: "php-misconceptions"
chapter: 8
estimatedTime: "PT10M"
sidebar:
  order: 8
---

## Overview

Suppose a signed-in customer opens an invoice. The app needs to check that the requested ID is valid, that this customer can view that invoice and that the data reaches the browser safely. Those checks address different risks. PHP and Laravel give you tools for each of them.

## Follow an invoice through the application

| Stage | Question | Mechanism |
| --- | --- | --- |
| Input | Does this identifier have the expected form? | Route constraints or request validation |
| Access | May this customer view this invoice? | A policy tied to the specific invoice, with tenant scope where the app needs it |
| Storage | Will the database treat the ID as data? | Parameter binding through PDO or the query builder. Use an allowlist for dynamic identifiers such as sort columns |
| Output | Can a customer name be rendered as text? | Blade’s escaped output for ordinary HTML text and context-appropriate encoding elsewhere |
| Deployment | Does the running stack still receive fixes? | A supported, patched runtime, a dependency audit and production configuration with debug output disabled |

Authentication establishes who the requester is. A policy checks what that person may see. Parameter binding helps keep values out of SQL syntax, and escaping helps keep text out of HTML markup. Each check still matters when the others pass. [Laravel authorisation](https://laravel.com/docs/13.x/authorization) · [PDO prepared statements](https://www.php.net/manual/en/pdo.prepared-statements.php) · [Blade output](https://laravel.com/docs/13.x/blade#displaying-unescaped-data)

Check the PHP version actually serving requests and get security fixes for your active branch. `composer audit` checks package advisories. An audit of your dependencies and a check of application access controls cover different parts of your system. [PHP supported versions](https://www.php.net/supported-versions.php) · [Composer audit](https://getcomposer.org/doc/03-cli.md#audit) · [Laravel deployment](https://laravel.com/docs/13.x/deployment)

## Exercise

For the HTTP tests, use an existing demo app with an authenticated invoice route and its invoice policy. Create two test accounts and one fixture invoice owned by the first account. Use the same valid invoice ID in all three requests. Each test exercises a different access decision.

- Sign in as the owner with `actingAs()`. Assert HTTP 200 and the expected invoice data.
- Sign in as the other account. Assert your intended 403 or 404 and check that the response omits the fixture’s invoice data.
- Leave the request signed out. Assert the login redirect for a browser route or HTTP 401 for a JSON route.

If you don’t have an invoice route, write the three cases as a test plan using an example invoice ID and two named accounts. [Laravel HTTP tests](https://laravel.com/docs/13.x/http-tests)

## Next chapter

[Can PHP Scale?](/series/php-misconceptions/chapters/09-scaling-php/)
