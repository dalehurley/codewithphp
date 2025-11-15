# Chapter 25: Data Migrations

Learn database migration strategies in Go. Explore migration tools, versioning, and best practices for evolving your database schema safely.

## Overview

Database migrations manage schema changes over time. Unlike PHP's Laravel migrations, Go doesn't have a single standard tool, but several excellent options exist like golang-migrate, goose, and GORM's auto-migrate.

## Files

1. `01-migration-basics.go` - Up/down migrations, versioning
2. `02-golang-migrate.go` - Using golang-migrate/migrate
3. `03-goose-migrations.go` - Goose migration tool
4. `04-gorm-automigrate.go` - GORM auto-migration
5. `05-zero-downtime-migrations.go` - Safe migration strategies
6. `06-data-migrations.go` - Migrating data, not just schema

## Quick Reference

**Laravel Migrations**:
```php
// database/migrations/2024_01_01_create_users_table.php
public function up() {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
}

// Run: php artisan migrate
```

**Go (golang-migrate)**:
```sql
-- migrations/000001_create_users_table.up.sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

-- migrations/000001_create_users_table.down.sql
DROP TABLE users;
```

```go
import "github.com/golang-migrate/migrate/v4"

m, _ := migrate.New(
    "file://migrations",
    "postgres://user:pass@localhost:5432/db?sslmode=disable",
)

m.Up()  // Run migrations
```

## Common Patterns

### Creating Migrations
```bash
# With golang-migrate CLI
migrate create -ext sql -dir migrations -seq create_users_table
migrate create -ext sql -dir migrations -seq add_email_to_users
```

### Running Migrations Programmatically
```go
package main

import (
    "database/sql"
    "github.com/golang-migrate/migrate/v4"
    "github.com/golang-migrate/migrate/v4/database/postgres"
    _ "github.com/golang-migrate/migrate/v4/source/file"
)

func runMigrations(db *sql.DB) error {
    driver, err := postgres.WithInstance(db, &postgres.Config{})
    if err != nil {
        return err
    }

    m, err := migrate.NewWithDatabaseInstance(
        "file://migrations",
        "postgres", driver,
    )
    if err != nil {
        return err
    }

    return m.Up()
}
```

### GORM Auto-Migrate
```go
type User struct {
    ID        uint
    Name      string
    Email     string `gorm:"uniqueIndex"`
    CreatedAt time.Time
}

// Auto-migrate (development only!)
db.AutoMigrate(&User{})
```

## Best Practices

- Version migrations sequentially
- Always create both up and down migrations
- Test migrations on staging before production
- Backup database before migrations
- Make migrations idempotent when possible
- Avoid destructive changes in production
- Use transactions for atomic migrations
- Keep migrations small and focused
- Don't use auto-migrate in production

## Next Steps

- Chapter 26: Unit Testing
- Chapter 27: Table-Driven Tests
- Chapter 39: CI/CD & Deployment

---

**Key Takeaway**: Go has several migration tools. golang-migrate is database-agnostic and production-ready, while GORM's AutoMigrate is convenient for development. Always test migrations and maintain up/down migration files.
