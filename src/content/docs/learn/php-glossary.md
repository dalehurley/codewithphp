---
title: "PHP Glossary with Examples"
description: "Look up common PHP terms in plain English with short examples and links to lessons on arrays, autoloading, traits, PDO and more."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
prev: false
next: false
---

Look up a term you met in a PHP lesson or project. The examples show what the term looks like in code, and the linked lesson puts it to work.

## Values and functions

| Term | What it means in PHP | Learn by doing |
| --- | --- | --- |
| Variable | A name for a value. In `$quantity = 3;` the name is `$quantity`. | [Variables and data types](/series/php-basics/chapters/02-variables-data-types-and-constants/) |
| Associative array | An array used to look up values by key, such as `['title' => 'Dune']`. Read the title with `$book['title']`. | [Arrays in PHP](/series/php-basics/chapters/06-deep-dive-into-arrays/) |
| Parameter | A named input in a function's definition. In `function double(int $n): int { return $n * 2; }` the parameter is `$n` and the value in `double(3)` is an argument. | [Functions and return values](/series/php-basics/chapters/04-understanding-and-using-functions/) |
| Type declaration | A type PHP checks at a boundary, such as `int $n` for a parameter or `: int` for a return value. | [PHP typing in practice](/series/php-misconceptions/chapters/01-php-types/) |
| Closure | An anonymous function you can assign to a variable or pass around. `$double = fn (int $n): int => $n * 2;` stores one in `$double`. | [Anonymous functions and arrow functions](/series/php-basics/chapters/04-understanding-and-using-functions/) |
| Exception | An object that reports an error or an unexpected condition. `throw new RuntimeException('File unavailable');` signals one. | [Errors and exceptions](/series/php-basics/chapters/11-error-and-exception-handling/) |

## Classes and packages

| Term | What it means in PHP | Learn by doing |
| --- | --- | --- |
| Class | A definition of an object's properties and methods. `new DateTimeImmutable('2026-11-20')` builds an object from the `DateTimeImmutable` class. | [Classes and objects](/series/php-basics/chapters/08-introduction-to-object-oriented-programming/) |
| Interface | A contract describing the public methods a class supplies. In `class Report implements JsonSerializable`, the class must supply `jsonSerialize()`. | [Interfaces and inheritance](/series/php-basics/chapters/09-oop-inheritance-abstract-classes-and-interfaces/) |
| Trait | A reusable group of methods and properties that a class can include with a declaration such as `use Timestamps;` inside its class body. | [Traits and namespaces](/series/php-basics/chapters/10-oop-traits-and-namespaces/) |
| Namespace | A name prefix for a group of definitions. `App\Reports\Summary` is a different full class name from `App\Billing\Summary`. | [Traits and namespaces](/series/php-basics/chapters/10-oop-traits-and-namespaces/) |
| Dependency injection | Passing an object what it needs to do its work. In `new BookRepository($pdo)`, the repository receives a database connection from its caller. | [Follow dependencies through a Laravel feature](/series/php-misconceptions/chapters/04-clean-laravel-code/) |
| Composer | PHP's dependency manager. `composer install` installs the versions recorded in `composer.lock` when that file is present. | [Composer and dependencies](/series/php-basics/chapters/12-dependency-management-with-composer/) |
| Autoloading | Loading a class file when its class is first needed. An app can load Composer's autoloader with `require __DIR__ . '/vendor/autoload.php';` from the project root. | [Use Composer's autoloader](/series/php-basics/chapters/12-dependency-management-with-composer/) |

## Web applications and data

| Term | What it means in PHP | Learn by doing |
| --- | --- | --- |
| Superglobal | A built-in variable accessible inside functions as well as at the top level. `$_GET['name'] ?? ''` reads a query parameter with a default. | [Read a form submission](/learn/php-for-html-css/) |
| Request and response | A browser sends a request such as `GET /about`. The server responds with a status, headers, and often a body such as a page. | [Follow a PHP request](/learn/read-a-php-codebase/) |
| Router | Code that chooses what runs for an HTTP method and URL path. In the router lesson, `$router->get('/about', $handler)` registers a handler. | [Build a basic HTTP router](/series/php-basics/chapters/17-building-a-basic-http-router/) |
| Session | Data that spans requests, typically linked by an ID in a cookie. After `session_start()`, the app can use `$_SESSION` to read or write that data. | [Sessions and cookies](/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies/) |
| Extension | An optional part of PHP that adds features. `extension_loaded('pdo_sqlite')` checks whether the SQLite PDO driver is available to the current process. | [Compare PHP versions and extensions](/learn/local-vs-hosted-php/) |
| PDO | A common PHP API for accessing databases through drivers. `new PDO('sqlite::memory:')` uses PDO SQLite to open a database in memory. | [Query a database with PDO](/series/php-basics/chapters/14-interacting-with-databases-using-pdo/) |
| Prepared statement | A database statement whose placeholders receive values separately. A statement like `SELECT title FROM books WHERE id = :id` receives a value for `id` at execution. | [See a prepared statement in the reading list](/series/php-basics/sqlite-starter/) |
| Escaping | Preparing text for a particular output context. For HTML text, `htmlspecialchars('A & B')` produces `A &amp; B`. | [Render safe text in a PHP page](/learn/php-for-html-css/) |
| SAPI | The interface between PHP and the program running it. `PHP_SAPI` can report `cli` for a terminal or `fpm-fcgi` for PHP-FPM. | [Compare local and hosted PHP](/learn/local-vs-hosted-php/) |
