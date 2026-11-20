<?php

declare(strict_types=1);

use App\Http\Controllers\ShopController;
use App\Http\Middleware\TraceShopRequest;
use Illuminate\Support\Facades\Route;

Route::get('/lab', [ShopController::class, 'lab']);
Route::get('/catalogue-preview', [ShopController::class, 'cataloguePreview']);
Route::middleware(TraceShopRequest::class)->group(function (): void {
    Route::get('/catalogue', [ShopController::class, 'catalogue']);
    Route::get('/catalogue-summary', [ShopController::class, 'catalogueSummary']);
    Route::get('/order-history', [ShopController::class, 'orderHistory']);
    Route::post('/orders', [ShopController::class, 'order']);
});
Route::post('/exports', [ShopController::class, 'requestExport']);
Route::post('/exports/sync', [ShopController::class, 'requestExport'])->name('shop.exports.sync');
Route::get('/exports/{export}', [ShopController::class, 'exportStatus'])->whereNumber('export');
Route::get('/exports/{export}/file', [ShopController::class, 'download'])->whereNumber('export');
