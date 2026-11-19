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
