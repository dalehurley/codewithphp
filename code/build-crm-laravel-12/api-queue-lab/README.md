# Laravel 12 CRM contact API and queue lab

This example supplies the code for chapters 20, 21, 29 and 30 of the CRM course. It uses a separate Laravel 12 application and SQLite database. Contacts belong to individual users here. The main CRM course uses team ownership.

## Install the lab

From a scratch directory, create a Laravel 12 project. Keep a clone of [Code with PHP](https://github.com/dalehurley/codewithphp) nearby.

```bash
composer create-project laravel/laravel crm-api-lab "12.*"
cd crm-api-lab
php artisan install:api --without-migration-prompt
rsync -a /path/to/codewithphp/code/build-crm-laravel-12/api-queue-lab/ ./
```

The last command copies the lab files into your new app, including its API routes. In `app/Models/User.php`, import `Laravel\Sanctum\HasApiTokens` and add `HasApiTokens` to the model's trait list alongside `HasFactory` and `Notifiable`.

Keep the new app's SQLite configuration and set `QUEUE_CONNECTION=database` in `.env`. Create the empty file if necessary, run the migrations and run the lab's feature tests.

```bash
touch database/database.sqlite
php artisan migrate
php artisan test --filter=CrmLabTest
```

The tests cover contact create, read, update and delete, unauthenticated requests, read-only tokens, access across two users and retrying an export without creating a duplicate file.

## Try the API and export

Start the app in one terminal with `php artisan serve --host=127.0.0.1`. In another terminal from the lab's Laravel project directory, create a local demo token and post the first contact.

```bash
token=$(php artisan crm-lab:token --no-ansi)
curl -i http://127.0.0.1:8000/api/v1/contacts \
  -X POST -H "Authorization: Bearer ${token}" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"name":"Avery Chen","email":"avery@example.test"}'
unset token
```

Expect HTTP 201 and a `data` object containing the contact's ID, name and email.

```bash
php artisan crm-lab:export
php artisan queue:work database --queue=exports --once --timeout=30
```

Read the JSON file under `storage/app/private/` at the path printed by `crm-lab:export`. The default SQLite queue tables come from Laravel's starter migrations. The local filesystem disk stores this lab's exports outside the public directory.

To run the same job under Horizon using Redis, follow the [Horizon chapter](https://codewithphp.com/series/build-crm-laravel-12/chapters/30-monitoring-queues-horizon/).

The HTTP and database-worker examples were verified with PHP 8.5 and Laravel 12.73. The Horizon example was verified with Horizon 5.52 and Valkey 9.1.3 using Laravel’s Redis connection.
