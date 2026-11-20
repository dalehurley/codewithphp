<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Catalogue\CatalogueSummary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CatalogueBenchmarkSeeder extends Seeder
{
    public function run(): void
    {
        $fixture = config('shop.catalogue_benchmark');
        $category = $fixture['category'];
        $firstId = $fixture['first_id'];
        $endId = $firstId + $fixture['size'];

        $paragraph = 'Product specification, care instructions, stock notes and packaging details. ';
        $description = substr(str_repeat($paragraph, (int) ceil($fixture['description_bytes'] / strlen($paragraph))), 0, $fixture['description_bytes']);

        DB::transaction(static function () use ($category, $firstId, $endId, $description): void {
            DB::table('categories')->upsert(['id' => $category, 'name' => "Category {$category}"], ['id'], ['name']);

            for ($start = $firstId; $start < $endId; $start += 250) {
                $products = [];
                for ($id = $start; $id < min($start + 250, $endId); $id++) {
                    $products[] = [
                        'id' => $id,
                        'category_id' => $category,
                        'title' => "Product {$id}",
                        'price_cents' => 500 + ($id % 101) * 37,
                        'description' => $description,
                    ];
                }

                DB::table('products')->upsert($products, ['id'], ['category_id', 'title', 'price_cents', 'description']);
            }
            app(CatalogueSummary::class)->invalidateAfterCommit([$category]);
        });
    }
}
