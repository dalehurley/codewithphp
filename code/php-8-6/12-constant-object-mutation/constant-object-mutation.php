<?php

// filename: constant-object-mutation.php

declare(strict_types=1);

final class WorkerSettings
{
    public function __construct(public int $timeoutSeconds)
    {
    }
}

const DEFAULT_WORKER_SETTINGS = new WorkerSettings(5);

final class WorkerConfiguration
{
    public const DEFAULTS = DEFAULT_WORKER_SETTINGS;
}

$sameSettings = WorkerConfiguration::DEFAULTS;
echo 'Before: ', $sameSettings->timeoutSeconds, PHP_EOL;
WorkerConfiguration::DEFAULTS->timeoutSeconds = 12;
echo 'After: ', $sameSettings->timeoutSeconds, PHP_EOL;
