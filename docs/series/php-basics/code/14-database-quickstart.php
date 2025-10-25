<?php

// Create the database connection
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

    // Insert with prepared statement
    $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->execute(["Quick Start Post", "This demonstrates a secure database insert."]);

    // Fetch all posts
    $stmt = $pdo->query("SELECT * FROM posts");
    $posts = $stmt->fetchAll();

    echo "Success! Found " . count($posts) . " post(s).\n";
    print_r($posts);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
