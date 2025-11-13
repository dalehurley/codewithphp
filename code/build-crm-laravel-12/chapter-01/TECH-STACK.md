# Technology Stack Reference

This document provides a detailed overview of the technology stack used in the Build a CRM with Laravel 12 series.

## Quick Reference

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| Backend Framework | Laravel | 12.x | Server-side web framework, routing, business logic |
| Frontend Library | React | 19.x | Component-based UI rendering |
| Backend-Frontend Bridge | Inertia.js | Latest | Server-to-client data transfer, no separate API |
| CSS Framework | Tailwind CSS | 4.x | Utility-first styling |
| UI Component Library | shadcn/ui | Latest | Pre-built, customizable components |
| Database | MySQL | 8.0+ | Primary data store |
| Cache/Queue Store | Redis | 6.0+ | Caching and background job queue |
| Containerization | Docker & Laravel Sail | Latest | Development environment |
| JavaScript Runtime | Node.js | 18.0+ | Frontend tooling, build process |
| Language | PHP | 8.4+ | Server-side language |

## Backend Stack

### Laravel 12
- **Purpose**: Complete web framework handling routing, database, authentication, authorization, and more
- **Key Packages**:
  - `laravel/framework` — Core framework
  - `laravel/tinker` — Interactive shell for the application
  - `laravel/telescope` — Local debugging and monitoring
  - `laravel/pulse` — Production performance monitoring
- **Documentation**: [laravel.com/docs/12.x](https://laravel.com/docs/12.x)
- **Why Laravel?**: Developer-friendly, batteries-included, excellent documentation, vibrant ecosystem

### Eloquent ORM
- **Purpose**: Object-Relational Mapping for database interactions
- **Features**: Query builder, relationships, eager loading, scopes
- **Included with**: Laravel core
- **Benefits**: Write database queries in PHP instead of raw SQL

### Laravel Eloquent Relationships
- `hasMany()` / `belongsTo()` — One-to-many relationships
- `belongsToMany()` / `through()` — Many-to-many with pivots
- `hasOne()` / `hasOneThrough()` — One-to-one relationships
- Used for: Contacts → Companies, Deals → Stages, Tasks → Contacts/Deals

### Database Migrations
- **Purpose**: Version control for your database schema
- **File Location**: `database/migrations/`
- **Key Feature**: Rollback capability if something goes wrong
- **Usage**: `php artisan migrate` and `php artisan migrate:rollback`

### Laravel Mix / Vite
- **Purpose**: JavaScript and CSS compilation for the frontend
- **Configuration**: `vite.config.js` and `webpack.mix.js`
- **Features**: Hot module replacement (HMR) for instant refreshes during development

## Frontend Stack

### React 19
- **Purpose**: JavaScript library for building dynamic, component-based user interfaces
- **Version**: React 19+ (latest with concurrent rendering, hooks improvements)
- **Key Concepts**:
  - Components as functions
  - Hooks for state management (`useState`, `useEffect`, etc.)
  - Props for component communication
- **Documentation**: [react.dev](https://react.dev)
- **Why React?**: Largest ecosystem, massive community, excellent tooling, component reusability

### JSX/TypeScript
- **Purpose**: React components written with HTML-like syntax
- **Type Safety**: TypeScript adds static type checking for JavaScript
- **Compilation**: Babel and Vite handle compilation before browser execution

### Inertia.js
- **Purpose**: Bridge between Laravel backend and React frontend
- **How It Works**:
  1. Laravel returns JSON responses with page data
  2. Inertia renders the appropriate React component
  3. Link clicks and form submissions stay within the SPA
  4. No separate API endpoints needed for page navigation
- **Benefits**: SPA experience with traditional server-side simplicity
- **Documentation**: [inertiajs.com](https://inertiajs.com)

### Tailwind CSS 4
- **Purpose**: Utility-first CSS framework for rapid UI development
- **Approach**: Apply small utility classes directly in JSX instead of writing CSS
- **Examples**: `bg-blue-500`, `font-bold`, `p-4`, `hover:bg-blue-600`
- **Benefits**: Faster development, consistent design system, smaller final CSS file
- **Configuration**: `tailwind.config.js`
- **Documentation**: [tailwindcss.com](https://tailwindcss.com)

### shadcn/ui
- **Purpose**: Pre-built, accessible UI components (not a traditional component library)
- **Components Included**: Buttons, dialogs, forms, cards, tables, etc.
- **Customization**: Copy components into your project and modify
- **Design System**: Built on Tailwind CSS and Radix UI
- **Documentation**: [ui.shadcn.com](https://ui.shadcn.com)

## Database Layer

### MySQL 8.0+
- **Purpose**: Primary relational database for all persistent data
- **Typical Tables in This CRM**:
  - `users` — User accounts
  - `contacts` — Individual people
  - `companies` — Organizations
  - `deals` — Sales opportunities
  - `tasks` — To-do items
  - `teams` — Team/account groupings
- **Why MySQL?**: Mature, reliable, widespread hosting support, excellent Laravel integration
- **Connection**: Via `DB_` environment variables in `.env` file

### Laravel Migrations
- **Purpose**: Schema versioning and rollback capability
- **Command**: `php artisan make:migration`
- **Files**: Stored in `database/migrations/` directory
- **Example**: `2024_01_15_000000_create_contacts_table.php`

## Caching & Queue Layer

### Redis 6.0+
- **Purpose**: High-speed data store for two critical functions:
  1. **Caching** — Store query results to reduce database load
  2. **Queues** — Background job queue for long-running tasks
- **CLI**: Access via `redis-cli` for debugging
- **Laravel Integration**: `REDIS_` environment variables in `.env`
- **Why Redis?**: Extremely fast, reliable, perfect for Laravel queues

### Laravel Queues
- **Purpose**: Move time-consuming tasks off the request cycle
- **Examples**: Sending emails, generating reports, processing images
- **Worker**: `php artisan queue:work` to process jobs
- **Monitoring**: Laravel Horizon dashboard

## Development Environment

### Laravel Sail
- **Purpose**: Pre-configured Docker environment for local development
- **Includes**: PHP, MySQL, Redis, Node.js, and more
- **Quick Start**: `./vendor/bin/sail up`
- **Benefits**: Eliminates "works on my machine" problems
- **Documentation**: [laravel.com/docs/12.x/sail](https://laravel.com/docs/12.x/sail)

### Docker & Docker Compose
- **Purpose**: Containerization for reproducible environments
- **Configuration**: `docker-compose.yml`
- **Containers in This Project**:
  - `laravel.test` — PHP application container
  - `mysql` — Database container
  - `redis` — Cache/queue container
  - `mailhog` — Email testing container
  - `minio` — S3-compatible storage (optional)

### Node.js 18+
- **Purpose**: JavaScript runtime for frontend tooling and build processes
- **Package Manager**: npm or yarn
- **Build Tools**: Vite for asset compilation
- **Scripts**: `npm run dev` and `npm run build` in `package.json`

## Official Laravel Packages

### Authentication & Authorization
- **Laravel Sanctum** — Token-based API authentication for mobile apps and SPAs
- **Laravel Passport** — OAuth2 server (not used in this series; Sanctum is simpler)

### Database & ORM
- **Laravel Scout** — Full-text search integration (we use MeiliSearch)
- **Laravel Eloquent** — Built-in ORM

### Communication & Real-time
- **Laravel Reverb** — WebSocket server for real-time features
- **Laravel Echo** — JavaScript library for listening to WebSocket events

### Background Processing
- **Laravel Horizon** — Dashboard for monitoring queues
- **Laravel Telescope** — Debugging and monitoring in development

### Billing & Payments
- **Laravel Cashier** — Stripe subscription billing integration

### Monitoring & Performance
- **Laravel Pulse** — Production performance monitoring
- **Laravel Telescope** — Development debugging

## Version Compatibility Matrix

| Package | Version | Notes |
|---------|---------|-------|
| PHP | 8.4+ | Required for modern syntax and performance |
| Laravel | 12.x | Latest stable version |
| React | 19.x | Latest with concurrent rendering |
| Node.js | 18+ | LTS recommended, 20+ works fine |
| MySQL | 8.0+ | 5.7 will work but deprecated |
| Redis | 6.0+ | 7.x recommended for latest features |
| Docker Desktop | Latest | For Laravel Sail |
| Composer | 2.0+ | PHP dependency manager |

## Environment Configuration

### .env File
The `.env` file stores environment-specific configuration:

```
APP_NAME=CRM
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com

STRIPE_KEY=sk_test_...
STRIPE_SECRET=...
```

### Build Artifacts
- `public/build/` — Compiled JavaScript and CSS
- Generated by: `npm run build`
- Served by: Laravel static file serving

## Performance Considerations

### Caching Strategy
- Database query results cached in Redis
- Configuration cached in production
- View compilation cached

### Asset Optimization
- JavaScript and CSS minified with Vite
- Images optimized and served from `public/storage/`
- Database indexes on frequently queried columns

### Database Optimization
- Eloquent eager loading to prevent N+1 queries
- Database migrations for schema versioning
- Proper indexing on foreign keys

## Deployment Stack

### Production Options

#### Option 1: Laravel Forge (VPS-based)
- Virtual Private Server on DigitalOcean, AWS, Linode, etc.
- Automatic SSL, deployments, monitoring
- MySQL managed or self-hosted
- Redis managed or self-hosted

#### Option 2: Laravel Vapor (Serverless)
- AWS Lambda-based serverless deployment
- Automatic scaling, zero downtime deployments
- Managed database and Redis
- Excellent for SaaS applications

Both options support the complete stack described above.

## Learning Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [React Documentation](https://react.dev)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/docs/getting-started)
- [Laravel Bootcamp](https://bootcamp.laravel.com) — Interactive Laravel tutorial

## Next Steps

After understanding this stack, proceed to Chapter 02 to install Laravel 12 and set up your local development environment with Laravel Sail.
