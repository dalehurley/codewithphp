---
title: "01: Can PHP Be Typed?"
description: "Use PHP type declarations, test strict and coercive calls, and see where static analysis adds guarantees."
series: "php-misconceptions"
chapter: 1
estimatedTime: "PT10M"
sidebar:
  order: 1
---

## Overview

PHP is dynamically typed. It checks declared parameter, return and property types at runtime. You choose where to put those declarations, so a form that starts with raw strings can pass validated data into an application built around typed objects. Types can describe interfaces, unions such as `int|string` and intersections such as `Countable&Iterator`. [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php)

## Prerequisites

Use PHP 8.6 or later and open a terminal at the root of the Code with PHP checkout. Confirm your CLI with `php --version`. Allow 10 minutes.

## Quick Start

The example builds a [typed unit price](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/UnitPrice.php) and passes it two quantities.

```bash
php code/php-misconceptions/types.php
```

```text
3750
The strict caller rejected the numeric string.
```

The class declares an `int` input and return value. Its constructor also checks that the price is non-negative. A type can tell you a value is an integer. The constructor supplies the rule about which integers make sense for a price.

## Predict the result

What happens when you pass the string `"3"` to `forQuantity(int $quantity)`? This 20-second screen recording runs the same call in both modes.

<video controls preload="none" poster="/videos/php-strict-types.webp" width="960" height="540" style="max-width:100%;height:auto;" aria-label="PHP strict types example, transcript follows">
  <source src="/videos/php-strict-types.mp4" type="video/mp4">
  <a href="/videos/php-strict-types.mp4">Download the recording</a>
</video>

The default-mode call prints `3750` after converting the string to an integer. With strict types in the calling file, the same argument raises a `TypeError`.

## The file making the call matters

Try the same method from a caller that omits `declare(strict_types=1)`.

```bash
php -r 'require "code/php-misconceptions/UnitPrice.php"; echo (new UnitPrice(1250))->forQuantity("3"), PHP_EOL;'
# 3750
```

The class file declares `strict_types=1` in both cases. For a direct call to a user-defined function or method, scalar arguments follow the calling file and scalar return values follow the declaring file. The default mode can convert a numeric string to an integer. The strict caller in the first example rejects that string. Strict scalar checking still allows an `int` where a `float` is expected. [PHP strict typing rules](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)

## What a static analyser adds

A parameter declared as `array` accepts any PHP array. The example below passes `['3']` to a function annotated with `@param list<int> $quantities`. PHP accepts the array and prints its length, `1`.

```bash
php code/php-misconceptions/quantity-list.php
```

Once you have PHPStan in the [developer tooling demo app](/series/php-misconceptions/chapters/11-developer-tooling/), run this from that app. Replace the example path with the location of your checkout.

```bash
vendor/bin/phpstan analyse --level=5 --no-progress "/path/to/codewithphp/code/php-misconceptions/quantity-list.php"
```

PHPStan reports that the function expects `list<int>` and received an array containing the string `'3'`. Change the argument to `[3]` and rerun both commands. PHP still prints `1` and the analyser passes. PHPDoc generics can also express relationships between types. PHP 8.6 has no native generic declarations. [PHPStan PHPDoc types](https://phpstan.org/writing-php-code/phpdoc-types) · [PHP generics RFC status](https://wiki.php.net/rfc/bound_erased_generic_types)

## Exercise

Change the strict caller’s input to an integer `3`. Print the second call’s return value and check that it also prints `3750`. In a real form handler, validate the incoming value before converting it. A cast alone can turn an invalid value into an integer.

## Next chapter

[Is PHP Always Slow?](/series/php-misconceptions/chapters/02-php-performance/)
