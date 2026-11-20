---
title: "01: Can PHP Be Typed?"
description: "Compare strict and coercive PHP calls, check a list with PHPStan, and validate form input before passing it into typed code."
series: "php-misconceptions"
chapter: 1
estimatedTime: "PT20M"
sidebar:
  order: 1
---

## Overview

PHP is dynamically typed and lets you declare types on parameters, return values and properties. PHP enforces those declarations at runtime. Even in a strict file, `$value = 3;` and then `$value = '3';` are both valid assignments. PHP checks the value when, for example, you pass it to a parameter declared as `int`. [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php)

A form handler illustrates where three kinds of checks belong.

| Check | Where to use it | Example |
| --- | --- | --- |
| Input validation | Where raw data enters your application | Parse the string `'3'` and check that the number is within the allowed range. |
| Native type declarations | On the parameters, returns and properties your code relies on | Make the pricing method take a quantity declared as `int`. |
| Static analysis | In your editor or build checks, before running the application | Have PHPStan check that code passes a list of integers to a batch pricing function. |

## Prerequisites

The series uses PHP 8.6. The standalone examples in this chapter also run on PHP 8.2 or later, which supports the `readonly` class used below. The PHPStan walkthrough uses `curl`. [PHP 8.2 migration guide](https://www.php.net/manual/en/migration82.new-features.php#migration82.new-features.core.readonly-classes)

If you already have the checkout, open a terminal at its root and run `php --version`. For a new checkout, use these commands.

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp
php --version
```

## Predict the result

A unit costs 1,250 cents. Two PHP files each call `forQuantity(int $quantity)` with the integer `3` and then with the string `"3"`. One file starts with `declare(strict_types=1)`. What will each file do with the string?

## Quick Start

Both files load the same [`UnitPrice` class](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/UnitPrice.php).

```php
<?php

declare(strict_types=1);

final readonly class UnitPrice
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Unit price must be non-negative.');
        }
    }

    public function forQuantity(int $quantity): int
    {
        return $this->cents * $quantity;
    }
}
```

In the constructor, `public int $cents` declares both a parameter and a property. In `forQuantity(int $quantity): int`, the first `int` describes the argument and the second describes the return value. All prices and totals are in cents.

Run these files from the checkout root.

```bash
php code/php-misconceptions/types.php
php code/php-misconceptions/types-coercive.php
```

<details>
<summary>Reveal the results and watch the animation</summary>

The strict file prints `3750` for the integer, then prints `The quantity call raised a TypeError.` after catching the error for the string. The coercive file prints `3750` twice.

The animation shows the value returned by the method. The CLI scripts use `echo` to print it.

<video controls preload="none" poster="/videos/php-strict-types.webp" width="960" height="540" style="max-width:100%;height:auto;" aria-label="Silent PHP strict types animation. A transcript follows.">
  <source src="/videos/php-strict-types.mp4" type="video/mp4">
  <a href="/videos/php-strict-types.mp4">Download the animation</a>
</video>

**Animation transcript.** The animation shows the method `forQuantity(int $quantity): int` and the expression `(new UnitPrice(1250))->forQuantity("3")`. With the default calling mode, PHP converts the string to an integer and the method returns `3750`. The animation then shows `declare(strict_types=1);` above the same expression in the calling file. This time PHP raises `TypeError` before the method body runs. The last frame asks which file makes the call.

</details>

## The file making the call matters

Open [`types.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/types.php) and [`types-coercive.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/types-coercive.php). They run the same calls. The strict file adds `declare(strict_types=1);` after `<?php`. The coercive file leaves it out.

For a direct call to a user-defined function or method, the calling file controls scalar argument coercion. The declaration in `UnitPrice.php` applies to calls made in that file. Try the following values by changing `$input` in each caller.

| Value for `$input` | Strict caller | Coercive caller |
| --- | --- | --- |
| `3` | Returns `3750` | Returns `3750` |
| `"3"` | Raises `TypeError` | Converts to `3` and returns `3750` |
| `"three"` | Raises `TypeError` | Raises `TypeError` |

Strict scalar checking still accepts an `int` for a `float` parameter. Operators have their own rules. In the strict file, `"3" == 3` evaluates to `true`, and `"3" === 3` evaluates to `false`. Try both with `var_dump()`. [Strict typing rules](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict) · [Comparison operators](https://www.php.net/manual/en/language.operators.comparison.php)

### Return values follow the declaring file

To see a return check, save this as `return-quantity.php` in a scratch directory.

```php
<?php

declare(strict_types=1);

function quantity(): int
{
    return '3';
}
```

From that directory, run `php -r 'require "return-quantity.php"; quantity();'`. The caller uses the default coercive mode, but `quantity()` raises a `TypeError` because the strict file that defines it declares an `int` return and returns a string. [PHP return type example](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)

### Combining native types

A union such as `int|string` accepts either type. Passing `"3"` to that union keeps it as a string, even in a coercive call, because it already matches the `string` member. An intersection such as `Countable&Iterator` requires a single object that implements both interfaces. An `ArrayIterator` satisfies that declaration. [PHP type system](https://www.php.net/manual/en/language.types.type-system.php) · [Coercion with unions](https://www.php.net/manual/en/language.types.type-juggling.php)

### Types and business rules

Try these two commands from the checkout root.

```bash
php -r 'require "code/php-misconceptions/UnitPrice.php"; new UnitPrice(-1);'
php -r 'require "code/php-misconceptions/UnitPrice.php"; echo (new UnitPrice(1250))->forQuantity(-1), PHP_EOL;'
```

| Expression | Result |
| --- | --- |
| `new UnitPrice(-1)` | The constructor throws `InvalidArgumentException` because the unit price must be non-negative. |
| `(new UnitPrice(1250))->forQuantity(-1)` | Returns `-1250` because `-1` is an integer and the method has no quantity range check. |

If an order can only contain non-negative quantities, check that rule before pricing. Put the rule inside the pricing method too if it must hold for every caller.

## What a static analyser adds

[`quantity-list.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/quantity-list.php) prices a batch by forwarding each array element to `forQuantity()`. Its function declares the native type `array` and adds a more specific PHPDoc contract for PHPStan.

```php
/** @param list<int> $quantities */
function totalQuantities(UnitPrice $price, array $quantities): int
{
    $total = 0;

    foreach ($quantities as $quantity) {
        $total += $price->forQuantity($quantity);
    }

    return $total;
}
```

A `list<int>` has integer values and sequential keys starting at zero. The supplied script passes `['3']`. The native `array` parameter accepts it, then the strict call inside the loop raises `TypeError` for the string element. [PHPStan list types](https://phpstan.org/writing-php-code/phpdoc-types#lists)

```bash
php code/php-misconceptions/quantity-list.php
```

From the checkout root, download the PHPStan version used for this example and analyse both the class and the script.

```bash
mkdir -p tmp/php-types
curl -fL https://github.com/phpstan/phpstan/releases/download/2.3.7/phpstan.phar -o tmp/php-types/phpstan.phar
php tmp/php-types/phpstan.phar analyse --level=5 --no-progress -v \
  code/php-misconceptions/UnitPrice.php code/php-misconceptions/quantity-list.php
```

PHPStan reports `argument.type` at the call to `totalQuantities()`. It expects `list<int>` and receives `array{'3'}`. That is the expected first result, with exit status 1.

Change the last line in `quantity-list.php` to pass `[3]` and rerun the PHP and PHPStan commands. PHP prints `3750`, and PHPStan reports no errors with exit status 0. Then try `[2 => 3]`. PHP still prints `3750`, but PHPStan flags the key sequence because it starts at 2. PHPStan checks the contract against the code it analyses. Validate data from forms, files or APIs before passing it into a function that relies on that contract. [PHPStan command-line usage](https://phpstan.org/user-guide/command-line-usage) · [PHPDoc generics](https://phpstan.org/blog/generics-in-php-using-phpdocs)

## Exercise

### Confirm the strict call succeeds

In `code/php-misconceptions/types.php`, set `$input = 3;`. The second call prints its return value on success. When you rerun the script, expect two lines of `3750`.

### Validate a quantity before calling typed code

Run [`validate-quantity.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/validate-quantity.php) with each input. The CLI argument stands in for a string received from a form.

```bash
php code/php-misconceptions/validate-quantity.php 3
php code/php-misconceptions/validate-quantity.php 0
php code/php-misconceptions/validate-quantity.php 3abc
php code/php-misconceptions/validate-quantity.php -1
```

The first two commands print `3750` and `0`. The other two print a validation error and exit with status 1. The script uses `FILTER_VALIDATE_INT` with an allowed range of 0 to 1,000. On success the filter returns an integer. The condition `$quantity === false` distinguishes validation failure from a valid zero. A cast such as `(int) '3abc'` yields `3`, so casting alone would silently accept that input. [PHP integer validation](https://www.php.net/manual/en/filter.constants.validation.php)

### Apply the same checks to a batch

A form submits `['3', '0', '3abc', '-1']`. Where would you check the raw entries, enforce the pricing rules and verify the list contract? Assume the form rejects the whole batch when any entry is invalid.

<details>
<summary>Check your answer</summary>

1. In the form handler, verify that the field contains an array and that each entry is a string. Apply the same integer and range validation from the single-quantity example to each entry. Reject the batch when an entry fails. Otherwise, append each parsed integer to a new array with `$quantities[] = $quantity` to create consecutive keys.
2. In `UnitPrice`, keep the constructor’s non-negative price check and the method’s native `int` signature. If negative quantities are invalid for every caller, add a guard for `$quantity < 0` in `forQuantity()`.
3. At the call to `totalQuantities()`, PHPStan can check that the code passes a `list<int>`. For a corrected batch of `['3', '0']`, validation produces `[3, 0]` and the function returns `3750` cents.

</details>

## Next chapter

[Is PHP Always Slow?](/series/php-misconceptions/chapters/02-php-performance/)
