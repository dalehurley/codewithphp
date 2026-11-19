---
title: "06: Do Dollar Signs Make PHP Expensive?"
description: "Read PHP’s variable syntax, check its open-source license and work out which expenses belong in the cost of an app."
series: "php-misconceptions"
chapter: 6
estimatedTime: "PT5M"
sidebar:
  order: 6
---

## What the dollar sign buys you

A PHP variable name starts with a dollar sign. In the [typing example](/series/php-misconceptions/chapters/01-php-types/), `$price` holds a `UnitPrice` object. If you store a quantity in `$quantity`, then `$price->forQuantity($quantity)` passes it to the method. The parentheses mark the call. `$price->cents` reads the object's `cents` property. [PHP variable syntax](https://www.php.net/manual/en/language.variables.basics.php)

## What you actually pay for

You can run PHP without paying a runtime licence fee. PHP is open source, and Laravel’s framework is available under the MIT licence. You can use a free editor and self-host. Hosted platforms, premium tools and commercial packages can have their own prices and terms. [PHP licensing](https://www.php.net/license/) · [Laravel framework licence](https://github.com/laravel/framework/blob/13.x/LICENSE.md)

Estimate a monthly total for the application you have in mind. Add web and queue compute, data storage, backups, network traffic, monitoring and the staff time for deployment, patches and support. If a managed service and a self-managed server are both options, put the same traffic, retention and uptime expectations in each estimate.

## Exercise

Take a fictional quote for A\$40 per month in hosting, A\$20 in database costs and one hour at A\$80 in operating time. The monthly total is A\$140. Add a second background worker costing A\$15 per month and recalculate the total. The dollar signs in the source code are still free.

## Next chapter

[Is PHP Only for WordPress?](/series/php-misconceptions/chapters/07-beyond-wordpress/)
