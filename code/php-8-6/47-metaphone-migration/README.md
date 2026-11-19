# Move Phonetic Matching Out of Core

Isolate a phonetic encoder behind a callable so you can replace the deprecated `metaphone()` implementation and verify your search behavior.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/47-metaphone-migration/metaphone-migration.php
```

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/47-metaphone-migration/).
