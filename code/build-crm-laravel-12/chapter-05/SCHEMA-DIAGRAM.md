# CRM Database Schema Diagram

This document provides visual representations of the database schema created in Chapter 05.

## Entity Relationship Diagram (ERD)

```
┌─────────────────────┐         ┌──────────────────┐
│      users          │         │      teams       │
├─────────────────────┤         ├──────────────────┤
│ id (PK)             │         │ id (PK)          │
│ name                │         │ name             │
│ email               │◄────────│ slug (UNIQUE)    │
│ password_hash       │  N    1 │ plan_type (ENUM)│
│ created_at          │         │ created_at       │
│ updated_at          │         │ updated_at       │
└─────────────────────┘         └──────────────────┘
        ▲                               ▲
        │                               │
        │ (Many-to-Many)               │
        │                               │
        └─────────┬──────────────┬──────┘
                  │              │
             ┌────▼──────────────▼──┐
             │    team_user         │
             ├─────────────────────┤
             │ id (PK)             │
             │ team_id (FK)        │
             │ user_id (FK)        │
             │ role (ENUM)         │
             │ joined_at           │
             │ UNIQUE(team_id, user_id)
             └─────────────────────┘

┌──────────────────┐           ┌──────────────────┐
│    companies     │           │    contacts      │
├──────────────────┤           ├──────────────────┤
│ id (PK)          │1         1│ id (PK)          │
│ team_id (FK)────┐│◄─────────┐│ team_id (FK)────┐
│ name             ││          ││ company_id (FK)─┤
│ website (NULL)   ││          ││ first_name       │
│ industry (NULL)  ││          ││ last_name        │
│ employee_count   ││          ││ email (UNIQUE)   │
│ notes (NULL)     ││          ││ phone (NULL)     │
│ created_at       ││          ││ job_title (NULL) │
│ updated_at       ││          ││ notes (NULL)     │
│ deleted_at       ││          ││ created_at       │
└──────────────────┘           ││ updated_at       │
                               ││ deleted_at       │
                               └──────────────────┘

┌──────────────────┐           ┌──────────────────┐
│      deals       │           │      tasks       │
├──────────────────┤           ├──────────────────┤
│ id (PK)          │1    N     │ id (PK)          │
│ team_id (FK)────┐├───────────│ team_id (FK)────┐
│ company_id (FK)─┤           │ deal_id (FK) ◄──┤
│ created_by (FK)─┤           │ contact_id (FK)  │
│ name             │           │ assigned_to (FK) │
│ amount (DECIMAL) │           │ title            │
│ stage (ENUM)     │           │ description      │
│ probability      │           │ priority (ENUM)  │
│ expected_close   │           │ due_date (NULL)  │
│ notes (NULL)     │           │ completed        │
│ created_at       │           │ completed_at     │
│ updated_at       │           │ created_at       │
│ deleted_at       │           │ updated_at       │
└──────────────────┘           │ deleted_at       │
                               └──────────────────┘
```

## Multi-Tenancy Structure

```
Team: "Acme Corp"
├── team_id = 1
├── Members: alice@acme.com, bob@acme.com
│
├─ Companies
│  ├─ Acme Sales (company_id = 1, team_id = 1)
│  │  └─ Contacts
│  │     ├─ John Doe (contact_id = 1, team_id = 1)
│  │     └─ Jane Smith (contact_id = 2, team_id = 1)
│  │  └─ Deals
│  │     └─ Enterprise Software License (deal_id = 1, team_id = 1, stage = proposal)
│  │        └─ Tasks
│  │           ├─ Send proposal (assigned_to = alice, team_id = 1)
│  │           └─ Follow up (assigned_to = bob, team_id = 1)
│  │
│  └─ Other Company (company_id = 2, team_id = 1)
│
└─ Standalone Tasks (not attached to deal/contact)
   └─ Weekly check-in (deal_id = NULL, contact_id = NULL, team_id = 1)

Team: "Beta Inc"
├── team_id = 2
├── Members: charlie@beta.com
│
└─ Companies
   └─ Beta Partners (company_id = 3, team_id = 2)
      └─ Contacts
         └─ Alice Partner (contact_id = 3, team_id = 2)
```

## Foreign Key Relationships

### One-to-Many Relationships

```
Team ──┬──> Companies
       ├──> Contacts
       ├──> Deals
       ├──> Tasks
       └──> Team Members (via team_user pivot)

Company ──┬──> Contacts
          └──> Deals

Deal ──┬──> Tasks
       └──> (created_by) User

Contact ──> Tasks

User ──┬──> Tasks (assigned_to)
       ├──> Deals (created_by)
       └──> Team Members (via team_user pivot)
```

## Many-to-Many Relationship

```
Users ◄─────────┬─────────────► Teams
                │
            team_user
         (Pivot Table)
         
jane@example.com ──┐
                   ├─[team_user]─┬─ Acme Corp (role: owner)
alice@example.com ─┤             ├─ Beta Inc (role: admin)
                   └────┘        └─ Gamma LLC (role: member)
```

## Table Dependency Order

This shows the order migrations must run (dependencies):

```
1. users (no dependencies)
   ↓
2. teams (no dependencies)
   ↓
3. team_user (depends on: teams, users)
   ↓
4-6. companies, contacts, deals, tasks
     (all depend on: teams, users)
     (deals depends on: companies, users)
     (contacts depends on: companies)
     (tasks depends on: contacts, deals, users)
```

## Soft Delete Pattern

Tables with soft deletes create queries that exclude deleted records:

```
Normal Query:
  SELECT * FROM companies WHERE team_id = 1

With Soft Delete:
  SELECT * FROM companies 
  WHERE team_id = 1 
  AND deleted_at IS NULL  -- Automatically excluded

Restore Deleted:
  UPDATE companies 
  SET deleted_at = NULL 
  WHERE id = 5
```

## Index Strategy

### Single Column Indexes (Performance)

```
teams
  └─ PRIMARY KEY (id)

team_user
  ├─ PRIMARY KEY (id)
  ├─ FOREIGN KEY (team_id)
  └─ FOREIGN KEY (user_id)

companies
  ├─ PRIMARY KEY (id)
  ├─ INDEX (team_id)          -- Filter by team
  └─ INDEX (created_at)       -- Sort by date

contacts
  ├─ PRIMARY KEY (id)
  ├─ INDEX (team_id)          -- Filter by team
  ├─ INDEX (company_id)       -- Filter by company
  ├─ INDEX (email)            -- Unique lookup
  └─ INDEX (created_at)       -- Sort by date

deals
  ├─ PRIMARY KEY (id)
  ├─ INDEX (team_id)          -- Filter by team
  ├─ INDEX (company_id)       -- Filter by company
  ├─ INDEX (stage)            -- Pipeline filtering
  └─ INDEX (created_at)       -- Sort by date

tasks
  ├─ PRIMARY KEY (id)
  ├─ INDEX (team_id)          -- Filter by team
  ├─ INDEX (deal_id)          -- Find related tasks
  ├─ INDEX (contact_id)       -- Find related tasks
  ├─ INDEX (assigned_to)      -- User's assignments
  ├─ INDEX (completed)        -- Filter incomplete
  └─ INDEX (due_date)         -- Upcoming tasks
```

### Composite Indexes (Exercise 3)

```
contacts
  └─ INDEX (team_id, email)   -- Find contact in team by email
```

This composite index optimizes queries like:
```sql
SELECT * FROM contacts 
WHERE team_id = 1 AND email = 'john@example.com'
```

## Cascade Delete Behavior

When a record is deleted, related records behave as follows:

```
DELETE Team ID 1:
  ├─ team_user rows with team_id=1 ──► DELETED
  ├─ companies with team_id=1 ──► DELETED
  │  └─ contacts with company_id=* ──► DELETED
  │  └─ deals with company_id=* ──► DELETED
  ├─ contacts with team_id=1 ──► DELETED
  │  └─ tasks with contact_id=* ──► DELETED
  ├─ deals with team_id=1 ──► DELETED
  │  └─ tasks with deal_id=* ──► DELETED
  └─ tasks with team_id=1 ──► DELETED

DELETE Company ID 5:
  ├─ contacts with company_id=5 ──► DELETED
  ├─ deals with company_id=5 ──► DELETED
  │  └─ tasks with deal_id=* ──► DELETED
  └─ (companies soft delete if deleted_at set)

DELETE User ID 10:
  ├─ team_user rows with user_id=10 ──► DELETED
  ├─ tasks with assigned_to=10 ──► assigned_to SET NULL
  └─ deals with created_by=10 ──► DELETED (cascade)
```

## Enum Fields

Restricted value sets prevent invalid data:

```
teams.plan_type
  ├─ 'free'        (Starter plan)
  ├─ 'pro'         (Professional plan)
  └─ 'enterprise'  (Enterprise plan)

team_user.role
  ├─ 'owner'       (Full access)
  ├─ 'admin'       (Administrative access)
  ├─ 'member'      (Standard access)
  └─ 'viewer'      (Read-only access)

deals.stage
  ├─ 'prospecting'     (Initial contact)
  ├─ 'qualified'       (Needs identified)
  ├─ 'proposal'        (Solution proposed)
  ├─ 'negotiation'     (Negotiating terms)
  ├─ 'closed_won'      (Deal won)
  └─ 'closed_lost'     (Deal lost)

tasks.priority
  ├─ 'low'         (Low priority)
  ├─ 'medium'      (Normal priority)
  ├─ 'high'        (Urgent)
  └─ 'critical'    (Blocking issue)
```

## Common Queries

### Find All Contacts in a Team

```sql
SELECT * FROM contacts 
WHERE team_id = 1 
AND deleted_at IS NULL;

-- Uses INDEX (team_id)
-- Automatically excludes soft-deleted records
```

### Find All Open Deals in a Stage

```sql
SELECT d.* FROM deals d
WHERE d.team_id = 1 
AND d.stage = 'proposal'
AND d.deleted_at IS NULL;

-- Uses INDEX (team_id, stage)
-- Efficient stage filtering
```

### Find Tasks Assigned to User

```sql
SELECT t.* FROM tasks t
WHERE t.team_id = 1 
AND t.assigned_to = 10
AND t.completed = false
AND t.due_date <= CURDATE();

-- Uses INDEX (team_id, assigned_to, completed, due_date)
-- Finds urgent pending tasks
```

### Company with All Related Data

```sql
SELECT 
  c.name as company,
  COUNT(DISTINCT ct.id) as contact_count,
  COUNT(DISTINCT d.id) as deal_count,
  SUM(d.amount) as total_value
FROM companies c
LEFT JOIN contacts ct ON c.id = ct.company_id 
  AND ct.deleted_at IS NULL
LEFT JOIN deals d ON c.id = d.company_id 
  AND d.deleted_at IS NULL
WHERE c.team_id = 1 
AND c.deleted_at IS NULL
GROUP BY c.id;

-- Multiple indexes used for optimal performance
```

---

**Schema Version**: 1.0 (Chapter 05)

**Total Tables**: 6

**Total Columns**: 60+

**Total Indexes**: 20+

**Multi-Tenancy**: ✓ Fully Implemented

**Data Isolation**: ✓ Team-Scoped

