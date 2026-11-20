# PHP and SQLite starter

A small reading list built with PHP and PDO SQLite. Add a book, mark it as read, then restart the server to see that the list survives.

From this directory, run the PHP development server:

```sh
php -S 127.0.0.1:8000 -t public
```

Open [the local reading list](http://127.0.0.1:8000/). PHP creates `storage/reading-list.sqlite` on the first request. The `public` directory serves the app and keeps the database file outside the document root.

PHP 8.4 or later and the PDO SQLite extension are the requirements. The [starter guide](https://codewithphp.com/series/php-basics/sqlite-starter/) has single-file download steps for macOS, Linux, and Windows, plus a walkthrough of the code.
