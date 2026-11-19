<?php

// filename: clamp.php

declare(strict_types=1);

use Time\Duration;

foreach ([-5, 25, 150] as $requested) {
    printf("%d -> %d\n", $requested, clamp($requested, 1, 100));
}

$timeout = clamp(
    Duration::fromMilliseconds(300),
    Duration::fromSeconds(1),
    Duration::fromSeconds(5),
);
echo 'Timeout seconds: ', $timeout->seconds, PHP_EOL;

try {
    clamp(3, 10, 1);
} catch (ValueError) {
    echo "Rejected reversed limits\n";
}
