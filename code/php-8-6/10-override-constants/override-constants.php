<?php

// filename: override-constants.php

declare(strict_types=1);

class VersionOne
{
    public const int VERSION = 1;
}

final class VersionTwo extends VersionOne
{
    #[Override]
    public const int VERSION = 2;
}

interface ServiceState
{
    public const Ready = 'ready';
}

enum WorkerState: string implements ServiceState
{
    #[Override]
    case Ready = 'ready';
}

echo 'Version ', VersionTwo::VERSION, PHP_EOL;
echo WorkerState::Ready->value, PHP_EOL;
