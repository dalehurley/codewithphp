<?php

// filename: printf-performance.php

declare(strict_types=1);

$warehouse = 'South';
$count = 7;
printf("%s has %d orders\n", $warehouse, $count);
printf("Batch %04d\n", $count);
