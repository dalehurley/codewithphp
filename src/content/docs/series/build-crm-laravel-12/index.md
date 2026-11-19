---
title: "Build a CRM with Laravel 12"
description: "Build a Laravel 12 CRM with teams, contacts, companies, deals and tasks. Run a REST API, issue Sanctum tokens and monitor Redis jobs with Horizon."
series: "build-crm-laravel-12"
difficulty: "Intermediate"
---

Build a customer relationship management application with Laravel 12, React and Inertia. The main project covers setup, team permissions, contacts, companies, deals, tasks and notifications. A separate runnable lab takes one feature through an authenticated REST API, a background export and a monitored Redis queue.

## Where to start

- **New to Laravel?** Start with [the project overview](/series/build-crm-laravel-12/chapters/01-introduction-series-overview/) and [the Laravel Sail setup](/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment/).
- **Building a contact API or learning Sanctum?** Jump to [the Laravel 12 contacts API lab](/series/build-crm-laravel-12/chapters/20-building-restful-api-crm/). It runs in a separate Laravel 12 app with SQLite and no frontend setup.
- **Want to run a queue worker?** Start with [background jobs](/series/build-crm-laravel-12/chapters/29-queues-background-jobs/), then use [Horizon](/series/build-crm-laravel-12/chapters/30-monitoring-queues-horizon/) to watch the job run on Redis.

You should be comfortable with PHP classes, the command line and the basics of HTTP and SQL. For the main project, use the PHP, Composer, Node.js and Docker setup in chapter 02. [PHP Basics](/series/php-basics/) covers the language if you need to build those foundations first.

## Available chapters

### Setup and application foundations

1. [Introduction and project overview](/series/build-crm-laravel-12/chapters/01-introduction-series-overview/)
2. [Set up the Laravel 12 development environment](/series/build-crm-laravel-12/chapters/02-setting-up-laravel-12-project-dev-environment/)
3. [Laravel fundamentals and project structure](/series/build-crm-laravel-12/chapters/03-laravel-12-fundamentals-project-structure/)
4. [Plan the application architecture and data model](/series/build-crm-laravel-12/chapters/04-planning-application-architecture-data-modeling/)
5. [Database migrations and schema design](/series/build-crm-laravel-12/chapters/05-database-migrations-schema-design/)
6. [Eloquent models and relationships](/series/build-crm-laravel-12/chapters/06-eloquent-models-relationships/)
7. [User authentication with the React starter kit](/series/build-crm-laravel-12/chapters/07-user-authentication-react-starter-kit/)
8. [Team management and user roles](/series/build-crm-laravel-12/chapters/08-team-management-user-roles/)
9. [Authorization and access control](/series/build-crm-laravel-12/chapters/09-authorization-access-control/)
10. [Layout and interface design](/series/build-crm-laravel-12/chapters/10-layout-ui-design-customization/)

### CRM modules

11. [Model contacts in the database](/series/build-crm-laravel-12/chapters/11-contacts-module-database-model/)
12. [Create, read, update and delete contacts](/series/build-crm-laravel-12/chapters/12-contacts-module-crud-operations/)
13. [Model companies in the database](/series/build-crm-laravel-12/chapters/13-companies-module-database-model/)
14. [Create, read, update and delete companies](/series/build-crm-laravel-12/chapters/14-companies-module-crud-operations/)
15. [Design the deals pipeline](/series/build-crm-laravel-12/chapters/15-deals-module-database-pipeline-design/)
16. [Build the deals pipeline interface](/series/build-crm-laravel-12/chapters/16-deals-module-crud-pipeline-interface/)
17. [Model tasks in the database](/series/build-crm-laravel-12/chapters/17-tasks-module-database-model/)
18. [Build the tasks module and scheduling](/series/build-crm-laravel-12/chapters/18-tasks-module-crud-task-scheduling/)
19. [Notifications and email integration](/series/build-crm-laravel-12/chapters/19-notifications-email-integration/)

### API and queue labs

Each lab uses the same [example project](https://github.com/dalehurley/codewithphp/tree/main/code/build-crm-laravel-12/api-queue-lab). The links retain their original chapter numbers.

- [20 · Build a Laravel 12 REST API for contacts](/series/build-crm-laravel-12/chapters/20-building-restful-api-crm/)
- [21 · Authenticate an API with Laravel Sanctum tokens](/series/build-crm-laravel-12/chapters/21-api-authentication-sanctum/)
- [29 · Export contacts with a background job](/series/build-crm-laravel-12/chapters/29-queues-background-jobs/)
- [30 · Monitor Redis queues with Laravel Horizon](/series/build-crm-laravel-12/chapters/30-monitoring-queues-horizon/)

## Further reading

- [Laravel 12 documentation](https://laravel.com/framework/docs/12.x)
- [Laravel Sail documentation](https://laravel.com/framework/docs/12.x/sail)
- [Inertia documentation](https://inertiajs.com/)
- [Code examples and setup files](https://github.com/dalehurley/codewithphp/tree/main/code/build-crm-laravel-12)
