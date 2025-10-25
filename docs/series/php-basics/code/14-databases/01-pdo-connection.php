<?php

declare(strict_types=1);

/**
 * PDO Database Connection
 * 
 * Demonstrates how to connect to a database using PDO
 * with proper error handling and configuration.
 */

echo "=== PDO Database Connection ===" . PHP_EOL . PHP_EOL;

// Example 1: Basic SQLite connection (no server needed)
echo "1. SQLite Connection:" . PHP_EOL;

try {
    $pdo = new PDO('sqlite:blog.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "✓ Connected to SQLite database successfully!" . PHP_EOL;
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . PHP_EOL;
    exit;
}
echo PHP_EOL;

// Example 2: MySQL connection (commented out - requires MySQL server)
echo "2. MySQL Connection Example (commented):" . PHP_EOL;
echo <<<'CODE'
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=my_database;charset=utf8mb4',
        'username',
        'password',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    echo "Connected to MySQL!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
CODE;
echo PHP_EOL . PHP_EOL;

// Example 3: Connection function for reuse
echo "3. Reusable Connection Function:" . PHP_EOL;

function getDatabaseConnection(string $dsn = 'sqlite:blog.db'): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            echo "Database connection established" . PHP_EOL;
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

$db = getDatabaseConnection();
echo "Connection object created" . PHP_EOL;
$db2 = getDatabaseConnection();  // Reuses same connection
echo "Same connection reused (singleton pattern)" . PHP_EOL;
echo PHP_EOL;

// Example 4: Test the connection with a simple query
echo "4. Testing Connection:" . PHP_EOL;

try {
    $version = $pdo->query('SELECT sqlite_version()')->fetchColumn();
    echo "SQLite version: $version" . PHP_EOL;
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Example 5: Database configuration class
echo "5. Database Configuration Class:" . PHP_EOL;

class DatabaseConfig
{
    public const DEFAULT_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public static function connect(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        array $options = []
    ): PDO {
        $options = array_merge(self::DEFAULT_OPTIONS, $options);

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log error (in production)
            error_log("Database connection error: " . $e->getMessage());

            // Don't expose detailed errors to users
            throw new RuntimeException("Database connection failed");
        }
    }

    public static function sqlite(string $path = 'database.db'): PDO
    {
        return self::connect("sqlite:$path");
    }

    public static function mysql(
        string $host,
        string $database,
        string $username,
        string $password,
        int $port = 3306
    ): PDO {
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        return self::connect($dsn, $username, $password);
    }
}

// Usage
$db3 = DatabaseConfig::sqlite('blog.db');
echo "Connected using DatabaseConfig class" . PHP_EOL;
echo PHP_EOL;

// Cleanup
unlink('blog.db');  // Delete test database file
echo "Test database cleaned up" . PHP_EOL;
