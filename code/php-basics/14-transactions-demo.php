<?php

$dbPath = __DIR__ . '/data/database.sqlite';
$dsn = "sqlite:$dbPath";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create a users table for this demo
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL
        )
    ");

    echo "=== Transactions Demo ===" . PHP_EOL . PHP_EOL;

    // Example 1: Getting the last insert ID
    echo "1. Inserting a user and getting the ID:" . PHP_EOL;

    $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->execute(["Alice Johnson", "alice@example.com"]);

    $userId = $pdo->lastInsertId();
    echo "   User created with ID: $userId" . PHP_EOL . PHP_EOL;

    // Example 2: Successful transaction
    echo "2. Successful transaction (inserting 2 users):" . PHP_EOL;

    try {
        // Begin the transaction
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute(["Bob Smith", "bob@example.com"]);
        $bobId = $pdo->lastInsertId();

        $stmt->execute(["Carol White", "carol@example.com"]);
        $carolId = $pdo->lastInsertId();

        // Commit the transaction (make changes permanent)
        $pdo->commit();

        echo "   Transaction committed! Bob ID: $bobId, Carol ID: $carolId" . PHP_EOL . PHP_EOL;
    } catch (PDOException $e) {
        // Rollback if anything goes wrong
        $pdo->rollback();
        echo "   Transaction failed and rolled back: " . $e->getMessage() . PHP_EOL;
    }

    // Example 3: Failed transaction (rollback)
    echo "3. Failed transaction (will rollback):" . PHP_EOL;

    try {
        $pdo->beginTransaction();

        // Insert first user
        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute(["David Brown", "david@example.com"]);
        echo "   David inserted (not committed yet)..." . PHP_EOL;

        // Try to insert duplicate email (will fail)
        $stmt->execute(["Eve Black", "alice@example.com"]); // alice@example.com already exists!

        $pdo->commit();
    } catch (PDOException $e) {
        // Rollback - David won't be inserted either
        $pdo->rollback();
        echo "   Error occurred: " . $e->getMessage() . PHP_EOL;
        echo "   Transaction rolled back - David was NOT inserted." . PHP_EOL . PHP_EOL;
    }

    // Verify final state
    echo "4. Final user count:" . PHP_EOL;
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "   Total users: $count" . PHP_EOL;

    echo PHP_EOL . "Users in database:" . PHP_EOL;
    $stmt = $pdo->query("SELECT id, name, email FROM users");
    foreach ($stmt->fetchAll() as $user) {
        echo "   [{$user['id']}] {$user['name']} - {$user['email']}" . PHP_EOL;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . PHP_EOL);
}
