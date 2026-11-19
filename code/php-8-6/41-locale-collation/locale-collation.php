<?php

// filename: locale-collation.php

declare(strict_types=1);

foreach (['de_DE', 'sv_SE'] as $locale) {
    $names = ['Zoe', 'Åsa', 'Anna'];
    $collator = new Collator($locale);
    if (!$collator->sort($names)) {
        throw new RuntimeException($collator->getErrorMessage());
    }
    printf("%s %s\n", $locale, implode(', ', $names));
}

$swedish = new Collator('sv_SE');
$order = $swedish->compare('Zoe', 'Åsa');
if ($order === false) {
    throw new RuntimeException($swedish->getErrorMessage());
}
echo 'Zoe comes first in Swedish: ', $order < 0 ? 'yes' : 'no', PHP_EOL;
