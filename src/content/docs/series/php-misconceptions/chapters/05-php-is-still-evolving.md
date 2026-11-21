---
title: "05: Is PHP Old and Dead?"
description: "Model order states with a backed enum and a property hook, then decide how to support the PHP branch your app runs."
series: "php-misconceptions"
chapter: 5
estimatedTime: "PT20M"
sidebar:
  order: 5
---

<span id="overview"></span>

## Why does a paid order still say pending?

Suppose your checkout marks order #12 as paid, but the confirmation screen still says pending. The screen reads a label that was saved when the order was created. If the label always comes from the order ID and current status, you can compute it on each read and avoid maintaining a second copy.

You’ll use a backed enum for the allowed states and a property hook for the label. Then you’ll decide whether to use the hook in an app whose production PHP version may differ from your laptop’s. PHP has been adding features since its first public release in 1995, and PHP 8.6 shipped on 19 November 2026. Each addition has a minimum version, and each PHP branch has its own support dates. [PHP history](https://www.php.net/manual/en/history.php.php) · [PHP 8.6 announcement](https://www.php.net/releases/8.6/en.php) · [PHP 8 changelog](https://www.php.net/ChangeLog-8.php)

## Quick Start

Run from the root of the [Code with PHP checkout](https://github.com/dalehurley/codewithphp). These order scripts require PHP 8.4 or later because they load a property hook. The series baseline is PHP 8.6. [PHP 8.4 migration guide](https://www.php.net/manual/en/migration84.new-features.php)

```bash
php --version
php code/php-misconceptions/modern-php.php
```

```text
Order #12 is pending.
Order #12 is paid.
```

## Keep the label in sync with the order

The script reads the label twice, with a single status change in between. Both reads use the same order.

```php
$order = new Order(12, OrderStatus::Pending);
echo $order->label, PHP_EOL;

$order->status = OrderStatus::Paid;
echo $order->label, PHP_EOL;
```

`Order.php` defines the allowed states and how to compute the label.

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
}

final class Order
{
    public string $label {
        get => "Order #{$this->id} is {$this->status->value}.";
    }

    public function __construct(
        public readonly int $id,
        public OrderStatus $status,
    ) {
    }
}
```

Here `OrderStatus::Paid` is an enum case, and its backing value is the string `'paid'`. The order stores the case. Each read of `$order->label` runs the `get` hook, which uses the current case’s `->value` in the message. PHP calls this a virtual property because it has no storage of its own. This virtual property has only a `get` hook, so the caller writes to `status` and reads the new label. [Example source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/Order.php) · [Backed enums](https://www.php.net/manual/en/language.enumerations.backed.php) · [Property hooks](https://www.php.net/manual/en/language.oop5.property-hooks.php)

To compare a saved label with the live property, copy `Order.php` and `modern-php.php` into a scratch directory. In the copy of `modern-php.php`, add `$savedLabel = $order->label;` just before the status change. Replace the second `echo` with `echo $savedLabel, PHP_EOL, $order->label, PHP_EOL;` and run `php modern-php.php` there. The second line shows the saved string still saying pending. The third shows the property saying paid.

The enum defines the states an order can hold. The public `status` property allows assignment of any case, including changing a paid order back to pending. If your application forbids a transition, give the class control of writes and make it check the old and new states before updating the property.

### Convert an external string at the boundary

A CLI argument enters the program as text. If you assign `'paid'` directly to the typed `Order::$status` property, PHP raises `TypeError`. Use the enum’s conversion method before constructing the order.

The boundary script loads `Order.php`, checks the input and constructs the order in the selected initial state.

```php
require __DIR__ . '/Order.php';

$status = OrderStatus::tryFrom($argv[1] ?? '');

if ($status === null) {
    fwrite(STDERR, 'Unknown order status.' . PHP_EOL);
    exit(1);
}

echo (new Order(12, $status))->label, PHP_EOL;
```

```bash
php code/php-misconceptions/order-status-input.php paid
php code/php-misconceptions/order-status-input.php shipped
```

The first command prints `Order #12 is paid.` and exits successfully. The second writes `Unknown order status.` to standard error and exits with status 1. `OrderStatus::tryFrom()` returns the case for a recognised backing value and `null` for an unknown string. `OrderStatus::from()` throws `ValueError` for an unknown string. Use `tryFrom()` where you expect to handle invalid input. Use the case’s `->value` when you need a string to store or send. [Input example source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/order-status-input.php) · [Backed enum conversions](https://www.php.net/manual/en/language.enumerations.backed.php)

### Read the version requirements

The enum and the hook solve different problems. The enum limits which state the order can hold. The hook lets callers use property syntax for a value computed on each read. If your callers can use a method, you can keep the enum on a runtime that lacks hooks.

| PHP release | Example of what it added |
| --- | --- |
| 8.0 | Constructor property promotion. The promoted constructor parameters in the example also declare properties. [PHP 8.0](https://www.php.net/releases/8.0/en.php) |
| 8.1 | Enums and readonly properties. The example uses both for the order’s state and ID. [PHP 8.1](https://www.php.net/releases/8.1/en.php) |
| 8.4 | Property hooks. This is the first release that can parse the computed `$label` property. [PHP 8.4](https://www.php.net/releases/8.4/en.php) |
| 8.6 | Partial function application, which can turn a function call with some arguments supplied into a closure. The order scripts run without it. [PHP 8.6](https://www.php.net/releases/8.6/en.php) |

To see where the syntax requirement comes from, copy `Order.php`, `modern-php.php` and `order-status-input.php` into a scratch directory. If you have a PHP 8.3 CLI, run `php -l Order.php` there with that binary. The hook causes a parse error before any order is created. Replace the full `$label` property in the copy with this method.

```php
public function label(): string
{
    return "Order #{$this->id} is {$this->status->value}.";
}
```

Change both reads in `modern-php.php` to `$order->label()` and the read in `order-status-input.php` to `(new Order(12, $status))->label()`. Lint `Order.php` again, then run both scripts from the scratch directory with the PHP 8.3 binary. The labels and the rejection of `shipped` should match the original examples. The method version’s syntax goes back to PHP 8.1. Consult the support calendar separately when choosing a branch to run. [PHP 8.1 migration guide](https://www.php.net/manual/en/migration81.new-features.php) · [PHP command-line options](https://www.php.net/manual/en/features.commandline.options.php)

## Check the version you actually run

Before using the hook in the checkout app, find out which PHP process will load `Order.php`. Start with these commands for the command-line interface (CLI) in the environment you want to check.

```bash
php -r 'printf("PHP %s (%s)\n", PHP_VERSION, PHP_SAPI);'
php --ini
```

The first command reports the CLI version and its interface, such as `PHP 8.5.0 (cli)`. The second lists which configuration files that binary reads. A queue worker, web server or deployed container can use a different executable or config. Use existing private diagnostics in the serving environment, or record `PHP_VERSION` and `PHP_SAPI` from a controlled staging request. [PHP predefined constants](https://www.php.net/manual/en/reserved.constants.php) · [PHP command-line options](https://www.php.net/manual/en/features.commandline.options.php)

To see the interface change locally, save a tiny script in a temporary directory and serve it on loopback. The commands use a POSIX-style shell such as Bash or Zsh, plus `mktemp` and `curl`.

```bash
probe="$(mktemp -d)"
cat > "$probe/index.php" <<'PHP'
<?php
printf("PHP %s (%s)\n", PHP_VERSION, PHP_SAPI);
printf("Loaded ini: %s\n", php_ini_loaded_file() ?: '(none)');
printf("PDO SQLite: %s\n", extension_loaded('pdo_sqlite') ? 'available' : 'unavailable');
PHP
php "$probe/index.php"
php -S 127.0.0.1:8026 -t "$probe"
```

From a second terminal run `curl -fsS http://127.0.0.1:8026/`. The version should match the CLI you used to start the server, and the interface should say `cli-server`. Compare the loaded configuration file and extension status in both outputs. Use Ctrl+C to stop the server. If 8026 is busy, choose a free port for both commands. When auditing a deployed app, take the measurement from inside its serving process. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php) · [PHP configuration file API](https://www.php.net/manual/en/function.php-ini-loaded-file.php)

### Read the support calendar

For PHP `8.4.28`, the branch is `8.4` and the patch release is `28`. Find the branch in the official support table, then use the downloads page or your package provider to check for a later patch.

| Support phase | What the PHP project provides | How to use it |
| --- | --- | --- |
| Active support | Regular bug and security fixes. | Keep the branch patched and check compatibility before moving to the next branch. |
| Security fixes only | Releases for critical security issues as needed. | Keep the branch patched while planning the move to a newer supported branch. |
| End of life | Upstream PHP support has ended. | Plan a move to a supported branch and check any separate support offered by your package provider. |

[PHP support schedule](https://www.php.net/supported-versions.php) · [Unsupported PHP branches](https://www.php.net/eol.php) · [PHP downloads](https://www.php.net/downloads.php)

### Check whether the project can move

Run the following from the root of the application you want to upgrade, where its `composer.json` and `composer.lock` live. Inspect the locked dependencies before changing the runtime.

```bash
composer prohibits php 8.6.0 --locked
composer check-platform-reqs --lock
```

The first command looks for requirements that block the specified PHP version. Substitute the version you intend to deploy. Once you can run Composer under the target PHP, use the second command to check the real PHP version and loaded extensions against the lock. It checks the actual platform even when `config.platform` is set. Then read the relevant PHP migration guides and run the application’s tests and a smoke check under the target serving runtime. [Composer command reference](https://getcomposer.org/doc/03-cli.md) · [PHP 8.6 migration guide](https://www.php.net/manual/en/migration86.php) · [Legacy PHP upgrade walkthrough](/series/php-misconceptions/chapters/13-upgrading-legacy-php/)

Try a version conflict from the recorded Laravel dependencies used in [Chapter 03](/series/php-misconceptions/chapters/03-laravel-performance/). From the checkout root, use the same shell as the loopback example. This copies the manifest and lock file into a temporary directory. Composer reads the lock without installing the app.

```bash
fixture="$PWD/code/php-misconceptions/laravel/results/dependencies"
lock_demo="$(mktemp -d)"
cp "$fixture/composer.json" "$lock_demo/composer.json"
cp "$fixture/composer.lock.json" "$lock_demo/composer.lock"
composer --working-dir="$lock_demo" prohibits php 8.2.0 --locked
```

Composer reports the blockers and exits with status 1. Look for this line among the results.

```text
laravel/framework v13.43.0 requires php (^8.3)
```

That package’s constraint excludes PHP 8.2.0. Other entries can impose a higher minimum, so read the full list when choosing your candidate. [Recorded Composer dependencies](https://github.com/dalehurley/codewithphp/tree/main/code/php-misconceptions/laravel/results/dependencies) · [Laravel 13.43.0 package requirements](https://github.com/laravel/framework/blob/v13.43.0/composer.json)

If you built the [Chapter 04 Laravel quote demo](/series/php-misconceptions/chapters/04-clean-laravel-code/), you can also run `composer prohibits php 8.2.0 --locked` from its temporary `php-quotes` app directory. The output reflects the versions in that app’s lock file.

## Exercise

### Add a cancellation state

Add `case Cancelled = 'cancelled';` to `OrderStatus` in `Order.php`. In `modern-php.php`, set the status to the new case after the paid example and print the label again. Expect `Order #12 is cancelled.` on the third line.

Now run `php code/php-misconceptions/order-status-input.php cancelled`. It should print the same label. Try `shipped` again and confirm it still fails. Both scripts use the same enum.

### Enforce a transition rule

Suppose an order can be paid or cancelled only while pending. In a fresh scratch directory, copy `Order.php` with your new enum case. Change `public OrderStatus $status` in the constructor to `public private(set) OrderStatus $status`. Code outside the class can still read the status. The class controls writes through methods like these. [PHP property visibility](https://www.php.net/manual/en/language.oop5.visibility.php)

```php
public function pay(): void
{
    if ($this->status !== OrderStatus::Pending) {
        throw new LogicException('Only pending orders can be paid.');
    }

    $this->status = OrderStatus::Paid;
}

public function cancel(): void
{
    if ($this->status !== OrderStatus::Pending) {
        throw new LogicException('Only pending orders can be cancelled.');
    }

    $this->status = OrderStatus::Cancelled;
}
```

Copy the original pending-to-paid version of `modern-php.php` into the same scratch directory, then replace `$order->status = OrderStatus::Paid;` with `$order->pay();`. Run `php modern-php.php` there. It should still print the pending and paid labels from Quick Start. If you copied the script from the cancellation-state exercise, remove the last assignment and label read first.

The constructor still accepts any `OrderStatus` case as an initial state. The methods govern changes to an existing order. If every new order must start pending, that would be a separate rule for construction.

From the scratch directory, try the operation on a pending order and on a separate paid order.

```bash
php -r 'require "Order.php"; $order = new Order(12, OrderStatus::Pending); $order->cancel(); echo $order->label, PHP_EOL;'
php -r '
require "Order.php";
$order = new Order(12, OrderStatus::Paid);
try {
    $order->cancel();
} catch (LogicException $exception) {
    echo $exception->getMessage(), PHP_EOL;
}
echo $order->label, PHP_EOL;
'
```

The pending order prints `Order #12 is cancelled.` The second command prints `Only pending orders can be cancelled.` and then `Order #12 is paid.` The rejection leaves the order’s state unchanged.

### Choose an upgrade action

On 20 November 2026, you want to use the computed order label in a checkout app. Your laptop CLI runs PHP 8.6.0 and the app serves HTTP requests with PHP 8.2.35. The host offers PHP 8.2, 8.4 and 8.6. A locked package requires PHP `>=8.2 <8.6`, and the other dependencies permit PHP 8.4. Can you use the hook today? Which branch would you trial, and what changes if you need regular upstream bug fixes beyond December 2026?

<details>
<summary>Check your reasoning</summary>

Keep the method on PHP 8.2 because the HTTP process cannot parse the hook. The 8.2 branch is in upstream security-only support until 31 December 2026. The upstream changelog lists PHP 8.2.36, released on 19 November, so check whether your package provider offers that patch or equivalent fixes while you prepare the branch upgrade.

Trial the latest available PHP 8.4 patch. It supports the hook and fits the locked constraint. Its active support ends on 31 December 2026, with security support through 31 December 2028. If you need regular upstream bug fixes beyond 2026, check whether your host can offer PHP 8.5, then verify all dependencies against that candidate. Of the branches already offered, PHP 8.6 meets that support requirement, and the blocking package needs a compatible release or replacement first. [PHP support schedule](https://www.php.net/supported-versions.php) · [PHP 8 changelog](https://www.php.net/ChangeLog-8.php)

For your candidate, use `composer prohibits`, run `composer check-platform-reqs --lock` under the target PHP, review the migration guides and run the test suite. Check the version, configuration and required extensions from a staging HTTP request. Repeat the pending-to-paid order flow there before deploying the hook.

</details>

## Next chapter

[Do Dollar Signs Make PHP Expensive?](/series/php-misconceptions/chapters/06-dollar-signs-and-cost/)
