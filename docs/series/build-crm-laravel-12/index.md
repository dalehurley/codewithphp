---
title: Build a CRM with Laravel 12
description: Master Laravel 12 and its ecosystem by building a production-ready CRM application from scratch—authentication, teams, billing, real-time features, and deployment.
series: build-crm-laravel-12
order: 0
difficulty: Intermediate
prerequisites:
  [
    "PHP 8.4+ experience",
    "Composer proficiency",
    "Basic Laravel knowledge helpful but not required",
    "Node.js and npm installed",
  ]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Build a CRM with Laravel 12</span>
</div>

<!-- Hero image will be added when generated -->
<!-- ![Build a CRM with Laravel 12](/images/build-crm-laravel-12/chapter-00-series-index-hero-full.webp) -->

# Build a CRM with Laravel 12 <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Welcome to **Build a CRM with Laravel 12** — a comprehensive, hands-on series that teaches you to master Laravel and its rich ecosystem by building a complete, production-ready Customer Relationship Management (CRM) application. By the end of this 40-chapter series, you'll have built a sophisticated CRM system with contacts, companies, deals, tasks, team management, authentication, billing, real-time features, and comprehensive deployment strategies.

Laravel is the most popular PHP framework in the world, powering millions of web applications from startups to enterprises. Its elegant syntax, powerful features, and vast ecosystem of official packages make it the go-to choice for building modern web applications. Laravel 12 continues this tradition with improved performance, enhanced developer experience, and seamless integration with modern JavaScript frameworks.

What makes this series unique is that you'll learn Laravel by _doing_—not just reading about it. Every chapter builds on the previous one, adding real features to your CRM application. You'll use Laravel's official packages (Sail, Sanctum, Cashier, Horizon, Telescope, and more) in realistic scenarios, understanding not just _how_ they work, but _when_ and _why_ to use them.

By building a complete CRM from the ground up, you'll gain deep, transferable knowledge that applies to any Laravel project. Whether you're building a SaaS application, an e-commerce platform, or an internal business tool, the patterns and practices you learn here will serve you throughout your development career.

## Who This Is For

This series is designed for:

- **Intermediate PHP developers** who want to master Laravel and modern web development
- **Framework learners** transitioning from other PHP frameworks (Symfony, CodeIgniter, etc.) or languages (Python, Ruby, Node.js)
- **Laravel developers** who know the basics but want to understand the full ecosystem and best practices
- **Full-stack developers** building real-world applications with authentication, payments, and deployment
- **Entrepreneurs and indie hackers** creating SaaS products and need a complete development roadmap

You should be comfortable with PHP syntax, object-oriented programming, and basic web development concepts (HTTP, databases, HTML/CSS). Prior Laravel experience is helpful but not required—we'll cover fundamentals before diving into advanced features.

## Prerequisites

**Software Requirements:**

- **PHP 8.4+** (Laravel 12 requires PHP 8.2+, but we recommend 8.4 for the latest features)
- **Composer** (PHP's dependency manager)
- **Node.js 18+** and **npm** (for frontend build tools)
- **Docker Desktop** (for Laravel Sail development environment)
- **Text editor or IDE** (VS Code, PhpStorm, Sublime Text, or your preferred editor)
- **Git** (for version control)

::: info Laravel Sail
We'll use **Laravel Sail** (Laravel's official Docker development environment) throughout this series. Sail provides a consistent, pre-configured environment with PHP, MySQL, Redis, Mailhog, and other services—no manual setup required. If you prefer a different local environment (XAMPP, Valet, Homestead), the code will work, but you'll need to adapt the setup instructions.
:::

**Time Commitment:**

- **Estimated total**: 55–75 hours to complete all chapters
- **Per chapter**: 45 minutes to 3 hours
- **Core project (Chapters 1-32 + 38-39)**: 45–55 hours
- **Bonus chapters**: 10–20 hours (optional advanced topics)

**Skill Assumptions:**

- You can write PHP classes, use namespaces, and understand inheritance
- You're familiar with databases and SQL basics
- You understand HTTP requests, sessions, and cookies
- You can use the command line to navigate directories and run commands
- You know HTML, CSS basics, and can read JavaScript (React knowledge helpful but not required)
- No prior Laravel experience required—we start with setup and fundamentals

## What You'll Build

<ProgressTracker seriesId="build-crm-laravel-12" :totalChapters="40" title="Your Progress" />

By working through this series, you will create:

1. **A production-ready CRM application** with:

   - User authentication and registration with React/Inertia frontend
   - Team management with role-based permissions
   - Complete CRUD for Contacts, Companies, Deals, and Tasks
   - Visual sales pipeline with drag-and-drop interface
   - Task scheduling with automated reminders
   - Email notifications and real-time updates
   - Search functionality with Laravel Scout
   - Subscription billing with Stripe via Laravel Cashier
   - RESTful API with Sanctum authentication
   - Background job processing with Redis queues
   - Comprehensive testing with PHPUnit and Dusk browser automation
   - Performance monitoring with Laravel Pulse and Telescope debugging

2. **A modern single-page application** using:

   - React 19 with TypeScript
   - Inertia.js for seamless server-client integration
   - Tailwind CSS 4 for styling
   - shadcn/ui component library for beautiful UI
   - Responsive design for mobile and desktop

3. **Professional development practices**:

   - Database design with migrations and relationships
   - Authorization with policies and gates
   - Form validation and error handling
   - Task scheduling and queue workers
   - Automated testing with PHPUnit and Dusk
   - Debugging and monitoring with Telescope and Pulse
   - Docker containerization and deployment
   - Real-time features with WebSockets
   - Full-text search implementation

4. **Deep understanding of Laravel's ecosystem**:
   - Sail (Docker dev environment)
   - Breeze/React starter kit (authentication)
   - Sanctum (API authentication)
   - Cashier (Stripe billing)
   - Horizon (queue monitoring)
   - Telescope (debugging)
   - Scout (search)
   - Reverb (WebSockets)
   - Octane (high-performance mode)
   - And more official packages

Every feature you build will be production-ready, following Laravel best practices and modern design patterns.

## How This Series Works

This series follows a **project-based, incremental approach**: you'll build a real CRM application from scratch, adding features chapter by chapter. Each chapter focuses on one concept or feature, ensuring you master it before moving on.

Every chapter includes:

- **Clear learning objectives** so you know what to expect
- **Step-by-step instructions** with complete code examples
- **Why it works** explanations connecting theory to practice
- **Hands-on exercises** to reinforce learning
- **Troubleshooting sections** for common errors
- **Further reading** for deeper exploration
- **Code samples** in the `/code/build-crm-laravel-12/` directory

The series is divided into **7 parts**:

1. **Core Setup** (Chapters 1-3): Laravel installation, Sail setup, and project structure
2. **Database & Foundation** (Chapters 4-10): Data modeling, authentication, teams, and authorization
3. **Core CRM Modules** (Chapters 11-18): Contacts, Companies, Deals, and Tasks with full CRUD
4. **Communication & API** (Chapters 19-23): Notifications, RESTful API, Sanctum, and social logins
5. **Business Features** (Chapters 24-28): Billing, search, real-time events, performance monitoring, and optimization
6. **Background Processing** (Chapters 29-30): Queues, jobs, and Horizon monitoring
7. **Testing & Production** (Chapters 31-39): Browser testing, debugging with Telescope, deployment, and best practices

**Bonus chapters** (marked with "Bonus") cover optional advanced topics. You can complete the core CRM without them, but they add valuable skills and features.

::: tip
Code along instead of copy-pasting. Type the code yourself, experiment with changes, and break things to see what happens. This builds muscle memory and deeper understanding.
:::

## Learning Path Overview

This diagram shows how the series progresses from setup to deployment:

![Laravel CRM Learning Path](/images/build-crm-laravel-12/chapter-00-learning-path-diagram-hero-full.webp)

Each part builds essential skills you'll need for the next. By Part 7, you'll have a complete, production-ready application.

## Quick Start

Want to verify your setup right now? Here's how to create a new Laravel project in under 5 minutes:

```bash
# 1. Create a new Laravel project with Sail
curl -s "https://laravel.build/my-crm?with=mysql,redis,mailhog" | bash

# 2. Navigate into the project
cd my-crm

# 3. Start Laravel Sail (Docker containers)
./vendor/bin/sail up -d

# 4. Access your application
open http://localhost

# Expected: Laravel welcome page appears in your browser
```

**What's Next?**  
If that worked, you're ready to start! Head to [Chapter 01](/series/build-crm-laravel-12/chapters/01-introduction-series-overview) for an overview of what we'll build, or continue to [Chapter 02](/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment) for detailed setup.

If you got an error, don't worry—[Chapter 02](/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment) will walk you through installing PHP, Composer, Docker, and everything you need.

## Chapters

### Part 1: Core Setup (Chapters 1–3)

Set up Laravel 12, understand the framework structure, and prepare for development.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-01-introduction-series-overview-hero-thumbnail.webp" alt="Chapter 01 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/01-introduction-series-overview">01 — Introduction & Series Overview</a></h4>
    <p style="margin-bottom: 0;">Understand the CRM project's scope, features, and architecture. Learn what you'll build over 40 chapters, explore the tech stack (Laravel 12, React, Inertia, Tailwind), and see how each official Laravel package fits into the application. Set clear expectations for your learning journey.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-02-setting-up-laravel-12-project-hero-thumbnail.webp" alt="Chapter 02 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment">02 — Setting Up Laravel 12 Project & Dev Environment</a></h4>
    <p style="margin-bottom: 0;">Install Laravel 12, configure Laravel Sail with Docker, and verify your development environment. Set up MySQL, Redis, and Mailhog services. Learn Sail CLI commands and ensure everything runs correctly before building the CRM.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-03-laravel-12-fundamentals-hero-thumbnail.webp" alt="Chapter 03 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/03-laravel-12-fundamentals-project-structure">03 — Laravel 12 Fundamentals & Project Structure</a></h4>
    <p style="margin-bottom: 0;">Explore Laravel's directory structure, MVC architecture, and Artisan commands. Understand routes, controllers, views, and configuration. Create a basic route and controller to verify your understanding of how Laravel handles requests.</p>
  </div>
</div>

### Part 2: Database & Foundation (Chapters 4–10)

Design the database, implement authentication, and build authorization systems.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-04-planning-architecture-data-modeling-hero-thumbnail.webp" alt="Chapter 04 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/04-planning-application-architecture-data-modeling">04 — Planning Application Architecture & Data Modeling</a></h4>
    <p style="margin-bottom: 0;">Plan the CRM's data models and relationships. Design entities (User, Team, Contact, Company, Deal, Task) and their relationships. Create an entity-relationship diagram that will guide database schema implementation. Design with multi-tenancy in mind.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-05-database-migrations-schema-design-hero-thumbnail.webp" alt="Chapter 05 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/05-database-migrations-schema-design">05 — Database Migrations & Schema Design</a></h4>
    <p style="margin-bottom: 0;">Create Laravel migrations for all CRM tables. Implement foreign keys, indexes, and constraints. Learn migration best practices, rollback strategies, and how to modify existing tables safely. Run migrations to build the complete database schema.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-06-eloquent-models-relationships-hero-thumbnail.webp" alt="Chapter 06 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/06-eloquent-models-relationships">06 — Eloquent Models & Relationships</a></h4>
    <p style="margin-bottom: 0;">Build Eloquent models for Contact, Company, Deal, and Task. Define relationships (one-to-many, belongs-to, many-to-many), set fillable fields, and add casts. Learn about eager loading, scopes, and accessors to work with CRM data efficiently.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-07-authentication-react-starter-kit-hero-thumbnail.webp" alt="Chapter 07 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/07-user-authentication-react-starter-kit">07 — User Authentication with React Starter Kit</a></h4>
    <p style="margin-bottom: 0;">Implement authentication using Laravel's official React starter kit (React 19, TypeScript, Inertia, Tailwind, shadcn/ui). Set up registration, login, password reset, and email verification. Understand Inertia's server-client bridge for building SPAs with Laravel.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-08-team-management-user-roles-hero-thumbnail.webp" alt="Chapter 08 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/08-team-management-user-roles">08 — Team Management & User Roles</a></h4>
    <p style="margin-bottom: 0;">Add team functionality to the CRM. Create teams, invite members, and assign roles (owner, admin, member). Implement team switching and manage team-scoped data. Prepare for multi-tenant authorization in the next chapter.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-09-authorization-access-control-hero-thumbnail.webp" alt="Chapter 09 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/09-authorization-access-control">09 — Authorization & Access Control</a></h4>
    <p style="margin-bottom: 0;">Implement Laravel policies and gates for authorization. Ensure users can only access their own team's data. Create policies for Contact, Company, Deal, and Task models. Apply authorization checks in controllers and views for secure multi-tenant data access.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-10-layout-ui-design-customization-hero-thumbnail.webp" alt="Chapter 10 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/10-layout-ui-design-customization">10 — Layout and UI Design Customization</a></h4>
    <p style="margin-bottom: 0;">Design the CRM's user interface with Tailwind CSS and shadcn/ui components. Create a navigation sidebar, customize the React starter kit's layout, and build reusable UI components. Ensure responsive design for mobile and desktop.</p>
  </div>
</div>

### Part 3: Core CRM Modules (Chapters 11–18)

Build the core CRM features: Contacts, Companies, Deals, and Tasks with full CRUD.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-11-contacts-module-database-model-hero-thumbnail.webp" alt="Chapter 11 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/11-contacts-module-database-model">11 — Contacts Module – Database & Model</a></h4>
    <p style="margin-bottom: 0;">Focus on the Contacts module. Refine the contacts table migration and Contact model with relationships to Company and Team. Use factories to generate sample contact data for testing and development.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-12-contacts-module-crud-operations-hero-thumbnail.webp" alt="Chapter 12 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/12-contacts-module-crud-operations">12 — Contacts Module – CRUD Operations</a></h4>
    <p style="margin-bottom: 0;">Build the Contacts management interface. Create resourceful routes and controllers for CRUD operations. Implement Inertia/React views for listing, creating, editing, and deleting contacts. Add validation and authorization checks.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-13-companies-module-database-model-hero-thumbnail.webp" alt="Chapter 13 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/13-companies-module-database-model">13 — Companies Module – Database & Model</a></h4>
    <p style="margin-bottom: 0;">Build the Companies module foundation. Define the Company model with relationships to Contacts and Deals. Prepare factories for generating company data. Understand how companies serve as the organizational structure for CRM data.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-14-companies-module-crud-operations-hero-thumbnail.webp" alt="Chapter 14 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/14-companies-module-crud-operations">14 — Companies Module – CRUD Operations</a></h4>
    <p style="margin-bottom: 0;">Implement company management with full CRUD. Create controllers and React views for companies. Show related contacts for each company using eager loading. Demonstrate relational data handling in forms (selecting a company for a contact).</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-15-deals-module-database-pipeline-design-hero-thumbnail.webp" alt="Chapter 15 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/15-deals-module-database-pipeline-design">15 — Deals Module – Database & Pipeline Design</a></h4>
    <p style="margin-bottom: 0;">Design the Deals (sales opportunities) module. Plan a sales pipeline with stages (New, In Progress, Won, Lost). Create the deals table with fields for stage, amount, closing date, and relationships to Company/Contact. Prepare for building a visual pipeline interface.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-16-deals-module-crud-pipeline-interface-hero-thumbnail.webp" alt="Chapter 16 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/16-deals-module-crud-pipeline-interface">16 — Deals Module – CRUD & Pipeline Interface</a></h4>
    <p style="margin-bottom: 0;">Build deal management with a visual pipeline interface. Implement CRUD operations for deals and create a Kanban-style board showing deals grouped by stage. Add drag-and-drop functionality (or stage selection) to move deals through the pipeline.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-17-tasks-module-database-model-hero-thumbnail.webp" alt="Chapter 17 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/17-tasks-module-database-model">17 — Tasks Module – Database & Model</a></h4>
    <p style="margin-bottom: 0;">Build the Tasks (activities/to-dos) module foundation. Decide on task associations (linked to Contacts, Deals, or standalone) and create the tasks table. Define the Task model with relationships and status fields (open, completed).</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-18-tasks-module-crud-task-scheduling-hero-thumbnail.webp" alt="Chapter 18 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/18-tasks-module-crud-task-scheduling">18 — Tasks Module – CRUD & Task Scheduling</a></h4>
    <p style="margin-bottom: 0;">Implement task management UI and automated reminders. Build CRUD operations for tasks, show related tasks when viewing contacts/deals, and use Laravel's Task Scheduling to send daily email reminders for due tasks. Learn about Laravel's scheduling system.</p>
  </div>
</div>

### Part 4: Communication & API (Chapters 19–23)

Add notifications, build a RESTful API, and integrate authentication and social logins.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-19-notifications-email-integration-hero-thumbnail.webp" alt="Chapter 19 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/19-notifications-email-integration">19 — Notifications & Email Integration</a></h4>
    <p style="margin-bottom: 0;">Use Laravel's notification system to send email alerts. Configure mail settings with Mailhog for testing. Create notifications for task reminders, deal closures, and team invitations. Learn to send both email and in-app notifications.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-20-building-restful-api-hero-thumbnail.webp" alt="Chapter 20 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/20-building-restful-api-crm">20 — Building a RESTful API for the CRM</a></h4>
    <p style="margin-bottom: 0;">Design and expose RESTful API endpoints for CRM data. Create API routes and controllers for Contacts, Companies, Deals, and Tasks. Use Laravel API Resources to format JSON responses consistently. Prepare the API for authentication in the next chapter.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-21-api-authentication-sanctum-hero-thumbnail.webp" alt="Chapter 21 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/21-api-authentication-sanctum">21 — API Authentication with Sanctum</a></h4>
    <p style="margin-bottom: 0;">Secure the CRM API with Laravel Sanctum token authentication. Configure Sanctum, create token generation endpoints, and protect API routes with Sanctum middleware. Demonstrate making authenticated API requests from external clients or SPAs.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-22-oauth2-laravel-passport-hero-thumbnail.webp" alt="Chapter 22 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/22-oauth2-laravel-passport">22 — OAuth2 with Laravel Passport</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Learn when to use Laravel Passport for full OAuth2 server functionality. Install Passport, configure OAuth clients, and understand authorization codes and scopes. See how Passport enables third-party applications to integrate with your CRM API.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-23-social-logins-socialite-hero-thumbnail.webp" alt="Chapter 23 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/23-social-logins-socialite">23 — Social Logins with Laravel Socialite</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Allow users to log in via social networks using Laravel Socialite. Configure Google OAuth, implement the OAuth flow, and link social accounts to users. Enhance the authentication experience with "Sign in with Google" functionality.</p>
  </div>
</div>

### Part 5: Business Features (Chapters 24–28)

Implement subscription billing, search, real-time features, and performance optimizations.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-24-subscription-billing-cashier-hero-thumbnail.webp" alt="Chapter 24 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/24-subscription-billing-cashier">24 — Subscription Billing with Laravel Cashier</a></h4>
    <p style="margin-bottom: 0;">Add SaaS billing to the CRM using Laravel Cashier and Stripe. Set up Stripe, create subscription plans, and implement checkout flows. Use Cashier's Billable trait to manage subscriptions, invoices, and payment methods. Handle webhooks for subscription events.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-25-advanced-search-scout-hero-thumbnail.webp" alt="Chapter 25 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/25-advanced-search-scout">25 — Advanced Search with Laravel Scout</a></h4>
    <p style="margin-bottom: 0;">Add full-text search to the CRM with Laravel Scout. Configure Scout with MeiliSearch or Algolia, index Contact and Company models, and implement a search interface. Enable users to quickly find records across the CRM using keyword search.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-26-realtime-events-reverb-echo-hero-thumbnail.webp" alt="Chapter 26 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/26-realtime-events-reverb-echo">26 — Real-Time Events with Laravel Reverb & Echo</a></h4>
    <p style="margin-bottom: 0;">Add real-time features with Laravel Reverb (WebSocket server) and Echo (JavaScript client). Broadcast events when records are created or updated, and update the UI live for all team members. Demonstrate live notifications and collaborative editing.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-27-performance-optimization-monitoring-hero-thumbnail.webp" alt="Chapter 27 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/27-performance-optimization-monitoring">27 — Performance Optimization & Monitoring</a></h4>
    <p style="margin-bottom: 0;">Optimize the CRM for production performance. Implement caching strategies (query results, configuration), reduce N+1 queries with eager loading, add database indexes, and use Laravel Pulse to monitor application health and performance in real time.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-28-high-performance-octane-hero-thumbnail.webp" alt="Chapter 28 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/28-high-performance-octane">28 — High-Performance Tuning with Laravel Octane</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Supercharge performance with Laravel Octane. Install Octane with Swoole or RoadRunner, configure the CRM to run with Octane, and understand caveats (stateful issues). See dramatic performance improvements for high-traffic scenarios.</p>
  </div>
</div>

### Part 6: Background Processing (Chapters 29–30)

Handle long-running tasks asynchronously with queues and monitor them effectively.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-29-queues-background-jobs-hero-thumbnail.webp" alt="Chapter 29 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/29-queues-background-jobs">29 — Queues & Background Jobs</a></h4>
    <p style="margin-bottom: 0;">Use Laravel's queue system for asynchronous task processing. Configure Redis queues, create jobs for sending emails and processing data, and run queue workers. Improve responsiveness by offloading heavy tasks to background jobs.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-30-monitoring-queues-horizon-hero-thumbnail.webp" alt="Chapter 30 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/30-monitoring-queues-horizon">30 — Monitoring Queues with Laravel Horizon</a></h4>
    <p style="margin-bottom: 0;">Monitor and manage queue workers with Laravel Horizon. Install Horizon, configure supervisors, and use the Horizon dashboard to view job throughput, failures, and performance metrics. Gain full visibility into background job processing.</p>
  </div>
</div>

### Part 7: Testing & Production (Chapters 31–39)

Test the application, debug issues, deploy to production, and follow best practices.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-31-browser-testing-dusk-hero-thumbnail.webp" alt="Chapter 31 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/31-browser-testing-dusk">31 — Automated Browser Testing with Laravel Dusk</a></h4>
    <p style="margin-bottom: 0;">Write end-to-end tests with Laravel Dusk. Install Dusk, write browser tests for login and CRUD operations, and run automated tests in headless Chrome. Ensure critical user workflows continue working as the CRM evolves.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-32-debugging-monitoring-telescope-hero-thumbnail.webp" alt="Chapter 32 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/32-debugging-monitoring-telescope">32 — Debugging & Monitoring with Laravel Telescope</a></h4>
    <p style="margin-bottom: 0;">Debug the CRM with Laravel Telescope. Install Telescope, explore the dashboard to view requests, queries, emails, exceptions, and jobs. Use Telescope to identify performance bottlenecks and fix bugs during development.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-33-feature-flags-pennant-hero-thumbnail.webp" alt="Chapter 33 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/33-feature-flags-pennant">33 — Feature Flags with Laravel Pennant</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Implement feature flags with Laravel Pennant. Define flags for beta features, enable them for specific users or teams, and toggle features without redeploying. Learn strategies for phased rollouts and A/B testing in the CRM.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-34-ai-assisted-development-boost-hero-thumbnail.webp" alt="Chapter 34 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/34-ai-assisted-development-boost">34 — AI-Assisted Development with Laravel Boost</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Explore Laravel Boost for AI-assisted coding. Install Boost, configure the MCP server, and use AI tools to generate controllers, policies, and tests. See how Boost's Laravel-specific knowledge accelerates development.</p>
    <p style="margin-top: 0.5rem; margin-bottom: 0; font-style: italic; color: #64748b;">Note: Basic Boost setup and usage covered in Chapter 02. This chapter explores advanced AI-assisted development workflows.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-35-mobile-pwa-support-hero-thumbnail.webp" alt="Chapter 35 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/35-mobile-pwa-support">35 — Mobile & PWA Support</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Make the CRM mobile-friendly and installable as a Progressive Web App. Audit responsiveness, add a web app manifest, implement a service worker for offline support, and enable "Add to Home Screen" functionality.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-36-multi-tenancy-tenant-management-hero-thumbnail.webp" alt="Chapter 36 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/36-multi-tenancy-tenant-management">36 — Multi-Tenancy & Tenant Management</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Explore advanced multi-tenancy strategies. Discuss single-database vs. multi-database approaches, introduce tenancy packages, and show how to scale the CRM to true tenant isolation with separate databases or schemas per team.</p>
    <p style="margin-top: 0.5rem; margin-bottom: 0; font-style: italic; color: #64748b;">Note: Multi-tenancy fundamentals and team data isolation covered in Chapter 07. This chapter explores advanced tenant separation strategies.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-37-extending-crm-packages-plugins-hero-thumbnail.webp" alt="Chapter 37 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/37-extending-crm-packages-plugins">37 — Extending the CRM via Packages & Plugins</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Design the CRM to be extensible with packages or plugins. Create a sample internal package, demonstrate Laravel's package development workflow, and show how to add functionality without modifying core CRM code.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-38-deployment-devops-hero-thumbnail.webp" alt="Chapter 38 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/38-deployment-devops">38 — Deployment & DevOps</a></h4>
    <p style="margin-bottom: 0;">Deploy the CRM to production. Use Laravel Forge to provision servers and deploy from Git, or explore Laravel Vapor for serverless deployment. Configure environment variables, set up queue workers and cron jobs, and ensure the CRM runs reliably in production.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-39-best-practices-conclusion-hero-thumbnail.webp" alt="Chapter 39 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/39-best-practices-conclusion">39 — Best Practices & Conclusion</a></h4>
    <p style="margin-bottom: 0;">Review best practices for maintaining and scaling Laravel applications. Discuss code organization, security, testing, backups, and ongoing monitoring. Refactor the CRM for production readiness and celebrate your accomplishment. Explore next steps for continued learning.</p>
  </div>
</div>

### Bonus: Alternative Authentication (Chapter 40)

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <!-- <img src="/images/build-crm-laravel-12/chapter-40-jetstream-alternative-hero-thumbnail.webp" alt="Chapter 40 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" /> -->
  <div>
    <h4 style="margin-top: 0;"><a href="/series/build-crm-laravel-12/chapters/40-bonus-jetstream-alternative-authentication">40 — Jetstream Alternative — Team Management & Authentication</a></h4>
    <p style="margin-bottom: 0;"><strong>[Bonus]</strong> Explore Laravel Jetstream as an alternative to the React starter kit. Install Jetstream with Livewire or Inertia, compare its built-in team management with the manual approach used in the series, and understand when to choose Jetstream for your projects.</p>
  </div>
</div>

---

## Frequently Asked Questions

**Do I need to know Laravel before starting this series?**  
No! While prior Laravel experience is helpful, we cover fundamentals from setup through deployment. If you're comfortable with PHP and object-oriented programming, you're ready to start.

**Which Laravel version does this series use?**  
Laravel 12.x. The concepts and patterns apply to Laravel 11+ as well, with only minor syntax differences.

**Why use the React starter kit instead of Jetstream?**  
Laravel 12's official React starter kit is the recommended approach for React-based applications. It provides a modern, lightweight foundation with React 19, TypeScript, and Inertia. We cover Jetstream as a bonus alternative in Chapter 40.

**Can I use Livewire instead of React?**  
Yes! While the series uses React, the backend concepts (models, controllers, policies, etc.) apply regardless of frontend choice. You can follow along and swap the React views for Livewire components.

**How long will each chapter take?**  
Core chapters: 45 minutes to 2 hours. Bonus chapters: 1-3 hours. Project chapters (deployment, testing): 2-4 hours.

**Do I need to complete every chapter?**  
No! Chapters marked **[Bonus]** are optional. You can build a complete, working CRM with just the core chapters (1-21, 24-27, 29-32, 38-39).

**What if I get stuck?**  
Each chapter includes troubleshooting sections. Check the code samples in `/code/build-crm-laravel-12/`, consult the [Laravel documentation](https://laravel.com/docs/12.x), or ask for help in [GitHub Discussions](https://github.com/dalehurley/codewithphp/discussions).

**Will this work on Windows?**  
Yes! Laravel Sail works on Windows with WSL2 (Windows Subsystem for Linux). We recommend using WSL2 for the best experience.

**Is the code production-ready?**  
Yes! We follow Laravel best practices, implement proper authorization and validation, and cover deployment strategies. The CRM you build will be suitable for real-world use.

**What comes after this series?**  
You'll be ready to build any Laravel application. Consider exploring Laravel Nova (admin panel), diving deeper into testing, or building your own SaaS project using the patterns learned here.

## Getting Help

**Stuck on something?** Here's where to get help:

- **Read the troubleshooting sections** in each chapter for common issues
- **Check the code samples** in `/code/build-crm-laravel-12/` for working examples
- **Consult Laravel documentation**: [laravel.com/docs/12.x](https://laravel.com/docs/12.x)
- **Laravel Sail documentation**: [laravel.com/docs/12.x/sail](https://laravel.com/docs/12.x/sail)
- **GitHub Discussions**: [Ask questions and share progress](https://github.com/dalehurley/codewithphp/discussions)
- **Report bugs**: [Open an issue](https://github.com/dalehurley/codewithphp/issues) for unclear explanations or broken examples

## Related Resources

Want to dive deeper? These resources complement the series:

- **[Laravel Documentation](https://laravel.com/docs/12.x)**: Official Laravel 12 documentation (essential reference)
- **[Laravel News](https://laravel-news.com/)**: Latest Laravel updates, packages, and tutorials
- **[Laracasts](https://laracasts.com/)**: Video tutorials for Laravel and PHP (highly recommended)
- **[Laravel Bootcamp](https://bootcamp.laravel.com/)**: Official Laravel learning path
- **[Laravel Ecosystem](https://laravel.com/)**: Explore Forge, Vapor, Nova, Envoyer, and other official tools
- **[Inertia.js Documentation](https://inertiajs.com/)**: Learn more about Inertia for SPAs
- **[Tailwind CSS](https://tailwindcss.com/)**: Master utility-first CSS
- **[shadcn/ui](https://ui.shadcn.com/)**: Beautiful UI components for React
- **[Stripe Documentation](https://stripe.com/docs)**: Payment processing and subscriptions

**Related Series on Code with PHP:**

- **[PHP Basics](/series/php-basics/)** — Master PHP fundamentals before diving into Laravel
- **[AI/ML for PHP Developers](/series/ai-ml-php-developers/)** — Add intelligent features to your Laravel apps

---

::: tip Ready to Start?
Head to [Chapter 01: Introduction & Series Overview](/series/build-crm-laravel-12/chapters/01-introduction-series-overview) to begin your Laravel journey!
:::

---

## Continue Your Learning

Master other aspects of modern web development:

**→ [PHP Basics](/series/php-basics/)** — Learn PHP from scratch  
**→ [AI/ML for PHP Developers](/series/ai-ml-php-developers/)** — Add AI capabilities to Laravel apps  
**→ [Python to Laravel](/series/python-developers-love-php-laravel/)** — Transition from Python frameworks

<style>
:root {
  --primary-teal: #0d9488;
  --primary-teal-dark: #0f766e;
  --laravel-red: #ff2d20;
  --laravel-dark: #e5261f;
  --php-amber: #f59e0b;
  --php-orange: #ea580c;
  --neutral-gray: #64748b;
  --bg-light: #f8fafc;
}

/* Chapter card enhancements */
div[style*="display: flex"][style*="align-items: flex-start"] {
  transition: all 0.3s ease;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--laravel-red);
}

div[style*="display: flex"][style*="align-items: flex-start"]:hover {
  background: var(--bg-light);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(255, 45, 32, 0.15);
  border-left-color: var(--laravel-dark);
}

/* Image styling */
div[style*="display: flex"] img[style*="width: 180px"] {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

div[style*="display: flex"]:hover img[style*="width: 180px"] {
  box-shadow: 0 4px 12px rgba(255, 45, 32, 0.2);
}

/* Link styling */
div[style*="display: flex"] h4 a {
  color: var(--laravel-red);
  transition: color 0.2s ease;
}

div[style*="display: flex"] h4 a:hover {
  color: var(--laravel-dark);
}
</style>
