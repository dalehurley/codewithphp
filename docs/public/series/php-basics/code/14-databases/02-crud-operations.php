<?php

declare(strict_types=1);

/**
 * CRUD Operations with PDO
 * 
 * Complete examples of Create, Read, Update, Delete operations
 * using prepared statements for security.
 */

echo "=== CRUD Operations ===" . PHP_EOL . PHP_EOL;

// Setup: Create database and table
$pdo = new PDO('sqlite:users.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        age INTEGER,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
");
echo "✓ Table created" . PHP_EOL . PHP_EOL;

// CREATE - Insert data
echo "=== CREATE (Insert) ===" . PHP_EOL;

$stmt = $pdo->prepare("
    INSERT INTO users (name, email, age) 
    VALUES (:name, :email, :age)
");

$stmt->execute([
    'name' => 'Alice Johnson',
    'email' => 'alice@example.com',
    'age' => 28
]);
$alice_id = $pdo->lastInsertId();
echo "✓ Inserted Alice (ID: $alice_id)" . PHP_EOL;

$stmt->execute([
    'name' => 'Bob Smith',
    'email' => 'bob@example.com',
    'age' => 35
]);
$bob_id = $pdo->lastInsertId();
echo "✓ Inserted Bob (ID: $bob_id)" . PHP_EOL;

$stmt->execute([
    'name' => 'Charlie Brown',
    'email' => 'charlie@example.com',
    'age' => 42
]);
echo "✓ Inserted Charlie" . PHP_EOL;
echo PHP_EOL;

// READ - Select data
echo "=== READ (Select) ===" . PHP_EOL;

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

echo "All users:" . PHP_EOL;
foreach ($users as $user) {
    echo "  [{$user['id']}] {$user['name']} ({$user['email']}) - Age: {$user['age']}" . PHP_EOL;
}
echo PHP_EOL;

// Fetch single user by ID
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $alice_id]);
$user = $stmt->fetch();

echo "Single user (Alice):" . PHP_EOL;
echo "  Name: {$user['name']}" . PHP_EOL;
echo "  Email: {$user['email']}" . PHP_EOL;
echo "  Age: {$user['age']}" . PHP_EOL;
echo PHP_EOL;

// Fetch with WHERE clause
$stmt = $pdo->prepare("SELECT * FROM users WHERE age > :age");
$stmt->execute(['age' => 30]);
$older_users = $stmt->fetchAll();

echo "Users older than 30:" . PHP_EOL;
foreach ($older_users as $user) {
    echo "  {$user['name']} - Age: {$user['age']}" . PHP_EOL;
}
echo PHP_EOL;

// UPDATE - Modify data
echo "=== UPDATE ===" . PHP_EOL;

$stmt = $pdo->prepare("
    UPDATE users 
    SET age = :age, name = :name 
    WHERE id = :id
");

$stmt->execute([
    'age' => 29,
    'name' => 'Alice Johnson-Smith',
    'id' => $alice_id
]);

echo "✓ Updated Alice's record ({$stmt->rowCount()} row affected)" . PHP_EOL;

// Verify update
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $alice_id]);
$user = $stmt->fetch();
echo "  New name: {$user['name']}, New age: {$user['age']}" . PHP_EOL;
echo PHP_EOL;

// DELETE - Remove data
echo "=== DELETE ===" . PHP_EOL;

$stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
$stmt->execute(['id' => $bob_id]);

echo "✓ Deleted Bob ({$stmt->rowCount()} row affected)" . PHP_EOL;

// Verify deletion
$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
echo "Total users remaining: $count" . PHP_EOL;
echo PHP_EOL;

// Count and Aggregate functions
echo "=== AGGREGATE FUNCTIONS ===" . PHP_EOL;

$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
echo "Total users: $count" . PHP_EOL;

$avgAge = $pdo->query("SELECT AVG(age) FROM users")->fetchColumn();
echo "Average age: " . round($avgAge, 1) . PHP_EOL;

$maxAge = $pdo->query("SELECT MAX(age) FROM users")->fetchColumn();
echo "Oldest user: $maxAge years" . PHP_EOL;
echo PHP_EOL;

// Practical example: User management class
echo "=== PRACTICAL EXAMPLE: UserRepository ===" . PHP_EOL;

class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(string $name, string $email, int $age): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, age) VALUES (:name, :email, :age)
        ");
        $stmt->execute(compact('name', 'email', 'age'));
        return (int)$this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findAll(): array
    {
        return $this->pdo->query("SELECT * FROM users")->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

$userRepo = new UserRepository($pdo);

$newId = $userRepo->create('Diana Prince', 'diana@example.com', 30);
echo "✓ Created Diana (ID: $newId)" . PHP_EOL;

$user = $userRepo->findById($newId);
echo "✓ Found: {$user['name']}" . PHP_EOL;

$userRepo->update($newId, ['age' => 31]);
echo "✓ Updated Diana's age" . PHP_EOL;

echo "All users:" . PHP_EOL;
foreach ($userRepo->findAll() as $u) {
    echo "  - {$u['name']}" . PHP_EOL;
}

// Cleanup
unlink('users.db');
echo PHP_EOL . "✓ Cleanup complete" . PHP_EOL;
