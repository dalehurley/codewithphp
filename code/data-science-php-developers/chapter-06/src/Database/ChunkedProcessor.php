<?php

declare(strict_types=1);

namespace DataScience\Database;

use PDO;
use Generator;

class ChunkedProcessor
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Process database results in chunks
     */
    public function processInChunks(
        string $table,
        int $chunkSize = 1000,
        ?string $orderBy = 'id',
        ?string $where = null
    ): Generator {
        $offset = 0;
        
        while (true) {
            $sql = "SELECT * FROM {$table}";
            
            if ($where !== null) {
                $sql .= " WHERE {$where}";
            }
            
            if ($orderBy !== null) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            $sql .= " LIMIT {$chunkSize} OFFSET {$offset}";
            
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($rows)) {
                break;
            }
            
            yield $rows;
            
            $offset += $chunkSize;
            
            // Free memory
            unset($rows, $stmt);
        }
    }
    
    /**
     * Process using cursor (more efficient for large datasets)
     */
    public function processWithCursor(
        string $sql,
        array $params = []
    ): Generator {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // Fetch one row at a time
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
        
        $stmt->closeCursor();
    }
    
    /**
     * Batch insert records efficiently
     */
    public function batchInsert(
        string $table,
        array $columns,
        Generator $dataGenerator,
        int $batchSize = 1000
    ): int {
        $batch = [];
        $totalInserted = 0;
        
        foreach ($dataGenerator as $row) {
            $batch[] = $row;
            
            if (count($batch) >= $batchSize) {
                $inserted = $this->insertBatch($table, $columns, $batch);
                $totalInserted += $inserted;
                $batch = [];
            }
        }
        
        // Insert remaining rows
        if (!empty($batch)) {
            $inserted = $this->insertBatch($table, $columns, $batch);
            $totalInserted += $inserted;
        }
        
        return $totalInserted;
    }
    
    /**
     * Insert a batch of records
     */
    private function insertBatch(
        string $table,
        array $columns,
        array $rows
    ): int {
        if (empty($rows)) {
            return 0;
        }
        
        $columnList = implode(', ', $columns);
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $values = implode(', ', array_fill(0, count($rows), $placeholders));
        
        $sql = "INSERT INTO {$table} ({$columnList}) VALUES {$values}";
        
        // Flatten row data
        $params = [];
        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $params[] = $row[$column] ?? null;
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Aggregate data while streaming
     */
    public function aggregate(
        string $sql,
        callable $aggregator,
        mixed $initial = null
    ): mixed {
        $result = $initial;
        
        foreach ($this->processWithCursor($sql) as $row) {
            $result = $aggregator($result, $row);
        }
        
        return $result;
    }
    
    /**
     * Count rows efficiently
     */
    public function count(string $table, ?string $where = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        
        if ($where !== null) {
            $sql .= " WHERE {$where}";
        }
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['count'];
    }
}
