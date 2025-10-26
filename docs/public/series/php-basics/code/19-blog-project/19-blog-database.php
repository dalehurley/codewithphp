<?php

declare(strict_types=1);

/**
 * Chapter 19 Code Sample: Database Connection Class
 * 
 * This file demonstrates the singleton pattern for database connections.
 * Copy this to src/Core/Database.php in your simple-blog project.
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Get the singleton PDO instance.
     * 
     * @return PDO The database connection
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dbPath = __DIR__ . '/../../data/database.sqlite';
            $dsn = "sqlite:$dbPath";

            try {
                self::$instance = new PDO($dsn);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Prevent cloning of the singleton instance.
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the singleton instance.
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
