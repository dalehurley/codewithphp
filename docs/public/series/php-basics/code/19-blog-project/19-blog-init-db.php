<?php

declare(strict_types=1);

/**
 * Chapter 19 Code Sample: Database Initialization Script
 * 
 * Run this script once to create the database schema.
 * Save as init-db.php in your project root and run: php init-db.php
 */

require 'vendor/autoload.php';

use App\Core\Database;

echo "Initializing database...\n";

try {
    $pdo = Database::getInstance();

    // Create posts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "✓ Table 'posts' created successfully.\n";

    // Optionally, insert sample data for testing
    $sampleDataExists = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();

    if ($sampleDataExists == 0) {
        echo "\nNo existing posts found. Creating sample data...\n";

        $samplePosts = [
            [
                'title' => 'Welcome to My Blog',
                'content' => 'This is the first post on my new PHP blog. I built this from scratch following the Code with PHP tutorial series. It features a clean MVC architecture, routing, and a SQLite database.'
            ],
            [
                'title' => 'Understanding MVC Architecture',
                'content' => 'MVC stands for Model-View-Controller. The Model handles data and business logic, the View handles presentation, and the Controller coordinates between them. This separation of concerns makes code more maintainable and testable.'
            ],
            [
                'title' => 'PHP 8.4 Features',
                'content' => 'PHP 8.4 introduces several exciting features including property hooks, asymmetric visibility, and improved type system. These features make PHP more expressive and help catch bugs earlier in the development process.'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");

        foreach ($samplePosts as $post) {
            $stmt->execute([$post['title'], $post['content']]);
            echo "  ✓ Created: {$post['title']}\n";
        }

        echo "\n✓ Sample data inserted successfully.\n";
    } else {
        echo "✓ Posts table already contains data ({$sampleDataExists} posts).\n";
    }

    echo "\n✓ Database initialization complete!\n";
    echo "✓ Database file: data/database.sqlite\n";
    echo "\nYou can now start the development server:\n";
    echo "  php -S localhost:8000 -t public\n\n";
} catch (Exception $e) {
    die("✗ Database initialization failed: " . $e->getMessage() . "\n");
}
