<?php

// Add to routes/web.php inside the authenticated middleware group

use App\Http\Controllers\CompanyController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Companies resource routes
    Route::resource('companies', CompanyController::class);
});







