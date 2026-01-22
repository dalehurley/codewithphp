---
title: "09: Working with Databases"
description: "PDO vs JDBC, prepared statements, database connections"
sidebar:
  label: "09: Working with Databases"
  order: 9
  badge:
    text: Intermediate
    variant: caution
---

![Database Hero](/images/php-for-java-developers/chapter-09-database-hero-full.webp)

# Chapter 9: Working with Databases

<Badge type="warning">Intermediate</Badge> <Badge type="info">75-90 min</Badge>

## Overview

PHP Data Objects (PDO) is PHP's database abstraction layer—similar to JDBC in Java. It provides a consistent interface for working with multiple database systems (MySQL, PostgreSQL, SQLite, etc.). If you're familiar with JDBC's `Connection`, `PreparedStatement`, and `ResultSet` classes, you'll find PDO's API refreshingly similar yet more streamlined.

In this chapter, you'll learn how to safely and efficiently work with databases in PHP, with constant comparisons to JDBC patterns you already know. We'll start with basic connections and prepared statements, then move to advanced topics like transactions, error handling, and the repository pattern. By the end, you'll be able to build robust data access layers that prevent SQL injection, handle errors gracefully, and maintain data consistency.

You'll build practical examples including a connection manager, a complete repository implementation, and a query builder. These patterns form the foundation for working with databases in PHP applications, whether you're building simple scripts or complex enterprise applications.

## Prerequisites

::: info Time Estimate
⏱️ **75-90 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 8: Composer & Dependencies](/series/php-for-java-developers/chapters/08-composer-and-dependencies)
- Understanding of SQL and relational databases
- Familiarity with JDBC in Java

## What You'll Build

In this chapter, you'll:
- Set up database connections with PDO
- Execute safe queries with prepared statements
- Handle transactions and error modes
- Build a complete data access layer
- Implement repository pattern

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Compare PDO to JDBC** and understand their similarities
- **Create database connections** with proper configuration
- **Use prepared statements** to prevent SQL injection
- **Fetch results** in different formats
- **Handle transactions** and rollbacks
- **Implement error handling** for database operations
- **Build repositories** following best practices
- **Use query builders** for dynamic SQL
- **Understand ORMs** like Eloquent and Doctrine

---

## Section 1: PDO Introduction

### Goal

Understand PDO and how it compares to JDBC.

### What is PDO?

PDO (PHP Data Objects) provides:
- **Database abstraction layer**: Work with multiple databases using the same API
- **Prepared statements**: Prevent SQL injection
- **Error handling**: Exceptions for database errors
- **Transactions**: ACID compliance

### PDO vs JDBC Comparison

::: code-group

```php [PHP with PDO]
<?php

declare(strict_types=1);

// Connect to database
$pdo = new PDO(
    'mysql:host=localhost;dbname=myapp;charset=utf8mb4',
    'username',
    'password',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

// Prepared statement
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

echo $user['name'];
```

```java [Java with JDBC]
import java.sql.*;

// Connect to database
Connection conn = DriverManager.getConnection(
    "jdbc:mysql://localhost:3306/myapp?useSSL=false",
    "username",
    "password"
);

// Prepared statement
PreparedStatement stmt = conn.prepareStatement(
    "SELECT * FROM users WHERE email = ?"
);
stmt.setString(1, email);
ResultSet rs = stmt.executeQuery();

if (rs.next()) {
    System.out.println(rs.getString("name"));
}

rs.close();
stmt.close();
conn.close();
```

:::

### Key Similarities and Differences

| Feature | PDO | JDBC |
|---------|-----|------|
| **Abstraction** | Multi-database support | Multi-database support |
| **Prepared statements** | ✅ Built-in | ✅ Built-in |
| **Transactions** | ✅ Supported | ✅ Supported |
| **Error handling** | Exceptions | Exceptions |
| **Connection pooling** | ❌ Not built-in | ✅ Built-in |
| **Fetch modes** | Multiple (assoc, object, etc.) | ResultSet only |
| **Auto-close** | ✅ Automatic | ❌ Must close manually |
| **Parameter binding** | `?` or `:name` | `?` only |

---

## Section 2: Database Connection

### Goal

Learn to create and configure PDO connections properly.

### Basic Connection

```php
<?php

declare(strict_types=1);

// DSN (Data Source Name) format: driver:host;dbname;charset
$dsn = 'mysql:host=localhost;dbname=myapp;charset=utf8mb4';
$username = 'root';
$password = 'secret';

$options = [
    // Throw exceptions on errors
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

    // Return associative arrays by default
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // Disable emulated prepared statements (use real ones)
    PDO::ATTR_EMULATE_PREPARES => false,

    // Persistent connections (connection pooling)
    PDO::ATTR_PERSISTENT => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Connected successfully!\n";
} catch (PDOException $e) {
    // Never expose credentials in error messages
    error_log($e->getMessage());
    die("Database connection failed");
}
```

### Connection Configuration Class

```php
<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    /**
     * Get singleton PDO instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'] ?? 'localhost',
                $_ENV['DB_NAME'] ?? 'myapp'
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO(
                    $dsn,
                    $_ENV['DB_USER'] ?? 'root',
                    $_ENV['DB_PASS'] ?? '',
                    $options
                );
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                throw new \RuntimeException('Failed to connect to database');
            }
        }

        return self::$instance;
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

// Usage
$pdo = Connection::getInstance();
```

### Different Database Drivers

::: code-group

```php [MySQL]
$pdo = new PDO(
    'mysql:host=localhost;dbname=myapp;charset=utf8mb4',
    'username',
    'password'
);
```

```php [PostgreSQL]
$pdo = new PDO(
    'pgsql:host=localhost;port=5432;dbname=myapp',
    'username',
    'password'
);
```

```php [SQLite]
$pdo = new PDO('sqlite:/path/to/database.sqlite');
```

```php [SQL Server]
$pdo = new PDO(
    'sqlsrv:Server=localhost;Database=myapp',
    'username',
    'password'
);
```

:::

---

## Section 3: Prepared Statements

### Goal

Master prepared statements to prevent SQL injection and improve performance.

### Why Prepared Statements?

**Security**: Prevent SQL injection
```php
// ❌ NEVER DO THIS - Vulnerable to SQL injection!
$email = $_POST['email'];
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($sql);  // DANGEROUS!

// ✅ ALWAYS use prepared statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);  // Safe!
```

**Performance**: Query is parsed once, executed multiple times
```php
$stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (?, ?)');

foreach ($users as $user) {
    $stmt->execute([$user['name'], $user['email']]);  // Reuses prepared statement
}
```

### Positional Parameters

```php
<?php

declare(strict_types=1);

// Single parameter
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([42]);
$user = $stmt->fetch();

// Multiple parameters
$stmt = $pdo->prepare(
    'SELECT * FROM users WHERE email = ? AND status = ?'
);
$stmt->execute(['user@example.com', 'active']);
$users = $stmt->fetchAll();

// INSERT with positional parameters
$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
);
$stmt->execute([
    'Alice',
    'alice@example.com',
    password_hash('secret', PASSWORD_BCRYPT)
]);

echo "Inserted user with ID: " . $pdo->lastInsertId();
```

### Named Parameters

```php
<?php

declare(strict_types=1);

// Named parameters (more readable)
$stmt = $pdo->prepare(
    'SELECT * FROM users WHERE email = :email AND status = :status'
);

$stmt->execute([
    ':email' => 'user@example.com',
    ':status' => 'active'
]);

// Or without colons in array keys
$stmt->execute([
    'email' => 'user@example.com',
    'status' => 'active'
]);

$users = $stmt->fetchAll();

// INSERT with named parameters
$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, created_at)
     VALUES (:name, :email, :created_at)'
);

$stmt->execute([
    'name' => 'Bob',
    'email' => 'bob@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);
```

### Binding Parameters

```php
<?php

declare(strict_types=1);

// Bind positional parameters with explicit types
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND status = ?');
$stmt->bindValue(1, 42, PDO::PARAM_INT);
$stmt->bindValue(2, 'active', PDO::PARAM_STR);
$stmt->execute();

// Bind named parameters
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$email = 'user@example.com';
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

// bindParam vs bindValue
$id = 1;
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');

// bindParam: binds variable (evaluated at execute time)
$stmt->bindParam(1, $id);
$id = 2;  // Changes the bound value
$stmt->execute();  // Uses id = 2

// bindValue: binds value (evaluated at bind time)
$stmt->bindValue(1, $id);
$id = 3;  // Does NOT change the bound value
$stmt->execute();  // Still uses id = 2
```

### Java Comparison

::: code-group

```php [PHP PDO]
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

```java [Java JDBC]
PreparedStatement stmt = conn.prepareStatement(
    "SELECT * FROM users WHERE email = ?"
);
stmt.setString(1, email);
ResultSet rs = stmt.executeQuery();

if (rs.next()) {
    // Process result
}
```

:::

---

## Section 4: Fetching Results

### Goal

Learn different ways to retrieve query results.

### Fetch Modes

```php
<?php

declare(strict_types=1);

$stmt = $pdo->prepare('SELECT * FROM users WHERE status = ?');
$stmt->execute(['active']);

// 1. Fetch associative array (default with our config)
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com']

// 2. Fetch numeric array
$user = $stmt->fetch(PDO::FETCH_NUM);
// [1, 'Alice', 'alice@example.com']

// 3. Fetch both (associative + numeric)
$user = $stmt->fetch(PDO::FETCH_BOTH);
// [0 => 1, 'id' => 1, 1 => 'Alice', 'name' => 'Alice', ...]

// 4. Fetch as object
$user = $stmt->fetch(PDO::FETCH_OBJ);
// object { id: 1, name: 'Alice', email: 'alice@example.com' }
echo $user->name;  // 'Alice'

// 5. Fetch into class
class User
{
    public int $id;
    public string $name;
    public string $email;
}

$user = $stmt->fetchObject(User::class);
// User instance with properties set
```

### Fetching Multiple Rows

```php
<?php

declare(strict_types=1);

$stmt = $pdo->prepare('SELECT * FROM users');
$stmt->execute();

// 1. Fetch all rows at once
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
// [['id' => 1, 'name' => 'Alice'], ['id' => 2, 'name' => 'Bob'], ...]

// 2. Fetch all as objects
$users = $stmt->fetchAll(PDO::FETCH_CLASS, User::class);
// [User, User, User, ...]

// 3. Iterate over results (memory efficient for large datasets)
foreach ($stmt as $row) {
    echo $row['name'] . "\n";
}

// 4. Fetch single column
$stmt = $pdo->prepare('SELECT email FROM users');
$stmt->execute();
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
// ['alice@example.com', 'bob@example.com', ...]

// 5. Fetch key-value pairs
$stmt = $pdo->prepare('SELECT id, name FROM users');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
// [1 => 'Alice', 2 => 'Bob', ...]

// 6. Fetch grouped by column
$stmt = $pdo->prepare('SELECT status, id, name FROM users');
$stmt->execute();
$usersByStatus = $stmt->fetchAll(PDO::FETCH_GROUP);
// ['active' => [['id' => 1, 'name' => 'Alice'], ...], 'inactive' => [...]]
```

### Fetching Single Values

```php
<?php

declare(strict_types=1);

// Fetch single value
$stmt = $pdo->prepare('SELECT COUNT(*) FROM users');
$stmt->execute();
$count = $stmt->fetchColumn();
echo "Total users: $count\n";

// Fetch single row
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([1]);
$user = $stmt->fetch();

if ($user) {
    echo "Found: {$user['name']}\n";
} else {
    echo "User not found\n";
}

// Check if row exists
$stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
$stmt->execute(['test@example.com']);
$exists = (bool) $stmt->fetchColumn();
```

---

## Section 5: INSERT, UPDATE, DELETE Operations

### Goal

Perform data modification operations safely.

### INSERT Operations

```php
<?php

declare(strict_types=1);

// Single insert
$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password, created_at)
     VALUES (:name, :email, :password, :created_at)'
);

$stmt->execute([
    'name' => 'Alice',
    'email' => 'alice@example.com',
    'password' => password_hash('secret', PASSWORD_BCRYPT),
    'created_at' => date('Y-m-d H:i:s'),
]);

$userId = $pdo->lastInsertId();
echo "Created user with ID: $userId\n";

// Affected rows
$affectedRows = $stmt->rowCount();
echo "Rows affected: $affectedRows\n";

// Batch insert (reuse prepared statement)
$stmt = $pdo->prepare(
    'INSERT INTO users (name, email) VALUES (?, ?)'
);

$users = [
    ['Alice', 'alice@example.com'],
    ['Bob', 'bob@example.com'],
    ['Charlie', 'charlie@example.com'],
];

foreach ($users as $user) {
    $stmt->execute($user);
}

echo "Inserted " . count($users) . " users\n";
```

### UPDATE Operations

```php
<?php

declare(strict_types=1);

// Update single record
$stmt = $pdo->prepare(
    'UPDATE users SET name = :name, updated_at = :updated_at WHERE id = :id'
);

$stmt->execute([
    'name' => 'Alice Updated',
    'updated_at' => date('Y-m-d H:i:s'),
    'id' => 1,
]);

$affected = $stmt->rowCount();
if ($affected > 0) {
    echo "User updated successfully\n";
} else {
    echo "User not found or no changes made\n";
}

// Update multiple records
$stmt = $pdo->prepare('UPDATE users SET status = ? WHERE created_at < ?');
$stmt->execute(['inactive', date('Y-m-d', strtotime('-1 year'))]);
echo "Deactivated " . $stmt->rowCount() . " inactive users\n";

// Increment counter
$stmt = $pdo->prepare('UPDATE posts SET views = views + 1 WHERE id = ?');
$stmt->execute([42]);
```

### DELETE Operations

```php
<?php

declare(strict_types=1);

// Delete single record
$stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([1]);

if ($stmt->rowCount() > 0) {
    echo "User deleted successfully\n";
} else {
    echo "User not found\n";
}

// Delete multiple records
$stmt = $pdo->prepare('DELETE FROM users WHERE status = ?');
$stmt->execute(['inactive']);
echo "Deleted " . $stmt->rowCount() . " inactive users\n";

// Soft delete (update instead of delete)
$stmt = $pdo->prepare(
    'UPDATE users SET deleted_at = :deleted_at WHERE id = :id'
);
$stmt->execute([
    'deleted_at' => date('Y-m-d H:i:s'),
    'id' => 1,
]);
```

---

## Section 6: Transactions

### Goal

Use transactions to ensure data consistency.

### Basic Transaction Usage

```php
<?php

declare(strict_types=1);

try {
    // Start transaction
    $pdo->beginTransaction();

    // Perform multiple operations
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total) VALUES (?, ?)');
    $stmt->execute([1, 99.99]);
    $orderId = $pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)');
    $stmt->execute([$orderId, 100, 2]);
    $stmt->execute([$orderId, 101, 1]);

    $stmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
    $stmt->execute([2, 100]);
    $stmt->execute([1, 101]);

    // Commit if all successful
    $pdo->commit();
    echo "Order created successfully!\n";

} catch (PDOException $e) {
    // Rollback on error
    $pdo->rollBack();
    echo "Order failed: " . $e->getMessage() . "\n";
}
```

### Transaction Helper Class

```php
<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use Closure;

class Transaction
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * Execute callback within transaction
     */
    public function execute(Closure $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this->pdo);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Check if currently in transaction
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}

// Usage
$transaction = new Transaction($pdo);

$orderId = $transaction->execute(function($pdo) {
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total) VALUES (?, ?)');
    $stmt->execute([1, 99.99]);
    $orderId = $pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)');
    $stmt->execute([$orderId, 100, 2]);

    return $orderId;
});

echo "Order created: $orderId\n";
```

### Nested Transactions (Savepoints)

```php
<?php

declare(strict_types=1);

class SavepointTransaction
{
    private array $savepoints = [];

    public function __construct(private PDO $pdo) {}

    public function begin(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        } else {
            $savepoint = 'savepoint_' . count($this->savepoints);
            $this->pdo->exec("SAVEPOINT $savepoint");
            $this->savepoints[] = $savepoint;
        }
    }

    public function commit(): void
    {
        if (empty($this->savepoints)) {
            $this->pdo->commit();
        } else {
            $savepoint = array_pop($this->savepoints);
            $this->pdo->exec("RELEASE SAVEPOINT $savepoint");
        }
    }

    public function rollback(): void
    {
        if (empty($this->savepoints)) {
            $this->pdo->rollBack();
        } else {
            $savepoint = array_pop($this->savepoints);
            $this->pdo->exec("ROLLBACK TO SAVEPOINT $savepoint");
        }
    }
}

// Usage
$transaction = new SavepointTransaction($pdo);

$transaction->begin();  // Start outer transaction

try {
    // Insert user
    $stmt = $pdo->prepare('INSERT INTO users (name) VALUES (?)');
    $stmt->execute(['Alice']);

    $transaction->begin();  // Create savepoint

    try {
        // Insert profile (might fail)
        $stmt = $pdo->prepare('INSERT INTO profiles (user_id, bio) VALUES (?, ?)');
        $stmt->execute([$pdo->lastInsertId(), 'Bio text']);
        $transaction->commit();  // Release savepoint
    } catch (PDOException $e) {
        $transaction->rollback();  // Rollback to savepoint (user still inserted)
    }

    $transaction->commit();  // Commit outer transaction
} catch (PDOException $e) {
    $transaction->rollback();  // Rollback everything
}
```

### Java Comparison

::: code-group

```php [PHP PDO Transactions]
try {
    $pdo->beginTransaction();

    // Execute queries
    $stmt->execute([...]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    throw $e;
}
```

```java [Java JDBC Transactions]
try {
    conn.setAutoCommit(false);

    // Execute queries
    stmt.executeUpdate();

    conn.commit();
} catch (SQLException e) {
    conn.rollback();
    throw e;
}
```

:::

---

## Section 7: Error Handling

### Goal

Handle database errors gracefully.

### Error Modes

```php
<?php

declare(strict_types=1);

// 1. Silent mode (default - not recommended)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$stmt = $pdo->prepare('SELECT * FROM nonexistent_table');
if ($stmt === false) {
    $error = $pdo->errorInfo();
    echo "Error: " . $error[2];
}

// 2. Warning mode (emits PHP warnings)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$stmt = $pdo->prepare('SELECT * FROM nonexistent_table');  // Triggers warning

// 3. Exception mode (recommended)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $stmt = $pdo->prepare('SELECT * FROM nonexistent_table');
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
```

### Custom Exception Handling

```php
<?php

declare(strict_types=1);

namespace App\Database;

use PDOException;

class DatabaseException extends \RuntimeException
{
    private ?string $query;
    private array $bindings;

    public function __construct(
        string $message,
        ?string $query = null,
        array $bindings = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
        $this->bindings = $bindings;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'query' => $this->query,
            'bindings' => $this->bindings,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}

// Wrapper class for safe query execution
class QueryExecutor
{
    public function __construct(private \PDO $pdo) {}

    public function execute(string $query, array $bindings = []): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($bindings);
            return $stmt;
        } catch (PDOException $e) {
            throw new DatabaseException(
                'Query execution failed: ' . $e->getMessage(),
                $query,
                $bindings,
                (int) $e->getCode(),
                $e
            );
        }
    }
}

// Usage
$executor = new QueryExecutor($pdo);

try {
    $stmt = $executor->execute(
        'SELECT * FROM users WHERE email = ?',
        ['user@example.com']
    );
    $user = $stmt->fetch();
} catch (DatabaseException $e) {
    error_log(json_encode($e->toArray()));
    throw $e;
}
```

---

## Section 8: Repository Pattern

### Goal

Implement data access layer using repository pattern.

### Basic Repository

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

abstract class Repository
{
    public function __construct(
        protected PDO $pdo
    ) {}

    abstract protected function getTable(): string;

    public function findById(int $id): ?array
    {
        $table = $this->getTable();
        $stmt = $this->pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findAll(): array
    {
        $table = $this->getTable();
        $stmt = $this->pdo->query("SELECT * FROM {$table}");
        return $stmt->fetchAll();
    }

    public function findBy(array $criteria): array
    {
        $table = $this->getTable();
        $conditions = [];
        $bindings = [];

        foreach ($criteria as $column => $value) {
            $conditions[] = "$column = ?";
            $bindings[] = $value;
        }

        $where = implode(' AND ', $conditions);
        $stmt = $this->pdo->prepare("SELECT * FROM {$table} WHERE {$where}");
        $stmt->execute($bindings);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $table = $this->getTable();
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $table = $this->getTable();
        $sets = [];
        $bindings = [];

        foreach ($data as $column => $value) {
            $sets[] = "$column = ?";
            $bindings[] = $value;
        }
        $bindings[] = $id;

        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = ?",
            $table,
            implode(', ', $sets)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $table = $this->getTable();
        $stmt = $this->pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
```

### Specific Repository Implementation

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

class UserRepository extends Repository
{
    protected function getTable(): string
    {
        return 'users';
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findActiveUsers(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users WHERE status = "active"');
        return $stmt->fetchAll();
    }

    public function searchByName(string $search): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE name LIKE ?');
        $stmt->execute(["%{$search}%"]);
        return $stmt->fetchAll();
    }

    public function createWithProfile(array $userData, array $profileData): int
    {
        $this->pdo->beginTransaction();

        try {
            // Create user
            $userId = $this->create($userData);

            // Create profile
            $profileData['user_id'] = $userId;
            $stmt = $this->pdo->prepare(
                'INSERT INTO profiles (user_id, bio, avatar) VALUES (?, ?, ?)'
            );
            $stmt->execute([
                $profileData['user_id'],
                $profileData['bio'] ?? null,
                $profileData['avatar'] ?? null,
            ]);

            $this->pdo->commit();
            return $userId;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }
}

// Usage
$userRepo = new UserRepository($pdo);

// Find user
$user = $userRepo->findById(1);
$user = $userRepo->findByEmail('alice@example.com');

// Create user
$userId = $userRepo->create([
    'name' => 'Alice',
    'email' => 'alice@example.com',
    'password' => password_hash('secret', PASSWORD_BCRYPT),
    'created_at' => date('Y-m-d H:i:s'),
]);

// Update user
$userRepo->update($userId, [
    'name' => 'Alice Updated',
    'updated_at' => date('Y-m-d H:i:s'),
]);

// Delete user
$userRepo->delete($userId);

// Custom queries
$activeUsers = $userRepo->findActiveUsers();
$users = $userRepo->searchByName('Alice');
$count = $userRepo->countByStatus('active');
```

---

## Section 9: Query Builder

### Goal

Build dynamic queries safely without string concatenation.

### Simple Query Builder

```php
<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

class QueryBuilder
{
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private array $select = ['*'];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $orderBy = [];

    public function __construct(
        private PDO $pdo
    ) {}

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = "$column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $sql = $this->buildCountQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return (int) $stmt->fetchColumn();
    }

    private function buildSelectQuery(): string
    {
        $sql = sprintf(
            'SELECT %s FROM %s',
            implode(', ', $this->select),
            $this->table
        );

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    private function buildCountQuery(): string
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        return $sql;
    }
}

// Usage
$builder = new QueryBuilder($pdo);

// Simple query
$users = $builder->table('users')
    ->where('status', '=', 'active')
    ->get();

// Complex query
$users = $builder->table('users')
    ->select(['id', 'name', 'email'])
    ->where('status', '=', 'active')
    ->where('created_at', '>', '2024-01-01')
    ->whereIn('role', ['admin', 'moderator'])
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->offset(20)
    ->get();

// Get first result
$user = $builder->table('users')
    ->where('email', '=', 'alice@example.com')
    ->first();

// Count
$count = $builder->table('users')
    ->where('status', '=', 'active')
    ->count();

echo "Found $count active users\n";
```

---

## Section 10: ORMs Overview

### Goal

Understand when to use ORMs vs raw PDO.

### Popular PHP ORMs

**1. Eloquent (Laravel)**

```php
<?php

// Install: composer require illuminate/database

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'myapp',
    'username' => 'root',
    'password' => 'secret',
    'charset' => 'utf8mb4',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Define model
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}

// Usage
$user = User::find(1);
$user = User::where('email', 'alice@example.com')->first();
$users = User::where('status', 'active')->get();

$user = new User();
$user->name = 'Alice';
$user->email = 'alice@example.com';
$user->save();

$user->update(['name' => 'Alice Updated']);
$user->delete();
```

**2. Doctrine (Symfony)**

```php
<?php

// Install: composer require doctrine/orm

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__ . '/src/Entity'];
$isDevMode = true;

$dbParams = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'secret',
    'dbname' => 'myapp',
];

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($dbParams, $config);

// Define entity
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private int $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    // Getters and setters...
}

// Usage
$user = $entityManager->find(User::class, 1);
$users = $entityManager->getRepository(User::class)->findAll();

$user = new User();
$user->setName('Alice');
$entityManager->persist($user);
$entityManager->flush();
```

### PDO vs ORM Comparison

| Feature | PDO | ORM (Eloquent/Doctrine) |
|---------|-----|-------------------------|
| **Learning curve** | Low | High |
| **Performance** | Fast | Slightly slower |
| **Flexibility** | High | Medium |
| **Boilerplate** | More | Less |
| **Type safety** | Manual | Built-in |
| **Relationships** | Manual joins | Automatic |
| **Migrations** | Manual | Built-in |
| **Caching** | Manual | Built-in |
| **Best for** | Complex queries, performance-critical | CRUD, rapid development |

::: tip When to Use Each

**Use PDO when:**
- You need maximum performance
- You have complex queries
- You want full control
- Your project is small/simple

**Use ORM when:**
- You have many relationships
- You want rapid development
- You need migrations/seeds
- Your team prefers Active Record pattern
:::

---

## Exercises

### Exercise 1: User CRUD Repository

Create a complete user management system:

**Requirements:**
- User repository with full CRUD operations
- Prepared statements for all queries
- Transaction support for complex operations
- Custom search methods (by email, by name, by status)
- Pagination support

### Exercise 2: Blog System with Relationships

Build a blog system with posts, comments, and categories:

**Requirements:**
- Post repository with category relationship
- Comment repository with post/user relationships
- Methods to fetch posts with their comments
- Transaction-based post creation (post + categories)
- Search and filtering capabilities

### Exercise 3: Query Builder Extension

Extend the QueryBuilder class:

**Requirements:**
- Add `orWhere()` support
- Add `join()` support
- Add `groupBy()` and `having()` support
- Add `insert()`, `update()`, `delete()` methods
- Add query logging for debugging

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Create PDO connections with proper configuration
- [ ] Use prepared statements to prevent SQL injection
- [ ] Fetch results in different formats (assoc, object, etc.)
- [ ] Perform INSERT, UPDATE, DELETE operations safely
- [ ] Use transactions for data consistency
- [ ] Handle database errors with exceptions
- [ ] Implement repository pattern for data access
- [ ] Build dynamic queries with query builder
- [ ] Compare PDO to JDBC
- [ ] Understand when to use ORM vs raw PDO

::: tip Ready for More?
In [Chapter 10: Building REST APIs](/series/php-for-java-developers/chapters/10-building-rest-apis), we'll build a complete RESTful API using everything we've learned so far.
:::

---

## Further Reading

**Official Documentation:**
- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [PDO MySQL Driver](https://www.php.net/manual/en/ref.pdo-mysql.php)
- [SQL Injection Prevention](https://www.php.net/manual/en/security.database.sql-injection.php)

**ORMs:**
- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)

---


<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/08-composer-and-dependencies">← Chapter 8: Composer & Dependencies</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/10-building-rest-apis">Chapter 10: Building REST APIs →</a>
  </div>
</div>
