<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Service\ClaudeService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Dependency Injection Example\n\n";

// Simple DI container
class Container
{
    private array $services = [];

    public function register(string $id, callable $factory): void
    {
        $this->services[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->services[$id])) {
            throw new \Exception("Service {$id} not found");
        }
        return ($this->services[$id])($this);
    }
}

// Configure container
$container = new Container();

$container->register('claude', function($c) {
    return new ClaudeService(
        apiKey: $_ENV['ANTHROPIC_API_KEY'],
        model: $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-20250514'
    );
});

$container->register('chatbot', function($c) {
    $claude = $c->get('claude');
    return new class($claude) {
        public function __construct(private ClaudeService $claude) {}

        public function answer(string $question): string
        {
            return $this->claude->chat($question, 'You are a helpful assistant');
        }
    };
});

// Use services
$chatbot = $container->get('chatbot');
$answer = $chatbot->answer('What is 2+2?');
echo "Answer: {$answer}\n";
