<?php

use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/invoice-lab/sign-in', static function () {
    return response('The invoice lab signs in test users through its HTTP tests.', 401);
})->name('login');

Route::get('/invoice-lab/invoices/{invoice}', static function (string $invoice) {
    $headers = ['Cache-Control' => 'private, no-store'];
    $record = Invoice::query()->whereKey($invoice)->first();

    abort_unless($record !== null && Gate::allows('view', $record), 404, '', $headers);

    return response()->view('invoice-lab', ['invoice' => $record], 200, $headers);
})->middleware('auth')->where('invoice', '[1-9][0-9]{0,8}');
