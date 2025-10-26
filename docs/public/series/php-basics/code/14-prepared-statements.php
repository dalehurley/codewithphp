<?php

$dbPath = __DIR__ . '/data/database.sqlite';
$dsn = "sqlite:$dbPath";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT NOT NULL
        )
    ");

    // Method 1: Anonymous placeholders with ?
    $title = "My Second Post";
    $content = "Content for the second post.";

    // 1. Prepare the SQL statement with anonymous placeholders (?)
    $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");

    // 2. Execute the statement with the data
    // The array values must be in the same order as the placeholders
    $stmt->execute([$title, $content]);

    echo "Post created with anonymous placeholders (?)." . PHP_EOL;

    // Method 2: Named placeholders with :name
    $title3 = "My Third Post";
    $content3 = "Content for the third post.";

    $stmt = $pdo->prepare("
        INSERT INTO posts (title, content) VALUES (:post_title, :post_content)
    ");

    // With named placeholders, the array keys must match the placeholder names
    $stmt->execute([
        ':post_title' => $title3,
        ':post_content' => $content3
    ]);

    echo "Post created with named placeholders (:name)." . PHP_EOL;

    // Even if someone tries SQL injection, it's treated as literal text
    $maliciousTitle = "Harmless Title'); DROP TABLE posts; --";
    $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->execute([$maliciousTitle, "This is safe!"]);

    echo "Even malicious-looking data was inserted safely." . PHP_EOL;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . PHP_EOL);
}
