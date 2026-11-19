<?php

// filename: debug-info-return-type.php

declare(strict_types=1);

final class Report
{
    public function __construct(private ?string $status)
    {
    }

    public function __debugInfo(): array
    {
        return $this->status === null ? [] : ['status' => $this->status];
    }
}

var_dump(new Report(null));
