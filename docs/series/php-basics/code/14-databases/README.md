# Chapter 14: Interacting with Databases - Code Examples

Comprehensive database examples using PDO for secure, modern database interactions.

## Files

1. **`01-pdo-connection.php`** - Database connections, configuration
2. **`02-crud-operations.php`** - Create, Read, Update, Delete operations
3. **`03-prepared-statements-security.php`** - SQL injection prevention, transactions

## Quick Start

```bash
php 01-pdo-connection.php
php 02-crud-operations.php
php 03-prepared-statements-security.php
```

## Key Concepts

- **PDO**: PHP Data Objects - database abstraction layer
- **Prepared Statements**: Prevent SQL injection
- **CRUD**: Create, Read, Update, Delete
- **Transactions**: Group operations atomically
- **Named Parameters**: `:name` (recommended)
- **Positional Parameters**: `?` (alternative)

## Security

**NEVER do this:**

```php
$sql = "SELECT * FROM users WHERE id = $_GET[id]";  // DANGEROUS!
```

**ALWAYS do this:**

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_GET['id']]);  // SAFE!
```

## Related Chapter

[Chapter 14: Interacting with Databases](../../chapters/14-interacting-with-databases.md)
