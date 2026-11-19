# Quote SQL String Literals with MySQLi

Quote a name containing an apostrophe and round-trip it through a SELECT. Use the new API when building a string literal for SQL generation where binding parameters is unavailable.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/22-mysqli-quote-string/mysqli-quote-string.php
```

Requirements: The `mysqli` extension and a reachable local MySQL or MariaDB server. Set `MYSQL_USER` for an account with permission to connect. Optionally set `MYSQL_HOST` (default `127.0.0.1`), `MYSQL_PORT` (default `3306`), and `MYSQL_PASSWORD` for that account.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/22-mysqli-quote-string/).
