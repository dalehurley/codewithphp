<?php

// filename: define-third-argument.php

declare(strict_types=1);

define('DEFAULT_CURRENCY', 'AUD');
echo constant('DEFAULT_CURRENCY'), PHP_EOL;
echo 'Lowercase alias exists: ', defined('default_currency') ? 'yes' : 'no', PHP_EOL;
