<?php

declare(strict_types=1);

$pdo = new PDO('sqlite::memory:', options: [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT NOT NULL, author TEXT NOT NULL)');

$author = "O'Neil";
$insert = $pdo->prepare('INSERT INTO posts (title, author) VALUES (:title, :author)');
$insert->execute(['title' => 'First post', 'author' => $author]);

$select = $pdo->prepare('SELECT title FROM posts WHERE author = :author');
$select->execute(['author' => $author]);

echo $select->fetchColumn(), PHP_EOL;
