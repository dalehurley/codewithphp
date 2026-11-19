# Handle Stream Flush and Close Failures

Simulate a buffered write that fails during finalization. PHP 8.6 propagates stream flush and close failures through `fclose()`, `file_put_contents()`, and `copy()`.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/52-stream-close-failures/stream-close-failures.php
```

Requirements: `allow_url_fopen=1` for the in-memory data URL used as the copy source.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/52-stream-close-failures/).
