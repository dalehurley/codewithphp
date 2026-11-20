---
title: "Printable PHP Array Reference"
description: "Print a one-page PHP array reference for lookup, filtering, mapping, sorting, reindexing, and extracting a column."
prev: false
next: false
---

[Open the one-page PHP array sheet](/downloads/practice/php-array-reference.html) and print it from your browser. Choose **Print** or use **Ctrl+P** on Windows and Linux or **Command+P** on a Mac. Use the default scale on A4 or US Letter paper. To keep a copy offline, select **Save as PDF** in the print dialogue.

The sheet is for work with PHP lists and associative arrays. It shows which operations keep the original keys and what to change if a filtered list needs to become a JSON array.

## Try it with a real input shape

Save the [sample JSON dataset](/downloads/practice/library-books.json) next to your PHP script. Decode the downloaded text as an associative array, then extract the titles:

```php
<?php

$contents = file_get_contents(__DIR__ . '/library-books.json');
if ($contents === false) {
    exit("Download library-books.json first.\n");
}

$books = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
$titles = array_column($books, 'title');

echo count($titles), PHP_EOL; // 8
echo $titles[0], PHP_EOL;     // Stars, Maps, and Tides
```

For more examples, follow the [PHP arrays lesson](/series/php-basics/chapters/06-deep-dive-into-arrays/) or look for a specific task in the [examples index](/practice/).
