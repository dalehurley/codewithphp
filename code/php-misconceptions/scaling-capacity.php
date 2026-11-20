<?php

declare(strict_types=1);

$inputs = array_slice($argv, 1);
if ($inputs === []) {
    $inputs = ['3', '8', '100', '25', '6'];
}

if (count($inputs) !== 5 || preg_grep('/\A(?:0|[1-9][0-9]{0,3})\z/', $inputs, PREG_GREP_INVERT) !== []) {
    fwrite(STDERR, 'Usage: php scaling-capacity.php [instances workers_per_instance busy_ms total_connections reserved_connections]' . PHP_EOL);
    fwrite(STDERR, 'Provide all five values as decimal integers from 0 to 9999, without leading zeros.' . PHP_EOL);
    exit(1);
}

[$instances, $workersPerInstance, $busyMs, $totalConnections, $reservedConnections] = array_map('intval', $inputs);

if (min($instances, $workersPerInstance, $busyMs, $totalConnections) === 0 || $reservedConnections > $totalConnections) {
    fwrite(STDERR, 'Use positive values for instances, workers, busy milliseconds and total connections. Reserved connections may be 0 through the total.' . PHP_EOL);
    exit(1);
}

// The lab assumes each configured web slot may hold one connection at peak.
$configuredSlots = $instances * $workersPerInstance;
$webConnections = $totalConnections - $reservedConnections;
$slotsOverBudget = max(0, $configuredSlots - $webConnections);
$cappedWorkers = min($workersPerInstance, intdiv($webConnections, $instances));
$cappedSlots = $instances * $cappedWorkers;
$busySeconds = $busyMs / 1000;

printf("Configured web slots: %d\n", $configuredSlots);
printf("Worker-only ceiling: %.1F requests/s\n", $configuredSlots / $busySeconds);
printf("Database budget for web: %d connections\n", $webConnections);
printf("Configured slots above budget: %d\n", $slotsOverBudget);
printf("Equal cap within the budget: %d workers per instance (%d slots)\n", $cappedWorkers, $cappedSlots);
printf("Ceiling at that cap: %.1F requests/s\n", $cappedSlots / $busySeconds);
