---
title: "PHP Troubleshooting Guide for Common Errors"
description: "Find the PHP error you saw and work through a short fix for syntax errors, Composer classes, missing SQLite drivers, redirects, and local server problems."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
---

Start with the first error in your terminal or the PHP development server log. It usually names the file and line where PHP noticed the problem. If the page is blank, check that log and run `php -l public/index.php` from your project folder, using your own entry file if it has a different name.

| What you see | Where to look |
| --- | --- |
| `php: command not found` or `'php' is not recognized` | [PHP command not found](#php-command-not-found) |
| `Could not open input file` | [PHP cannot find the script](#php-cannot-find-the-script) |
| `Parse error: syntax error` | [PHP cannot parse the code](#php-cannot-parse-the-code) |
| `Undefined array key` | [A value is missing from an array](#a-value-is-missing-from-an-array) |
| `Class "…" not found` | [PHP cannot load a class](#php-cannot-load-a-class) |
| `could not find driver` | [PDO cannot find the database driver](#pdo-cannot-find-the-database-driver) |
| `unable to open database file` | [SQLite cannot open the file](#sqlite-cannot-open-the-file) |
| `headers already sent` | [PHP sent output before a header](#php-sent-output-before-a-header) |
| `Address already in use` | [The web server port is busy](#the-web-server-port-is-busy) |

## PHP command not found

Your terminal needs to find the PHP executable. Check it with `command -v php` on macOS or Linux, or `Get-Command php` in PowerShell. An installation can exist on disk even when the terminal cannot find it.

Follow the [installation steps for your operating system](/series/php-basics/chapters/00-setting-up-your-development-environment/), open a fresh terminal, and run `php -v`. If it shows a different version from the one you installed, use the command above to see which executable your terminal chose.

## PHP cannot find the script

`php hello.php` looks for that file in your current folder. Run `pwd` and `ls` on macOS or Linux, or `Get-Location` and `Get-ChildItem` in PowerShell. Compare both the folder and filename with what your editor shows. Then change to the folder containing the script and run it again. [First PHP script](/series/php-basics/chapters/01-your-first-php-script/)

## PHP cannot parse the code

Run `php -l hello.php` to check the file without executing it. Read the reported line and the line just before it. In this example, PHP flags the second `echo` because the first statement is missing its semicolon.

```php
<?php

echo 'Hello'
echo ' again', PHP_EOL;
```

Add the semicolon after `'Hello'`. Run `php -l hello.php` again and look for `No syntax errors detected`. When the error appears after a version upgrade or downgrade, check that your PHP version supports the syntax on that line. [Error and exception handling](/series/php-basics/chapters/11-error-and-exception-handling/)

## A value is missing from an array

PHP warns about `Undefined array key "name"` when code reads a key that is absent. Inspect the actual keys with `print_r(array_keys($profile))` in local development. For optional inputs, supply a default. For required inputs, validate them and tell the reader what to provide.

```php
<?php

$profile = ['email' => 'reader@example.test'];
$name = $profile['name'] ?? 'Guest';

echo $name, PHP_EOL;
```

This prints `Guest`. The default also applies when the key exists and its value is `null`. [PHP arrays](/series/php-basics/chapters/06-deep-dive-into-arrays/)

## PHP cannot load a class

Compare the class name in the error with the declaration, namespace, and filename. Check letter case, especially if the project moved from macOS or Windows to a case-sensitive Linux filesystem.

If the project uses Composer, run `composer install` from the folder containing `composer.json`. Load the generated autoloader before referring to a dependency. For an entry file alongside `composer.json`, that is:

```php
require __DIR__ . '/vendor/autoload.php';
```

If the class is your own, check that its namespace matches the project's Composer autoload mapping. [Composer and autoloading lesson](/series/php-basics/chapters/12-dependency-management-with-composer/) · [Composer autoloading documentation](https://getcomposer.org/doc/01-basic-usage.md#autoloading)

## PDO cannot find the database driver

PDO needs a driver for the connection prefix. For `sqlite:`, the extension is PDO SQLite. Check both the PHP configuration and the available PDO drivers:

```sh
php --ini
php -r 'print_r(PDO::getAvailableDrivers());'
```

If `sqlite` is missing, enable or install PDO SQLite for the PHP version that runs the application. Restart the local server, then repeat the driver check. If the command reports that `PDO` itself is missing, enable PDO first. A separately managed web server can use a different PHP version or configuration from the terminal. [PDO drivers](https://www.php.net/manual/en/pdo.drivers.php) · [PDO lesson](/series/php-basics/chapters/14-interacting-with-databases-using-pdo/)

## SQLite cannot open the file

SQLite can create a database file, but its parent folder must already exist and be writable by the process running PHP. Check the path in your `sqlite:` connection string. Build it from `__DIR__` or `dirname(__DIR__)` so it points to the same place when you launch PHP from a different folder.

For the starter, the project folder needs to be writable so PHP can create `storage/reading-list.sqlite`. The database lives alongside `public/` so the development server serves the app from its own document root. [PHP and SQLite starter](/series/php-basics/sqlite-starter/)

## PHP sent output before a header

When you see `Cannot modify header information - headers already sent`, read the `output started at` location in the warning first. Common sources are an `echo`, HTML above PHP code, whitespace in an included file, or an earlier warning rendered into the response.

Handle a form submission and send its redirect before rendering the page. In this pattern, the normal GET request continues to the page output:

```php
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and save the form here.
    header('Location: /', true, 303);
    exit;
}

echo 'Ready to accept a form.';
```

If whitespace came from a file that contains only PHP, leave its closing `?>` tag out. [PHP header documentation](https://www.php.net/manual/en/function.header.php)

## The web server port is busy

The PHP development server can report `Address already in use` when another program is listening on the same port. Stop the previous server with **Ctrl+C** in its terminal, or choose another port:

```sh
php -S 127.0.0.1:8001 -t public
```

Open [port 8001](http://127.0.0.1:8001/) in your browser. If your lesson keeps its entry file in the project root, use the document-root command shown in that lesson. [PHP development server documentation](https://www.php.net/manual/en/features.commandline.webserver.php)
