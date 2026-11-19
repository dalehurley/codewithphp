<?php

// filename: duration.php

declare(strict_types=1);

use Time\Duration;

$baseDelay = Duration::fromMilliseconds(750);
$attemptDelay = $baseDelay->multiplyBy(3);
$delay = min($attemptDelay, Duration::fromSeconds(2));

printf("Base: %d s %d ns\n", $baseDelay->seconds, $baseDelay->nanoseconds);
printf("Capped delay: %d s %d ns\n", $delay->seconds, $delay->nanoseconds);
