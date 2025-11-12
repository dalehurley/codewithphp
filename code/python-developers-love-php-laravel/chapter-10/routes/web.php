<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle-complete');
});

require __DIR__.'/auth.php';

