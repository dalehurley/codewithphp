# Chapter 23: ORMs & Query Builders

Explore Go's ORM landscape: GORM, sqlx, sqlc, and more. Learn when to use ORMs vs raw SQL, and how they compare to Eloquent and Doctrine.

## Overview

Go offers several options for database abstraction: full ORMs like GORM, query builders like sqlx, and code generators like sqlc. Each has trade-offs between convenience and control.

## Files

1. `01-gorm-basics.go` - GORM setup, models, CRUD operations
2. `02-gorm-associations.go` - Has One, Has Many, Many to Many
3. `03-sqlx-queries.go` - sqlx for easier scanning
4. `04-query-builders.go` - Building dynamic queries
5. `05-raw-sql-vs-orm.go` - When to use each approach
6. `06-sqlc-codegen.go` - Type-safe SQL with sqlc

## Quick Reference

**GORM (ORM)**:
```go
import "gorm.io/gorm"
import "gorm.io/driver/mysql"

type User struct {
    ID    uint
    Name  string
    Email string
}

db, _ := gorm.Open(mysql.Open(dsn), &gorm.Config{})

// Create
db.Create(&User{Name: "Alice", Email: "alice@example.com"})

// Read
var user User
db.First(&user, 1)  // Find by ID
db.Where("name = ?", "Alice").First(&user)

// Update
db.Model(&user).Update("email", "new@example.com")

// Delete
db.Delete(&user)
```

**sqlx (Enhanced database/sql)**:
```go
import "github.com/jmoiron/sqlx"

type User struct {
    ID    int    `db:"id"`
    Name  string `db:"name"`
    Email string `db:"email"`
}

db := sqlx.Connect("mysql", dsn)

// Query into struct
var user User
db.Get(&user, "SELECT * FROM users WHERE id = ?", 1)

// Query into slice
var users []User
db.Select(&users, "SELECT * FROM users WHERE age > ?", 18)
```

## Common Patterns

### GORM Associations
```go
type User struct {
    ID    uint
    Name  string
    Posts []Post  // Has Many
}

type Post struct {
    ID     uint
    Title  string
    UserID uint
}

// Auto-migrate
db.AutoMigrate(&User{}, &Post{})

// Preload associations
var user User
db.Preload("Posts").First(&user, 1)

// Create with association
user := User{
    Name: "Alice",
    Posts: []Post{
        {Title: "First Post"},
        {Title: "Second Post"},
    },
}
db.Create(&user)
```

### Dynamic Query Building
```go
query := db.Model(&User{})

if name != "" {
    query = query.Where("name LIKE ?", "%"+name+"%")
}

if minAge > 0 {
    query = query.Where("age >= ?", minAge)
}

var users []User
query.Find(&users)
```

## When to Use What

**Use GORM when**:
- Building standard CRUD applications
- Want automatic migrations
- Need relationship management
- Rapid development is priority

**Use sqlx when**:
- Need more control than GORM
- Want struct scanning without ORM overhead
- Complex queries with raw SQL
- Performance is critical

**Use database/sql when**:
- Maximum control needed
- Simple queries
- Minimal dependencies desired
- Learning database interactions

**Use sqlc when**:
- Want type safety without runtime overhead
- Prefer SQL over ORM syntax
- Code generation acceptable
- Database schema is source of truth

## Best Practices

- Don't over-use ORMs for simple queries
- Use prepared statements (ORMs do this automatically)
- Be careful with N+1 queries (use Preload in GORM)
- Monitor generated SQL queries
- Use transactions for multi-step operations
- Keep models close to database schema
- Consider performance impact of ORMs

## Comparison with PHP

| Feature | Laravel Eloquent | GORM |
|---------|-----------------|------|
| Active Record | Yes | Yes |
| Relationships | Excellent | Good |
| Migrations | Built-in | Auto-migrate or manual |
| Query Builder | Excellent | Good |
| Performance | Moderate | Good |
| Learning Curve | Moderate | Moderate |

## Next Steps

- Chapter 24: Redis & Caching
- Chapter 25: Data Migrations
- Chapter 26: Unit Testing

---

**Key Takeaway**: Go offers multiple database abstraction levels. GORM is great for rapid development like Laravel's Eloquent, while sqlx provides a middle ground, and raw database/sql offers maximum control. Choose based on your needs.
