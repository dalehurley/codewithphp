#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CodeReview\PullRequestAnalyzer;
use CodeReview\GithubWebhookHandler;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$analyzer = new PullRequestAnalyzer($_ENV['ANTHROPIC_API_KEY']);

// Example: Review a file
$code = <<<'PHP'
<?php
function getUserData($id) {
    $db = new PDO('mysql:host=localhost;dbname=test', 'root', '');
    $result = $db->query("SELECT * FROM users WHERE id = " . $id);
    return $result->fetch();
}
PHP;

echo "Analyzing code...\n\n";

// Security check
$security = $analyzer->securityCheck($code);
echo "Security Analysis:\n";
echo $security['summary'] . "\n\n";

// Suggest improvements
$improvements = $analyzer->suggestImprovements($code);
echo "Suggested Improvements:\n";
echo $improvements['suggestions'] . "\n\n";

// Review specific file
$review = $analyzer->reviewFile(
    'src/User.php',
    $code,
    '+function getUserData($id) {...}'
);
echo "File Review:\n";
echo $review['review'] . "\n";
echo "Severity: " . $review['severity'] . "\n";
