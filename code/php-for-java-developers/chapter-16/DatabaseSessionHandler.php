<?php

declare(strict_types=1);

namespace App\Session;

use PDO;
use SessionHandlerInterface;

/**
 * Database session handler
 * Stores session data in a database instead of files
 * Useful for load-balanced applications and better scalability
 */
class DatabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo) {}

    /**
     * Initialize session (called when session_start() is called)
     */
    public function open(string $path, string $name): bool
    {
        return true; // Database connection already established
    }

    /**
     * Close session (called when script ends)
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Read session data
     */
    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT data FROM sessions WHERE id = ? AND last_activity > ?'
        );
        $stmt->execute([$id, time() - 1800]); // 30 minutes timeout
        
        $data = $stmt->fetchColumn();
        return $data !== false ? $data : '';
    }

    /**
     * Write session data
     */
    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'REPLACE INTO sessions (id, data, last_activity) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$id, $data, time()]);
    }

    /**
     * Destroy session
     */
    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Garbage collection (clean up old sessions)
     */
    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM sessions WHERE last_activity < ?'
        );
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount() ?: 0;
    }
}



