---
title: "Laravel 12 Queues and Background Jobs"
description: "Queue a CRM contact export in Laravel 12 with a database connection, run a worker and verify a retry-safe JSON export."
series: "build-crm-laravel-12"
chapter: 29
difficulty: "Intermediate"
dateModified: "2026-11-20"
sidebar:
  label: "29: Queues and Background Jobs"
  order: 29
---

A Laravel 12 background job implements `ShouldQueue`, gets dispatched on a named queue and runs in a separate worker. This example exports a user's CRM contacts to a private JSON file. Dispatch returns before the file is ready when the application uses an asynchronous queue connection.

## Queue and run an export

Set up the [Laravel 12 contacts lab](/series/build-crm-laravel-12/chapters/20-building-restful-api-crm/) and create at least one contact. Set `QUEUE_CONNECTION=database` in the lab's `.env`, run `php artisan migrate` and clear a previous config cache with `php artisan config:clear` if necessary.

```bash
php artisan crm-lab:export
```

The command prints a relative path such as `contact-exports/1/171ccb05-5095-40af-abc5-8f3a52a716a0.json`. The example ID is generated per request. Start one worker iteration to consume the pending job.

```bash
php artisan queue:work database --queue=exports --once --timeout=30
```

Look for a completed `App\Jobs\ExportContacts` in the worker output. Read your reported file under `storage/app/private/`. With one saved contact, the file should contain an array like this.

```json
[
    {
        "id": 1,
        "name": "Avery Chen",
        "email": "avery@example.test"
    }
]
```

## Keep retries predictable

The [export job](https://github.com/dalehurley/codewithphp/blob/main/code/build-crm-laravel-12/api-queue-lab/app/Jobs/ExportContacts.php) receives two small values, the owner's ID and an export ID. It fetches current data when it executes and stores the result using a path based on those IDs. A retry writes to the same path, so it does not create duplicate files. This lab demonstrates small exports. A large dataset would call for streaming and a consistent snapshot strategy.

The job can try three times and times out at 20 seconds. Keep the worker's timeout below the queue connection's `retry_after` setting, so another worker does not claim a job that is still running. A persistent storage failure raises an exception and appears in the failed jobs list. Use `php artisan queue:failed` to inspect it before retrying. When dispatching from inside a database transaction, dispatch after commit if the job depends on those changes. [Laravel 12 queues](https://laravel.com/framework/docs/12.x/queues)

Run `php artisan test --filter=CrmLabTest` to check that repeated execution writes one file and includes only the right user's contacts. Move the same job to an observed Redis queue in the [Horizon lesson](/series/build-crm-laravel-12/chapters/30-monitoring-queues-horizon/).
