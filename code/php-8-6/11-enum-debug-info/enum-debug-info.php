<?php

// filename: enum-debug-info.php

declare(strict_types=1);

enum JobState: string
{
    case Queued = 'queued';
    case Complete = 'complete';

    public function __debugInfo(): array
    {
        return ['state' => $this->value, 'finished' => $this === self::Complete];
    }
}

var_dump(JobState::Queued);
echo json_encode(JobState::Queued, JSON_THROW_ON_ERROR), PHP_EOL;
