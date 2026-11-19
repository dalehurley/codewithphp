# Migrate mbstring Regular Expressions to PCRE

Replace a multibyte match and split with `preg_match()` and `preg_split()`. PHP 8.6 deprecates the mbstring regex functions backed by Oniguruma.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/37-mbregex-to-pcre/mbregex-to-pcre.php
```

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/37-mbregex-to-pcre/).
