---
title: "PHP Debugging Challenges With Hints and Solutions"
description: "Practise debugging four short PHP scripts. Fix a syntax error, a missing array key, a JSON shape problem, and a file path bug."
prev: false
next: false
---

Try four small bugs you can reproduce on your machine. Each challenge includes a broken PHP file, an expected result, a hint, and a solution. Use PHP 8.0 or later.

[Download the PHP debugging challenges (ZIP)](/downloads/practice/php-debugging-challenges.zip)

## How to debug one challenge

Extract the ZIP and open the `php-debugging-challenges` folder in a terminal. Run the broken script before editing it. Read the error or compare the actual output with the expected result. Change one line, then run the same command again.

Use the linter on a script with a parse error:

```sh
php -l broken/01-greeting.php
```

For a script that runs but may issue a warning, show all errors in your local command:

```sh
php -d display_errors=1 -d error_reporting=-1 broken/02-optional-key.php
```

These options affect the PHP process for that command. Keep detailed errors in local debugging and logs for hosted apps.

## Four short challenges

| File in `broken/` | Expected output | Hint |
| --- | --- | --- |
| `01-greeting.php` | `Hello, PHP!` | Look one line before the token in the parser error. |
| `02-optional-key.php` | `Moon Atlas (no subtitle)` | Give an optional array key a fallback. |
| `03-json-list.php` | `["php"]` | Inspect the numeric indexes left after filtering. |
| `04-read-file.php` | `First pick: Moon Atlas` | Compare `getcwd()` with `__DIR__`. |

Run a file from the `solutions/` folder after you have tried fixing its counterpart. To check your file path fix, run it from another working directory using the full path to the script. It should still print the same book title.

If you have a different error, consult the [PHP troubleshooting guide](/series/php-basics/troubleshooting/) or the [error and exception lesson](/series/php-basics/chapters/11-error-and-exception-handling/).
