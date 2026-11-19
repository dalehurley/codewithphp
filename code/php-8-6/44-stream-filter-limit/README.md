# Set a Limit for php://filter Chains

Apply a known read filter with an explicit slot limit. PHP 8.6 deprecates a `php://filter` URL with more than 16 filters if no explicit limit is configured.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/44-stream-filter-limit/stream-filter-limit.php
```

Requirements: `allow_url_fopen=1` for the in-memory data URL used as the input.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/44-stream-filter-limit/).
