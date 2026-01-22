---
title: "06: Eloquent Models & Relationships"
description: "Build Eloquent models for all CRM entities and define relationships using Laravel's expressive ORM syntax"
sidebar:
  label: "06: Eloquent Models & Relationships"
  order: 6
  badge:
    text: Intermediate
    variant: caution
---
![Eloquent Models & Relationships](/images/build-crm-laravel-12/chapter-06-eloquent-models-relationships-hero-full.webp)

# Chapter 06: Eloquent Models & Relationships

## Overview

With your database schema in place, it's time to create Eloquent models that provide an object-oriented interface to your data. Eloquent is Laravel's elegant ORM (Object-Relational Mapping) that makes database interactions intuitive and expressive.

In this chapter, you'll generate models for User, Team, Company, Contact, Deal, and Task with comprehensive relationships. Beyond basic one-to-many relationships, you'll implement **multi-tenancy via Team scoping**, **user assignment patterns** for accountability, **soft deletes for data preservation**, and **pseudo-polymorphic relationships** for flexibility. You'll define relationships using Eloquent's relationship methods, allowing you to access related data naturally (e.g., `$company->contacts` or `$deal->assignedTo->name`). You'll also configure fillable fields, add casts for type conversion, implement accessors, and use query scopes.

By the end of this chapter, you'll have a production-ready data layer with proper data isolation, user-based access control preparation, and enterprise-grade patterns. You'll understand how Eloquent translates object calls into SQL queries and how relationships make working with relational data elegant, secure, and efficient.

This chapter is primarily code generation with relationship definitions—no UI yet. The models you create will power the controllers and business logic in Chapter 07.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 05](/series/build-crm-laravel-12/chapters/05-database-migrations-schema-design) with database migrated
- ✅ Understanding of object-oriented PHP and Eloquent basics
- ✅ Familiarity with database relationships (one-to-many, many-to-many)
- ✅ Knowledge of data isolation concepts (multi-tenancy)
- ✅ Understanding of soft delete patterns

**Estimated Time**: ~75 minutes (extended for multi-tenancy and advanced patterns)

## What You'll Build

By the end of this chapter, you will have:

**Core Models**:
- Eloquent models: `User`, `Team`, `Company`, `Contact`, `Deal`, `Task`
- User model integrated with team system

**Relationships**:
- `hasMany`, `belongsTo`, `belongsToMany` (Team ↔ User via pivot)
- Multi-tenancy scoping via `team_id` on all data entities
- User assignment relationships (`owner`, `assignedTo`)
- Pseudo-polymorphic relationships (Task → Contact OR Deal)

**Advanced Features**:
- SoftDeletes trait on Company, Contact, Deal, Task
- Fillable fields configured for mass assignment protection
- Type casts for dates, booleans, decimals, and enums
- Accessors for computed properties (e.g., `full_name`)
- Query scopes for reusable business logic filters
- HasFactory trait for testing and seeding readiness
- Comprehensive troubleshooting for all patterns

**Production Patterns**:
- Data isolation via multi-tenancy
- Accountability via user assignment
- Data preservation via soft deletes
- Flexibility via pseudo-polymorphic design
- Performance optimization via eager loading

## Objectives

**Foundational**:
- Generate Eloquent models using Artisan
- Define one-to-many relationships (hasMany/belongsTo)
- Define inverse relationships (BelongsTo)
- Configure fillable and guarded properties
- Add casts for proper type handling

**Advanced**:
- Create many-to-many relationships (BelongsToMany) with pivot data
- Implement multi-tenancy via team_id scoping
- Apply user assignment patterns for accountability
- Use SoftDeletes trait for data preservation
- Create pseudo-polymorphic relationships

**Optimization & Practice**:
- Understand lazy vs. eager loading
- Create query scopes for reusable filters
- Build accessors for computed properties
- Use Tinker to test all relationship patterns
- Validate multi-tenancy data isolation

## Step 1: Generate Model Files (~5 min)

### Goal

Generate Eloquent model files for all CRM entities (Team, Contact, Company, Deal, Task) using Artisan commands.

### Actions

1. **Generate models with migrations**:

```bash
# Navigate to your project directory
cd crm-app

# Generate models with -m flag to create associated migrations
# (These are optional since migrations already exist, but -m creates model methods)
sail artisan make:model Team -m
sail artisan make:model Contact -m
sail artisan make:model Company -m
sail artisan make:model Deal -m
sail artisan make:model Task -m
```

2. **Verify model files were created**:

```bash
# List the generated models
ls app/Models/

# You should see:
# Team.php
# Contact.php
# Company.php
# Deal.php
# Task.php
# User.php (created by default)
```

3. **Check a generated model file**:

```bash
# View the skeleton model
cat app/Models/Team.php
```

### Expected Result

```
app/Models/ directory now contains:
- Team.php
- Contact.php
- Company.php
- Deal.php
- Task.php
- User.php

Each file is a basic Eloquent model class extending Model with an empty class body.
```

### Why It Works

Laravel's `make:model` command generates model classes that extend Eloquent's `Model` base class. By convention, Eloquent assumes:
- Model name `Team` maps to table `teams`
- Model name `Contact` maps to table `contacts`
- And so on (Laravel pluralizes automatically)

The `-m` flag creates empty migration files (which we skip since migrations already exist from Chapter 05). Even without migrations, models are ready to interact with existing tables.

### Troubleshooting

- **Models not appearing in `app/Models/`** — Verify your current directory is the Laravel project root (crm-app). Run `pwd` to confirm.
- **Error: "Class not found"** — Ensure you ran the artisan commands via `sail artisan` (not just `artisan`), as Sail provides the containerized PHP environment.
- **Namespace issues** — Models are auto-namespaced to `App\Models`. If you import them, use `use App\Models\Team;`

## Step 2: Define Team Model & User Integration (~10 min)

### Goal

Define the Team model as the central multi-tenancy container, with relationships to User (ownership + membership) and all CRM entities.

### Actions

1. **Update User model first** (critical for team integration):

```php
# filename: app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'team_id',  // 🔑 CRITICAL: Multi-tenancy scope for this user
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class);
    }
}
```

2. **Create Team model with multi-tenancy relationships**:

```php
# filename: app/Models/Team.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',  // Owner of the team
        'slug',
    ];

    // ============ OWNERSHIP & MEMBERSHIP ============

    /**
     * The User who owns this Team
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all Users belonging to this Team (via pivot table)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->withTimestamps()
                    ->withPivot('role');
    }

    // ============ CRM ENTITIES (Multi-tenancy scope) ============

    /**
     * Get all Contacts for this Team
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get all Companies for this Team
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Get all Deals for this Team
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get all Tasks for this Team
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

3. **Access Pivot Table Data**:

The Team ↔ User many-to-many relationship stores metadata (like `role`) in the pivot table. Here's how to access and update it:

```bash
sail artisan tinker

# Access pivot data (role) from a team member
$team = Team::first();
$user = $team->users()->first();
echo $user->pivot->role;  # Returns: 'owner', 'admin', or 'member'

# Access pivot data from the user side
$user = User::first();
$team = $user->teams()->first();
echo $team->pivot->role;  # Same role, accessed from user perspective

# Update pivot data (change a user's role)
$team->users()->updateExistingPivot($user->id, ['role' => 'admin']);

# Or using the attach/sync pattern
$team->users()->sync([
    $user->id => ['role' => 'member'],
    // Add more users as needed
]);
```

4. **Verify in Tinker**:

```bash
sail artisan tinker

# Test owner relationship
$team = Team::first();
$team->owner;  # Should return the User who owns this team

# Test membership and roles
$team->users()->count();  # Count of team members
$team->users()->first()->pivot->role;  # First user's role
```

### Expected Result

```
Team model provides:
- owner(): Returns the User who owns this team
- users(): Returns all team members (many-to-many with role metadata)
- contacts(), companies(), deals(), tasks(): Multi-tenancy scoping

Pivot Table Data:
- Access role: $user->pivot->role (returns 'owner', 'admin', 'member')
- Update role: $team->users()->updateExistingPivot($userId, ['role' => 'admin'])
- Sync members: $team->users()->sync([...])

Key insight: Team is the ROOT entity for multi-tenancy.
All other entities scope to a single team.
```

### Why It Works

**Multi-tenancy pattern**: A Team is a container that owns all associated data. Every Contact, Company, Deal, and Task must belong to exactly one Team. This ensures:
1. Data isolation between teams
2. Simple filtering: `Contact::where('team_id', $teamId)->get()`
3. Automatic scoping: `$team->contacts` returns only this team's contacts

**Many-to-many with pivot**: The `belongsToMany` relationship creates a `team_user` pivot table that can store additional metadata (like user `role`) through `withPivot()`.

### Troubleshooting

- **"Column team_id does not exist on users"** — Ensure Chapter 05 migration added team_id to users table
- **Many-to-many not working** — Verify `team_user` pivot table exists from migrations
- **Pivot data returns null** — Use `withPivot('role')` in relationship definition to include pivot columns
- **Cannot update pivot** — Verify user is attached to team first: `$team->users()->attach($user->id, ['role' => 'member'])`
- **Relationship returns empty** — Create test data first: `$team = Team::create(['name' => 'Test', 'user_id' => 1])`

## Step 3: Define Company Model (~10 min)

### Goal

Define the Company model with multi-tenancy scoping and relationships to Contacts and Deals.

### Actions

1. **Create Company model with SoftDeletes**:

```php
# filename: app/Models/Company.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'email',
        'phone',
        'website',
        'address_street',
        'address_city',
        'address_state',
        'address_zip',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Multi-tenancy: Company belongs to a Team
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // Company has many Contacts
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    // Company has many Deals
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
```

2. **Understand SoftDeletes**:

The `SoftDeletes` trait adds soft delete functionality:
- `delete()` sets `deleted_at` timestamp instead of removing the record
- Deleted records are automatically excluded from queries
- Use `withTrashed()` to include soft-deleted records
- Use `restore()` to undelete a record

3. **Test in Tinker**:

```bash
sail artisan tinker

# Create a company
$company = Company::create([
    'team_id' => 1,
    'name' => 'Tech Corp',
    'email' => 'info@techcorp.com',
]);

# Access the team
$company->team->name;  # Returns: 'Team name'

# Soft delete
$company->delete();  # Sets deleted_at, doesn't remove from DB

# Will not appear in normal queries
Company::find($company->id);  # Returns: null

# But can be recovered
$company->restore();  # Clears deleted_at
```

### Expected Result

```
Company model supports:
- team(): Returns the Team this company belongs to
- contacts(): Returns all Contacts for this company
- deals(): Returns all Deals for this company
- Soft deletes: Deleted records preserved, not displayed by default
```

### Why It Works

**SoftDeletes**: Business data is often recovered from backups rather than permanently deleted. Soft deletes give flexibility to restore records while keeping normal queries clean.

**Multi-tenancy scoping**: Every Company must have a `team_id`. This ensures that data from different teams is never mixed, providing natural data isolation.

### Troubleshooting

- **"Column deleted_at does not exist"** — Chapter 05 migration must have added `softDeletes()` column
- **Company appears after delete** — Use `->onlyTrashed()` to show only deleted records
- **Restore not working** — Ensure `deleted_at` is NULL before checking normal queries

## Step 4: Define Contact Model (~10 min)

### Goal

Define the Contact model with multi-tenancy, company assignment, user ownership, and a full-name accessor.

### Actions

1. **Create Contact model with relationships**:

```php
# filename: app/Models/Contact.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'company_id',
        'user_id',  // Owner/assigned user
        'first_name',
        'last_name',
        'email',
        'phone',
        'title',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============ MULTI-TENANCY ============

    /**
     * Multi-tenancy: Contact belongs to a Team
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // ============ RELATIONSHIPS ============

    /**
     * The Company this contact works for
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The User assigned as the contact owner
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all Deals associated with this Contact
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get all Tasks associated with this Contact
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // ============ ACCESSORS & MUTATORS ============

    /**
     * Get the contact's full name (first_name + last_name) - ACCESSOR (getter)
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Automatically normalize email to lowercase - MUTATOR (setter)
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtolower($value),
            set: fn ($value) => strtolower($value),
        );
    }
}
```

2. **Understanding Accessors vs. Mutators**:

The Contact model shows both patterns:
- **Accessor** (getter): `fullName()` computes a virtual property when accessed
- **Mutator** (setter): `email()` transforms data when it's stored or retrieved

In the example above:
- `$contact->full_name` automatically combines first + last name (accessor)
- `$contact->email = 'JANE@EXAMPLE.COM'` automatically converts to lowercase (mutator)

3. **Understanding the User Assignment Pattern**:

The `user_id` field represents the **owner** or **assigned rep** for this contact:
- One contact can have one owner (many-to-one via BelongsTo)
- One user can own many contacts (one-to-many on User model)
- This enables assignment and tracking of responsibility

4. **Test in Tinker**:

```bash
sail artisan tinker

# Create test data
$user = User::first();
$company = Company::first();
$contact = Contact::create([
    'team_id' => 1,
    'company_id' => $company->id,
    'user_id' => $user->id,
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'email' => 'JANE@EXAMPLE.COM',  # Note: uppercase
]);

# Test relationships
$contact->owner->name;  # Returns: User's name
$contact->company->name;  # Returns: Company name
$contact->full_name;  # Accessor: "Jane Smith"

# Test mutator
$contact->email;  # Returns: 'jane@example.com' (lowercase!)

# Mutator works on creation too
echo $contact->email;  # Outputs: jane@example.com (auto-normalized)
```

### Expected Result

```
Contact model provides:
- team(): Multi-tenancy parent
- company(): The company this contact represents
- owner(): The assigned user/contact owner
- deals(): Related sales deals
- tasks(): Related activities
- full_name: Accessor (computed) property combining first/last names
- email: Mutator that normalizes to lowercase (both get & set)

Relationships: Contact → Company → Team (hierarchy)
             Contact → User (ownership)

Attribute Transformations:
- Accessor: $contact->full_name (computed on read)
- Mutator: $contact->email automatically lowercases (both read & write)
```

### Why It Works

**Multi-tenancy**: `team_id` ensures each contact belongs to exactly one team, preventing cross-team data mixing.

**User assignment**: The `user_id` field enables role-based access control (RBAC). In Chapter 07, you'll verify that users can only see contacts assigned to them.

**Accessors**: The `full_name` attribute is computed on-the-fly. It never appears in the database, but is automatically available when accessing `$contact->full_name`.

### Troubleshooting

- **"Column user_id does not exist"** — Chapter 05 migration must have added user_id foreign key
- **Accessor returning null or blank** — Check first_name/last_name are set: `dd($contact->first_name)`
- **Mutator not working** — Ensure both `get:` and `set:` are defined in `Attribute::make()`. Check `$casts` if transforming field types.
- **Email not lowercased** — Verify mutator method returns `Attribute::make()` with both `get:` and `set:` callbacks
- **Owner relationship returns null** — user_id might be NULL. Assign a valid user_id when creating contact

## Step 5: Define Deal Model (~10 min)

### Goal

Define the Deal model with relationships to Team, Company, and Task, and add an Enum for deal status values.

### Actions

1. **Create a Status Enum** (optional but recommended for deal status):

```bash
# Generate an Enum class
sail artisan make:enum DealStatus
```

2. **Update the DealStatus Enum**:

```php
# filename: app/Enums/DealStatus.php
<?php

namespace App\Enums;

enum DealStatus: string
{
    case Prospect = 'prospect';
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match($this) {
            self::Prospect => 'Prospect',
            self::Qualified => 'Qualified',
            self::Proposal => 'Proposal',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }
}
```

3. **Create Deal model with user assignment and soft deletes**:

```php
# filename: app/Models/Deal.php
<?php

namespace App\Models;

use App\Enums\DealStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'company_id',
        'contact_id',
        'user_id',  // Assigned sales representative
        'title',
        'amount',
        'stage',
        'expected_close_date',
        'details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'stage' => DealStatus::class,  // Pipeline stage (prospect, qualified, won, lost, etc.)
        'expected_close_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============ MULTI-TENANCY ============

    /**
     * Multi-tenancy: Deal belongs to a Team
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // ============ RELATIONSHIPS ============

    /**
     * The Company for this Deal
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The primary Contact for this Deal (optional)
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->nullable();
    }

    /**
     * The User assigned as the sales representative
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all Tasks associated with this Deal
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

4. **Test in Tinker**:

```bash
sail artisan tinker

# Create test data
$user = User::first();
$company = Company::first();
$deal = Deal::create([
    'team_id' => 1,
    'company_id' => $company->id,
    'user_id' => $user->id,
    'title' => 'Enterprise Software License',
    'amount' => 50000.00,
    'stage' => 'qualified',
    'expected_close_date' => now()->addDays(30),
]);

# Test relationships
$deal->assignedTo->name;  # Returns: sales rep's name
$deal->stage->label();  # Returns: "Qualified"
$deal->company->name;  # Company name
```

### Expected Result

```
Deal model provides:
- team(): Multi-tenancy parent
- company(): The company for this deal
- contact(): Optional primary contact
- assignedTo(): Assigned sales representative
- tasks(): Related activities/tasks
- Enum-based stage (prospect → negotiation → won/lost)
- Amount with 2 decimal place precision
- Soft delete support

Multi-tenancy + User assignment pattern:
  Deal → Team (data isolation)
  Deal → User (responsibility tracking)
```

### Why It Works

**Multi-tenancy**: Every Deal must belong to exactly one Team, ensuring data stays properly scoped.

**User assignment** (`assignedTo` relationship): The `user_id` field enables tracking who owns each sales opportunity. This creates accountability and enables features like "show me only my deals."

**Enum casting for stage**: The pipeline stage is type-safe. You can use constants like `DealStatus::Won` instead of magic strings like `'won'`.

**SoftDeletes**: Deleted deals are preserved but hidden from normal queries, enabling recovery if needed.

### Troubleshooting

- **"Undefined enum case: stage"** — Ensure DealStatus enum exists and is imported
- **Amount showing incorrect precision** — Check `'amount' => 'decimal:2'` is in $casts
- **Assigned user returns null** — Verify user_id is set when creating the deal

## Step 6: Define Task Model (~10 min)

### Goal

Define the Task model representing activities/tasks related to deals and contacts.

### Actions

1. **Create TaskStatus Enum** (optional but recommended):

```bash
sail artisan make:enum TaskStatus
```

2. **Update TaskStatus Enum**:

```php
# filename: app/Enums/TaskStatus.php
<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
```

3. **Create Task Model with user assignment and flexible relationships**:

```php
# filename: app/Models/Task.php
<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',      // Assigned user
        'contact_id',   // Can be related to a Contact (nullable)
        'deal_id',      // Or to a Deal (nullable) - pseudo-polymorphic
        'title',
        'description',
        'is_complete',  // Boolean completion flag
        'due_at',       // When the task is due
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============ MULTI-TENANCY ============

    /**
     * Multi-tenancy: Task belongs to a Team
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // ============ USER ASSIGNMENT ============

    /**
     * The User assigned to this task
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ============ FLEXIBLE RELATIONSHIPS (Pseudo-Polymorphic) ============

    /**
     * Task can be related to a Contact (nullable)
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->nullable();
    }

    /**
     * Task can be related to a Deal (nullable)
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class)->nullable();
    }

    // ============ QUERY SCOPES ============

    /**
     * Scope: Get only completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_complete', true);
    }

    /**
     * Scope: Get only open/incomplete tasks
     */
    public function scopeOpen($query)
    {
        return $query->where('is_complete', false);
    }
}
```

4. **Test in Tinker**:

```bash
sail artisan tinker

# Create test task assigned to a user
$user = User::first();
$deal = Deal::first();

$task = Task::create([
    'team_id' => 1,
    'user_id' => $user->id,
    'deal_id' => $deal->id,
    'title' => 'Follow up with client',
    'description' => 'Call to discuss proposal',
    'due_at' => now()->addDays(2),
    'is_complete' => false,
]);

# Test relationships
$task->assignedTo->name;  # Sales rep's name
$task->deal->company->name;  # Access deal -> company

# Test query scopes
$openTasks = Task::open()->get();  # Incomplete tasks
$completed = Task::completed()->get();  # Completed tasks

# Mark complete
$task->update(['is_complete' => true]);
Task::completed()->count();  # Now includes this task
```

### Expected Result

```
Task model provides:
- team(): Multi-tenancy parent
- assignedTo(): Assigned user/responsible party
- contact(): Optional contact relationship
- deal(): Optional deal relationship
- Flexible pseudo-polymorphic design (can link to Contact OR Deal)
- Boolean is_complete flag for completion tracking
- Query scopes: ->open() and ->completed()
- Soft delete support
- DateTime casting for due_at

Multi-tenancy + User assignment + Flexible relationships:
  Task → Team (data isolation)
  Task → User (responsibility)
  Task → Contact or Deal (context - not both)
```

### Why It Works

**Pseudo-Polymorphic relationships**: Tasks can relate to either a Contact (standalone activity) or a Deal (opportunity-specific task). Both `contact_id` and `deal_id` are nullable, allowing flexibility:
- Task for Contact: "Call Jane to get missing info"
- Task for Deal: "Send contract to legal review"
- Standalone Task: "Attend industry conference"

**Boolean is_complete**: Simpler than enum status (just done or not done).

**Query scopes**: `Task::open()` and `Task::completed()` provide readable filters without repeated `where()` clauses.

**Soft deletes**: Deleted tasks are preserved but hidden from normal queries.

### Troubleshooting

- **Scopes not working** — Method name format: `scopeOpen()` → accessed as `->open()`
- **Multiple nullable FKs** — Allowed: Tasks can relate to Contact OR Deal (not both required)
- **is_complete not storing** — Verify `'is_complete' => 'boolean'` is in $casts
- **due_at showing wrong format** — Check `'due_at' => 'datetime'` in $casts

## Step 7: Test Relationships in Tinker (~10 min)

### Goal

Test all model relationships interactively using Laravel Tinker to ensure everything works end-to-end.

### Actions

1. **Start Tinker**:

```bash
sail artisan tinker
```

2. **Create test data using factories** (in Tinker session):

You have two options: manual creation or using factories. Factories are more efficient for generating realistic test data:

**Option A: Using Factories** (Recommended):

```php
# Create a team (using factory)
$team = Team::factory()->create(['name' => 'Sales Team', 'slug' => 'sales']);

# Create multiple companies for testing
$companies = Company::factory(3)->create(['team_id' => $team->id]);

# Create contacts for first company
$contacts = Contact::factory(5)->create([
    'team_id' => $team->id,
    'company_id' => $companies->first()->id,
]);

# Create deals with various statuses
$deals = Deal::factory(2)->create([
    'team_id' => $team->id,
    'company_id' => $companies->first()->id,
]);

# Create tasks
$tasks = Task::factory(10)->create(['team_id' => $team->id]);
```

**Option B: Manual Creation** (if you prefer explicit data):

```php
# Create a team
$team = Team::create(['name' => 'Sales Team', 'slug' => 'sales']);

# Create a company in the team
$company = $team->companies()->create([
    'name' => 'Tech Innovations Inc',
    'email' => 'info@techinnovations.com',
    'phone' => '555-0100',
    'website' => 'techinnovations.com',
    'industry' => 'Technology',
    'size' => 'medium',
]);

# Create contacts for the company
$contact1 = $company->contacts()->create([
    'team_id' => $team->id,
    'first_name' => 'Sarah',
    'last_name' => 'Johnson',
    'email' => 'sarah@techinnovations.com',
    'phone' => '555-0101',
    'position' => 'VP Sales',
]);

$contact2 = $company->contacts()->create([
    'team_id' => $team->id,
    'first_name' => 'Mike',
    'last_name' => 'Chen',
    'email' => 'mike@techinnovations.com',
    'phone' => '555-0102',
    'position' => 'CTO',
]);

# Create a deal
$deal = $company->deals()->create([
    'team_id' => $team->id,
    'contact_id' => $contact1->id,
    'user_id' => User::first()->id,  # Assign sales rep
    'title' => 'Enterprise License Agreement',
    'amount' => 50000.00,
    'stage' => 'qualified',  # Note: field name is 'stage' (not 'status')
    'expected_close_date' => now()->addDays(30),
]);

# Create tasks for the deal
$task1 = $deal->tasks()->create([
    'team_id' => $team->id,
    'user_id' => User::first()->id,  # Assign user
    'title' => 'Send proposal',
    'description' => 'Email enterprise package proposal to Sarah',
    'is_complete' => false,  # Note: field name is 'is_complete' (not 'status')
    'due_at' => now()->addDays(2),
]);

$task2 = $deal->tasks()->create([
    'team_id' => $team->id,
    'user_id' => User::first()->id,  # Assign user
    'title' => 'Follow-up call',
    'description' => 'Call Mike to discuss technical requirements',
    'is_complete' => false,
    'due_at' => now()->addDays(5),
]);
```

3. **Test relationships** (still in Tinker):

```php
# Test basic relationships
$company->contacts()->count();  # Returns: 2
$deal->tasks()->count();  # Returns: 2

# Test inverse relationships
$contact1->company->name;  # Returns: "Tech Innovations Inc"
$task1->deal->title;  # Returns: "Enterprise License Agreement"

# Test accessors
$contact1->full_name;  # Returns: "Sarah Johnson"

# Test eager loading (important for performance)
$companies = Company::with('contacts', 'deals')->get();
# Now accessing $companies->contacts and $companies->deals won't hit database again

# Test enum usage
$deal->stage === 'qualified';  # True (can compare as string)
$deal->stage->label();  # Returns: "Qualified"

# Test query scopes
$openTasks = Task::open()->get();  # Returns tasks with is_complete = false
$completedTasks = Task::completed()->get();  # Returns tasks with is_complete = true

# Test user assignments
$deal->assignedTo->name;  # Returns assigned sales rep name
$task1->assignedTo->name;  # Returns assigned user name

# Test deep relationships
$task1->deal->company->name;  # Returns: "Tech Innovations Inc" (3 levels deep)
```

4. **Exit Tinker**:

```php
exit
```

### Expected Result

```
All relationships work correctly:

✓ MULTI-TENANCY:
  - Team isolates all data (companies, contacts, deals, tasks)
  - Each record scoped to team_id
  - No cross-team data leakage

✓ RELATIONSHIPS:
  - Team ↔ User (many-to-many with role via pivot)
  - Team → Company/Contact/Deal/Task (one-to-many)
  - Company → Contact/Deal (one-to-many)
  - Contact → Company/Team/Deal/Task (many-to-one)
  - Deal → Team/Company/Contact/Task (relationships)
  - Task ↔ Contact/Deal (pseudo-polymorphic, both nullable)

✓ PIVOT DATA:
  - Access roles: $user->pivot->role
  - Update roles: $team->users()->updateExistingPivot()
  - Sync members: $team->users()->sync([...])

✓ USER ASSIGNMENT:
  - Contact has owner() relationship
  - Deal has assignedTo() relationship
  - Task has assignedTo() relationship
  - Enables accountability tracking

✓ ATTRIBUTE TRANSFORMATION:
  - Accessors compute values (full_name)
  - Mutators normalize data (email lowercase)
  - Both work on read and write

✓ DATA SAFETY:
  - Soft deletes hide deleted records
  - Restore capability preserved
  - Normal queries exclude deleted records

✓ TEST DATA GENERATION:
  - Factories create realistic test data: Model::factory()->create()
  - Multiple records: Model::factory(5)->create()
  - With constraints: Model::factory()->create(['field' => 'value'])

✓ COMPUTED PROPERTIES:
  - Contact full_name accessor works
  - All enum labels accessible
  - Type casts applied automatically

✓ PERFORMANCE:
  - Eager loading prevents N+1 queries
  - Query scopes filter efficiently
  - Relationships chain properly
```

### Why It Works

**Relationships in Eloquent** are built on SQL foreign keys. When you call `$team->companies()`, Eloquent:
1. Sees the relationship method returns `HasMany`
2. Assumes foreign key is `team_id` in companies table
3. Executes: `SELECT * FROM companies WHERE team_id = ?`
4. Returns a collection or query builder for chaining

**Eager loading** (`with()`) pre-fetches related data in fewer queries:
- Without eager loading: 1 query to get companies + N queries to get each company's contacts (N+1 problem)
- With eager loading: 2 queries total (1 for companies, 1 for all related contacts)

**Nested relationships** (`$task->deal->company->name`) work because each model knows its relationships. Eloquent lazily loads each level as needed (or eagerly with proper `with()` chains).

### Troubleshooting

**General Relationship Issues**:
- **"Column not found" error** — Foreign key doesn't exist in database. Run `sail artisan migrate` to ensure Chapter 05 tables/columns created.
- **Relationship returns null** — Foreign key value is NULL or incorrect. Verify you're using correct parent ID when creating child records.
- **"BadMethodCallException" error** — Method name misspelled. Check relationship names match model definitions exactly.

**Multi-Tenancy Issues**:
- **Seeing data from other teams** — Verify team_id is set on all records. Use `Team::first()->companies` to see scoped data only.
- **team_id becomes NULL** — Ensure you're passing team_id when creating related models, or use relationship method: `$team->companies()->create([...])`

**User Assignment Issues**:
- **Owner/AssignedTo returns null** — user_id might be NULL. Assign valid user_id when creating record: `'user_id' => User::first()->id`
- **"Column user_id does not exist"** — Verify Chapter 05 added user_id foreign key to contacts, deals, tasks tables.

**Soft Delete Issues**:
- **Deleted records still appearing** — Use `->withTrashed()` if you want to include soft-deleted records. Normal queries exclude them.
- **Cannot restore** — Verify model has `use SoftDeletes;` trait. Call `$record->restore()` only on soft-deleted records.

**Enum & Type Casting Issues**:
- **Enum case error** — Using wrong case: `'qualified'` instead of `DealStatus::Qualified` (or string `'qualified'` for DealStatus).
- **Boolean field wrong** — Check `'is_complete' => 'boolean'` is in $casts. Boolean values must be true/false, not strings.
- **Date formatting wrong** — Verify `'due_at' => 'datetime'` in $casts for datetime fields.

**Test Data & Factory Issues**:
- **Factory not working** — Verify model has `use HasFactory;` trait
- **Factory returns wrong data** — Check factory file in `database/factories/` for default state
- **Multiple records not created** — Use correct syntax: `Model::factory(5)->create()` (not `->create(5)`)

**N+1 Query Problem**:
- **Performance degrading** — Use eager loading: `Company::with('contacts', 'deals')->get()` instead of looping.
- **Checking query count** — Enable query logging: `Log::info(DB::getQueryLog());` or use `DB::enableQueryLog()` in Tinker.

## Wrap-up

Congratulations! You've now built a **production-ready data layer** for your SaaS CRM using Eloquent models and advanced relationship patterns. Your models provide:

**Core Models**:
- ✅ **User** — Authentication + team assignment, with team ownership and membership
- ✅ **Team** — Multi-tenancy parent container, isolates data between teams
- ✅ **Company** — Customer organizations with soft deletes for data safety
- ✅ **Contact** — People records with owner assignment and full-name accessors
- ✅ **Deal** — Sales pipeline with enum stages, user assignment, and task tracking
- ✅ **Task** — Flexible activities (Contact, Deal, or standalone) with completion tracking

**Architecture Patterns Mastered**:
- ✅ **Multi-tenancy** — team_id scoping on all data entities for data isolation
- ✅ **User assignment** — owner() and assignedTo() relationships for accountability
- ✅ **Data preservation** — SoftDeletes trait for recovery without deletion
- ✅ **Flexible relationships** — Pseudo-polymorphic tasks linking to Contact OR Deal
- ✅ **One-to-many** relationships (hasMany/belongsTo)
- ✅ **Many-to-many** relationships (BelongsToMany) with pivot metadata
- ✅ **Accessors** for computed properties
- ✅ **Query scopes** for reusable business logic
- ✅ **Type casting** for automatic conversion (enums, booleans, decimals, dates)
- ✅ **Eager loading** for performance optimization
- ✅ **Bidirectional** relationship access

**Production Features**:
- ✅ Automatic data isolation between teams
- ✅ Accountability through user assignments
- ✅ Data recovery via soft deletes
- ✅ Type-safe enums (DealStatus, TaskStatus)
- ✅ Role-based access control foundation (RBAC) ready for Chapter 07
- ✅ Enterprise-grade relationship design

**What's Next**:
In Chapter 07, you'll create controllers and authentication to use these models for CRUD operations, implementing role-based access control so users only see their team's data. Your multi-tenant models now power a secure, scalable SaaS application.

**Your Architecture is Ready**:
You have a fully relational database schema with models that enforce data isolation, track accountability, and preserve data safely. Eloquent abstracts away SQL complexity while maintaining security, flexibility, and power. This is enterprise-ready foundation code.

## Exercises

### Exercise 1: Query Relationships Across Models (~10 min)

**Goal**: Practice accessing data through multiple relationship levels

Write Tinker commands to find all tasks for a specific company's deals:

```bash
sail artisan tinker

# Find a company
$company = Company::where('name', 'Tech Innovations Inc')->first();

# Get all deals for that company
$deals = $company->deals;  # or $company->deals()->get()

# For each deal, get all open tasks
$openTasks = [];
foreach ($deals as $deal) {
    $openTasks = array_merge($openTasks, $deal->tasks()->open()->get()->all());
}

# Or using eager loading (more efficient):
$deals = $company->deals()->with('tasks')->get();
$openTasks = $deals->flatMap(fn($deal) => $deal->tasks()->open()->get());

echo count($openTasks);  # Display the count
```

**Validation**: You can access tasks nested 3 levels deep (Company → Deals → Tasks) and filter using query scopes.

Expected output:
```
2
```

### Exercise 2: Create Complex Related Records (~15 min)

**Goal**: Build a realistic data structure using model relationships

In Tinker, create a complete scenario:

```bash
sail artisan tinker

# 1. Create a new team
$team = Team::create(['name' => 'Customer Success', 'slug' => 'cs']);

# 2. Create 2 companies in the team
$acme = $team->companies()->create([
    'name' => 'Acme Corp',
    'email' => 'info@acme.com',
    'phone' => '555-0200',
    'website' => 'acme.com',
    'industry' => 'Manufacturing',
    'size' => 'large',
]);

$startup = $team->companies()->create([
    'name' => 'Startup Inc',
    'email' => 'contact@startup.io',
    'phone' => '555-0300',
    'website' => 'startup.io',
    'industry' => 'Software',
    'size' => 'small',
]);

# 3. Create contacts for Acme
$cto = $acme->contacts()->create([
    'team_id' => $team->id,
    'first_name' => 'Alice',
    'last_name' => 'Rodriguez',
    'email' => 'alice@acme.com',
    'position' => 'CTO',
]);

# 4. Create a deal with Startup
$deal = $startup->deals()->create([
    'team_id' => $team->id,
    'contact_id' => $cto->id,  # Contact from different company (valid!)
    'title' => 'Software Migration Project',
    'amount' => 75000.00,
    'currency' => 'USD',
    'status' => 'proposal',
    'expected_close_date' => now()->addDays(45),
]);

# 5. Create tasks for the deal
$deal->tasks()->create([
    'team_id' => $team->id,
    'title' => 'Requirements gathering',
    'status' => 'in_progress',
    'due_date' => now()->addDays(3),
]);

# 6. Verify structure
echo "Team: " . $team->name . "\n";
echo "Companies: " . $team->companies()->count() . "\n";
echo "Deal amount: $" . $deal->amount . "\n";
echo "Deal status: " . $deal->status->label() . "\n";  # Should print: "Proposal"
```

**Validation**: All relationships work correctly and you can create complex nested structures in a single workflow.

### Exercise 3: Filter with Query Scopes (~10 min)

**Goal**: Use model query scopes for practical filtering

Create tasks with different statuses, then filter them:

```bash
sail artisan tinker

# Create multiple tasks with different statuses
$team = Team::first();
$task1 = Task::create(['team_id' => $team->id, 'title' => 'Open task', 'status' => 'open', 'due_date' => now()]);
$task2 = Task::create(['team_id' => $team->id, 'title' => 'In progress', 'status' => 'in_progress', 'due_date' => now()]);
$task3 = Task::create(['team_id' => $team->id, 'title' => 'Completed', 'status' => 'completed', 'due_date' => now()]);
$task4 = Task::create(['team_id' => $team->id, 'title' => 'Cancelled', 'status' => 'cancelled', 'due_date' => now()]);

# Use scopes to filter
$open = Task::open()->count();       # Should return: 2 (open + in_progress)
$completed = Task::completed()->count();  # Should return: 1
$all = Task::count();                # Should return: 4+

echo "Open tasks: " . $open . "\n";
echo "Completed: " . $completed . "\n";
echo "Total: " . $all . "\n";
```

**Validation**: Scopes properly filter records and chain with other query methods.

### Challenge Exercise: Implement Your Own Scope (~20 min)

**Goal**: Add custom query scopes to Task model

Extend the Task model with a new scope. In `app/Models/Task.php`, add:

```php
// Add to Task model:
public function scopeOverdue($query)
{
    return $query->where('due_date', '<', now()->startOfDay())
                 ->where('status', '!=', 'completed');
}
```

Then test it:

```bash
sail artisan tinker

# Create an overdue task
Task::create([
    'team_id' => 1,
    'title' => 'Urgent: Old task',
    'status' => 'open',
    'due_date' => now()->subDays(5),  # 5 days ago
]);

# Use your new scope
$overdue = Task::overdue()->get();
echo "Overdue tasks: " . count($overdue) . "\n";
```

**Validation**: Your custom scope works and filters correctly based on due date logic.


## Further Reading

- [Eloquent Models](https://laravel.com/docs/12.x/eloquent) - Complete Eloquent reference
- [Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships) - All relationship types
- [Eager Loading](https://laravel.com/docs/12.x/eloquent-relationships#eager-loading) - Performance optimization
- [Accessors & Mutators](https://laravel.com/docs/12.x/eloquent-mutators) - Computed properties

