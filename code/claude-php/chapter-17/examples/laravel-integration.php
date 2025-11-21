<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "Laravel Integration Example\n\n";

// Simulated Laravel Service Provider
class ClaudeServiceProvider
{
    public function register($app): void
    {
        $app->singleton('claude', function ($app) {
            return new \ClaudePHP\Service\ClaudeService(
                apiKey: config('services.claude.api_key'),
                model: config('services.claude.model', 'claude-sonnet-4-5')
            );
        });
    }

    public function boot(): void
    {
        // Publish configuration
        echo "✓ Published config/claude.php\n";
    }
}

// Simulated config/claude.php
function config($key, $default = null)
{
    $configs = [
        'services.claude.api_key' => $_ENV['ANTHROPIC_API_KEY'] ?? 'test-key',
        'services.claude.model' => 'claude-sonnet-4-5',
    ];
    return $configs[$key] ?? $default;
}

// Usage in Laravel Controller
class ChatController
{
    public function __construct(private \ClaudePHP\Service\ClaudeService $claude)
    {
    }

    public function chat(string $message): string
    {
        return $this->claude->chat($message);
    }
}

echo "\nExample Laravel Integration:\n";
echo "1. Service Provider: ClaudeServiceProvider\n";
echo "2. Facade: Claude::chat(\$message)\n";
echo "3. Controller injection: public function __construct(ClaudeService \$claude)\n";
echo "\n✓ Integration pattern demonstrated\n";
