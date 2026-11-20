---
title: "PHP vs SQL"
description: "See what PHP does in an application and what SQL does in a database. Group the same data in PHP and in SQLite."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 12
---

PHP runs the application. SQL asks a relational database to read or change its data. In a PHP web app, PHP can check who may see a sales report, a SQL query can calculate the totals, and PHP can format those totals for the reader.

| Task | PHP | SQL |
| --- | --- | --- |
| Handle a web request | Read input, apply permissions, and build the response. | Run statements that the application sends to the database. |
| Group data | Use a loop for records already in application memory. | Use `GROUP BY` to group records where they are stored. |
| Choose output order | Explicitly sort lists or map entries before presenting them. | Use `ORDER BY` for a predictable row order. |
| Work with input | Validate a value and pass it using a bound parameter. | Receive the parameter as data when the database executes the prepared statement. |

## Count tickets by status

```php
<?php
$statuses = ['open', 'closed', 'open'];
$totals = array_count_values($statuses);
ksort($totals);

foreach ($totals as $status => $total) {
    printf("%s|%d\n", $status, $total);
}
```

```sql
WITH tickets(status) AS (
    VALUES ('open'), ('closed'), ('open')
)
SELECT status, COUNT(*) AS total
FROM tickets
GROUP BY status
ORDER BY status;
```

Run the PHP file with `php tickets.php` and the SQL file with `sqlite3 :memory: < tickets.sql`. With SQLite's default list output format, both print `closed|1` and then `open|2`. The SQL statement expresses the grouping directly. For a large table already in the database, grouping there can send fewer rows back to PHP.

## Use both for a small project

Store tickets in SQLite and use SQL to query or group them. Keep the request, validation and display in PHP. Learn PDO prepared statements as soon as the database query starts receiving values from a form. Keep data validation and access checks even when you use a framework's query builder.

- [PHP PDO with databases](/series/php-basics/chapters/14-interacting-with-databases-using-pdo/)
- [Runnable PHP and SQLite starter](/series/php-basics/sqlite-starter/)
- [SQLite SELECT documentation](https://sqlite.org/lang_select.html)
- [PHP prepared statements](https://www.php.net/manual/en/pdo.prepared-statements.php) · [All PHP comparisons](/compare/)
