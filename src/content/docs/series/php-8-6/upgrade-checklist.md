---
title: "PHP 8.5 to 8.6 Upgrade Checklist"
description: "Compare PHP 8.5 and 8.6, identify breaking changes and deprecations, and test your PHP upgrade before switching production."
series: "php-8-6"
author: "Code with PHP"
datePublished: "2026-11-20"
socialImage: "/social/php-8-6-overview.jpg"
estimatedTime: "PT30M"
sidebar:
  order: -1
---

Before switching an application from PHP 8.5 to 8.6, run the same locked dependencies and test suite on both versions. Pay particular attention to sign-in and sessions, string normalization, and code that passes user-selected modes or flags to built-in functions. Keep the PHP 8.5 environment available until the application passes the PHP 8.6 checks.

## What changes in PHP 8.6?

| Area | What to check | How to update |
| --- | --- | --- |
| Session defaults | PHP 8.6 defaults to strict session ID handling, HttpOnly cookies, and `SameSite=Lax`. Application and server configuration can override these defaults. | Test sign-in, session regeneration, and any legitimate cross-site POST callbacks. Check the web runtime's effective settings and review custom session handlers. [Session lab](/series/php-8-6/chapters/45-session-defaults/) |
| String normalization | The default character masks for `trim()`, `ltrim()`, and `rtrim()` now include form feed (`\f`). | Add byte-level fixtures for imports or formatting that must preserve form feeds. Supply an explicit character mask if you need the older behavior. [Trimming lab](/series/php-8-6/chapters/51-trim-form-feed/) |
| Invalid flags and modes | Invalid mode values for functions such as `array_filter()` and `scandir()` now throw `ValueError`. | Test invalid values, use the documented constants, and handle validation failures at the application boundary. [ValueError lab](/series/php-8-6/chapters/53-stricter-value-errors/) |
| Cleanup and return values | Returning from a `finally` block emits a compile-time deprecation. A return there can override an earlier result or suppress an exception. | Keep cleanup in `finally` and move the return into the success path. Test cleanup on an exception as well as a normal return. [Cleanup lab](/series/php-8-6/chapters/33-return-in-finally/) |
| Deprecated names and arguments | Certain uses of `let` and `is` as symbol names are deprecated. Explicitly passing `false` as the third argument to `define()` is deprecated. | Rename affected symbols and references. Remove the obsolete `define()` argument. [Identifier lab](/series/php-8-6/chapters/36-deprecated-identifiers/) · [Constants lab](/series/php-8-6/chapters/42-define-third-argument/) |

Check the official migration guide for changes specific to your extensions and platform. [PHP 8.6 backward-incompatible changes](https://www.php.net/manual/en/migration86.incompatible.php) · [PHP 8.6 deprecations](https://www.php.net/manual/en/migration86.deprecated.php)

## Compare the two PHP runtimes

Open the Code with PHP checkout and point each variable at its corresponding CLI binary. The [comparison script](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/upgrade/compare-runtime.php) runs without a database or network connection.

```bash
PHP85=/path/to/php8.5
PHP86=/path/to/php8.6

"$PHP85" -n code/php-8-6/upgrade/compare-runtime.php
"$PHP86" -n code/php-8-6/upgrade/compare-runtime.php
```

With the session extension built in, the output on PHP 8.5.0 is:

```text
PHP 8.5.0
trim() bytes: 0c68656c6c6f0c
Invalid array_filter() mode: returned [1]
Session defaults: strict=0, HttpOnly=no, SameSite=(unset)
```

On PHP 8.6.0:

```text
PHP 8.6.0
trim() bytes: 68656c6c6f
Invalid array_filter() mode: ValueError
Session defaults: strict=1, HttpOnly=yes, SameSite=Lax
```

The `-n` flag skips the usual PHP configuration files so you can compare compiled defaults. If the session extension is loaded through an INI file, run the command without `-n` to see the effective configuration for that CLI. Read any web runtime settings through that runtime because PHP-FPM and the CLI may load different configuration files.

## Before changing production

1. **Record the baseline.** Save the PHP version, loaded INI paths and extensions from the web runtime and workers. Use `php --ini` and `php -m` for the CLI. Record the dependency lockfile and the known-good build you could restore.
2. **Run a PHP 8.6 test job using the lockfile.** Run `composer install` and `composer check-platform-reqs` under PHP 8.6. If dependency resolution blocks the upgrade, find the constraint with `composer prohibits php 8.6.0`. Review dependency updates independently so you can isolate what changed.
3. **Lint and test with deprecations reported.** Report `E_ALL` in development and CI. Lint application PHP files to catch compile-time deprecations, then exercise the HTTP and job paths that load code at runtime. Compare failures and new warnings against the PHP 8.5 run.
4. **Test the deployed configuration in staging.** Check sign-in, cookie flags, queued jobs, scheduled jobs, database connections and file imports using the extensions and settings that production will load.
5. **Verify the rollout.** Compare error rates and key workflows after switching PHP. Restart long-running workers so they use the new binary. Have a way to restore the previous runtime and restart workers again if a regression appears.

If the same source still runs on PHP 8.5, wait to introduce PHP 8.6-only syntax such as [partial function application](/series/php-8-6/chapters/01-partial-function-application/) in those files. A version check in the same file cannot prevent the older parser from encountering that syntax.

Continue with the [PHP 8.6 course and runnable labs](/series/php-8-6/) or see the [full PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING).
