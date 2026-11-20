---
title: "Practical PHP Examples by Task"
description: "Find runnable PHP examples for reading CSV, grouping arrays, working with dates, returning JSON, and saving data in SQLite."
prev: false
next: false
---

Choose something you want PHP to do. Each example leads to runnable code or a lesson with commands and expected output.

## Files and data

| I want to | Use this example |
| --- | --- |
| Read a CSV file in PHP | [Import eight sample books with `fgetcsv`](/practice/sample-dataset/#read-the-csv-in-php) |
| Decode JSON and catch invalid input | [Run the JSON encoding and decoding examples](https://github.com/dalehurley/codewithphp/blob/main/code/php-basics/23-json-apis/json-basics.php) |
| Query a SQLite database | [Count finished books with PDO](/practice/sample-dataset/#query-the-sqlite-file-with-pdo) |
| Save a form submission in SQLite | [Run the one-file reading list](/series/php-basics/sqlite-starter/) |
| Read a file next to a PHP script | [Fix a working-directory bug](/practice/debugging-challenges/#four-short-challenges) |

## Arrays, numbers, and dates

| I want to | Use this example |
| --- | --- |
| Filter an array and return a JSON list | [Keep a filtered list indexed from zero](/practice/debugging-challenges/#four-short-challenges) |
| Group rows by a field | [Run the multidimensional array examples](https://github.com/dalehurley/codewithphp/blob/main/code/php-basics/06-arrays/multidimensional-arrays.php) and look for the `groupBy` function |
| Get one column from a list of rows | [Try `array_column` with the reading list](/practice/php-array-reference/) |
| Sort students by average grade | [Try the offline PHP Basics exercises](/practice/php-basics-exercises/) |
| Work with dates and handle invalid input | [Compare the exception when parsing a bad date](/practice/php-behaviour/#invalid-date-input). For arithmetic, try [adding a day with `DateTimeImmutable`](https://www.php.net/manual/en/datetimeimmutable.modify.php) |
| Round and format a number to the nearest hundred | [Compare `number_format` results across versions](/practice/php-behaviour/#rounding-to-the-nearest-hundred) |

## Web responses

| I want to | Use this example |
| --- | --- |
| Return JSON from an HTTP endpoint | [Build and test the simple JSON API](/series/php-basics/chapters/23-working-with-json-and-apis/) |
| Read and validate form fields | [Process form input in PHP](/series/php-basics/chapters/05-handling-html-forms-and-user-input/) |
| Match a URL to a PHP handler | [Build a basic HTTP router](/series/php-basics/chapters/17-building-a-basic-http-router/) |

## Practise offline

- [PHP Basics offline exercises](/practice/php-basics-exercises/) gives you starter files, sample inputs, and solutions.
- [PHP debugging challenges](/practice/debugging-challenges/) lets you fix four short problems.
- [Printable PHP array reference](/practice/php-array-reference/) summarises common operations on one page.
- [Sample reading list dataset](/practice/sample-dataset/) gives you the same records in CSV, JSON, and SQLite.
- [PHP behaviour across versions](/practice/php-behaviour/) gives you three small examples to run on your own PHP installation.
