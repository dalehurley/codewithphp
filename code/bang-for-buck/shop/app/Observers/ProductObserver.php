<?php

declare(strict_types=1);

namespace App\Observers;

use App\Catalogue\CatalogueSummary;
use App\Models\Product;

final class ProductObserver
{
    public function saved(Product $product): void
    {
        if ($product->wasRecentlyCreated || $product->wasChanged(['category_id', 'title', 'price_cents'])) {
            app(CatalogueSummary::class)->invalidateAfterCommit([
                $product->getOriginal('category_id') ?? $product->category_id,
                $product->category_id,
            ]);
        }
    }

    public function deleted(Product $product): void
    {
        app(CatalogueSummary::class)->invalidateAfterCommit([$product->category_id]);
    }
}
