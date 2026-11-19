# Handle New ValueErrors When Upgrading

Probe three input checks that changed between PHP 8.5 and 8.6. PHP now raises `ValueError` in more APIs when the argument has the right type but an invalid value.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/53-stricter-value-errors/stricter-value-errors.php
```

Requirements: A 64-bit PHP 8.6 CLI for the out-of-range delay case.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/53-stricter-value-errors/).
