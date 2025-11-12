<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ML Demo Page
Route::get('/ml-demo', function () {
    return view('ml-demo');
})->name('ml.demo');

// Sentiment Analysis Demo (alternative name)
Route::get('/sentiment-demo', function () {
    return view('ml-demo');
})->name('sentiment.demo');
