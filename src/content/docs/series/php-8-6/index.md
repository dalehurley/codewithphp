---
title: What’s New in PHP 8.6
description: "Explore PHP 8.6 features, breaking changes and deprecations with runnable labs and a PHP 8.5 to 8.6 upgrade checklist."
---

## Overview

Work through the PHP 8.6 release announcement one topic at a time. Each chapter demonstrates a language feature, library addition, performance change, or migration in a small PHP script.

Start by building callbacks, modeling timeouts, and checking property access. The extension labs use PHP to work with URLs, archives, Unicode text, and local TLS connections.

**Upgrading from PHP 8.5?** Start with the [PHP 8.6 upgrade checklist](/series/php-8-6/upgrade-checklist/) to compare both runtimes and plan your compatibility checks. Then use the [deprecation and upgrade labs](#deprecations-and-upgrade-changes) to test the changes that affect your code.

## Who This Is For

This series is for developers who already use PHP or are comfortable with functions, arrays, and classes. If you’re new to the language, start with [PHP Basics](/series/php-basics/).

## Prerequisites

- A PHP 8.6 CLI. The examples use PHP 8.6.0 behavior. [PHP downloads](https://www.php.net/downloads.php)
- A local checkout of [Code with PHP](https://github.com/dalehurley/codewithphp) and a terminal opened at its root.
- Optional extensions and services listed in the chapters that use them. The MySQLi labs need a database connection, and the TLS labs run on loopback.
- Allow about 10–15 minutes per chapter.

## What You’ll Build

You’ll assemble a set of small programs for common jobs such as bounding inputs, processing incoming stream data, formatting text, and checking upgrade behavior. The PHP source is in [the series example directory](https://github.com/dalehurley/codewithphp/tree/main/code/php-8-6).

## Learning Objectives

- Use the new syntax and core APIs in standalone scripts.
- Check which extensions and external libraries your PHP build supplies.
- Recognize several operations whose results, warnings, or exceptions change after upgrading.
- Verify your migration with focused inputs and expected output.

## How This Series Works

Choose a chapter and run its example. Compare the output, then try the exercise to see how changing an input or configuration affects the result. The chapters work independently unless a prerequisite says otherwise.

For a first pass, follow the four groups in order: language and core APIs → extensions and protocols → performance → upgrade changes.

## Quick Start

Run these commands from the repository root.

```bash
# Confirm which PHP binary you are using.
php --version

# Run the first lesson.
php code/php-8-6/01-partial-function-application/partial-function-application.php
```

The first two lines from the example are:

```text
INFO [worker] Job started
ERROR [billing] Payment declined
```

## Chapters

### Language and core APIs

1. [Partial Function Application](/series/php-8-6/chapters/01-partial-function-application/)
2. [Elapsed Time with Time\Duration](/series/php-8-6/chapters/02-duration/)
3. [Wait for I/O with Io\Poll](/series/php-8-6/chapters/03-polling-api/)
4. [Structured Stream Errors](/series/php-8-6/chapters/04-stream-errors/)
5. [Limit Values with clamp()](/series/php-8-6/chapters/05-clamp/)
6. [Type-Safe Sorting with SortDirection](/series/php-8-6/chapters/06-sort-direction/)
7. [DocComments on Function Parameters](/series/php-8-6/chapters/07-parameter-doccomments/)
8. [Inspect Property Read and Write Access](/series/php-8-6/chapters/08-property-access-reflection/)
9. [Default Values for Readonly Properties](/series/php-8-6/chapters/09-readonly-defaults/)
10. [Override Attributes on Constants and Enum Cases](/series/php-8-6/chapters/10-override-constants/)
11. [Customize Enum Dumps with __debugInfo()](/series/php-8-6/chapters/11-enum-debug-info/)
12. [Change Properties on Objects Held in Constants](/series/php-8-6/chapters/12-constant-object-mutation/)

### Extensions and protocols

13. [Build URLs and URI References from Components](/series/php-8-6/chapters/13-uri-builders/)
14. [Identify URI and Host Types](/series/php-8-6/chapters/14-uri-types/)
15. [Percent-Encode WHATWG URL Components](/series/php-8-6/chapters/15-url-percent-encoding/)
16. [Establish a Shared Key with X-Wing and ML-KEM-768](/series/php-8-6/chapters/16-sodium-kem/)
17. [Encrypt IP Addresses with Sodium](/series/php-8-6/chapters/17-sodium-ipcrypt/)
18. [Extend Hash Output with SHAKE and TurboSHAKE](/series/php-8-6/chapters/18-sodium-xof/)
19. [Resume a TLS Stream Session](/series/php-8-6/chapters/19-tls-session-resumption/)
20. [Authenticate TLS Streams with External PSKs](/series/php-8-6/chapters/20-tls-pre-shared-keys/)
21. [Send TLS 1.3 Early Data](/series/php-8-6/chapters/21-tls-early-data/)
22. [Quote SQL String Literals with MySQLi](/series/php-8-6/chapters/22-mysqli-quote-string/)
23. [Set Byte Order with pack() and unpack()](/series/php-8-6/chapters/23-binary-endianness/)
24. [Locate a JSON Syntax Error](/series/php-8-6/chapters/24-json-error-location/)
25. [Show Function Arguments in PHP Errors](/series/php-8-6/chapters/25-error-include-args/)
26. [Reverse Text by Grapheme Cluster](/series/php-8-6/chapters/26-grapheme-strrev/)
27. [Format Localized Number Ranges](/series/php-8-6/chapters/27-intl-number-range/)
28. [Read and Write ZIP Archives in Memory](/series/php-8-6/chapters/28-zip-in-memory/)

### Performance

29. [Compiler Optimizations for array_map()](/series/php-8-6/chapters/29-array-map-performance/)
30. [Faster printf() with Simple Formats](/series/php-8-6/chapters/30-printf-performance/)
31. [Caching Stateless Closures](/series/php-8-6/chapters/31-stateless-closure-cache/)
32. [Benchmark JSON Encoding on Your Data](/series/php-8-6/chapters/32-json-encoding-performance/)

### Deprecations and upgrade changes

33. [Move Returns Out of finally Blocks](/series/php-8-6/chapters/33-return-in-finally/)
34. [Move Results and Generators Out of Object Lifecycles](/series/php-8-6/chapters/34-constructor-destructor-returns/)
35. [Return an Array from __debugInfo()](/series/php-8-6/chapters/35-debug-info-return-type/)
36. [Rename Deprecated Identifiers](/series/php-8-6/chapters/36-deprecated-identifiers/)
37. [Migrate mbstring Regular Expressions to PCRE](/series/php-8-6/chapters/37-mbregex-to-pcre/)
38. [Replace Deprecated Numeric Aliases](/series/php-8-6/chapters/38-numeric-aliases/)
39. [Replace spl_object_hash() and spl_classes()](/series/php-8-6/chapters/39-spl-object-and-extension-introspection/)
40. [Pass Arrays to Array and Compression APIs](/series/php-8-6/chapters/40-objects-as-arrays/)
41. [Replace strcoll() and SORT_LOCALE_STRING with Collator](/series/php-8-6/chapters/41-locale-collation/)
42. [Drop the Third Argument to define()](/series/php-8-6/chapters/42-define-third-argument/)
43. [Handle Class Names Explicitly in Type Checks](/series/php-8-6/chapters/43-class-string-guards/)
44. [Set a Limit for php://filter Chains](/series/php-8-6/chapters/44-stream-filter-limit/)
45. [Audit PHP 8.6 Session Cookie Defaults](/series/php-8-6/chapters/45-session-defaults/)
46. [Validate IDs in Custom Session Handlers](/series/php-8-6/chapters/46-custom-session-handlers/)
47. [Move Phonetic Matching Out of Core](/series/php-8-6/chapters/47-metaphone-migration/)
48. [Replace Deprecated MySQLi Charset and Statement Calls](/series/php-8-6/chapters/48-mysqli-charset-and-statements/)
49. [Migrate SplFileObject CSV Calls](/series/php-8-6/chapters/49-splfileobject-csv/)
50. [Replace Deprecated ArrayIterator Methods](/series/php-8-6/chapters/50-arrayiterator-deprecations/)
51. [Account for Form Feeds in trim()](/series/php-8-6/chapters/51-trim-form-feed/)
52. [Handle Stream Flush and Close Failures](/series/php-8-6/chapters/52-stream-close-failures/)
53. [Handle New ValueErrors When Upgrading](/series/php-8-6/chapters/53-stricter-value-errors/)

## Frequently Asked Questions

### Can I follow the series on PHP 8.5?

Use 8.6 for the examples. Several migration scripts also run on 8.5, which lets you compare behavior. PHP 8.5 cannot parse the new partial-application syntax.

### Does this cover the whole changelog?

The chapters cover the features, improvements, and upgrade notes highlighted in the release announcement. For changes specific to other extensions, SAPIs, or platforms, consult the [migration guide](https://www.php.net/manual/en/migration86.php) and [versioned upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING).

## Getting Help

Check `php --version` and use `php -m` to list your extensions. If you report a problem, include the chapter number, PHP version, OS, and the output from the example. [Code with PHP issues](https://github.com/dalehurley/codewithphp/issues)

## Related Resources

- [PHP 8.6 release announcement](https://www.php.net/releases/8.6/en.php)
- [PHP 8.5 to 8.6 migration guide](https://www.php.net/manual/en/migration86.php)
- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
