<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BuildCatalogueHeadline;

final class FormatCatalogueHeadline
{
    public function handle(BuildCatalogueHeadline $event): string
    {
        return "First {$event->productCount} products";
    }
}
