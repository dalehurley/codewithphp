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

