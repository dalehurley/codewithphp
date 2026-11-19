# Validate IDs in Custom Session Handlers

Check the ID hooks on a file-backed session handler. PHP 8.6 deprecates session handler objects without both `create_sid()` and `validateId()`.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/46-custom-session-handlers/custom-session-handlers.php
```

Requirements: The `session` extension and a writable temporary directory.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/46-custom-session-handlers/).
