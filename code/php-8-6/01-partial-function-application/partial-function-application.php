<?php

// filename: partial-function-application.php

declare(strict_types=1);

function formatLog(string $channel, string $level, string $message): string
{
    return "$level [$channel] $message";
}

$workerLog = formatLog('worker', ...);
echo $workerLog('INFO', 'Job started'), PHP_EOL;

$billingError = formatLog('billing', 'ERROR', ?);
echo $billingError('Payment declined'), PHP_EOL;

$amounts = [1.235, 7.5];
print_r(array_map(round(?, 2), $amounts));
