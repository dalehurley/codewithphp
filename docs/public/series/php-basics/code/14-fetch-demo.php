<?php

$dbPath = __DIR__ . '/data/database.sqlite';
$dsn = "sqlite:$dbPath";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "=== Fetching Data Demo ===" . PHP_EOL . PHP_EOL;

    // 1. Fetching ALL rows
    echo "1. Fetching ALL rows:" . PHP_EOL;
    $stmt = $pdo->query("SELECT * FROM posts");
    $allPosts = $stmt->fetchAll(); // Returns an array of all rows

    foreach ($allPosts as $post) {
        echo "  - ID: {$post['id']}, Title: {$post['title']}" . PHP_EOL;
    }
    echo PHP_EOL;

    // 2. Fetching a SINGLE row
    echo "2. Fetching a SINGLE row (ID = 2):" . PHP_EOL;
    $postId = 2;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $singlePost = $stmt->fetch(); // Returns a single row, or false if not found

    if ($singlePost) {
        echo "  - Title: {$singlePost['title']}" . PHP_EOL;
        echo "  - Content: {$singlePost['content']}" . PHP_EOL;
    } else {
        echo "  - Post not found." . PHP_EOL;
    }
    echo PHP_EOL;

    // 3. Fetching a single COLUMN from a row
    echo "3. Fetching a single COLUMN (just the title of ID = 3):" . PHP_EOL;
    $postId = 3;
    $stmt = $pdo->prepare("SELECT title FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $postTitle = $stmt->fetchColumn(); // Returns the value of the first column

    if ($postTitle) {
        echo "  - Title: $postTitle" . PHP_EOL;
    } else {
        echo "  - Post not found." . PHP_EOL;
    }
    echo PHP_EOL;

    // 4. Counting rows
    echo "4. Counting total posts:" . PHP_EOL;
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $count = $stmt->fetchColumn();
    echo "  - Total posts: $count" . PHP_EOL;
    echo PHP_EOL;

    // 5. Checking if a row exists
    echo "5. Checking if a post with ID = 999 exists:" . PHP_EOL;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([999]);
    $exists = $stmt->fetch();
    echo "  - Exists: " . ($exists ? "Yes" : "No") . PHP_EOL;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . PHP_EOL);
}
