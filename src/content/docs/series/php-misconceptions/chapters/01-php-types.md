---
title: "01: Can PHP Be Typed?"
description: "Compare strict and coercive PHP calls, validate form input before pricing it, and use PHPStan to check a list of integers."
series: "php-misconceptions"
chapter: 1
estimatedTime: "PT20M"
sidebar:
  order: 1
---

<span id="overview"></span>

## Predict the result

Your order form sends the string `'3'`, and your pricing method expects `forQuantity(int $quantity): int`. Each item costs 1,250 cents. Will PHP convert the string and calculate 3,750 cents, or reject the call? What changes if the calling file starts with `declare(strict_types=1);`?

PHP is dynamically typed. A local variable can hold `3` and then `'3'`. When you declare a type on a parameter, return value or property, PHP checks that contract at runtime. The form still has to decide what counts as an acceptable order quantity. [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php)

## Prerequisites

Use the PHP command-line interface (CLI) with PHP 8.2 or later for this chapter’s examples. The series targets PHP 8.6. You’ll also need Git if you’re cloning the repository and `curl` to download PHPStan. Commands use a Bash-compatible shell such as Bash, Zsh or Git Bash on Windows. [PHP setup guide](/series/php-basics/chapters/00-setting-up-your-development-environment/) · [PHP 8.2 readonly classes](https://www.php.net/manual/en/migration82.new-features.php#migration82.new-features.core.readonly-classes)

If you already have the checkout, open a terminal at its root and run `php --version`. For a new checkout, run these commands.

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp
php --version
```

## Quick Start

These excerpts isolate the call with the string `'3'`. The full scripts also call the method with the integer `3` and catch any `TypeError` from the second call.

In `types.php`, the caller opts into strict typing.

```php
<?php

declare(strict_types=1);

require __DIR__ . '/UnitPrice.php';

$price = new UnitPrice(1250);
$input = '3';
echo $price->forQuantity($input), PHP_EOL;
```

In `types-coercive.php`, the caller uses the default mode.

```php
<?php

require __DIR__ . '/UnitPrice.php';

$price = new UnitPrice(1250);
$input = '3';
echo $price->forQuantity($input), PHP_EOL;
```

Both scripts load the same [`UnitPrice` class](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/UnitPrice.php). Its constructor declares `public int $cents`, which creates both an `int` parameter and a matching property. The `readonly` modifier prevents reassignment after initialisation. In `forQuantity(int $quantity): int`, the first `int` is the parameter type and the second is the return type. [Constructor promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion) · [Readonly classes](https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.readonly)

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

Run the two scripts from the checkout root.

```bash
php code/php-misconceptions/types.php
php code/php-misconceptions/types-coercive.php
```

<details>
<summary>Reveal the results and watch the animation</summary>

Both scripts print `3750` for the integer. For the string, the strict script catches `TypeError` and prints `The quantity call raised a TypeError.` The coercive script prints `3750` again.

<video controls preload="none" poster="/videos/php-strict-types.webp" width="960" height="540" style="max-width:100%;height:auto;" aria-label="Silent PHP strict types animation. A transcript follows.">
  <source src="/videos/php-strict-types.mp4" type="video/mp4">
  <a href="/videos/php-strict-types.mp4">Download the animation</a>
</video>

**Animation transcript.** The animation shows the method `forQuantity(int $quantity): int` and the call `(new UnitPrice(1250))->forQuantity("3")`. In the default mode, PHP converts the string to an integer and the method returns `3750`. The animation then puts `declare(strict_types=1);` in the calling file. PHP raises `TypeError` before the method body runs. The closing question asks which file makes the call. That file determines the scalar argument check for this direct call.

</details>

## The file making the call matters

[`types.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/types.php) calls the method in strict mode. [`types-coercive.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/types-coercive.php) allows PHP to convert the numeric string before the method runs. That automatic conversion is called *coercion*. For a direct call to a user-defined function or method, the calling file controls scalar argument coercion, even when the function or method lives in a different file. [PHP strict typing rules](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)

Change `$input` in each caller and rerun the scripts. The table shows the second call in each script.

| Value for `$input` | Strict caller | Coercive caller |
| --- | --- | --- |
| `3` | Returns `3750` | Returns `3750` |
| `'3'` | Raises `TypeError` | Converts to `3` and returns `3750` |
| `'three'` | Raises `TypeError` | Raises `TypeError` |

Strict scalar checking accepts an `int` for a `float` parameter. Comparisons follow their own rules. Run `php -r 'declare(strict_types=1); var_dump("3" == 3, "3" === 3);'` to see `bool(true)` and `bool(false)`. [PHP strict typing rules](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict) · [Comparison operators](https://www.php.net/manual/en/language.operators.comparison.php)

### Return values follow the declaring file

Save this as `return-quantity.php` in a scratch directory.

```php
<?php

declare(strict_types=1);

function quantity(): int
{
    return '3';
}
```

From that directory, run `php -r 'require "return-quantity.php"; quantity();'`. The caller uses the default mode, but `quantity()` raises a `TypeError`. The return happens in the strict file, where `quantity()` promises to return an `int`. [PHP return type example](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict)

### Property writes follow the assignment

To see a property check separately from `UnitPrice`’s `readonly` modifier, try an ordinary write to a mutable property.

```bash
php -r 'class Counter { public int $count = 0; } $counter = new Counter(); $counter->count = "3"; var_dump($counter->count);'
php -r 'declare(strict_types=1); class Counter { public int $count = 0; } $counter = new Counter(); $counter->count = "3";'
```

The first command stores `int(3)`. The second raises `TypeError` at the assignment. If the class and assignment are in separate files, the file with the assignment determines whether PHP can coerce the value. [Typed properties RFC](https://wiki.php.net/rfc/typed_properties_v2#strict_and_coercive_typing_modes)

| Value being checked | File that controls scalar coercion |
| --- | --- |
| Argument to a direct call to a user-defined function or method | File containing the call |
| Value returned by a user-defined function or method | File defining that function or method |
| Value in an ordinary assignment to a typed property | File containing the assignment |

### Combining native types

A union such as `int|string` accepts either type. The input `'3'` stays a string because it already matches one member. Use `int` when the rest of the method expects a quantity that has already been parsed. An intersection such as `Countable&Iterator` requires a single object that implements both interfaces. An `ArrayIterator` meets that requirement. [PHP type system](https://www.php.net/manual/en/language.types.type-system.php) · [Coercion with unions](https://www.php.net/manual/en/language.types.type-juggling.php)

### Types and business rules

Imagine the form only accepts quantities from 0 to 1,000. Will the native `int` declaration enforce that range? Run these commands from the checkout root.

```bash
php -r 'require "code/php-misconceptions/UnitPrice.php"; new UnitPrice(-1);'
php -r 'require "code/php-misconceptions/UnitPrice.php"; echo (new UnitPrice(1250))->forQuantity(-1), PHP_EOL;'
php -r 'require "code/php-misconceptions/UnitPrice.php"; echo (new UnitPrice(1250))->forQuantity(1001), PHP_EOL;'
```

The constructor throws `InvalidArgumentException` because it explicitly guards against negative unit prices. The two quantity calls print `-1250` and `1251250` because both arguments are valid integers.

In this example, 1,000 is the form's limit. Other callers can price larger quantities. If negative quantities are invalid for every caller, put that guard inside `forQuantity()` as well. A type declaration describes the value PHP can pass into the method. A guard enforces a rule about what the method can do with that value.

## Validate a quantity before calling typed code

Validate the raw field at the point it enters the application. [`validate-quantity.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/validate-quantity.php) uses a CLI argument to stand in for a form field, then passes the parsed integer into the strict pricing call.

```bash
php code/php-misconceptions/validate-quantity.php 3
php code/php-misconceptions/validate-quantity.php 0
php code/php-misconceptions/validate-quantity.php 3abc
php code/php-misconceptions/validate-quantity.php -1
php code/php-misconceptions/validate-quantity.php 1001
```

The first two commands print `3750` and `0`. The others print a validation error and exit with status 1. The script checks the string with `FILTER_VALIDATE_INT` and inclusive bounds of 0 and 1,000. With these options, `0` is valid, but a value with a leading zero such as `03` is rejected. [PHP validation filters](https://www.php.net/manual/en/filter.constants.validation.php)

```php
$quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0, 'max_range' => 1000],
]);

if ($quantity === false) {
    fwrite(STDERR, 'Enter a whole-number quantity from 0 to 1000.' . PHP_EOL);
    exit(1);
}
```

The filter returns an integer on success. The comparison `$quantity === false` treats `0` as a valid result. A cast such as `(int) '3abc'` yields `3` and would silently accept the malformed field. In a form handler, read `$_POST['quantity'] ?? null` and check `is_string($rawQuantity)` before filtering. A missing field or a field sent as an array should be rejected. [PHP form variables](https://www.php.net/manual/en/language.variables.external.php)

## What a static analyser adds

The form handler can now turn a field into a checked integer. When the form sends several quantities, the pricing function also needs a contract for the collection. The batch function in [`total-quantities.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/total-quantities.php) uses the native type `array` and a PHPDoc annotation that PHPStan can check.

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/UnitPrice.php';

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

A `list<int>` has integer values and consecutive keys starting at zero. This exercise uses that contract because the form handler builds a new sequence of quantities. The sum itself only uses the values. If callers needed to retain arbitrary integer keys, `array<int, int>` would describe that contract. Both annotations allow integers outside the form's range. [PHPStan array and list types](https://phpstan.org/writing-php-code/phpdoc-types#lists)

[`quantity-list.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/quantity-list.php) deliberately passes `['3']`. PHP accepts the argument as an array. The later call to `forQuantity()` is in the strict file shown above, so it raises `TypeError` when it receives a string element.

```bash
php code/php-misconceptions/quantity-list.php
```

Expect an uncaught `TypeError` that identifies `forQuantity()` and points to the call inside the loop in `total-quantities.php`. Now check the same code with PHPStan 2.3.7.

```bash
mkdir -p tmp/php-types
curl -fL https://github.com/phpstan/phpstan/releases/download/2.3.7/phpstan.phar -o tmp/php-types/phpstan.phar
php tmp/php-types/phpstan.phar analyse --level=5 --no-progress -v \
  code/php-misconceptions/UnitPrice.php code/php-misconceptions/total-quantities.php \
  code/php-misconceptions/quantity-list.php
```

PHPStan reports `argument.type` at the call to `totalQuantities()` in `quantity-list.php`. It expects `list<int>` and sees `array{'3'}`. This diagnostic and exit status 1 are the expected first result. [PHPStan command-line usage](https://phpstan.org/user-guide/command-line-usage) · [PHPDoc generics](https://phpstan.org/blog/generics-in-php-using-phpdocs)

Change the last line of `quantity-list.php` to pass each of these arrays, then rerun both commands.

| Batch argument | PHP | PHPStan |
| --- | --- | --- |
| `[3]` | Prints `3750` | Passes with exit status 0 |
| `[2 => 3]` | Prints `3750` | Flags the key sequence with exit status 1 |
| `[-1]` | Prints `-1250` | Passes with exit status 0 |
| `[]` | Prints `0` | Passes with exit status 0 |

Put back `['3']` to restore the deliberately failing example. PHPStan checks the list contract in the code it analyses. The form handler still has to inspect and parse the actual submitted values.

## Exercise

### Confirm the strict call succeeds

In `code/php-misconceptions/types.php`, set `$input = 3;` and rerun the script. Expect two lines of `3750`. Restore `$input = '3';` to try the original prediction again.

### Apply the same checks to a batch

An order form submits `['3', '0', '3abc', '-1']`. It needs at least one quantity, and a single invalid entry rejects the whole batch. Write a loop that checks the raw entries, applies the single-quantity filter and builds a list of integers for `totalQuantities()`. Start an exercise file next to `total-quantities.php` with these lines.

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/total-quantities.php';

$rawQuantities = ['3', '0', '3abc', '-1'];
```

Where will you check the form’s range? What should happen if the input keys are sparse, one entry is an array, or the field is missing?

<details>
<summary>Check your answer</summary>

Open the runnable [`validate-quantity-list.php` solution](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/validate-quantity-list.php). It uses a fixture for the field a web handler would read as `$_POST['quantities'] ?? null`. The parser enforces the form’s minimum of one entry. It rejects a missing or empty batch and any entry that fails the string, integer or range check. It appends each parsed integer using `$quantities[] = $quantity`, so the resulting keys start at zero and have no gaps. The strict pricing function then consumes those integers.

Run the default case from the checkout root, then change `$rawQuantities` in the solution and try the other cases.

```bash
php code/php-misconceptions/validate-quantity-list.php
```

| Raw field | Result |
| --- | --- |
| `['3', '0']` | Prices `[3, 0]` and prints `3750` cents |
| `[2 => '3', 5 => '0']` | Reindexes to `[3, 0]` and prints `3750` cents |
| `['3', '0', '3abc', '-1']` or `['1001']` | Prints a validation error and exits with status 1 |
| `[['3']]`, `[]` or `null` | Prints a validation error and exits with status 1 |

Restore `['3', '0']` and run PHPStan on the solution and its dependencies. Expect no errors and exit status 0.

```bash
php tmp/php-types/phpstan.phar analyse --level=5 --no-progress -v \
  code/php-misconceptions/UnitPrice.php code/php-misconceptions/total-quantities.php \
  code/php-misconceptions/validate-quantity-list.php
```

</details>

From the form to the total, validation turns the raw field into acceptable quantities. PHP enforces the native argument, property and return types at runtime. PHPStan checks the list shape and element types before the application runs.

## Next chapter

[Is PHP Always Slow?](/series/php-misconceptions/chapters/02-php-performance/)
