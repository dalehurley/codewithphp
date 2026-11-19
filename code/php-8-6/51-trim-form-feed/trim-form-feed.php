<?php

// filename: trim-form-feed.php

declare(strict_types=1);

$value = "\fready\f";
echo 'Both: ', bin2hex(trim($value)), PHP_EOL;
echo 'Left: ', bin2hex(ltrim($value)), PHP_EOL;
echo 'Right: ', bin2hex(rtrim($value)), PHP_EOL;

$oldMask = " \n\r\t\v\0";
echo 'Explicit mask: ', bin2hex(trim($value, $oldMask)), PHP_EOL;
