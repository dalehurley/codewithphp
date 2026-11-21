---
title: "13: Does Legacy PHP Always Need a Rewrite?"
description: "Rehearse a legacy PHP upgrade with a behaviour baseline, a Composer constraint lab and a tested cutover decision."
series: "php-misconceptions"
chapter: 13
estimatedTime: "PT25M"
sidebar:
  order: 13
---

<span id="overview"></span>

## Decide what has to change

Your reporting integration reads `paid` for a cleared order. The export still works, but a PHP diagnostic and a dependency constraint are in the way of moving it to a newer runtime. Can you clear both blockers and keep the integration working?

Start with an incremental upgrade when you can preserve the contracts callers rely on and rehearse recovery. Compare a rewrite when core requirements have changed or the incremental route cannot meet those contracts and recovery needs at acceptable cost. In this chapter, you'll fix the export, clear a dependency constraint, then decide whether the evidence supports a limited release.

Open Bash or Zsh at the root of the repository. Follow the checkout instructions if you arrived here directly. Use `php -v` and `composer --version` to confirm the tools on your path. The first runs use PHP 8.5 to represent the current deployment. They were checked with PHP 8.5.0 and Composer 2.8.12 and 2.11.2. PHP 8.6 is the prospective target and the series baseline. [Checkout instructions](/series/php-misconceptions/chapters/01-php-types/#prerequisites)

## Practise a compatibility fix

Capture the existing behaviour in a disposable copy. The export prints `paid` for a cleared order and a blank line for an unlabeled order. Each command captures the export on stdout and the diagnostic on stderr. [Legacy fixture](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/legacy-order-label.php) · [PHP 8.2 deprecations](https://www.php.net/manual/en/migration82.deprecated.php)

```bash
sandbox="$(mktemp -d)"
(
    set -eu
    cp code/php-misconceptions/legacy-order-label.php "$sandbox/"
    cd "$sandbox"
    php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php > before.out 2> before.err
    printf 'paid\n' | cmp - before.out
    grep -F 'Creation of dynamic property LegacyOrder::$label is deprecated' before.err
    php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php --unlabeled > before-unlabeled.out 2> before-unlabeled.err
    printf '\n' | cmp - before-unlabeled.out
    grep -F 'Undefined property: LegacyOrder::$label' before-unlabeled.err
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

Expect `paid` again. PHP 8.2 deprecated creating dynamic properties on most classes, with exceptions such as `stdClass` and classes marked with `#[AllowDynamicProperties]`. Declaring the field clears the deprecation for the paid case. It also introduces an initialisation requirement. Run the unlabeled case with the typed property as written. Expect a fatal error when it reads the property before assigning it. [PHP dynamic property RFC](https://wiki.php.net/rfc/deprecate_dynamic_properties) · [Typed properties](https://www.php.net/manual/en/language.oop5.properties.php)

```bash
(cd "$sandbox" && php -d display_errors=stderr -d log_errors=0 legacy-order-label.php --unlabeled)
```

Change the declaration to `public ?string $label = null;` and the last line to `echo $order->label ?? '', PHP_EOL;`. Re-run the paid check, then check that an unlabeled order still emits exactly one newline and leaves stderr empty.

```bash
(
    set -eu
    cd "$sandbox"
    php -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php --unlabeled > after-unlabeled.out 2> after-unlabeled.err
    cmp before-unlabeled.out after-unlabeled.out
    test ! -s after-unlabeled.err
)
```

If a real input source can supply an integer label, record what the receiving integration expects before choosing how to validate or convert it. The nullable property accepts strings and null in this strict fixture. You now have evidence about two export outcomes on PHP 8.5. Check the dependency separately before trying the target runtime.

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

Expect the constraint `^2.0` in `composer.json`, the locked version `2.0.0` in the `show` output and no package prohibition for PHP 8.6.0. That settles the dependency's declared PHP constraint. `check-platform-reqs --lock` ignores `config.platform` and checks the PHP and extensions actually running Composer. On the lab's PHP 8.5 CLI, it can only establish that platform's result. An extension required by the lock can still be missing on the target. [Composer platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

When a PHP 8.6 CLI is available, set `target_php` to the absolute path of that executable and `composer_phar` to the absolute path of a current Composer PHAR. This block was checked on PHP 8.6.0 with Composer 2.11.2. Run it in the same shell. It requires PHP 8.6, installs from the updated lock in a fresh directory and runs both export checks on the target binary. [Composer downloads](https://getcomposer.org/download/)

```bash
(
    set -eu
    "$target_php" -v
    if ! "$target_php" -r 'exit(PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 6 ? 0 : 1);'; then
        printf '%s\n' 'Set target_php to a PHP 8.6 executable.' >&2
        exit 1
    fi
    target_run="$(mktemp -d)"
    cp "$deps/composer.json" "$deps/composer.lock" "$target_run/"
    "$target_php" "$composer_phar" --working-dir="$target_run" about
    COMPOSER_DISABLE_NETWORK=1 "$target_php" "$composer_phar" --working-dir="$target_run" install --no-interaction
    "$target_php" "$composer_phar" --working-dir="$target_run" check-platform-reqs --lock
    cd "$sandbox"
    "$target_php" -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php > target-paid.out 2> target-paid.err
    "$target_php" -d error_reporting=E_ALL -d display_errors=stderr -d log_errors=0 legacy-order-label.php --unlabeled > target-unlabeled.out 2> target-unlabeled.err
    cmp before.out target-paid.out
    cmp before-unlabeled.out target-unlabeled.out
    test ! -s target-paid.err
    test ! -s target-unlabeled.err
)
```

In a real upgrade, deploy the reviewed lock to staging and test the web, worker and scheduled processes on the target. The simulated package consists of metadata, so the target lab exercises the install and export separately. Each production process may load a different PHP configuration from the CLI. If all you have run is the PHP 8.5 lab, hold the PHP 8.6 rollout until you have target evidence. [Replaying the lock file in CI](/series/php-misconceptions/chapters/11-developer-tooling/)

## Plan the upgrade

1. **Inventory the running stack.** Record the PHP versions and extensions used by web requests, background workers and scheduled jobs, plus the framework and deployed lock file. Choose a supported target. Read PHP and framework upgrade notes for every version boundary on the route and check which framework and dependency versions can run on both sides. [PHP supported versions](https://www.php.net/supported-versions.php) · [PHP migration guides](https://www.php.net/manual/en/appendices.php) · [Laravel upgrade guide](https://laravel.com/docs/13.x/upgrade)
2. **Capture baseline outcomes.** On the current release and runtime, save assertions for normal and edge cases plus status, output and effects. Run the changed code and its reviewed lock file on the current and target runtimes when the supported versions overlap. Keep the output and diagnostics separate for each run. If the same lock file cannot run both versions, identify a compatible intermediate step or document the dependency/runtime change you must test together.
3. **Rehearse the release.** Verify the new web, worker and scheduled processes in staging. Introduce compatible schema changes before switching code that requires them. Keep old reads and writes working while deployments overlap. Rehearse restarting or draining workers so old handlers and new messages interact only in combinations you have tested. [Laravel worker deployment](https://laravel.com/docs/13.x/queues#queue-workers-and-deployment) · [Shared state and capacity](/series/php-misconceptions/chapters/09-scaling-php/)

Suppose the release notes and package constraints for a fictional framework give you these choices.

| Framework release | PHP requirement | Outcome |
| --- | --- | --- |
| 4.2, deployed | `>=8.4 <8.6` | Runs on the current PHP 8.5 runtime. Support ends in a month. |
| 4.3, supported | `>=8.5 <8.7` | Runs on both PHP 8.5 and 8.6. |
| 5.0, desired | `>=8.6 <8.7` | Requires the target runtime. |

Choose 4.3 as the bridge. Rehearse and deploy it on PHP 8.5 before support for 4.2 ends. Verify 4.3 on both runtimes before cutting over to PHP 8.6, then take the 5.0 upgrade once the PHP cutover is stable. With no supported intermediate release, test the new framework and runtime together as one candidate. Read the migration notes for every PHP version crossed, even if you make one runtime switch.

If the blocker is confined to an unmaintained export package, consider replacing that package behind the same export contract. Compare a rewrite if upgrading or replacing components cannot meet the support deadline, contract or recovery needs. Include data migration and rollback in the estimate. [PHP migration guides](https://www.php.net/manual/en/appendices.php) · [Composer prohibits](https://getcomposer.org/doc/03-cli.md#prohibits-why-not)

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

Before a canary, where a small share of real traffic uses the candidate, agree on what will stop the rollout. Halt on an unexpected export label or file shape, a rejected receipt, or a duplicate delivery. Compare error rate, p95 latency and queue age with the recorded baseline. Hold the release if the previous release cannot handle the candidate’s state and you have no rehearsed recovery path for it.

## Exercise

Assume the candidate passes the staging checks on the target runtime. The candidate also writes a new status field and can emit a new receipt format. Reporting still reads the original label, and the previous web and worker releases may still be active. Run this local model to see what those releases can handle. [Cutover fixture](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/legacy-upgrade/rehearse-cutover.php)

```bash
php code/php-misconceptions/legacy-upgrade/rehearse-cutover.php
```

Look for the legacy overwrite and rejected receipt in the output. After a compatible write, the candidate reads `reconciled` from the new field and reporting still reads `paid` from the label. In this exercise, `PASS` means the script observed the named outcome, even when that outcome calls for a hold. Check that the compatible worker prints one delivery for each receipt ID even though `R2` is presented twice. The model uses an in-memory record and delivery list. In staging, repeat the writes on a disposable order and replay a delayed job and its retry through the real queue and delivery path.

Would you start the PHP canary with the current write and payload behaviour? What evidence would permit the PHP canary while holding the new receipt format for a later release? Return to the framework table. Suppose the existing export package works with framework 4.3 on PHP 8.5 but has no maintained PHP 8.6 release. What must ship before support for 4.2 ends in one month? Explain how you would preserve the `paid` reporting contract and rehearse recovery before the PHP cutover.

<details>
<summary>Check your reasoning</summary>

Hold the combined release. A previous web process that replaces the whole order can erase the candidate's status field, and a previous worker can reject the new payload. First make the order writes compatible or defer writing the new status. Verify both directions in staging. Have the previous release create an order without a status and check the candidate can read it. Then have the candidate write the status, have the previous release update the order, and read it again through the candidate and the reporting integration. Confirm that the new status and original label survive.

Start the PHP canary once the target, mixed-version and recovery checks pass, while continuing to emit the original receipt format. Enable the new format after compatible workers are ready. If both formats share a queue, the new workers must accept both while old-format jobs remain eligible for delivery. Route the new format to a versioned queue if previous workers could otherwise receive it.

For rollback, disable the producers of new-format jobs first. Keep a compatible worker available to drain or recover queued, delayed and in-flight jobs and their retries before restoring any older worker that would see them. Resume the rollout after the target boundary checks and the rollback rehearsal succeed.

If recovery yields a lost or duplicate receipt, hold the format change even if the PHP canary succeeds. For the framework case, move to 4.3 on PHP 8.5 with the existing export package before 4.2 support ends. Replace the package before the PHP 8.6 cutover, and test the replacement on PHP 8.5 first if it supports both versions. Check that reporting still receives `paid` and rehearse recovery on staging data. A rewrite would have to carry forward the reporting contract, migrate data and prove recovery before it could replace the deployed 4.2 app.

</details>

## Continue learning

Use the [PHP 8.6 series](/series/php-8-6/) to choose changes to test on the target runtime. The [PHP Misconceptions series](/series/php-misconceptions/) links the earlier labs if you need a fixture for a specific boundary.
