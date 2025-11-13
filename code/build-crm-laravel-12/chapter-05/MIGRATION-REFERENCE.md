# Migration Reference - Copy-Paste Code

This file contains the complete migration code for each of the 6 migration files needed for Chapter 05. You can copy these directly into your migration files created with `sail artisan make:migration`.

## 1. Create Teams Table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_teams_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('plan_type', ['free', 'pro', 'enterprise'])->default('free');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
```

## 2. Create Team-User Pivot Table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_team_user_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'member', 'viewer'])->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->unique(['team_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
```

## 3. Create Companies Table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_companies_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->integer('employee_count')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('team_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
```

## 4. Create Contacts Table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_contacts_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('team_id');
            $table->index('company_id');
            $table->index('user_id');
            $table->index('email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
```

## 5. Create Deals Table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_deals_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->enum('stage', [
                'prospecting',
                'qualified',
                'proposal',
                'negotiation',
                'closed_won',
                'closed_lost',
            ])->default('prospecting');
            $table->integer('probability')->default(0); // 0-100%
            $table->date('expected_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('team_id');
            $table->index('company_id');
            $table->index('user_id');
            $table->index('stage');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
```

## 6. Create Tasks Table

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_tasks_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->date('due_date')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('team_id');
            $table->index('deal_id');
            $table->index('contact_id');
            $table->index('assigned_to');
            $table->index(['assigned_to', 'completed']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
```

## Optional: Add Composite Index to Contacts

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_index_team_email_to_contacts_table.php`

This migration is created in Exercise 3 to add a performance optimization:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Composite index for finding contacts within a team by email
            $table->index(['team_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['team_id', 'email']);
        });
    }
};
```

## Quick Workflow

1. **Generate all migrations**:
```bash
sail artisan make:migration create_teams_table
sail artisan make:migration create_team_user_table
sail artisan make:migration create_companies_table
sail artisan make:migration create_contacts_table
sail artisan make:migration create_deals_table
sail artisan make:migration create_tasks_table
```

2. **Copy-paste the code above into each file** - The migration class name and namespace are already generated, just replace the up() and down() methods

3. **Run all migrations**:
```bash
sail artisan migrate
```

4. **Verify**:
```bash
sail artisan migrate:status
sail mysql
SHOW TABLES;
EXIT;
```

## Column Reference

### Teams
- `id` - Primary key
- `name` - Team/organization name
- `slug` - URL-friendly identifier (unique)
- `plan_type` - ENUM: free, pro, enterprise
- `created_at`, `updated_at` - Timestamps

### Team-User
- `id` - Primary key
- `team_id` - Foreign key to teams
- `user_id` - Foreign key to users
- `role` - ENUM: owner, admin, member, viewer
- `joined_at` - When user joined team

### Companies
- `id` - Primary key
- `team_id` - Foreign key (multi-tenancy)
- `name` - Company name
- `website` - Company website URL (nullable)
- `industry` - Industry type (nullable)
- `employee_count` - Number of employees (nullable)
- `notes` - General notes (nullable)
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete marker

### Contacts
- `id` - Primary key
- `team_id` - Foreign key (multi-tenancy)
- `company_id` - Foreign key to companies (nullable)
- `first_name` - Contact first name
- `last_name` - Contact last name
- `email` - Email address (globally unique)
- `phone` - Phone number (nullable)
- `job_title` - Job title (nullable)
- `notes` - General notes (nullable)
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete marker

### Deals
- `id` - Primary key
- `team_id` - Foreign key (multi-tenancy)
- `company_id` - Foreign key to companies
- `created_by` - Foreign key to users (deal creator)
- `name` - Deal/opportunity name
- `amount` - DECIMAL(12,2) deal value
- `stage` - ENUM: prospecting, qualified, proposal, negotiation, closed_won, closed_lost
- `probability` - 0-100 win probability
- `expected_close_date` - Expected close date (nullable)
- `notes` - Deal notes (nullable)
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete marker

### Tasks
- `id` - Primary key
- `team_id` - Foreign key (multi-tenancy)
- `deal_id` - Foreign key to deals (nullable)
- `contact_id` - Foreign key to contacts (nullable)
- `assigned_to` - Foreign key to users (nullable)
- `title` - Task title
- `description` - Task description (nullable)
- `priority` - ENUM: low, medium, high, critical
- `due_date` - Task due date (nullable)
- `completed` - Boolean completion flag
- `completed_at` - When task was completed (nullable)
- `created_at`, `updated_at` - Timestamps
- `deleted_at` - Soft delete marker

---

**All migrations use PHP 8.4 syntax with `declare(strict_types=1)` at the top.**

