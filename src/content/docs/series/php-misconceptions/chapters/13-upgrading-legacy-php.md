---
title: "13: Does Legacy PHP Always Need a Rewrite?"
description: "Make a legacy PHP upgrade smaller by capturing existing behaviour, resolving dependency constraints and rehearsing the cutover."
series: "php-misconceptions"
chapter: 13
estimatedTime: "PT15M"
sidebar:
  order: 13
---

## Overview

An old PHP application may contain working business rules alongside obsolete dependencies and language behaviour. You can often carry those rules into a supported PHP version while retaining the application’s current framework. Start by finding which part of the stack prevents an upgrade and which behaviour users rely on. [PHP migration guides](https://www.php.net/manual/en/appendices.php)

## Practise a compatibility fix

From the Code with PHP checkout, use PHP 8.5 to run this exercise. Copy the example to a temporary directory and capture its output and diagnostics separately.

```bash
sandbox="$(mktemp -d)"
cp code/php-misconceptions/legacy-order-label.php "$sandbox/"
cd "$sandbox"
php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php > before.out 2> before.err
cat before.out before.err
```

The output is `paid`. The diagnostic reports that creating the dynamic `LegacyOrder::$label` property is deprecated. Add `public string $label;` inside the class, keeping the assignment before the read. Run the same check again.

```bash
php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php > after.out 2> after.err
cmp before.out after.out
test ! -s after.err
```

Both checks exit successfully when the output is identical and the diagnostic file is empty. In an application, capture the same kind of baseline at a boundary such as the [quote feature’s HTTP tests](/series/php-misconceptions/chapters/04-clean-laravel-code/) and run it on your target PHP version.

## Plan the upgrade

1. **Inventory the runtime.** Record the versions serving web traffic, background jobs and scheduled jobs, plus extensions and the committed dependency lock file. Check which branches still receive fixes.
2. **Find the blockers.** In a Composer project, use `composer prohibits php 8.6.0` to see which declared constraints block that example target. Read the PHP and framework upgrade notes for every version boundary you cross. `composer check-platform-reqs` checks the PHP version and extensions available where you run it. [Composer CLI](https://getcomposer.org/doc/03-cli.md#prohibits-why-not) · [PHP migration guides](https://www.php.net/manual/en/appendices.php)
3. **Capture the business rules.** Use fixtures for ordinary and edge cases. Assert outputs and effects such as invoice totals, exports, access checks and jobs dispatched. Keep a reviewed baseline for any intentional behaviour changes.
4. **Fix one compatibility issue at a time.** PHP 8.2 deprecated creating dynamic properties on most classes. If a property belongs to every instance, give it an explicit declaration and choose its initial state from the behaviour you captured. Read the same guide for exceptions where dynamic properties remain valid. [PHP 8.2 deprecations](https://www.php.net/manual/en/migration82.deprecated.php)
5. **Rehearse the cutover.** Run web and worker checks against representative staging data, monitor failures and define a rollback path. If there is a schema migration, verify what running the previous code against the new schema would do.

A rewrite can make sense when the domain has changed or the migration costs more than replacing the system. Compare that cost against a tested incremental path and include the cost of rediscovering the rules in the old application.

## Continue learning

The [PHP 8.6 series](/series/php-8-6/) has focused labs for changes you can test before upgrading. Return to [PHP Misconceptions](/series/php-misconceptions/) to choose another claim.
