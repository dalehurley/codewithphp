---
title: "12: Which PHP Quirks Still Matter?"
description: "Test PHP comparisons, search offsets and JSON list shape by running and breaking a small catalogue filter."
series: "php-misconceptions"
chapter: 12
estimatedTime: "PT20M"
sidebar:
  order: 12
---

## Overview

A small catalogue report has three requirements. The codes `'08'` and `'8'` identify different entries, a search can match anywhere in a name including at the start, and clients expect a JSON list. Comparisons, return values and array keys all affect that contract. Some well-known PHP examples date from before PHP 8. Run the examples on your current PHP version and check which behaviour your callers depend on. [Eevee’s 2012 critique](https://eev.ee/blog/2012/04/09/php-a-fractal-of-bad-design/) · [PHP 8.0 migration guide](https://www.php.net/manual/en/migration80.incompatible.php)

## Quick Start

From the checkout root, run the original example with PHP 8.6, the series baseline. The examples were also verified on PHP 8.5.0. [Quirks example source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/quirks.php)

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

## Identify which comparisons changed

| Expression | PHP 7 behaviour | PHP 8 behaviour |
| --- | --- | --- |
| `0 == 'php'` | `true` | `false` |
| `0 == '0'` | `true` | `true` |
| `0 === '0'` | `false` | `false` |
| `'08' == '8'` | `true` | `true` |
| `'08' === '8'` | `false` | `false` |

The PHP 7 column follows the migration notes and RFC. When PHP 8 loosely compares a number with a non-numeric string, it converts the number to a string for the comparison. A number and a numeric string still compare numerically, as do two numeric strings with `==`. Compare catalogue codes with `===`, or use `in_array($code, $knownCodes, true)` for membership. `strict_types=1` affects scalar type declarations at call boundaries. It leaves comparison rules and raw CLI strings as they are. [Number and string comparison RFC](https://wiki.php.net/rfc/string_to_number_comparison) · [Comparison operators](https://www.php.net/manual/en/language.operators.comparison.php) · [Strict membership](https://www.php.net/manual/en/function.in-array.php) · [Type declarations and input validation](/series/php-misconceptions/chapters/01-php-types/)

## Distinguish a position from a Boolean

`strpos()` gives a zero-based byte offset or `false`. Try the three outcomes and a UTF-8 example. Save the examples in UTF-8 if you put them in a file.

```bash
php -r 'foreach (["lar", "vel", "xyz"] as $term) { var_export(strpos("laravel", $term)); echo PHP_EOL; }'
php -r 'var_export(strpos("café noir", " ")); echo PHP_EOL;'
```

| Search | Expected result |
| --- | --- |
| `lar` in `laravel` | `0`, a match at the start |
| `vel` in `laravel` | `4`, a later match |
| `xyz` in `laravel` | `false`, no match |
| Space in UTF-8 `café noir` | `5`, because `é` takes two bytes |

Use `$position !== false` when testing a returned position. A truthiness check drops matches at byte zero. With the `mbstring` extension, `mb_strpos('café noir', ' ', 0, 'UTF-8')` gives the character position `4`. Use `str_contains($text, $term)` when you only need a yes or no answer. The standard library uses different argument orders. Compare `strpos($text, $term)` with `in_array($term, $items, true)`. A small function named for your application can keep those details in one place when the same operation appears at several call sites. [strpos](https://www.php.net/manual/en/function.strpos.php) · [mb_strpos](https://www.php.net/manual/en/function.mb-strpos.php) · [str_contains](https://www.php.net/manual/en/function.str-contains.php) · [in_array](https://www.php.net/manual/en/function.in-array.php)

## Choose a JSON shape

PHP arrays are ordered maps. In the original example, `unset($names[1])` leaves keys `0` and `2`. JSON encodes that sparse array as an object. `array_values()` rebuilds a consecutive list starting at `0`, which JSON then encodes as an array. `array_is_list()` can check the consecutive-key rule before encoding. An empty PHP array counts as a list and encodes as `[]`. For a JSON object contract, preserve the meaningful keys and encode an object, including an empty object `{}` when there are no entries. [PHP array keys](https://www.php.net/manual/en/language.types.array.php) · [array_is_list](https://www.php.net/manual/en/function.array-is-list.php) · [JSON encoding](https://www.php.net/manual/en/function.json-encode.php)

PHP converts array key `'8'` to integer `8` and keeps `'08'` as a string. If both `'8'` and `8` appear as keys in the same array, the latter value overwrites the former. For a list of records, store codes in a `code` field as strings. For an in-memory lookup indexed by textual codes, prefix the keys, for example `code:8` and `code:08`. [PHP array key conversion](https://www.php.net/manual/en/language.types.array.php)

## Run the catalogue filter

The command accepts a non-empty, case-sensitive query and an enabled-only flag. The defaults are `lar` and `1`. It parses `1` and `0` explicitly, so `0` includes disabled entries and a string such as `false` produces an input error. Casting the non-empty string `'false'` to a Boolean would turn it into `true`. [Catalogue filter source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/catalog-filter.php) · [PHP Boolean conversion](https://www.php.net/manual/en/language.types.boolean.php)

```bash
php code/php-misconceptions/catalog-filter.php
php code/php-misconceptions/catalog-filter.php lar 0
php code/php-misconceptions/catalog-filter.php xyz 1
php code/php-misconceptions/catalog-filter.php lar false
```

The default output keeps the two enabled entries in catalogue order, one with a prefix match and the other with a later match.

```json
[{"code":"08","name":"laravel starter","enabled":true},{"code":"0","name":"learn laravel","enabled":true}]
```

The `lar 0` run includes the disabled entry with code `"8"` between `"08"` and `"0"`. The `xyz 1` run prints `[]`. The invalid flag prints usage to standard error, leaves standard output empty and exits with status 1. An empty query also fails validation. Successful calls always emit a JSON list. The report uses `!== false` for matches at any position, including byte zero. It reindexes after filtering because `array_filter()` preserves keys. [PHP array filtering](https://www.php.net/manual/en/function.array-filter.php)

## Exercise

From the checkout root, make a disposable copy.

```bash
scratch="$(mktemp -d)" && cp code/php-misconceptions/catalog-filter.php "$scratch/"
php "$scratch/catalog-filter.php"
```

1. In the copy, replace `strpos($entry['name'], $query) !== false` with `(bool) strpos($entry['name'], $query)` and rerun the last command. Which entry disappears? Restore the original comparison.
2. Replace `array_values($matches)` in the final line with `$matches` and rerun. Which keys appear in the JSON? Restore the original call.
3. A different caller expects to look up catalogue records by textual code. Assume each code is unique. Replace `array_values($matches)` in the final line with `(object) array_column($matches, null, 'code')`. Run both commands below. Which keys are present, and what shape does an empty result have? Restore the original list output afterwards. [PHP array_column](https://www.php.net/manual/en/function.array-column.php)

```bash
php "$scratch/catalog-filter.php" lar 0
php "$scratch/catalog-filter.php" xyz 1
```

<details>
<summary>Check your reasoning</summary>

1. The entry with code `"08"` disappears because its position is `0`. The entry with code `"0"` remains because `lar` occurs later in `learn laravel`. Use `!== false` to keep both matches.
2. The enabled entries still have PHP keys `0` and `2`. They appear as the outer JSON object keys `"0"` and `"2"`, even though the report contains the same records. Reindex the list before encoding it.
3. With `lar 0`, the outer JSON object has distinct keys `"08"`, `"8"` and `"0"`. The `0` flag includes the disabled entry with code `"8"`. With `xyz 1`, the object cast keeps the empty result as `{}`. Give callers that still expect an ordered list the original response. Capture both shapes with fixtures, including a prefix match, a later match, no matches and an invalid flag, before changing PHP versions.

</details>

## Next chapter

[Does Legacy PHP Always Need a Rewrite?](/series/php-misconceptions/chapters/13-upgrading-legacy-php/)
