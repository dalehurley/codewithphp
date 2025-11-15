# Chapter 22: MySQL & PostgreSQL

Deep dive into MySQL and PostgreSQL with Go. Learn database-specific features, drivers, best practices, and performance optimization.

## Overview

While database/sql provides a common interface, each database has unique features. This chapter covers MySQL and PostgreSQL specifics, from driver installation to advanced features like JSONB and full-text search.

## Files

1. `01-mysql-driver.go` - MySQL driver, DSN configuration
2. `02-postgresql-driver.go` - PostgreSQL driver, connection strings
3. `03-database-specific-features.go` - JSONB, arrays, full-text search
4. `04-migrations.go` - Schema migrations, versioning
5. `05-performance-tuning.go` - Indexes, query optimization
6. `06-connection-best-practices.go` - Pool sizing, timeouts

## Quick Reference

**MySQL**:
```go
import _ "github.com/go-sql-driver/mysql"

dsn := "user:password@tcp(localhost:3306)/dbname?parseTime=true&charset=utf8mb4"
db, err := sql.Open("mysql", dsn)
```

**PostgreSQL**:
```go
import _ "github.com/lib/pq"

dsn := "host=localhost port=5432 user=postgres password=secret dbname=mydb sslmode=disable"
db, err := sql.Open("postgres", dsn)
```

## Common Patterns

### MySQL JSON Columns
```go
type UserPrefs struct {
    Theme    string `json:"theme"`
    Language string `json:"language"`
}

// Insert JSON
prefs := UserPrefs{Theme: "dark", Language: "en"}
prefsJSON, _ := json.Marshal(prefs)
db.Exec("INSERT INTO users (name, preferences) VALUES (?, ?)", "Alice", prefsJSON)

// Query JSON
var prefsData []byte
db.QueryRow("SELECT preferences FROM users WHERE id = ?", 1).Scan(&prefsData)
json.Unmarshal(prefsData, &prefs)
```

### PostgreSQL Arrays
```go
// Insert array
tags := pq.Array([]string{"go", "database", "tutorial"})
db.Exec("INSERT INTO posts (title, tags) VALUES ($1, $2)", "My Post", tags)

// Query array
var tags []string
db.QueryRow("SELECT tags FROM posts WHERE id = $1", 1).Scan(pq.Array(&tags))
```

## Best Practices

- Use parseTime=true for MySQL to handle time.Time
- Use prepared statements to prevent SQL injection
- Configure connection pool based on load
- Use database-specific placeholder styles (? for MySQL, $1 for PostgreSQL)
- Enable SSL for production connections
- Monitor slow queries and add indexes
- Use transactions for data consistency

## Next Steps

- Chapter 23: ORMs & Query Builders
- Chapter 24: Redis & Caching
- Chapter 25: Data Migrations

---

**Key Takeaway**: MySQL and PostgreSQL have different features and syntax. Use database-specific drivers and leverage advanced features like JSON columns and arrays while maintaining portability with database/sql interface.
