# Chapter 21: Database/SQL Package

Master Go's database/sql package - the foundation for database access. Learn connection pooling, prepared statements, and transactions - concepts similar to PDO but with better concurrency support.

## Overview

Go's database/sql package provides a generic interface for SQL databases. Unlike PHP's PDO which handles connections per request, Go maintains a connection pool, making it highly concurrent and efficient.

## Files in This Chapter

1. `01-connection-basics.go` - Opening connections, DSN, drivers
2. `02-queries.go` - Query, QueryRow, QueryContext, scanning results
3. `03-prepared-statements.go` - Prepare, Exec, performance benefits
4. `04-transactions.go` - Begin, Commit, Rollback, isolation
5. `05-connection-pooling.go` - Pool configuration, limits, timeouts
6. `06-context-cancellation.go` - Using context for timeouts and cancellation

## Quick Reference

**PHP (PDO)**:
```php
$dsn = "mysql:host=localhost;dbname=mydb";
$pdo = new PDO($dsn, 'user', 'password');

// Query
$stmt = $pdo->query("SELECT * FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['name'];
}

// Prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Transaction
$pdo->beginTransaction();
try {
    $pdo->exec("INSERT INTO users ...");
    $pdo->exec("UPDATE accounts ...");
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}
```

**Go (database/sql)**:
```go
import (
    "database/sql"
    _ "github.com/go-sql-driver/mysql"
)

db, err := sql.Open("mysql", "user:password@tcp(localhost:3306)/mydb")

// Query
rows, err := db.Query("SELECT * FROM users")
defer rows.Close()

for rows.Next() {
    var id int
    var name string
    rows.Scan(&id, &name)
    fmt.Println(name)
}

// Prepared statement
stmt, err := db.Prepare("SELECT * FROM users WHERE id = ?")
defer stmt.Close()

row := stmt.QueryRow(id)
row.Scan(&user.ID, &user.Name)

// Transaction
tx, err := db.Begin()

_, err = tx.Exec("INSERT INTO users ...")
_, err = tx.Exec("UPDATE accounts ...")

if err != nil {
    tx.Rollback()
} else {
    tx.Commit()
}
```

## Key Concepts

### 1. Opening Connection

```go
import (
    "database/sql"
    _ "github.com/go-sql-driver/mysql"
)

func main() {
    dsn := "user:password@tcp(localhost:3306)/dbname?parseTime=true"
    db, err := sql.Open("mysql", dsn)
    if err != nil {
        log.Fatal(err)
    }
    defer db.Close()

    // Test connection
    if err := db.Ping(); err != nil {
        log.Fatal(err)
    }
}
```

### 2. Querying Rows

```go
// Multiple rows
rows, err := db.Query("SELECT id, name, email FROM users WHERE age > ?", 18)
if err != nil {
    log.Fatal(err)
}
defer rows.Close()

for rows.Next() {
    var id int
    var name, email string

    if err := rows.Scan(&id, &name, &email); err != nil {
        log.Fatal(err)
    }

    fmt.Printf("%d: %s <%s>\n", id, name, email)
}

if err := rows.Err(); err != nil {
    log.Fatal(err)
}
```

### 3. Single Row Query

```go
var user User

err := db.QueryRow("SELECT id, name, email FROM users WHERE id = ?", 123).
    Scan(&user.ID, &user.Name, &user.Email)

if err == sql.ErrNoRows {
    fmt.Println("User not found")
} else if err != nil {
    log.Fatal(err)
}
```

### 4. Executing Statements

```go
// INSERT
result, err := db.Exec(
    "INSERT INTO users (name, email) VALUES (?, ?)",
    "Alice", "alice@example.com",
)

lastID, err := result.LastInsertId()
rowsAffected, err := result.RowsAffected()

// UPDATE
result, err = db.Exec(
    "UPDATE users SET name = ? WHERE id = ?",
    "Bob", 123,
)

// DELETE
result, err = db.Exec("DELETE FROM users WHERE id = ?", 123)
```

### 5. Prepared Statements

```go
// Prepare once, execute many times
stmt, err := db.Prepare("INSERT INTO users (name, email) VALUES (?, ?)")
if err != nil {
    log.Fatal(err)
}
defer stmt.Close()

for _, user := range users {
    _, err := stmt.Exec(user.Name, user.Email)
    if err != nil {
        log.Fatal(err)
    }
}
```

### 6. Transactions

```go
tx, err := db.Begin()
if err != nil {
    log.Fatal(err)
}

_, err = tx.Exec("INSERT INTO accounts (user_id, balance) VALUES (?, ?)", 1, 100)
if err != nil {
    tx.Rollback()
    log.Fatal(err)
}

_, err = tx.Exec("UPDATE users SET account_created = true WHERE id = ?", 1)
if err != nil {
    tx.Rollback()
    log.Fatal(err)
}

if err = tx.Commit(); err != nil {
    log.Fatal(err)
}
```

## Common Patterns

### 1. Connection Pool Configuration

```go
db.SetMaxOpenConns(25)                 // Max open connections
db.SetMaxIdleConns(5)                  // Max idle connections
db.SetConnMaxLifetime(5 * time.Minute) // Max connection lifetime
db.SetConnMaxIdleTime(5 * time.Minute) // Max idle time
```

### 2. Context with Timeout

```go
ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
defer cancel()

rows, err := db.QueryContext(ctx, "SELECT * FROM users")
```

### 3. Scanning into Structs

```go
type User struct {
    ID    int
    Name  string
    Email sql.NullString  // For nullable columns
}

rows, err := db.Query("SELECT id, name, email FROM users")
defer rows.Close()

var users []User
for rows.Next() {
    var u User
    err := rows.Scan(&u.ID, &u.Name, &u.Email)
    if err != nil {
        log.Fatal(err)
    }
    users = append(users, u)
}
```

### 4. NULL Handling

```go
var name sql.NullString
var age sql.NullInt64

err := db.QueryRow("SELECT name, age FROM users WHERE id = ?", id).
    Scan(&name, &age)

if name.Valid {
    fmt.Println("Name:", name.String)
} else {
    fmt.Println("Name is NULL")
}
```

## Best Practices

- Use connection pooling (it's automatic)
- Always close rows with defer
- Use prepared statements for repeated queries
- Use transactions for related operations
- Handle NULL values with sql.Null* types
- Use context for timeouts and cancellation
- Check errors from rows.Err()
- Don't use fmt.Sprintf for queries (SQL injection!)

## Comparison with PHP

| Feature | PHP PDO | Go database/sql |
|---------|---------|-----------------|
| Connection | Per request | Pooled |
| Concurrency | Process-based | Goroutine-safe |
| Prepared statements | Manual | Manual |
| Transactions | Similar | Similar |
| NULL handling | null | sql.Null* types |
| Placeholders | Named/positional | Positional (?) |

## Next Steps

- Chapter 22: MySQL & PostgreSQL - Database-specific features
- Chapter 23: ORMs & Query Builders - GORM, sqlx
- Chapter 24: Redis & Caching

---

**Key Takeaway**: Go's database/sql package is concurrent-safe with built-in connection pooling. Unlike PHP where each request gets a new connection, Go maintains a pool that handles thousands of concurrent queries efficiently.
