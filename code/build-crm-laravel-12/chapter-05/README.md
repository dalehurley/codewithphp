# Chapter 05: Database Migrations & Schema Design

This directory contains the migration files and code examples for implementing the CRM database schema using Laravel migrations.

## Overview

This chapter covers creating Laravel migrations for all CRM tables:
- **teams** - Team/organization container for multi-tenancy
- **team_user** - Pivot table for team membership
- **companies** - Sales organizations
- **contacts** - Individual people/prospects
- **deals** - Sales opportunities in the pipeline
- **tasks** - Action items and follow-ups

## Migration Files

The migration files are created using `sail artisan make:migration` and are automatically placed in your `database/migrations/` directory.

### Generating Migrations

```bash
# Generate all migration files
sail artisan make:migration create_teams_table
sail artisan make:migration create_team_user_table
sail artisan make:migration create_companies_table
sail artisan make:migration create_contacts_table
sail artisan make:migration create_deals_table
sail artisan make:migration create_tasks_table
```

### Migration Content

Each migration file contains:
- **up()** method - Schema definition for creating the table
- **down()** method - Rollback logic for reverting the migration

See the chapter tutorial for the complete implementation of each migration.

## Running Migrations

### Run All Pending Migrations

```bash
sail artisan migrate
```

### Check Migration Status

```bash
sail artisan migrate:status
```

### Rollback Last Batch

```bash
sail artisan migrate:rollback
```

### Rollback All Migrations

```bash
sail artisan migrate:reset
```

### Fresh Migrations (Dangerous!)

```bash
# This drops all tables and re-runs migrations
sail artisan migrate:fresh
```

## Verifying the Schema

### Using MySQL CLI

```bash
# Connect to MySQL
sail mysql

# List all tables
SHOW TABLES;

# View table structure
DESCRIBE contacts;

# View indexes
SHOW INDEX FROM contacts;

# Exit MySQL
EXIT;
```

### Using Artisan Tinker

```bash
# Start Tinker interactive shell
sail artisan tinker

# List all tables
>>> Schema::getTables()

# Get columns for a table
>>> Schema::getColumns('contacts')

# Exit Tinker
>>> exit
```

## Schema Overview

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| `teams` | Organization containers | id, name, slug, plan_type |
| `team_user` | Team membership (pivot) | id, team_id, user_id, role |
| `companies` | Sales organizations | id, team_id, name, website, industry |
| `contacts` | Individual people | id, team_id, company_id, first_name, last_name, email |
| `deals` | Sales opportunities | id, team_id, company_id, name, amount, stage |
| `tasks` | Action items | id, team_id, deal_id, contact_id, assigned_to, title, priority |

## Key Patterns

### Multi-Tenancy

Every data table includes a `team_id` foreign key that scopes data to teams:
```php
$table->foreignId('team_id')->constrained()->cascadeOnDelete();
```

### Foreign Key Relationships

Using `foreignId()` and `constrained()` for referential integrity:
```php
$table->foreignId('company_id')->constrained()->cascadeOnDelete();
```

### Soft Deletes

For safe archival of records:
```php
$table->softDeletes();
```

This creates a `deleted_at` column. Records are excluded from queries by default but can be restored.

### Indexes

Strategic indexing on frequently queried columns:
```php
$table->index('team_id');
$table->index('created_at');
$table->index(['team_id', 'email']); // Composite index
```

## Testing the Migrations

### Verify All Tables Exist

```php
sail artisan tinker

>>> count(Schema::getTables())
=> 6 // Should show 6 tables (teams, team_user, companies, contacts, deals, tasks)
```

### Verify Foreign Keys

```php
sail mysql

SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = DATABASE();

EXIT;
```

### Test Soft Deletes

The migration creates `deleted_at` column on:
- companies
- contacts
- deals
- tasks

## Next Steps

After running these migrations:

1. **Chapter 06**: Create Eloquent models that correspond to these tables
2. **Chapter 07**: Create model relationships and eager loading
3. **Chapter 08**: Build database seeders to populate test data
4. **Chapter 09**: Implement CRUD operations on these tables

## Troubleshooting

### Migrations Won't Run

```bash
# Ensure MySQL is running
sail ps

# Ensure Sail containers are started
sail up -d

# Check migration status
sail artisan migrate:status
```

### Foreign Key Constraint Errors

```bash
# Ensure migrations run in correct order
# Check migration timestamps
ls database/migrations/

# May need to rollback and re-run
sail artisan migrate:rollback
sail artisan migrate
```

### Can't Connect to Database

```bash
# Check environment variables
cat .env

# Ensure .env has correct DB_HOST and DB_PORT
# Default: DB_HOST=mysql DB_PORT=3306
```

## References

- [Laravel Migrations Documentation](https://laravel.com/docs/12.x/migrations)
- [Schema Builder](https://laravel.com/docs/12.x/migrations#tables)
- [Foreign Key Constraints](https://laravel.com/docs/12.x/migrations#foreign-key-constraints)
- [Soft Deletes](https://laravel.com/docs/12.x/eloquent#soft-deleting)

## Series Information

This is part of the **Build a CRM with Laravel 12** series on Code with PHP.

- Series: [Build a CRM with Laravel 12](https://codewithphp.com/series/build-crm-laravel-12/)
- Chapter: [05: Database Migrations & Schema Design](https://codewithphp.com/series/build-crm-laravel-12/chapters/05-database-migrations-schema-design)
- Previous: [04: Planning Application Architecture & Data Modeling](https://codewithphp.com/series/build-crm-laravel-12/chapters/04-planning-application-architecture-data-modeling)
- Next: [06: Eloquent Models and Relationships](https://codewithphp.com/series/build-crm-laravel-12/chapters/06-eloquent-models-relationships)

## Prerequisites

- PHP 8.4+
- Laravel 12
- Composer
- Node.js 18+
- Docker Desktop (for Laravel Sail)
- MySQL 8.0+ (via Docker)

Refer to Chapter 02 for detailed setup instructions.
