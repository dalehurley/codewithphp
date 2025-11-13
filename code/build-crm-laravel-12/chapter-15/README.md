# Chapter 15: Deals Module - Database & Pipeline Design

Code samples and reference implementations for designing the sales pipeline and deals database schema.

## Files in This Directory

### Migrations

- **`migration-pipeline-stages.php`** - Creates `pipeline_stages` reference table with stage definitions, probabilities, and configuration
- **`migration-deals.php`** - Creates `deals` table with stage, amount, probability, closing date, and relationships
- **`migration-deal-contact-role.php`** (see chapter) - Junction table linking deals to multiple contacts with roles
- **`migration-deal-line-items.php`** (see chapter) - Tracks products/services quoted in deals
- **`migration-deal-stage-history.php`** (see chapter) - Immutable audit trail of stage transitions

### Seeders

- **`seeder-pipeline-stages.php`** - Seeds four foundational stages (New, In Progress, Won, Lost) with probabilities and colors

### Models

- **`Deal.php`** - Core Deal model with team scoping, relationships, computed properties, and query scopes
- **`PipelineStage.php`** - Stage configuration model with ordered scopes
- **`DealStageHistory.php`** - Immutable history model with update/delete protection
- **`DealLineItem.php`** (see chapter) - Line item model with computed totals

### Factories

- **`DealFactory.php`** - Test data factory with stage modifiers (won, lost, inStage), high-value deals, and automatic history creation

### Testing

- **`test-deals-schema.php`** - Comprehensive test script validating schema, relationships, and computed values

## Key Architectural Decisions

### 1. Weighted Revenue Forecasting

Deals use **weighted forecasting** where:
```
Forecasted Revenue = Deal Amount × Probability
```

**Critical**: Probability is **not user-editable**. It's a fixed attribute tied to the pipeline stage configuration. When a deal moves stages, probability updates automatically.

**Why?** Manual probability entry introduces bias and destroys forecast reliability. Fixed probabilities based on historical conversion rates produce trustworthy projections.

### 2. Computed Columns

```php
// In migration:
$table->decimal('weighted_amount', 18, 2)->storedAs('amount * probability');
```

MySQL automatically calculates weighted amount:
- ✅ Always accurate (impossible to have mismatched values)
- ✅ Can be indexed and queried efficiently
- ✅ No application logic required

### 3. Denormalized Probability

`deals.probability` is copied from `pipeline_stages.probability` when the stage changes.

**Benefits**:
- ✅ Speeds up forecasting queries (no join required)
- ✅ Maintains historical accuracy (if stage probabilities change, old deals retain original values)

**Tradeoff**: Requires update logic to keep in sync (enforced in Chapter 16's controller)

### 4. Temporal Data Modeling

The `deal_stage_history` table captures every stage transition:

```
DealID | OldStage | NewStage    | TransitionDate     | Comment
-------|----------|-------------|--------------------|-------------------
42     | NULL     | New         | 2025-01-15 09:00   | "Inbound lead"
42     | New      | In Progress | 2025-01-18 14:30   | "Qualified"
42     | In Progress | Won      | 2025-02-01 16:45   | "Contract signed"
```

Enables velocity metrics:
- **Time in stage**: How long deals stay in each phase
- **Sales cycle length**: Time from creation to close
- **Stage regression detection**: Deals moving backwards

### 5. Many-to-Many Contact Roles

Enterprise B2B sales involve multiple decision-makers:

```php
Deal #42 → Contact #101 (Decision Maker, is_primary=true)
         → Contact #102 (Technical Evaluator)
         → Contact #103 (Champion)
```

The `deal_contact_role` junction table captures:
- **Role**: Decision Maker, Technical Evaluator, Champion, Influencer
- **is_primary**: Identifies main contact for communications

### 6. Financial Data Integrity

`deals.amount` should equal the sum of line items:

```php
// Application-level recalculation (Chapter 16):
$deal->amount = $deal->lineItems()->sum('line_total');
$deal->save();
```

Prevents data drift where manually-entered amounts don't match quoted products.

## Running the Test Script

```bash
# Ensure database is migrated and seeded
sail artisan migrate:fresh --seed

# Run comprehensive schema test
sail artisan tinker < code/build-crm-laravel-12/chapter-15/test-deals-schema.php
```

## Using the Deal Factory

```bash
sail artisan tinker

# Basic deal creation
$deal = App\Models\Deal::factory()->create();

# Create 10 deals for a specific team
$team = App\Models\Team::first();
$deals = App\Models\Deal::factory(10)->forTeam($team)->create();

# Create deals in specific stages
$newDeal = App\Models\Deal::factory()->inStage('New')->create();
$progressDeal = App\Models\Deal::factory()->inStage('In Progress')->create();

# Create won/lost deals
$wonDeal = App\Models\Deal::factory()->won()->create();
$lostDeal = App\Models\Deal::factory()->lost()->create();

# Chain modifiers for complex scenarios
$bigDeal = App\Models\Deal::factory()
    ->highValue()
    ->won()
    ->forCompany($company)
    ->create();

exit
```

**Key Features**:
- Automatically creates stage history on deal creation
- Realistic deal names (e.g., "Enterprise License Deal - Acme Corp")
- Stage-specific modifiers ensure probability matches stage
- Won/lost deals automatically set closed_at and is_won flags

Expected output validates:
1. ✅ Pipeline stages load with correct probabilities
2. ✅ Deal creation with all relationships
3. ✅ Weighted amount computes automatically
4. ✅ Stage history captures transitions
5. ✅ Contact roles attach properly
6. ✅ Line items calculate with discounts
7. ✅ Stage transitions update probability
8. ✅ Computed properties work correctly

## Database Schema Overview

```
pipeline_stages
├── id
├── pipeline_name (default: 'Sales Pipeline')
├── stage_name (e.g., "New", "In Progress")
├── probability (0.00 to 1.00)
├── stage_type (open, closed_won, closed_lost)
├── sort_order (Kanban column sequence)
├── wip_limit (Work-in-progress constraint)
└── color (Hex code for UI)

deals
├── id
├── team_id → teams
├── company_id → companies
├── pipeline_stage_id → pipeline_stages
├── owner_id → users
├── name
├── amount
├── probability (denormalized)
├── weighted_amount (computed: amount × probability)
├── closing_date
├── closed_at
├── lead_source
├── description
├── is_won
└── timestamps + soft deletes

deal_contact_role
├── id
├── deal_id → deals
├── contact_id → contacts
├── role (string: "Decision Maker", etc.)
└── is_primary (boolean)

deal_line_items
├── id
├── deal_id → deals
├── product_name
├── description
├── quantity
├── unit_price
├── discount_rate
└── line_total (computed: quantity × unit_price × (1 - discount_rate))

deal_stage_history
├── id
├── deal_id → deals
├── old_stage_id → pipeline_stages (nullable)
├── new_stage_id → pipeline_stages
├── modified_by_user_id → users
├── transition_date
└── comment
```

## Strategic Indexes

### Deals Table

```php
// Kanban board query (team view)
$table->index(['team_id', 'pipeline_stage_id']);

// Sales rep pipeline view
$table->index(['owner_id', 'pipeline_stage_id']);

// Forecasting queries
$table->index('closing_date');
```

### Deal Stage History

```php
// Temporal queries (time-in-stage calculations)
$table->index(['deal_id', 'transition_date']);
```

These composite indexes optimize the two primary query patterns:
1. **Kanban board**: "Show all deals in each stage for my team"
2. **Velocity analysis**: "Calculate time spent in each stage per deal"

## Weighted Forecasting Example

```php
// Total pipeline value (unweighted)
$totalPipeline = Deal::open()->sum('amount');
// $500,000

// Weighted forecast (realistic projection)
$weightedForecast = Deal::open()->sum('weighted_amount');
// $200,000 (40% of pipeline)

// Example deal breakdown:
// - $100K deal at "New" (10%): $10K weighted
// - $200K deal at "In Progress" (50%): $100K weighted
// - $200K deal at "In Progress" (50%): $100K weighted
// Total: $500K pipeline, $210K weighted
```

## Velocity Metrics Queries

### Time in Stage

```sql
-- Calculate average days in each stage
SELECT 
    old_stage.stage_name,
    AVG(DATEDIFF(h2.transition_date, h1.transition_date)) AS avg_days
FROM deal_stage_history h1
JOIN deal_stage_history h2 ON h1.deal_id = h2.deal_id 
    AND h2.transition_date = (
        SELECT MIN(transition_date) 
        FROM deal_stage_history 
        WHERE deal_id = h1.deal_id 
        AND transition_date > h1.transition_date
    )
JOIN pipeline_stages old_stage ON h1.new_stage_id = old_stage.id
GROUP BY old_stage.stage_name;
```

### Sales Cycle Length

```sql
-- Average time from creation to close (won deals only)
SELECT AVG(DATEDIFF(
    (SELECT MAX(transition_date) FROM deal_stage_history WHERE deal_id = d.id),
    (SELECT MIN(transition_date) FROM deal_stage_history WHERE deal_id = d.id)
)) AS avg_cycle_days
FROM deals d WHERE is_won = true;
```

## Next Steps

Chapter 16 will implement:
- `DealController` with CRUD operations
- Drag-and-drop Kanban board interface
- Atomic stage transition logic
- Deal detail view with contacts, line items, history
- Authorization policies for team isolation
- Real-time weighted forecast aggregation

## References

- [Laravel Migrations](https://laravel.com/docs/12.x/migrations)
- [Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships)
- [MySQL Computed Columns](https://dev.mysql.com/doc/refman/8.0/en/create-table-generated-columns.html)
- [Sales Pipeline Best Practices](https://www.salesforce.com/resources/articles/sales-pipeline/)
- [Weighted Sales Forecasting](https://www.pipedrive.com/en/blog/weighted-pipeline)
