# Chapter 06: Eloquent Models & Relationships

This directory contains all model classes and enums for Chapter 06 of the Build a CRM with Laravel 12 series.

## Files Overview

### Models (place in `app/Models/`)

- **Team.php** — Team model with one-to-many relationships to companies, contacts, deals, and tasks
- **Company.php** — Company model with belongsTo Team and hasMany Contacts/Deals
- **Contact.php** — Contact model with belongsTo Team/Company and full_name accessor
- **Deal.php** — Deal model with relationships and enum casting for status
- **Task.php** — Task model with query scopes for filtering

### Enums (place in `app/Enums/`)

- **DealStatus.php** — Backed enum with 6 sales pipeline stages: prospect, qualified, proposal, negotiation, won, lost
- **TaskStatus.php** — Backed enum with 4 task statuses: open, in_progress, completed, cancelled

## Usage

### 1. Copy Model Files

Copy all `.php` files from this directory to your Laravel project:

```bash
# Copy models to app/Models/
cp Team.php app/Models/
cp Company.php app/Models/
cp Contact.php app/Models/
cp Deal.php app/Models/
cp Task.php app/Models/

# Copy enums to app/Enums/
mkdir -p app/Enums
cp DealStatus.php app/Enums/
cp TaskStatus.php app/Enums/
```

### 2. Ensure Database is Migrated

Before using these models, run migrations from Chapter 05:

```bash
sail artisan migrate
```

### 3. Test in Tinker

Start an interactive Tinker session:

```bash
sail artisan tinker
```

#### Create a Team

```php
$team = Team::create(['name' => 'Sales', 'slug' => 'sales']);
```

#### Create a Company

```php
$company = $team->companies()->create([
    'name' => 'Acme Corp',
    'email' => 'info@acme.com',
    'phone' => '555-0100',
    'website' => 'acme.com',
    'industry' => 'Technology',
    'size' => 'medium',
]);
```

#### Create Contacts

```php
$contact = $company->contacts()->create([
    'team_id' => $team->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@acme.com',
    'position' => 'CEO',
]);

// Access the full_name accessor
echo $contact->full_name;  // Output: "John Doe"
```

#### Create a Deal

```php
$deal = $company->deals()->create([
    'team_id' => $team->id,
    'contact_id' => $contact->id,
    'title' => 'Enterprise License',
    'amount' => 50000.00,
    'currency' => 'USD',
    'status' => 'qualified',
    'expected_close_date' => now()->addDays(30),
]);

// Status is automatically cast to DealStatus enum
echo $deal->status->label();  // Output: "Qualified"
```

#### Create Tasks

```php
$task = $deal->tasks()->create([
    'team_id' => $team->id,
    'title' => 'Send proposal',
    'status' => 'open',
    'due_date' => now()->addDays(2),
]);

// Use query scopes
$openTasks = Task::open()->get();
$completedTasks = Task::completed()->get();
```

## Key Features

### Relationships

- **Team** has many Companies, Contacts, Deals, Tasks
- **Company** belongs to Team, has many Contacts and Deals
- **Contact** belongs to Team and Company
- **Deal** belongs to Team and Company, has many Tasks
- **Task** belongs to Team, Deal, and Contact (optional)

### Accessors

- **Contact::fullName** — Computed property combining first_name and last_name

### Casts

- **Deal::amount** — Decimal with 2 places for monetary values
- **Deal::status** — Backed enum for type safety
- **Task::status** — Backed enum for filtering
- **Date fields** — Automatically cast to Carbon instances

### Query Scopes

- **Task::open()** — Filters tasks with status open or in_progress
- **Task::completed()** — Filters tasks with completed status

## Enums

### DealStatus Cases

- `prospect` — Initial lead stage
- `qualified` — Qualified opportunity
- `proposal` — Proposal sent
- `negotiation` — Active negotiation
- `won` — Deal won
- `lost` — Deal lost

### TaskStatus Cases

- `open` — Not yet started
- `in_progress` — Currently being worked on
- `completed` — Finished
- `cancelled` — No longer needed

## Best Practices

### Eager Loading

Always use eager loading to prevent N+1 queries:

```php
// Good: Load relationships in advance
$companies = Company::with('contacts', 'deals')->get();

// Avoid: This causes N+1 queries
foreach (Company::all() as $company) {
    echo $company->contacts->count();  // 1 query per company!
}
```

### Fillable Fields

Models use the `$fillable` property to protect mass assignment. Only listed fields can be set via `create()` or `update()`:

```php
// Safe: Only fillable fields are set
Company::create([
    'team_id' => 1,
    'name' => 'Example Corp',
    // Other fillable fields
]);
```

### Type Casting

Leverage casts for automatic type conversion:

```php
$deal = Deal::first();

// Automatically cast to decimal
$deal->amount;  // Returns: Decimal "50000.00"

// Automatically cast to enum
$deal->status;  // Returns: DealStatus enum instance
$deal->status === DealStatus::Won;  // Type-safe comparison

// Automatically cast to Carbon
$deal->expected_close_date->format('Y-m-d');  // Works with Carbon methods
```

## Testing Models

See the chapter tutorial for comprehensive testing examples using Tinker, including:

- Creating related records
- Testing accessors
- Using enum comparisons
- Filtering with query scopes
- Eager loading patterns

## Troubleshooting

### "Class not found: DealStatus"

Ensure:
1. Enum file is in `app/Enums/DealStatus.php`
2. Import in Deal model: `use App\Enums\DealStatus;`
3. Run `composer dump-autoload`

### Relationship returns null

Check:
1. Foreign key exists in database (run `sail artisan migrate`)
2. Foreign key has a valid value
3. Relationship name matches method name exactly

### Accessor not working

Ensure:
1. Method name format is `protected function propertyName():`
2. Returns `Attribute::make(get: fn() => ...)`
3. Access as property: `$model->propertyName` (not `$model->propertyName()`)

## Related Chapter

- **Chapter 05** — Database migrations that create the underlying tables
- **Chapter 07** — Controllers that use these models for CRUD operations

## Further Reading

- [Eloquent Models Documentation](https://laravel.com/docs/12.x/eloquent)
- [Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships)
- [Model Accessors & Mutators](https://laravel.com/docs/12.x/eloquent-mutators)
- [PHP Backed Enums](https://www.php.net/manual/en/language.enumerations.backed.php)
