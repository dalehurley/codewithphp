# Replace Deprecated MySQLi Charset and Statement Calls

Check the connection charset and run a bound query without creating an empty statement. PHP 8.6 deprecates `get_charset()`, `stmt_init()`, their procedural counterparts and constructing `mysqli_stmt` without SQL.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/48-mysqli-charset-and-statements/mysqli-charset-and-statements.php
```

Requirements: The `mysqli` extension and a reachable local MySQL or MariaDB server. Set `MYSQL_USER` for an account with permission to connect. Optionally set `MYSQL_HOST` (default `127.0.0.1`), `MYSQL_PORT` (default `3306`), and `MYSQL_PASSWORD` for that account.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/48-mysqli-charset-and-statements/).
