---
title: "Test PHP on Two Versions with GitHub Actions"
description: "Copy a small dependency-free PHP test and a GitHub Actions matrix that runs it on PHP 8.4 and 8.5."
prev: false
next: false
---

Use the same command on your machine and on GitHub Actions. This example checks a function that prepares a book title for the [reading list project](/series/php-basics/sqlite-starter/). The isolated example has no dependencies beyond PHP.

## Write the function and its test

Save the function as `book-title.php` at the root of a small repository.

```php
<?php

declare(strict_types=1);

function normalizeBookTitle(string $title): string
{
    return trim($title);
}
```

Save the test next to it as `test-book-title.php`.

```php
<?php

declare(strict_types=1);

require __DIR__ . '/book-title.php';

$cases = [
    ['  My Book  ', 'My Book'],
    ["\tNext Book\n", 'Next Book'],
    ['  ', ''],
    ['Notes & Queries', 'Notes & Queries'],
];

foreach ($cases as [$input, $expected]) {
    $actual = normalizeBookTitle($input);
    if ($actual !== $expected) {
        fwrite(STDERR, sprintf(
            "Expected %s, got %s\n",
            var_export($expected, true),
            var_export($actual, true)
        ));
        exit(1);
    }
}

echo "4 checks passed.\n";
```

Run the test with `php test-book-title.php`. Expect `4 checks passed.` To watch a failure, temporarily return `$title` directly from the function. Rerun the test and expect an error and exit status 1, then restore the function.

## Add the workflow

Save this as `.github/workflows/php-tests.yml`. The two matrix entries are an example support range for these scripts. Use the versions that your own project supports.

```yaml
name: PHP tests

on: [push, pull_request]

permissions:
  contents: read

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ['8.4', '8.5']
    steps:
      - uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7.0.1
        with:
          persist-credentials: false
      - uses: shivammathur/setup-php@eb7c497e18156a6bbabfef1d3a82760b9eda3962 # 2.40.0
        with:
          php-version: ${{ matrix.php }}
          coverage: none
      - run: php test-book-title.php
```

Push all three files to your repository and open its **Actions** tab. Expect two jobs for the same test command, one per PHP version. A nonzero exit status marks that job as failed and leaves the other version’s result visible.

When your project uses Composer, install its dependencies in the workflow before running its test command. Keep the matrix versions aligned with the minimum and maximum PHP versions the project supports, and update action pins when you review dependencies. [GitHub Actions matrix documentation](https://docs.github.com/en/actions/how-tos/write-workflows/choose-what-workflows-do/run-job-variations) · [Setup PHP action](https://github.com/shivammathur/setup-php)
