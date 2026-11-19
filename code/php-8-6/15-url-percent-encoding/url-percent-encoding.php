<?php

// filename: url-percent-encoding.php

declare(strict_types=1);

use Uri\WhatWg\UrlPercentEncodingMode;
use function Uri\WhatWg\url_percent_encode;

echo url_percent_encode('maps/Hobart north.svg', UrlPercentEncodingMode::PathSegment), PHP_EOL;
echo url_percent_encode('Hobart & coast', UrlPercentEncodingMode::FormQuery), PHP_EOL;
