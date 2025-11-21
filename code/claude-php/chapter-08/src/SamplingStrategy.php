<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class SamplingStrategy
{
    public function __construct(
        public readonly float $temperature,
        public readonly float $topP,
        public readonly string $description,
        public readonly array $bestFor
    ) {}
}

