---
title: "PHP vs Perl"
description: "Compare PHP and Perl for web applications, text processing and scripts. Run the same log-filtering example in both languages."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 8
---

PHP fits a web application where you want an ecosystem for templates, databases and HTTP requests. Perl remains a practical choice for existing scripts and text-processing pipelines that already use its modules. Both work from the command line, which makes it easy to compare them on a small input file.

| Decision | PHP | Perl |
| --- | --- | --- |
| Text patterns | Functions such as `preg_match` take a pattern and subject. | The `=~` operator applies a pattern directly to a value. |
| Collections | PHP arrays provide ordered lists and maps. | Perl provides separate arrays and hashes. |
| Libraries | Composer is the usual dependency manager. | CPAN distributes Perl modules. |
| Reading a large file | Iterate a stream with `fgets` or use `SplFileObject`. | Read one line at a time from a file handle. |

## Extract the messages from error lines

```php
<?php
$lines = ['INFO started', 'ERROR disk full', 'ERROR export failed'];

foreach ($lines as $line) {
    if (preg_match('/^ERROR (.+)$/', $line, $match) === 1) {
        echo $match[1], PHP_EOL;
    }
}
```

```perl
use strict;
use warnings;

my @lines = ('INFO started', 'ERROR disk full', 'ERROR export failed');

for my $line (@lines) {
    if (my ($message) = $line =~ /^ERROR (.+)$/) {
        print "$message\n";
    }
}
```

Both print `disk full` and `export failed` on separate lines. Run the examples with `php example.php` or `perl example.pl`. The pattern only matches `ERROR` at the start of the line and requires a message. Adapt the data source to a line-by-line file reader for logs that would be expensive to hold in memory.

## Decide whether to migrate a script

For a working Perl utility, start with fixtures that preserve the behaviour people depend on. Include blank lines, malformed lines and representative character encodings. Port to PHP if using the same runtime as a PHP application simplifies deployment or ownership. An established script may be easiest to retain as a scheduled tool with a documented input and output.

- [Perl introduction](https://perldoc.perl.org/perlintro)
- [PHP preg_match](https://www.php.net/manual/en/function.preg-match.php)
- [Learn PHP control structures](/series/php-basics/chapters/03-control-structures/)
- [PHP vs Python](/compare/php-vs-python/) · [All PHP comparisons](/compare/)
