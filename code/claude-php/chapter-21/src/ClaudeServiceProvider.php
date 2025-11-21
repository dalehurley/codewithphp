<?php

declare(strict_types=1);

namespace ClaudePhp\LaravelIntegration;

use ClaudePhp\ClaudePhp;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

/**
 * Claude Service Provider for Laravel
 *
 * Registers Claude AI services and bindings
 */
class ClaudeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/claude.php', 'claude'
        );

        // Register the main Claude client
        $this->app->singleton('claude', function (Application $app) {
            return new ClaudePhp(
                    apiKey: config('claude.api_key')
                );
        });

        // Register the Claude service
        $this->app->singleton(ClaudeService::class, function (Application $app) {
            return new ClaudeService(
                client: $app->make('claude'),
                cache: $app->make('cache')->store(config('claude.cache.driver')),
                logger: $app->make('log')
            );
        });

        // Register the prompt manager
        $this->app->singleton(PromptManager::class, function (Application $app) {
            return new PromptManager(
                path: config('claude.prompts.path'),
                cache: $app->make('cache')->store()
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/claude.php' => config_path('claude.php'),
            ], 'claude-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'claude-migrations');

            // Register commands
            $this->commands([
                Console\Commands\ClaudeChatCommand::class,
                Console\Commands\ClaudeTestCommand::class,
            ]);
        }

        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'claude');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/claude'),
        ], 'claude-views');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'claude',
            ClaudeService::class,
            PromptManager::class,
        ];
    }
}
