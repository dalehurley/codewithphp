---
title: "05: Is PHP Old and Dead?"
description: "Model order states with a backed enum and a property hook, then decide how to support the PHP branch your app runs."
series: "php-misconceptions"
chapter: 5
estimatedTime: "PT20M"
sidebar:
  order: 5
---

## Overview

PHP was released publicly in 1995. PHP 8.6 shipped on 19 November 2026. Every supported branch has its own maintenance dates. To decide what you can use in your code, check when a feature first appeared and which branch runs in your environment. [PHP history](https://www.php.net/manual/en/history.php) · [PHP 8.6 announcement](https://www.php.net/releases/8.6/en.php) · [PHP 8 changelog](https://www.php.net/ChangeLog-8.php)

This chapter follows an order whose status can change. You’ll use a backed enum to limit the available states, a property hook to keep the display label current, and the release calendar to decide whether an application needs a patch or a branch upgrade.

## Quick Start

Run from the root of the [Code with PHP checkout](https://github.com/dalehurley/codewithphp). The series uses PHP 8.6 as its baseline. These order examples require PHP 8.4 or later for the property hook. [PHP 8.4 migration guide](https://www.php.net/manual/en/migration84.new-features.php)

```bash
php --version
php code/php-misconceptions/modern-php.php
```

```text
Order #12 is pending.
Order #12 is paid.
```

## Keep the label in sync with the order

Imagine that the constructor saved `"Order #12 is pending."` in a plain string property. Changing the status would leave that label stale until another part of the program updated it. The example keeps the order ID and status as the source data, and computes the label each time it is read.

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

Here `OrderStatus::Paid` is an enum case, and its backing value is the string `'paid'`. The order stores the enum case. When you read `$order->label`, the `get` hook reads the current case and uses its `->value` in the message. This is a virtual property because the hook computes its result from other properties. The getter also has no setter, so the caller changes the order’s state by writing to `status`. [Example source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/Order.php) · [Backed enums](https://www.php.net/manual/en/language.enumerations.backed.php) · [Property hooks](https://www.php.net/manual/en/language.oop5.property-hooks.php)

Try the difference between saving a label and reading it on demand. Add `$savedLabel = $order->label;` just before the status change in `modern-php.php`, then print `$savedLabel` alongside `$order->label` afterwards. The saved string still says pending, while the property now says paid.

The enum defines the states an order can hold. The public `status` property allows assignment of any case, including changing a paid order back to pending. If your application forbids a transition, give the class control of writes and make it check the old and new states before updating the property.

### Convert an external string at the boundary

A CLI argument enters the program as text. If you assign `'paid'` directly to the typed `Order::$status` property, PHP raises `TypeError`. Use the enum’s conversion method before constructing the order.

Once `Order.php` is loaded, the boundary script checks the input and constructs the order in the selected initial state.

```php
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

| PHP release | Example of what it added |
| --- | --- |
| 8.0 | Constructor property promotion, union types and attributes. The promoted constructor parameters in the example also declare properties. [PHP 8.0](https://www.php.net/releases/8.0/en.php) |
| 8.1 | Enums and readonly properties. The example uses both for the order’s state and ID. [PHP 8.1](https://www.php.net/releases/8.1/en.php) |
| 8.4 | Property hooks. This is the first release that can parse the computed `$label` property. [PHP 8.4](https://www.php.net/releases/8.4/en.php) |
| 8.6 | Partial function application, which can turn a function call with some arguments supplied into a closure. [PHP 8.6](https://www.php.net/releases/8.6/en.php) |

If you need the same behaviour on PHP 8.1–8.3, replace the hook with a method and use `$order->label()` at each read. That keeps the enum and computes the value from the current state. Make this change on a copy if you want to run the original exercise too.

```php
public function label(): string
{
    return "Order #{$this->id} is {$this->status->value}.";
}
```

## Check the version you actually run

Run these commands in the environment you want to check.

```bash
php -r 'printf("PHP %s (%s)\n", PHP_VERSION, PHP_SAPI);'
php --ini
```

The first command reports the CLI version and its interface, such as `PHP 8.5.0 (cli)`. The second lists which configuration files that binary reads. A queue worker, web server or deployed container can use a different executable or config. Use existing private diagnostics in the serving environment, or record `PHP_VERSION` and `PHP_SAPI` from a controlled staging request. [PHP predefined constants](https://www.php.net/manual/en/reserved.constants.php) · [PHP command-line options](https://www.php.net/manual/en/features.commandline.options.php)

To see the interface change locally, save a tiny script in a temporary directory and serve it on loopback.

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

In an application with a Composer lock file, inspect the locked dependencies before changing the runtime.

```bash
composer prohibits php 8.6.0 --locked
composer check-platform-reqs --lock
```

The first command looks for requirements that block the specified PHP version. Substitute the version you intend to deploy. Once you can run Composer under the target PHP, use the second command to check the real PHP version and loaded extensions against the lock. It checks the actual platform even when `config.platform` is set. Then read the relevant PHP migration guides and run the application’s tests and a smoke check under the target serving runtime. [Composer command reference](https://getcomposer.org/doc/03-cli.md) · [PHP 8.6 migration guide](https://www.php.net/manual/en/migration86.php) · [Legacy PHP upgrade walkthrough](/series/php-misconceptions/chapters/13-upgrading-legacy-php/)

Try `composer prohibits php 8.2.0 --locked` in the [Laravel quote demo](/series/php-misconceptions/chapters/04-clean-laravel-code/). With Laravel 13.43.0, one line names `laravel/framework` and shows its PHP constraint, `^8.3`. That constraint excludes PHP 8.2.0 even if PHP itself can still run other applications on that branch. [Laravel 13.43.0 package requirements](https://github.com/laravel/framework/blob/v13.43.0/composer.json)

## Exercise

### Add a cancellation state

Add `case Cancelled = 'cancelled';` to `OrderStatus` in `Order.php`. In `modern-php.php`, set the status to the new case after the paid example and print the label again. Expect `Order #12 is cancelled.` on the third line.

Now run `php code/php-misconceptions/order-status-input.php cancelled`. It should print the same label. Try `shipped` again and confirm it still fails. Both scripts use the same enum.

### Enforce a transition rule

Suppose only pending orders can be cancelled. Work on a scratch copy of `Order.php` with your new case. Change `public OrderStatus $status` in the constructor to `public private(set) OrderStatus $status`. Code outside the class can still read the status. The class controls writes. Add this method inside `Order`. [PHP property visibility](https://www.php.net/manual/en/language.oop5.visibility.php)

```php
public function cancel(): void
{
    if ($this->status !== OrderStatus::Pending) {
        throw new LogicException('Only pending orders can be cancelled.');
    }

    $this->status = OrderStatus::Cancelled;
}
```

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

On 20 November 2026, your laptop CLI runs PHP 8.6.0 and your HTTP app serves requests with PHP 8.2.35. The host offers PHP 8.2, 8.4 and 8.6. A locked package requires PHP `>=8.2 <8.6`, and the other dependencies permit PHP 8.4. Which runtime determines production support, which branch would you trial and what would you check?

<details>
<summary>Check your reasoning</summary>

Assess the HTTP app’s PHP 8.2 branch. It is in security-only support until 31 December 2026. The upstream changelog lists PHP 8.2.36, released on 19 November, so first check whether your package provider offers that patch or equivalent fixes.

Trial the latest available PHP 8.4 patch. The locked constraint allows 8.4 and the branch still receives active support on the scenario date. Use `composer prohibits` for the candidate version, run `composer check-platform-reqs --lock` under the target PHP, review the migration guides and run the test suite. Check the version, configuration and required extensions from a staging HTTP request before switching production. The package would need a compatible release or replacement before you could move this app to 8.6. [PHP support schedule](https://www.php.net/supported-versions.php) · [PHP 8 changelog](https://www.php.net/ChangeLog-8.php)

</details>

## Next chapter

[Do Dollar Signs Make PHP Expensive?](/series/php-misconceptions/chapters/06-dollar-signs-and-cost/)
