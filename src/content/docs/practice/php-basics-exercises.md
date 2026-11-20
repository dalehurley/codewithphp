---
title: "PHP Basics Offline Exercise Pack"
description: "Download four PHP Basics exercises with starter files, test inputs, and working solutions for calculations, FizzBuzz, functions, and arrays."
prev: false
next: false
---

Practise four exercises from [PHP Basics](/series/php-basics/) offline. You need PHP 8.0 or later on your machine. Use the [PHP setup lesson](/series/php-basics/chapters/00-setting-up-your-development-environment/) if `php -v` is unavailable.

[Download the PHP Basics exercise pack (ZIP)](/downloads/practice/php-basics-exercises.zip)

## Work through the exercises

1. Extract the ZIP and open the `php-basics-exercises` folder in a terminal.
2. Read the matching section in `README.md` for the instructions and expected values.
3. Open a starter script in your editor. For the first exercise, edit `starters/01-calculations.php` and run `php starters/01-calculations.php`.
4. When the values match, try different inputs. Compare your approach with the corresponding script in `solutions/`.

| Exercise | Check your result | Read the lesson |
| --- | --- | --- |
| Calculate with two numbers | For 20 and 5, the sum is 25 and the quotient is 4. | [First PHP script](/series/php-basics/chapters/01-your-first-php-script/) |
| Print FizzBuzz from 1 to 100 | Item 15 is FizzBuzz and item 100 is Buzz. | [Control structures](/series/php-basics/chapters/03-control-structures/) |
| Convert temperatures with functions | 25 °C equals 77 °F and 298.15 K. | [Functions](/series/php-basics/chapters/04-understanding-and-using-functions/) |
| Sort a grade report by average | Charlie ranks first with 95.00. Eve is last with 80.40. | [Arrays](/series/php-basics/chapters/06-deep-dive-into-arrays/) |

## Compare two working approaches

### Choose a FizzBuzz label

Run these from the extracted folder:

```sh
php solutions/03-fizzbuzz.php
php solutions/03-fizzbuzz-match.php
```

The chapter solution uses an `if` chain for the first 100 items, then demonstrates `match` for 50. The alternate file uses `match` for all 100. Both check the combined condition before testing individual factors. An `if` chain leaves room for several actions in each branch. A `match` expression fits here when the only decision is which label to return.

### Average a grade list

```sh
php solutions/06-student-grades.php
php solutions/06-student-grades-array-map.php
```

The chapter solution builds a student-to-average array with `foreach`. The alternate applies `array_map` to a single associative array so each calculated average keeps its original student key. Use the loop if you want to inspect each row or apply different rules per student. The map is short when every row follows the same formula.

If you'd rather fix an existing script, try the [PHP debugging challenges](/practice/debugging-challenges/).
