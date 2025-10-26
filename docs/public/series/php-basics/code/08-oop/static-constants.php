<?php

declare(strict_types=1);

/**
 * Static Members and Class Constants
 * 
 * Demonstrates:
 * - Static properties and methods
 * - Class constants
 * - self:: and static:: keywords
 * - Practical use cases
 */

echo "=== Static Members and Constants ===" . PHP_EOL . PHP_EOL;

// Example 1: Class constants
echo "1. Class Constants:" . PHP_EOL;

class MathHelper
{
    public const PI = 3.14159265359;
    public const E = 2.71828182846;

    public static function circleArea(float $radius): float
    {
        return self::PI * $radius * $radius;
    }

    public static function circleCircumference(float $radius): float
    {
        return 2 * self::PI * $radius;
    }
}

echo "PI: " . MathHelper::PI . PHP_EOL;
echo "E: " . MathHelper::E . PHP_EOL;
echo "Circle area (r=5): " . MathHelper::circleArea(5) . PHP_EOL;
echo "Circle circumference (r=5): " . MathHelper::circleCircumference(5) . PHP_EOL;
echo PHP_EOL;

// Example 2: Static properties
echo "2. Static Properties:" . PHP_EOL;

class Counter
{
    private static int $count = 0;

    public function __construct()
    {
        self::$count++;
    }

    public static function getCount(): int
    {
        return self::$count;
    }

    public static function reset(): void
    {
        self::$count = 0;
    }
}

echo "Initial count: " . Counter::getCount() . PHP_EOL;
$c1 = new Counter();
$c2 = new Counter();
$c3 = new Counter();
echo "After creating 3 objects: " . Counter::getCount() . PHP_EOL;
Counter::reset();
echo "After reset: " . Counter::getCount() . PHP_EOL;
echo PHP_EOL;

// Example 3: Configuration class
echo "3. Configuration Class:" . PHP_EOL;

class Config
{
    public const APP_NAME = "My Application";
    public const APP_VERSION = "1.0.0";
    public const DEBUG_MODE = true;

    private static array $settings = [];

    public static function set(string $key, mixed $value): void
    {
        self::$settings[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$settings[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset(self::$settings[$key]);
    }

    public static function getAppInfo(): array
    {
        return [
            'name' => self::APP_NAME,
            'version' => self::APP_VERSION,
            'debug' => self::DEBUG_MODE
        ];
    }
}

Config::set('database', 'mysql');
Config::set('host', 'localhost');

echo "App Name: " . Config::APP_NAME . PHP_EOL;
echo "Database: " . Config::get('database') . PHP_EOL;
echo "Has 'database': " . (Config::has('database') ? 'Yes' : 'No') . PHP_EOL;
echo "Has 'missing': " . (Config::has('missing') ? 'Yes' : 'No') . PHP_EOL;
print_r(Config::getAppInfo());
echo PHP_EOL;

// Example 4: Singleton pattern (common static use case)
echo "4. Singleton Pattern:" . PHP_EOL;

class Database
{
    private static ?Database $instance = null;
    private string $connection;

    // Private constructor prevents direct instantiation
    private function __construct()
    {
        $this->connection = "Database connection established";
        echo "Creating new database connection..." . PHP_EOL;
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql): string
    {
        return "Executing: $sql";
    }
}

$db1 = Database::getInstance();
echo $db1->query("SELECT * FROM users") . PHP_EOL;

$db2 = Database::getInstance();  // Uses same instance, doesn't create new connection
echo "Same instance? " . ($db1 === $db2 ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 5: Utility class with only static methods
echo "5. Utility Class:" . PHP_EOL;

class StringUtil
{
    // No constructor needed - all methods are static

    public static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    public static function truncate(string $text, int $length, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }

    public static function randomString(int $length = 10): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $result;
    }
}

echo "Slug: " . StringUtil::slugify("Hello World! This is PHP") . PHP_EOL;
echo "Truncate: " . StringUtil::truncate("This is a very long text", 15) . PHP_EOL;
echo "Random: " . StringUtil::randomString(12) . PHP_EOL;
echo PHP_EOL;

// Example 6: Status codes constant
echo "6. Status Codes with Constants:" . PHP_EOL;

class HttpStatus
{
    public const OK = 200;
    public const CREATED = 201;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const SERVER_ERROR = 500;

    public static function getMessage(int $code): string
    {
        return match ($code) {
            self::OK => 'OK',
            self::CREATED => 'Created',
            self::BAD_REQUEST => 'Bad Request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Not Found',
            self::SERVER_ERROR => 'Internal Server Error',
            default => 'Unknown Status'
        };
    }
}

echo "Status 200: " . HttpStatus::getMessage(HttpStatus::OK) . PHP_EOL;
echo "Status 404: " . HttpStatus::getMessage(HttpStatus::NOT_FOUND) . PHP_EOL;
echo "Status 500: " . HttpStatus::getMessage(HttpStatus::SERVER_ERROR) . PHP_EOL;
