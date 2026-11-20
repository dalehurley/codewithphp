---
title: "Free Sample Dataset for PHP in CSV, JSON, and SQLite"
description: "Download the same eight fictional books as CSV, JSON, or SQLite to practise importing rows, validating fields, grouping records, and querying data in PHP."
prev: false
next: false
---

Use this fictional reading list to practise working with data in PHP. All three downloads contain the same eight rows. The dataset is small enough that you can check a query by hand.

- <a href="/downloads/practice/library-books.csv" download>Download the CSV</a>
- <a href="/downloads/practice/library-books.json" download>Download the JSON</a>
- <a href="/downloads/practice/library-books.sqlite" download>Download the SQLite database</a>

## Fields to expect

| Field | Meaning |
| --- | --- |
| `id` | Unique positive integer |
| `title` | Book title in UTF-8 |
| `category` | `fiction`, `science`, or `cooking` |
| `pages` | Positive integer |
| `status` | `queued`, `reading`, or `finished` |
| `finished_on` | Date in `YYYY-MM-DD` form. Blank in CSV and `null` in JSON and SQLite if the book is unfinished. |

The first title contains commas within one quoted CSV cell. The second contains an accented character. Use these rows to check your parser before you move on to a larger dataset. CSV fields arrive as strings. JSON and SQLite store `id` and `pages` as integers.

## Read the CSV in PHP

Save the CSV and this code in the same folder. Save the PHP file as `read-csv.php` and run `php read-csv.php`. The first line should show the whole title `Stars, Maps, and Tides`.

```php
<?php

$file = fopen(__DIR__ . '/library-books.csv', 'rb');
if ($file === false) {
    exit("Could not open library-books.csv.\n");
}

fgetcsv($file, escape: ''); // Skip the header.
while (($row = fgetcsv($file, escape: '')) !== false) {
    echo $row[1], PHP_EOL;
}
fclose($file);
```

Look up how to set a delimiter or escape character in the PHP manual. [PHP `fgetcsv` documentation](https://www.php.net/manual/en/function.fgetcsv.php)

## Query the SQLite file with PDO

Save the SQLite file in the same folder as `count-finished.php`. Run `php count-finished.php`. This example requires PDO SQLite and should print `4`.

```php
<?php

$path = __DIR__ . '/library-books.sqlite';
if (!is_file($path)) {
    exit("Download library-books.sqlite first.\n");
}

$db = new PDO('sqlite:' . $path);
$query = $db->prepare('SELECT COUNT(*) FROM books WHERE status = :status');
$query->execute(['status' => 'finished']);
echo $query->fetchColumn(), PHP_EOL;
```

For the JSON version, read the file and decode it with `json_decode($contents, true, 512, JSON_THROW_ON_ERROR)`. Walk through a complete example in the [JSON and APIs lesson](/series/php-basics/chapters/23-working-with-json-and-apis/).

## Check your result

- Count books by category. Expect 3 fiction, 3 science, and 2 cooking.
- Sum the page counts for finished books. Expect 640.
- Order the finished books by date, newest first. Expect `Rocks After Rain` at the top.
- Make a copy of the CSV and set a page count to zero. Write a check that reports the corresponding row before accepting it.

The dataset is fictional and available under the [project's MIT licence](https://github.com/dalehurley/codewithphp/blob/main/LICENSE).
