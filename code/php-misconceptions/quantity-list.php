<?php

declare(strict_types=1);

require_once __DIR__ . '/total-quantities.php';

echo totalQuantities(new UnitPrice(1250), ['3']), PHP_EOL;
