<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ML\SentimentAnalysisService;
use Illuminate\Support\ServiceProvider;

class MLServiceProvider extends ServiceProvider
{
    /**
     * Register ML services.
     */
    public function register(): void
    {
        // Bind SentimentAnalysisService as singleton
        // This ensures the model loads only once per request
        $this->app->singleton(SentimentAnalysisService::class, function ($app) {
            return new SentimentAnalysisService();
        });

        // Add more ML services here as you build them:
        // $this->app->singleton(ProductRecommendationService::class, ...);
        // $this->app->singleton(ChatbotService::class, ...);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

