---
title: "PHP and SQLite Starter Project"
description: "Run a one-file PHP reading list with PDO SQLite. Learn how a form saves a book and how to keep the database outside the web root."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
---

Build a reading list you can run locally. Add a book, mark it as read, and restart the server to see that it is still there. The app uses a single PHP file and a SQLite database that it creates on your machine.

## What you need

Use PHP 8.6 or later with PDO SQLite. Check that PHP can see the driver:

```sh
php -v
php -r 'print_r(PDO::getAvailableDrivers());'
```

Look for `sqlite` in the list. The [troubleshooting guide](/series/php-basics/troubleshooting/#pdo-cannot-find-the-database-driver) covers missing drivers.

## Download and run

Save [the starter PHP file](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/php-sqlite-starter/public/index.php) as `php-sqlite-starter/public/index.php`, or use the commands below.

### macOS or Linux

```sh
mkdir -p php-sqlite-starter/public
curl -fL https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/php-sqlite-starter/public/index.php \
  -o php-sqlite-starter/public/index.php
cd php-sqlite-starter
php -S 127.0.0.1:8000 -t public
```

### Windows PowerShell

```powershell
New-Item -ItemType Directory -Force -Path 'php-sqlite-starter/public'
Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/php-sqlite-starter/public/index.php' -OutFile 'php-sqlite-starter/public/index.php'
Set-Location 'php-sqlite-starter'
php -S 127.0.0.1:8000 -t public
```

Open [the reading list at localhost](http://127.0.0.1:8000/) and add a book. PHP creates the SQLite file on the first request.

```text
php-sqlite-starter/
├── public/
│   └── index.php
└── storage/
    └── reading-list.sqlite
```

The `-t public` option makes `public/` the document root. The database stays in `storage/`, outside the directory the server serves. Use this development server on your own machine. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php)

## Follow one book through the code

1. **Open the page.** A GET request opens the SQLite file and runs a SELECT to show the saved books.
2. **Add a title.** The form sends a POST with a session token. PHP checks the token and title, then passes the title to an INSERT as a prepared-statement parameter.
3. **Read the result.** PHP sends a redirect back to the list. Refreshing that page repeats the GET and keeps your new book from being submitted again.
4. **Mark the book as read.** The second form sends the book ID. An UPDATE saves its status. Stop the server with **Ctrl+C**, start it again, and open the list to check that both changes persisted.

Titles are escaped when PHP renders them. Try a title containing `&` or quotes and check that it appears as typed.

When the starter is working, try adding an author field to the form and database. The [PDO lesson](/series/php-basics/chapters/14-interacting-with-databases-using-pdo/) explains the SQL calls, and the [sessions and cookies lesson](/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies/) shows where the form token is stored. [Browse the starter source](https://github.com/dalehurley/codewithphp/tree/main/code/php-sqlite-starter)
