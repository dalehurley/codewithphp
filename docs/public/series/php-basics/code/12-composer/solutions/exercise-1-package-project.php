<?php

declare(strict_types=1);

/**
 * Exercise 1: Build a Project with Composer Packages
 * 
 * This demonstrates using popular Composer packages in a real project
 * 
 * Required packages (run: composer require):
 * - vlucas/phpdotenv (Environment variables)
 * - monolog/monolog (Logging)
 * - guzzlehttp/guzzle (HTTP client)
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

echo "=== Composer Package Integration Demo ===" . PHP_EOL . PHP_EOL;

// 1. Setup Environment Variables with phpdotenv
echo "1. Environment Variables (phpdotenv):" . PHP_EOL;

// Create .env file if it doesn't exist
if (!file_exists(__DIR__ . '/.env')) {
    file_put_contents(__DIR__ . '/.env', "APP_NAME=MyApp
APP_ENV=development
LOG_LEVEL=debug
API_URL=https://jsonplaceholder.typicode.com
");
    echo "✓ Created .env file" . PHP_EOL;
}

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "  APP_NAME: " . $_ENV['APP_NAME'] . PHP_EOL;
echo "  APP_ENV: " . $_ENV['APP_ENV'] . PHP_EOL;
echo PHP_EOL;

// 2. Setup Logging with Monolog
echo "2. Logging (Monolog):" . PHP_EOL;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__ . '/app.log', Logger::DEBUG));
$log->pushHandler(new FirePHPHandler());

$log->info('Application started', ['env' => $_ENV['APP_ENV']]);
$log->debug('Debug information', ['user_id' => 123]);

echo "✓ Logs written to app.log" . PHP_EOL;
echo PHP_EOL;

// 3. HTTP Client with Guzzle
echo "3. HTTP Client (Guzzle):" . PHP_EOL;

$client = new Client([
    'base_uri' => $_ENV['API_URL'],
    'timeout' => 30.0,
]);

try {
    // GET request
    $response = $client->request('GET', '/posts/1');
    $data = json_decode($response->getBody()->getContents(), true);

    echo "✓ GET request successful" . PHP_EOL;
    echo "  Status: " . $response->getStatusCode() . PHP_EOL;
    echo "  Post title: " . $data['title'] . PHP_EOL;

    $log->info('API request successful', [
        'endpoint' => '/posts/1',
        'status' => $response->getStatusCode()
    ]);
} catch (GuzzleException $e) {
    echo "✗ HTTP request failed: " . $e->getMessage() . PHP_EOL;

    $log->error('API request failed', [
        'error' => $e->getMessage()
    ]);
}

echo PHP_EOL;

// 4. Build a Complete Feature
echo "4. Complete Feature - User Fetcher:" . PHP_EOL;

class UserService
{
    private Client $client;
    private Logger $logger;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function getUser(int $id): ?array
    {
        try {
            $this->logger->info("Fetching user", ['user_id' => $id]);

            $response = $this->client->request('GET', "/users/$id");
            $user = json_decode($response->getBody()->getContents(), true);

            $this->logger->info("User fetched successfully", [
                'user_id' => $id,
                'username' => $user['username']
            ]);

            return $user;
        } catch (GuzzleException $e) {
            $this->logger->error("Failed to fetch user", [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    public function getAllUsers(): array
    {
        try {
            $this->logger->info("Fetching all users");

            $response = $this->client->request('GET', '/users');
            $users = json_decode($response->getBody()->getContents(), true);

            $this->logger->info("All users fetched", [
                'count' => count($users)
            ]);

            return $users;
        } catch (GuzzleException $e) {
            $this->logger->error("Failed to fetch users", [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }
}

$userService = new UserService($client, $log);

// Fetch single user
$user = $userService->getUser(1);
if ($user) {
    echo "✓ User fetched: {$user['name']} ({$user['email']})" . PHP_EOL;
}

// Fetch all users
$users = $userService->getAllUsers();
echo "✓ Fetched " . count($users) . " users" . PHP_EOL;

echo PHP_EOL;

// 5. Configuration Management
echo "5. Configuration Class:" . PHP_EOL;

class Config
{
    private static ?Config $instance = null;
    private array $config;

    private function __construct()
    {
        $this->config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'DefaultApp',
                'env' => $_ENV['APP_ENV'] ?? 'production',
            ],
            'api' => [
                'url' => $_ENV['API_URL'] ?? '',
                'timeout' => 30,
            ],
            'logging' => [
                'level' => $_ENV['LOG_LEVEL'] ?? 'info',
                'path' => __DIR__ . '/app.log',
            ],
        ];
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

$config = Config::getInstance();
echo "  App name: " . $config->get('app.name') . PHP_EOL;
echo "  API URL: " . $config->get('api.url') . PHP_EOL;
echo "  Log level: " . $config->get('logging.level') . PHP_EOL;

echo PHP_EOL;
echo "✓ Composer package integration complete!" . PHP_EOL;
echo PHP_EOL;

echo "📦 Installed Packages:" . PHP_EOL;
echo "  - vlucas/phpdotenv: Environment variable management" . PHP_EOL;
echo "  - monolog/monolog: Flexible logging library" . PHP_EOL;
echo "  - guzzlehttp/guzzle: Powerful HTTP client" . PHP_EOL;
echo PHP_EOL;

echo "💡 To run this example:" . PHP_EOL;
echo "  1. composer require vlucas/phpdotenv monolog/monolog guzzlehttp/guzzle" . PHP_EOL;
echo "  2. php exercise-1-package-project.php" . PHP_EOL;
