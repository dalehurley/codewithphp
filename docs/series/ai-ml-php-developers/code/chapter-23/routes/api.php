<?php

declare(strict_types=1);

use App\Http\Controllers\MLController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ML Service Routes
Route::prefix('ml')->middleware('throttle:60,1')->group(function () {
    // Sentiment Analysis - Synchronous
    Route::post('/sentiment', [MLController::class, 'analyzeSentiment']);

    // Sentiment Analysis - Asynchronous
    Route::post('/sentiment/async', [MLController::class, 'analyzeSentimentAsync'])
        ->middleware('throttle:30,1'); // Lower limit for async

    // Health Check
    Route::get('/health', [MLController::class, 'health']);

    // Metrics
    Route::get('/metrics', [MLController::class, 'metrics']);
});
