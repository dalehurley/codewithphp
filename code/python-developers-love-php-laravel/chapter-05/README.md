# Chapter 05: Working with Data - Eloquent ORM & Database Workflow

This directory contains code examples comparing Django ORM, SQLAlchemy, and Eloquent ORM. All examples demonstrate the same concepts with different syntax.

## File Structure

### Model Definitions

- **`django-orm-model.py`** - Django ORM model definitions with relationships
- **`sqlalchemy-model.py`** - SQLAlchemy model definitions with relationships
- **`eloquent-model.php`** - Eloquent model definitions with relationships

### Migrations

- **`django-migration-detailed.py`** - Django migration examples (create tables, add columns, modify columns)
- **`laravel-migration-detailed.php`** - Laravel migration examples (create tables, add columns, modify columns, pivot tables)

### Relationships

- **`django-relationships.py`** - Django ORM relationships (one-to-one, one-to-many, many-to-many)
- **`sqlalchemy-relationships.py`** - SQLAlchemy relationships (one-to-one, one-to-many, many-to-many)
- **`eloquent-relationships.php`** - Eloquent relationships (hasOne, belongsTo, hasMany, belongsToMany)

### Query Builder

- **`django-queries.py`** - Django ORM query examples (filtering, ordering, aggregations)
- **`sqlalchemy-queries.py`** - SQLAlchemy query examples (filtering, ordering, aggregations)
- **`eloquent-queries.php`** - Eloquent query builder examples (where, orderBy, aggregations)

### Advanced Features

- **`eloquent-eager-loading.php`** - Eager loading examples to prevent N+1 queries
- **`eloquent-scopes.php`** - Global and local scopes for reusable queries
- **`eloquent-accessors-mutators.php`** - Accessors (computed attributes) and mutators (value transformers)
- **`eloquent-events.php`** - Model events and observers (creating, created, updating, updated, deleting, deleted)
- **`eloquent-soft-deletes.php`** - Soft delete examples (deleted_at timestamp)
- **`eloquent-collections.php`** - Collection methods (pluck, map, filter, groupBy, etc.)
- **`eloquent-transactions-raw.php`** - Database transactions and raw SQL queries

## Running the Examples

### Django Examples

```bash
# Ensure Django is installed
pip install django

# Create a Django project (if needed)
django-admin startproject myproject
cd myproject
python manage.py startapp myapp

# Copy model files to myapp/models.py
# Copy migration files to myapp/migrations/
# Copy query files to myapp/queries.py

# Run migrations
python manage.py makemigrations
python manage.py migrate

# Run queries in Django shell
python manage.py shell
# Then import and use: from myapp.queries import *
```

### SQLAlchemy Examples

```bash
# Ensure SQLAlchemy is installed
pip install sqlalchemy

# Create a Python script
python sqlalchemy-model.py
python sqlalchemy-queries.py
```

### Eloquent Examples

```bash
# Ensure Laravel is installed
composer create-project laravel/laravel myproject
cd myproject

# Copy model files to app/Models/
# Copy migration files to database/migrations/
# Copy query files to app/Queries/ or use in controllers

# Run migrations
php artisan migrate

# Use in Laravel Tinker
php artisan tinker
# Then: use App\Models\Post; Post::all();
```

## Key Comparisons

### Model Definitions

| Feature | Django | SQLAlchemy | Eloquent |
|--------|--------|------------|----------|
| Base Class | `models.Model` | `Base` | `Model` |
| Table Name | Auto or `Meta.db_table` | `__tablename__` | Auto or `$table` |
| Mass Assignment | Form validation | Manual | `$fillable` / `$guarded` |
| Relationships | `ForeignKey()`, `ManyToMany()` | `relationship()` | `belongsTo()`, `hasMany()` |

### Migrations

| Feature | Django | Laravel |
|---------|--------|---------|
| Create | `makemigrations` | `make:migration` |
| Run | `migrate` | `migrate` |
| Rollback | `migrate app zero` | `migrate:rollback` |
| Schema | Model-based | Schema builder |

### Relationships

| Type | Django | SQLAlchemy | Eloquent |
|------|--------|------------|----------|
| One-to-many | `ForeignKey()` | `relationship()` | `hasMany()` / `belongsTo()` |
| One-to-one | `OneToOneField()` | `relationship(uselist=False)` | `hasOne()` / `belongsTo()` |
| Many-to-many | `ManyToManyField()` | `relationship(secondary=table)` | `belongsToMany()` |

### Queries

| Operation | Django | SQLAlchemy | Eloquent |
|-----------|--------|------------|----------|
| Filter | `filter(field=value)` | `filter(Model.field == value)` | `where('field', value)` |
| Order | `order_by('-field')` | `order_by(desc(Model.field))` | `orderBy('field', 'desc')` |
| Count | `count()` | `.count()` | `count()` |
| Eager Load | `select_related()` / `prefetch_related()` | `joinedload()` / `subqueryload()` | `with()` |

## Common Patterns

### Creating a Model

**Django:**
```python
post = Post.objects.create(title='My Post', content='Content')
```

**SQLAlchemy:**
```python
post = Post(title='My Post', content='Content')
session.add(post)
session.commit()
```

**Eloquent:**
```php
$post = Post::create(['title' => 'My Post', 'content' => 'Content']);
```

### Querying with Relationships

**Django:**
```python
posts = Post.objects.select_related('author').prefetch_related('tags').all()
```

**SQLAlchemy:**
```python
posts = session.query(Post).options(joinedload(Post.author), subqueryload(Post.tags)).all()
```

**Eloquent:**
```php
$posts = Post::with('author', 'tags')->get();
```

### Filtering and Ordering

**Django:**
```python
posts = Post.objects.filter(author_id=1).order_by('-published_at')[:10]
```

**SQLAlchemy:**
```python
posts = session.query(Post).filter(Post.author_id == 1).order_by(desc(Post.published_at)).limit(10).all()
```

**Eloquent:**
```php
$posts = Post::where('author_id', 1)->orderBy('published_at', 'desc')->limit(10)->get();
```

## Notes

- All examples use PHP 8.4 syntax and Laravel 11.x conventions
- Django examples use Django 4.x+ syntax
- SQLAlchemy examples use SQLAlchemy 2.x syntax
- Code examples are educational and may need adaptation for production use
- Always use proper error handling and validation in production code

## Further Reading

- [Laravel Eloquent Documentation](https://laravel.com/docs/eloquent)
- [Django ORM Documentation](https://docs.djangoproject.com/en/stable/topics/db/)
- [SQLAlchemy Documentation](https://docs.sqlalchemy.org/)

