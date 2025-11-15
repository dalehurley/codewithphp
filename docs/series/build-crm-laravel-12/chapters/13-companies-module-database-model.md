---
title: "13: Companies Module - Database & Model"
description: "Define the Company model with relationships to contacts and deals as the organizational unit for your CRM pipeline"
series: "build-crm-laravel-12"
chapter: 13
order: 13
difficulty: "Intermediate"
prerequisites:
  - "/series/build-crm-laravel-12/chapters/12-contacts-module-crud-operations"
---

![Companies Module - Database & Model](/images/build-crm-laravel-12/chapter-13-companies-module-database-model-hero-full.webp)

# Chapter 13: Companies Module - Database & Model

## Overview

With contacts fully operational, it's time to build the Companies module. Companies are the organizational entities that your sales team works with—they represent the customers (prospects, current clients, past clients) in your CRM. Every contact works for a company, and every deal is associated with a company. Companies are the hub connecting people and opportunities.

In this chapter, you'll verify the companies table structure from Chapter 05, refine the Company Eloquent model with proper configuration, implement the `HasTeamScope` trait for multi-tenancy security, and create an efficient database factory for testing. You'll also define key relationships: companies have many contacts and deals, creating a natural hierarchy in your CRM.

By the end, you'll have a production-ready Company model with automatic team-based data isolation, relationships that enable "show all contacts for this company" queries, and a testing factory ready for Chapter 14's CRUD operations. Like the Contact model in Chapter 11, this chapter prepares the data layer for the interface layer in Chapter 14.

This chapter focuses on model refinement and data layer security—no UI yet. The strong foundation here enables features like "company details with all associated contacts" or "company deal pipeline" in Chapter 14 and beyond.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 12](/series/build-crm-laravel-12/chapters/12-contacts-module-crud-operations) with Contacts CRUD complete
- ✅ Completed [Chapter 06](/series/build-crm-laravel-12/chapters/06-eloquent-models-relationships) with base models generated
- ✅ Laravel Sail running with all containers active
- ✅ Database migrations from Chapter 05 applied (`sail artisan migrate`)
- ✅ Basic understanding of Laravel migrations and Eloquent models
- ✅ Knowledge of database relationships (belongs-to, has-many)
- ✅ Understanding of multi-tenancy and the `HasTeamScope` trait from Chapter 11

**Estimated Time**: ~45 minutes (streamlined for direct CRUD preparation)

**Verify your setup:**

```bash
# Navigate to your project
cd crm-app

# Verify Sail is running
sail ps  # Should show: laravel.test, mysql, redis all "Up"

# Verify migrations are applied
sail artisan migrate:status  # Should show all migrations as "Ran"

# Verify Company model exists
cat app/Models/Company.php  # Should exist from Chapter 06

# Verify HasTeamScope trait exists
cat app/Models/Traits/HasTeamScope.php  # Created in Chapter 11
```

## Quick Start

Want to see the end result in 5 minutes? Here's what you'll accomplish:

```bash
# After completing this chapter:

# 1. Company model is fully configured
sail artisan tinker
$company = App\Models\Company::first();
echo $company->name;  # Returns company name

# 2. Global scope automatically filters by team
$teamCompanies = App\Models\Company::all();  # Only current user's team

# 3. Relationships work seamlessly
echo $company->contacts()->count();  # All contacts for this company
echo $company->deals()->count();     # All deals for this company

# 4. Team is auto-set on creation
$newCompany = App\Models\Company::create([...]);
echo $newCompany->team_id;  # Automatically set by trait
```

Your Company model is now secure, optimized, and ready for CRUD operations!

## What You'll Build

By the end of this chapter, you will have:

**Core Foundation**:

- ✅ **Verified companies table schema** with all professional fields (address, industry, etc.)
- ✅ **Refined Company model** with proper `$fillable` configuration and relationships
- ✅ **Computed properties** (full_address accessor) for frontend convenience
- ✅ **Multi-tenancy scoping** via the `HasTeamScope` trait (same pattern as Contacts)
- ✅ **Automatic team_id assignment** on company creation

**Security & Patterns**:

- ✅ **`HasTeamScope` trait applied** for automatic team-based data filtering
- ✅ **Global scope mechanism** that prevents cross-team data access
- ✅ **Mass assignment protection** with proper `$fillable` configuration
- ✅ **Relationships to Contacts and Deals** enabling hierarchical queries

**Readiness for Chapter 14**:

- ✅ **Complete Company model** ready for controller integration
- ✅ **Security-first design** that prevents data leakage between teams
- ✅ **Foundation for CRUD operations** (Index, Show, Create, Update, Delete)
- ✅ **Query scopes** for convenient filtering in controllers
- ✅ **Company Factory** for generating test data

## Companies in the CRM: Organizational Hub 🏢

**Why Companies Matter**:

In your CRM, Companies are the **organizational entities** that serve as the hub connecting people and opportunities:

- **Contacts** work FOR companies (many contacts per company)
- **Deals** are WITH companies (many deals per company)
- **Tasks** can be tied to companies through contacts/deals
- **Revenue** is tracked at the company level
- **Relationships** are managed at the company level

This makes Companies fundamentally different from Contacts. While Contacts are individual people, Companies are organizations with multiple relationships, financial data, and complex hierarchies.

**Key Characteristics**:

- ✅ Organizational entities (not people)
- ✅ Can have many contacts
- ✅ Can have many deals
- ✅ Central to pipeline analysis
- ✅ Require address information
- ✅ Track industry and size
- ✅ Source of revenue

## Objectives

**Foundational**:

- Verify the companies table schema has all required business fields
- Define all relationships correctly (Team, Contacts, Deals)
- Understand multi-tenancy scoping patterns from Chapter 11
- Configure mass assignment protection
- Understand Companies' role as organizational hub

**Advanced**:

- Apply the `HasTeamScope` trait to the Company model
- Create computed properties (accessors) for derived fields
- Create query scopes for filtering companies by various criteria
- Prepare the model for global scope queries in Chapter 14
- Implement a factory for test data generation

**Validation & Practice**:

- Test the model relationships in Tinker
- Test computed properties/accessors
- Verify the trait automatically sets team_id on creation
- Confirm all relationships work correctly
- Prepare the model for CRUD operations in Chapter 14

## Step 1: Review & Refine Company Migration (~10 min)

### Goal

Verify the companies table migration from Chapter 05 has all essential fields for a complete CRM company record.

### Actions

1. **Inspect the companies migration**:

```bash
# Check the migration status
sail artisan migrate:status

# Find and review the companies migration file
find database/migrations -name "*create_companies_table*" -exec cat {} \;
```

2. **Verify required columns are present**:

The companies table should contain these essential columns:

```
✓ id (Primary Key)
✓ team_id (Foreign Key - CRITICAL for Multi-Tenancy)
✓ name (Company name, required string)
✓ email (Company email, nullable string)
✓ phone (Company phone, nullable string)
✓ website (Company website/domain, nullable string)
✓ address_street (Street address, nullable string)
✓ address_city (City, nullable string)
✓ address_state (State/Province, nullable string)
✓ address_zip (Postal code, nullable string)
✓ industry (Industry type, nullable string)
✓ notes (General information, nullable text)
✓ created_at, updated_at (Timestamps)
✓ deleted_at (Soft deletes)
```

3. **Check database schema**:

```bash
# View the actual database schema
sail artisan schema:show --table=companies
```

4. **Add missing fields (if needed)**:

If any column is missing, create a new migration:

```bash
sail artisan make:migration add_[field]_to_companies_table
```

Then add the column in the migration's `up()` method:

```php
public function up(): void
{
    Schema::table('companies', function (Blueprint $table) {
        $table->string('website')->nullable()->after('name');
    });
}

public function down(): void
{
    Schema::table('companies', function (Blueprint $table) {
        $table->dropColumn('website');
    });
}
```

Run: `sail artisan migrate`

### Expected Result

```
✓ All essential company fields confirmed in database
✓ team_id present for multi-tenancy scoping
✓ Foreign keys properly configured
✓ Ready for model refinement
```

### Why It Works

**Schema verification** ensures the database foundation is solid before building model logic. The `team_id` field is critical—it enables automatic data isolation between different CRM teams, just like in the Contact model from Chapter 11.

### Troubleshooting

- **Migration file not found** — Create one: `sail artisan make:migration create_companies_table`
- **Column mismatch with model** — Create a new migration to add missing columns
- **Foreign key error** — Ensure teams table exists; migrations run in order

## Step 2: Refine Company Model & Relationships (~15 min)

### Goal

Configure the Company model with proper mass assignment protection, relationships to contacts and deals, and soft delete support.

### Actions

1. **Configure mass assignment protection**:

Open `app/Models/Company.php` and ensure the `$fillable` array includes all modifiable fields:

```php
# filename: app/Models/Company.php

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
    'industry',
    'notes',
];
```

2. **Add Computed Properties (Accessors)** (Optional but Recommended):

Like the Contact model's `full_name` accessor from Chapter 11, consider adding useful computed properties to your Company model. For example, a `full_address` that combines street, city, state, and zip:

```php
# filename: app/Models/Company.php

use Illuminate\Database\Eloquent\Casts\Attribute;

// In the Company class, add this accessor

/**
 * Get the company's full address (computed property)
 * This is computed on-the-fly: $company->full_address returns formatted address
 */
protected function fullAddress(): Attribute
{
    return Attribute::make(
        get: function () {
            $parts = [
                $this->address_street,
                $this->address_city,
                $this->address_state,
                $this->address_zip,
            ];

            // Filter out empty parts and join with commas
            return implode(', ', array_filter($parts));
        }
    );
}
```

**Why Add This?**

- **Frontend convenience**: React components can use `$company->full_address` instead of managing 4 separate fields
- **Business logic centralization**: Address formatting logic lives in one place
- **Consistency**: Same pattern as Contact's `full_name` accessor
- **No database overhead**: Computed on-the-fly without storing redundantly

**Using the Accessor in Tinker** (from Step 2 Exercise):

```php
$company->full_address;  # Returns: "123 Main St, San Francisco, CA 94105"
```

3. **Define all relationships**:

Ensure your Company model includes these relationships:

```php
# filename: app/Models/Company.php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ============ MULTI-TENANCY ============

/**
 * Multi-tenancy: Company belongs to a Team
 */
public function team(): BelongsTo
{
    return $this->belongsTo(Team::class);
}

// ============ RELATIONSHIPS ============

/**
 * Get all Contacts for this Company
 */
public function contacts(): HasMany
{
    return $this->hasMany(Contact::class);
}

/**
 * Get all Deals for this Company
 */
public function deals(): HasMany
{
    return $this->hasMany(Deal::class);
}
```

3. **Add type casting for production readiness**:

```php
# filename: app/Models/Company.php

protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
];
```

4. **Optional: Add Useful Query Scopes** (for Chapter 14 CRUD):

Query scopes provide convenient filtering methods. Add these to your Company model:

```php
# filename: app/Models/Company.php

/**
 * Scope: Filter companies by industry
 */
public function scopeByIndustry($query, $industry)
{
    return $query->where('industry', $industry);
}

/**
 * Scope: Filter companies with minimum employee count
 */
public function scopeWithMinEmployees($query, $count)
{
    return $query->where('employee_count', '>=', $count);
}

/**
 * Scope: Search companies by name or website
 */
public function scopeSearch($query, $term)
{
    return $query->where(function ($q) use ($term) {
        $q->where('name', 'like', "%{$term}%")
          ->orWhere('website', 'like', "%{$term}%")
          ->orWhere('industry', 'like', "%{$term}%");
    });
}
```

These scopes enable readable queries in Chapter 14 controllers (e.g., `Company::byIndustry('Technology')->search('acme')->get()`)

5. **Performance Optimization - Eager Loading** (Optional but Recommended):

For future controller use, understand the difference between lazy and eager loading. When you'll show company details with related contacts and deals, use eager loading to prevent N+1 queries:

```php
# filename: app/Models/Company.php (Optional - reference for Chapter 14)

// ❌ BAD - N+1 problem: 1 query to get companies + N queries to get contacts/deals
$companies = Company::all();
foreach ($companies as $company) {
    echo $company->contacts()->count();  // Extra query per company!
    echo $company->deals()->count();     // Extra query per company!
}

// ✅ GOOD - Eager loading: 2 queries total (companies + related data)
$companies = Company::with('contacts', 'deals')->get();
foreach ($companies as $company) {
    echo $company->contacts()->count();  // No extra queries!
    echo $company->deals()->count();     // No extra queries!
}
```

**Why This Matters for Chapter 14**:

- **Without eager loading**: 1 query for 10 companies → 20 extra queries (10 contacts + 10 deals)
- **With eager loading**: 1 query for 10 companies → 2 queries total
- **Performance improvement**: 21 queries → 3 queries (7x faster!)

You won't implement eager loading here (that's Chapter 14's controller logic), but understanding this pattern helps you design models that support it.

### Expected Result

```
✓ Company model has $fillable configured for mass assignment
✓ All relationships defined (team, contacts, deals)
✓ Type casting configured for date fields
✓ Model ready for CRUD operations and team scoping
✓ Optional query scopes for convenient filtering
```

### Why It Works

**`$fillable` provides security** — Only explicitly listed fields can be mass-assigned, preventing accidental data exposure.

**Relationships enable querying** — You can use Eloquent's intuitive syntax: `$company->contacts` returns all associated contacts without complex SQL joins.

**Type casting** ensures dates are Carbon instances, making date operations easier and more reliable.

### Troubleshooting

- **"Column not found" error** — Verify field exists: `sail artisan schema:show --table=companies`
- **Relationship returns null** — Verify foreign key has a value: `dd($company->team_id)`
- **Dates not formatted properly** — Ensure type casts are configured in `$casts` array

## Step 3: Apply Multi-Tenancy Scoping with HasTeamScope Trait (~10 min)

### Goal

Apply the `HasTeamScope` trait (created in Chapter 11) to the Company model for automatic multi-tenancy data isolation.

### Actions

1. **Import and use the trait in the Company model**:

```php
# filename: app/Models/Company.php

use App\Models\Traits\HasTeamScope;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasTeamScope; // Add the trait

    // ... rest of model ...
}
```

2. **Verify the trait is properly imported**:

```bash
# Test in Tinker that the trait is applied
sail artisan tinker

# Check the trait is loaded
$company = App\Models\Company::first();
echo class_uses_recursive($company::class) | Has the trait?
# Should output references to HasTeamScope

exit
```

3. **Understand what the trait provides**:

The `HasTeamScope` trait (from Chapter 11) automatically:

- Filters all queries by the current user's `team_id` via a global scope
- Sets `team_id` automatically when creating a new company
- Ensures users can never accidentally query cross-team data

### Expected Result

```
✓ HasTeamScope trait imported and used in Company model
✓ Trait automatically filters queries by team
✓ Trait auto-sets team_id on company creation
✓ Model ready for Chapter 14 CRUD with built-in security
```

### Why It Works

**Global scopes enforce security** — Every query against the Company model automatically includes `WHERE team_id = {current_user_team_id}`, preventing cross-team data access.

**The trait is reusable** — Once working on Contact (Chapter 11) and now on Company, you can apply it to Deal, Task, and other models for consistent multi-tenancy.

### Troubleshooting

- **"Auth not found" error** — Verify Laravel's auth system is set up (done in Chapter 07)
- **Team_id still not being set** — Check if `Auth::check()` is true; verify you're creating via model (not raw SQL)
- **Trait file not found** — Verify it exists: `ls app/Models/Traits/HasTeamScope.php`

## Step 4 (Optional): Create Company Factory for Testing (~10 min)

### Goal

Create a factory for generating realistic test company data in Chapter 14 and beyond.

### Actions

1. **Generate the Company factory**:

```bash
sail artisan make:factory CompanyFactory
```

2. **Implement the factory**:

```php
# filename: database/factories/CompanyFactory.php
<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        // Find a random existing Team, or default to ID 1 for testing
        $teamId = Team::inRandomOrder()->first()->id ?? 1;

        return [
            'team_id' => $teamId,  // 🚨 Required for multi-tenancy
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->domainName(),
            'address_street' => $this->faker->streetAddress(),
            'address_city' => $this->faker->city(),
            'address_state' => $this->faker->stateAbbr(),
            'address_zip' => $this->faker->postcode(),
            'industry' => $this->faker->randomElement([
                'Technology',
                'Finance',
                'Healthcare',
                'Retail',
                'Manufacturing',
                'Services',
                'Education',
                'Other',
            ]),
            'notes' => $this->faker->optional()->text(),
        ];
    }

    /**
     * Create a company in a specific team
     */
    public function forTeam(Team $team): static
    {
        return $this->state(['team_id' => $team->id]);
    }

    /**
     * Create a tech startup
     */
    public function techStartup(): static
    {
        return $this->state([
            'industry' => 'Technology',
            'employee_count' => $this->faker->numberBetween(1, 50),
        ]);
    }

    /**
     * Create a large enterprise
     */
    public function enterprise(): static
    {
        return $this->state([
            'employee_count' => $this->faker->numberBetween(1000, 10000),
        ]);
    }
}
```

3. **Test the factory in Tinker**:

```bash
sail artisan tinker

# Create a single company
$company = App\Models\Company::factory()->create();
echo $company->name;

# Create multiple companies for a team
$team = App\Models\Team::first();
$companies = App\Models\Company::factory(5)->forTeam($team)->create();
echo $companies->count();  # Should output: 5

# Create a tech startup
$startup = App\Models\Company::factory()->techStartup()->create();
echo $startup->industry;  # Should output: "Technology"

exit
```

### Expected Result

```
✓ CompanyFactory created with realistic test data generation
✓ Factory supports multiple variations (tech startup, enterprise)
✓ Factory can be scoped to specific teams
✓ Factory generates valid company data for Chapter 14 testing
```

### Why It Works

**Factories** provide a convenient way to generate test data. In Chapter 14, when testing CRUD operations, you can use:

```php
$company = Company::factory()->create();
```

Instead of manually creating companies with all fields.

---

## Wrap-up 🎉

You have successfully completed the foundation of the Companies Module:

### What You've Accomplished

- ✅ **Confirmed the database schema** with all required business fields
- ✅ **Refined the Company model** with necessary fillable attributes and relationships
- ✅ **Applied the `HasTeamScope` trait** for automatic multi-tenancy scoping
- ✅ **Created query scopes** for convenient filtering
- ✅ **Implemented a factory** for test data generation
- ✅ **Prepared the model** for CRUD operations in Chapter 14

### Key Concepts You've Learned

**Companies as Organizational Hub** — Understanding Companies' unique role connecting Contacts, Deals, and Revenue
**Computed Properties/Accessors** — Creating convenient virtual properties (full_address) for frontend use
**Multi-Tenancy with Traits** — How to use traits to enforce team-based data isolation (pattern from Chapter 11)
**Eloquent Relationships** — Creating strong relationships between Companies, Contacts, and Deals
**Query Scopes** — Reusable filters for common queries
**Mass Assignment Protection** — Using `$fillable` to prevent data exposure
**Test Data Factories** — Generating realistic test data efficiently
**Performance Optimization** — Understanding N+1 problems and eager loading patterns for Chapter 14 dashboards
**Efficient Counting** — Using `withCount()` for relationship counts without extra queries

### How This Connects to Chapter 14

In **Chapter 14: Companies Module – CRUD Operations**, you'll build:

- **Create controllers** that use your Company model with efficient queries
- **Build index page** to list all companies (filtered by team) with relationship counts
- **Build show page** to display company details with contacts and deals using eager loading
- **Implement form validation** for company creation
- **Build forms** for creating and editing companies

The stable data layer you've created here is the foundation. The relationships you've defined enable:

- **Automatic team filtering** via the `HasTeamScope` trait (security)
- **Efficient data loading** with eager loading patterns (performance)
- **Dashboard statistics** with `withCount()` (user experience)

In Chapter 14, you'll apply these patterns in controllers to build dashboards that display company information with contacts and deals—and the queries will run efficiently because you've built the model correctly now.

---

## Bonus: Counting Relationships Efficiently

When you build company dashboards in Chapter 14, you'll often need counts like "this company has 5 contacts and 3 deals." There are efficient ways to do this:

```php
# filename: Reference for Chapter 14 dashboards

// ❌ Inefficient: N+1 problem when getting counts
$company = Company::first();
$contactCount = $company->contacts()->count();  // Extra query!
$dealCount = $company->deals()->count();        // Extra query!

// ✅ Better: Load relationship with counts built-in
$company = Company::withCount('contacts', 'deals')->first();
echo $company->contacts_count;  // No extra query!
echo $company->deals_count;     // No extra query!

// ✅ Best: Eager load + counts for multiple companies
$companies = Company::withCount('contacts', 'deals')->get();
// Now $company->contacts_count is available without extra queries
```

This pattern will be important when displaying company lists with statistics in Chapter 14. The model relationships you're building here enable this efficient data loading.

---

## Exercises 🧠

### Exercise 1: Test the Model in Tinker (~10 min)

**Goal**: Verify the Company model works correctly with all relationships.

**Instructions:**

1. Launch Laravel Tinker: `sail artisan tinker`
2. Create a company and verify its relationships

**Tinker Example:**

```php
// Create a team first
$team = App\Models\Team::create([
    'name' => 'Sales',
    'slug' => 'sales',
    'user_id' => 1,
]);

// Create a company in the team
$company = App\Models\Company::create([
    'team_id' => $team->id,
    'name' => 'Tech Innovations Inc',
    'website' => 'techinnovations.com',
    'industry' => 'Technology',
    'employee_count' => 150,
    'notes' => 'Leading AI solutions provider',
]);

// Access relationships
echo $company->team->name;          # Returns: "Sales"
echo $company->contacts()->count(); # Returns: 0 (no contacts yet)
echo $company->deals()->count();    # Returns: 0 (no deals yet)

// Create a contact for this company
$contact = App\Models\Contact::create([
    'team_id' => $team->id,
    'company_id' => $company->id,
    'user_id' => 1,
    'first_name' => 'John',
    'last_name' => 'Smith',
    'email' => 'john@techinnovations.com',
]);

// Now verify the relationship
echo $company->contacts()->count(); # Returns: 1
echo $company->contacts()->first()->full_name; # Returns: "John Smith"
```

**Validation**: The model correctly stores and retrieves company data with all relationships working.

---

### Exercise 1B: Test the Full Address Accessor (~10 min)

**Goal**: Verify the `full_address` accessor works correctly (like Chapter 11's `full_name`).

**Instructions:**

1. Launch Laravel Tinker: `sail artisan tinker`
2. Create a company with address fields
3. Test the `full_address` accessor

**Tinker Example:**

```php
// Create a company with address
$company = App\Models\Company::create([
    'team_id' => 1,
    'name' => 'Acme Corporation',
    'website' => 'acme.com',
    'industry' => 'Manufacturing',
    'address_street' => '123 Main Street',
    'address_city' => 'San Francisco',
    'address_state' => 'CA',
    'address_zip' => '94105',
    'notes' => 'Major manufacturing client',
]);

// Test the accessor
echo $company->full_address;
// Returns: "123 Main Street, San Francisco, CA, 94105"

// Test with partial address
$company2 = App\Models\Company::create([
    'team_id' => 1,
    'name' => 'Tech Startup',
    'website' => 'startup.io',
    'industry' => 'Technology',
    'address_city' => 'San Francisco',
    'address_state' => 'CA',
    // No street or zip - accessor handles partial data
]);

// Returns only populated fields
echo $company2->full_address;
// Returns: "San Francisco, CA"
```

**Validation**:

- ✓ Accessor combines address fields
- ✓ Handles partial/missing address fields gracefully
- ✓ Returns formatted string ready for display
- ✓ Works the same way as Contact's `full_name` from Chapter 11

**Learning**: This shows how computed properties add convenience for frontend development without storing redundant data.

---

### Exercise 2: Check Trait Behavior - Auto-Setting team_id (~10 min)

**Goal**: Verify the `HasTeamScope` trait automatically sets `team_id` on creation.

**Instructions:**

1. In Tinker, create a company with explicit team_id
2. Verify the trait's `creating` hook works properly

**Tinker Example:**

```php
// Step 1: Verify a team exists
$team = App\Models\Team::first();
echo "Team: " . $team->name . " | ID: " . $team->id;

// Step 2: Create a company WITH explicit team_id (required for now)
// In real HTTP requests with Auth::check() true, team_id auto-sets
$company = App\Models\Company::create([
    'team_id' => $team->id,  // Explicit for Tinker testing
    'name' => 'Global Solutions Ltd',
    'website' => 'globalsolutions.com',
    'industry' => 'Consulting',
]);

// Step 3: Verify team_id was properly set
echo "Company Team ID: " . $company->team_id;
echo "Company Name: " . $company->name;
```

**Validation**:

- ✓ Company created successfully
- ✓ team_id properly set
- ✓ Model relationships work correctly

**Note**: In Tinker, `Auth::check()` returns false, so the trait's automatic team_id assignment won't work. In Chapter 14's HTTP requests, `Auth::check()` returns true and the trait automatically sets team_id. This exercise verifies the model structure is correct.

---

### Exercise 3: Verify Global Scope Filtering (~10 min)

**Goal**: Confirm the global scope prevents querying companies from other teams.

**Instructions:**

1. Create two different teams
2. Create companies for each team
3. Verify queries are properly scoped

**Tinker Example:**

```php
// Create two teams
$team1 = App\Models\Team::create([
    'name' => 'Sales',
    'slug' => 'sales-' . now()->timestamp,
    'user_id' => 1,
]);

$team2 = App\Models\Team::create([
    'name' => 'Support',
    'slug' => 'support-' . now()->timestamp,
    'user_id' => 1,
]);

// Create companies for each team
$company1 = App\Models\Company::create([
    'team_id' => $team1->id,
    'name' => 'Acme Corp',
    'website' => 'acme.com',
    'industry' => 'Manufacturing',
]);

$company2 = App\Models\Company::create([
    'team_id' => $team2->id,
    'name' => 'TechStart Inc',
    'website' => 'techstart.io',
    'industry' => 'Technology',
]);

// In a real application, querying would be scoped by Auth user's team
// The global scope is active and will filter queries by team in Chapter 14
// when authentication is properly integrated
```

**Validation**: The trait is now active and will automatically filter queries by team in Chapter 14 when authentication is properly integrated.

---

### Exercise 4 (Challenge): Efficient Relationship Counting (~15 min)

**Goal**: Practice efficient query patterns you'll use in Chapter 14 dashboards.

**Instructions:**

1. Create multiple companies with many contacts and deals
2. Practice both lazy and eager loading
3. Observe the query difference

**Tinker Example:**

```php
// Create test data
$team = App\Models\Team::first();

// Create 3 companies
$companies = App\Models\Company::factory(3)->forTeam($team)->create();

// Create contacts and deals for each
foreach ($companies as $company) {
    App\Models\Contact::factory(5)->create([
        'team_id' => $team->id,
        'company_id' => $company->id,
    ]);

    App\Models\Deal::factory(3)->create([
        'team_id' => $team->id,
        'company_id' => $company->id,
    ]);
}

// ❌ Inefficient approach (7 queries!)
echo "Inefficient approach:\n";
$companies = App\Models\Company::all();
foreach ($companies as $company) {
    echo "{$company->name}: " .
         $company->contacts()->count() . " contacts, " .
         $company->deals()->count() . " deals\n";
    // 1 query to get companies + 3 queries for each (contacts + deals) = 7 queries
}

// ✅ Efficient approach (1 query!)
echo "\nEfficient approach:\n";
$companies = App\Models\Company::withCount('contacts', 'deals')->get();
foreach ($companies as $company) {
    echo "{$company->name}: " .
         $company->contacts_count . " contacts, " .
         $company->deals_count . " deals\n";
    // 1 query total!
}
```

**Validation**:

- The efficient approach runs in 1 query
- The inefficient approach runs in 7 queries (1 + 3×2)
- Demonstrates the performance impact of proper model design
- Shows patterns you'll use in Chapter 14

**Learning Outcome**: Understanding these patterns prepares you for building efficient controllers and dashboards in Chapter 14.

---

## Implementation Checklist ✅

Before moving to Chapter 14, verify you have:

### Database & Schema

- [ ] Companies table has all fields (team_id, name, email, phone, website, address_street, address_city, address_state, address_zip, industry, notes)
- [ ] All foreign keys properly configured
- [ ] Migrations ran successfully: `sail artisan migrate:status`
- [ ] Soft deletes enabled on companies table

### Company Model (`app/Models/Company.php`)

- [ ] `$fillable` array includes: team_id, name, email, phone, website, address_street, address_city, address_state, address_zip, industry, notes
- [ ] `$casts` array includes: created_at, updated_at, deleted_at (for datetime handling)
- [ ] Relationships defined: team(), contacts(), deals()
- [ ] Query scopes implemented: byIndustry(), withMinEmployees(), search() (optional but recommended)
- [ ] `HasTeamScope` trait imported and used
- [ ] Model uses SoftDeletes trait

### HasTeamScope Trait (`app/Models/Traits/HasTeamScope.php`)

- [ ] Trait file created in correct location (from Chapter 11)
- [ ] Global scope implemented with Auth check
- [ ] `creating()` hook sets team_id automatically
- [ ] Namespace correct: `App\Models\Traits`

### Company Factory (Optional but Recommended)

- [ ] Factory file created: `database/factories/CompanyFactory.php`
- [ ] Factory has `definition()` method with all fields
- [ ] Factory has `forTeam()` modifier method
- [ ] Factory has variation methods: `techStartup()`, `enterprise()`
- [ ] Factory tested in Tinker: `Company::factory()->create()`

### Testing in Tinker

- [ ] Model creates successfully with all fields
- [ ] Relationships return correct data
- [ ] Soft deletes work correctly
- [ ] Team relationships properly scoped
- [ ] Factory generates valid test data

### Performance Readiness

- [ ] Understand N+1 query problem and eager loading solution
- [ ] Know how to use `withCount()` for efficient counting
- [ ] Understand difference between lazy and eager loading
- [ ] Ready to apply eager loading patterns in Chapter 14 controllers
- [ ] Can identify when to use query scopes vs raw queries

### Ready for Chapter 14

- [ ] Model structure is solid and security-focused
- [ ] All relationships working
- [ ] Trait is applied and imported correctly
- [ ] Factory ready for CRUD testing
- [ ] Performance patterns understood for efficient controllers
- [ ] Ready to build controllers and CRUD operations with optimized queries

---

<ChapterCheckbox 
  seriesId="build-crm-laravel-12"
  chapterId="13"
  label="Completed: 13: Companies Module - Database & Model"
/>

## Further Reading

- [Eloquent Models](https://laravel.com/docs/12.x/eloquent) — Complete Eloquent reference
- [Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships) — All relationship types and patterns
- [Query Scopes](https://laravel.com/docs/12.x/eloquent#query-scopes) — Reusable query filters
- [Soft Deletes](https://laravel.com/docs/12.x/eloquent#soft-deleting) — Data preservation patterns
- [Factories](https://laravel.com/docs/12.x/eloquent-factories) — Test data generation
- [Eager Loading](https://laravel.com/docs/12.x/eloquent-relationships#eager-loading) — Query optimization

<ChapterCheckbox 
  seriesId="build-crm-laravel-12"
  chapterId="13"
  label="Completed: 13: Companies Module - Database & Model"
/>

## Further Reading

- [Laravel Documentation](https://laravel.com/docs/12.x/) - Official Laravel reference
