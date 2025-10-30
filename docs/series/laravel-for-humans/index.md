---
title: Laravel for Humans
description: Build a production-ready SaaS application with Laravel—from zero to deployed on Forge with Spark, Stripe, and modern real-time features.
series: laravel-for-humans
order: 2
difficulty: Intermediate
prerequisites:
  [
    "PHP fundamentals (variables, functions, OOP)",
    "Basic understanding of databases",
    "Command line familiarity",
    "HTML/CSS basics",
  ]
---

# Laravel for Humans

## Overview

Welcome to **Laravel for Humans** — a comprehensive, project-based series that transforms you from a Laravel beginner into a confident developer capable of building and deploying production-ready SaaS applications. By the end of this series, you'll have built a complete collaborative project management SaaS with team features, subscription billing, real-time updates, and professional deployment on Laravel Forge.

Laravel is the world's most popular PHP framework, powering thousands of successful applications from startups to enterprises. Its elegant syntax, powerful features, and thriving ecosystem make it the perfect choice for modern web development. This series will teach you not just how to use Laravel, but how to build professional, scalable applications the right way.

## Who This Is For

This series is designed for:

- **PHP developers** ready to learn Laravel and build real applications
- **Framework beginners** who want hands-on, project-based learning
- **Aspiring SaaS founders** who need to build and deploy their product
- **Full-stack developers** transitioning from other frameworks (Django, Rails, Express, etc.)

You should have basic PHP knowledge (variables, functions, classes, arrays) and understand fundamental web concepts (HTTP, databases, HTML). If you're completely new to PHP, we recommend starting with our **PHP Basics** series first.

## Prerequisites

**Required Knowledge:**

- PHP fundamentals (variables, functions, OOP basics)
- Basic SQL and database concepts
- HTML/CSS fundamentals
- Command line comfort (cd, ls, running commands)
- Git basics (clone, commit, push)

**Software Requirements:**

- **PHP 8.2+** (PHP 8.3+ recommended)
- **Composer** (PHP dependency manager)
- **Node.js 20+** and npm (for asset compilation)
- **SQLite or MySQL/PostgreSQL** (SQLite simplest for learning)
- **Code editor** (VS Code with Laravel extensions, or PhpStorm)
- **Git** for version control

**Optional but Recommended:**

- **Laravel Herd** or **Laravel Valet** for local development
- **Stripe account** (free test mode for payments)
- **DigitalOcean account** for deployment (when you reach deployment chapters)

**Time Commitment:**

- **Estimated total**: 40–60 hours to complete all chapters
- **Per chapter**: 1–3 hours depending on complexity
- **Progressive project**: Built incrementally throughout the series

## What You'll Build

Throughout this series, you'll progressively build **TaskFlow** — a modern, collaborative project management SaaS application featuring:

**Core Application Features:**

1. **User Authentication & Authorization**
   - Registration, login, email verification
   - Password reset and two-factor authentication
   - Role-based permissions

2. **Organizations & Teams**
   - Multi-tenant architecture with Laravel Spark
   - Team invitations and management
   - Role-based access control

3. **Project Management**
   - Projects with tasks and subtasks
   - Task assignments and due dates
   - File attachments and comments
   - Activity feeds and notifications

4. **Real-Time Features**
   - Live task updates with Laravel Echo & Reverb
   - Real-time notifications
   - Online presence indicators

5. **Subscription & Billing**
   - Multiple pricing tiers
   - Stripe integration via Spark
   - Usage-based billing
   - Invoices and payment history

6. **Background Processing**
   - Email notifications with queues
   - Report generation
   - Data exports
   - Scheduled tasks with Laravel Scheduler

7. **RESTful API**
   - JSON API for mobile/third-party apps
   - API authentication with Sanctum
   - Rate limiting and versioning

8. **Production Deployment**
   - Deployment on Laravel Forge + DigitalOcean
   - Zero-downtime deployments
   - SSL certificates and custom domains
   - FrankenPHP for enhanced performance
   - Application monitoring with Nightwatch

Every feature will be built following Laravel best practices, with clean architecture, comprehensive testing, and production-ready code.

## Learning Objectives

By the end of this series, you will be able to:

- **Master Laravel fundamentals**: routing, controllers, models, views, and migrations
- **Build secure authentication systems** with email verification and 2FA
- **Design clean, maintainable architectures** using services, actions, and repositories
- **Create powerful APIs** with authentication, validation, and proper RESTful design
- **Implement real-time features** using websockets with Echo and Reverb
- **Process background jobs** efficiently with queues and Horizon
- **Manage payments and subscriptions** with Stripe and Laravel Spark
- **Write comprehensive tests** with Pest or PHPUnit
- **Deploy production applications** on Laravel Forge with zero-downtime
- **Monitor and debug** applications with Telescope and Nightwatch
- **Optimize performance** with caching, database indexing, and FrankenPHP
- **Follow Laravel best practices** for code organization, security, and scalability

## How This Series Works

This series follows a **progressive project-based approach**. Instead of learning features in isolation, you'll build a complete SaaS application chapter by chapter, adding new features and complexity as you go.

### Learning Philosophy

1. **Build First, Understand Why**: You'll write code immediately, then we'll explain the concepts and patterns
2. **Real-World Context**: Every feature solves a real problem in your SaaS application
3. **Incremental Complexity**: Start simple, add sophistication progressively
4. **Production Ready**: All code follows Laravel best practices and is deployment-ready

### Chapter Structure

Each chapter includes:

- **Clear objectives** — What you'll learn and build
- **Conceptual overview** — Understanding the "why" behind the code
- **Step-by-step implementation** — Building features with working code
- **Code organization** — Following Laravel conventions and best practices
- **Testing** — Writing tests for new features (from Chapter 10 onwards)
- **Common pitfalls** — Avoiding typical mistakes
- **Further exploration** — Deep dives and advanced topics

### The Progressive Project

Rather than building separate demo apps for each chapter, you'll continuously enhance the same application:

- **Chapters 1–5**: Set up Laravel and build authentication
- **Chapters 6–10**: Core functionality (projects, tasks, teams)
- **Chapters 11–15**: Advanced features (API, real-time, files)
- **Chapters 16–20**: Background processing and optimization
- **Chapters 21–25**: SaaS features (billing, subscriptions)
- **Chapters 26–30**: Deployment, monitoring, and production

By the final chapter, you'll have a complete, production-ready SaaS that could serve real customers.

## Learning Path Overview

```
┌─────────────────────────────────────────────────────────────┐
│  Part 1: Laravel Foundations (Ch 01-05)                     │
│  • Setup • Routing • Controllers • Blade • Auth             │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 2: Building Core Features (Ch 06-10)                  │
│  • Database • Eloquent • Relationships • Validation         │
│  • Organizations • Projects • Tasks                         │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 3: Advanced Features (Ch 11-15)                       │
│  • API Development • File Uploads • Real-time Updates       │
│  • Search • Notifications                                   │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 4: Background & Performance (Ch 16-20)                │
│  • Queues • Jobs • Scheduler • Caching • Optimization       │
│  • Horizon • Performance Monitoring                         │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 5: SaaS & Billing (Ch 21-25)                          │
│  • Laravel Spark • Stripe • Subscriptions • Teams           │
│  • Usage Billing • Invoices                                 │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 6: Production Deployment (Ch 26-30)                   │
│  • Forge Setup • VPS Deployment • FrankenPHP                │
│  • Monitoring • Security • Scaling                          │
└─────────────────────────────────────────────────────────────┘
```

## Quick Start

Already have Laravel installed? Here's a 2-minute preview of what you'll build:

```bash
# Install Laravel (we'll cover this in detail in Chapter 1)
composer create-project laravel/laravel taskflow
cd taskflow

# Start the development server
php artisan serve

# Visit http://localhost:8000
```

**What's Next?**
Head to [Chapter 01](/series/laravel-for-humans/chapters/01-installing-laravel-and-your-first-application) to start your journey!

## Chapters

### Part 1: Laravel Foundations (Chapters 01–05)

Get Laravel installed, understand the framework fundamentals, and implement authentication.

- [**01 — Installing Laravel and Your First Application**](/series/laravel-for-humans/chapters/01-installing-laravel-and-your-first-application)
  Install Laravel, understand the project structure, and create your first routes

- [**02 — Routing and Controllers: The Heart of Laravel**](/series/laravel-for-humans/chapters/02-routing-and-controllers-the-heart-of-laravel)
  Master routing, controllers, and the request/response lifecycle

- [**03 — Views and Blade Templating**](/series/laravel-for-humans/chapters/03-views-and-blade-templating)
  Build dynamic interfaces with Blade templates and components

- [**04 — Authentication: Registration and Login**](/series/laravel-for-humans/chapters/04-authentication-registration-and-login)
  Implement secure user authentication with Laravel Breeze

- [**05 — Advanced Authentication: Email Verification and 2FA**](/series/laravel-for-humans/chapters/05-advanced-authentication-email-verification-and-2fa)
  Add email verification, password resets, and two-factor authentication

### Part 2: Building Core Features (Chapters 06–10)

Build the heart of your application: database models, relationships, and core business logic.

- [**06 — Database Migrations and Schema Design**](/series/laravel-for-humans/chapters/06-database-migrations-and-schema-design)
  Design your database schema and write migrations for projects and tasks

- [**07 — Eloquent ORM: Models, Queries, and Relationships**](/series/laravel-for-humans/chapters/07-eloquent-orm-models-queries-and-relationships)
  Master Eloquent for database interactions and model relationships

- [**08 — Form Validation and Request Objects**](/series/laravel-for-humans/chapters/08-form-validation-and-request-objects)
  Validate user input professionally with Form Requests

- [**09 — Building Organizations and Teams**](/series/laravel-for-humans/chapters/09-building-organizations-and-teams)
  Implement multi-tenancy with organizations and team management

- [**10 — Projects and Tasks: Core CRUD Operations**](/series/laravel-for-humans/chapters/10-projects-and-tasks-core-crud-operations)
  Build the project and task management features with full CRUD

### Part 3: Advanced Features (Chapters 11–15)

Add sophisticated features: APIs, file handling, real-time updates, and search.

- [**11 — Building a RESTful API with Laravel Sanctum**](/series/laravel-for-humans/chapters/11-building-a-restful-api-with-laravel-sanctum)
  Create a JSON API for mobile apps and third-party integrations

- [**12 — API Resources, Versioning, and Rate Limiting**](/series/laravel-for-humans/chapters/12-api-resources-versioning-and-rate-limiting)
  Build professional APIs with transformers and version control

- [**13 — File Uploads and Storage with Laravel**](/series/laravel-for-humans/chapters/13-file-uploads-and-storage-with-laravel)
  Handle file uploads, storage drivers (S3, local), and serving files

- [**14 — Real-Time Features with Laravel Echo and Reverb**](/series/laravel-for-humans/chapters/14-real-time-features-with-laravel-echo-and-reverb)
  Implement websockets for live task updates and notifications

- [**15 — Search and Filtering with Laravel Scout**](/series/laravel-for-humans/chapters/15-search-and-filtering-with-laravel-scout)
  Add powerful search functionality across projects and tasks

### Part 4: Background Processing & Performance (Chapters 16–20)

Master asynchronous processing, caching, and performance optimization.

- [**16 — Introduction to Queues and Jobs**](/series/laravel-for-humans/chapters/16-introduction-to-queues-and-jobs)
  Move slow operations to background jobs for better performance

- [**17 — Email Notifications and Mailables**](/series/laravel-for-humans/chapters/17-email-notifications-and-mailables)
  Send beautiful emails for task assignments, mentions, and activity

- [**18 — Queue Management with Laravel Horizon**](/series/laravel-for-humans/chapters/18-queue-management-with-laravel-horizon)
  Monitor and manage queues with Horizon's elegant dashboard

- [**19 — Task Scheduling and Automated Jobs**](/series/laravel-for-humans/chapters/19-task-scheduling-and-automated-jobs)
  Schedule recurring tasks like report generation and cleanup

- [**20 — Caching Strategies and Performance Optimization**](/series/laravel-for-humans/chapters/20-caching-strategies-and-performance-optimization)
  Implement caching with Redis and optimize database queries

### Part 5: SaaS Features & Billing (Chapters 21–25)

Transform your application into a revenue-generating SaaS with subscriptions and billing.

- [**21 — Introduction to Laravel Spark**](/series/laravel-for-humans/chapters/21-introduction-to-laravel-spark)
  Install and configure Laravel Spark for SaaS features

- [**22 — Subscription Plans and Stripe Integration**](/series/laravel-for-humans/chapters/22-subscription-plans-and-stripe-integration)
  Set up pricing tiers and connect Stripe for payments

- [**23 — Managing Subscriptions: Upgrades, Downgrades, and Cancellations**](/series/laravel-for-humans/chapters/23-managing-subscriptions-upgrades-downgrades-and-cancellations)
  Handle subscription lifecycle and prorated billing

- [**24 — Usage-Based Billing and Metering**](/series/laravel-for-humans/chapters/24-usage-based-billing-and-metering)
  Implement pay-per-use features (API calls, storage, team members)

- [**25 — Invoices, Receipts, and Billing Management**](/series/laravel-for-humans/chapters/25-invoices-receipts-and-billing-management)
  Generate invoices, handle failed payments, and manage billing

### Part 6: Production Deployment & Operations (Chapters 26–30)

Deploy your SaaS to production with professional DevOps practices.

- [**26 — Introduction to Laravel Forge and VPS Hosting**](/series/laravel-for-humans/chapters/26-introduction-to-laravel-forge-and-vps-hosting)
  Set up a DigitalOcean droplet with Forge for deployment

- [**27 — Deploying Your Application with Zero Downtime**](/series/laravel-for-humans/chapters/27-deploying-your-application-with-zero-downtime)
  Configure deployment pipelines, environment variables, and SSL

- [**28 — FrankenPHP: Modern PHP Application Server**](/series/laravel-for-humans/chapters/28-frankenphp-modern-php-application-server)
  Use FrankenPHP for enhanced performance and HTTP/2 support

- [**29 — Production Monitoring with Telescope and Nightwatch**](/series/laravel-for-humans/chapters/29-production-monitoring-with-telescope-and-nightwatch)
  Monitor application health, errors, and performance in production

- [**30 — Security, Backups, and Scaling Your SaaS**](/series/laravel-for-humans/chapters/30-security-backups-and-scaling-your-saas)
  Implement security best practices, automated backups, and scaling strategies

---

## Frequently Asked Questions

**Do I need to know PHP before starting?**
Yes! You should understand PHP basics: variables, functions, classes, arrays. If you're new to PHP, complete our **PHP Basics** series first. Laravel is a PHP framework, so PHP fundamentals are essential.

**Can I follow along with my own SaaS idea instead of TaskFlow?**
Absolutely! The concepts apply to any SaaS. However, we recommend building TaskFlow alongside your own project so you have working reference code.

**Do I need to pay for Laravel Spark?**
Laravel Spark costs $99 for lifetime access. We don't introduce it until Chapter 21, so you can complete 20 chapters free. Spark dramatically accelerates SaaS development, but you can build billing manually if preferred.

**What about testing? Do you cover it?**
Yes! Starting in Chapter 10, every feature includes tests. We use Pest (modern) and PHPUnit (traditional). Testing is integrated throughout, not tacked on at the end.

**Can I use MySQL/PostgreSQL instead of SQLite?**
Yes! We use SQLite for simplicity in early chapters, but the code works identically with MySQL or PostgreSQL. We cover production database setup in deployment chapters.

**How much does deployment cost?**
- **Laravel Forge**: $15/month (server management)
- **DigitalOcean VPS**: $6-12/month for starter droplet
- **Stripe**: Free (transaction fees apply when processing real payments)
- **Total**: ~$21-27/month for production hosting

You don't need to deploy until Chapter 26, and you can use free alternatives like Laravel Cloud or self-host if budget is tight.

**Will this be kept up to date with new Laravel versions?**
Yes! We'll update the series for major Laravel releases. Current content is for Laravel 11, which has long-term support through 2026.

**I'm coming from [Django/Rails/Express] — will this make sense?**
Yes! If you know another web framework, you'll find Laravel familiar. We highlight patterns and show Laravel equivalents for common framework concepts.

## Project Structure

Your completed TaskFlow application will follow this clean architecture:

```
taskflow/
├── app/
│   ├── Actions/          # Business logic and operations
│   ├── Http/
│   │   ├── Controllers/  # HTTP request handling
│   │   ├── Middleware/   # Request filtering
│   │   └── Resources/    # API transformers
│   ├── Models/           # Eloquent models
│   ├── Policies/         # Authorization logic
│   ├── Jobs/             # Background jobs
│   ├── Notifications/    # Email and database notifications
│   └── Events/           # Application events
├── database/
│   ├── migrations/       # Database schema
│   └── seeders/          # Test data
├── resources/
│   ├── views/            # Blade templates
│   ├── js/               # JavaScript (Vue/React)
│   └── css/              # Stylesheets
├── routes/
│   ├── web.php           # Web routes
│   ├── api.php           # API routes
│   └── console.php       # Artisan commands
├── tests/
│   ├── Feature/          # Feature tests
│   └── Unit/             # Unit tests
└── public/               # Web root
```

## Technology Stack

Your application will use modern Laravel ecosystem tools:

**Core Framework:**

- Laravel 11 (latest LTS)
- PHP 8.2+ (8.3 recommended)
- MySQL/PostgreSQL (production) or SQLite (development)

**Frontend:**

- Blade templating
- Alpine.js for interactivity
- Tailwind CSS for styling
- Laravel Vite for asset bundling

**SaaS Features:**

- Laravel Spark (billing, teams, subscriptions)
- Stripe (payment processing)
- Laravel Sanctum (API authentication)

**Real-Time:**

- Laravel Echo (WebSocket client)
- Laravel Reverb (WebSocket server)
- Redis (pub/sub)

**Background Processing:**

- Laravel Queues
- Laravel Horizon (queue monitoring)
- Laravel Scheduler

**Development Tools:**

- Laravel Telescope (debugging)
- Pest (testing)
- Laravel Pint (code formatting)

**Production:**

- Laravel Forge (server management)
- DigitalOcean (VPS hosting)
- FrankenPHP (application server)
- Laravel Nightwatch (monitoring)

## Getting Help

**Stuck on something?** Here's where to get help:

- **Chapter troubleshooting sections** for common issues
- **Laravel Documentation**: [laravel.com/docs](https://laravel.com/docs) (comprehensive and excellent)
- **Code examples**: Working code for every chapter in the series repository
- **Laracasts**: [laracasts.com](https://laracasts.com) (video tutorials)
- **Laravel News**: [laravel-news.com](https://laravel-news.com) (articles and packages)
- **GitHub Discussions**: Ask questions and share progress
- **Laravel Discord**: Active community support

## Related Resources

Want to dive deeper? These resources complement the series:

**Official Resources:**

- [Laravel Documentation](https://laravel.com/docs) — Comprehensive and well-maintained
- [Laracasts](https://laracasts.com) — Video tutorials from Laravel experts
- [Laravel News](https://laravel-news.com) — Latest updates and packages
- [Laravel Bootcamp](https://bootcamp.laravel.com) — Official Laravel tutorial

**Tools & Packages:**

- [Laravel Forge](https://forge.laravel.com) — Server management
- [Laravel Spark](https://spark.laravel.com) — SaaS scaffolding
- [Envoyer](https://envoyer.io) — Zero-downtime deployment
- [Laravel Vapor](https://vapor.laravel.com) — Serverless deployment on AWS

**Community:**

- [Laracasts Forums](https://laracasts.com/discuss) — Active community discussions
- [Laravel Discord](https://discord.gg/laravel) — Real-time chat
- [r/laravel](https://reddit.com/r/laravel) — Reddit community
- [Laravel News Podcast](https://laravel-news.com/podcast) — Weekly updates

**Advanced Topics:**

- [Laravel Beyond CRUD](https://laravel-beyond-crud.com) — Architecture and design
- [Domain-Driven Design in Laravel](https://laravel-news.com/domain-driven-design-in-laravel)
- [Building Scalable Laravel Apps](https://adevait.com/laravel/scalability)

---

## What Makes This Series Different?

**1. Progressive Project-Based Learning**
You won't build disconnected demo apps. Every chapter adds features to the same SaaS, showing you how real applications grow.

**2. Production-Ready Code**
No toy examples. Every line of code follows Laravel best practices and is ready for real users.

**3. Modern Laravel Ecosystem**
Uses the latest Laravel features and official packages (Reverb, Spark, Horizon) — not outdated patterns.

**4. Complete Deployment Guide**
Many tutorials stop at "localhost". We take you all the way to production with Forge, monitoring, and scaling.

**5. Real SaaS Features**
Subscriptions, billing, teams, usage metering — everything you need to charge customers and run a business.

**6. Testing Throughout**
Testing isn't an afterthought. You'll write tests for every feature, building confidence and preventing regressions.

---

::: tip Ready to Build Your SaaS?
Head to [Chapter 01: Installing Laravel and Your First Application](/series/laravel-for-humans/chapters/01-installing-laravel-and-your-first-application) to begin building TaskFlow!
:::
