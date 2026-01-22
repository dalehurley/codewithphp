---
title: "12: Contacts Module - CRUD Operations"
description: "Build complete CRUD interface for managing contacts with form validation, authorization checks, and React Inertia views"
sidebar:
  label: "12: Contacts Module - CRUD Operations"
  order: 12
  badge:
    text: Intermediate
    variant: caution
---
![Contacts Module - CRUD Operations](/images/build-crm-laravel-12/chapter-12-contacts-module-crud-operations-hero-full.webp)

# Chapter 12: Contacts Module - CRUD Operations

## Overview

You now have a solid Contact model with multi-tenancy security built-in. It's time to expose that model through a complete CRUD interface that your sales team can use. In this chapter, you'll build the backend controllers that handle creating, reading, updating, and deleting contacts, and pair them with React Inertia views that provide a modern interface.

The Contact module will become the cornerstone of your CRM. Users will navigate to the Contacts section, see a list of all team contacts (automatically filtered by the `HasTeamScope` trait from Chapter 11), click to view details, and be able to create new contacts or edit existing ones. Every action will be secured by authorization policies ensuring users can only see their team's data.

By the end of this chapter, you'll understand the complete Laravel-to-React data flow: form validation with Inertia form helpers, authorization with policies, error handling, and reactive form state.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 11](/series/build-crm-laravel-12/chapters/11-contacts-module-database-model) with Contact model and `HasTeamScope` trait
- ✅ Completed [Chapter 10](/series/build-crm-laravel-12/chapters/10-layout-ui-design-customization) with UI layout
- ✅ Completed [Chapter 09](/series/build-crm-laravel-12/chapters/09-authorization-access-control) with authorization policies
- ✅ Laravel Sail running with all containers active
- ✅ Node.js dev server running: `sail npm run dev`
- ✅ Understanding of Laravel Controllers and Eloquent
- ✅ Basic React/Inertia knowledge from previous chapters

**Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Navigate to your project
cd crm-app

# Verify Sail containers are running
sail ps  # Should show laravel.test, mysql, redis all "Up"

# Verify Node dev server is running
# In another terminal: sail npm run dev

# Verify Contact model exists with trait
grep -l "HasTeamScope" app/Models/Contact.php

# Verify Inertia middleware is configured
grep -r "HandleInertiaRequests" app/Http/Middleware/

# Verify UI components from Chapter 10 exist
ls -la resources/js/Components/ | grep -E "Button|Card|Input"
```

## What You'll Build

By the end of this chapter, you will have:

**Backend (Laravel)**:
- ✅ **ContactController** with Index, Show, Create, Store, Edit, Update, Delete actions
- ✅ **Routes** for contacts CRUD in `routes/web.php`
- ✅ **ContactRequest validation classes** for Create and Update (form validation)
- ✅ **ContactPolicy** (or update existing) to authorize who can perform actions
- ✅ **Service layer** (optional) to encapsulate business logic
- ✅ **Error handling** for validation, authorization, and database errors

**Frontend (React/Inertia)**:
- ✅ **Contacts Index page** - List all team contacts with search, pagination, delete action
- ✅ **Contacts Show page** - Display single contact details with edit/delete buttons
- ✅ **Contacts Create page** - Form to add new contact with real-time validation feedback
- ✅ **Contacts Edit page** - Form to update existing contact
- ✅ **Reusable ContactForm component** - Shared form for Create/Edit pages
- ✅ **Navigation integration** - Add Contacts link to sidebar (from Chapter 10)

**Data Flow**:
- ✅ User → React form → Inertia → Laravel Controller → Database
- ✅ Validation errors returned to React with field-level feedback
- ✅ Authorization checks prevent unauthorized access
- ✅ Multi-tenancy enforced automatically via trait

## Objectives

**Backend Development**:
- Create a RESTful controller with proper action names and routing
- Implement form request validation with Laravel's FormRequest class
- Write authorization policies and use them in controllers with `authorize()` method
- Handle validation errors and return them to the frontend
- Implement soft delete and restore functionality where appropriate

**Frontend Development**:
- Build Inertia pages that consume Laravel API responses
- Use Inertia form helper for handling form state and errors
- Create reusable form components with error display
- Implement loading states and success/error messaging
- Build a responsive data table for the index view

**Full-Stack Integration**:
- Understand the complete request/response cycle with Inertia
- Use Vue/React reactivity to provide real-time user feedback
- Implement proper error handling and user communication
- Ensure multi-tenancy security at every step

## Quick Start (Optional)

Want to see the end result in 5 minutes?

```bash
# After completing this chapter, you can:

# 1. Navigate to contacts in your app
# http://localhost:80/contacts

# 2. See all team contacts listed
# (The HasTeamScope trait automatically filters them)

# 3. Click on a contact to see details
# 4. Create a new contact with the form
# 5. Edit an existing contact
# 6. Delete a contact

# From the Laravel CLI, you can also test:
sail artisan tinker
$contact = App\Models\Contact::first();
echo $contact->full_name; # See the data you created through the UI
```

Your Contact module is now fully functional!

---

## Step 1: Generate ContactController and Routes (~15 min)

### Goal

Create the ContactController with RESTful actions and wire up the routes for CRUD operations.

### Actions

1. **Generate the resource controller**:

```bash
# Generate a resource controller with all CRUD methods
sail artisan make:controller ContactController --resource --model=Contact
```

This creates `app/Http/Controllers/ContactController.php` with methods:
- `index()` - List all contacts
- `create()` - Show create form
- `store()` - Save new contact
- `show()` - Display contact details
- `edit()` - Show edit form
- `update()` - Save changes
- `destroy()` - Delete contact

2. **Open and review the controller**:

```bash
cat app/Http/Controllers/ContactController.php
```

3. **Register routes in `routes/web.php`**:

Open `routes/web.php` and add the contact routes inside your authenticated middleware:

```php
# filename: routes/web.php

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✅ Add this: Contacts CRUD
    Route::resource('contacts', ContactController::class);

    // ... other routes ...
});
```

This automatically creates these routes:
- `GET /contacts` → `ContactController@index` (list)
- `GET /contacts/create` → `ContactController@create` (show form)
- `POST /contacts` → `ContactController@store` (save)
- `GET /contacts/{contact}` → `ContactController@show` (view)
- `GET /contacts/{contact}/edit` → `ContactController@edit` (show form)
- `PUT /contacts/{contact}` → `ContactController@update` (save)
- `DELETE /contacts/{contact}` → `ContactController@destroy` (delete)

4. **Verify routes are registered**:

```bash
# List all routes including contacts
sail artisan route:list | grep contacts
```

Expected output should show 7 contact routes with GET, POST, PUT, DELETE methods.

### Expected Result

```
✓ ContactController created with 7 RESTful methods
✓ Routes registered in web.php
✓ All CRUD routes accessible (sail artisan route:list)
✓ Ready to implement controller logic
```

### Why It Works

**Resource controllers** provide a standard RESTful pattern that's widely recognized in web development. Instead of manually defining 7 routes, Laravel's `make:controller --resource` generates them automatically. **Named routes** (`route('contacts.index')`) let you reference URLs throughout your app without hardcoding paths.

### Troubleshooting

- **Routes not showing up** — Verify you added `Route::resource()` inside `web.php` and in the auth middleware
- **"Contact model not found"** — Add `--model=Contact` flag, or the controller won't auto-inject the model
- **Import errors** — Ensure `use App\Http\Controllers\ContactController;` is at the top of routes/web.php

---

## Step 2: Create Form Request Validation Classes (~15 min)

### Goal

Create reusable validation request classes for Create and Update operations with proper authorization.

### Actions

1. **Generate a Form Request for storing/creating contacts**:

```bash
# Create a form request for validating contact creation
sail artisan make:request StoreContactRequest
```

2. **Implement StoreContactRequest** (`app/Http/Requests/StoreContactRequest.php`):

```php
# filename: app/Http/Requests/StoreContactRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // All authenticated users can create contacts in their team
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $teamId = $this->user()->team_id;

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                // IMPORTANT: Enforce email uniqueness WITHIN the team, not globally
                // This allows alice@example.com in team A and team B separately
                Rule::unique('contacts', 'email')
                    ->where('team_id', $teamId),
            ],
            'phone' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'The contact\'s first name is required.',
            'last_name.required' => 'The contact\'s last name is required.',
            'email.required' => 'An email address is required.',
            'email.unique' => 'This email is already in use by another contact in your team.',
        ];
    }
}
```

3. **Generate an UpdateContactRequest**:

```bash
sail artisan make:request UpdateContactRequest
```

4. **Implement UpdateContactRequest**:

```php
# filename: app/Http/Requests/UpdateContactRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Users can only update contacts in their team
        $contact = $this->route('contact');
        return $this->user()->team_id === $contact->team_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $contact = $this->route('contact');
        $teamId = $this->user()->team_id;

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                // Allow the current contact's email, but reject duplicates in the team
                Rule::unique('contacts', 'email')
                    ->where('team_id', $teamId)
                    ->ignore($contact->id),
            ],
            'phone' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already in use by another contact in your team.',
        ];
    }
}
```

### Expected Result

```
✓ StoreContactRequest created with validation rules
✓ UpdateContactRequest created with authorization check
✓ Both classes validate email uniqueness
✓ Custom error messages defined for better UX
```

### Why It Works

**Form Requests** separate validation logic from controllers, keeping them clean and testable. The `authorize()` method checks permission **before** validation runs, preventing unauthorized users from even attempting invalid data.

For updates, `unique:contacts,email,{$contactId}` tells Laravel to allow the current contact's email (ignore the current record), but reject if another contact uses it.

### Troubleshooting

- **"Request class not found"** in controller — Add `use App\Http\Requests\StoreContactRequest;` at the top
- **Validation not working** — Verify rules array has correct field names matching your form
- **"Column not found" in unique rule** — Ensure the table and column names are correct

---

## Step 3: Implement ContactController Methods (~20 min)

### Goal

Build the controller with logic for each CRUD action, using your Contact model and form validation.

### Actions

1. **Open the ContactController**:

```bash
cat app/Http/Controllers/ContactController.php
```

2. **Implement all controller methods**:

Replace the placeholder methods with full implementations:

```php
# filename: app/Http/Controllers/ContactController.php
<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Inertia\Inertia;

class ContactController extends Controller
{
    /**
     * Display a listing of all team contacts.
     * The HasTeamScope trait automatically filters by team_id
     */
    public function index(): Response
    {
        $contacts = Contact::with(['company', 'owner:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'flash' => session('message'), // For success messages
        ]);
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create(): Response
    {
        // Fetch companies for the dropdown
        $companies = auth()->user()->team->companies()->pluck('name', 'id');

        return Inertia::render('Contacts/Create', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created contact in the database.
     * Form validation happens automatically via StoreContactRequest.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        // Validated data from request
        $data = $request->validated();

        // Add current user's team_id (for safety, even though trait does this)
        $data['team_id'] = auth()->user()->team_id;

        // Create the contact
        $contact = Contact::create($data);

        return redirect()
            ->route('contacts.show', $contact)
            ->with('message', "Contact '{$contact->full_name}' created successfully.");
    }

    /**
     * Display a single contact with details.
     */
    public function show(Contact $contact): Response
    {
        // Authorization check: User can only see contacts from their team
        $this->authorize('view', $contact);

        // Load relationships for display
        $contact->load(['team', 'company', 'owner:id,name', 'deals']);

        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
        ]);
    }

    /**
     * Show the form for editing a contact.
     */
    public function edit(Contact $contact): Response
    {
        // Authorization check
        $this->authorize('update', $contact);

        // Fetch companies for dropdown
        $companies = auth()->user()->team->companies()->pluck('name', 'id');

        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
            'companies' => $companies,
        ]);
    }

    /**
     * Update the contact in the database.
     */
    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        // Authorization check
        $this->authorize('update', $contact);

        // Update with validated data
        $contact->update($request->validated());

        return redirect()
            ->route('contacts.show', $contact)
            ->with('message', 'Contact updated successfully.');
    }

    /**
     * Delete a contact (soft delete if using SoftDeletes).
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        // Authorization check
        $this->authorize('delete', $contact);

        $fullName = $contact->full_name;
        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('message', "Contact '{$fullName}' deleted successfully.");
    }
}
```

3. **Verify the controller compiles**:

```bash
# Check for syntax errors
sail artisan make:controller --help  # If this works, syntax is OK
```

### Expected Result

```
✓ All 7 CRUD methods implemented
✓ Form validation integrated (StoreContactRequest, UpdateContactRequest)
✓ Authorization checks in place (show, edit, update, destroy)
✓ Inertia responses render React components
✓ Flash messages passed for user feedback
```

### Why It Works

**`$this->authorize()`** checks authorization policies before allowing the action. Laravel will throw a 403 error if denied.

**Eager loading** with `->with()` prevents N+1 query problems:
- **Without eager loading**: 1 query for contacts + 1 query PER contact for relationships = 16 queries for 15 contacts
- **With eager loading**: 1 query for contacts + 1 query for companies + 1 query for owners = 3 queries total
- This is critical for performance as your contact list grows

**`->paginate(15)`** returns paginated results for scalability:
- Limits database load (only 15 contacts per page, not all)
- Improves frontend performance (less data to render)
- Better UX with navigation between pages

### Troubleshooting

- **"FormRequest class not found"** — Add imports at the top: `use App\Http\Requests\StoreContactRequest;`
- **"Inertia not found"** — Add import: `use Inertia\Inertia; use Inertia\Response;`
- **Authorization method not found** — Ensure you've created the ContactPolicy in Step 4

---

## Step 4: Create ContactPolicy for Authorization (~10 min)

### Goal

Create an authorization policy to control who can view, edit, or delete contacts. This is a practical application of the authorization patterns introduced in [Chapter 09](/series/build-crm-laravel-12/chapters/09-authorization-access-control).

### Actions

1. **Generate a policy**:

```bash
sail artisan make:policy ContactPolicy --model=Contact
```

2. **Implement the policy**:

```php
# filename: app/Policies/ContactPolicy.php
<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    /**
     * Determine if the user can view the contact.
     * Users can only view contacts from their own team.
     */
    public function view(User $user, Contact $contact): bool
    {
        return $user->team_id === $contact->team_id;
    }

    /**
     * Determine if the user can create a contact.
     * All authenticated users can create (team_id is set automatically).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the contact.
     */
    public function update(User $user, Contact $contact): bool
    {
        return $user->team_id === $contact->team_id;
    }

    /**
     * Determine if the user can delete the contact.
     */
    public function delete(User $user, Contact $contact): bool
    {
        return $user->team_id === $contact->team_id;
    }

    /**
     * Determine if the user can permanently delete the contact.
     * (If using soft deletes - currently prevented for safety)
     * 
     * Note: The Contact model uses SoftDeletes trait (see Chapter 11).
     * delete() performs soft delete, forceDelete() permanently removes.
     * We restrict forceDelete for data recovery purposes.
     */
    public function forceDelete(User $user, Contact $contact): bool
    {
        return false; // Prevent permanent deletion; only admins should do this
    }

    /**
     * Determine if the user can restore a soft-deleted contact.
     * (Only applicable if contact was soft deleted)
     */
    public function restore(User $user, Contact $contact): bool
    {
        return $user->team_id === $contact->team_id;
    }
}
```

3. **Register the policy** (if not auto-discovered):

Most Laravel versions auto-discover policies. To verify, check `app/Providers/AuthServiceProvider.php`:

```php
# filename: app/Providers/AuthServiceProvider.php

protected $policies = [
    // If needed, add explicitly:
    // Contact::class => ContactPolicy::class,
];
```

Modern Laravel auto-discovers policies by convention, so you may not need to add it.

### Expected Result

```
✓ ContactPolicy created
✓ Authorization checks defined for view, update, delete
✓ Team membership verified for all actions
✓ Ready to use in controller with $this->authorize()
```

### Why It Works

**Policies centralize authorization logic** in a dedicated class instead of scattering checks throughout controllers. When the controller calls `$this->authorize('update', $contact)`, Laravel finds the `ContactPolicy::update()` method and runs it.

The policy ensures **users can only access their team's contacts**, even if someone tries to manually construct a URL with another team's contact ID.

### Troubleshooting

- **Authorization check not working** — Verify the policy method name matches the action name in controller
- **Team comparison always fails** — Ensure both `$user->team_id` and `$contact->team_id` have values (check database)

---

## Step 5: Build React/Inertia Pages for Contacts (~30 min)

### Goal

Create the frontend views for displaying and managing contacts using React and Inertia.

### Prerequisites for This Step

Before proceeding, verify you have the UI components from Chapter 10:
- `Button.tsx` - For action buttons
- `Card.tsx` - For content containers
- `Input.tsx` - For form fields
- `Select.tsx` - For dropdowns

If these components don't exist, create basic versions or import from shadcn/ui.

### Actions

1. **Create the Contacts page directory**:

```bash
# From project root
mkdir -p resources/js/Pages/Contacts
```

2. **Create the Contacts Index page** (list all contacts):

```tsx
# filename: resources/js/Pages/Contacts/Index.tsx
import React, { useState } from 'react'
import { Head, Link, usePage, router } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Button } from '@/Components/Button'
import { Card } from '@/Components/Card'
import { AlertCircle, Edit, Trash2, Plus } from 'lucide-react'

interface Contact {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone?: string
  title?: string
  company?: { id: number; name: string }
  created_at: string
}

interface Props {
  contacts: {
    data: Contact[]
    links: any
    meta: any
  }
  flash?: { message: string }
}

export default function Index({ contacts, flash }: Props) {
  const [deleting, setDeleting] = useState<number | null>(null)

  const handleDelete = (contact: Contact) => {
    if (confirm(`Delete ${contact.full_name}? This action cannot be undone.`)) {
      setDeleting(contact.id)
      router.delete(route('contacts.destroy', contact.id), {
        onFinish: () => setDeleting(null),
      })
    }
  }

  return (
    <AuthenticatedLayout>
      <Head title="Contacts" />

      <div className="py-8">
        {/* Header */}
        <div className="mb-8 flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Contacts</h1>
            <p className="mt-2 text-gray-600 dark:text-gray-400">
              Manage all contacts in your CRM
            </p>
          </div>
          <Link href={route('contacts.create')}>
            <Button className="flex items-center gap-2">
              <Plus className="h-4 w-4" />
              New Contact
            </Button>
          </Link>
        </div>

        {/* Flash Message */}
        {flash?.message && (
          <div className="mb-4 rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-900/20 dark:text-green-300">
            {flash.message}
          </div>
        )}

        {/* Contacts Table */}
        <Card>
          {contacts.data.length === 0 ? (
            <div className="py-12 text-center">
              <AlertCircle className="mx-auto h-12 w-12 text-gray-400" />
              <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                No contacts yet
              </h3>
              <p className="mt-2 text-gray-600 dark:text-gray-400">
                Create your first contact to get started
              </p>
              <Link href={route('contacts.create')}>
                <Button className="mt-4">Create First Contact</Button>
              </Link>
            </div>
          ) : (
            <table className="w-full">
              <thead className="border-b border-gray-200 dark:border-gray-700">
                <tr>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                    Name
                  </th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                    Email
                  </th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                    Company
                  </th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                    Title
                  </th>
                  <th className="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {contacts.data.map((contact) => (
                  <tr
                    key={contact.id}
                    className="hover:bg-gray-50 dark:hover:bg-gray-800"
                  >
                    <td className="px-6 py-4">
                      <Link href={route('contacts.show', contact.id)}>
                        <span className="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                          {contact.full_name}
                        </span>
                      </Link>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                      {contact.email}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                      {contact.company?.name || '—'}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                      {contact.title || '—'}
                    </td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <Link href={route('contacts.edit', contact.id)}>
                          <Button
                            variant="ghost"
                            size="sm"
                            className="flex items-center gap-1"
                          >
                            <Edit className="h-4 w-4" />
                          </Button>
                        </Link>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="flex items-center gap-1 text-red-600 hover:text-red-800"
                          onClick={() => handleDelete(contact)}
                          disabled={deleting === contact.id}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </Card>

        {/* Pagination */}
        {contacts.links && (
          <div className="mt-6 flex items-center justify-between">
            <div className="text-sm text-gray-600 dark:text-gray-400">
              Showing {contacts.meta.from} to {contacts.meta.to} of {contacts.meta.total}
            </div>
            <div className="flex gap-2">
              {contacts.links.map((link: any) => (
                <Link
                  key={link.label}
                  href={link.url || '#'}
                  className={`inline-flex items-center px-3 py-2 text-sm ${
                    link.active
                      ? 'border-b-2 border-blue-600 font-medium text-blue-600'
                      : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
                  }`}
                >
                  {link.label}
                </Link>
              ))}
            </div>
          </div>
        )}
      </div>
    </AuthenticatedLayout>
  )
}
```

3. **Create ContactForm component** (shared by Create/Edit):

```tsx
# filename: resources/js/Pages/Contacts/ContactForm.tsx
import React from 'react'
import { useForm } from '@inertiajs/react'
import { Button } from '@/Components/Button'
import { Input } from '@/Components/Input'
import { Select } from '@/Components/Select'
import TextArea from '@/Components/TextArea'

interface Contact {
  id?: number
  first_name: string
  last_name: string
  email: string
  phone?: string
  title?: string
  company_id?: number
}

interface Props {
  contact?: Contact
  companies: Record<string, string>
  onSubmit: (data: any) => void
  isSubmitting: boolean
}

export default function ContactForm({
  contact,
  companies,
  onSubmit,
  isSubmitting,
}: Props) {
  const { data, setData, errors } = useForm({
    first_name: contact?.first_name || '',
    last_name: contact?.last_name || '',
    email: contact?.email || '',
    phone: contact?.phone || '',
    title: contact?.title || '',
    company_id: contact?.company_id || '',
  })

  const handleChange = (field: string, value: any) => {
    setData(field as any, value)
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    onSubmit(data)
  }

  // Check if there are any validation errors
  const hasErrors = Object.keys(errors).length > 0

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Form-Level Error Alert (shows if there are any validation errors) */}
      {hasErrors && (
        <div className="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
          <p className="text-sm font-medium text-red-800 dark:text-red-200">
            Please fix the errors below before submitting:
          </p>
          <ul className="mt-2 list-inside list-disc space-y-1">
            {Object.entries(errors).map(([field, message]) => (
              <li key={field} className="text-sm text-red-700 dark:text-red-300">
                {Array.isArray(message) ? message[0] : message}
              </li>
            ))}
          </ul>
        </div>
      )}
      {/* First and Last Name Row */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label htmlFor="first_name" className="block text-sm font-medium text-gray-900 dark:text-white">
            First Name *
          </label>
          <Input
            id="first_name"
            type="text"
            value={data.first_name}
            onChange={(e) => handleChange('first_name', e.target.value)}
            className={errors.first_name ? 'border-red-500' : ''}
            placeholder="John"
          />
          {errors.first_name && (
            <p className="mt-1 text-sm text-red-600">{errors.first_name}</p>
          )}
        </div>

        <div>
          <label htmlFor="last_name" className="block text-sm font-medium text-gray-900 dark:text-white">
            Last Name *
          </label>
          <Input
            id="last_name"
            type="text"
            value={data.last_name}
            onChange={(e) => handleChange('last_name', e.target.value)}
            className={errors.last_name ? 'border-red-500' : ''}
            placeholder="Smith"
          />
          {errors.last_name && (
            <p className="mt-1 text-sm text-red-600">{errors.last_name}</p>
          )}
        </div>
      </div>

      {/* Email */}
      <div>
        <label htmlFor="email" className="block text-sm font-medium text-gray-900 dark:text-white">
          Email *
        </label>
        <Input
          id="email"
          type="email"
          value={data.email}
          onChange={(e) => handleChange('email', e.target.value)}
          className={errors.email ? 'border-red-500' : ''}
          placeholder="john@example.com"
        />
        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
      </div>

      {/* Phone and Title Row */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label htmlFor="phone" className="block text-sm font-medium text-gray-900 dark:text-white">
            Phone
          </label>
          <Input
            id="phone"
            type="tel"
            value={data.phone}
            onChange={(e) => handleChange('phone', e.target.value)}
            className={errors.phone ? 'border-red-500' : ''}
            placeholder="+1 (555) 000-0000"
          />
          {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
        </div>

        <div>
          <label htmlFor="title" className="block text-sm font-medium text-gray-900 dark:text-white">
            Title
          </label>
          <Input
            id="title"
            type="text"
            value={data.title}
            onChange={(e) => handleChange('title', e.target.value)}
            className={errors.title ? 'border-red-500' : ''}
            placeholder="Sales Manager"
          />
          {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
        </div>
      </div>

      {/* Company */}
      <div>
        <label htmlFor="company_id" className="block text-sm font-medium text-gray-900 dark:text-white">
          Company
        </label>
        <Select
          id="company_id"
          value={data.company_id}
          onChange={(e) => handleChange('company_id', e.target.value)}
          className={errors.company_id ? 'border-red-500' : ''}
        >
          <option value="">Select a company...</option>
          {Object.entries(companies).map(([id, name]) => (
            <option key={id} value={id}>
              {name}
            </option>
          ))}
        </Select>
        {errors.company_id && (
          <p className="mt-1 text-sm text-red-600">{errors.company_id}</p>
        )}
      </div>

      {/* Submit Button */}
      <div className="flex gap-2 pt-4">
        <Button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Saving...' : 'Save Contact'}
        </Button>
        <Button variant="outline" type="button" onClick={() => window.history.back()}>
          Cancel
        </Button>
      </div>
    </form>
  )
}
```

4. **Create Contacts Create page**:

```tsx
# filename: resources/js/Pages/Contacts/Create.tsx
import React from 'react'
import { Head, router } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Card } from '@/Components/Card'
import ContactForm from './ContactForm'

interface Props {
  companies: Record<string, string>
}

export default function Create({ companies }: Props) {
  const [isSubmitting, setIsSubmitting] = React.useState(false)

  const handleSubmit = (data: any) => {
    setIsSubmitting(true)
    router.post(route('contacts.store'), data, {
      onFinish: () => setIsSubmitting(false),
    })
  }

  return (
    <AuthenticatedLayout>
      <Head title="Create Contact" />

      <div className="py-8">
        <h1 className="mb-8 text-3xl font-bold text-gray-900 dark:text-white">
          Create New Contact
        </h1>

        <Card className="max-w-2xl">
          <ContactForm
            companies={companies}
            onSubmit={handleSubmit}
            isSubmitting={isSubmitting}
          />
        </Card>
      </div>
    </AuthenticatedLayout>
  )
}
```

5. **Create Contacts Edit page**:

```tsx
# filename: resources/js/Pages/Contacts/Edit.tsx
import React from 'react'
import { Head, router } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Card } from '@/Components/Card'
import ContactForm from './ContactForm'

interface Contact {
  id: number
  first_name: string
  last_name: string
  email: string
  phone?: string
  title?: string
  company_id?: number
  full_name: string
}

interface Props {
  contact: Contact
  companies: Record<string, string>
}

export default function Edit({ contact, companies }: Props) {
  const [isSubmitting, setIsSubmitting] = React.useState(false)

  const handleSubmit = (data: any) => {
    setIsSubmitting(true)
    router.put(route('contacts.update', contact.id), data, {
      onFinish: () => setIsSubmitting(false),
    })
  }

  return (
    <AuthenticatedLayout>
      <Head title={`Edit ${contact.full_name}`} />

      <div className="py-8">
        <h1 className="mb-8 text-3xl font-bold text-gray-900 dark:text-white">
          Edit: {contact.full_name}
        </h1>

        <Card className="max-w-2xl">
          <ContactForm
            contact={contact}
            companies={companies}
            onSubmit={handleSubmit}
            isSubmitting={isSubmitting}
          />
        </Card>
      </div>
    </AuthenticatedLayout>
  )
}
```

6. **Create Contacts Show page**:

```tsx
# filename: resources/js/Pages/Contacts/Show.tsx
import React from 'react'
import { Head, Link } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Card } from '@/Components/Card'
import { Button } from '@/Components/Button'
import { Edit, ArrowLeft } from 'lucide-react'

interface Contact {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone?: string
  title?: string
  company?: { id: number; name: string }
  owner?: { id: number; name: string }
  deals?: any[]
  created_at: string
  updated_at: string
}

interface Props {
  contact: Contact
}

export default function Show({ contact }: Props) {
  return (
    <AuthenticatedLayout>
      <Head title={contact.full_name} />

      <div className="py-8">
        {/* Header */}
        <div className="mb-8 flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Link href={route('contacts.index')}>
              <Button variant="ghost" size="sm">
                <ArrowLeft className="h-4 w-4" />
              </Button>
            </Link>
            <div>
              <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                {contact.full_name}
              </h1>
              {contact.title && (
                <p className="mt-2 text-gray-600 dark:text-gray-400">{contact.title}</p>
              )}
            </div>
          </div>
          <Link href={route('contacts.edit', contact.id)}>
            <Button className="flex items-center gap-2">
              <Edit className="h-4 w-4" />
              Edit
            </Button>
          </Link>
        </div>

        <div className="grid grid-cols-3 gap-6">
          {/* Main Details */}
          <div className="col-span-2 space-y-6">
            {/* Contact Information */}
            <Card>
              <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                Contact Information
              </h2>
              <dl className="space-y-4">
                <div>
                  <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                    Email
                  </dt>
                  <dd className="mt-1 text-gray-900 dark:text-white">
                    <a
                      href={`mailto:${contact.email}`}
                      className="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                    >
                      {contact.email}
                    </a>
                  </dd>
                </div>
                {contact.phone && (
                  <div>
                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                      Phone
                    </dt>
                    <dd className="mt-1 text-gray-900 dark:text-white">{contact.phone}</dd>
                  </div>
                )}
                {contact.title && (
                  <div>
                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                      Job Title
                    </dt>
                    <dd className="mt-1 text-gray-900 dark:text-white">{contact.title}</dd>
                  </div>
                )}
              </dl>
            </Card>

            {/* Company Information */}
            {contact.company && (
              <Card>
                <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                  Company
                </h2>
                <Link href={route('companies.show', contact.company.id)}>
                  <p className="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    {contact.company.name}
                  </p>
                </Link>
              </Card>
            )}

            {/* Related Deals */}
            {contact.deals && contact.deals.length > 0 && (
              <Card>
                <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                  Related Deals
                </h2>
                <ul className="space-y-2">
                  {contact.deals.map((deal) => (
                    <li key={deal.id}>
                      <Link href={route('deals.show', deal.id)}>
                        <span className="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                          {deal.title}
                        </span>
                      </Link>
                    </li>
                  ))}
                </ul>
              </Card>
            )}
          </div>

          {/* Sidebar */}
          <div>
            {contact.owner && (
              <Card>
                <h3 className="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                  Assigned To
                </h3>
                <p className="text-gray-600 dark:text-gray-400">{contact.owner.name}</p>
              </Card>
            )}

            <Card className="mt-6">
              <h3 className="mb-4 text-sm font-semibold text-gray-900 dark:text-white">
                Created
              </h3>
              <p className="text-sm text-gray-600 dark:text-gray-400">
                {new Date(contact.created_at).toLocaleDateString()}
              </p>
            </Card>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
```

### Expected Result

```
✓ Contacts Index page displays list with pagination
✓ Create page shows form for new contacts
✓ Edit page shows form with existing data pre-filled
✓ Show page displays contact details
✓ Validation errors display in-form with red highlighting
✓ Flash messages show success after create/update/delete
✓ Links work between pages (Index → Show → Edit)
```

### Why It Works

**Inertia Form Helper** (`useForm`) manages form state and tracks changes. When the form submits, Inertia sends data to the server, catches validation errors, and re-renders with errors displayed.

**Component Reuse**: The `ContactForm` is shared between Create and Edit pages, reducing duplication.

**Eager-loaded Relationships**: The controller eager-loads `company` and `owner`, so the frontend has all necessary data without N+1 queries.

### Troubleshooting

- **"Input component not found"** — Create basic Input component: `resources/js/Components/Input.tsx`
- **Form doesn't submit** — Check browser console for errors; verify form field names match backend validation rules
- **Validation errors not showing** — Verify error object structure from Laravel is correct

---

## Step 6: Update Sidebar Navigation (~5 min)

### Goal

Add a Contacts link to the CRM navigation sidebar from Chapter 10.

### Actions

1. **Open your Sidebar component** (created in Chapter 10):

```bash
cat resources/js/Components/Sidebar.tsx
```

2. **Add Contacts navigation item**:

Find the navigation section and add this link:

```tsx
// Inside your nav items array:

{
  label: 'Contacts',
  href: route('contacts.index'),
  icon: Users,  // or PersonIcon
  active: route().current('contacts.*'),
}
```

Full example in context:

```tsx
# filename: resources/js/Components/Sidebar.tsx (partial)

import { Users, Building, Briefcase, CheckSquare, BarChart3 } from 'lucide-react'

const navItems = [
  {
    label: 'Dashboard',
    href: route('dashboard'),
    icon: BarChart3,
    active: route().current('dashboard'),
  },
  {
    label: 'Contacts',        // ← Add this
    href: route('contacts.index'),
    icon: Users,
    active: route().current('contacts.*'),
  },
  {
    label: 'Companies',
    href: route('companies.index'),
    icon: Building,
    active: route().current('companies.*'),
  },
  {
    label: 'Deals',
    href: route('deals.index'),
    icon: Briefcase,
    active: route().current('deals.*'),
  },
  {
    label: 'Tasks',
    href: route('tasks.index'),
    icon: CheckSquare,
    active: route().current('tasks.*'),
  },
]

// Render the items...
```

3. **Verify the link works**:

```bash
# Restart dev server if necessary
sail npm run dev
```

Visit your app and look for the Contacts link in the sidebar.

### Expected Result

```
✓ Contacts link appears in sidebar
✓ Link highlights when on contacts pages
✓ Clicking the link navigates to /contacts
```

---

## Exercises

### Exercise 1: Test Authorization Failure (~10 min)

**Goal**: Verify that a user in one team cannot access or modify a contact in another team.

**Instructions**:

1. **Create test data** in Tinker:

```bash
sail artisan tinker

# Create a second team and user
$teamB = App\Models\Team::create(['name' => 'Beta Team']);
$userB = App\Models\User::create([
    'team_id' => $teamB->id,
    'name' => 'User B',
    'email' => 'userb@example.com',
    'password' => bcrypt('password')
]);

# Create a contact for team B
$contactB = App\Models\Contact::create([
    'team_id' => $teamB->id,
    'user_id' => $userB->id,
    'first_name' => 'Contact',
    'last_name' => 'B',
    'email' => 'contactb@example.com'
]);

exit
```

2. **Test the policy manually**:

```bash
sail artisan tinker

$userA = App\Models\User::find(1);  # Original user in team A
$contactB = App\Models\Contact::find(CONTACT_B_ID);

// Test the policy directly
$policy = new App\Policies\ContactPolicy();
dd($policy->update($userA, $contactB));  // Should return FALSE
```

Expected: Policy returns `false` because `$userA->team_id !== $contactB->team_id`

3. **Test via browser**:
   - Login as User A
   - Try accessing `/contacts/{contactB->id}/edit` in URL bar
   - Should receive 403 Forbidden error

**Validation**: Authorization check correctly prevents cross-team access.

---

### Exercise 2: Implement Search and Filter (~20 min)

**Goal**: Add search functionality to filter contacts by name or email on the index page.

**Instructions**:

1. **Update ContactController@index** to handle search parameter:

```php
# filename: app/Http/Controllers/ContactController.php (in index method)

public function index(Request $request): Response
{
    $contacts = Contact::with(['company', 'owner:id,name'])
        // Add search filtering
        ->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15)
        ->withQueryString();  // Preserve search param in pagination links

    return Inertia::render('Contacts/Index', [
        'contacts' => $contacts,
        'filters' => [
            'search' => $request->input('search'),
        ],
    ]);
}
```

2. **Update Contacts/Index.tsx** to include search input:

```tsx
# filename: resources/js/Pages/Contacts/Index.tsx (add to component)

import { router } from '@inertiajs/react'

export default function Index({ contacts, filters }: Props) {
  const [search, setSearch] = React.useState(filters?.search || '')

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    if (search.trim()) {
      router.get(route('contacts.index'), { search }, { preserveScroll: true })
    } else {
      router.get(route('contacts.index'), {}, { preserveScroll: true })
    }
  }

  return (
    <AuthenticatedLayout>
      {/* ... existing header ... */}

      {/* Add search form before the table */}
      <Card className="mb-6">
        <form onSubmit={handleSearch} className="flex gap-2">
          <Input
            type="text"
            placeholder="Search by name or email..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="flex-1"
          />
          <Button type="submit">Search</Button>
          {search && (
            <Button
              type="button"
              variant="outline"
              onClick={() => {
                setSearch('')
                router.get(route('contacts.index'), {})
              }}
            >
              Clear
            </Button>
          )}
        </form>
      </Card>

      {/* ... existing table ... */}
    </AuthenticatedLayout>
  )
}
```

**Validation**: 
- Searching for "John" filters to contacts with "John" in first/last name ✓
- Searching for "john@example.com" finds the matching email ✓
- Pagination links preserve the search term ✓
- "Clear" button resets search ✓
- Searching for non-existent term (e.g., "xyz") shows no results ✓
- Case-insensitive search works (e.g., "john" matches "John") ✓
- Partial matches work (e.g., "al" finds "Alice") ✓
- Blank search shows all contacts again ✓

---

### Exercise 3: Test CRUD Operations in the Browser (~20 min)

**Goal**: Verify all CRUD operations work end-to-end through the UI.

**Instructions**:

1. **Create a contact**:
   - Click "Contacts" in sidebar
   - Click "New Contact"
   - Fill form: First Name "Alice", Last Name "Johnson", Email "alice@example.com"
   - Submit
   - Should redirect to contact view with "Contact created successfully" message

2. **View the contact**:
   - Contact detail page displays with all fields
   - Email shows as mailto link
   - "Edit" button is visible

3. **Edit the contact**:
   - Click "Edit" button
   - Change job title to "Sales Manager"
   - Submit
   - Should show updated data with "Contact updated successfully" message

4. **List and search contacts**:
   - Return to Contacts index
   - Contact appears in the table
   - Use search box to find "Alice" or "alice@example.com"
   - Results filter correctly

5. **Delete the contact**:
   - Click delete button (trash icon)
   - Confirm deletion
   - Contact removed from list with success message

6. **Test edge cases**:
   - Create multiple contacts and verify pagination works (create 20+)
   - Verify "No contacts yet" message shows when list is empty
   - Create contact with minimal data (only required fields)
   - Edit contact without changing any data (should still work)

**Validation**: All 5 operations complete without errors, messages display correctly, and edge cases handled gracefully.

---

### Exercise 4: Test Form Validation (~15 min)

**Goal**: Verify validation rules work and display errors properly.

**Instructions**:

1. Navigate to Create contact form

2. **Test required field validation**:
   - Leave first name blank, submit
   - Should show "The first name is required" error in red

3. **Test email validation**:
   - Enter invalid email "not-an-email"
   - Should show email format error

4. **Test email uniqueness within team**:
   - Create contact: alice@example.com in team A
   - Try creating another contact with same email in team A
   - Should fail with "This email is already in use"
   - Verify you CAN create alice@example.com in team B (different team)

5. **Test field constraints**:
   - Enter a 200-character first name (exceeds max:100)
   - Should show "First name must not exceed 100 characters"

**Validation**: 
- Required fields show errors
- Email format validated
- Email unique per team (not globally)
- All error messages display clearly in red
- Form doesn't submit until fixed

---

## Important: Clean Up Test Data Before Moving Forward

After completing exercises, clean up the test data you created to keep your development environment organized:

```bash
# Option 1: Delete specific contacts (faster, selective)
sail artisan tinker
$contacts = App\Models\Contact::where('email', 'like', '%example.com%')->get();
$contacts->each->delete();
exit

# Option 2: Reset entire database (comprehensive, but deletes all data)
sail artisan migrate:refresh
sail artisan db:seed  # if you have seeders

# Verify cleanup
sail artisan tinker
App\Models\Contact::count()  # Should return 0 or expected count
```

**Important**: If you're moving to the next chapter (Chapter 13), clean up test data to avoid confusion with new modules.

---

## Wrap-up 🎉

You have successfully built a complete CRUD interface for Contacts:

### What You've Accomplished

- ✅ **Created ContactController** with all 7 RESTful actions
- ✅ **Implemented form validation** with StoreContactRequest and UpdateContactRequest
- ✅ **Built authorization policies** to ensure users see only their team's data
- ✅ **Created React pages** for Index, Create, Edit, and Show
- ✅ **Integrated validation errors** into the form UI
- ✅ **Added navigation** to the sidebar
- ✅ **Tested end-to-end** CRUD operations
- ✅ **Verified multi-tenancy** with the HasTeamScope trait from Chapter 11

### Key Concepts You've Learned

**Backend Patterns**:
- Resource controllers with RESTful routing
- Form Requests for validation and authorization
- Authorization policies for fine-grained access control
- Eager loading for query optimization

**Frontend Patterns**:
- Inertia form helper for state management
- Shared components (ContactForm) to reduce duplication
- Error display in reactive UI
- Pagination and data listing

**Full-Stack Integration**:
- Laravel to React data flow
- Validation errors returned and displayed
- Authorization checks at both controller and policy level
- Multi-tenancy enforced silently by the trait

### How This Connects to Future Chapters

**Companies Module (Chapter 13-14)**: Will follow the same pattern—database model, then CRUD. You can reuse the ContactForm structure for company forms.

**Deals Module (Chapter 15-16)**: Deals are more complex (with status pipelines), but the basic CRUD pattern remains the same.

**Tasks Module (Chapter 17-18)**: Similar structure with additional task-specific features.

The Contact module is now the template for all future modules. Each will have:
1. Database schema and model (Chapter N)
2. CRUD interface with forms and list views (Chapter N+1)

---

## Implementation Checklist ✅

Before moving to Chapter 13, verify:

### Backend Setup
- [ ] ContactController created with 7 RESTful methods
- [ ] Routes registered: `Route::resource('contacts', ContactController::class)`
- [ ] StoreContactRequest created with validation rules
- [ ] UpdateContactRequest created with authorization
- [ ] ContactPolicy created with view/update/delete checks
- [ ] All imports correct in controller (use statements)

### Frontend Setup
- [ ] Contacts/Index.tsx created and displays contact list
- [ ] Contacts/Show.tsx created and displays details
- [ ] Contacts/Create.tsx created with form
- [ ] Contacts/Edit.tsx created with form
- [ ] ContactForm.tsx component created and shared
- [ ] Sidebar updated with Contacts navigation link

### Testing
- [ ] Create a contact via UI ✓
- [ ] View contact details ✓
- [ ] Edit contact ✓
- [ ] Delete contact ✓
- [ ] Form validation shows errors ✓
- [ ] Contacts appear in sidebar navigation ✓

### Security
- [ ] Authorization checks in place ($this->authorize)
- [ ] Only team contacts visible (HasTeamScope working)
- [ ] Manual URL access to other team's contact shows 403
- [ ] Validation errors prevent bad data

---


## Further Reading

- [Laravel Resource Controllers](https://laravel.com/docs/12.x/controllers#resource-controllers) — Convention-based routing and controller patterns
- [Form Requests](https://laravel.com/docs/12.x/validation#form-request-validation) — Validation and authorization in dedicated classes
- [Authorization Policies](https://laravel.com/docs/12.x/authorization#creating-policies) — Fine-grained access control
- [Inertia.js Documentation](https://inertiajs.com/responses) — Full Inertia guide for Laravel integration
- [Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships) — Eager loading and relationship patterns
- [Pagination](https://laravel.com/docs/12.x/pagination) — Handling large data sets efficiently
