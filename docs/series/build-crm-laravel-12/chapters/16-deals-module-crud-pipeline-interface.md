---
title: "16: Deals Module - CRUD & Pipeline Interface"
description: "Build deal management with visual Kanban pipeline and drag-and-drop stage transitions"
series: "build-crm-laravel-12"
chapter: 16
order: 16
difficulty: "Intermediate"
prerequisites:
  - "/series/build-crm-laravel-12/chapters/15-deals-module-database-pipeline-design"
---

![Deals Module - CRUD & Pipeline Interface](/images/build-crm-laravel-12/chapter-16-deals-module-crud-pipeline-interface-hero-full.webp)

# Chapter 16: Deals Module - CRUD & Pipeline Interface

## Overview

Your pipeline is designed, your database is ready—now it's time to build the interface that turns your CRM into a revenue-tracking powerhouse. The Deals CRUD interface is where sales teams live: creating opportunities, updating amounts, transitioning deals through stages, and ultimately marking deals as Won or Lost.

In this chapter, you'll build a complete Deal management system with Create, Read, Update, and Delete operations plus a visual Kanban-style pipeline board. You'll implement drag-and-drop functionality (or select-based stage transitions), create Laravel controllers with authorization ensuring team data isolation, build React/Inertia views displaying deals grouped by stage, and demonstrate real-world patterns like weighted forecasting and stage history tracking.

By the end of this chapter, your sales team will be able to:

- **View the sales pipeline** in a visual Kanban board with deals organized by stage
- **Create new deals** linked to companies and contacts with expected closing dates
- **Drag deals between stages** (or use dropdown selection) triggering automatic history tracking
- **View deal details** showing complete information, related contacts, and stage history
- **Edit deal information** including amount, closing date, and assigned owner
- **Delete deals** with soft delete support for recovery
- **Track weighted forecasts** showing realistic revenue projections based on stage probability

This chapter combines everything you've learned: controllers, policies, eager loading, complex forms, and advanced React UI patterns. You're building the centerpiece of a professional CRM system.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 15](/series/build-crm-laravel-12/chapters/15-deals-module-database-pipeline-design) with Deal model and pipeline stages configured
- ✅ Completed [Chapter 14](/series/build-crm-laravel-12/chapters/14-companies-module-crud-operations) to understand CRUD patterns
- ✅ Completed [Chapter 09](/series/build-crm-laravel-12/chapters/09-authorization-access-control) to understand authorization policies
- ✅ Laravel Sail running with all containers active
- ✅ Database migrations applied with deals, pipeline_stages, and relationships populated
- ✅ Basic understanding of Laravel controllers, policies, Inertia.js, and React hooks
- ✅ Familiarity with React drag-and-drop libraries (we'll use dnd-kit) or select-based alternatives

**Estimated Time**: ~140 minutes (includes controllers, policies, routes, Kanban board UI, drag-and-drop, deal forms, and stage history tracking)

**Verify your setup:**

```bash
# Navigate to your project
cd crm-app

# Verify Sail is running
sail ps  # Should show: laravel.test, mysql, redis all "Up"

# Verify Deal model and relationships work
sail artisan tinker
$deal = App\Models\Deal::first();
echo $deal->title;
echo $deal->company->name;
echo $deal->stage->name;
$deal->stageHistory()->count();  # Should work
exit
```

## What You'll Build

By the end of this chapter, you will have:

**Backend Controllers & Security**:

- ✅ **DealController** with resourceful CRUD methods plus `updateStage()` for transitions
- ✅ **DealRequest FormRequest** validating deal data with team-specific rules
- ✅ **DealPolicy** authorizing all actions at the team level
- ✅ **PipelineController** serving Kanban board data with deals grouped by stage
- ✅ **Stage transition logic** creating immutable history records on every move
- ✅ **Weighted forecast calculations** showing realistic revenue projections
- ✅ **Team-scoped queries** ensuring users only see their team's deals

**Frontend Views (React/Inertia)**:

- ✅ **Pipeline Kanban Board** displaying deals in columns by stage
- ✅ **Drag-and-drop support** using @dnd-kit/core for smooth transitions
- ✅ **Deal cards** showing title, company, amount, closing date, and owner
- ✅ **Deals Index** with list view, filters, and search
- ✅ **Deal Show** displaying complete details with stage history timeline
- ✅ **Deal Create/Edit** form linking to companies and contacts
- ✅ **Stage history component** showing who moved the deal when and why

**Integration**:

- ✅ **Resourceful routes** for deals using `Route::resource()`
- ✅ **Custom route** for stage updates: `PATCH /deals/{deal}/stage`
- ✅ **Authorization checks** on every action ensuring team isolation
- ✅ **Eager loading** preventing N+1 queries when loading deals with relationships
- ✅ **Real-time UI updates** (optimistic updates for smooth UX)
- ✅ **Complete working deals management** ready for Chapter 17

## Quick Start

Want to see it working in 5 minutes? Here's the end result:

```bash
# After completing this chapter:

# 1. View the pipeline board
open http://localhost/pipeline

# 2. Create a new deal
# Click "New Deal" → Fill form → Save
# Expected: Deal appears in "New" column

# 3. Drag deal to "In Progress"
# Drag card from "New" to "In Progress" column
# Expected: Card moves, history record created

# 4. View deal details
open http://localhost/deals/1
# Expected: Full details, stage history timeline

# 5. Check weighted forecast
# Pipeline board shows: Total value, weighted forecast by stage
# Expected: Accurate revenue projections
```

## Objectives

By completing this chapter, you will:

- **Build a DealController** with full CRUD operations and stage update endpoint
- **Create a Deal policy** enforcing team-based authorization on all actions
- **Implement FormRequest validation** with custom rules for deal data
- **Design a Kanban pipeline board** in React displaying deals grouped by stage
- **Add drag-and-drop functionality** using @dnd-kit/core for smooth interactions
- **Track stage transitions** creating immutable history records automatically
- **Calculate weighted forecasts** showing realistic revenue based on stage probability
- **Master relational forms** linking deals to companies, contacts, and users

## Step 1: Generate Deal Controller and Policy (~15 min)

### Goal

Create the Laravel controller handling all deal operations and the policy enforcing team-level authorization.

### Actions

1. **Generate controller and policy**:

```bash
# Generate resourceful controller
sail artisan make:controller DealController --resource

# Generate policy
sail artisan make:policy DealPolicy --model=Deal
```

2. **Build DealController** (`app/Http/Controllers/DealController.php`):

```php
<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Company;
use App\Models\Contact;
use App\Models\PipelineStage;
use App\Models\User;
use App\Http\Requests\DealRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DealController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Deal::class, 'deal');
    }

    public function index(Request $request)
    {
        $query = Deal::query()
            ->with(['company', 'stage', 'owner', 'primaryContact'])
            ->where('team_id', $request->user()->currentTeam->id);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by stage
        if ($stageId = $request->input('stage_id')) {
            $query->where('pipeline_stage_id', $stageId);
        }

        // Filter by owner
        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $deals = $query->paginate(25)->withQueryString();

        return Inertia::render('Deals/Index', [
            'deals' => $deals,
            'filters' => $request->only(['search', 'stage_id', 'owner_id', 'sort', 'direction']),
            'stages' => PipelineStage::all(),
            'users' => User::where('current_team_id', $request->user()->currentTeam->id)->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Deals/Create', [
            'companies' => Company::where('team_id', auth()->user()->currentTeam->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'contacts' => Contact::where('team_id', auth()->user()->currentTeam->id)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
            'stages' => PipelineStage::all(),
            'users' => User::where('current_team_id', auth()->user()->currentTeam->id)
                ->get(['id', 'name']),
        ]);
    }

    public function store(DealRequest $request)
    {
        $deal = Deal::create([
            ...$request->validated(),
            'team_id' => $request->user()->currentTeam->id,
            'owner_id' => $request->input('owner_id', $request->user()->id),
        ]);

        // Create initial stage history
        $deal->stageHistory()->create([
            'pipeline_stage_id' => $deal->pipeline_stage_id,
            'user_id' => $request->user()->id,
            'notes' => 'Deal created',
        ]);

        return redirect()
            ->route('deals.show', $deal)
            ->with('success', 'Deal created successfully.');
    }

    public function show(Deal $deal)
    {
        $deal->load([
            'company',
            'primaryContact',
            'stage',
            'owner',
            'stageHistory.user',
            'stageHistory.stage',
        ]);

        return Inertia::render('Deals/Show', [
            'deal' => $deal,
        ]);
    }

    public function edit(Deal $deal)
    {
        return Inertia::render('Deals/Edit', [
            'deal' => $deal->load('company', 'primaryContact', 'stage', 'owner'),
            'companies' => Company::where('team_id', auth()->user()->currentTeam->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'contacts' => Contact::where('team_id', auth()->user()->currentTeam->id)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
            'stages' => PipelineStage::all(),
            'users' => User::where('current_team_id', auth()->user()->currentTeam->id)
                ->get(['id', 'name']),
        ]);
    }

    public function update(DealRequest $request, Deal $deal)
    {
        $deal->update($request->validated());

        return redirect()
            ->route('deals.show', $deal)
            ->with('success', 'Deal updated successfully.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()
            ->route('deals.index')
            ->with('success', 'Deal deleted successfully.');
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $this->authorize('update', $deal);

        $validated = $request->validate([
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStageId = $deal->pipeline_stage_id;

        $deal->update([
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
        ]);

        // Record stage history
        $deal->stageHistory()->create([
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
            'user_id' => $request->user()->id,
            'notes' => $validated['notes'] ?? "Moved from {$oldStageId} to {$validated['pipeline_stage_id']}",
        ]);

        return back()->with('success', 'Deal stage updated.');
    }
}
```

3. **Build DealPolicy** (`app/Policies/DealPolicy.php`):

```php
<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their team's deals
    }

    public function view(User $user, Deal $deal): bool
    {
        return $user->currentTeam->id === $deal->team_id;
    }

    public function create(User $user): bool
    {
        return true; // All team members can create deals
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->currentTeam->id === $deal->team_id;
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->currentTeam->id === $deal->team_id;
    }

    public function restore(User $user, Deal $deal): bool
    {
        return $user->currentTeam->id === $deal->team_id;
    }

    public function forceDelete(User $user, Deal $deal): bool
    {
        return $user->currentTeam->id === $deal->team_id;
    }
}
```

4. **Create DealRequest** for validation:

```bash
sail artisan make:request DealRequest
```

**Build DealRequest** (`app/Http/Requests/DealRequest.php`):

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy handles authorization
    }

    public function rules(): array
    {
        $dealId = $this->route('deal')?->id;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:deals,title,' . $dealId . ',id,team_id,' . $this->user()->currentTeam->id,
            ],
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0|max:999999999.99',
            'company_id' => 'required|exists:companies,id',
            'primary_contact_id' => 'nullable|exists:contacts,id',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'expected_close_date' => 'nullable|date|after_or_equal:today',
            'owner_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'A deal with this title already exists in your team.',
            'amount.required' => 'Please enter the deal amount.',
            'company_id.required' => 'Please select a company for this deal.',
            'expected_close_date.after_or_equal' => 'The closing date must be today or in the future.',
        ];
    }
}
```

### Expected Result

```bash
# Test policy and controller
sail artisan tinker
$user = User::first();
$deal = Deal::first();
Gate::authorize('view', $deal);  # Should pass if same team
# Expected: No exception
```

### Why It Works

The **DealController** follows Laravel's resourceful pattern with automatic route model binding and policy authorization via `authorizeResource()`. The custom `updateStage()` method handles stage transitions separately from full updates, allowing drag-and-drop operations without requiring all deal fields.

The **DealPolicy** enforces team-level isolation on every action, ensuring users can only interact with their team's deals. The **DealRequest** validates all input with team-scoped uniqueness rules preventing duplicate deal titles within the same team.

The controller uses **eager loading** (`with()`) to prevent N+1 queries when loading deals with relationships, improving performance significantly when displaying lists of deals.

### Troubleshooting

- **Error: "Call to undefined method authorizeResource()"** — Ensure the controller extends `App\Http\Controllers\Controller` base class
- **Policy not enforced** — Run `sail artisan optimize:clear` to clear cached policies
- **Unique validation fails** — Verify `team_id` column exists on deals table and matches current team

## Step 2: Create Pipeline Board Controller (~10 min)

### Goal

Build a dedicated controller serving the Kanban board with deals grouped by stage.

### Actions

1. **Generate controller**:

```bash
sail artisan make:controller PipelineController
```

2. **Build PipelineController** (`app/Http/Controllers/PipelineController.php`):

```php
<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $teamId = $request->user()->currentTeam->id;

        // Get all stages with deals
        $stages = PipelineStage::with([
            'deals' => function ($query) use ($teamId) {
                $query->where('team_id', $teamId)
                    ->with(['company', 'owner', 'primaryContact'])
                    ->orderBy('expected_close_date');
            }
        ])->orderBy('order')->get();

        // Calculate metrics
        $totalValue = Deal::where('team_id', $teamId)->sum('amount');
        $weightedForecast = Deal::where('team_id', $teamId)
            ->join('pipeline_stages', 'deals.pipeline_stage_id', '=', 'pipeline_stages.id')
            ->selectRaw('SUM(deals.amount * pipeline_stages.probability / 100) as forecast')
            ->value('forecast');

        $dealCountByStage = Deal::where('team_id', $teamId)
            ->selectRaw('pipeline_stage_id, COUNT(*) as count')
            ->groupBy('pipeline_stage_id')
            ->pluck('count', 'pipeline_stage_id');

        return Inertia::render('Pipeline/Index', [
            'stages' => $stages,
            'metrics' => [
                'total_value' => $totalValue,
                'weighted_forecast' => round($weightedForecast, 2),
                'deal_count' => Deal::where('team_id', $teamId)->count(),
                'deal_count_by_stage' => $dealCountByStage,
            ],
        ]);
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $this->authorize('update', $deal);

        $validated = $request->validate([
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $oldStage = $deal->stage;
        $newStage = PipelineStage::findOrFail($validated['pipeline_stage_id']);

        $deal->update([
            'pipeline_stage_id' => $newStage->id,
        ]);

        // Create history record
        $deal->stageHistory()->create([
            'pipeline_stage_id' => $newStage->id,
            'user_id' => $request->user()->id,
            'notes' => "Moved from {$oldStage->name} to {$newStage->name}",
        ]);

        return response()->json([
            'message' => 'Deal stage updated successfully',
            'deal' => $deal->load(['company', 'owner', 'stage']),
        ]);
    }
}
```

### Expected Result

```bash
# Test the pipeline endpoint
sail artisan tinker
$response = app()->make('App\Http\Controllers\PipelineController')->index(request());
# Expected: Inertia response with stages and metrics
```

### Why It Works

The **PipelineController** loads all stages with their associated deals in a single query using eager loading. The `deals` relationship is constrained to the current team and ordered by expected close date, ensuring efficient data loading.

The **metrics calculation** uses SQL aggregation to compute total deal value and weighted forecast without loading all deals into memory. The weighted forecast multiplies each deal amount by its stage probability, giving a realistic revenue projection.

The **updateStage** method returns JSON instead of redirecting, making it perfect for AJAX requests from the drag-and-drop interface where we want to update the UI without a full page reload.

### Troubleshooting

- **N+1 query detected** — Verify `with(['company', 'owner', 'primaryContact'])` is present in the deals query
- **Weighted forecast is null** — Ensure pipeline_stages table has probability column with numeric values
- **Authorization fails** — Add `Gate::allows('viewAny', Deal::class)` check before loading data

## Step 3: Register Routes (~5 min)

### Goal

Define routes for deals and pipeline board.

### Actions

1. **Add routes** to `routes/web.php`:

```php
use App\Http\Controllers\DealController;
use App\Http\Controllers\PipelineController;

// Deals resource routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Pipeline board
    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::patch('/deals/{deal}/stage', [PipelineController::class, 'updateStage'])->name('deals.update-stage');

    // Deals CRUD
    Route::resource('deals', DealController::class);
});
```

2. **Test routes**:

```bash
# List all deal routes
sail artisan route:list --name=deals

# Expected output:
# GET    /deals ...................... deals.index
# POST   /deals ...................... deals.store
# GET    /deals/create ............... deals.create
# GET    /deals/{deal} ............... deals.show
# PUT    /deals/{deal} ............... deals.update
# DELETE /deals/{deal} ............... deals.destroy
# GET    /deals/{deal}/edit .......... deals.edit
# PATCH  /deals/{deal}/stage ......... deals.update-stage
# GET    /pipeline ................... pipeline.index
```

### Expected Result

```bash
# Verify routes work
open http://localhost/pipeline
# Expected: Pipeline board loads (after creating views)
```

### Why It Works

**Resourceful routes** via `Route::resource()` automatically generate all seven CRUD routes with correct HTTP methods and route names. The custom `updateStage` route is added separately with a PATCH method, allowing stage updates without requiring all deal fields.

Route names like `deals.index` and `pipeline.index` are used throughout the application for URL generation via `route()` helper, making the code maintainable and refactoring-safe.

### Troubleshooting

- **Route not found** — Run `sail artisan route:clear` and verify route names
- **Middleware not applied** — Ensure routes are within `middleware(['auth', 'verified'])` group
- **PATCH method not working** — Verify forms include `@method('PATCH')` directive

## Step 4: Build Deals Index View (~20 min)

### Goal

Create a React/Inertia view listing all deals with search, filters, and sorting.

### Actions

1. **Create Deals Index** (`resources/js/Pages/Deals/Index.tsx`):

```tsx
import React, { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Select } from "@/Components/ui/select";
import { Badge } from "@/Components/ui/badge";
import { formatCurrency, formatDate } from "@/lib/utils";

interface Deal {
  id: number;
  title: string;
  amount: number;
  expected_close_date: string | null;
  company: { id: number; name: string };
  stage: { id: number; name: string; color: string };
  owner: { id: number; name: string } | null;
  primary_contact: { id: number; first_name: string; last_name: string } | null;
}

interface Props {
  deals: {
    data: Deal[];
    links: any[];
    meta: any;
  };
  filters: {
    search?: string;
    stage_id?: number;
    owner_id?: number;
    sort?: string;
    direction?: string;
  };
  stages: Array<{ id: number; name: string }>;
  users: Array<{ id: number; name: string }>;
}

export default function Index({ deals, filters, stages, users }: Props) {
  const [search, setSearch] = useState(filters.search || "");

  const handleFilter = (key: string, value: string) => {
    router.get(
      route("deals.index"),
      {
        ...filters,
        [key]: value,
      },
      {
        preserveState: true,
        replace: true,
      }
    );
  };

  const handleSort = (field: string) => {
    const direction =
      filters.sort === field && filters.direction === "asc" ? "desc" : "asc";
    router.get(
      route("deals.index"),
      {
        ...filters,
        sort: field,
        direction,
      },
      {
        preserveState: true,
        replace: true,
      }
    );
  };

  return (
    <AuthenticatedLayout
      header={
        <div className="flex justify-between items-center">
          <h2 className="font-semibold text-xl text-gray-800 leading-tight">
            Deals
          </h2>
          <div className="flex gap-2">
            <Link href={route("pipeline.index")}>
              <Button variant="outline">Pipeline Board</Button>
            </Link>
            <Link href={route("deals.create")}>
              <Button>New Deal</Button>
            </Link>
          </div>
        </div>
      }
    >
      <Head title="Deals" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          {/* Filters */}
          <div className="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <Input
                placeholder="Search deals..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                onKeyUp={(e) =>
                  e.key === "Enter" && handleFilter("search", search)
                }
              />
              <Select
                value={filters.stage_id?.toString()}
                onValueChange={(value) => handleFilter("stage_id", value)}
              >
                <option value="">All Stages</option>
                {stages.map((stage) => (
                  <option key={stage.id} value={stage.id}>
                    {stage.name}
                  </option>
                ))}
              </Select>
              <Select
                value={filters.owner_id?.toString()}
                onValueChange={(value) => handleFilter("owner_id", value)}
              >
                <option value="">All Owners</option>
                {users.map((user) => (
                  <option key={user.id} value={user.id}>
                    {user.name}
                  </option>
                ))}
              </Select>
              <Button
                variant="outline"
                onClick={() => router.get(route("deals.index"))}
              >
                Clear Filters
              </Button>
            </div>
          </div>

          {/* Deals Table */}
          <div className="bg-white shadow-sm rounded-lg overflow-hidden">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th
                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSort("title")}
                  >
                    Title
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Company
                  </th>
                  <th
                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSort("amount")}
                  >
                    Amount
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stage
                  </th>
                  <th
                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSort("expected_close_date")}
                  >
                    Close Date
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Owner
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {deals.data.map((deal) => (
                  <tr key={deal.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Link
                        href={route("deals.show", deal.id)}
                        className="text-blue-600 hover:text-blue-900 font-medium"
                      >
                        {deal.title}
                      </Link>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Link
                        href={route("companies.show", deal.company.id)}
                        className="text-gray-600 hover:text-gray-900"
                      >
                        {deal.company.name}
                      </Link>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap font-semibold">
                      {formatCurrency(deal.amount)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Badge style={{ backgroundColor: deal.stage.color }}>
                        {deal.stage.name}
                      </Badge>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {deal.expected_close_date
                        ? formatDate(deal.expected_close_date)
                        : "Not set"}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {deal.owner?.name || "Unassigned"}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link
                        href={route("deals.edit", deal.id)}
                        className="text-indigo-600 hover:text-indigo-900 mr-3"
                      >
                        Edit
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>

            {/* Pagination */}
            <div className="px-6 py-4 border-t">
              {/* Add pagination component here */}
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
```

2. **Add utility functions** (`resources/js/lib/utils.ts`):

```typescript
export function formatCurrency(amount: number): string {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(amount);
}

export function formatDate(date: string): string {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}
```

### Expected Result

```bash
# Visit deals index
open http://localhost/deals

# Expected:
# - Table listing all deals
# - Search bar filtering by title/company
# - Dropdowns filtering by stage/owner
# - Sortable columns
# - Links to pipeline board and create deal
```

### Why It Works

The **Deals Index** uses Inertia's `router.get()` for client-side navigation with `preserveState: true`, keeping filter values in the URL as query parameters. This makes filters bookmarkable and allows users to share filtered views.

**Sorting** toggles between ascending and descending by checking if the current sort field matches the clicked column. The UI shows visual feedback via cursor-pointer on sortable headers.

**Links** use Inertia's `<Link>` component for instant navigation without full page reloads, providing a SPA-like experience while maintaining server-side rendering benefits.

### Troubleshooting

- **TypeScript errors** — Install types: `npm install @types/react --save-dev`
- **Badge component missing** — Install shadcn/ui badge: `npx shadcn-ui@latest add badge`
- **formatCurrency undefined** — Verify utils.ts file exists and exports functions

## Step 5: Build Pipeline Kanban Board (~30 min)

### Goal

Create a visual Kanban board displaying deals grouped by stage with drag-and-drop support.

### Actions

1. **Install drag-and-drop library**:

```bash
# Using dnd-kit (modern, accessible drag-and-drop)
npm install @dnd-kit/core @dnd-kit/sortable @dnd-kit/utilities
```

2. **Create Pipeline Board** (`resources/js/Pages/Pipeline/Index.tsx`):

```tsx
import React, { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button } from "@/Components/ui/button";
import { Card } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import { formatCurrency, formatDate } from "@/lib/utils";
import {
  DndContext,
  DragEndEvent,
  DragOverlay,
  DragStartEvent,
  PointerSensor,
  useSensor,
  useSensors,
} from "@dnd-kit/core";

interface Deal {
  id: number;
  title: string;
  amount: number;
  expected_close_date: string | null;
  company: { id: number; name: string };
  owner: { id: number; name: string } | null;
  primary_contact: { id: number; first_name: string; last_name: string } | null;
}

interface Stage {
  id: number;
  name: string;
  color: string;
  probability: number;
  order: number;
  deals: Deal[];
}

interface Props {
  stages: Stage[];
  metrics: {
    total_value: number;
    weighted_forecast: number;
    deal_count: number;
    deal_count_by_stage: Record<number, number>;
  };
}

export default function PipelineIndex({ stages, metrics }: Props) {
  const [activeDeal, setActiveDeal] = useState<Deal | null>(null);

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 8, // Require 8px movement before drag starts
      },
    })
  );

  const handleDragStart = (event: DragStartEvent) => {
    const dealId = event.active.id as number;
    const deal = stages
      .flatMap((stage) => stage.deals)
      .find((d) => d.id === dealId);
    setActiveDeal(deal || null);
  };

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;

    if (!over) {
      setActiveDeal(null);
      return;
    }

    const dealId = active.id as number;
    const newStageId = parseInt(over.id as string);

    // Find current stage
    const currentStage = stages.find((stage) =>
      stage.deals.some((deal) => deal.id === dealId)
    );

    if (!currentStage || currentStage.id === newStageId) {
      setActiveDeal(null);
      return;
    }

    // Update stage via API
    router.patch(
      route("deals.update-stage", dealId),
      { pipeline_stage_id: newStageId },
      {
        preserveState: true,
        onSuccess: () => {
          // Reload to get updated metrics
          router.reload({ only: ["stages", "metrics"] });
        },
      }
    );

    setActiveDeal(null);
  };

  return (
    <AuthenticatedLayout
      header={
        <div className="flex justify-between items-center">
          <h2 className="font-semibold text-xl text-gray-800 leading-tight">
            Sales Pipeline
          </h2>
          <div className="flex gap-2">
            <Link href={route("deals.index")}>
              <Button variant="outline">List View</Button>
            </Link>
            <Link href={route("deals.create")}>
              <Button>New Deal</Button>
            </Link>
          </div>
        </div>
      }
    >
      <Head title="Sales Pipeline" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          {/* Metrics */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <Card className="p-6">
              <div className="text-sm text-gray-600">Total Pipeline Value</div>
              <div className="text-3xl font-bold text-gray-900 mt-2">
                {formatCurrency(metrics.total_value)}
              </div>
              <div className="text-sm text-gray-500 mt-1">
                {metrics.deal_count} deals
              </div>
            </Card>
            <Card className="p-6">
              <div className="text-sm text-gray-600">Weighted Forecast</div>
              <div className="text-3xl font-bold text-green-600 mt-2">
                {formatCurrency(metrics.weighted_forecast)}
              </div>
              <div className="text-sm text-gray-500 mt-1">
                Based on stage probability
              </div>
            </Card>
            <Card className="p-6">
              <div className="text-sm text-gray-600">Win Rate</div>
              <div className="text-3xl font-bold text-blue-600 mt-2">
                {(
                  (metrics.weighted_forecast / metrics.total_value) *
                  100
                ).toFixed(1)}
                %
              </div>
              <div className="text-sm text-gray-500 mt-1">
                Probability-weighted
              </div>
            </Card>
          </div>

          {/* Kanban Board */}
          <DndContext
            sensors={sensors}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
          >
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {stages.map((stage) => (
                <StageColumn key={stage.id} stage={stage} metrics={metrics} />
              ))}
            </div>

            <DragOverlay>
              {activeDeal && <DealCard deal={activeDeal} isDragging />}
            </DragOverlay>
          </DndContext>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

// Stage Column Component
function StageColumn({ stage, metrics }: { stage: Stage; metrics: any }) {
  const { useDroppable } = require("@dnd-kit/core");
  const { setNodeRef } = useDroppable({ id: stage.id });

  const stageValue = stage.deals.reduce((sum, deal) => sum + deal.amount, 0);
  const dealCount = metrics.deal_count_by_stage[stage.id] || 0;

  return (
    <div ref={setNodeRef} className="bg-gray-100 rounded-lg p-4 min-h-[600px]">
      {/* Stage Header */}
      <div className="mb-4">
        <div className="flex items-center justify-between mb-2">
          <h3 className="font-semibold text-gray-900">{stage.name}</h3>
          <Badge style={{ backgroundColor: stage.color }}>{dealCount}</Badge>
        </div>
        <div className="text-sm text-gray-600">
          {formatCurrency(stageValue)}
        </div>
        <div className="text-xs text-gray-500">
          {stage.probability}% probability
        </div>
      </div>

      {/* Deals */}
      <div className="space-y-3">
        {stage.deals.map((deal) => (
          <DealCard key={deal.id} deal={deal} />
        ))}
      </div>
    </div>
  );
}

// Deal Card Component
function DealCard({
  deal,
  isDragging = false,
}: {
  deal: Deal;
  isDragging?: boolean;
}) {
  const { useDraggable } = require("@dnd-kit/core");
  const { attributes, listeners, setNodeRef, transform } = useDraggable({
    id: deal.id,
  });

  const style = transform
    ? {
        transform: `translate3d(${transform.x}px, ${transform.y}px, 0)`,
      }
    : undefined;

  return (
    <Card
      ref={setNodeRef}
      style={style}
      {...listeners}
      {...attributes}
      className={`p-4 cursor-move hover:shadow-md transition-shadow ${
        isDragging ? "opacity-50" : ""
      }`}
    >
      <Link href={route("deals.show", deal.id)} className="block">
        <h4 className="font-semibold text-gray-900 mb-2 hover:text-blue-600">
          {deal.title}
        </h4>
        <div className="text-sm text-gray-600 mb-2">{deal.company.name}</div>
        <div className="text-lg font-bold text-green-600 mb-2">
          {formatCurrency(deal.amount)}
        </div>
        {deal.expected_close_date && (
          <div className="text-xs text-gray-500">
            Close: {formatDate(deal.expected_close_date)}
          </div>
        )}
        {deal.owner && (
          <div className="text-xs text-gray-500 mt-1">
            Owner: {deal.owner.name}
          </div>
        )}
      </Link>
    </Card>
  );
}
```

3. **Build assets**:

```bash
npm run build
```

### Expected Result

```bash
# Visit pipeline board
open http://localhost/pipeline

# Expected:
# - 4 columns (New, In Progress, Won, Lost)
# - Deal cards in each column
# - Drag cards between columns
# - Metrics at top showing total value and forecast
# - Cards link to deal details
```

### Why It Works

**@dnd-kit** provides accessible, performant drag-and-drop with keyboard support. The `DndContext` wraps the entire board, `useDroppable` makes columns accept drops, and `useDraggable` makes cards draggable.

When a deal is dropped, `handleDragEnd` sends a PATCH request to `deals.update-stage`, updating the database and creating a history record. The `router.reload()` fetches fresh data, ensuring the UI stays in sync.

**Optimistic updates** could be added by immediately moving the card in state before the API call, then rolling back on error. This makes the UI feel instant while maintaining data integrity.

### Troubleshooting

- **Cards not dragging** — Verify `cursor-move` class is present and `listeners` props are spread correctly
- **Drop not working** — Ensure `setNodeRef` is attached to the column container
- **TypeScript errors** — Run `npm install @types/react @types/react-dom --save-dev`

## Step 6: Create Deal Form Views (~25 min)

### Goal

Build Create and Edit forms for deals with company/contact selection.

### Actions

1. **Create Deal Create Form** (`resources/js/Pages/Deals/Create.tsx`):

```tsx
import React from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Select } from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";

interface Props {
  companies: Array<{ id: number; name: string }>;
  contacts: Array<{ id: number; first_name: string; last_name: string }>;
  stages: Array<{ id: number; name: string }>;
  users: Array<{ id: number; name: string }>;
}

export default function Create({ companies, contacts, stages, users }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    title: "",
    description: "",
    amount: "",
    company_id: "",
    primary_contact_id: "",
    pipeline_stage_id: stages[0]?.id.toString() || "",
    expected_close_date: "",
    owner_id: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route("deals.store"));
  };

  return (
    <AuthenticatedLayout
      header={
        <h2 className="font-semibold text-xl text-gray-800 leading-tight">
          Create New Deal
        </h2>
      }
    >
      <Head title="Create Deal" />

      <div className="py-12">
        <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white shadow-sm rounded-lg p-6">
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Title */}
              <div>
                <Label htmlFor="title">Deal Title *</Label>
                <Input
                  id="title"
                  value={data.title}
                  onChange={(e) => setData("title", e.target.value)}
                  placeholder="e.g., Enterprise Software License"
                  className={errors.title ? "border-red-500" : ""}
                />
                {errors.title && (
                  <p className="text-sm text-red-600 mt-1">{errors.title}</p>
                )}
              </div>

              {/* Description */}
              <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={data.description}
                  onChange={(e) => setData("description", e.target.value)}
                  placeholder="Deal details and notes..."
                  rows={4}
                />
              </div>

              {/* Amount */}
              <div>
                <Label htmlFor="amount">Deal Amount *</Label>
                <Input
                  id="amount"
                  type="number"
                  step="0.01"
                  value={data.amount}
                  onChange={(e) => setData("amount", e.target.value)}
                  placeholder="0.00"
                  className={errors.amount ? "border-red-500" : ""}
                />
                {errors.amount && (
                  <p className="text-sm text-red-600 mt-1">{errors.amount}</p>
                )}
              </div>

              {/* Company */}
              <div>
                <Label htmlFor="company_id">Company *</Label>
                <Select
                  id="company_id"
                  value={data.company_id}
                  onValueChange={(value) => setData("company_id", value)}
                  className={errors.company_id ? "border-red-500" : ""}
                >
                  <option value="">Select a company</option>
                  {companies.map((company) => (
                    <option key={company.id} value={company.id}>
                      {company.name}
                    </option>
                  ))}
                </Select>
                {errors.company_id && (
                  <p className="text-sm text-red-600 mt-1">
                    {errors.company_id}
                  </p>
                )}
              </div>

              {/* Primary Contact */}
              <div>
                <Label htmlFor="primary_contact_id">Primary Contact</Label>
                <Select
                  id="primary_contact_id"
                  value={data.primary_contact_id}
                  onValueChange={(value) =>
                    setData("primary_contact_id", value)
                  }
                >
                  <option value="">Select a contact (optional)</option>
                  {contacts.map((contact) => (
                    <option key={contact.id} value={contact.id}>
                      {contact.first_name} {contact.last_name}
                    </option>
                  ))}
                </Select>
              </div>

              {/* Stage */}
              <div>
                <Label htmlFor="pipeline_stage_id">Pipeline Stage *</Label>
                <Select
                  id="pipeline_stage_id"
                  value={data.pipeline_stage_id}
                  onValueChange={(value) => setData("pipeline_stage_id", value)}
                  className={errors.pipeline_stage_id ? "border-red-500" : ""}
                >
                  {stages.map((stage) => (
                    <option key={stage.id} value={stage.id}>
                      {stage.name}
                    </option>
                  ))}
                </Select>
                {errors.pipeline_stage_id && (
                  <p className="text-sm text-red-600 mt-1">
                    {errors.pipeline_stage_id}
                  </p>
                )}
              </div>

              {/* Expected Close Date */}
              <div>
                <Label htmlFor="expected_close_date">Expected Close Date</Label>
                <Input
                  id="expected_close_date"
                  type="date"
                  value={data.expected_close_date}
                  onChange={(e) =>
                    setData("expected_close_date", e.target.value)
                  }
                />
              </div>

              {/* Owner */}
              <div>
                <Label htmlFor="owner_id">Deal Owner</Label>
                <Select
                  id="owner_id"
                  value={data.owner_id}
                  onValueChange={(value) => setData("owner_id", value)}
                >
                  <option value="">Assign to a team member (optional)</option>
                  {users.map((user) => (
                    <option key={user.id} value={user.id}>
                      {user.name}
                    </option>
                  ))}
                </Select>
              </div>

              {/* Actions */}
              <div className="flex items-center justify-end gap-4">
                <Link href={route("deals.index")}>
                  <Button type="button" variant="outline">
                    Cancel
                  </Button>
                </Link>
                <Button type="submit" disabled={processing}>
                  {processing ? "Creating..." : "Create Deal"}
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
```

2. **Create Deal Edit Form** (`resources/js/Pages/Deals/Edit.tsx`):

Same structure as Create.tsx, but use `put()` instead of `post()` and pre-populate form with deal data.

### Expected Result

```bash
# Visit create form
open http://localhost/deals/create

# Expected:
# - Form with all fields
# - Company dropdown populated
# - Stage dropdown with default "New"
# - Validation errors displayed
# - Submit creates deal and redirects
```

### Why It Works

**Inertia's useForm hook** provides form state management, validation error handling, and submission with proper CSRF token handling. The `processing` state disables the submit button during submission, preventing double-clicks.

**Select components** are populated with data passed from the controller, ensuring users can only select valid companies, contacts, and stages from their team.

### Troubleshooting

- **Form not submitting** — Verify `route('deals.store')` matches route name in web.php
- **Validation errors not showing** — Check `errors` object structure matches field names
- **Select dropdowns empty** — Ensure controller passes companies, contacts, stages arrays

## Step 7: Build Deal Show View with Stage History (~15 min)

### Goal

Display complete deal details with a timeline of stage transitions.

### Actions

1. **Create Deal Show View** (`resources/js/Pages/Deals/Show.tsx`):

```tsx
import React from "react";
import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button } from "@/Components/ui/button";
import { Card } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import { formatCurrency, formatDate, formatDateTime } from "@/lib/utils";

interface Deal {
  id: number;
  title: string;
  description: string | null;
  amount: number;
  expected_close_date: string | null;
  company: { id: number; name: string };
  primary_contact: { id: number; first_name: string; last_name: string } | null;
  stage: { id: number; name: string; color: string; probability: number };
  owner: { id: number; name: string } | null;
  stage_history: Array<{
    id: number;
    stage: { id: number; name: string; color: string };
    user: { id: number; name: string };
    notes: string | null;
    created_at: string;
  }>;
  created_at: string;
  updated_at: string;
}

interface Props {
  deal: Deal;
}

export default function Show({ deal }: Props) {
  const weightedValue = (deal.amount * deal.stage.probability) / 100;

  return (
    <AuthenticatedLayout
      header={
        <div className="flex justify-between items-center">
          <h2 className="font-semibold text-xl text-gray-800 leading-tight">
            {deal.title}
          </h2>
          <div className="flex gap-2">
            <Link href={route("deals.edit", deal.id)}>
              <Button variant="outline">Edit</Button>
            </Link>
            <Link href={route("deals.index")}>
              <Button variant="outline">Back to List</Button>
            </Link>
          </div>
        </div>
      }
    >
      <Head title={deal.title} />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Main Details */}
            <div className="lg:col-span-2 space-y-6">
              {/* Deal Info Card */}
              <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Deal Information</h3>
                <dl className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Title</dt>
                    <dd className="mt-1 text-sm text-gray-900">{deal.title}</dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">
                      Amount
                    </dt>
                    <dd className="mt-1 text-lg font-bold text-green-600">
                      {formatCurrency(deal.amount)}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">
                      Company
                    </dt>
                    <dd className="mt-1 text-sm">
                      <Link
                        href={route("companies.show", deal.company.id)}
                        className="text-blue-600 hover:text-blue-900"
                      >
                        {deal.company.name}
                      </Link>
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">
                      Primary Contact
                    </dt>
                    <dd className="mt-1 text-sm">
                      {deal.primary_contact ? (
                        <Link
                          href={route("contacts.show", deal.primary_contact.id)}
                          className="text-blue-600 hover:text-blue-900"
                        >
                          {deal.primary_contact.first_name}{" "}
                          {deal.primary_contact.last_name}
                        </Link>
                      ) : (
                        <span className="text-gray-500">
                          No contact assigned
                        </span>
                      )}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Stage</dt>
                    <dd className="mt-1">
                      <Badge style={{ backgroundColor: deal.stage.color }}>
                        {deal.stage.name}
                      </Badge>
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">
                      Expected Close Date
                    </dt>
                    <dd className="mt-1 text-sm text-gray-900">
                      {deal.expected_close_date
                        ? formatDate(deal.expected_close_date)
                        : "Not set"}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Owner</dt>
                    <dd className="mt-1 text-sm text-gray-900">
                      {deal.owner?.name || "Unassigned"}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">
                      Weighted Value
                    </dt>
                    <dd className="mt-1 text-sm font-semibold text-green-600">
                      {formatCurrency(weightedValue)}
                      <span className="text-gray-500 ml-1">
                        ({deal.stage.probability}%)
                      </span>
                    </dd>
                  </div>
                </dl>

                {deal.description && (
                  <div className="mt-6">
                    <dt className="text-sm font-medium text-gray-500 mb-2">
                      Description
                    </dt>
                    <dd className="text-sm text-gray-900 whitespace-pre-wrap">
                      {deal.description}
                    </dd>
                  </div>
                )}
              </Card>

              {/* Stage History */}
              <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Stage History</h3>
                <div className="space-y-4">
                  {deal.stage_history.map((history, index) => (
                    <div key={history.id} className="flex gap-4">
                      <div className="flex-shrink-0">
                        <div
                          className="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold"
                          style={{ backgroundColor: history.stage.color }}
                        >
                          {index + 1}
                        </div>
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between">
                          <p className="text-sm font-medium text-gray-900">
                            Moved to {history.stage.name}
                          </p>
                          <p className="text-sm text-gray-500">
                            {formatDateTime(history.created_at)}
                          </p>
                        </div>
                        <p className="text-sm text-gray-600">
                          By {history.user.name}
                        </p>
                        {history.notes && (
                          <p className="text-sm text-gray-700 mt-1">
                            {history.notes}
                          </p>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </Card>
            </div>

            {/* Sidebar */}
            <div className="space-y-6">
              {/* Quick Stats */}
              <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Quick Stats</h3>
                <dl className="space-y-3">
                  <div>
                    <dt className="text-sm text-gray-500">Created</dt>
                    <dd className="text-sm font-medium">
                      {formatDateTime(deal.created_at)}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm text-gray-500">Last Updated</dt>
                    <dd className="text-sm font-medium">
                      {formatDateTime(deal.updated_at)}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm text-gray-500">Days in Stage</dt>
                    <dd className="text-sm font-medium">
                      {deal.stage_history.length > 0
                        ? Math.floor(
                            (new Date().getTime() -
                              new Date(
                                deal.stage_history[0].created_at
                              ).getTime()) /
                              (1000 * 60 * 60 * 24)
                          )
                        : 0}{" "}
                      days
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm text-gray-500">Stage Changes</dt>
                    <dd className="text-sm font-medium">
                      {deal.stage_history.length} times
                    </dd>
                  </div>
                </dl>
              </Card>

              {/* Actions */}
              <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Actions</h3>
                <div className="space-y-2">
                  <Link href={route("pipeline.index")} className="block">
                    <Button variant="outline" className="w-full">
                      View in Pipeline
                    </Button>
                  </Link>
                  <Link
                    href={route("companies.show", deal.company.id)}
                    className="block"
                  >
                    <Button variant="outline" className="w-full">
                      View Company
                    </Button>
                  </Link>
                  {deal.primary_contact && (
                    <Link
                      href={route("contacts.show", deal.primary_contact.id)}
                      className="block"
                    >
                      <Button variant="outline" className="w-full">
                        View Contact
                      </Button>
                    </Link>
                  )}
                </div>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
```

2. **Add formatDateTime utility**:

```typescript
// In resources/js/lib/utils.ts
export function formatDateTime(date: string): string {
  return new Date(date).toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}
```

### Expected Result

```bash
# Visit deal show page
open http://localhost/deals/1

# Expected:
# - Complete deal information
# - Stage history timeline
# - Weighted value calculation
# - Links to related company/contact
# - Quick stats showing days in stage
# - Edit button in header
```

### Why It Works

The **Show view** displays all deal data including relationships loaded via eager loading in the controller. The **stage history** section renders a timeline showing every stage transition with who moved it and when.

**Weighted value** is calculated client-side by multiplying the deal amount by the current stage's probability, giving a realistic forecast of expected revenue.

### Troubleshooting

- **Stage history empty** — Verify `stageHistory` relationship is loaded in controller
- **Weighted value NaN** — Ensure `probability` column exists and contains numeric values
- **formatDateTime undefined** — Add function to utils.ts and import

## Step 8: Test Complete Workflow (~10 min)

### Goal

Verify the entire deals system works end-to-end.

### Actions

1. **Test Pipeline Board**:

```bash
# 1. Visit pipeline
open http://localhost/pipeline

# 2. Drag a deal from "New" to "In Progress"
# Expected: Card moves, page reloads, history created

# 3. Check metrics
# Expected: Weighted forecast updates based on new stage probability
```

2. **Test Deal Creation**:

```bash
# 1. Click "New Deal"
open http://localhost/deals/create

# 2. Fill form:
# - Title: "Enterprise Software License"
# - Amount: 50000
# - Company: Select existing
# - Stage: "New"
# - Close Date: Next month

# 3. Submit
# Expected: Deal created, redirects to show page, history record exists
```

3. **Test Deal Show Page**:

```bash
# 1. View deal details
open http://localhost/deals/1

# Expected:
# - All information displayed
# - Stage history shows creation + any moves
# - Weighted value calculated correctly
# - Links to company/contact work
```

4. **Test Authorization**:

```bash
sail artisan tinker
$user = User::find(2);  # Different team
$deal = Deal::first();
Gate::forUser($user)->authorize('view', $deal);
# Expected: AuthorizationException (different team)
```

### Expected Result

✅ **Complete working deals system**:

- Pipeline board displays all deals grouped by stage
- Drag-and-drop moves deals and creates history
- Deal CRUD operations work with validation
- Authorization prevents cross-team access
- Weighted forecasts calculate correctly
- Stage history tracks all transitions

### Why It Works

The complete system integrates controllers, policies, eager loading, form validation, and React UI into a cohesive workflow. **Team-scoped queries** ensure data isolation, **authorization policies** prevent unauthorized access, and **stage history tracking** provides an audit trail for every deal movement.

### Troubleshooting

- **Drag-and-drop not working** — Check browser console for JavaScript errors, verify @dnd-kit installed
- **Authorization fails unexpectedly** — Clear policy cache: `sail artisan optimize:clear`
- **Metrics incorrect** — Verify pipeline_stages.probability column has correct values (0-100)

## Exercises

### Exercise 1: Add Deal Notes

**Goal**: Learn to add supplemental data to existing features

Add a notes section to the Deal Show page allowing team members to add timestamped comments about the deal.

Requirements:

- Create `deal_notes` table with `deal_id`, `user_id`, `note`, `timestamps`
- Add `DealNote` model with relationships
- Create form component on Deal Show page
- Display notes in reverse chronological order
- Show author name and timestamp for each note

**Validation**: Notes should display below stage history with author attribution and cannot be empty

### Exercise 2: Add Stage-Specific Fields

**Goal**: Understand dynamic form fields based on state

Add stage-specific required fields that only show when a deal reaches certain stages.

Requirements:

- When deal moves to "In Progress": Require estimated_hours field
- When deal moves to "Won": Require won_date and contract_number fields
- When deal moves to "Lost": Require lost_reason field
- Validate these fields only when moving to those stages
- Display stage-specific data on Deal Show page

**Validation**: Attempting to move to "Won" without contract number should fail with clear error message

### Exercise 3: Bulk Stage Updates

**Goal**: Practice batch operations and optimistic UI updates

Add ability to select multiple deals and move them all to a new stage at once.

Requirements:

- Add checkboxes to pipeline cards
- Show "Move Selected" button when deals are checked
- Allow selecting target stage from dropdown
- Update all selected deals in one transaction
- Create stage history for each deal
- Show success message with count of deals moved

**Validation**: Moving 3 deals from "New" to "In Progress" should create 3 history records and update 3 deals

## Wrap-up

Congratulations! You've built a professional deals management system with a visual Kanban pipeline board. Your CRM now has:

✅ **Complete Deal CRUD** with team-scoped authorization  
✅ **Visual Kanban pipeline** displaying deals grouped by stage  
✅ **Drag-and-drop functionality** for smooth stage transitions  
✅ **Stage history tracking** creating immutable audit trails  
✅ **Weighted forecasting** showing realistic revenue projections  
✅ **Eager loading** preventing N+1 queries on all views  
✅ **Relational forms** linking deals to companies and contacts  
✅ **Authorization policies** ensuring team data isolation

You've mastered advanced Laravel and React patterns including drag-and-drop interfaces, complex eager loading, and real-time metrics calculation. The Deals module is the heart of any CRM system—you've built it with production-ready code.

**What's next?** In [Chapter 17](/series/build-crm-laravel-12/chapters/17-tasks-module-database-model), you'll design the Tasks module allowing users to create to-do items linked to deals, contacts, and companies. You'll learn about polymorphic relationships enabling tasks to belong to multiple entity types.

<ChapterCheckbox 
  seriesId="build-crm-laravel-12"
  chapterId="16"
  label="Completed: 16: Deals Module - CRUD & Pipeline Interface"
/>

## Further Reading

- [Laravel Authorization Docs](https://laravel.com/docs/12.x/authorization) — Policies, gates, and authorization patterns
- [Inertia.js Manual Visits](https://inertiajs.com/manual-visits) — router.get(), router.post(), preserveState
- [dnd-kit Documentation](https://docs.dndkit.com/) — Comprehensive drag-and-drop library guide
- [React Hook Form](https://react-hook-form.com/) — Alternative form library for complex forms
- [Laravel Query Builder](https://laravel.com/docs/12.x/queries) — Aggregation, grouping, and complex queries
- [Eloquent Eager Loading](https://laravel.com/docs/12.x/eloquent-relationships#eager-loading) — Preventing N+1 queries
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/) — PHP coding style guide

**Code Samples**: View complete implementations in [`/code/build-crm-laravel-12/chapter-16/`](https://github.com/dalehurley/codewithphp/tree/main/code/build-crm-laravel-12/chapter-16)
