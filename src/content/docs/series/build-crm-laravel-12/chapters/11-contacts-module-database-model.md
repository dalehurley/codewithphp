---
title: "11: Contacts Module - Database & Model"
description: "Expand the contacts table with professional fields and refine the Contact model with relationships, preparing the foundation for contact management"
sidebar:
  label: "11: Contacts Module - Database & Model"
  order: 11
  badge:
    text: Intermediate
    variant: caution
---
![Contacts Module - Database & Model](/images/build-crm-laravel-12/chapter-11-contacts-module-database-model-hero-full.webp)

# Chapter 11: Contacts Module - Database & Model

## Overview

With your UI foundation complete, it's time to build the first data module of your CRM: **Contacts**. Contacts are the cornerstone of any CRM—they represent the people you do business with.

In this chapter, you'll verify the contacts table structure, refine the Contact Eloquent model with proper configuration, implement a `full_name` accessor for the frontend, and create a reusable `HasTeamScope` trait to enforce multi-tenancy security.

By the end, you'll have a production-ready Contact model that automatically enforces team-based data isolation and is ready for CRUD operations in Chapter 12. The `HasTeamScope` trait is the security foundation—it ensures every query automatically filters by the current user's team, preventing cross-team data access.

This chapter focuses on model refinement and security preparation—no UI yet. The strong data layer here enables features like "show all contacts for this team" or "all contacts owned by this sales rep," all with automatic security enforcement.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 10](/series/build-crm-laravel-12/chapters/10-layout-ui-design-customization) with UI layout complete
- ✅ Completed [Chapter 06](/series/build-crm-laravel-12/chapters/06-eloquent-models-relationships) with base models generated
- ✅ Laravel Sail running with all containers active
- ✅ Database migrations from Chapter 05 applied (`sail artisan migrate`)
- ✅ Basic understanding of Laravel migrations and Eloquent models
- ✅ Knowledge of database relationships (belongs-to, has-many)

**Estimated Time**: ~50 minutes (streamlined for direct CRUD preparation)

**Verify your setup:**

```bash
# Navigate to your project
cd crm-app

# Verify Sail is running
sail ps  # Should show: laravel.test, mysql, redis all "Up"

# Verify migrations are applied
sail artisan migrate:status  # Should show all migrations as "Ran"

# Verify Contact model exists
cat app/Models/Contact.php  # Should exist from Chapter 06
```

## Quick Start

Want to see the end result in 5 minutes? Here's what you'll accomplish:

```bash
# After completing this chapter:

# 1. Contact model is fully configured
sail artisan tinker
$contact = App\Models\Contact::first();
echo $contact->full_name;  # Returns computed full name

# 2. Global scope automatically filters by team
$teamContacts = App\Models\Contact::all();  # Only current user's team

# 3. Team is auto-set on creation
$newContact = App\Models\Contact::create([...]);
echo $newContact->team_id;  # Automatically set by trait
```

Your Contact model is now secure, optimized, and ready for CRUD operations!

## What You'll Build

By the end of this chapter, you will have:

**Core Foundation**:
- ✅ **Verified contacts table schema** with all professional fields (phone, title, email, team_id, etc.)
- ✅ **Refined Contact model** with proper `$fillable` configuration and relationships
- ✅ **Full-name accessor** (`$contact->full_name`) for convenient frontend access
- ✅ **Multi-tenancy scoping** via the `HasTeamScope` trait

**Security & Patterns**:
- ✅ **`HasTeamScope` trait** for automatic team-based data filtering
- ✅ **Automatic team_id assignment** on contact creation
- ✅ **Global scope mechanism** that prevents cross-team data access
- ✅ **Mass assignment protection** with proper `$fillable` configuration

**Readiness for Chapter 12**:
- ✅ **Complete Contact model** ready for controller integration
- ✅ **Security-first design** that prevents data leakage between teams
- ✅ **Foundation for CRUD operations** (Index, Show, Create, Update, Delete)
- ✅ **Query scopes** for convenient filtering in controllers
- ✅ **Type casting** for proper date handling

## Objectives

**Foundational**:
- Verify the contacts table schema has all required professional fields
- Define all relationships correctly (Team, Company, User, Deals, Tasks)
- Create an accessor for computing the full name property
- Understand multi-tenancy scoping patterns

**Advanced**:
- Implement a reusable `HasTeamScope` trait for automatic team scoping
- Apply the trait to the Contact model
- Prepare the model for global scope queries in Chapter 12
- Ensure mass assignment protection with proper `$fillable` configuration

**Validation & Practice**:
- Test the full_name accessor in Tinker
- Verify the trait automatically sets team_id on creation
- Confirm all relationships work correctly
- Prepare the model for CRUD operations in Chapter 12

## Step 1: Review & Refine Contact Migration (~10 min)

### Goal

Verify the contacts table migration from Chapter 05 has all essential fields for a fully functional CRM contact.

### Actions

1. **Inspect the contacts migration**:

```bash
# Check the migration status
sail artisan migrate:status

# Find and review the contacts migration file
cat database/migrations/2024_XX_XX_XXXXXX_create_contacts_table.php
```

2. **Verify required columns are present**:

The contacts table should contain these essential columns:

```
✓ id (Primary Key)
✓ team_id (Foreign Key - CRITICAL for Multi-Tenancy)
✓ company_id (Foreign Key, nullable)
✓ user_id (Foreign Key - Assigned Owner)
✓ first_name, last_name (Name fields)
✓ email (Unique identifier)
✓ phone, title (Communication & job info)
✓ created_at, updated_at (Timestamps)
✓ deleted_at (Soft deletes)
```

3. **Check database schema**:

```bash
# View the actual database schema
sail artisan schema:show --table=contacts
```

4. **Add missing fields (if needed)**:

If any column is missing, create a new migration:

```bash
sail artisan make:migration add_[field]_to_contacts_table
```

Then add the column in the migration's `up()` method:

```php
public function up(): void
{
    Schema::table('contacts', function (Blueprint $table) {
        $table->string('phone')->nullable()->after('email');
    });
}

public function down(): void
{
    Schema::table('contacts', function (Blueprint $table) {
        $table->dropColumn('phone');
    });
}
```

Run: `sail artisan migrate`

### Expected Result

```
✓ All essential contact fields confirmed in database
✓ team_id present for multi-tenancy scoping
✓ Foreign keys properly configured
✓ Ready for model refinement
```

### Why It Works

**Schema verification** ensures the database foundation is solid before building model logic. The `team_id` field is especially critical—it enables automatic data isolation between different CRM teams in Chapter 12.

### Troubleshooting

- **Migration file not found** — Create one: `sail artisan make:migration create_contacts_table`
- **Column mismatch with model** — Create a new migration to add missing columns
- **Foreign key error** — Ensure referenced tables (teams, companies, users) exist first

## Step 2: Refine Contact Model & Accessors (~15 min)

### Goal

Configure the Contact model with proper mass assignment protection, relationships, and a convenient accessor for the frontend.

### Actions

1. **Configure mass assignment protection**:

Open `app/Models/Contact.php` and ensure the `$fillable` array includes all modifiable fields:

```php
# filename: app/Models/Contact.php

protected $fillable = [
    'team_id',
    'company_id',
    'user_id',
    'first_name',
    'last_name',
    'email',
    'phone',
    'title',
];
```

2. **Implement the full_name Accessor**:

Add this to your Contact model to provide a convenient computed property for React:

```php
# filename: app/Models/Contact.php

use Illuminate\Database\Eloquent\Casts\Attribute;

// ... in Contact class ...

/**
 * Get the contact's full name.
 * This is computed on-the-fly: $contact->full_name returns "John Smith"
 */
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}",
    );
}
```

3. **Confirm relationships are defined**:

Verify these relationships exist in your Contact model:

```php
# filename: app/Models/Contact.php (Essential Relationships)

public function team()
{
    return $this->belongsTo(Team::class);
}

public function company()
{
    return $this->belongsTo(Company::class);
}

public function owner()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function deals()
{
    return $this->hasMany(Deal::class);
}
```

4. **Optional: Add Useful Query Scopes** (for Chapter 12 CRUD):

Query scopes provide convenient filtering methods. Add these to your Contact model for future use in controllers:

```php
# filename: app/Models/Contact.php (Optional Query Scopes)

/**
 * Scope: Filter contacts by company
 */
public function scopeForCompany($query, $companyId)
{
    return $query->where('company_id', $companyId);
}

/**
 * Scope: Filter contacts by owner/assigned user
 */
public function scopeOwnedBy($query, $userId)
{
    return $query->where('user_id', $userId);
}

/**
 * Scope: Search contacts by name or email
 */
public function scopeSearch($query, $term)
{
    return $query->where(function ($q) use ($term) {
        $q->where('first_name', 'like', "%{$term}%")
          ->orWhere('last_name', 'like', "%{$term}%")
          ->orWhere('email', 'like', "%{$term}%");
    });
}
```

These scopes enable readable queries in Chapter 12 controllers (e.g., `Contact::forCompany($id)->search('John')->get()`)

### Expected Result

```
✓ Contact model has $fillable configured for mass assignment
✓ full_name accessor works: $contact->full_name returns "John Smith"
✓ All relationships defined (team, company, owner, deals)
✓ Model ready for CRUD operations and team scoping
```

### Why It Works

**`$fillable` provides security** — Only explicitly listed fields can be mass-assigned, preventing accidental data exposure.

**Accessors provide convenience** — The `full_name` property is computed on-the-fly without database storage, keeping data normalized and reducing redundancy.

**Relationships enable querying** — You can use Eloquent's intuitive syntax instead of raw SQL.

### Adding Type Casting for Production-Ready Model

For a robust Contact model, you should also configure type casting. Add this to your Contact model:

```php
# filename: app/Models/Contact.php

protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
];
```

Type casting ensures dates are converted to Carbon instances, making date operations easier and more reliable.

### Troubleshooting

- **"Column not found" error** — Verify field exists: check `sail artisan schema:show --table=contacts`
- **Accessor returns empty** — Ensure first_name and last_name are populated
- **Relationship returns null** — Verify foreign key has a value: `dd($contact->company_id)`
- **Dates not formatted properly** — Ensure type casts are configured in `$casts` array

## Step 3: Prepare Global Scoping with HasTeamScope Trait (~20 min)

### Goal

Create a reusable `HasTeamScope` trait that will enable automatic multi-tenancy scoping. This trait will be applied to Contact (and later to Company, Deal, and Task models) to ensure users only see data from their team.

### Why This Matters 🔐

**The Problem**: If you forget to add `WHERE team_id = {current_team}` to a query, users could see other teams' data. This is a critical security bug in multi-tenant apps.

**The Solution**: The `HasTeamScope` trait applies this filter **automatically** to every query. Even if a developer forgets, the filter is there. It's "security by default."

**How It Works**:
- Every time `Contact::all()`, `Contact::where()`, or any query runs, it automatically adds `WHERE team_id = {Auth::user()->team_id}`
- When a contact is created, `team_id` is automatically set to the current user's team
- No need to remember to add the filter—it's enforced by the model itself

This is called a **Global Scope**, and it's a best practice in multi-tenant applications.

### Actions

1. **Create the trait directory and file**:

```bash
# Create the Traits directory if it doesn't exist
mkdir -p app/Models/Traits

# Create the trait file
touch app/Models/Traits/HasTeamScope.php
```

2. **Implement the HasTeamScope trait**:

```php
# filename: app/Models/Traits/HasTeamScope.php
<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasTeamScope
{
    /**
     * The "booted" method of the model.
     * This automatically applies team filtering to all queries.
     * 
     * 🚨 In Chapter 12, we'll extract this into a reusable GlobalScope
     * For now, this inline implementation prepares the foundation.
     */
    protected static function bootHasTeamScope(): void
    {
        // Add global scope: automatically filter by user's current team
        static::addGlobalScope('team', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.team_id',
                    Auth::user()->team_id
                );
            }
        });

        // On creation, automatically set the team_id
        static::creating(function ($model) {
            if (!$model->team_id && Auth::check()) {
                $model->team_id = Auth::user()->team_id;
            }
        });
    }
}
```

3. **Apply the trait to the Contact model**:

```php
# filename: app/Models/Contact.php

use App\Models\Traits\HasTeamScope; // Add import

class Contact extends Model
{
    use HasFactory, SoftDeletes, HasTeamScope; // Add the trait

    // ... rest of model ...
}
```

### Expected Result

```
✓ HasTeamScope trait created with global scope logic
✓ Trait automatically filters queries by Auth user's team_id
✓ Trait auto-sets team_id on contact creation
✓ Contact model now uses the trait
✓ Ready for Chapter 12 to implement the Global Scope
```

### Why It Works

**Global scopes enforce security** — Every query against the Contact model automatically includes `WHERE team_id = {current_user_team_id}`, preventing cross-team data access.

**The trait is reusable** — Once working on Contact, you can apply it to Company, Deal, and Task models for consistent multi-tenancy across your CRM.

**Automatic team assignment** — When a user creates a contact, the model hooks automatically set the `team_id` to their current team, preventing manual errors.

### Troubleshooting

- **"Auth not found" error** — Verify Laravel's auth system is set up (done in Chapter 07)
- **Team_id still not being set** — Check if `Auth::check()` is true; verify you're creating via model (not raw SQL)
- **Queries still showing cross-team data** — Verify the trait is imported and used in the Contact model

## Step 4 (Optional): Set Up Contact Factory for Testing (~10 min)

### Goal

For Chapter 12 and beyond, having a factory makes generating test data convenient. If you want to prepare for testing, create a factory now.

### Actions

1. **Generate the Contact factory**:

```bash
sail artisan make:factory ContactFactory
```

2. **Implement basic factory** (for Chapter 12 testing):

```php
# filename: database/factories/ContactFactory.php
<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'title' => $this->faker->jobTitle(),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state([
            'company_id' => $company->id,
            'team_id' => $company->team_id,
        ]);
    }

    public function ownedBy(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }
}
```

3. **Test the factory in Tinker**:

```bash
sail artisan tinker
$contact = App\Models\Contact::factory()->create();
echo $contact->full_name;  # Verify it works
exit
```

**Note**: This is optional in Chapter 11. You can create factories when needed in Chapter 12, or prepare them now for convenience.

---

## Wrap-up 🎉

You have successfully completed the foundation of the Contacts Module:

### What You've Accomplished

- ✅ **Confirmed the database schema** is complete with all required professional fields
- ✅ **Refined the Contact model** with necessary fillable attributes and relationships
- ✅ **Implemented the `full_name` accessor** for convenient frontend access
- ✅ **Created the `HasTeamScope` trait** for automatic multi-tenancy scoping
- ✅ **Applied multi-tenancy patterns** ensuring data isolation between teams
- ✅ **Prepared the model** for global scope implementation in Chapter 12

### Key Concepts You've Learned

**Multi-Tenancy with Traits** — How to use traits to enforce team-based data isolation
**Eloquent Accessors** — Creating computed properties that enhance the model
**Global Scopes** — Automatic query filtering for security (ready for Chapter 12)
**Mass Assignment Protection** — Using `$fillable` to prevent data exposure
**Model Hooks** — Using `bootHasTeamScope()` and `creating()` events for automatic behavior

### How This Connects to Chapter 12

In **Chapter 12: Contacts Module – CRUD Operations (Index & Show)**, you'll build:

- **Create controllers** that use your Contact model
- **Build index page** to list all contacts (filtered by team via global scope)
- **Build show page** to display a single contact
- **Implement form validation** for contact creation

The stable data layer you've created here is the foundation. The `HasTeamScope` trait will automatically enforce security—every query will include the team filter, preventing data breaches. In Chapter 12, you'll see this in action as you build the CRUD interface.

---

## Exercises 🧠

### Exercise 1: Test the Accessor in Tinker (~10 min)

**Goal**: Verify the `full_name` accessor works correctly.

**Instructions:**

1. Launch Laravel Tinker: `sail artisan tinker`.
2. Create a contact (or find an existing one).
3. Access the `full_name` property.

**Tinker Example:**

```php
// Create a contact with first and last names
$contact = App\Models\Contact::create([
    'team_id' => 1,
    'user_id' => 1,
    'company_id' => 1,
    'first_name' => 'Alfred',
    'last_name' => 'Pennyworth',
    'email' => 'alfred' . now()->timestamp . '@example.com'
]);

// Access the full_name accessor
echo $contact->full_name;
```

**Validation**: The output should be: **`Alfred Pennyworth`**

The accessor automatically concatenated first and last names without storing the value in the database.

---

### Exercise 2: Check Trait Behavior - Auto-Setting team_id (~10 min)

**Goal**: Verify the `HasTeamScope` trait automatically sets `team_id` on creation.

**Instructions:**

1. Ensure you have an authenticated user (with a team_id).
2. In Tinker, create a new contact **without** explicitly setting `team_id`.
3. Verify the trait's `creating` hook automatically assigned the user's team.

**Tinker Example:**

```php
// Step 1: Verify a user with team_id exists
$user = App\Models\User::find(1);
echo "User: " . $user->name . " | Team ID: " . $user->team_id;

// Step 2: Create a contact WITHOUT setting team_id
// In real HTTP requests, Auth::check() returns true
// In Tinker, we're just verifying the trait logic works with explicit team_id
$contact = App\Models\Contact::create([
    'team_id' => $user->team_id,  // For Tinker testing
    'user_id' => $user->id,
    'first_name' => 'Lucius',
    'last_name' => 'Fox',
    'email' => 'lucius' . now()->timestamp . '@example.com'
]);

// Step 3: Verify team_id was set
echo "Contact Team ID: " . $contact->team_id;
echo "Full Name: " . $contact->full_name;
```

**Validation**: 

- ✓ Contact created successfully
- ✓ team_id is set to user's team_id (1)
- ✓ full_name accessor works: "Lucius Fox"

**Note**: In Tinker, `Auth::check()` returns false, so the trait's automatic team_id won't work. In Chapter 12's HTTP requests, `Auth::check()` returns true and the trait automatically sets team_id. This exercise verifies the model structure is correct.

---

### Exercise 3: Verify Global Scope Filtering (~10 min)

**Goal**: Confirm the global scope prevents querying contacts from other teams.

**Instructions:**

1. Create two users in different teams.
2. Create contacts for each team.
3. Query contacts and verify only the current user's team contacts are returned.

**Tinker Example:**

```php
// Create two teams
$team1 = App\Models\Team::create(['name' => 'Sales']);
$team2 = App\Models\Team::create(['name' => 'Support']);

// Create users for each team
$user1 = App\Models\User::create([
    'team_id' => $team1->id,
    'name' => 'John',
    'email' => 'john' . now()->timestamp . '@example.com',
    'password' => bcrypt('password')
]);

$user2 = App\Models\User::create([
    'team_id' => $team2->id,
    'name' => 'Jane',
    'email' => 'jane' . now()->timestamp . '@example.com',
    'password' => bcrypt('password')
]);

// Create contacts for each team (in a real app, the global scope would auto-set team_id)
$contact1 = App\Models\Contact::create([
    'team_id' => $team1->id,
    'user_id' => $user1->id,
    'first_name' => 'Alice',
    'last_name' => 'Johnson',
    'email' => 'alice' . now()->timestamp . '@example.com'
]);

$contact2 = App\Models\Contact::create([
    'team_id' => $team2->id,
    'user_id' => $user2->id,
    'first_name' => 'Bob',
    'last_name' => 'Smith',
    'email' => 'bob' . now()->timestamp . '@example.com'
]);

// The global scope is active; it filters by Auth::user()->team_id
// In Chapter 12, this will prevent team1 users from seeing team2 data
```

**Validation**: The trait is now active and will automatically filter queries by team in Chapter 12 when authentication is properly integrated.

---

## Implementation Checklist ✅

Before moving to Chapter 12, verify you have:

### Database & Schema
- [ ] Contacts table has all fields (team_id, company_id, user_id, first_name, last_name, email, phone, title)
- [ ] All foreign keys properly configured
- [ ] Migrations ran successfully: `sail artisan migrate:status`

### Contact Model (`app/Models/Contact.php`)
- [ ] `$fillable` array includes: team_id, company_id, user_id, first_name, last_name, email, phone, title
- [ ] `$casts` array includes: created_at, updated_at, deleted_at (for datetime handling)
- [ ] `fullName()` accessor implemented
- [ ] Relationships defined: team(), company(), owner(), deals()
- [ ] Query scopes implemented: forCompany(), ownedBy(), search() (optional but recommended)
- [ ] `HasTeamScope` trait imported and used

### HasTeamScope Trait (`app/Models/Traits/HasTeamScope.php`)
- [ ] Trait file created in correct location
- [ ] Global scope implemented with Auth check
- [ ] `creating()` hook sets team_id automatically
- [ ] Namespace correct: `App\Models\Traits`

### Contact Factory (Optional but Recommended)
- [ ] Factory file created: `database/factories/ContactFactory.php`
- [ ] Factory has `definition()` method with all fields
- [ ] Factory has `forCompany()` modifier method
- [ ] Factory has `ownedBy()` modifier method
- [ ] Factory tested in Tinker: `Contact::factory()->create()`

### Testing in Tinker
- [ ] Exercise 1: `$contact->full_name` returns correct full name
- [ ] Exercise 2: Contact model accepts required fields
- [ ] Exercise 3: Global scope structure is in place

### Ready for Chapter 12
- [ ] Model structure is solid and security-focused
- [ ] All relationships working
- [ ] Trait is applied and imported correctly
- [ ] Ready to build controllers and CRUD operations

---


## Further Reading

- [Eloquent Models](https://laravel.com/docs/12.x/eloquent) — Complete Eloquent reference
- [Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships) — All relationship types and patterns
- [Accessors & Mutators](https://laravel.com/docs/12.x/eloquent-mutators) — Creating computed properties and transforming data
- [Query Scopes](https://laravel.com/docs/12.x/eloquent#query-scopes) — Reusable query filters
- [Soft Deletes](https://laravel.com/docs/12.x/eloquent#soft-deleting) — Data preservation patterns
- [Factories](https://laravel.com/docs/12.x/eloquent-factories) — Test data generation
- [Eager Loading](https://laravel.com/docs/12.x/eloquent-relationships#eager-loading) — Query optimization
