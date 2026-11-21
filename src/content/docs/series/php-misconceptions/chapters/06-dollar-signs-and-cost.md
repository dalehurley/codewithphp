---
title: "06: Do Dollar Signs Make PHP Expensive?"
description: "Read PHP variables and currency strings, check software licences, and compare two fictional operating budgets."
series: "php-misconceptions"
chapter: 6
estimatedTime: "PT15M"
sidebar:
  order: 6
---

You’re preparing to host a small Laravel app. A self-managed plan starts with lower provider charges than a managed plan. Which costs less once you count the time your team spends on updates, alerts and backups? You’ll answer that with a PHP estimator that uses a dollar sign both to name a variable and to print a price.

## What the dollar sign buys you

In `$quantity = 3`, the dollar sign marks `$quantity` as a PHP variable and the assignment gives it the value `3`. Inside a string, the quotes and the characters after the dollar sign determine whether PHP inserts a value or prints the sign itself. [PHP variable syntax](https://www.php.net/manual/en/language.variables.basics.php)

### Predict what prints

The series uses PHP 8.6. Run `php --version` in a terminal to check your PHP CLI. Both standalone examples in this chapter also run on PHP 8.5. If you need to install PHP, start with the [PHP setup lesson](/series/php-basics/chapters/00-setting-up-your-development-environment/).

Read the four `echo` calls and predict which dollar signs will appear. Save the snippet as `dollar-signs.php` in a scratch directory. In your terminal, change to that directory and run `php dollar-signs.php`.

```php
<?php

$quantity = 3;

echo '$quantity', PHP_EOL;
echo "Quantity: $quantity", PHP_EOL;
echo "Variable name: \$quantity", PHP_EOL;
echo "Hosting: A\$40", PHP_EOL;
```

```text
$quantity
Quantity: 3
Variable name: $quantity
Hosting: A$40
```

Single quotes keep `$quantity` literal. In double quotes, PHP substitutes its value, a process called interpolation. Escaping the sign with `\$` prints it literally. The escape is optional in `A$40` because a digit cannot start a variable name. The estimator uses the escape to make the literal currency sign explicit. [PHP strings and interpolation](https://www.php.net/manual/en/language.types.string.php)

Variables also hold objects. In the Chapter 1 batching example, `$price` holds a `UnitPrice` object. The expression `$price->forQuantity($quantity)` passes the quantity as an argument and calls the object’s method. The arrow can also read a property, as in `$price->cents`. [Chapter 1 pricing example](/series/php-misconceptions/chapters/01-php-types/#what-a-static-analyser-adds)

## What you actually pay for

Before pricing either hosting plan, check which software and services carry a charge. The PHP runtime and Laravel framework have no licence fee. PHP 8.6 uses the PHP License version 4, which is effectively equivalent to BSD-3-Clause. The Laravel framework uses the MIT licence. If you redistribute software, read the applicable terms and keep the required notices. Hosted services, paid support and commercial dependencies have terms of their own. [PHP licensing](https://www.php.net/license/) · [PHP licence text](https://www.php.net/license/4_0.txt) · [Laravel framework licence](https://github.com/laravel/framework/blob/13.x/LICENSE.md)

### Check the dependencies you ship

To follow along with the Laravel example, use the app created in the [Chapter 4 quote demo setup](/series/php-misconceptions/chapters/04-clean-laravel-code/#quick-start). Run both commands in the temporary `php-quotes` directory printed by `pwd` during setup. It contains the app’s `composer.json` and installed `vendor/` directory.

```bash
composer licenses --no-dev
composer show --path laravel/framework
```

The first command lists installed production dependencies and their declared licences. Look for a row starting with `laravel/framework` with `MIT` in the licence column. The second command prints the installed package’s absolute path, which ends in `php-quotes/vendor/laravel/framework` in the demo. Open `LICENSE.md` in that directory to read the actual terms. Keep any paid licences, service fees or subscriptions your app needs in the budget. The fictional comparison below assumes no additional charges of those kinds. [Composer licence inventory](https://getcomposer.org/doc/03-cli.md#licenses) · [Composer inventory source](https://github.com/composer/composer/blob/2.8.12/src/Composer/Command/LicensesCommand.php)

## Build a comparable estimate

Both plans run the same app at 100 GB of outbound traffic per month and meet the same availability needs. Both provide monitoring, daily backups with seven days of retention, and 30 days of logs. With the self-managed plan, your team spends more time applying updates, checking alerts and testing restores. Assume that time costs the team A\$80 per hour.

<table>
  <caption>Fictional monthly costs at 100 GB of outbound traffic (AUD)</caption>
  <thead>
    <tr><th scope="col">Item</th><th scope="col">Managed</th><th scope="col">Self-managed</th></tr>
  </thead>
  <tbody>
    <tr><th scope="row">Web hosting</th><td>40</td><td>30</td></tr>
    <tr><th scope="row">Database</th><td>20</td><td>15</td></tr>
    <tr><th scope="row">Backups, logs and monitoring</th><td>Included</td><td>10</td></tr>
    <tr><th scope="row">Outbound traffic</th><td>Included</td><td>Included</td></tr>
    <tr><th scope="row">Provider charges</th><td>60</td><td>55</td></tr>
    <tr><th scope="row">Staff time</th><td>80 for 1 hour</td><td>160 for 2 hours</td></tr>
    <tr><th scope="row">Total operating cost</th><td><strong>140</strong></td><td><strong>215</strong></td></tr>
  </tbody>
</table>

At 100 GB, self-management saves A\$5 in provider charges. Its extra hour of staff time costs A\$80, taking the total to A\$215, or A\$75 more than the managed plan. That makes staff time worth checking before you choose a plan.

The managed plan includes 100 GB and charges A\$0.10 per extra GB. The self-managed plan includes 125 GB and charges A\$0.20 per extra GB. Both plans can serve the same app at 250 GB in this exercise, with the same availability and retention and no capacity upgrades. Only the traffic charges change unless you also change another input.

### Run the estimate in PHP

If you need a [Code with PHP checkout](https://github.com/dalehurley/codewithphp), clone it and change to its root. You can also [download the standalone estimator](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/php-misconceptions/cost-estimate.php).

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp
```

From the checkout root, run the command below. If you downloaded only the file, run `php cost-estimate.php` from the directory where you saved it. The script needs only the PHP CLI.

```bash
php code/php-misconceptions/cost-estimate.php
```

```text
Managed: A$140.00 per month
Self-managed: A$215.00 per month
```

The script stores dollar amounts and rates in integer cents. It subtracts each plan’s included gigabytes from monthly traffic, uses `max(0, ...)` to set negative results to zero, then multiplies by the per-GB rate. At 100 GB, the managed plan adds `6000` cents in fixed charges, `0` for extra traffic and `8000` for one hour of staff time. That puts `14000` in `$managedTotalCents`. [Estimator source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/cost-estimate.php)

The estimator’s print line uses both kinds of dollar sign.

```php
printf("Managed: A\$%.2F per month\n", $managedTotalCents / 100);
```

`$managedTotalCents` supplies the amount. Dividing by `100` converts cents to dollars for display. `%.2F` formats the result as `140.00`, with two decimal places and a decimal point. The `\$` in the format string prints the literal dollar sign in `A$140.00`. [PHP `printf` formatting](https://www.php.net/manual/en/function.printf.php)

### Compare the same period

Carry both monthly operating costs over the same twelve months. With the baseline inputs and a fictional setup cost of A\$240, the self-managed plan costs twelve times A\$215 plus A\$240 in the first year. That’s A\$2,820, or A\$235 a month on average over that year. The managed plan costs A\$1,680 in the same period with no setup charge.

## Exercise

Start with the baseline script. Save your changes and rerun the same command after each edit. The script prints monthly totals. Work out the separate traffic charges, the fractional break-even time and the first-year totals by hand.

1. At 100 GB, set `$managedFixedCents` to `4000 + 2000 + 1500`. Both plans need the same background worker, and the self-managed plan can run it within its existing charges. Assume operating time and traffic stay the same. What does the script print for the managed plan?
2. Restore `$managedFixedCents` to `4000 + 2000`, set `$trafficGb` to `250`, then run the estimator. Calculate each plan’s extra traffic charge using its own allowance and rate. Set `$selfManagedHours` to `1` and rerun. Which plan has the lower monthly operating cost at 250 GB when both take one hour to operate?
3. Restore `$trafficGb` to `100` and `$selfManagedHours` to `2`. At what monthly operating time does the self-managed plan tie with the managed plan? Subtract the self-managed plan’s provider charges from the managed total and divide what’s left by the hourly rate. Which work would you time or estimate before using that threshold to make a decision?
4. Set `$selfManagedHours` to `1` and rerun. Which plan costs less each month? Multiply each printed total by 12 and add the plan’s setup charge. Which costs less in the first year?

<details>
<summary>Check your calculations</summary>

1. The extra worker raises the managed total from A\$140 to A\$155 per month.
2. The managed overage is (250 − 100) GB × A\$0.10 per GB = A\$15. The self-managed overage is (250 − 125) GB × A\$0.20 per GB = A\$25. With the original hours, the totals are A\$155 and A\$240, respectively. At one hour each, the managed plan stays at A\$155 and the self-managed plan costs A\$160.
3. At 100 GB, the self-managed provider charges are A\$55, leaving A\$85 for staff time within the A\$140 managed total. At A\$80 per hour, the break-even point is 1.0625 hours, or 1 hour, 3 minutes and 45 seconds. Estimate the time your team would spend applying patches, checking alerts and testing restores before relying on the comparison.
4. At one hour each, self-management costs A\$135 per month and managed hosting costs A\$140. The first-year cost for self-management is (12 × A\$135) + A\$240 = A\$1,860. For managed hosting it’s (12 × A\$140) + A\$0 = A\$1,680. The managed plan costs A\$180 less in the first year.

</details>

## Next chapter

[Is PHP Only for WordPress?](/series/php-misconceptions/chapters/07-beyond-wordpress/)
