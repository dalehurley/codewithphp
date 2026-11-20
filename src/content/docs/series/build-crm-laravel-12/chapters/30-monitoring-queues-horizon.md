---
title: "Monitor Laravel 12 Redis Queues with Horizon"
description: "Install Laravel Horizon, run a CRM export on Redis and find completed jobs in the Horizon dashboard. Includes worker configuration and troubleshooting."
series: "build-crm-laravel-12"
chapter: 30
difficulty: "Intermediate"
dateModified: "2026-11-20"
sidebar:
  label: "30: Queue Monitoring with Horizon"
  order: 30
---

Laravel Horizon supervises Redis queue workers and shows their throughput, runtimes and failures. Install it in the Laravel 12 contacts lab, then watch one of the exports from the [background jobs chapter](/series/build-crm-laravel-12/chapters/29-queues-background-jobs/) go through Redis.

## Install Horizon in the lab

You need PHP's `pcntl`, `posix` and `redis` extensions and a reachable local Redis server. Horizon can also use a suitable PHP Redis client configured on the application. The lab already has a created demo user if you ran `php artisan crm-lab:token` during setup.

```bash
composer require 'laravel/horizon:^5.0'
php artisan horizon:install
```

Set `QUEUE_CONNECTION=redis`, `REDIS_HOST=127.0.0.1` and `REDIS_PORT=6379` in the lab's `.env`, adjusting the port if your local Redis instance uses another. Clear a cached config with `php artisan config:clear`.

In `config/horizon.php`, set the existing `supervisor-1` entry under `defaults` to listen on the Redis `exports` queue. Laravel combines these defaults with the named supervisor under `environments`.

```php
'connection' => 'redis',
'queue' => ['exports'],
```

## Watch a completed export

Start `php artisan horizon` and keep the process open. In another terminal inside the same project, check the worker and dispatch the job.

```bash
php artisan horizon:status
php artisan crm-lab:export
```

Expect Horizon's status to be running. The command returns a file path and a Horizon worker writes the export to that path under `storage/app/private/`. Run `php artisan serve --host=127.0.0.1` in a third terminal. Open `http://127.0.0.1:8000/horizon` and find `App\Jobs\ExportContacts` in Completed Jobs.

If the job stays pending, confirm that the worker and the dispatcher share the same Redis connection settings, queue name and environment. Jobs left on the database connection are still processed by a database worker. When testing a configuration change, restart Horizon gracefully with `php artisan horizon:terminate` and then start it again.

## Finish the lab

Use the [API and queue finish-line checklist](/teach/project-checklists/#laravel-crm-api-and-queue-lab) to check the HTTP, export, and worker results. Then [try a CRM extension](/teach/next-php-projects/#go-further-with-the-crm) using your local demo data.

## Operate Horizon safely

The local dashboard is intended for a loopback lab. For a deployed app, use the `viewHorizon` gate to restrict dashboard access to authorised operators and supervise the Horizon process so it restarts after a deploy. Schedule `horizon:snapshot` to populate historical metrics, and keep the job and worker timeout lower than the Redis connection's `retry_after` value. [Laravel 12 Horizon documentation](https://laravel.com/framework/docs/12.x/horizon)

Return to the [Laravel CRM course](/series/build-crm-laravel-12/) for the full set of available lessons.
