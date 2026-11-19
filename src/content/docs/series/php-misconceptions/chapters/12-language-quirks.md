---
title: "12: Which PHP Quirks Still Matter?"
description: "Test comparison rules, false-or-integer results and array keys against the PHP runtime you use today."
series: "php-misconceptions"
chapter: 12
estimatedTime: "PT10M"
sidebar:
  order: 12
---

## Overview

Some historical criticism of PHP describes behaviour you can still reproduce. Some describes APIs and defaults that have changed. PHP 8 changed how loose comparisons handle numbers and non-numeric strings. The old examples are a good reason to check your runtime and read the exact return type of an unfamiliar function. [Eevee’s 2012 critique](https://eev.ee/blog/2012/04/09/php-a-fractal-of-bad-design/) · [PHP 8.0 migration guide](https://www.php.net/manual/en/migration80.incompatible.php)

## Quick Start

Run the script from the repository root with PHP 8.4 or later.

```bash
php code/php-misconceptions/quirks.php
```

```text
0 == "php": false
0 == "0": true
0 === "0": false
Match at offset 0
{"0":"Ada","2":"Linus"}
["Ada","Linus"]
```

- **Comparison.** PHP 8 no longer treats a non-numeric string like `"php"` as zero when loosely comparing it to a number. The numeric string `"0"` still compares equal to `0` with `==`. Use `===` when the type is part of the comparison. The example already uses `strict_types=1`, which does not change the behaviour of `==`. [Number and string comparisons](https://wiki.php.net/rfc/string_to_number_comparison)
- **Return values.** `strpos()` returns a zero-based byte offset or `false`. A truthiness check treats offset zero as false. Use `!== false` to check whether you got a position, or `str_contains()` for a Boolean answer. [strpos](https://www.php.net/manual/en/function.strpos.php) · [str_contains](https://www.php.net/manual/en/function.str-contains.php)
- **Arrays.** PHP arrays are ordered maps. Unsetting the middle element here leaves keys 0 and 2, so JSON encodes the remaining map as an object. Use `array_values()` to renumber the keys when the caller expects a JSON list. [PHP arrays](https://www.php.net/manual/en/language.types.array.php) · [JSON encoding](https://www.php.net/manual/en/function.json-encode.php)

The standard library still has different naming and argument conventions. Compare `strpos($text, $term)` with `in_array($term, $items, true)`. An IDE can surface those signatures. When an awkward API appears repeatedly at an application boundary, a small function with a domain name can give callers a simpler contract. [strpos](https://www.php.net/manual/en/function.strpos.php) · [in_array](https://www.php.net/manual/en/function.in-array.php)

## Exercise

Search for `'lar'`, `'vel'` and `'xyz'` in `'laravel'`. Predict the three results of `strpos()` before running it. A correct check distinguishes `0`, `4` and `false`.

## Next chapter

[Does Legacy PHP Always Need a Rewrite?](/series/php-misconceptions/chapters/13-upgrading-legacy-php/)
