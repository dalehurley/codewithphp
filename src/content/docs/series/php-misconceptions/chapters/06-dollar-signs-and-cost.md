---
title: "06: Do Dollar Signs Make PHP Expensive?"
description: "Read PHP variables and currency strings, check software licences, and compare two fictional operating budgets."
series: "php-misconceptions"
chapter: 6
estimatedTime: "PT15M"
sidebar:
  order: 6
---

## What the dollar sign buys you

A dollar sign before a variable name is part of PHP’s syntax. In the [typing example](/series/php-misconceptions/chapters/01-php-types/), `$price` holds a `UnitPrice` object and `$quantity` holds a quantity. The expression `$price->forQuantity($quantity)` calls a method on the object and supplies the variable’s value. In `$price->cents`, the arrow accesses the property named `cents`. [PHP variable syntax](https://www.php.net/manual/en/language.variables.basics.php)

### Predict what prints

Read the four `echo` calls. Which dollar signs will appear in their output? Save the snippet as `dollar-signs.php` in a scratch directory and run `php dollar-signs.php`.

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

Single quotes keep `$quantity` literal. Double quotes let PHP substitute the variable’s value unless you escape the dollar sign with `\$`. In `A$40`, the `4` cannot start a variable name, so the escape is optional. You’ll see it used for the currency prefix in the estimator. [PHP strings and interpolation](https://www.php.net/manual/en/language.types.string.php)

## What you actually pay for

You can run PHP and Laravel without paying a fee for their runtime or framework licences. PHP 8.6 uses the PHP License version 4, equivalent to BSD-3-Clause. The Laravel framework uses the MIT licence. If you redistribute software, read the applicable licence and keep the notices required by its terms. Hosted services, paid support and commercial dependencies have terms of their own. [PHP licensing](https://www.php.net/license/) · [PHP licence text](https://www.php.net/license/4_0.txt) · [Laravel framework licence](https://github.com/laravel/framework/blob/13.x/LICENSE.md)

### Check the dependencies you ship

Run this in a Composer application that already has its dependencies installed. Use the earlier [Laravel quote demo](/series/php-misconceptions/chapters/04-clean-laravel-code/) if you want a concrete example.

```bash
composer licenses --no-dev
composer show --path laravel/framework
```

The first command lists installed production dependencies and their declared licences. In the quote demo, find the `laravel/framework` row, which shows `MIT`. The second command locates that package’s directory. Open `LICENSE.md` in the directory to read the actual terms. Review separate service or subscription terms alongside the software inventory. [Composer license inventory command](https://github.com/composer/composer/blob/2.8.12/src/Composer/Command/LicensesCommand.php)

## Build a comparable estimate

Fix the workload and what the application needs first. For this exercise, suppose both plans can serve the same app at 100 GB of outbound traffic per month and meet the same availability needs. Both provide daily backups with seven days of retention and 30 days of logs. The fictional amounts below are in Australian dollars.

| Monthly item | Managed plan (AUD) | Self-managed plan (AUD) |
| --- | ---: | ---: |
| Web hosting | 40 | 30 |
| Database | 20 | 15 |
| Backups, logs and monitoring | Included | 10 |
| Outbound traffic at 100 GB | Included | Included |
| Operating time at 80 per hour | 80 for 1 hour | 160 for 2 hours |
| **Monthly total at this workload** | **140** | **215** |

Separate fixed charges from usage charges and operating time. In this example, the managed plan includes 100 GB and charges A\$0.10 per extra GB. The self-managed plan includes 125 GB and charges A\$0.20 per extra GB. The time estimate covers routine work such as checking alerts, applying updates and checking that backups can be restored. Use the cost of that time in the comparison even when it doesn’t appear on a hosting invoice.

### Run the estimate in PHP

From the [Code with PHP checkout](https://github.com/dalehurley/codewithphp), run the estimator using PHP 8.6, the series baseline. It uses standard PHP and needs no dependencies.

```bash
php code/php-misconceptions/cost-estimate.php
```

```text
Managed: A$140.00 per month
Self-managed: A$215.00 per month
```

Enter fixed charges and rates in integer cents. For example, `8000` represents A\$80. Each plan calculates extra traffic by subtracting its own allowance and applying `max(0, ...)`, so usage below either allowance never creates a credit. The estimate adds fixed charges, usage and hours times the hourly rate. It uses whole hours for the runnable scenarios and prints the totals in dollars. [Estimator source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/cost-estimate.php) · [PHP string formatting](https://www.php.net/manual/en/function.sprintf.php)

### Compare the same period

Keep setup work separate from a recurring monthly bill. If moving to the self-managed plan costs a fictional A\$240 once, its first-year cost at the baseline workload is twelve months at A\$215 plus A\$240, or A\$2,820. That averages A\$235 per month over the first year. The managed plan totals A\$1,680 over the same period if it has no setup charge. Check what each plan actually includes before applying the same method to a real quote.

## Exercise

1. At 100 GB, add `1500` cents to `$managedFixedCents` for a background worker. Assume it leaves traffic and operating time unchanged. What does the script print for the new managed total?
2. Restore the original fixed charges, set `$trafficGb` to `250`, then compare the two totals. How much does each plan charge for the extra traffic? Which has the smaller recurring bill at 250 GB if both plans take one hour to operate? Restore `$selfManagedHours` to `2` before the next question.
3. Restore `$trafficGb` to `100`. At what monthly operating time does the self-managed plan match the managed plan’s recurring cost? Which assumption would you check before relying on this estimate?
4. Set `$selfManagedHours` to `1`. Which plan costs less each month? Which costs less in the first year once you include the self-managed plan’s A\$240 setup cost?

<details>
<summary>Check your calculations</summary>

1. The extra worker raises the managed total from A\$140 to A\$155 per month.
2. The managed plan charges A\$15 for 150 GB of overage. The self-managed plan charges A\$25 for 125 GB of overage. With the original hours, the totals are A\$155 and A\$240. If both plans take one hour to operate, the self-managed total becomes A\$160 and the managed plan has the smaller bill at 250 GB.
3. The self-managed plan has A\$55 in fixed costs at the original workload. It can spend A\$85 on operating time before reaching A\$140. At A\$80 per hour, that is 1.0625 hours, or 1 hour, 3 minutes and 45 seconds. At one hour its total is A\$135. At two hours it is A\$215. The monthly choice is sensitive to how long patches, monitoring and restore checks take. Estimate that work against your team’s own experience before putting a number in the budget.
4. At one hour, self-management costs A\$135 per month and the managed plan costs A\$140. Twelve months of self-management plus setup costs A\$1,860. The managed plan costs A\$1,680 over the same year. The smaller recurring bill saves A\$60 that year, while setup costs A\$240.

</details>

## Next chapter

[Is PHP Only for WordPress?](/series/php-misconceptions/chapters/07-beyond-wordpress/)
