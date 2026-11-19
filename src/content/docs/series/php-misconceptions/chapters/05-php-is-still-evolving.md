---
title: "05: Is PHP Old and Dead?"
description: "Read the PHP release and support schedule and try an enum with a computed property."
series: "php-misconceptions"
chapter: 5
estimatedTime: "PT10M"
sidebar:
  order: 5
---

## Overview

PHP dates to 1995. PHP 8.6 shipped on 19 November 2026. Those two dates can be true at the same time. The first tells you how long the language has existed. The second tells you development continues. [PHP history](https://www.php.net/manual/en/history.php) · [PHP 8.6 release announcement](https://www.php.net/releases/8.6/en.php)

## Quick Start

From the Code with PHP checkout, run the example with PHP 8.4 or later.

```bash
php --version
php code/php-misconceptions/modern-php.php
```

```text
Order #12 is pending.
Order #12 is paid.
```

The script uses an enum to constrain the order to known states. A property hook computes its display label from its current state. Enums arrived in PHP 8.1 and property hooks in PHP 8.4. Constructor property promotion, union types and attributes arrived earlier in the 8.x series. [PHP 8.0 release](https://www.php.net/releases/8.0/en.php) · [PHP 8.1 release](https://www.php.net/releases/8.1/en.php) · [PHP 8.4 release](https://www.php.net/releases/8.4/en.php)

## Check the version you actually run

PHP publishes dates for active support and security support for each branch. Find your minor version in that table. Then compare your patch version with the latest release in your supported branch. A Laravel dependency, hosting platform or extension can affect when you move to a newer branch. The PHP CLI and the PHP runtime serving HTTP can also come from different installs. [PHP support schedule](https://www.php.net/supported-versions.php) · [PHP downloads](https://www.php.net/downloads.php)

## Exercise

Add an enum case for a cancelled order and print the label after setting that state. Then check the support dates for the PHP binary you ran. The enum example demonstrates an available feature, while the support check tells you how long your branch will get fixes.

## Next chapter

[Do Dollar Signs Make PHP Expensive?](/series/php-misconceptions/chapters/06-dollar-signs-and-cost/)
