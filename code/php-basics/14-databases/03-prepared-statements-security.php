<?php

declare(strict_types=1);

/**
 * Prepared Statements and SQL Injection Prevention
 * 
 * Demonstrates why prepared statements are essential
 * and how they prevent SQL injection attacks.
 */

echo "=== Prepared Statements & Security ===" . PHP_EOL . PHP_EOL;

// Setup
$pdo = new PDO('sqlite:security_demo.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE users (
        id INTEGER PRIMARY KEY,
        username TEXT,
        password TEXT,
        role TEXT
    )
");

$pdo->exec("
    INSERT INTO users (username, password, role) VALUES
    ('admin', 'secret123', 'admin'),
    ('user1', 'pass123', 'user'),
    ('user2', 'pass456', 'user')
");

// DANGEROUS: SQL Injection vulnerability
echo "❌ DANGEROUS: String concatenation (DO NOT USE)" . PHP_EOL;
echo "Code: \$sql = \"SELECT * FROM users WHERE username = '\$username'\";" . PHP_EOL;
echo "Attack: Username: admin' OR '1'='1" . PHP_EOL;
echo "Result: Returns ALL users (security breach!)" . PHP_EOL;
echo PHP_EOL;

// SAFE: Prepared statements
echo "✅ SAFE: Prepared Statements" . PHP_EOL;

$username = "admin";
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

echo "Query: SELECT * FROM users WHERE username = :username" . PHP_EOL;
echo "Parameter: username = 'admin'" . PHP_EOL;
echo "Result: Found user '{$user['username']}' with role '{$user['role']}'" . PHP_EOL;
echo PHP_EOL;

// Attack attempt with prepared statement
echo "Attack Attempt with Prepared Statement:" . PHP_EOL;
$malicious = "admin' OR '1'='1";
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $malicious]);
$result = $stmt->fetch();

echo "Attempted username: $malicious" . PHP_EOL;
echo "Result: " . ($result ? "User found" : "No user found (attack prevented!)") . PHP_EOL;
echo PHP_EOL;

// Named parameters vs positional parameters
echo "Named vs Positional Parameters:" . PHP_EOL;

// Named parameters (recommended - clearer)
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND role = :role");
$stmt->execute(['username' => 'admin', 'role' => 'admin']);
echo "✓ Named parameters: Clear and readable" . PHP_EOL;

// Positional parameters
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
$stmt->execute(['admin', 'admin']);
echo "✓ Positional parameters: More concise" . PHP_EOL;
echo PHP_EOL;

// Binding parameters explicitly
echo "Explicit Parameter Binding:" . PHP_EOL;

$stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
$stmt->bindValue(':username', 'newuser', PDO::PARAM_STR);
$stmt->bindValue(':password', password_hash('secret', PASSWORD_DEFAULT), PDO::PARAM_STR);
$stmt->bindValue(':role', 'user', PDO::PARAM_STR);
$stmt->execute();

echo "✓ User created with explicit binding" . PHP_EOL;
echo PHP_EOL;

// IN clause with array
echo "IN Clause with Multiple Values:" . PHP_EOL;

$roles = ['admin', 'user'];
$placeholders = str_repeat('?,', count($roles) - 1) . '?';
$stmt = $pdo->prepare("SELECT * FROM users WHERE role IN ($placeholders)");
$stmt->execute($roles);
$users = $stmt->fetchAll();

echo "Found " . count($users) . " users with roles: " . implode(', ', $roles) . PHP_EOL;
foreach ($users as $user) {
    echo "  - {$user['username']} ({$user['role']})" . PHP_EOL;
}
echo PHP_EOL;

// Transactions for data integrity
echo "Transactions:" . PHP_EOL;

try {
    $pdo->beginTransaction();

    // Multiple related operations
    $pdo->prepare("UPDATE users SET role = ? WHERE username = ?")->execute(['admin', 'user1']);
    $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)")->execute(['user3', 'pass', 'user']);

    $pdo->commit();
    echo "✓ Transaction committed successfully" . PHP_EOL;
} catch (Exception $e) {
    $pdo->rollBack();
    echo "✗ Transaction rolled back: " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Best practices summary
echo "=== SECURITY BEST PRACTICES ===" . PHP_EOL;
echo "1. ✓ ALWAYS use prepared statements" . PHP_EOL;
echo "2. ✓ NEVER concatenate user input into SQL" . PHP_EOL;
echo "3. ✓ Use named parameters for clarity" . PHP_EOL;
echo "4. ✓ Validate input before querying" . PHP_EOL;
echo "5. ✓ Use transactions for related operations" . PHP_EOL;
echo "6. ✓ Hash passwords (never store plain text)" . PHP_EOL;
echo "7. ✓ Use PDO error mode ERRMODE_EXCEPTION" . PHP_EOL;
echo "8. ✓ Limit database user permissions" . PHP_EOL;

// Cleanup
unlink('security_demo.db');
