---
title: "Learn PHP in Seven Days"
description: "Follow seven short PHP exercises from a browser-based first script to a small SQLite reading list that saves your books."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
prev: false
next: false
---

Spend about 20–30 minutes on a task each day. You will start with a single line of PHP and finish by adapting a small reading list that remembers its books between visits.

## Day 1 — Change a greeting

Open the browser example in [Write and Run Your First PHP Script](/series/php-basics/chapters/01-your-first-php-script/). Change the words in the quoted greeting and run the example. You are ready to continue when you see your new output.

## Day 2 — Run a file on your machine

Follow [the PHP setup lesson](/series/php-basics/chapters/00-setting-up-your-development-environment/) for your operating system. Save your greeting in `hello.php` and run `php hello.php` in the terminal from the folder where you saved it. Compare the result with yesterday's browser output.

## Day 3 — Put several books in an array

Start a new file named `books.php`. Put your own titles in the array and run `php books.php`.

```php
<?php

$books = ['Dune', 'Kindred', 'The Hobbit'];

foreach ($books as $book) {
    echo $book, PHP_EOL;
}
```

Expect one title on each line. Add a fourth title and check that it appears. [PHP arrays](/series/php-basics/chapters/06-deep-dive-into-arrays/)

## Day 4 — Read an HTML form

Follow [PHP for HTML and CSS developers](/learn/php-for-html-css/) to make a form that greets you. A working page reads **Hello, Sam!** after you submit `Sam`. Change the heading and button text to see which part of the page HTML controls.

## Day 5 — Check and display a value

In your greeting page, submit a value containing spaces around the name and check that `trim()` removes the extra spaces. Then submit `A & B`. The browser should display the ampersand, and the page source will show it escaped as `&amp;`. Point to the line that reads the input and the separate line that formats it for HTML. [Handling form input](/series/php-basics/chapters/05-handling-html-forms-and-user-input/)

## Day 6 — Save a book in SQLite

Follow the [PHP and SQLite starter](/series/php-basics/sqlite-starter/) in a fresh folder. Add one or more of your own titles. Mark one as read. Stop and restart the local server, then confirm that the titles and read status are still there.

## Day 7 — Make the reading list your own

To see the change on a fresh list, add `Dune` first and `Kindred` second. The original query shows the newer title first. Open `public/index.php` in the reading list project and find the query containing `ORDER BY id DESC`. Change that part of the query to `ORDER BY title COLLATE NOCASE, id DESC`. Reload the list and verify that **Dune** appears before **Kindred** regardless of when you added them.

Add another title, mark it as read, and refresh the page. Check that the new book is in alphabetical order and its read status persists. [Learn how the query works](/series/php-basics/chapters/14-interacting-with-databases-using-pdo/) · [Continue with PHP Basics](/series/php-basics/)
