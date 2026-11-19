<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('_benchmark')->group(function (): void {
    Route::get('/ping', static fn (): JsonResponse => response()->json(['ok' => true]));

    Route::get('/runtime', static function (): JsonResponse {
        $opcacheAvailable = function_exists('opcache_get_status');
        $status = $opcacheAvailable ? opcache_get_status(false) : false;

        return response()->json([
            'php' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'entrypoint' => basename($_SERVER['SCRIPT_FILENAME'] ?? ''),
            'opcache_available' => $opcacheAvailable,
            'opcache' => $status['opcache_enabled'] ?? false,
            'jit_config' => ini_get('opcache.jit'),
            'jit' => ($status['jit']['enabled'] ?? false) || ($status['jit']['on'] ?? false),
        ]);
    });

    Route::get('/orders/per-row', static function (): JsonResponse {
        $orders = DB::table('benchmark_orders')
            ->select('id', 'customer_id')
            ->orderBy('id')
            ->get();

        $result = $orders->map(static fn (object $order): array => [
            'id' => $order->id,
            'customer' => DB::table('benchmark_customers')->where('id', $order->customer_id)->value('name'),
        ]);

        return response()->json($result);
    });

    Route::get('/orders/join', static function (): JsonResponse {
        $orders = DB::table('benchmark_orders')
            ->join('benchmark_customers', 'benchmark_customers.id', '=', 'benchmark_orders.customer_id')
            ->select('benchmark_orders.id', 'benchmark_customers.name as customer')
            ->orderBy('benchmark_orders.id')
            ->get();

        return response()->json($orders);
    });
});
