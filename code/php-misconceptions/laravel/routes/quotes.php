<?php

declare(strict_types=1);

use App\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/quotes', QuoteController::class);
