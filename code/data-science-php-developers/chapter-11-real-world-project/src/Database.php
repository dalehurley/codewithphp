<?php

declare(strict_types=1);

namespace SmartRecommender;

use PDO;

class Database
{
    private static ?PDO $instance = null;
    private static bool $connecting = false;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            if (self::$connecting) {
                throw new \RuntimeException('Recursive database connection attempt detected');
            }
            
            self::$connecting = true;
            
            try {
                $config = require __DIR__ . '/../config/database.php';
                
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    $config['host'],
                    $config['database'],
                    $config['charset']
                );
                
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } finally {
                self::$connecting = false;
            }
        }
        
        return self::$instance;
    }
    
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
