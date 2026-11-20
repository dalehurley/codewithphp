<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use App\Profiling\RequestTrace;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config([
            'database.redis.cache.timeout' => 0.2,
            'database.redis.cache.read_timeout' => 0.2,
        ]);
    }

    public function boot(): void
    {
        Product::observe(ProductObserver::class);

        if (!$this->app->environment('lab', 'testing')) {
            return;
        }

        Event::listen(QueryExecuted::class, static function (QueryExecuted $query): void {
            $trace = request()->attributes->get(RequestTrace::class);

            if ($trace instanceof RequestTrace) {
                $trace->recordQuery((float) $query->time);
            }
        });
    }
}
