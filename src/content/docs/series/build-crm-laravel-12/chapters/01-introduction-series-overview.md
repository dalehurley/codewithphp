---
title: "01: Introduction & Series Overview"
description: "Plan a Laravel 12 CRM with React and teams. See how the course builds contacts, deals, tasks, notifications and a separate API and queue lab."
sidebar:
  label: "01: Introduction & Series Overview"
  order: 1
  badge:
    text: Intermediate
    variant: caution
---
![Introduction & Series Overview](/images/build-crm-laravel-12/chapter-01-introduction-series-overview-hero-full.webp)

# Chapter 01: Introduction & Series Overview

## Overview

This course builds a customer relationship management (CRM) app with Laravel 12. The main project covers teams, contacts, companies, deals, tasks and notifications. A separate lab uses the same CRM domain to teach REST APIs and background jobs without requiring a frontend.

## Quick Checklist: Before You Start

Before diving into this chapter, take 2 minutes to verify you're ready:

- ✓ Do you have PHP 8.6+ installed? (`php --version`)
- ✓ Do you have Composer installed? (`composer --version`)
- ✓ Do you have Node.js 18+ installed? (`node --version`)
- ✓ Is Docker Desktop running on your machine? (`docker --version`)
- ✓ Do you have basic OOP knowledge (classes, inheritance)?
- ✓ Are you prepared for a ~50-hour commitment over the next 2-3 months?

If you answered "no" to any of the first four, read the Prerequisites section below. If "no" to the last question, consider waiting until you have time to dedicate to this series. This series deserves your focus.

## Prerequisites

Before starting this chapter, you should have:

- **PHP 8.3+ installed** (8.6+ is recommended for latest features, but 8.3 works fine)
  - Check your version: `php --version`
  - If you have PHP 8.0-8.2, consider upgrading to 8.3 or 8.6 for best compatibility
  - The setup verifier checks for PHP 8.6 on your host. Laravel 12 supports PHP 8.3, and Sail runs in a separate container.
- **Basic OOP knowledge**: Classes, inheritance, namespaces (PHP fundamentals)
- **Basic web development knowledge**: HTTP, HTML, CSS, how client-server applications work
- **Composer** installed for PHP dependency management
- **Node.js 18+** installed for frontend tooling
- **Docker Desktop** installed and running (for Laravel Sail development environment)
- **Database basics**: Understanding of tables, relationships, SQL queries
- **Terminal/command line** comfort for running commands
- **Curiosity** about modern web frameworks and building real applications

**No prior Laravel experience required** — we'll cover everything from installation to deployment.

### Version Compatibility Note

This series targets **PHP 8.3+**, not 8.6 exclusively. Here's the breakdown:

| PHP Version          | Supported?         | Notes                                                                           |
| -------------------- | ------------------ | ------------------------------------------------------------------------------- |
| **PHP 8.6**          | ✅ Fully           | Recommended. Newest features, all examples work without modification            |
| **PHP 8.3**          | ✅ Fully           | Stable choice. All code works; some modern features (property hooks) may differ |
| **PHP 8.2**          | ⚠️ Partial         | Basic features work; not tested. Consider upgrading                             |
| **PHP 8.1 or older** | ❌ Not recommended | Missing important improvements; will cause compatibility issues                 |

**If you only have PHP 8.2**: The fundamentals in this series will work, but we strongly recommend upgrading to 8.3 or 8.6 for the best experience and to match production environments.

**Estimated Time**: ~20 minutes

**Verify your setup:**

Before proceeding, run these commands to confirm your tools are installed:

```bash
# Check PHP version (should show 8.6 or higher)
php --version

# Check Composer version
composer --version

# Check Node.js version (should show 18 or higher)
node --version

# Confirm Docker Desktop is running
docker --version
```

If any command fails or shows a version below the requirement, install the missing tool before continuing.

## What You'll Build in This Series

The main project builds on Laravel's routing, Eloquent models and authorization. Its published lessons take you from your local setup to notifications.

| Feature | Chapters |
| --- | --- |
| Authentication and team permissions | 07–09 |
| Contacts and companies | 11–14 |
| Deals and tasks | 15–18 |
| Notifications and email | 19 |
| Contact API and Sanctum | 20–21 |
| Background exports and Horizon | 29–30 |

### Tech Stack

**Backend**:

- Laravel 12 (PHP 8.3+)
- MySQL 8.0 (relational database)
- Redis (caching, job queues)

**Frontend**:

- React 19 (UI framework)
- Inertia.js (SPA without separate API)
- Tailwind CSS 4 (styling)
- shadcn/ui (component library)

**Infrastructure**:

- Docker (local development with Laravel Sail)
- Docker Compose (multi-service orchestration)
- GitHub (version control, deployment automation)

See the [course overview](/series/build-crm-laravel-12/) for the full set of available lessons and the separate API and queue lab.

## Objectives

- Understand the CRM project's scope, features, and target users
- Explore the technology stack: Laravel 12, React, Inertia, Tailwind, and supporting tools
- Learn where the React starter kit, Sanctum and Horizon fit
- Choose between the main project and the independent API and queue lab
- Identify prerequisites and ensure you're ready to start building

## The CRM Application: Features & Scope

### What is a CRM?

At its core, a **Customer Relationship Management (CRM)** system is a technology and a strategy for managing all your company's relationships and interactions with customers and potential customers.

For a developer, it's a dynamic, data-driven web application designed to centralize, manage, and analyze customer data. Instead of scattering contacts in spreadsheets, tasks in a notebook, and deals in emails, a CRM provides a single source of truth.

The goal is simple: **improve business relationships to grow the business**. A CRM helps businesses stay connected to customers, streamline processes, and improve profitability. Our application will focus on the **sales** aspect of a CRM, helping users visualize and manage their sales leads from first contact to a closed deal.

### Core Features You'll Implement

This series will guide you through building the most essential features of a modern sales CRM:

- **Contact & Company Management:** The foundational database of your application. You'll build full CRUD (Create, Read, Update, Delete) functionality for contacts and the companies they work for.
- **Deal & Pipeline Management:** The heart of our CRM. You'll create a visual, drag-and-drop "kanban" board that allows users to move "Deals" (potential sales) through different stages (e.g., "New," "Proposal Sent," "Won," "Lost").
- **Task Management:** A "to-do" system integrated with your contacts and deals. Users will be able to create tasks like "Follow up with Jane Doe about the proposal" and receive automated reminders.
- **User & Team Management:** You'll build a robust authentication system and add multi-tenancy, allowing a user (an "owner") to invite team members ("admins" or "members") to collaborate in their account with specific permissions.

### Target Users

The target users for the CRM you'll build are **small businesses, freelancers, and sales teams** who need a simple, visual, and affordable tool to manage their sales process. They are overwhelmed by complex, expensive enterprise solutions (like Salesforce) and just need a clean, efficient way to track their leads and close more deals.

## Technology Stack Deep Dive

We have selected a modern, powerful, and highly productive technology stack. Each piece is chosen to maximize developer experience and application performance.

### Backend: Laravel 12

Laravel 12 serves as the "brain" of our application. It's a PHP framework renowned for its elegant syntax and powerful features. It will handle:

- **Routing:** Directing incoming web requests to the correct controllers.
- **Business Logic:** Handling all data manipulation, calculations, and rules.
- **Database Interaction:** Using its powerful **Eloquent ORM** to query and manage our database with simple, expressive PHP code.
- **Authentication & Authorization:** Securely managing user login, registration, and permissions.
- **API Endpoints:** Providing data to our frontend and external services.

We chose Laravel 12 for its developer-first focus, its batteries-included ecosystem, and its ability to scale from a small project to a large-scale application.

### Frontend: React + Inertia + Tailwind

This is our "TALL-R" stack variant, which provides a seamless, single-page application (SPA) experience without the complexity of a traditional decoupled API.

- **React 19:** A powerful JavaScript library for building dynamic, component-based user interfaces. It will render all our UI, from a simple button to the complex drag-and-drop pipeline.
- **Inertia.js:** This is the magic "glue." Inertia allows our Laravel backend to talk _directly_ to our React frontend. When you click a link, Laravel sends a JSON payload with the new page's data, and Inertia intelligently swaps out the React components. This gives us the speed of an SPA with the simplicity of a traditional server-side-rendered application.
- **Tailwind CSS 4:** A utility-first CSS framework for building custom designs rapidly. Instead of writing separate CSS files, we'll build our UI by applying small, composable utility classes (like `bg-blue-500` or `font-bold`) directly in our React components.
- **shadcn/ui:** This is _not_ a component library. It's a collection of beautifully designed, re-usable UI components (like dialogs, forms, and cards) built with Tailwind and React that we can copy-paste into our project and customize, giving us a professional look from day one.

### Database & Caching: MySQL + Redis

- **MySQL:** Our primary database. It's a time-tested, reliable, and powerful relational database (SQL) that will store all our persistent data: users, contacts, companies, deals, tasks, etc.
- **Redis:** An extremely fast, in-memory key-value store. We'll use it for two critical jobs:
  1. **Caching:** Storing the results of complex database queries to speed up page loads.
  2. **Queues:** Managing our background job list, ensuring tasks like sending thousands of emails don't slow down the user's web request.

### Development Environment: Laravel Sail

To eliminate "it works on my machine" problems, we'll use **Laravel Sail**. Sail is a lightweight command-line interface for interacting with Laravel's default Docker development environment. It provides a single command (`sail up`) to launch a complete, containerized development environment with PHP, MySQL, Redis, and all our other dependencies. This ensures your environment perfectly matches the one we use in this series.

## How Laravel Works: The Request Lifecycle

Before you start building, it's important to understand how Laravel processes web requests. This mental model will make everything that follows more intuitive.

### The Request Journey

When a user visits your CRM application, here's what happens:

1. **User Action** → Visitor types `http://localhost/contacts` in their browser
2. **HTTP Request** → Browser sends HTTP GET request to your Laravel server
3. **Entry Point** → Laravel boots via `public/index.php`, which loads the application
4. **Router** → Laravel checks `routes/web.php` to find a route matching `/contacts`
5. **Middleware** → Request passes through middleware (e.g., authentication checks, CSRF protection)
6. **Controller** → If a matching route is found, Laravel calls the appropriate **Controller** method
7. **Business Logic** → Controller queries the database (via **Models**) and processes data
8. **View Rendering** → Controller returns a **View** (React component via Inertia in our case)
9. **Response** → Laravel sends HTML/JSON back to the browser
10. **Display** → Browser renders the page to the user

### Why This Matters for Your CRM

- **Routes** define what URLs users can visit (`/contacts`, `/deals`, etc.)
- **Controllers** contain the logic for handling requests
- **Models** represent database tables and handle data operations
- **Views** display data to users (in our case, React components)

Throughout this series, you'll be creating routes, controllers, models, and views. Understanding this flow helps you know where to put your code and why.

### A Simple Example

When a user clicks "View All Contacts":

```
Route matches: /contacts →
Calls ContactController@index →
Controller queries Contact model for all contacts →
Returns contacts to Inertia/React view →
React renders a table of contacts →
User sees the contacts
```

This is the core pattern you'll repeat for every feature in the CRM.

## Multi-Tenancy: Building for Teams

One of the most important architectural decisions for this CRM is **multi-tenancy**. This means one application serves multiple teams, each with completely isolated data.

### What Does Multi-Tenancy Mean?

In a multi-tenant application:

- **Team A** can see and edit only their contacts, deals, and tasks
- **Team B** sees completely different data, even though they use the same application
- **Both teams** share the same codebase and database (but not their data)

Think of it like apartment buildings: one building (application), multiple units (teams), each with their own locks and isolation.

### Why Multi-Tenancy for This CRM?

1. **Scalability** - One application instance serves many customers instead of running separate apps for each
2. **Cost Efficiency** - Shared infrastructure means lower hosting costs
3. **Maintenance** - Deploy once, every team gets the update automatically
4. **Real-World Skills** - Multi-tenancy is increasingly standard in SaaS applications

### How It Works in Practice

When a user logs in:

```
1. User logs in with team credentials
2. Laravel stores team_id in the session
3. Every database query automatically filters by team_id
4. User can only see/edit their team's data
5. Database enforces this at the model level (scoping)
```

For example, when you query contacts:

```php
// Without multi-tenancy: Gets ALL contacts (wrong!)
$contacts = Contact::all();

// With multi-tenancy: Gets only current team's contacts (correct!)
$contacts = Contact::whereBelongsTo(auth()->user()->team)->get();
```

### Data Isolation Across Features

Every feature you build in this CRM maintains this isolation:

- **Contacts** belong to a team
- **Companies** belong to a team
- **Deals** belong to a team
- **Tasks** belong to a team
- **Team Members** can only access their own team's data

By Chapter 08, you'll learn to enforce this isolation automatically using Laravel's query scopes. For now, just know that this architectural decision influences everything from database design to authorization logic.

## Essential Tool: Artisan CLI

Throughout this series, you'll use **Artisan** constantly. It's Laravel's command-line tool that automates repetitive tasks and helps you generate boilerplate code.

### What is Artisan?

Artisan is a CLI (command-line interface) included with every Laravel project. You invoke it via:

```bash
php artisan command-name
```

Or via Laravel Sail:

```bash
sail artisan command-name
```

### Common Artisan Commands You'll Use

| Command                       | Purpose                                     | Example                                 |
| ----------------------------- | ------------------------------------------- | --------------------------------------- |
| `php artisan tinker`          | Interactive PHP shell connected to your app | Test queries, debug code                |
| `php artisan make:migration`  | Generate a database migration file          | Create new database tables              |
| `php artisan make:model`      | Generate a Model class                      | Define database table structure in code |
| `php artisan make:controller` | Generate a Controller class                 | Handle HTTP requests                    |
| `php artisan migrate`         | Run database migrations                     | Create/update database tables           |
| `php artisan serve`           | Start a local development server            | Test your app locally                   |
| `php artisan route:list`      | Show all registered routes                  | View all URLs your app responds to      |
| `php artisan config:cache`    | Cache configuration files                   | Optimize for production                 |

### Why Artisan Matters

Instead of manually:

- Creating a Controller file and writing boilerplate code
- Creating a database migration and structuring it correctly
- Navigating complex configurations

You run a single command and Artisan generates everything correctly formatted and in the right place.

### Example: Generating a Controller

Without Artisan:

```
1. Create file: app/Http/Controllers/ContactController.php
2. Write namespace, class declaration, inheritance
3. Add proper method stubs
4. Format according to Laravel conventions
```

With Artisan:

```bash
php artisan make:controller ContactController
```

Done. Artisan handles all the boilerplate.

### A Preview

In Chapter 03, you'll create your first routes and controllers using Artisan. In Chapter 04, you'll use Artisan to generate database migrations. By Chapter 05, you'll be comfortable with the command-line workflow. For now, just know that **CLI commands are your friend**-they save time and reduce errors.

## Laravel Ecosystem Packages

The published lessons use these Laravel tools:

| Tool | Where you'll use it |
| --- | --- |
| [Laravel Sail](https://laravel.com/framework/docs/12.x/sail) | Run PHP, a database and supporting services for the main project in Chapter 02. |
| [React starter kit](https://laravel.com/framework/docs/12.x/starter-kits) | Start with sign-in, registration and an Inertia frontend in Chapter 07. |
| [Sanctum](https://laravel.com/framework/docs/12.x/sanctum) | Issue personal access tokens and check token abilities in Chapter 21. |
| [Horizon](https://laravel.com/framework/docs/12.x/horizon) | Run the lab's Redis workers and inspect completed exports in Chapter 30. |

## Which lessons to follow

Work through [setup and application structure](/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment/) first if you want to build the main CRM. Chapters 04–10 cover models, database tables and authentication. Chapters 11–19 use those foundations for CRM features and notifications.

If you already know Laravel, you can start the independent [contacts API lab](/series/build-crm-laravel-12/chapters/20-building-restful-api-crm/). Start with the API and Sanctum personal access tokens in chapters 20 and 21, then move to database jobs and Redis with Horizon in chapters 29 and 30.

## Exercises

### Exercise 1: Define Your Learning Goals (~10 min)

**Goal**: Clarify what you want to achieve from this series before you choose a learning path

Create a simple document (notepad, Google Doc, or markdown file) and write down:

- What motivated you to learn Laravel? (1-2 sentences)
- What type of application do you want to build after completing this series?
- Which features from this CRM are most important for your goals? (team access, the sales pipeline, the contacts API or queue workers)
- How much time per week can you realistically dedicate to this series?
- What's your experience level with PHP? (beginner, intermediate, advanced)

**Validation**: You're done when you have written answers to all five questions. Keep this document handy-refer back to it when you feel stuck or unmotivated.

### Exercise 2: Explore the Laravel Documentation (~10 min)

**Goal**: Familiarize yourself with Laravel's official resources so you know where to find help

1. Visit [Laravel 12 Documentation](https://laravel.com/docs/12.x) - This is your reference guide for the entire series
2. Spend 2-3 minutes browsing the table of contents to understand what topics are covered
3. Read the "Getting Started" section introduction
4. Bookmark this page for quick reference during development
5. Optional: Visit the [Laravel Bootcamp](https://bootcamp.laravel.com/) to see an alternative learning approach

**Validation**: You should be able to answer:

- Where would you look to find information about Eloquent relationships?
- How would you search for information about middleware?
- What sections cover database migrations?

### Exercise 3: Assess Your Prerequisites & Set Up Your Environment (~15 min)

**Goal**: Ensure your computer is ready for the series and that you can navigate the terminal

Run each command below in your terminal. All should succeed with the correct versions:

```bash
# Verify PHP 8.6 or higher
php --version
# Expected output: PHP 8.6.x (or higher)

# Verify Composer is installed
composer --version
# Expected output: Composer version X.X.X

# Verify Node.js 18 or higher
node --version
# Expected output: v18.x or higher

# Verify Docker Desktop is running
docker --version
# Expected output: Docker version X.X.X
```

**Validation**: If all four commands succeed with correct versions, you're ready for Chapter 02. If any fail:

- **PHP version too old**: Download PHP 8.6+ from [php.net](https://www.php.net/downloads)
- **Composer not found**: Install Composer from [getcomposer.org](https://getcomposer.org)
- **Node version too old**: Download Node.js 18+ from [nodejs.org](https://nodejs.org)
- **Docker not running**: Start Docker Desktop application on your system

Once all commands pass, you're fully prepared to begin Chapter 02.

## Common Mistakes to Avoid

Learning a complex series like this is challenging. Avoid these common pitfalls:

### Mistake 1: Skipping to Code Too Quickly

**What happens**: You skip chapters 1-3 and jump straight to Chapter 4, only to realize you don't have the right project structure or authentication set up.

**Why it fails**: Each chapter builds on previous code. The database schema in Chapter 4 depends on authentication from Chapter 3.

**How to avoid it**: Follow chapters in order, even if they seem simple. Part 1-3 build the essential foundation.

### Mistake 2: Not Having Adequate Time

**What happens**: You start the series while juggling a full-time job, family, and other commitments, then burn out around Chapter 15.

**Why it fails**: Real learning requires focus. Rushing through code without understanding creates gaps that compound later.

**How to avoid it**: Be honest about time availability. Dedicate consistent blocks (aim for 8-10 hours weekly) and stick to a schedule.

### Mistake 3: Ignoring Error Messages

**What happens**: You get a "database connection error" and skip Chapter 5 instead of reading the error message and fixing it.

**Why it fails**: Error messages are your teachers. They tell you exactly what's wrong. Ignoring them means you never learn debugging skills.

**How to avoid it**: Read every error message. Google it. Understand it. This is how real developers work.

### Mistake 4: Not Running the Verification Script

**What happens**: You start Chapter 2, run a command, and get "PHP version too old" - but you thought you had 8.6.

**Why it fails**: Assumptions about your environment are dangerous. Different PHP versions can be installed in different places.

**How to avoid it**: Run `php verify-environment.php` before starting. Fix any issues immediately.

### Mistake 5: Trying to Understand Everything at Once

**What happens**: You spend 3 hours reading about Eloquent relationships, Docker, Inertia, and Laravel Sail all in Chapter 2.

**Why it fails**: Your brain needs time to absorb concepts. Each chapter focuses on specific topics for a reason.

**How to avoid it**: Trust the learning path. Focus on the current chapter. Other topics will be covered when you need them.

### Mistake 6: Not Doing the Exercises

**What happens**: You read every chapter but skip the exercises because "you understand the concepts."

**Why it fails**: Understanding in your head ≠ being able to do it. Real learning happens through doing.

**How to avoid it**: Complete every exercise. They're not busy work-they're deliberate practice that builds skills.

## Wrap-up

Congratulations! You've completed your foundational planning session for the Build a CRM with Laravel 12 series. Take a moment to celebrate what you now understand:

**What You've Accomplished:**

- ✓ Understand the CRM project scope, core features, and target users
- ✓ Explored the complete technology stack (Laravel 12, React, Inertia, Tailwind, etc.)
- ✓ Identified the tools used for local setup, authentication and queue monitoring
- ✓ Chosen a starting point in the main project or the API and queue lab
- ✓ Verified your development environment is ready (PHP 8.6+, Composer, Node, Docker)

**Why This Matters:**

Many developers jump straight into code without understanding the big picture. By taking this time to plan, you've set yourself up for success. You know exactly what you're building, why it matters, and how your learning will progress. When you hit a challenging chapter later, you can look back at this roadmap and remember why you started.

**What's Next:**

In **Chapter 02: Installation & Environment Setup**, we'll stop planning and start building. You'll run your first Laravel commands, spin up Docker containers with Laravel Sail (the Docker-based development environment), and configure all your services (PHP, MySQL, Redis, Mailhog). This is where your CRM comes to life.

Why Docker? In Chapter 1, you verified that Docker Desktop is running on your machine. Docker eliminates "it works on my machine" problems by creating consistent, containerized development environments. Whether you're on macOS, Linux, or Windows, Sail ensures you have identical PHP versions, database versions, and all services configured the same way. This consistency is critical when building real applications.

By the end of Chapter 02, your complete development environment will be running locally, ready for Chapter 03 where you'll explore Laravel's project structure and create your first routes.

Before moving to Chapter 02, make sure you've:

- [ ] Completed all three exercises above
- [ ] Verified all four tools are installed and updated
- [ ] Written down your learning goals from Exercise 1
- [ ] Bookmarked the Laravel documentation
- [ ] Set aside dedicated time for the series (aim for consistent weekly blocks)

You're ready. Let's build something amazing.


## Further Reading

To deepen your understanding of the topics covered in this chapter:

- [Laravel 12 Release Notes](https://laravel.com/docs/12.x/releases) - What's new in Laravel 12
- [Laravel Philosophy](https://laravel.com/docs/12.x/readme) - Understanding Laravel's design principles
- [Inertia.js Overview](https://inertiajs.com/) - How Inertia bridges server and client
- [React Documentation](https://react.dev/) - Modern React with hooks and TypeScript
- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework
- [Laravel Ecosystem](https://laravel.com/) - Explore Forge, Vapor, Nova, and other official tools
