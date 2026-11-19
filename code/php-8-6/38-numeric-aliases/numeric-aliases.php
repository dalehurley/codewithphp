<?php

// filename: numeric-aliases.php

declare(strict_types=1);

foreach ([42, 4.25, '4.25'] as $reading) {
    printf("%s int=%s float=%s cast=%.2f\n", get_debug_type($reading),
        is_int($reading) ? 'yes' : 'no',
        is_float($reading) ? 'yes' : 'no',
        floatval($reading));
}
