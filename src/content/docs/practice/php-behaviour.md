---
title: "PHP Behaviour Across Versions With Runnable Examples"
description: "Run three small PHP scripts and compare outputs for loose equality, negative number_format precision, and invalid date exceptions."
prev: false
next: false
---

These three scripts show a change you can spot when updating PHP. Each script runs on both sides of the change. Predict the output, then run the script with your installed PHP version.

[Download all three source files (ZIP)](/downloads/practice/php-behaviour-examples.zip)

Open the extracted `php-behaviour-examples` folder in your terminal. Use `php -v` to see which runtime you are testing. The older version bands describe what to expect from historical runtimes. The [PHP support schedule](https://www.php.net/supported-versions.php) lists releases you can use for a current project.

## Loose comparison with non-numeric strings

```php
<?php

var_dump(0 == 'pineapple');
var_dump(0 === 'pineapple');
```

| Runtime | First line | Second line |
| --- | --- | --- |
| PHP 7.4 | `bool(true)` | `bool(false)` |
| PHP 8.0 and later | `bool(false)` | `bool(false)` |

Run `php loose-comparison.php` or open [the loose comparison source](https://github.com/dalehurley/codewithphp/blob/main/code/learning-resources/practice/behaviour/loose-comparison.php). In PHP 8, loose comparison of a number and a non-numeric string compares their string forms. Strict comparison checks the types and values. Try replacing `'pineapple'` with `'0'`. The loose result is true in both versions because `'0'` is numeric. [PHP 8.0 migration guide](https://www.php.net/manual/en/migration80.incompatible.php)

## Rounding to the nearest hundred

```php
<?php

echo number_format(1764.25, -2), PHP_EOL;
```

| Runtime | Output |
| --- | --- |
| PHP 7.4 to 8.2 | `1,764` |
| PHP 8.3 and later | `1,800` |

Run `php round-before-decimal.php` or open [the rounding source](https://github.com/dalehurley/codewithphp/blob/main/code/learning-resources/practice/behaviour/round-before-decimal.php). From PHP 8.3, negative precision tells `number_format` how many places to round before the decimal point. Earlier versions treated it as zero. If you maintain code that runs on both, use `number_format(round(1764.25, -2))` to make the rounding explicit and get `1,800` from either. [`number_format` in the PHP manual](https://www.php.net/manual/en/function.number-format.php)

## Invalid date input

```php
<?php

try {
    new DateTimeImmutable('this is not a date');
} catch (Exception $error) {
    echo get_class($error), PHP_EOL;
}
```

| Runtime | Output |
| --- | --- |
| PHP 7.4 to 8.2 | `Exception` |
| PHP 8.3 and later | `DateMalformedStringException` |

Run `php invalid-date.php` or open [the date source](https://github.com/dalehurley/codewithphp/blob/main/code/learning-resources/practice/behaviour/invalid-date.php). A catch for `Exception` handles either. If your project requires PHP 8.3 or later, catch `DateMalformedStringException` specifically where you want to ask a reader for a different date. Try replacing the input with `2026-11-20` to run the success path. [`DateTimeImmutable` constructor changelog](https://www.php.net/manual/en/datetimeimmutable.construct.php)

For more on updating an existing project, see the [PHP 8.6 upgrade checklist](/series/php-8-6/upgrade-checklist/).
