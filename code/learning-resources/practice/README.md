# Practice downloads

After updating a starter, challenge, behaviour example, or the canonical dataset CSV, regenerate the downloads from the repository root.

```sh
php code/learning-resources/practice/build-downloads.php
```

The maintainer command needs PHP 8.1 or later with the `pdo_sqlite` and `zip` extensions. The site serves the generated downloads from `public/downloads/practice/`. Commit affected archives and dataset files together with the source change.

- `exercises/` contains the offline instructions, starter scripts, grade data, and alternative solutions. The ZIP also copies four full solutions from `code/php-basics/`.
- `debugging/` contains the intentional failures, fixtures, solutions, and hints. The missing semicolon in `broken/01-greeting.php` is part of the first exercise.
- `behaviour/` contains small scripts that can run on both sides of the version changes they demonstrate.
- `public/downloads/practice/library-books.csv` is the source for the corresponding JSON and SQLite files.
- `public/downloads/practice/php-array-reference.html` contains the single-page print reference. It is ready to print directly from a browser.
