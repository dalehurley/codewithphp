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

