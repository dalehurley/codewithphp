# Chapter 14: Companies Module - CRUD Operations

This directory contains code samples for implementing full CRUD operations for the Companies module in the Laravel CRM.

## Files

### Backend

- **CompanyController.php** — Handles CRUD operations (Index with search/filtering, Create, Store, Show, Edit, Update, Destroy)
- **CompanyRequest.php** — FormRequest validation with team-specific uniqueness rules
- **CompanyPolicy.php** — Authorization policy ensuring users can only access their team's companies
- **CompanyModel.php** — Complete Company Eloquent model with relationships and methods
- **routes-web.php** — Route definition snippet showing how to register resourceful routes

### Frontend (React/Inertia)

- **CompaniesIndexView.tsx** — List all companies with pagination and quick actions
- **CompaniesShowView.tsx** — Display company details with related contacts and deals
- **CompaniesCreateEditView.tsx** — Form for creating and editing companies

## Key Concepts

### FormRequest Validation

The controller uses `CompanyRequest` FormRequest for validation with team-specific uniqueness:

```php
// In CompanyController@store and @update
public function store(CompanyRequest $request)
{
    Company::create($request->validated());
}
```

The FormRequest enforces that company names are unique only within the current team.

### Search and Filtering

The controller implements search and filtering with query optimization:

```php
// In CompanyController@index
$companies = Company::query()
    ->when($request->input('search'), function ($query, $search) {
        $query->where('name', 'like', "%{$search}%");
    })
    ->when($request->input('industry'), function ($query, $industry) {
        $query->where('industry', $industry);
    })
    ->withCount(['contacts', 'deals']) // Optimization for counts
    ->latest()
    ->paginate(15)
    ->withQueryString(); // Preserve filters in pagination links
```

The frontend uses debounced search (300ms delay) to reduce API calls while typing.

### Eager Loading

The controller uses `load()` with query callbacks to eager load relationships and prevent N+1 queries:

```php
// In CompanyController@show
$company->load([
    'contacts' => fn ($query) => $query->latest(),
    'deals' => fn ($query) => $query->latest(),
]);
```

The `withCount()` method efficiently loads relationship counts without N+1 queries:

```php
// Efficiently loads contacts_count and deals_count
$companies = Company::withCount(['contacts', 'deals'])->get();
```

### Team-Based Isolation

The Company model uses the `HasTeamScope` trait to automatically filter by team:

```php
// Global scope automatically applied
$companies = Company::all(); // Only current team's companies
```

### Authorization

The `CompanyPolicy` ensures users can only view/edit/delete companies in their team:

```php
public function view(User $user, Company $company): bool
{
    return $user->current_team_id === $company->team_id;
}
```

### Relational Data Display

The Show view displays related contacts and deals:

```tsx
<Card>
  <CardHeader>
    <CardTitle className="text-lg">Contacts</CardTitle>
  </CardHeader>
  <CardContent>
    {contacts.data.map((contact) => (
      // Display each contact
    ))}
  </CardContent>
</Card>
```

## Usage

1. Copy `CompanyController.php` to `app/Http/Controllers/`
2. Copy `CompanyPolicy.php` to `app/Policies/`
3. Register the policy in `app/Providers/AuthServiceProvider.php`
4. Add route registration from `routes-web.php` to `routes/web.php`
5. Create views from the React components in `resources/js/Pages/Companies/`

## Relational Form Handling

The chapter also demonstrates updating Contact forms to allow selecting a company:

- Update `ContactController@create` and `@edit` to pass companies list
- Add `company_id` validation to `ContactRequest`
- Add company dropdown to Contact form component

## Next Steps

In Chapter 15, you'll build on this foundation to create the Deals module with a visual sales pipeline interface.

