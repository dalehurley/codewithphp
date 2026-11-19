---
title: "Laravel 12 Sanctum API Token Authentication"
description: "Issue a Laravel 12 Sanctum token, authenticate curl requests and protect a contacts API with token abilities, expiration and ownership checks."
series: "build-crm-laravel-12"
chapter: 21
difficulty: "Intermediate"
dateModified: "2026-11-20"
sidebar:
  label: "21: API Authentication with Sanctum"
  order: 21
---

To authenticate an external script against a Laravel 12 API, install Sanctum, add `HasApiTokens` to the user model, create a token with specific abilities and send it in the request's `Authorization: Bearer` header. The API route uses `auth:sanctum` to identify the caller and `CheckAbilities` to enforce the token's permissions.

## Issue and use a token

Use the [Laravel 12 contacts lab](/series/build-crm-laravel-12/chapters/20-building-restful-api-crm/) from the preceding chapter. Its installation runs `php artisan install:api` and adds the trait. The lab configures bearer authentication only. A first-party SPA usually uses Sanctum's session and CSRF flow instead. [Laravel 12 Sanctum documentation](https://laravel.com/framework/docs/12.x/sanctum)

Run this in a terminal from your lab project with the local app listening on port 8000.

```bash
token=$(php artisan crm-lab:token --read-only --no-ansi)
curl -i http://127.0.0.1:8000/api/v1/contacts \
  -H "Authorization: Bearer ${token}" -H 'Accept: application/json'
```

Expect HTTP 200 with your list of contacts, which may be empty. The lab command creates the local demo user on first use and returns an hour-long token. In your own application, issue tokens only after authorising the signed-in user, and display the plain text token once.

## Enforce token abilities and resource ownership

The lab grants a read-only token `contacts:read`. A token created without the `--read-only` flag also receives `contacts:write`. The [API routes](https://github.com/dalehurley/codewithphp/blob/main/code/build-crm-laravel-12/api-queue-lab/routes/api.php) check the relevant ability for each method.

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('contacts', [ContactApiController::class, 'store'])
        ->middleware(CheckAbilities::class.':contacts:write');
});
```

Try a valid POST using your read-only token.

```bash
curl -i http://127.0.0.1:8000/api/v1/contacts \
  -X POST -H "Authorization: Bearer ${token}" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"name":"Avery Chen","email":"avery@example.test"}'
unset token
```

Expect HTTP 403. Without a token, the same JSON request returns HTTP 401. A read-write token still receives HTTP 404 for another user's contact because abilities control what the integration can do and the controller controls whose records it can access.

## Expire and revoke credentials

The demo issues one-hour tokens. Real integrations should use HTTPS, store tokens outside their source code, and rotate or revoke tokens when access changes. To revoke a specific token after authorising the owner, delete the corresponding row through its relationship.

```php
$user->tokens()->whereKey($tokenId)->delete();
```

An application can also schedule `sanctum:prune-expired` to clean expired records. Test the lab with `php artisan test --filter=CrmLabTest`. Then take the same CRM data into a background job in the [queue lesson](/series/build-crm-laravel-12/chapters/29-queues-background-jobs/).
