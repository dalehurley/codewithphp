<?php

declare(strict_types=1);

use App\Catalogue\CatalogueSummary;
use App\Models\Product;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

Artisan::command('shop:product-price {product} {price_cents}', function (): void {
    $input = Validator::make($this->arguments(), [
        'product' => ['required', 'integer', 'min:1'],
        'price_cents' => ['required', 'integer', 'between:0,2147483647'],
    ])->validate();

    DB::transaction(static function () use ($input): void {
        $product = Product::query()->findOrFail($input['product']);
        $product->price_cents = (int) $input['price_cents'];
        $product->save();
    });

    $this->info('Product ' . $input['product'] . ' now costs ' . $input['price_cents'] . ' cents.');
})->purpose('Change a product price through the catalogue invalidation path');

Artisan::command('shop:catalogue-expire {category}', function (CatalogueSummary $catalogue): int {
    $category = (int) $this->argument('category');
    if (!$catalogue->expireSoon($category)) {
        $this->error('Cache the category summary before setting its remaining TTL.');

        return 1;
    }

    $this->info('Catalogue summary expires in one second.');

    return 0;
})->purpose('Shorten one cached summary TTL for the expiry experiment');

Artisan::command('shop:report {run}', function (): void {
    $run = $this->argument('run');
    $exports = DB::table('exports')->where('run_id', $run)
        ->selectRaw('state, count(*) as total')
        ->groupBy('state')->orderBy('state')->pluck('total', 'state');

    $this->line(json_encode([
        'run' => $run,
        'orders' => DB::table('orders')->where('run_id', $run)->count(),
        'exports' => (object) $exports->all(),
        'queue_depth' => DB::table('jobs')->count(),
        'failed_jobs' => DB::table('failed_jobs')->count(),
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
})->purpose('Print the persisted state of one load-test run');
