---
title: "13: Does Legacy PHP Always Need a Rewrite?"
description: "Rehearse a legacy PHP upgrade with a behaviour baseline, a Composer constraint lab and a tested cutover decision."
series: "php-misconceptions"
chapter: 13
estimatedTime: "PT25M"
sidebar:
  order: 13
---

## Overview

A legacy order export prints `paid` for an order that has cleared. Its readers rely on that value. A PHP upgrade is a chance to keep the export contract while replacing the old runtime and fixing the code or dependency that blocks it. The exercises below give you two kinds of evidence for that decision, followed by a staged release plan. [PHP migration guides](https://www.php.net/manual/en/appendices.php)

## Practise a compatibility fix

Use Bash or Zsh and PHP 8.5 to reproduce this diagnostic. Both labs were checked on PHP 8.5.0 and the dependency lab was checked with Composer 2.8.12. From the checkout root, capture the existing behavior in a disposable copy. The check stops if the program fails, the output changes or the expected diagnostic is missing. [Legacy fixture](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/legacy-order-label.php) · [PHP 8.2 deprecations](https://www.php.net/manual/en/migration82.deprecated.php)

```bash
sandbox="$(mktemp -d)"
(
    set -eu
    cp code/php-misconceptions/legacy-order-label.php "$sandbox/"
    cd "$sandbox"
    php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php > before.out 2> before.err
    printf 'paid\n' | cmp - before.out
    grep -F 'Creation of dynamic property LegacyOrder::$label is deprecated' before.err
    pwd
)
```

The diagnostic is separate from the `paid` output. Edit `legacy-order-label.php` in the printed directory. Add the property below and keep the existing assignment before the read.

```php
final class LegacyOrder
{
    public string $label;
}
```

Run this in the same terminal, which still has the `sandbox` path.

```bash
(
    set -eu
    cd "$sandbox"
    php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php > after.out 2> after.err
    cmp before.out after.out
    test ! -s after.err
    cat after.out
)
```

Expect `paid` again. The files preserve the unchanged output and show that the diagnostic disappeared. PHP 8.2 deprecated creating dynamic properties on most classes, with exceptions such as `stdClass` and classes that opt in with `#[AllowDynamicProperties]`. A fixed field belongs in the class definition. A typed property without a default must be initialised before it is read. If the old export emits a blank line for an unlabeled order and callers expect it, `?string $label = null` can represent that state and `echo $order->label ?? '', PHP_EOL;` can preserve the blank line. Test that case alongside `paid` and check the types accepted from each input source before narrowing the property type. [PHP dynamic property RFC](https://wiki.php.net/rfc/deprecate_dynamic_properties) · [Typed properties](https://www.php.net/manual/en/language.oop5.properties.php)

## Find a dependency that blocks the target

This offline example has two versions of a simulated export package. Version `1.0.0` allows PHP from 8.2 up to, but excluding, 8.6. Version `2.0.0` allows PHP `^8.2`, including 8.6. The embedded repository provides package metadata without application code. `config.platform.php` holds the solve at PHP 8.5.0 to represent the current deployment while `prohibits` investigates a prospective PHP 8.6.0. [Offline Composer fixture](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/legacy-upgrade/composer.json) · [Composer package repositories](https://getcomposer.org/doc/05-repositories.md#package) · [Composer platform configuration](https://getcomposer.org/doc/06-config.md#platform)

From the same checkout root, make a second disposable directory and generate its initial lock file. This fixture uses `--no-audit` because its packages are simulated and there is no external repository involved.

```bash
deps="$(mktemp -d)"
(
    set -eu
    cp code/php-misconceptions/legacy-upgrade/composer.json "$deps/"
    cd "$deps"
    COMPOSER_DISABLE_NETWORK=1 composer update --no-interaction --no-audit
    prohibits_status=0
    composer prohibits php 8.6.0 --locked > blocked.out 2>&1 || prohibits_status=$?
    test "$prohibits_status" -eq 1
    grep -F 'codewithphp/example-order-export' blocked.out | grep -F '>=8.2 <8.6'
)
```

Composer reports that `codewithphp/example-order-export` `1.0.0` requires `php (>=8.2 <8.6)`. The block checks both the exit status 1 from `prohibits` and the actual dependency message, since other failures can also return 1. Update just that dependency and repeat the check. [Composer prohibits](https://getcomposer.org/doc/03-cli.md#prohibits-why-not)

```bash
COMPOSER_DISABLE_NETWORK=1 composer --working-dir="$deps" require 'codewithphp/example-order-export:^2.0' --no-interaction --no-audit &&
    composer --working-dir="$deps" show --locked codewithphp/example-order-export &&
    composer --working-dir="$deps" prohibits php 8.6.0 --locked &&
    composer --working-dir="$deps" check-platform-reqs --lock
```

Expect the constraint `^2.0` in `composer.json`, the locked version `2.0.0` in the `show` output and no package prohibition for PHP 8.6.0. `check-platform-reqs --lock` compares the lock file with the PHP and extensions actually running Composer and ignores `config.platform`. If you run it on PHP 8.5 for this exercise, it checks PHP 8.5. In a real upgrade, repeat it inside the target runtime and run your application tests there. [Composer platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs) · [Replaying the lock file in CI](/series/php-misconceptions/chapters/11-developer-tooling/)

## Plan the upgrade

1. **Inventory the running stack.** Record the PHP versions and extensions used by web requests, background workers and scheduled jobs, plus the framework and deployed lock file. Choose a supported target. Read PHP and framework upgrade notes for every version boundary on the route and check which framework and dependency versions can run on both sides. [PHP supported versions](https://www.php.net/supported-versions.php) · [PHP migration guides](https://www.php.net/manual/en/appendices.php) · [Laravel upgrade guide](https://laravel.com/docs/13.x/upgrade)
2. **Capture baseline outcomes.** On the current release and runtime, save assertions for normal and edge cases plus status, output and effects. Run the changed code and its reviewed lock file on the current and target runtimes when the supported versions overlap. Keep the output and diagnostics separate for each run. If the same lock file cannot run both versions, identify a compatible intermediate step or document the dependency/runtime change you must test together.
3. **Rehearse the release.** Verify the new web, worker and scheduled processes in staging. Introduce compatible schema changes before switching code that requires them. Keep old reads and writes working while deployments overlap. Rehearse restarting or draining workers so old handlers and new messages interact only in combinations you have tested. [Laravel worker deployment](https://laravel.com/docs/13.x/queues#queue-workers-and-deployment) · [Shared state and capacity](/series/php-misconceptions/chapters/09-scaling-php/)

| Run | What its result tells you |
| --- | --- |
| Deployed release on the current runtime | Establishes the expected business output and records current diagnostics. |
| Candidate release on the current runtime, where supported | Isolates changes from the application patch and dependency updates before changing PHP. |
| Candidate release on the target runtime | Checks the actual runtime, extensions, HTTP behavior and background work that the prospective Composer result cannot exercise. |
| Previous release on its runtime against state left by the candidate | Checks recovery against the new schema, files and queued work, including delayed jobs and retries. |

| Boundary | Evidence to keep in each applicable run |
| --- | --- |
| Quote creation | The expected cents and response shape for a valid quantity, plus the 422 validation response for an invalid quantity. |
| Private invoice | The owner sees the expected invoice. A different account and an anonymous request get their intended outcomes without private data. |
| Order export and queued receipt | Confirm the export label and file shape, which message handlers accept each payload, and what a retried notification does. |

The [quote tests](/series/php-misconceptions/chapters/04-clean-laravel-code/) and [invoice access lab](/series/php-misconceptions/chapters/08-php-security/) give examples of assertions worth carrying forward. Include the worker’s effects, such as one delivered receipt after a retry. [Receipt recovery example](/series/php-misconceptions/chapters/10-php-concurrency/)

Before testing a small share of real traffic, agree on what will stop the rollout. For an export change, an unexpected label or file shape is a reason to halt. Compare error rate, p95 and queue age with the recorded baseline. Hold the release if the previous release cannot handle the candidate’s state and you have no rehearsed recovery path for it.

## Exercise

The typed-field fix and Composer checks pass. The new release writes a status field to the order record and introduces a receipt payload that the previous worker rejects. An older reporting integration still needs the original label. Would you deploy the PHP change, the new field and the new job payload together? When might a rewrite be worth comparing with this incremental path?

<details>
<summary>Check your reasoning</summary>

Keep the job payload change out of the PHP canary. Retain the original label and add the status field without removing data the old code needs. Verify that the previous web release can still read and update an order after the new release has written it. Keep emitting the old job format during the canary. Enable the new format only after compatible workers are ready. If both formats share a queue, the new workers must accept both while old-format jobs remain eligible for delivery. Route the new format to a versioned queue if old workers could otherwise receive it.

For rollback, disable the producers of new-format jobs first. Keep a compatible worker available to drain or recover queued, delayed and in-flight jobs and their retries before restoring any older worker that would see them. Resume the rollout after the target boundary checks and the rollback rehearsal succeed.

Here the incompatible payload calls for a separate cutover. Compare a rewrite if core requirements have changed or a supported incremental sequence cannot meet the existing contracts and recovery needs at acceptable cost. Include the cost of discovering old rules, migrating data and customers, and rehearsing the replacement’s own rollout and recovery.

</details>

## Continue learning

Use the [PHP 8.6 series](/series/php-8-6/) to choose changes to test on the target runtime. The [PHP Misconceptions series](/series/php-misconceptions/) links the earlier labs if you need a fixture for a specific boundary.
