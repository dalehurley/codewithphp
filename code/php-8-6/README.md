# What’s New in PHP 8.6

Each numbered directory contains the runnable example for the corresponding chapter. See the [series overview](../../src/content/docs/series/php-8-6/index.md) for the full chapter list, prerequisites, and exercises.

Run examples from the repository root with a PHP 8.6 CLI.

```bash
php --version
php code/php-8-6/01-partial-function-application/partial-function-application.php
```

## Compare PHP 8.5 and 8.6

`upgrade/compare-runtime.php` compares trimming, argument validation, and session defaults on the two PHP runtimes. Run it with each PHP executable. The `-n` flag compares compiled defaults.

```bash
/path/to/php8.5 -n code/php-8-6/upgrade/compare-runtime.php
/path/to/php8.6 -n code/php-8-6/upgrade/compare-runtime.php
```

See the [PHP 8.5 to 8.6 upgrade checklist](https://codewithphp.com/series/php-8-6/upgrade-checklist/) for expected output and rollout checks.

## Check the examples

Use a PHP 8.6 build with `intl`, `mbstring`, `openssl`, `session`, `sodium`, `uri`, `zip`, and `zlib`. The Sodium labs need libsodium 1.0.22 or later. The loopback TLS labs use `proc_open()`, and the certificate-based labs also use the `openssl` command. The polling lab uses a Unix-domain socket pair.

The runner lints each example, executes it, and checks for runtime diagnostics.

```bash
bash code/php-8-6/run-examples.sh

# Select a specific PHP 8.6 executable if needed.
PHP_BIN=/path/to/php8.6 bash code/php-8-6/run-examples.sh
```

Add `--with-mysql` to include the two MySQLi labs. Use a test database server and set `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_USER`, and `MYSQL_PASSWORD` as applicable. The examples only issue `SELECT` statements and can connect without selecting a database.

```bash
MYSQL_USER=tutorial bash code/php-8-6/run-examples.sh --with-mysql
```
