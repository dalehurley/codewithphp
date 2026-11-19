<?php

// filename: binary-endianness.php

declare(strict_types=1);

$packet = pack('s>f>', -2, 1.5);
echo bin2hex($packet), PHP_EOL;

$decoded = unpack('s>status/f>scale', $packet);
printf("status=%d scale=%.1f\n", $decoded['status'], $decoded['scale']);
