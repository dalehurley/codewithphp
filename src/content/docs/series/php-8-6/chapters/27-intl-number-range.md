---
title: "27: Format Localized Number Ranges"
description: "Format a temperature range for US English and French"
series: "php-8-6"
chapter: 27
order: 27
difficulty: "Intermediate"
prerequisites:
  - "/series/php-8-6/"
estimatedTime: "PT10M"
sidebar:
  order: 27
---

## Overview

Format a temperature range for US English and French. ICU provides locale-specific spacing, punctuation, and units.

## Prerequisites

- PHP 8.6 CLI. Confirm the version with `php --version`.
- The `intl` extension with ICU 63 or later.
- Review the [previous chapter](/series/php-8-6/chapters/26-grapheme-strrev/) if you’re following the series in order.

Allow about 10 minutes for the walkthrough and exercise.

## What You'll Build

A runnable [`intl-number-range.php`](https://github.com/dalehurley/codewithphp/blob/main/code/php-8-6/27-intl-number-range/intl-number-range.php) example.

## Step 1: Run the example (~5 min)

Run the following from the root of the Code with PHP repository.

```bash
php code/php-8-6/27-intl-number-range/intl-number-range.php
```

```php
<?php

// filename: intl-number-range.php

declare(strict_types=1);

foreach (['en_US', 'fr_FR'] as $locale) {
    $formatter = IntlNumberRangeFormatter::createFromSkeleton(
        'measure-unit/temperature-celsius precision-integer',
        $locale,
        IntlNumberRangeFormatter::COLLAPSE_UNIT,
        IntlNumberRangeFormatter::IDENTITY_FALLBACK_SINGLE_VALUE,
    );
    printf("%s %s\n", $locale, $formatter->format(18, 22));
}
```

### Expected Result

```text
en_US 18–22°C
fr_FR 18–22 °C
```

### Why It Works

The skeleton specifies the unit and precision. The collapse setting controls when a repeated unit can appear once. The identity fallback determines the rendering when the endpoints format to the same value. Output can vary with the ICU and CLDR data shipped by your system.

## Exercise

Format both endpoints as 18. With `IDENTITY_FALLBACK_SINGLE_VALUE` the range should display a single temperature.

## Wrap-up

Continue with [Read and Write ZIP Archives in Memory](/series/php-8-6/chapters/28-zip-in-memory/).

## Further Reading

- [PHP 8.6.0 upgrade notes](https://github.com/php/php-src/blob/php-8.6.0/UPGRADING)
