<?php

use App\Http\Controllers\ContactApiController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('contacts', [ContactApiController::class, 'index'])
        ->middleware(CheckAbilities::class.':contacts:read');
    Route::get('contacts/{contact}', [ContactApiController::class, 'show'])
        ->whereNumber('contact')->middleware(CheckAbilities::class.':contacts:read');

    Route::post('contacts', [ContactApiController::class, 'store'])
        ->middleware(CheckAbilities::class.':contacts:write');
    Route::patch('contacts/{contact}', [ContactApiController::class, 'update'])
        ->whereNumber('contact')->middleware(CheckAbilities::class.':contacts:write');
    Route::delete('contacts/{contact}', [ContactApiController::class, 'destroy'])
        ->whereNumber('contact')->middleware(CheckAbilities::class.':contacts:write');
});
