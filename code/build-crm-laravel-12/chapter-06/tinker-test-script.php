/**
 * Tinker Test Script for Chapter 06: Eloquent Models & Relationships
 * 
 * This file contains all the Tinker commands for testing relationships.
 * Copy and paste each section into: sail artisan tinker
 * 
 * Usage: Start Tinker with `sail artisan tinker`, then paste these commands
 */

// ==========================================
// SECTION 1: Create Team and Companies
// ==========================================

$team = Team::create(['name' => 'Sales Team', 'slug' => 'sales']);

$company = $team->companies()->create([
    'name' => 'Tech Innovations Inc',
    'email' => 'info@techinnovations.com',
    'phone' => '555-0100',
    'website' => 'techinnovations.com',
    'industry' => 'Technology',
    'size' => 'medium',
]);

// ==========================================
// SECTION 2: Create Contacts
// ==========================================

$contact1 = $company->contacts()->create([
    'team_id' => $team->id,
    'first_name' => 'Sarah',
    'last_name' => 'Johnson',
    'email' => 'sarah@techinnovations.com',
    'phone' => '555-0101',
    'position' => 'VP Sales',
]);

$contact2 = $company->contacts()->create([
    'team_id' => $team->id,
    'first_name' => 'Mike',
    'last_name' => 'Chen',
    'email' => 'mike@techinnovations.com',
    'phone' => '555-0102',
    'position' => 'CTO',
]);

// ==========================================
// SECTION 3: Create Deal
// ==========================================

$deal = $company->deals()->create([
    'team_id' => $team->id,
    'contact_id' => $contact1->id,
    'title' => 'Enterprise License Agreement',
    'amount' => 50000.00,
    'currency' => 'USD',
    'status' => 'qualified',
    'expected_close_date' => now()->addDays(30),
]);

// ==========================================
// SECTION 4: Create Tasks
// ==========================================

$task1 = $deal->tasks()->create([
    'team_id' => $team->id,
    'title' => 'Send proposal',
    'description' => 'Email enterprise package proposal to Sarah',
    'status' => 'open',
    'due_date' => now()->addDays(2),
]);

$task2 = $deal->tasks()->create([
    'team_id' => $team->id,
    'title' => 'Follow-up call',
    'description' => 'Call Mike to discuss technical requirements',
    'status' => 'open',
    'due_date' => now()->addDays(5),
]);

// ==========================================
// SECTION 5: Test Relationships
// ==========================================

// Test HasMany relationships
$company->contacts()->count();  // Should return: 2
$deal->tasks()->count();  // Should return: 2

// Test BelongsTo relationships
$contact1->company->name;  // Should return: "Tech Innovations Inc"
$task1->deal->title;  // Should return: "Enterprise License Agreement"

// Test Accessors
$contact1->full_name;  // Should return: "Sarah Johnson"

// Test Enum casting
$deal->status;  // Returns DealStatus enum
$deal->status->label();  // Should return: "Qualified"
$deal->status === 'qualified';  // True (can compare as string)

// ==========================================
// SECTION 6: Test Eager Loading
// ==========================================

$companies = Company::with('contacts', 'deals')->get();
// Now accessing $company->contacts and $company->deals won't hit database again

// ==========================================
// SECTION 7: Test Query Scopes
// ==========================================

$openTasks = Task::open()->get();  // Tasks with status open or in_progress
$completedTasks = Task::completed()->get();  // Tasks with status completed

// Create more tasks to test scopes
Task::create([
    'team_id' => $team->id,
    'title' => 'Task 1',
    'status' => 'open',
    'due_date' => now(),
]);

Task::create([
    'team_id' => $team->id,
    'title' => 'Task 2',
    'status' => 'in_progress',
    'due_date' => now(),
]);

Task::create([
    'team_id' => $team->id,
    'title' => 'Task 3',
    'status' => 'completed',
    'due_date' => now(),
]);

Task::create([
    'team_id' => $team->id,
    'title' => 'Task 4',
    'status' => 'cancelled',
    'due_date' => now(),
]);

Task::open()->count();  // Should return: 2 (open + in_progress)
Task::completed()->count();  // Should return: 1

// ==========================================
// SECTION 8: Test Deep Relationships
// ==========================================

$task1->deal->company->name;  // Should return: "Tech Innovations Inc" (3 levels deep)

// ==========================================
// SECTION 9: Practical Queries
// ==========================================

// Find all tasks for a company's deals
$company = Company::first();
$deals = $company->deals()->with('tasks')->get();
$openTasksForCompany = $deals->flatMap(fn($deal) => $deal->tasks()->open()->get());

// Find all open tasks due today or earlier
$overdueTasks = Task::where('due_date', '<=', now()->toDateString())
                     ->open()
                     ->get();

// Get company with all related data (eager load everything)
$company = Company::with('team', 'contacts', 'deals.tasks', 'deals.contact')
                   ->first();

// ==========================================
// SECTION 10: Exit Tinker
// ==========================================

exit





