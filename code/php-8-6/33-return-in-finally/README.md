# Move Returns Out of finally Blocks

Keep resource cleanup in `finally` and return the result on the success path. Returning from `finally` is deprecated in PHP 8.6 because it can replace an earlier return or suppress an exception.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/33-return-in-finally/return-in-finally.php
```

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/33-return-in-finally/).
