---
title: "PHP vs Python"
description: "Choose between PHP and Python for websites, APIs and data work. Compare Laravel, Django and FastAPI, then run the same SQLite invoice report."
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-22"
sidebar:
  order: 1
---

Choose **PHP** when the project extends a WordPress site or a Laravel application, or when your team already knows how to build and deploy PHP web apps. Choose **Python** when the application depends on Python libraries for data processing, analytics or machine learning, or when your team already operates Python services. Both languages can build a website with accounts, forms, a database and an API.

[Run the invoice report](#run-the-same-sqlite-invoice-report) · [Choose what to learn first](#which-should-you-learn-first)

| If you need to | What to compare |
| --- | --- |
| Extend an existing site | Start with its framework, deployment process and existing tests. Reuse the code that already handles accounts and data. |
| Build a database-backed web app | Compare Laravel and Django on the same feature, including who will maintain it. Django’s model-based admin can get an internal workflow started quickly. |
| Ship an API around a Python library | Try FastAPI if the API needs little beyond validation and calling the library. Evaluate Django if the rest of the product also needs its models and admin. |
| Serve a site and run a data job | Use the web stack your team can operate and decide whether the job needs its own Python environment. Compare the cost of that boundary with doing the work inside the app. |

## PHP and Python for web development

**Laravel and Django** both provide tools for routing, working with database records, migrations and testing. A typical Laravel feature puts database behaviour in an Eloquent model and returns a view or JSON from a controller. A Django feature defines a model, routes a request to a view function or class, and can register the model in its admin. If staff need a quick way to manage records, try the Django admin on a representative model. If customers need a guided workflow, prototype that screen in each framework so you can compare how the forms, permissions and tests come together. [Laravel Eloquent](https://laravel.com/docs/13.x/eloquent) · [Django models](https://docs.djangoproject.com/en/stable/topics/db/models/) · [Django admin](https://docs.djangoproject.com/en/stable/ref/contrib/admin/)

**FastAPI** is a Python option for an API that can define its request and response models separately from an admin site. Its request validation uses type annotations and data models at the HTTP boundary. Laravel also has request validation and can return JSON from an existing app. Try one endpoint with a missing field, a bad value and a request from a user who lacks permission. That exercise reveals more about the day-to-day work than comparing two “hello world” handlers. [FastAPI request bodies](https://fastapi.tiangolo.com/tutorial/body/) · [Laravel validation](https://laravel.com/docs/13.x/validation)

If you are porting a Django feature, map one route, model, validation step and template before you commit to a port. For example, translate the Django model to an Eloquent model and the template to Blade, then agree on equivalent behaviour for an empty result, a rejected submission and an authorised edit. Use the same acceptance tests to compare the implementations. [Laravel Blade](https://laravel.com/docs/13.x/blade) · [Framework concepts for Python developers](/series/python-developers-love-php-laravel/chapters/01-mapping-concepts-python-web-frameworks-vs-laravel/)

## Language and development workflow

PHP’s arrays can hold an ordered list or an ordered map, so a list of invoice rows might be an array of associative arrays. Python commonly uses a list of dictionaries for the same shape. PHP checks declared parameter and return types when the program runs. A PHP call that passes the string `"1250"` to an `int` parameter can coerce it unless the calling file enables `strict_types`. In Python, an `int` annotation alone leaves the string unchanged. In both stacks, define whether you accept numeric strings and validate the input before you use it, even when a function declares its types. [PHP arrays](https://www.php.net/manual/en/language.types.array.php) · [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php) · [Python data structures](https://docs.python.org/3/tutorial/datastructures.html) · [Python typing](https://docs.python.org/3/library/typing.html)

In a PHP application, Composer installs dependencies and provides the autoloader. Commit the application's `composer.lock` so development and deployment install the same versions. In a Python project, create an isolated environment with `python3 -m venv .venv` and install the project’s declared dependencies using pip or the project’s package manager. Record resolved versions using the project's dependency workflow so a fresh checkout can reproduce the environment. In either language, put the main calculation behind a small function and use an integration test to check the database or HTTP boundary. [Composer installation workflow](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) · [Python virtual environments](https://docs.python.org/3/tutorial/venv.html)

## What you deploy and operate

A common PHP setup is a web server that passes requests to PHP-FPM, which manages PHP worker processes. A Django project can run under a WSGI or ASGI server. For a FastAPI project, plan for an ASGI server and decide how its processes restart and scale. In all three cases, compare the whole deployment you’ll maintain, including how you run migrations, collect logs and recover after a process exits. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) · [Django deployment](https://docs.djangoproject.com/en/stable/howto/deployment/) · [FastAPI deployment](https://fastapi.tiangolo.com/deployment/)

Take slow exports, email and data jobs out of the request path when users can collect the result later. Work through one retry and one failed job when comparing stacks. A second language can make the calculation easier if its libraries are a good fit, but you’ll also need to deploy, monitor and update a second runtime. For performance decisions, measure the same workload end to end with the production data shape and comparable resources.

## Run the same SQLite invoice report

A support team needs the total of paid invoices for one customer. Both scripts query the same file and bind a customer identifier as a parameter. They distinguish a known customer with no paid invoices from an identifier the customer list has never seen.

### Get the files and check your tools

Use PHP 8.4 or later with PDO SQLite, Python 3.9 or later with `sqlite3`, and the SQLite command-line tool. Use a supported release of each language for ongoing work. Download the three files or copy the code below into a new directory, then run the commands from that directory. [invoices.sql](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-python/invoices.sql) · [report.php](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-python/report.php) · [report.py](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-python/report.py)

```sh
php -v
php --ri pdo_sqlite
python3 -c "import sqlite3; print(sqlite3.sqlite_version)"
sqlite3 --version
```

The PHP driver command should show PDO SQLite information. The last two commands should print the SQLite versions used by your Python installation and the CLI. On Windows, use `py -3` in place of `python3` if that is how you launch Python. [PHP supported versions](https://www.php.net/supported-versions.php) · [Python release status](https://devguide.python.org/versions/) · [PHP PDO troubleshooting](/series/php-basics/troubleshooting/#pdo-cannot-find-the-database-driver)

### Create the fixture

Save this as `invoices.sql`. The fixture uses one currency and stores non-negative whole cents. SQLite's `INTEGER` affinity can still accept a fractional number, so the check verifies the value’s stored type and range. The foreign key ties invoices to the customer list. [SQLite storage types](https://www.sqlite.org/datatype3.html) · [SQLite foreign keys](https://www.sqlite.org/foreignkeys.html)

```sql title="invoices.sql"
PRAGMA foreign_keys = ON;

BEGIN;

CREATE TABLE customers (
    slug TEXT PRIMARY KEY NOT NULL
);

CREATE TABLE invoices (
    customer TEXT NOT NULL REFERENCES customers (slug),
    cents INTEGER NOT NULL CHECK (typeof(cents) = 'integer' AND cents >= 0),
    status TEXT NOT NULL CHECK (status IN ('paid', 'open'))
);

INSERT INTO customers (slug) VALUES ('north'), ('south'), ('west');

INSERT INTO invoices (customer, cents, status) VALUES
    ('north', 1250, 'paid'),
    ('north', 900, 'open'),
    ('north', 1200, 'paid'),
    ('south', 2000, 'paid'),
    ('west', 500, 'open');

COMMIT;
```

Create the database once in your example directory. The quoted `.read` command works in macOS and Linux shells and Windows PowerShell.

```sh
sqlite3 -bail invoices.sqlite ".read invoices.sql"
```

### PHP with PDO

Save as `report.php`. `Pdo\Sqlite` opens the existing file in read-only mode. [Pdo SQLite documentation](https://www.php.net/manual/en/class.pdo-sqlite.php)

```php title="report.php"
<?php
declare(strict_types=1);

use Pdo\Sqlite;

if ($argc !== 2 || trim($argv[1]) === '') {
    fwrite(STDERR, "Usage: php report.php CUSTOMER\n");
    exit(2);
}

$path = __DIR__ . '/invoices.sqlite';
if (!is_file($path)) {
    fwrite(STDERR, "Create invoices.sqlite from invoices.sql first.\n");
    exit(1);
}

try {
    $db = new Sqlite('sqlite:' . $path, options: [
        Sqlite::ATTR_OPEN_FLAGS => Sqlite::OPEN_READONLY,
    ]);
    $query = $db->prepare(<<<'SQL'
        SELECT COUNT(i.customer), COALESCE(SUM(i.cents), 0)
        FROM customers AS c
        LEFT JOIN invoices AS i
            ON i.customer = c.slug AND i.status = 'paid'
        WHERE c.slug = :customer
        GROUP BY c.slug
        SQL);
    $query->execute(['customer' => $argv[1]]);
    $row = $query->fetch(PDO::FETCH_NUM);
} catch (PDOException) {
    fwrite(STDERR, "Could not read the invoice database. Check that the fixture was loaded.\n");
    exit(1);
}

if ($row === false) {
    fwrite(STDERR, "Unknown customer.\n");
    exit(3);
}

[$count, $total] = $row;
printf("Paid invoices = %d, total = %d cents\n", $count, $total);
```

### Python with sqlite3

Save as `report.py`. SQLite’s URI option `mode=ro` opens the same file in read-only mode. [Python sqlite3 URIs](https://docs.python.org/3/library/sqlite3.html#how-to-work-with-sqlite-uris)

```python title="report.py"
from contextlib import closing
from pathlib import Path
import sqlite3
import sys

if len(sys.argv) != 2 or not sys.argv[1].strip():
    print("Usage: python3 report.py CUSTOMER", file=sys.stderr)
    raise SystemExit(2)

path = Path(__file__).with_name("invoices.sqlite")
if not path.is_file():
    print("Create invoices.sqlite from invoices.sql first.", file=sys.stderr)
    raise SystemExit(1)

try:
    database_uri = path.resolve().as_uri() + "?mode=ro"
    with closing(sqlite3.connect(database_uri, uri=True)) as db:
        row = db.execute(
            """
            SELECT COUNT(i.customer), COALESCE(SUM(i.cents), 0)
            FROM customers AS c
            LEFT JOIN invoices AS i
                ON i.customer = c.slug AND i.status = 'paid'
            WHERE c.slug = :customer
            GROUP BY c.slug
            """,
            {"customer": sys.argv[1]},
        ).fetchone()
except sqlite3.Error:
    print("Could not read the invoice database. Check that the fixture was loaded.", file=sys.stderr)
    raise SystemExit(1)

if row is None:
    print("Unknown customer.", file=sys.stderr)
    raise SystemExit(3)

count, total = row
print(f"Paid invoices = {count}, total = {total} cents")
```

### Check the output and exit codes

```sh
php report.php north
python3 report.py north
php report.php west
python3 report.py west
php report.php unknown
python3 report.py unknown
```

| Customer | Result in both versions | Exit code |
| --- | --- | --- |
| `north` | `Paid invoices = 2, total = 2450 cents` | `0` |
| `west` | `Paid invoices = 0, total = 0 cents` | `0` |
| `unknown` | `Unknown customer.` on standard error | `3` |

Missing arguments print usage and exit with `2`. A missing or unreadable database prints a fixture message and exits with `1`. If you load the fixture a second time into the same file, SQLite reports that the tables already exist. Start in a fresh example directory to recreate it.

The query starts at the customer list and joins only paid invoices. Putting the paid condition in the `LEFT JOIN` preserves a row for `west` even though none of its invoices qualify. `COUNT(i.customer)` skips the empty join value and `COALESCE` returns zero for the empty sum. An unknown customer produces no grouped row, which PDO reports as `false` and Python’s `fetchone()` reports as `None`. Both scripts keep the same query and output contract, so you can run the same checks after a port. [SQLite SELECT and joins](https://www.sqlite.org/lang_select.html) · [SQLite aggregate functions](https://www.sqlite.org/lang_aggfunc.html)

To show this report on a website, check that the signed-in user can view the requested customer before running the query. Keep the report’s SQL separate from the page or endpoint so you can reuse the same fixture in an integration test. Add a currency field and filter or group by it before using this design with amounts in more than one currency. [Build the PHP and SQLite starter](/series/php-basics/sqlite-starter/)

## When using both makes sense

Imagine a Laravel site that already handles customer accounts and needs a scheduled forecast built on a pandas workflow. Keep the site in PHP and have the Python job produce a dated report with a documented schema. Let the application read the last successful result and show when it was generated. Decide how the job retries and how the site behaves when its result is late. If the task is the SQL summary shown above, one app and a database query are enough. [pandas overview](https://pandas.pydata.org/docs/getting_started/overview.html)

## Which should you learn first?

Start with PHP if your first project is a WordPress extension or a server-rendered Laravel feature. Start with Python if your first deliverable is a data import, a report or an API around a Python library. To compare the web stacks as a beginner, take the same small feature from form through validation and into the database in each. Use the project’s first deliverable to decide which tutorial to finish first. [PHP and Laravel for Python developers](/series/python-developers-love-php-laravel/) · [PHP vs Ruby](/compare/php-vs-ruby/) · [PHP vs SQL](/compare/php-vs-sql/) · [All PHP comparisons](/compare/)
