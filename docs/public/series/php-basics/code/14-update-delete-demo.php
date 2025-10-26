<?php

$dbPath = __DIR__ . '/data/database.sqlite';
$dsn = "sqlite:$dbPath";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // UPDATE: Change the title of post with id = 1
    echo "Updating post with ID = 1..." . PHP_EOL;
    $stmt = $pdo->prepare("UPDATE posts SET title = ? WHERE id = ?");
    $stmt->execute(["My Updated Post Title", 1]);

    $rowsAffected = $stmt->rowCount();
    echo "Rows updated: $rowsAffected" . PHP_EOL . PHP_EOL;

    // DELETE: Remove post with id = 2
    echo "Deleting post with ID = 2..." . PHP_EOL;
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([2]);

    $rowsAffected = $stmt->rowCount();
    echo "Rows deleted: $rowsAffected" . PHP_EOL . PHP_EOL;

    // Verify changes
    echo "Remaining posts:" . PHP_EOL;
    $stmt = $pdo->query("SELECT id, title FROM posts");
    $posts = $stmt->fetchAll();

    foreach ($posts as $post) {
        echo "  - ID: {$post['id']}, Title: {$post['title']}" . PHP_EOL;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . PHP_EOL);
}
