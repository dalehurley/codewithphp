<?php

$dbPath = __DIR__ . '/data/database.sqlite';
$dsn = "sqlite:$dbPath";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            message TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "=== Batch Insert Demo ===" . PHP_EOL . PHP_EOL;

    // Method 1: Prepare once, execute many times (efficient)
    echo "Method 1: Prepare once, execute multiple times" . PHP_EOL;
    $startTime = microtime(true);

    $stmt = $pdo->prepare("INSERT INTO logs (message) VALUES (?)");

    // Simulate inserting 100 log entries
    for ($i = 1; $i <= 100; $i++) {
        $stmt->execute(["Log entry #$i"]);
    }

    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    echo "Inserted 100 records in {$elapsed}ms" . PHP_EOL . PHP_EOL;

    // Method 2: Using a transaction for even better performance
    echo "Method 2: Using a transaction for batch insert" . PHP_EOL;
    $startTime = microtime(true);

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO logs (message) VALUES (?)");

    for ($i = 101; $i <= 200; $i++) {
        $stmt->execute(["Log entry #$i"]);
    }

    $pdo->commit();

    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    echo "Inserted 100 records in {$elapsed}ms (with transaction - faster!)" . PHP_EOL . PHP_EOL;

    // Method 3: Multi-row insert (most efficient for many rows)
    echo "Method 3: Multi-row INSERT statement" . PHP_EOL;
    $startTime = microtime(true);

    // Build values for 100 inserts
    $values = [];
    $placeholders = [];

    for ($i = 201; $i <= 300; $i++) {
        $values[] = "Log entry #$i";
        $placeholders[] = "(?)";
    }

    $sql = "INSERT INTO logs (message) VALUES " . implode(", ", $placeholders);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    echo "Inserted 100 records in {$elapsed}ms (multi-row - fastest!)" . PHP_EOL . PHP_EOL;

    // Verify total count
    $stmt = $pdo->query("SELECT COUNT(*) FROM logs");
    $count = $stmt->fetchColumn();
    echo "Total log entries: $count" . PHP_EOL;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    die("Database error: " . $e->getMessage() . PHP_EOL);
}
