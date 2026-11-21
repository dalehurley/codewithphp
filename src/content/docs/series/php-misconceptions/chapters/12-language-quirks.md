---
title: "12: Which PHP Quirks Still Matter?"
description: "Test PHP comparisons, search offsets and JSON list shape by running and breaking a small catalogue filter."
series: "php-misconceptions"
chapter: 12
estimatedTime: "PT20M"
sidebar:
  order: 12
---

<span id="overview"></span>

## The catalogue contract

You’re upgrading PHP for a small catalogue report. A search for `lar` should find both `laravel starter` and `learn laravel`. The caller needs an ordered JSON list, even when the report finds nothing, and treats `"08"` and `"8"` as distinct item codes.

Which parts of that contract can PHP’s defaults break today? Start with three small probes, then run the report and change one decision at a time.

## Quick Start

Run the commands from the root of a local Code with PHP checkout with PHP 8.6, the series baseline. The chapter examples also run on PHP 8.5. The catalogue script uses `match`, introduced in PHP 8.0. Only the optional `mb_strpos()` example needs the `mbstring` extension. [Series setup](/series/php-misconceptions/#what-youll-need) · [Quirks example source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/quirks.php) · [PHP match](https://www.php.net/manual/en/control-structures.match.php)

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

The first three lines of the quick start show what happens when a number meets a string. Some examples in the 2012 critique predate PHP 8. PHP 7 converted `'php'` to `0` for the loose comparison below. Since PHP 8.0, if the string is non-numeric, PHP converts the number to a string for the comparison. A number and a numeric string still compare numerically with `==`, as do two numeric strings. The PHP 7 column follows the migration guide and RFC. [Eevee’s 2012 critique](https://eev.ee/blog/2012/04/09/php-a-fractal-of-bad-design/) · [PHP 8.0 migration guide](https://www.php.net/manual/en/migration80.incompatible.php) · [Number and string comparison RFC](https://wiki.php.net/rfc/string_to_number_comparison)

| Expression | PHP 7 behaviour | PHP 8 behaviour |
| --- | --- | --- |
| `0 == 'php'` | `true` | `false` |
| `0 == '0'` | `true` | `true` |
| `0 === '0'` | `false` | `false` |
| `'08' == '8'` | `true` | `true` |
| `'08' === '8'` | `false` | `false` |

Suppose the caller allows only code `'8'`. Predict whether each membership check will accept `'08'`, then run them.

```bash
php -r 'declare(strict_types=1); var_dump(in_array("08", ["8"]), in_array("08", ["8"], true));'
```

```text
bool(true)
bool(false)
```

`strict_types=1` affects scalar type declarations at call boundaries. Here, it takes the third argument to make `in_array()` compare the strings exactly. Use `===` to compare two catalogue codes directly. Keep code values as strings throughout the report so `"08"` retains its leading zero. [Comparison operators](https://www.php.net/manual/en/language.operators.comparison.php) · [Strict membership](https://www.php.net/manual/en/function.in-array.php) · [Type declarations and input validation](/series/php-misconceptions/chapters/01-php-types/)

## Distinguish a position from a Boolean

The report searches names by substring. `strpos()` returns either the number of bytes before the first match or `false` when the term is absent. Try three searches and a UTF-8 example. Save the examples in UTF-8 if you put them in a file.

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

Use `$position !== false` when testing a returned position. Casting to Boolean would discard `laravel starter` because byte zero is falsy. For a caller that needs the position in characters, `mb_strpos('café noir', ' ', 0, 'UTF-8')` gives `4` with the `mbstring` extension. Use `str_contains($text, $term)` for a yes-or-no search. Observe the argument order when switching between these APIs. String searches take the text first, and `in_array($code, $knownCodes, true)` takes the value to find first. [strpos](https://www.php.net/manual/en/function.strpos.php) · [mb_strpos](https://www.php.net/manual/en/function.mb-strpos.php) · [str_contains](https://www.php.net/manual/en/function.str-contains.php) · [in_array](https://www.php.net/manual/en/function.in-array.php)

## Choose a JSON shape

Once the report has the right rows, the PHP array keys determine what the caller sees. PHP arrays are ordered maps. In the quick start, `unset($names[1])` leaves keys `0` and `2`, so `json_encode()` writes an object. `array_values()` rebuilds a list, which encodes as a JSON array. A PHP list has integer keys from `0` through `count($array) - 1` in iteration order. You can check that with `array_is_list()`. An empty PHP array also counts as a list and encodes as `[]`. [PHP array keys](https://www.php.net/manual/en/language.types.array.php) · [array_is_list](https://www.php.net/manual/en/function.array-is-list.php) · [JSON encoding](https://www.php.net/manual/en/function.json-encode.php)

The report stores each code as a string in a `code` field. Using codes as PHP array keys has different rules. PHP converts key `'8'` to integer `8` and keeps `'08'` as a string, so assigning both `'8'` and integer `8` addresses the same slot. For an in-memory lookup that needs textual keys, prefix them as in `code:8` and `code:08`. If a caller needs a JSON object keyed by the original codes, encode an object explicitly, including `{}` for an empty result. You’ll compare those output shapes in the exercise. [PHP array key conversion](https://www.php.net/manual/en/language.types.array.php) · [JSON encoding](https://www.php.net/manual/en/function.json-encode.php)

## Run the catalogue filter

The catalogue command puts those choices together. Its non-empty query is case-sensitive and defaults to `lar`. The optional second argument is an enabled-only flag with a default of `1`. The command uses `match` to accept the exact CLI strings `'1'` for enabled entries and `'0'` for all entries. The string `'false'` is rejected. Casting it to Boolean would produce `true`. [Catalogue filter source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/catalog-filter.php) · [PHP match](https://www.php.net/manual/en/control-structures.match.php) · [PHP Boolean conversion](https://www.php.net/manual/en/language.types.boolean.php)

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

The `lar 0` run also includes the disabled `laravel archive` with code `"8"` between `"08"` and `"0"`. The `xyz 1` run prints `[]`. The invalid flag prints usage to standard error, leaves standard output empty and exits with status `1`. An empty query also fails validation.

Follow the default run through the code. The enabled check drops the row at key `1`. Then `strpos(...) !== false` accepts `lar` at byte `0` of `laravel starter` and byte `6` of `learn laravel`. The codes travel with those records as string values. Since `array_filter()` preserves original keys, `array_values($matches)` reindexes surviving rows before JSON encoding. That keeps the response a list for every successful search. [PHP array filtering](https://www.php.net/manual/en/function.array-filter.php)

## Exercise

From the checkout root, use Bash or Zsh to make a disposable copy. In each step, predict the output before running the edited script.

```bash
scratch="$(mktemp -d)" && cp code/php-misconceptions/catalog-filter.php "$scratch/"
php "$scratch/catalog-filter.php"
```

1. In the copy, replace `strpos($entry['name'], $query) !== false` with `(bool) strpos($entry['name'], $query)` and rerun the last command. Which entry disappears? Restore the original comparison.
2. Replace `array_values($matches)` in the final line with `$matches` and rerun. Which keys appear in the JSON? Restore the original call.
3. A different caller expects a JSON object keyed by code. Assume each code is unique. Replace `array_values($matches)` in the final line with `(object) array_column($matches, null, 'code')`. Run the three searches below. What happens to the one-match and no-match outputs if you remove the `(object)` cast? Restore the original list output for the next check. [PHP array_column](https://www.php.net/manual/en/function.array-column.php)

```bash
php "$scratch/catalog-filter.php" lar 0
php "$scratch/catalog-filter.php" learn 1
php "$scratch/catalog-filter.php" xyz 1
```

<details>
<summary>Check your reasoning</summary>

1. The entry with code `"08"` disappears because its position is `0`. The entry with code `"0"` remains because `lar` occurs later in `learn laravel`. Use `!== false` to keep both matches.
2. The enabled entries still have PHP keys `0` and `2`. They appear as the outer JSON object keys `"0"` and `"2"`, even though the report contains the same records. Reindex the list before encoding it.
3. For `lar 0`, `array_column()` creates PHP keys `'08'`, `8` and `0`. The JSON object has property names `"08"`, `"8"` and `"0"`. The `learn 1` search returns only code `"0"`. Its PHP lookup has a single integer key `0`, so the raw array encodes as a JSON list. With the cast it encodes as `{"0":{"code":"0","name":"learn laravel","enabled":true}}`. For `xyz 1`, the raw lookup encodes as `[]` and the cast produces `{}`.

</details>

### Check the command before an upgrade

Run this with the original list output restored. Compare standard output, standard error and exit status for the default search, a valid search with no results and an invalid flag.

```bash
check_catalog() {
  local exit_status=0
  php "$scratch/catalog-filter.php" "$@" >"$scratch/out" 2>"$scratch/err" || exit_status=$?
  printf '%s -> status=%s stdout=[%s] stderr=[%s]\n' \
    "${*:-default}" "$exit_status" "$(cat "$scratch/out")" "$(cat "$scratch/err")"
}
check_catalog
check_catalog xyz 1
check_catalog lar false
```

The default search exits `0` with the two enabled records on stdout, in catalogue order, and empty stderr. Expect exit `0` with `[]` on stdout and empty stderr for `xyz 1`. Expect exit `1` with empty stdout and the usage message on stderr for `lar false`. Run the same checks before and after an upgrade.

## Next chapter

[Does Legacy PHP Always Need a Rewrite?](/series/php-misconceptions/chapters/13-upgrading-legacy-php/)
