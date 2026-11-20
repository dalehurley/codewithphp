<?php

declare(strict_types=1);

namespace App\Events;

final readonly class BuildCatalogueHeadline
{
    public function __construct(public int $productCount)
    {
    }
}
