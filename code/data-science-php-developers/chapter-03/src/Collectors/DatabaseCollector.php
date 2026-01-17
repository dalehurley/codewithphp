<?php

declare(strict_types=1);

namespace DataScience\Collectors;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseCollector
{
    private PDO $pdo;
    private int $chunkSize;
    
    public function __construct(
        string $dsn,
        string $username,
        string $password,
        int $chunkSize = 1000
    ) {
        $this->chunkSize = $chunkSize;
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database connection failed: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Collect data in chunks to avoid memory issues
     */
    public function collect(
        string $query,
        array $params = [],
        ?callable $callback = null
    ): array {
        $allData = [];
        $offset = 0;
        
        while (true) {
            // Add pagination to query
            $paginatedQuery = $query . " LIMIT {$this->chunkSize} OFFSET {$offset}";
            
            try {
                $stmt = $this->pdo->prepare($paginatedQuery);
                $stmt->execute($params);
                $chunk = $stmt->fetchAll();
                
                if (empty($chunk)) {
                    break; // No more data
                }
                
                // Apply callback transformation if provided
                if ($callback) {
                    $chunk = array_map($callback, $chunk);
                }
                
                $allData = array_merge($allData, $chunk);
                $offset += $this->chunkSize;
                
                // Log progress
                echo "Collected " . count($allData) . " records...\n";
                
            } catch (PDOException $e) {
                throw new RuntimeException(
                    "Query failed at offset {$offset}: " . $e->getMessage()
                );
            }
        }
        
        return $allData;
    }
    
    /**
     * Collect with generator for memory efficiency (large datasets)
     */
    public function collectGenerator(
        string $query,
        array $params = []
    ): \Generator {
        $offset = 0;
        
        while (true) {
            $paginatedQuery = $query . " LIMIT {$this->chunkSize} OFFSET {$offset}";
            
            try {
                $stmt = $this->pdo->prepare($paginatedQuery);
                $stmt->execute($params);
                $chunk = $stmt->fetchAll();
                
                if (empty($chunk)) {
                    break;
                }
                
                foreach ($chunk as $row) {
                    yield $row;
                }
                
                $offset += $this->chunkSize;
                
            } catch (PDOException $e) {
                throw new RuntimeException(
                    "Query failed at offset {$offset}: " . $e->getMessage()
                );
            }
        }
    }
    
    /**
     * Get aggregate statistics without loading all data
     */
    public function getStats(string $table, string $column): array
    {
        $query = "
            SELECT 
                COUNT(*) as count,
                MIN({$column}) as min,
                MAX({$column}) as max,
                AVG({$column}) as avg
            FROM {$table}
        ";
        
        $stmt = $this->pdo->query($query);
        return $stmt->fetch();
    }
}


