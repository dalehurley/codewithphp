---
title: "PHP vs Python"
description: "Choose between PHP and Python for websites, APIs and data work. Compare Laravel, Django and FastAPI, then run the same SQLite invoice report."
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-21"
sidebar:
  order: 1
---

Choose **PHP** when the project extends a WordPress site or a Laravel application, or when your team already knows how to build and deploy PHP web apps. Choose **Python** when the application depends on Python libraries for data processing, analytics or machine learning, or when your team already operates Python services. Both languages can build a website with accounts, forms, a database and an API.

| If you need to | What to compare |
| --- | --- |
| Extend an existing site | Start with its framework, deployment process and existing tests. Reuse the code that already handles accounts and data. |
| Build a database-backed web app | Compare Laravel and Django on the same feature, including who will maintain it. Django’s model-based admin can get an internal workflow started quickly. |
| Ship an API around a Python library | Try FastAPI if the API needs little beyond validation and calling the library. Evaluate Django if the rest of the product also needs its models and admin. |
| Serve a site and run a data job | Use the web stack your team can operate and decide whether the job needs its own Python environment. Compare the cost of that boundary with doing the work inside the app. |

## PHP and Python for web development

**Laravel and Django** both provide tools for routing, working with database records, migrations and testing. A typical Laravel feature puts database behaviour in an Eloquent model and returns a view or JSON from a controller. A Django feature defines a model, routes a request to a view function or class, and can register the model in its admin. If staff need a quick way to manage records, try the Django admin on a representative model. If customers need a guided workflow, prototype that screen in each framework so you can compare how the forms, permissions and tests come together. [Laravel Eloquent](https://laravel.com/docs/13.x/eloquent) · [Django models](https://docs.djangoproject.com/en/stable/topics/db/models/) · [Django admin](https://docs.djangoproject.com/en/stable/ref/contrib/admin/)

**FastAPI** is a Python option for an API that can define its request and response models separately from an admin site. Its request validation uses type annotations and data models at the HTTP boundary. Laravel also has request validation and can return JSON from an existing app. Try one endpoint with a missing field, a bad value and a request from a user who lacks permission. That exercise reveals more about the day-to-day work than comparing two “hello world” handlers. [FastAPI request bodies](https://fastapi.tiangolo.com/tutorial/body/) · [Laravel validation](https://laravel.com/docs/13.x/validation)

## Language and development workflow

PHP’s arrays can hold an ordered list or an ordered map, so a list of invoice rows might be an array of associative arrays. Python commonly uses a list of dictionaries for the same shape. PHP checks declared parameter and return types when the program runs, with scalar coercion in some contexts. Python type hints support editors and static checkers, while runtime input validation is separate. In both stacks, validate request data before using it to make a business decision. [PHP arrays](https://www.php.net/manual/en/language.types.array.php) · [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php) · [Python data structures](https://docs.python.org/3/tutorial/datastructures.html) · [Python typing](https://docs.python.org/3/library/typing.html)

In a PHP application, Composer installs dependencies and provides the autoloader. Commit the application's `composer.lock` so development and deployment install the same versions. In a Python project, create an isolated environment with `python3 -m venv .venv` and install the project’s declared dependencies using pip or the project’s package manager. Record resolved versions using the project's dependency workflow so a fresh checkout can reproduce the environment. In either language, put the main calculation behind a small function and use an integration test to check the database or HTTP boundary. [Composer installation workflow](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) · [Python virtual environments](https://docs.python.org/3/tutorial/venv.html)

## What you deploy and operate

A common PHP setup is a web server that passes requests to PHP-FPM, which manages PHP worker processes. A Django project can run under a WSGI or ASGI server. For a FastAPI project, plan for an ASGI server and decide how its processes restart and scale. In all three cases, compare the whole deployment you’ll maintain, including how you run migrations, collect logs and recover after a process exits. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) · [Django deployment](https://docs.djangoproject.com/en/stable/howto/deployment/) · [FastAPI deployment](https://fastapi.tiangolo.com/deployment/)

Take slow exports, email and data jobs out of the request path when users can collect the result later. Work through one retry and one failed job when comparing stacks. A second language can make the calculation easier if its libraries are a good fit, but you’ll also need to deploy, monitor and update a second runtime. For performance decisions, measure the same workload end to end with the production data shape and comparable resources.

## Run the same SQLite invoice report

A support team needs the total of paid invoices for one customer. Both scripts query the same file, bind the customer identifier as a parameter, and format the same result. The fixture and commands work with PHP 8.0 or later with PDO SQLite, Python 3.9 or later with `sqlite3`, and the SQLite command-line tool. Create the three files below in a new directory. [PHP PDO SQLite](https://www.php.net/manual/en/ref.pdo-sqlite.php) · [Python sqlite3](https://docs.python.org/3/library/sqlite3.html)

### Create the fixture

Save as `invoices.sql` and run `sqlite3 invoices.sqlite < invoices.sql`.

```sql
CREATE TABLE invoices (
    customer TEXT NOT NULL,
    cents INTEGER NOT NULL,
    status TEXT NOT NULL CHECK (status IN ('paid', 'open'))
);

INSERT INTO invoices (customer, cents, status) VALUES
    ('north', 1250, 'paid'),
    ('north', 900, 'open'),
    ('north', 1200, 'paid'),
    ('south', 2000, 'paid'),
    ('west', 500, 'open');
```

### PHP with PDO

Save as `report.php`.

```php
<?php
declare(strict_types=1);

if ($argc !== 2 || trim($argv[1]) === '') {
    fwrite(STDERR, "Usage: php report.php CUSTOMER\n");
    exit(2);
}

$path = __DIR__ . '/invoices.sqlite';
if (!is_file($path)) {
    fwrite(STDERR, "Create invoices.sqlite from invoices.sql first.\n");
    exit(1);
}

$db = new PDO('sqlite:' . $path);
$query = $db->prepare(<<<'SQL'
    SELECT COUNT(*), COALESCE(SUM(cents), 0)
    FROM invoices
    WHERE customer = :customer AND status = 'paid'
    SQL);
$query->execute(['customer' => $argv[1]]);
[$count, $total] = $query->fetch(PDO::FETCH_NUM);

printf("Paid invoices = %d, total = %d cents\n", $count, $total);
```

### Python with sqlite3

Save as `report.py`.

```python
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

with closing(sqlite3.connect(path)) as db:
    count, total = db.execute(
        """
        SELECT COUNT(*), COALESCE(SUM(cents), 0)
        FROM invoices
        WHERE customer = :customer AND status = 'paid'
        """,
        {"customer": sys.argv[1]},
    ).fetchone()

print(f"Paid invoices = {count}, total = {total} cents")
```

### Check the output

```sh
php report.php north
python3 report.py north
php report.php west
python3 report.py west
```

```text
Paid invoices = 2, total = 2450 cents
Paid invoices = 2, total = 2450 cents
Paid invoices = 0, total = 0 cents
Paid invoices = 0, total = 0 cents
```

SQLite does the filtering, counting and summation in both versions. It returns a row even when the filter matches zero invoices. `COUNT(*)` is then zero and `COALESCE` turns the empty sum into zero. The amounts stay in integer cents throughout. PHP fetches the two columns into an indexed array while Python unpacks a tuple. Reusing the query and fixture lets you check that a port preserves how open invoices and empty results are handled. [SQLite aggregate functions](https://www.sqlite.org/lang_aggfunc.html)

To show this report on a website, check that the signed-in user can view the requested customer before running the query. Keep the report’s SQL separate from the page or endpoint so you can reuse the same fixture in an integration test. Add a currency field and filter or group by it before using this design with amounts in more than one currency. [Build the PHP and SQLite starter](/series/php-basics/sqlite-starter/)

## When using both makes sense

Imagine a Laravel site that already handles customer accounts and needs a scheduled forecast built on a pandas workflow. Keep the site in PHP and have the Python job produce a dated report with a documented schema. Let the application read the last successful result and show when it was generated. Decide how the job retries and how the site behaves when its result is late. If the task is the SQL summary shown above, one app and a database query are enough. [pandas overview](https://pandas.pydata.org/docs/getting_started/overview.html)

If you’re coming from Python, follow the guide to mapping familiar frameworks to Laravel. For another comparison, see how Ruby’s web ecosystem fits a similar project or how PHP and SQL divide application and database work. [PHP and Laravel for Python developers](/series/python-developers-love-php-laravel/) · [PHP vs Ruby](/compare/php-vs-ruby/) · [PHP vs SQL](/compare/php-vs-sql/) · [All PHP comparisons](/compare/)
