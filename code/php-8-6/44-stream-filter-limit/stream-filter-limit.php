<?php

// filename: stream-filter-limit.php

declare(strict_types=1);

$context = stream_context_create(['filter' => ['max_filter_count' => 1]]);
$stream = fopen('php://filter/read=string.toupper/resource=data://text/plain,order', 'r', context: $context);
if ($stream === false) {
    throw new RuntimeException('Cannot open the filtered input');
}
try {
    echo stream_get_contents($stream), PHP_EOL;
} finally {
    fclose($stream);
}
