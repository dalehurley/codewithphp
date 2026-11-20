<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Catalogue\CatalogueSummary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categoryCount = (int) config('shop.categories');
        $productsPerCategory = (int) config('shop.products_per_category');

        DB::transaction(static function () use ($categoryCount, $productsPerCategory): void {
            for ($category = 1; $category <= $categoryCount; $category++) {
                DB::table('categories')->upsert(
                    ['id' => $category, 'name' => "Category {$category}"],
                    ['id'],
                    ['name'],
                );

                $products = [];
                for ($position = 1; $position <= $productsPerCategory; $position++) {
                    $id = ($category - 1) * $productsPerCategory + $position;
                    $products[] = [
                        'id' => $id,
                        'category_id' => $category,
                        'title' => "Product {$id}",
                        'price_cents' => 500 + ($id % 101) * 37,
                    ];
                }

                DB::table('products')->upsert($products, ['id'], ['title', 'price_cents']);
            }
            app(CatalogueSummary::class)->invalidateAfterCommit(range(1, $categoryCount));
        });
    }
}
