---
title: "14: Companies Module - CRUD Operations"
description: "Implement full company management with CRUD, eager loading, and relational data handling"
series: "build-crm-laravel-12"
chapter: 14
order: 14
difficulty: "Intermediate"
prerequisites:
  - "/series/build-crm-laravel-12/chapters/13-companies-module-database-model"
---

![Companies Module - CRUD Operations](/images/build-crm-laravel-12/chapter-14-companies-module-crud-operations-hero-full.webp)

# Chapter 14: Companies Module - CRUD Operations

## Overview

Your contacts are fully operational, but they need a home. It's time to build the Companies interface—the place where your sales team manages customer organizations, views their associated contacts and deals, and tracks organizational information like size, industry, and location.

In this chapter, you'll create a complete Company management system with Create, Read, Update, and Delete (CRUD) operations. You'll build a Laravel controller with authorization policies ensuring team data isolation, create React/Inertia views for listing companies and displaying company details, and demonstrate relational data handling—showing all contacts and deals associated with each company using eager loading to prevent N+1 queries.

By the end of this chapter, your sales team will be able to:

- **View all companies** in a paginated list with contact and deal counts
- **Create new companies** with complete business information
- **View company details** with all related contacts and deals on a single page
- **Edit company information** and update records
- **Delete companies** from the system
- **Navigate seamlessly** between companies and their relationships

This chapter mirrors the Contacts CRUD pattern from Chapter 12 but adds complexity: companies have many relationships, require authorization policies, and benefit from eager loading. You'll learn practical patterns for building real-world Laravel applications.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 13](/series/build-crm-laravel-12/chapters/13-companies-module-database-model) with Company model and relationships defined
- ✅ Completed [Chapter 12](/series/build-crm-laravel-12/chapters/12-contacts-module-crud-operations) to understand CRUD patterns
- ✅ Completed [Chapter 09](/series/build-crm-laravel-12/chapters/09-authorization-access-control) to understand authorization policies
- ✅ Laravel Sail running with all containers active
- ✅ Database migrations applied and companies table populated with sample data
- ✅ Basic understanding of Laravel controllers, policies, and Inertia.js
- ✅ Familiarity with React component patterns from Chapter 12

**Estimated Time**: ~125 minutes (includes creating controllers, FormRequest validation, policies, routes, views, delete confirmation, soft deletes, and relational form updates)

**Verify your setup:**

```bash
# Navigate to your project
cd crm-app

# Verify Sail is running
sail ps  # Should show: laravel.test, mysql, redis all "Up"

# Verify Company model exists and has relationships
sail artisan tinker
$company = App\Models\Company::first();
$company->contacts()->count();  # Should work
$company->deals()->count();     # Should work
exit
```

## What You'll Build

By the end of this chapter, you will have:

**Backend Controllers & Security**:

- ✅ **CompanyController** with resourceful CRUD methods (index, create, store, show, edit, update, destroy)
- ✅ **CompanyRequest FormRequest** with team-specific uniqueness validation
- ✅ **CompanyPolicy** authorizing all actions at the team level
- ✅ **Eager loading** preventing N+1 queries when fetching companies with relationships
- ✅ **Team-scoped queries** ensuring users only see their team's data
- ✅ **Search and filtering** on the companies index page
- ✅ **Soft deletes** for data recovery and auditing

**Frontend Views (React/Inertia)**:

- ✅ **Companies Index** listing all companies with pagination and quick stats
- ✅ **Companies Trashed** view for managing soft-deleted companies
- ✅ **Company Show** displaying company details with related contacts and deals
- ✅ **Company Create/Edit** form for adding and modifying companies
- ✅ **Navigation components** linking between companies and their relationships

**Integration**:

- ✅ **Resourceful routes** for companies using `Route::resource()`
- ✅ **Authorization checks** on every page ensuring team isolation
- ✅ **Relational data handling** displaying contacts and deals for each company
- ✅ **Contact form updates** allowing users to select a company when creating/editing contacts
- ✅ **Complete working company management system** ready for Chapter 15

## Quick Start

Want to see it working in 5 minutes? Here's the end result:

```bash
# After completing this chapter:

# Access the companies index
curl http://localhost/companies

# View a specific company with all its contacts
curl http://localhost/companies/1

# Create a new company via the form
# Visit http://localhost/companies/create in your browser
```

## Objectives

By the end of this chapter, you will:

- Create a resourceful controller with authorization checks for team data isolation
- Build FormRequest validation with team-specific uniqueness rules
- Build authorization policies ensuring users only access their team's companies
- Implement eager loading to optimize database queries and prevent N+1 problems
- Create React views for company listing with search and filtering
- Implement soft deletes for data recovery and auditing
- Build a trashed companies view with restore and permanent delete functionality
- Demonstrate relational data handling by showing contacts and deals per company
- Update Contact forms to allow selecting a company (relational form handling)
- Build forms that handle multiple related entity types (companies with contact and deal counts)
- Understand how to structure complex views with relationships in Laravel/Inertia
- Implement pagination for both the company list and related entities

## Step 1: Resourceful Routes & Controller Generation (~5 min)

### Goal

Set up the basic routing and controller structure for the Companies module.

### Actions

1. **Generate the controller with resource methods**:

```bash
# From your project root
sail artisan make:controller CompanyController --resource --model=Company
```

2. **Define Routes**: Add the resource route to `routes/web.php`, protected by authentication middleware:

```php
# filename: routes/web.php
# Add this inside the authenticated middleware group

use App\Http\Controllers\CompanyController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // ... existing routes ...

    // Companies Resource Routes
    Route::resource('companies', CompanyController::class);
});
```

## Step 2: Server-Side Validation with FormRequest (~15 min)

### Goal

Implement robust validation for company data, ensuring the company name is unique only within the current team.

### Actions

1. **Generate Form Request**:

```bash
sail artisan make:request CompanyRequest
```

2. **Implement Validation Logic**: Open `app/Http/Requests/CompanyRequest.php` and replace with:

```php
# filename: app/Http/Requests/CompanyRequest.php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization check using CompanyPolicy
        $company = $this->route('company');
        $policyAction = $company ? 'update' : 'create';

        return $this->user()->can($policyAction, $company ?? Company::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = $this->route('company')?->id ?? null;
        $teamId = $this->user()->current_team_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Enforce uniqueness only within the current team
                Rule::unique('companies')
                    ->where(fn ($query) => $query->where('team_id', $teamId))
                    ->ignore($companyId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'industry' => ['nullable', 'string', 'max:100'],
            'employee_count' => ['nullable', 'integer', 'min:1'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
```

### Expected Result

```
✅ CompanyRequest created: app/Http/Requests/CompanyRequest.php
✅ Authorization method checks policy permissions
✅ Validation rules enforce team-specific uniqueness for company names
✅ All company fields validated with appropriate rules
```

### Why It Works

**FormRequest Pattern**: Laravel's FormRequest class centralizes validation logic, making it reusable and testable. The `authorize()` method runs before validation, ensuring users have permission.

**Team-Specific Uniqueness**: The `Rule::unique()` with a closure ensures company names are unique only within a team:

```php
Rule::unique('companies')
    ->where(fn ($query) => $query->where('team_id', $teamId))
    ->ignore($companyId)
```

This allows "Acme Corp" in Team 1 and "Acme Corp" in Team 2, but prevents duplicate names within the same team.

**Policy Integration**: The `authorize()` method calls the CompanyPolicy, ensuring create/update permissions are checked before validation runs.

### Troubleshooting

- **Error: "Call to undefined method route()"** — Use `$this->route('company')` instead of `route('company')` in FormRequest.
- **Uniqueness not working** — Verify `team_id` is correctly set on the user and the company model uses `HasTeamScope`.

## Step 3: Implement Index & Basic CRUD Logic (~20 min)

### Goal

Implement the core controller methods, relying on the `HasTeamScope` trait for data isolation and the `CompanyPolicy` for authorization.

### Actions

1. **Replace the generated controller with the complete implementation**:

```php
# filename: app/Http/Controllers/CompanyController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of companies for the current team.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Company::class);

        // Global Scope handles team_id filtering automatically
        $companies = Company::query()
            // 1. Search Logic: Filter by 'name' if 'search' query parameter is present
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            // 2. Filter Logic: Filter by 'industry' if 'industry' query parameter is present
            ->when($request->input('industry'), function ($query, $industry) {
                $query->where('industry', $industry);
            })
            ->withCount(['contacts', 'deals']) // Optimization for counts
            ->latest() // Order by latest created
            ->paginate(15)
            ->withQueryString(); // Preserve search/filter parameters on pagination links

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search', 'industry']), // Pass current filters back to view
        ]);
    }

    /**
     * Show the form for creating a new company.
     *
     * @return Response
     */
    public function create(): Response
    {
        $this->authorize('create', Company::class);
        return Inertia::render('Companies/Create');
    }

    /**
     * Store a newly created company in storage.
     *
     * @param CompanyRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CompanyRequest $request)
    {
        // team_id auto-assigned by HasTeamScope trait
        Company::create($request->validated());

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully!');
    }

    /**
     * Display the specified company with related contacts and deals.
     *
     * @param Company $company
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function show(Company $company): Response
    {
        // Authorization: Checks if the user can view this SPECIFIC company (must be in the same team)
        $this->authorize('view', $company);

        // Eager Load: Retrieve related data efficiently with query callbacks
        $company->load([
            'contacts' => fn ($query) => $query->latest()->limit(10),
            'deals' => fn ($query) => $query->latest()->limit(10),
        ]);

        // Note: Since Contact and Deal models also use HasTeamScope, the relational data
        // is double-checked for security, but the primary isolation is via the Company policy.

        return Inertia::render('Companies/Show', [
            'company' => $company,
            'contacts' => $company->contacts,
            'deals' => $company->deals,
        ]);
    }

    /**
     * Show the form for editing the specified company.
     *
     * @param Company $company
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function edit(Company $company): Response
    {
        $this->authorize('update', $company);

        return Inertia::render('Companies/Edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified company in storage.
     *
     * @param CompanyRequest $request
     * @param Company $company
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(CompanyRequest $request, Company $company)
    {
        $this->authorize('update', $company);

        $company->update($request->validated());

        return redirect()->route('companies.show', $company)
            ->with('success', 'Company updated successfully!');
    }

    /**
     * Remove the specified company from storage.
     *
     * @param Company $company
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(Company $company)
    {
        // Authorization includes role check (only owner can delete, Chapter 09)
        $this->authorize('delete', $company);

        $companyName = $company->name;
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', "Company '{$companyName}' deleted successfully!");
    }
}
```

### Expected Result

```
✅ Controller created: app/Http/Controllers/CompanyController.php
✅ All CRUD methods defined with proper return types
✅ FormRequest validation used instead of inline validation
✅ Authorization checks on all methods (viewAny, create, view, update, delete)
✅ Search and filtering implemented in index method
✅ Eager loading configured with query callbacks to prevent N+1 queries
✅ Query string preserved in pagination links
```

### Why It Works

**FormRequest Pattern**: Using `CompanyRequest` instead of inline validation centralizes validation logic, makes it reusable, and automatically handles authorization checks.

**Search and Filtering**: The `when()` method conditionally applies filters only when query parameters are present:

```php
->when($request->input('search'), function ($query, $search) {
    $query->where('name', 'like', "%{$search}%");
})
```

This keeps queries efficient—no unnecessary WHERE clauses when filters aren't used.

**Eager Loading with Callbacks**: The `load()` method with closures allows ordering related data:

```php
$company->load([
    'contacts' => fn ($query) => $query->latest(),
    'deals' => fn ($query) => $query->latest(),
]);
```

This loads contacts and deals in separate queries (2 total) instead of N+1 queries, and orders them by creation date.

**withQueryString()**: Preserves search/filter parameters when paginating, so users don't lose their filters when clicking "Next Page".

**Team Scoping**: The `HasTeamScope` trait automatically filters all queries by `team_id`, so `Company::query()` only returns the current team's companies.

### Troubleshooting

- **Error: "Class not found: CompanyRequest"** — Make sure you generated the FormRequest in Step 2 and it's in `app/Http/Requests/`.
- **Search not working** — Verify the search input name matches the query parameter (`search`).
- **Filters lost on pagination** — Ensure you're using `withQueryString()` on the paginator.

## Step 4: Create the CompanyPolicy (~10 min)

### Goal

Create an authorization policy ensuring users can only view, edit, and delete companies in their team.

### Actions

1. **Generate the policy**:

```bash
sail artisan make:policy CompanyPolicy --model=Company
```

2. **Update the policy with authorization rules**:

```php
# filename: app/Policies/CompanyPolicy.php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any companies.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view companies in their team
        return true;
    }

    /**
     * Determine whether the user can create companies.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create companies in their team
        return true;
    }

    /**
     * Determine whether the user can view the company.
     */
    public function view(User $user, Company $company): bool
    {
        return $user->current_team_id === $company->team_id;
    }

    /**
     * Determine whether the user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        return $user->current_team_id === $company->team_id;
    }

    /**
     * Determine whether the user can delete the company.
     */
    public function delete(User $user, Company $company): bool
    {
        return $user->current_team_id === $company->team_id;
    }
}
```

3. **Register the policy in AuthServiceProvider**:

```php
# In app/Providers/AuthServiceProvider.php, add to the $policies array:

protected $policies = [
    // ... existing policies
    Company::class => CompanyPolicy::class,
];
```

### Expected Result

```
✅ CompanyPolicy created: app/Policies/CompanyPolicy.php
✅ Five authorization methods defined: viewAny, create, view, update, delete
✅ Policy registered in AuthServiceProvider.php
✅ All authorization checks now use the policy
```

### Why It Works

**Policy Pattern**: Laravel's policy system centralizes authorization logic. Each policy method receives the user and model, returning true/false.

**Team Isolation**: The policy checks `$user->current_team_id === $company->team_id`, ensuring users only interact with their team's data:

```php
// If user is in team 1 and company is in team 2:
public function view(User $user, Company $company): bool
{
    return 1 === 2;  // Returns false, throws AuthorizationException
}
```

**In Controller**: The controller's `$this->authorize()` method automatically uses this policy:

```php
$this->authorize('view', $company);  // Calls CompanyPolicy@view
```

### Troubleshooting

- **Error: "Illuminate\Auth\Access\AuthorizationException"** — User fails the policy check. Verify the company's `team_id` matches the user's `current_team_id`.
- **Policy not being called** — Make sure the policy is registered in `AuthServiceProvider.php` and you're using `$this->authorize()` in the controller.

## Step 5: Create the Index View (~15 min)

### Goal

Build a React component displaying all companies with pagination, search, filtering, quick stats, and action links.

### Actions

1. **Create the directory structure**:

```bash
mkdir -p resources/js/Pages/Companies
```

2. **Create the Index component with search and filtering**:

```tsx
# filename: resources/js/Pages/Companies/Index.tsx
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { PageHeader } from '@/components/PageHeader';
import { Pagination } from '@/components/Pagination';
import AppLayout from '@/Layouts/AppLayout';
import { useState, useEffect, useRef } from 'react';

interface Company {
  id: number;
  name: string;
  email: string;
  phone: string;
  industry: string;
  city: string;
  contacts_count: number;
  deals_count: number;
}

interface Props {
  companies: {
    data: Company[];
    links: any;
    meta: any;
  };
  filters: {
    search?: string;
    industry?: string;
  };
}

export default function CompaniesIndex({ companies, filters }: Props) {
  const { flash } = usePage().props as { flash?: { success?: string } };
  const [search, setSearch] = useState(filters.search || '');
  const [industry, setIndustry] = useState(filters.industry || '');
  const initialMount = useRef(true);

  // Debounced search logic - waits 300ms after user stops typing
  useEffect(() => {
    // Skip initial render to prevent immediate load
    if (initialMount.current) {
      initialMount.current = false;
      return;
    }

    const delayDebounceFn = setTimeout(() => {
      router.get(
        '/companies',
        { search: search, industry: industry },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true, // Replace history entry instead of adding new one
        }
      );
    }, 300); // Wait 300ms after typing stops

    return () => clearTimeout(delayDebounceFn);
  }, [search, industry]);

  const handleClearFilters = () => {
    setSearch('');
    setIndustry('');
    router.get('/companies', {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <AppLayout title="Companies">
      {/* Success Message */}
      {flash?.success && (
        <div className="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {flash.success}
        </div>
      )}

      <PageHeader
        title="Companies"
        description="Manage all companies in your CRM"
      >
        <Link href="/companies/create">
          <Button>Add Company</Button>
        </Link>
      </PageHeader>

      {/* Search and Filter Section */}
      <Card className="mb-4">
        <CardContent className="pt-6">
          <div className="flex items-center gap-4 flex-wrap">
            {/* Search Input */}
            <div className="flex-1 min-w-[200px]">
              <Input
                type="text"
                placeholder="Search companies by name..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>

            {/* Industry Filter */}
            <div>
              <select
                value={industry}
                onChange={(e) => setIndustry(e.target.value)}
                className="px-3 py-2 border rounded-md shadow-sm"
              >
                <option value="">All Industries</option>
                <option value="Technology">Technology</option>
                <option value="Finance">Finance</option>
                <option value="Retail">Retail</option>
                <option value="Healthcare">Healthcare</option>
                {/* Add more industry options based on your data */}
              </select>
            </div>

            {/* Clear Filters Button */}
            {(search || industry) && (
              <Button variant="outline" onClick={handleClearFilters}>
                Clear Filters
              </Button>
            )}
          </div>
        </CardContent>
      </Card>

      <div className="space-y-4">
        <div className="rounded-lg border overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow className="bg-gray-50">
                <TableHead className="font-semibold">Company Name</TableHead>
                <TableHead className="font-semibold">Email</TableHead>
                <TableHead className="font-semibold">Phone</TableHead>
                <TableHead className="font-semibold">Industry</TableHead>
                <TableHead className="font-semibold text-center">Contacts</TableHead>
                <TableHead className="font-semibold text-center">Deals</TableHead>
                <TableHead className="font-semibold">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {companies.data.map((company) => (
                <TableRow key={company.id} className="hover:bg-gray-50">
                  <TableCell>
                    <Link href={`/companies/${company.id}`}>
                      <span className="text-blue-600 hover:underline cursor-pointer">
                        {company.name}
                      </span>
                    </Link>
                  </TableCell>
                  <TableCell>{company.email || '—'}</TableCell>
                  <TableCell>{company.phone || '—'}</TableCell>
                  <TableCell>{company.industry || '—'}</TableCell>
                  <TableCell className="text-center">
                    {company.contacts_count}
                  </TableCell>
                  <TableCell className="text-center">
                    {company.deals_count}
                  </TableCell>
                  <TableCell className="space-x-2">
                    <Link href={`/companies/${company.id}`}>
                      <Button variant="ghost" size="sm">
                        View
                      </Button>
                    </Link>
                    <Link href={`/companies/${company.id}/edit`}>
                      <Button variant="outline" size="sm">
                        Edit
                      </Button>
                    </Link>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>

        {companies.data.length === 0 && (
          <div className="text-center py-12">
            <p className="text-gray-500 mb-4">No companies yet.</p>
            <Link href="/companies/create">
              <Button>Create First Company</Button>
            </Link>
          </div>
        )}

        <Pagination links={companies.links} meta={companies.meta} />
      </div>
    </AppLayout>
  );
}
```

### Expected Result

```
✅ Companies listed in a clean table format
✅ Search input with debounced search (waits 300ms after typing stops)
✅ Industry filter dropdown with immediate filtering
✅ Clear filters button appears when filters are active
✅ Pagination working with Laravel's pagination links
✅ Contact and Deal counts displayed (from withCount optimization)
✅ Action buttons for editing companies
✅ Empty state shown when no companies exist
✅ Search/filter parameters preserved in URL and pagination links
```

### Why It Works

**Debounced Search**: The `useEffect` hook with a timeout prevents excessive API calls. It waits 300ms after the user stops typing before making the request, improving performance and reducing server load.

**State Management**: Using `useState` for search and industry allows React to manage form state locally, while `useRef` tracks the initial mount to prevent an immediate search on page load.

**Inertia Router**: The `router.get()` method makes GET requests with query parameters, using `preserveState: true` to maintain component state and `preserveScroll: true` to keep scroll position.

**withCount Optimization**: The controller uses `withCount(['contacts', 'deals'])` to efficiently load relationship counts in a single query instead of N+1 queries:

```php
// ❌ WITHOUT withCount (N+1 queries)
$companies = Company::all();
foreach ($companies as $company) {
    echo $company->contacts()->count(); // 1 query per company
}

// ✅ WITH withCount (1 query total)
$companies = Company::withCount(['contacts', 'deals'])->get();
foreach ($companies as $company) {
    echo $company->contacts_count; // No additional queries!
}
```

**Query String Preservation**: The `withQueryString()` method on the paginator ensures search and filter parameters are included in pagination links, so users don't lose their filters when navigating pages.

**Type Safety**: The `interface Props` and `interface Company` provide TypeScript types for the data passed from the controller, enabling autocomplete and type checking.

**Responsive Design**: The flex layout with `flex-wrap` ensures the search bar adapts to mobile screens, and the `min-w-[200px]` ensures the search input remains usable on small screens.

## Step 6: Create the Show View (~20 min)

### Goal

Display a single company's details with all related contacts and deals on one page.

### Actions

1. **Create the Show component**:

```tsx
# filename: resources/js/Pages/Companies/Show.tsx
import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/PageHeader';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/Layouts/AppLayout';

interface Contact {
  id: number;
  name: string;
  email: string;
  phone: string;
  title: string;
}

interface Deal {
  id: number;
  title: string;
  amount: number;
  stage: string;
  expected_close_date: string;
}

interface Company {
  id: number;
  name: string;
  email: string;
  phone: string;
  website: string;
  industry: string;
  employee_count: number;
  full_address: string;
  notes: string;
}

interface Props {
  company: Company;
  contacts: Contact[];
  deals: Deal[];
  flash?: {
    success?: string;
  };
}

export default function CompaniesShow({ company, contacts, deals, flash }: Props) {
  const { flash: pageFlash } = usePage().props as { flash?: { success?: string } };

  return (
    <AppLayout title={company.name}>
      {/* Success Message */}
      {pageFlash?.success && (
        <div className="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {pageFlash.success}
        </div>
      )}

      <PageHeader title={company.name} description="Company Details">
        <div className="space-x-2">
          <Link href={`/companies/${company.id}/edit`}>
            <Button>Edit</Button>
          </Link>
          <Link href="/companies">
            <Button variant="outline">Back to Companies</Button>
          </Link>
        </div>
      </PageHeader>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {/* Company Information Card */}
        <div className="md:col-span-1">
          <Card>
            <CardHeader>
              <CardTitle className="text-lg">Company Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {company.email && (
                <div>
                  <p className="text-sm text-gray-600">Email</p>
                  <a
                    href={`mailto:${company.email}`}
                    className="text-blue-600 hover:underline"
                  >
                    {company.email}
                  </a>
                </div>
              )}
              {company.phone && (
                <div>
                  <p className="text-sm text-gray-600">Phone</p>
                  <p className="font-medium">{company.phone}</p>
                </div>
              )}
              {company.website && (
                <div>
                  <p className="text-sm text-gray-600">Website</p>
                  <a
                    href={company.website}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-blue-600 hover:underline"
                  >
                    {company.website}
                  </a>
                </div>
              )}
              {company.industry && (
                <div>
                  <p className="text-sm text-gray-600">Industry</p>
                  <p className="font-medium">{company.industry}</p>
                </div>
              )}
              {company.employee_count && (
                <div>
                  <p className="text-sm text-gray-600">Employee Count</p>
                  <p className="font-medium">{company.employee_count}</p>
                </div>
              )}
              {company.full_address && (
                <div>
                  <p className="text-sm text-gray-600">Address</p>
                  <p className="font-medium text-sm">{company.full_address}</p>
                </div>
              )}
              {company.notes && (
                <div>
                  <p className="text-sm text-gray-600">Notes</p>
                  <p className="text-sm">{company.notes}</p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Contacts and Deals Summary */}
        <div className="md:col-span-2 space-y-6">
          {/* Contacts Section */}
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle className="text-lg">
                  Contacts {contacts.length > 0 && `(${contacts.length})`}
                </CardTitle>
                <Link href={`/contacts/create?company_id=${company.id}`}>
                  <Button size="sm">Add Contact</Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              {contacts.length > 0 ? (
                <div className="space-y-3">
                  {contacts.map((contact) => (
                    <Link
                      key={contact.id}
                      href={`/contacts/${contact.id}`}
                      className="block p-3 border rounded hover:bg-gray-50"
                    >
                      <p className="font-medium text-blue-600">{contact.name}</p>
                      <p className="text-sm text-gray-600">{contact.title}</p>
                      <p className="text-sm text-gray-500">{contact.email}</p>
                    </Link>
                  ))}
                </div>
              ) : (
                <p className="text-gray-500">No contacts yet</p>
              )}
            </CardContent>
          </Card>

          {/* Deals Section */}
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle className="text-lg">
                  Deals {deals.length > 0 && `(${deals.length})`}
                </CardTitle>
                <Link href={`/deals/create?company_id=${company.id}`}>
                  <Button size="sm">Add Deal</Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              {deals.length > 0 ? (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Title</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Stage</TableHead>
                        <TableHead>Close Date</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {deals.map((deal) => (
                        <TableRow key={deal.id}>
                          <TableCell>
                            <Link href={`/deals/${deal.id}`}>
                              <span className="text-blue-600 hover:underline">
                                {deal.title}
                              </span>
                            </Link>
                          </TableCell>
                          <TableCell>
                            ${deal.amount?.toLocaleString()}
                          </TableCell>
                          <TableCell>
                            <span className="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                              {deal.stage}
                            </span>
                          </TableCell>
                          <TableCell>
                            {new Date(deal.expected_close_date).toLocaleDateString()}
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              ) : (
                <p className="text-gray-500">No deals yet</p>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
```

### Expected Result

```
✅ Company information displayed in a sidebar
✅ Success messages displayed when redirected from create/update
✅ Related contacts listed with links to contact details (showing count in header)
✅ Related deals shown in a table format with amounts and stages (showing count in header)
✅ Buttons to add new contacts or deals for this company (with company_id pre-filled)
✅ Responsive layout on mobile and desktop
✅ Empty states shown when no contacts or deals exist
```

### Why It Works

**Relational Display**: The component receives `contacts` and `deals` as arrays from the eager-loaded relationships, displaying them alongside company information. This demonstrates relational data handling—showing multiple entities on one page.

**Eager Loading**: The controller uses `load()` with query callbacks to fetch contacts and deals efficiently. The `limit(10)` ensures we only show the most recent 10 items, keeping the page fast.

**Flash Messages**: The `usePage().props.flash` hook accesses Laravel's flash messages passed via `->with('success', ...)`. These messages automatically disappear after being displayed.

**Pre-filled Forms**: The "Add Contact" and "Add Deal" links include `?company_id=${company.id}` query parameter, which can be used to pre-fill the company selection in the create forms.

**Conditional Rendering**: The component checks if `company.email` exists before rendering the email section, preventing empty UI for optional fields.

**Links to Related Entities**: Each contact links to `/contacts/{id}` and each deal links to `/deals/{id}`, enabling navigation through the CRM.

**Type Safety**: The interfaces define the structure of contacts and deals, providing type safety and autocomplete in the editor.

### Troubleshooting

- **Contacts/Deals not showing** — Verify the Company model has `contacts()` and `deals()` relationship methods defined correctly.
- **Flash messages not appearing** — Check that Inertia middleware is configured to share flash messages. Verify `HandleInertiaRequests` middleware includes flash data.
- **Counts showing incorrectly** — Ensure the relationships are properly loaded. Check that `contacts` and `deals` are arrays, not paginated objects.

## Step 7: Create the Create/Edit Form (~20 min)

### Goal

Build a reusable form component for creating new companies and editing existing ones.

### Actions

1. **Create the Create/Edit component**:

```tsx
# filename: resources/js/Pages/Companies/CreateEdit.tsx
import { useForm } from '@inertiajs/react';
import { FormField } from '@/components/FormField';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';

interface Company {
  id?: number;
  name: string;
  email: string;
  phone: string;
  website: string;
  industry: string;
  employee_count: number;
  address: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  notes: string;
}

interface Props {
  company?: Company;
}

export default function CompaniesForm({ company }: Props) {
  const isEditing = !!company?.id;
  const { data, setData, post, put, processing, errors } = useForm(
    company || {
      name: '',
      email: '',
      phone: '',
      website: '',
      industry: '',
      employee_count: '',
      address: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      notes: '',
    }
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (isEditing) {
      put(`/companies/${company.id}`);
    } else {
      post('/companies');
    }
  };

  return (
    <AppLayout title={isEditing ? `Edit ${company.name}` : 'Create Company'}>
      <PageHeader
        title={isEditing ? `Edit ${company.name}` : 'Create Company'}
        description={
          isEditing
            ? 'Update company information'
            : 'Add a new company to your CRM'
        }
      />

      <Card className="max-w-2xl">
        <CardHeader>
          <CardTitle>
            {isEditing ? 'Edit Company' : 'New Company'}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Basic Information */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Basic Information</h3>

              <FormField
                label="Company Name"
                error={errors.name}
                required
              >
                <input
                  type="text"
                  value={data.name}
                  onChange={(e) => setData('name', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="Acme Corporation"
                />
              </FormField>

              <FormField
                label="Email"
                error={errors.email}
              >
                <input
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="contact@company.com"
                />
              </FormField>

              <FormField
                label="Phone"
                error={errors.phone}
              >
                <input
                  type="tel"
                  value={data.phone}
                  onChange={(e) => setData('phone', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="+1 (555) 123-4567"
                />
              </FormField>

              <FormField
                label="Website"
                error={errors.website}
              >
                <input
                  type="url"
                  value={data.website}
                  onChange={(e) => setData('website', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="https://company.com"
                />
              </FormField>
            </div>

            {/* Business Details */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Business Details</h3>

              <FormField
                label="Industry"
                error={errors.industry}
              >
                <input
                  type="text"
                  value={data.industry}
                  onChange={(e) => setData('industry', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="Technology, Finance, Retail, etc."
                />
              </FormField>

              <FormField
                label="Employee Count"
                error={errors.employee_count}
              >
                <input
                  type="number"
                  min="1"
                  value={data.employee_count}
                  onChange={(e) => setData('employee_count', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="1000"
                />
              </FormField>
            </div>

            {/* Address */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Address</h3>

              <FormField
                label="Street Address"
                error={errors.address}
              >
                <input
                  type="text"
                  value={data.address}
                  onChange={(e) => setData('address', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="123 Main Street"
                />
              </FormField>

              <div className="grid grid-cols-2 gap-4">
                <FormField
                  label="City"
                  error={errors.city}
                >
                  <input
                    type="text"
                    value={data.city}
                    onChange={(e) => setData('city', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="New York"
                  />
                </FormField>

                <FormField
                  label="State/Province"
                  error={errors.state}
                >
                  <input
                    type="text"
                    value={data.state}
                    onChange={(e) => setData('state', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="NY"
                  />
                </FormField>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <FormField
                  label="Postal Code"
                  error={errors.postal_code}
                >
                  <input
                    type="text"
                    value={data.postal_code}
                    onChange={(e) => setData('postal_code', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="10001"
                  />
                </FormField>

                <FormField
                  label="Country"
                  error={errors.country}
                >
                  <input
                    type="text"
                    value={data.country}
                    onChange={(e) => setData('country', e.target.value)}
                    className="w-full px-3 py-2 border rounded-md"
                    placeholder="United States"
                  />
                </FormField>
              </div>
            </div>

            {/* Additional Information */}
            <div className="space-y-4">
              <h3 className="font-semibold text-lg">Additional Information</h3>

              <FormField
                label="Notes"
                error={errors.notes}
              >
                <textarea
                  value={data.notes}
                  onChange={(e) => setData('notes', e.target.value)}
                  className="w-full px-3 py-2 border rounded-md"
                  placeholder="Internal notes about this company..."
                  rows={4}
                />
              </FormField>
            </div>

            {/* Submit Buttons */}
            <div className="flex gap-2 justify-end pt-4">
              <Button
                type="button"
                variant="outline"
                onClick={() => window.history.back()}
                disabled={processing}
              >
                Cancel
              </Button>
              <Button
                type="submit"
                disabled={processing}
              >
                {isEditing ? 'Update Company' : 'Create Company'}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </AppLayout>
  );
}
```

2. **Register two routes for Create and Edit pages** in `routes/web.php`:

The resourceful route already handles this, but you need two view files:

```
resources/js/Pages/Companies/Create.tsx
resources/js/Pages/Companies/Edit.tsx
```

Both can be the same component. Create them with:

```tsx
# filename: resources/js/Pages/Companies/Create.tsx
import CompaniesForm from './CreateEdit';

export default function Create() {
  return <CompaniesForm />;
}
```

```tsx
# filename: resources/js/Pages/Companies/Edit.tsx
import CompaniesForm from './CreateEdit';

export default function Edit({ company }: { company: any }) {
  return <CompaniesForm company={company} />;
}
```

### Expected Result

```
✅ Create form displayed with empty fields
✅ Edit form populated with existing company data
✅ Form sections organized: Basic Info, Business Details, Address, Additional
✅ Validation errors displayed inline
✅ Submit button changes text based on create vs edit
✅ Cancel button returns to previous page
```

### Why It Works

**Dual Purpose Component**: The `isEditing` flag switches between POST (create) and PUT (update). This DRY approach avoids code duplication.

**useForm Hook**: Inertia's `useForm` hook manages form state and handles submission:

```tsx
const { data, setData, post, put, processing } = useForm(initialData);
```

It tracks `data` (form values), `setData` (update values), `post` (submit for create), `put` (submit for update), and `processing` (disabled button during submission).

**Error Handling**: The `FormField` component displays `error` messages from validation, providing user feedback.

**Success Messages**: After form submission, Laravel redirects with `->with('success', ...)`, which Inertia automatically shares via `usePage().props.flash`. Display these messages at the top of your forms or index pages.

### Troubleshooting

- **Form not submitting** — Verify the form's `onSubmit` handler calls `e.preventDefault()` and uses the correct method (`post` or `put`).
- **Validation errors not showing** — Ensure the `FormField` component receives the `errors` prop from `useForm()` hook.
- **Success message not appearing** — Check that the controller redirects with `->with('success', ...)` and that your view accesses `usePage().props.flash`.

## Step 8: Relational Data Handling in Contact Forms (~35 min)

### Goal

Demonstrate the flexibility of relational data handling by updating the Contact form (from Chapter 12) to allow users to assign a company to a contact.

### Actions

1. **Update Contact Controller (`create`/`edit`)**: In `ContactController@create` and `ContactController@edit`, pass the list of available companies (which are automatically team-scoped) to the view.

```php
# filename: app/Http/Controllers/ContactController.php
# Update the create and edit methods

use App\Models\Company;
use App\Models\User;

public function create(): Response
{
    $this->authorize('create', Contact::class);

    // Global Scope filters this list automatically
    $companies = Company::select('id', 'name')->orderBy('name')->get();
    $users = User::select('id', 'name')->orderBy('name')->get();

    return Inertia::render('Contacts/Create', [
        'companies' => $companies, // 🚨 Pass companies list
        'users' => $users,
    ]);
}

public function edit(Contact $contact): Response
{
    $this->authorize('update', $contact);

    // Global Scope filters this list automatically
    $companies = Company::select('id', 'name')->orderBy('name')->get();
    $users = User::select('id', 'name')->orderBy('name')->get();

    return Inertia::render('Contacts/Edit', [
        'contact' => $contact,
        'companies' => $companies, // 🚨 Pass companies list
        'users' => $users,
    ]);
}
```

2. **Update Contact Form Request**: The `ContactRequest` must validate that the `company_id` exists and is within the user's team. The `exists:companies,id` rule combined with the `HasTeamScope` trait on the Company model handles this implicitly, but for stricter security, you can add a custom rule:

```php
# filename: app/Http/Requests/ContactRequest.php
# Add to the rules() method

public function rules(): array
{
    $teamId = $this->user()->current_team_id;

    return [
        // ... existing rules ...
        'company_id' => [
            'nullable',
            'integer',
            Rule::exists('companies', 'id')->where('team_id', $teamId),
        ],
    ];
}
```

3. **Update Contact Form Component (React)**: Modify the `resources/js/Pages/Contacts/Form.tsx` (or Create.tsx/Edit.tsx) to include a `<Select>` field that lists the companies passed as props.

```tsx
# filename: resources/js/Pages/Contacts/Form.tsx
# Add company selection field

interface Company {
  id: number;
  name: string;
}

interface Props {
  companies?: Company[];
  // ... other props
}

export default function ContactForm({ companies = [], ...otherProps }: Props) {
  // ... existing form code ...

  return (
    <form onSubmit={handleSubmit}>
      {/* ... existing fields ... */}

      <FormField
        label="Company"
        error={errors.company_id}
      >
        <select
          value={data.company_id || ''}
          onChange={(e) => setData('company_id', e.target.value ? parseInt(e.target.value) : null)}
          className="w-full px-3 py-2 border rounded-md"
        >
          <option value="">No Company</option>
          {companies.map((company) => (
            <option key={company.id} value={company.id}>
              {company.name}
            </option>
          ))}
        </select>
      </FormField>

      {/* ... rest of form ... */}
    </form>
  );
}
```

### Expected Result

```
✅ Contact create/edit forms now include a Company dropdown
✅ Companies list is automatically filtered by team (via HasTeamScope)
✅ Contact can be assigned to a company when creating/editing
✅ Validation ensures company_id belongs to the user's team
✅ Form displays "No Company" option for contacts without a company
```

### Why It Works

**Relational Form Handling**: By passing companies to the Contact form, users can select which company a contact belongs to. This demonstrates how to handle relational data in forms.

**Team Scoping**: The `Company::select('id', 'name')->get()` query automatically filters by team through the `HasTeamScope` trait, so users only see companies from their team.

**Validation Security**: The `Rule::exists()` with a `where()` clause ensures that even if someone tries to submit a `company_id` from another team, validation will fail.

**Optional Relationship**: The `nullable` rule and empty option allow contacts to exist without a company, providing flexibility.

### Troubleshooting

- **Companies not showing** — Verify the Company model uses `HasTeamScope` and `current_team_id` is set on the user.
- **Validation failing** — Check that the `exists` rule includes the `where('team_id', $teamId)` clause.
- **Form not updating** — Ensure the `company_id` field is included in the form's `useForm` initial data.

## Step 9: Add Delete Confirmation (~5 min)

### Goal

Add a confirmation dialog before deleting a company to prevent accidental deletions.

### Actions

1. **Update the Index view to include delete buttons with confirmation**:

```tsx
# In resources/js/Pages/Companies/Index.tsx
# Add this import and function

import { router } from '@inertiajs/react';

// Add this function inside the component
const handleDelete = (companyId: number, companyName: string) => {
  if (confirm(`Are you sure you want to delete "${companyName}"? This action cannot be undone.`)) {
    router.delete(`/companies/${companyId}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Optional: Show a toast notification
      },
    });
  }
};

// Update the Actions column in the table:
<TableCell className="space-x-2">
  <Link href={`/companies/${company.id}`}>
    <Button variant="ghost" size="sm">View</Button>
  </Link>
  <Link href={`/companies/${company.id}/edit`}>
    <Button variant="outline" size="sm">Edit</Button>
  </Link>
  <Button
    variant="destructive"
    size="sm"
    onClick={() => handleDelete(company.id, company.name)}
  >
    Delete
  </Button>
</TableCell>
```

2. **Alternatively, add delete button to Show view**:

```tsx
# In resources/js/Pages/Companies/Show.tsx
# Add delete handler and button

const handleDelete = () => {
  if (confirm(`Are you sure you want to delete "${company.name}"? This action cannot be undone.`)) {
    router.delete(`/companies/${company.id}`, {
      onSuccess: () => router.visit('/companies'),
    });
  }
};

// Add to PageHeader actions:
<Button variant="destructive" onClick={handleDelete}>
  Delete Company
</Button>
```

### Expected Result

```
✅ Delete buttons added to Index and Show views
✅ Confirmation dialog appears before deletion
✅ User can cancel deletion
✅ Successful deletion shows success message
✅ Company removed from list after deletion
```

### Why It Works

**Browser Confirm Dialog**: The native `confirm()` function shows a browser dialog that blocks execution until the user responds. This is simple but effective for delete confirmations.

**Router Delete Method**: Inertia's `router.delete()` method sends a DELETE request to the server, matching Laravel's resourceful route for `destroy`.

**Preserve Scroll**: The `preserveScroll: true` option keeps the user's scroll position after deletion, improving UX.

**Better Alternative**: For production apps, consider using a modal component (like shadcn/ui Dialog) instead of `confirm()` for better styling and UX.

### Troubleshooting

- **Delete not working** — Verify the route accepts DELETE method and the controller's `destroy` method is properly implemented.
- **Confirmation not showing** — Check that the `onClick` handler is correctly bound and the function is defined.

## Step 10: Implement Soft Deletes (~15 min)

### Goal

Add soft delete functionality to preserve deleted companies in the database for recovery and auditing purposes.

### Actions

1. **Add soft deletes to the Company model**:

```php
# filename: app/Models/Company.php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeamScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // 🚨 Add this import

class Company extends Model
{
    use HasFactory, HasTeamScope, SoftDeletes; // 🚨 Add SoftDeletes trait

    protected $fillable = [
        'name',
        'email',
        'phone',
        'website',
        'industry',
        'employee_count',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
    ];

    // ... existing relationships ...
}
```

2. **Create a migration to add the deleted_at column**:

```bash
sail artisan make:migration add_soft_deletes_to_companies_table
```

3. **Update the migration file**:

```php
# filename: database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_companies_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->softDeletes(); // Adds deleted_at timestamp column
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
```

4. **Run the migration**:

```bash
sail artisan migrate
```

5. **Add methods to view and restore trashed companies in the controller**:

```php
# filename: app/Http/Controllers/CompanyController.php
# Add these methods to the CompanyController class

/**
 * Display a listing of trashed (soft-deleted) companies.
 */
public function trashed(Request $request): Response
{
    $this->authorize('viewAny', Company::class);

    $companies = Company::onlyTrashed() // Only get soft-deleted companies
        ->when($request->input('search'), function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
        ->withCount(['contacts', 'deals'])
        ->latest('deleted_at')
        ->paginate(15)
        ->withQueryString();

    return Inertia::render('Companies/Trashed', [
        'companies' => $companies,
        'filters' => $request->only(['search']),
    ]);
}

/**
 * Restore a soft-deleted company.
 */
public function restore(string $id)
{
    $company = Company::onlyTrashed()->findOrFail($id);

    $this->authorize('update', $company);

    $company->restore();

    return redirect()->route('companies.index')
        ->with('success', "Company '{$company->name}' has been restored!");
}

/**
 * Permanently delete a soft-deleted company.
 */
public function forceDelete(string $id)
{
    $company = Company::onlyTrashed()->findOrFail($id);

    $this->authorize('delete', $company);

    $companyName = $company->name;
    $company->forceDelete(); // Permanently delete from database

    return redirect()->route('companies.trashed')
        ->with('success', "Company '{$companyName}' has been permanently deleted!");
}
```

6. **Add routes for trashed companies**:

```php
# filename: routes/web.php
# Add these routes inside the authenticated middleware group

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // ... existing routes ...

    // Companies Resource Routes
    Route::resource('companies', CompanyController::class);

    // 🚨 Add soft delete routes
    Route::get('companies/trashed/index', [CompanyController::class, 'trashed'])->name('companies.trashed');
    Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::delete('companies/{id}/force-delete', [CompanyController::class, 'forceDelete'])->name('companies.forceDelete');
});
```

7. **Create the Trashed Companies view**:

```tsx
# filename: resources/js/Pages/Companies/Trashed.tsx
import { Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { PageHeader } from '@/components/PageHeader';
import { Pagination } from '@/components/Pagination';
import AppLayout from '@/Layouts/AppLayout';
import { useState, useEffect, useRef } from 'react';

interface Company {
  id: number;
  name: string;
  email: string;
  phone: string;
  industry: string;
  city: string;
  contacts_count: number;
  deals_count: number;
  deleted_at: string;
}

interface Props {
  companies: {
    data: Company[];
    links: any;
    meta: any;
  };
  filters: {
    search?: string;
  };
}

export default function CompaniesTrashed({ companies, filters }: Props) {
  const { flash } = usePage().props as { flash?: { success?: string } };
  const [search, setSearch] = useState(filters.search || '');
  const initialMount = useRef(true);

  // Debounced search
  useEffect(() => {
    if (initialMount.current) {
      initialMount.current = false;
      return;
    }

    const delayDebounceFn = setTimeout(() => {
      router.get(
        '/companies/trashed/index',
        { search: search },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true,
        }
      );
    }, 300);

    return () => clearTimeout(delayDebounceFn);
  }, [search]);

  const handleRestore = (companyId: number, companyName: string) => {
    if (confirm(`Restore "${companyName}"?`)) {
      router.post(`/companies/${companyId}/restore`, {}, {
        preserveScroll: true,
      });
    }
  };

  const handleForceDelete = (companyId: number, companyName: string) => {
    if (confirm(`Permanently delete "${companyName}"? This action CANNOT be undone!`)) {
      router.delete(`/companies/${companyId}/force-delete`, {
        preserveScroll: true,
      });
    }
  };

  return (
    <AppLayout title="Trashed Companies">
      {flash?.success && (
        <div className="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {flash.success}
        </div>
      )}

      <PageHeader
        title="Trashed Companies"
        description="Restore or permanently delete soft-deleted companies"
      >
        <Link href="/companies">
          <Button variant="outline">Back to Companies</Button>
        </Link>
      </PageHeader>

      {/* Search Section */}
      <Card className="mb-4">
        <CardContent className="pt-6">
          <div className="flex items-center gap-4">
            <div className="flex-1 min-w-[200px]">
              <Input
                type="text"
                placeholder="Search trashed companies..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            {search && (
              <Button variant="outline" onClick={() => setSearch('')}>
                Clear Search
              </Button>
            )}
          </div>
        </CardContent>
      </Card>

      <div className="space-y-4">
        <div className="rounded-lg border overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow className="bg-gray-50">
                <TableHead className="font-semibold">Company Name</TableHead>
                <TableHead className="font-semibold">Industry</TableHead>
                <TableHead className="font-semibold text-center">Contacts</TableHead>
                <TableHead className="font-semibold text-center">Deals</TableHead>
                <TableHead className="font-semibold">Deleted</TableHead>
                <TableHead className="font-semibold">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {companies.data.map((company) => (
                <TableRow key={company.id} className="hover:bg-gray-50">
                  <TableCell className="font-medium">{company.name}</TableCell>
                  <TableCell>{company.industry || '—'}</TableCell>
                  <TableCell className="text-center">
                    {company.contacts_count}
                  </TableCell>
                  <TableCell className="text-center">
                    {company.deals_count}
                  </TableCell>
                  <TableCell>
                    {new Date(company.deleted_at).toLocaleDateString()}
                  </TableCell>
                  <TableCell className="space-x-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleRestore(company.id, company.name)}
                    >
                      Restore
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={() => handleForceDelete(company.id, company.name)}
                    >
                      Delete Forever
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>

        {companies.data.length === 0 && (
          <div className="text-center py-12">
            <p className="text-gray-500 mb-4">No trashed companies found.</p>
            <Link href="/companies">
              <Button variant="outline">Back to Companies</Button>
            </Link>
          </div>
        )}

        <Pagination links={companies.links} meta={companies.meta} />
      </div>
    </AppLayout>
  );
}
```

8. **Add a "View Trashed" link to the Companies Index**:

```tsx
# In resources/js/Pages/Companies/Index.tsx
# Update the PageHeader to include a link to trashed companies

<PageHeader
  title="Companies"
  description="Manage all companies in your CRM"
>
  <div className="flex gap-2">
    <Link href="/companies/trashed/index">
      <Button variant="outline">View Trashed</Button>
    </Link>
    <Link href="/companies/create">
      <Button>Add Company</Button>
    </Link>
  </div>
</PageHeader>
```

### Expected Result

```
✅ SoftDeletes trait added to Company model
✅ Migration created and run successfully
✅ deleted_at column added to companies table
✅ Trashed companies view created
✅ Users can view soft-deleted companies
✅ Users can restore soft-deleted companies
✅ Users can permanently delete companies from trash
✅ "View Trashed" link appears on Companies Index
✅ Delete operations now soft delete by default
```

### Why It Works

**Soft Deletes**: The `SoftDeletes` trait modifies Eloquent queries to automatically exclude soft-deleted records. When you call `$company->delete()`, Laravel sets the `deleted_at` timestamp instead of removing the row from the database.

**Querying Trashed Records**: Use `onlyTrashed()` to retrieve only soft-deleted records, `withTrashed()` to include both active and soft-deleted records, or the default behavior (soft-deleted records excluded).

**Restoration**: The `restore()` method sets `deleted_at` back to `null`, making the record visible in normal queries again.

**Permanent Deletion**: The `forceDelete()` method bypasses soft deletes and permanently removes the record from the database. This is useful for compliance (GDPR right to be forgotten) or cleanup.

**Referential Integrity**: Soft deletes preserve relationships. If a company is soft-deleted, you can still access its contacts and deals through the relationship. If you restore the company, all relationships are intact.

### Troubleshooting

- **Error: "Column not found: deleted_at"** — Run the migration: `sail artisan migrate`.
- **Trashed companies not showing** — Ensure you're using `onlyTrashed()` in the query.
- **Can't restore company** — Verify the policy allows `update` permission for the user.
- **Relationships missing after restore** — This shouldn't happen with soft deletes. If relationships use foreign keys, ensure they're not configured with `onDelete('cascade')`.

## Step 11: Test the Implementation (~10 min)

### Goal

Verify that all CRUD operations work correctly with proper team isolation and authorization.

### Actions

1. **Test creating a company**:

```bash
# Start Sail
sail up -d

# Visit the create form in your browser
open http://localhost/companies/create

# Fill in the form and submit
# You should be redirected to the company list
```

2. **Verify the company appears in the list**:

```bash
# In browser, visit
http://localhost/companies

# You should see your newly created company
```

3. **Test viewing company details**:

```bash
# Click on the company name in the list
# You should see company information, contacts, and deals
```

4. **Test team isolation**:

```bash
# In tinker, verify only your team's companies are visible
sail artisan tinker
$user = App\Models\User::first();
auth()->setUser($user);
Company::all()->count();  # Should only show current team's companies

# Try with a different team
$user2 = User::where('current_team_id', '!=', $user->current_team_id)->first();
auth()->setUser($user2);
Company::all()->count();  # Should be different
```

5. **Test search and filtering**:

```bash
# In browser, visit companies index
http://localhost/companies

# Type in search box - should see debounced results after 300ms
# Select an industry filter - should immediately filter results
# Click "Clear Filters" - should reset to show all companies
```

6. **Test delete confirmation**:

```bash
# Click delete button on a company
# Should see confirmation dialog
# Cancel - company should remain
# Confirm - company should be deleted with success message
```

7. **Test soft deletes**:

```bash
# Delete a company - it should soft delete
# Visit /companies/trashed/index
# You should see the deleted company
# Click "Restore" - company should reappear in main list
# Delete it again, then click "Delete Forever" in trash
# Company should be permanently removed
```

8. **Test authorization**:

```bash
# Create two users in different teams
# Try accessing one user's companies with the other user's session
# Should see 403 Forbidden error
```

### Expected Result

```
✅ Companies created successfully with success message
✅ Companies appear in the list with search/filter working
✅ Company details page shows related contacts and deals with counts
✅ Success messages display after create/update/delete
✅ Soft deletes preserve companies for recovery
✅ Trashed companies view shows deleted companies
✅ Restore functionality works correctly
✅ Permanent delete removes companies from database
✅ Only current team's companies are visible
✅ Authorization prevents cross-team access
✅ Edit and delete work correctly with confirmation
✅ Search debouncing works (waits 300ms)
✅ Filters preserve state in URL
```

### Why It Works

**CRUD Cycle**: Create → Read → Update → Delete flows through your controller, model, and views.

**Authorization**: The policy ensures every action checks team membership, preventing data leakage.

**Eager Loading**: The controller loads relationships upfront, making the Show view fast even with many contacts/deals.

**Flash Messages**: Laravel's session flash data is automatically shared by Inertia, allowing you to display success/error messages after redirects.

**Delete Confirmation**: The browser's `confirm()` dialog prevents accidental deletions, improving data safety.

## Exercises

### Exercise 1: Test Relational Creation

**Goal**: Verify a new contact can be created and correctly assigned to an existing company.

**Instructions**:

1. Log in and navigate to the **Companies Index**.
2. Create a new Company (e.g., "MegaCorp").
3. Navigate to the **Contacts Create** page.
4. Fill in the contact details and select "MegaCorp" from the **Company dropdown**.
5. Submit the form and navigate back to the "MegaCorp" detail page.

**Validation**: The new contact should appear immediately in the **Related Contacts List** on the MegaCorp company page.

### Exercise 2: Implement Filter by Industry

**Goal**: Enhance the industry filter to dynamically load available industries from the database.

**Instructions**:

1. Modify the **`CompanyController@index`** method to pass a list of unique industries to the view.
2. Update the **`Companies/Index.tsx`** component to use the dynamic industries list instead of hardcoded options.
3. Add a "Clear Filters" button that resets both search and industry filters.

**Controller Modification Snippet**:

```php
// app/Http/Controllers/CompanyController.php (in index method)
public function index(Request $request): Response
{
    // ... existing code ...

    $industries = Company::distinct()
        ->whereNotNull('industry')
        ->pluck('industry')
        ->sort()
        ->values();

    return Inertia::render('Companies/Index', [
        'companies' => $companies,
        'filters' => $request->only(['search', 'industry']),
        'industries' => $industries, // 🚨 Pass industries list
    ]);
}
```

**Validation**: The industry dropdown now shows only industries that actually exist in your database, and selecting an industry filters the companies list correctly.

### Exercise 3: Company Stats Dashboard

**Goal**: Create a view showing key company statistics (total companies, companies by industry, total contacts/deals).

Create a new Dashboard component and controller action:

```tsx
// resources/js/Pages/Companies/Dashboard.tsx
// Show metrics like:
// - Total companies: 24
// - By industry: Technology (8), Finance (5), Retail (3)
// - Total contacts across all companies: 156
// - Total deals pipeline value: $2.4M
```

**Validation**: The dashboard loads without errors and displays accurate aggregated data from the database.

## Wrap-up

🎉 **Congratulations!** You've built a complete company management system with full CRUD operations, team-based authorization, and relational data handling.

**What You Accomplished**:

- ✅ Created a resourceful controller with proper authorization checks
- ✅ Built FormRequest validation with team-specific uniqueness rules
- ✅ Built an authorization policy ensuring team data isolation
- ✅ Implemented eager loading with query callbacks to prevent N+1 database queries
- ✅ Created React views for listing, showing, creating, and editing companies
- ✅ Added search and filtering with debouncing to the companies index
- ✅ Implemented flash message display for user feedback
- ✅ Added delete confirmation dialogs to prevent accidental deletions
- ✅ Implemented soft deletes for data recovery and auditing
- ✅ Built a trashed companies view with restore and permanent delete
- ✅ Demonstrated relational data handling by displaying contacts and deals per company
- ✅ Updated Contact forms to allow selecting a company (relational form handling)
- ✅ Implemented pagination with query string preservation
- ✅ Built forms with comprehensive validation
- ✅ Connected your CRM modules—companies now link to contacts and deals

**Your CRM Now Has**:

- Contacts module with full CRUD (Chapter 12)
- Companies module with full CRUD (this chapter)
- Team-based data isolation
- Authorization policies
- Relational data display
- Professional user interface

**How This Connects to the Next Chapter**:

In Chapter 15, you'll build the Deals module with a visual sales pipeline. You'll create a Kanban-style board showing deals by stage, add drag-and-drop functionality, and link deals to companies. The patterns you learned here—relationships, authorization, eager loading—will apply directly to building the pipeline interface.

**Real-World Application**:

The patterns in this chapter are exactly what you'd use to build any CRUD module in a production app. Whether it's Products, Invoices, or Support Tickets, the flow is:

1. Create model with relationships
2. Build controller with authorization
3. Create policy for security
4. Build views with relational display
5. Test everything

This is the foundation of professional Laravel development.

<ChapterCheckbox 
  seriesId="build-crm-laravel-12"
  chapterId="14"
  label="Completed: 14: Companies Module - CRUD Operations"
/>

## Further Reading

- **[Laravel Controllers](https://laravel.com/docs/12.x/controllers)** — Comprehensive guide to resource controllers and authorization
- **[Laravel Policies](https://laravel.com/docs/12.x/authorization#creating-policies)** — Deep dive into authorization policies
- **[Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships)** — Complete reference for eager loading and relationships
- **[Inertia.js Documentation](https://inertiajs.com/)** — Learn more about building SPAs with Laravel and React
- **[React Forms Best Practices](https://react.dev/reference/react/useState)** — Form state management in React
- **[PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)** — PHP coding standards for controllers and models
