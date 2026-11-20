# PHP debugging challenges

Open this folder in your terminal and run `php -v`. Use PHP 8.0 or later. The scripts work locally without a database, web server, or network.

Run each broken script before changing it. Write down what happened and the filename or line number it pointed to. Make one small change, run the command again, and compare the result below. The `solutions/` directory has completed files you can run with the same command by replacing `broken/` with `solutions/`.

| File in `broken/` | Target output | Hint |
| --- | --- | --- |
| `01-greeting.php` | `Hello, PHP!` | Run `php -l broken/01-greeting.php`. Check the line before the parser's reported token. |
| `02-optional-key.php` | `Moon Atlas (no subtitle)` | Real rows can omit an optional key. Use the null coalescing operator for a fallback. |
| `03-json-list.php` | `["php"]` | Inspect the keys that survive filtering. A JSON list needs consecutive integer indexes from zero. |
| `04-read-file.php` | `First pick: Moon Atlas` | Run the script by path from this folder. Check `getcwd()` and `__DIR__` to find the difference. |

Run with warnings visible by adding `-d display_errors=1 -d error_reporting=-1` before the script path.

## After solving each challenge

- Change the greeting to a name you choose and lint the script again.
- Give Moon Atlas a subtitle. Check that your program now uses it.
- Add a second PHP entry to the JSON input. Check that you still get a list.
- Run the file-reading solution once from this folder and once from its parent. Check that the output stays the same.

[PHP troubleshooting guide](https://codewithphp.com/series/php-basics/troubleshooting/)
