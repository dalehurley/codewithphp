---
title: "Build a REST API with Laravel 12 for CRM Contacts"
description: "Build a Laravel 12 contacts API with Eloquent resources, pagination, validation and user-scoped CRUD. Run it locally and verify the HTTP responses."
series: "build-crm-laravel-12"
chapter: 20
difficulty: "Intermediate"
dateModified: "2026-11-20"
sidebar:
  label: "20: REST API for CRM Contacts"
  order: 20
---

Build a Laravel 12 REST API by defining versioned routes in `routes/api.php`, validating the request in a controller and returning an Eloquent resource. This lab implements create, list, view, update and delete for CRM contacts. Each query is scoped to the authenticated user before it touches a contact ID.

## Set up the Laravel 12 example

The [contact API lab](https://github.com/dalehurley/codewithphp/tree/main/code/build-crm-laravel-12/api-queue-lab) includes its migration, model, controller, resource, routes and feature tests. Follow the lab's installation steps in a fresh Laravel 12 app. It uses SQLite and personal access tokens so you can run this chapter without setting up the CRM frontend or team tables.

Start the app with `php artisan serve --host=127.0.0.1`. From a separate terminal in the same Laravel project, run the first request.

```bash
token=$(php artisan crm-lab:token --no-ansi)
curl -i http://127.0.0.1:8000/api/v1/contacts \
  -X POST -H "Authorization: Bearer ${token}" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"name":"Avery Chen","email":"avery@example.test"}'
```

Expect HTTP 201 and the resource body below. The ID depends on your database.

```json
{"data":{"id":1,"name":"Avery Chen","email":"avery@example.test"}}
```

## Define the resource endpoints

Laravel supplies the `/api` prefix. The route group adds `/v1`, leaving a place for a future incompatible API version if one is needed.

| Method | URL | Success status |
| --- | --- | --- |
| GET | `/api/v1/contacts` | 200 with paginated `data`, `links` and `meta` |
| POST | `/api/v1/contacts` | 201 with the created contact |
| GET | `/api/v1/contacts/{id}` | 200 with one contact |
| PATCH | `/api/v1/contacts/{id}` | 200 with the updated contact |
| DELETE | `/api/v1/contacts/{id}` | 204 with an empty body |

The [contact controller](https://github.com/dalehurley/codewithphp/blob/main/code/build-crm-laravel-12/api-queue-lab/app/Http/Controllers/ContactApiController.php) assigns `user_id` from the authenticated request when it creates a contact. It never takes ownership from the JSON body. Its update, show and delete operations all resolve the contact through the same owner-scoped query.

```php
private function contacts(Request $request): Builder
{
    return Contact::query()->where('user_id', $request->user()->getKey());
}
```

The lookup returns 404 for a missing contact and for a contact belonging to a different user. The resource exposes `id`, `name` and `email`. An invalid field produces HTTP 422, and the body identifies the validation failure. Use a PATCH request with just the fields you intend to change.

```bash
curl -i http://127.0.0.1:8000/api/v1/contacts/1 \
  -X PATCH -H "Authorization: Bearer ${token}" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"name":"Avery Lin"}'
unset token
```

Replace `1` with the ID returned by your POST. Expect a 200 response with `Avery Lin` in `data.name` and the original email address.

## Verify before adapting the example

Run `php artisan test --filter=CrmLabTest`. The tests send actual bearer tokens and prove that a different user's contact cannot be read, changed or deleted. To use the pattern in the team-based CRM, determine the team through authenticated membership, scope queries to that team and apply its contact policy. [Laravel 12 Eloquent API resources](https://laravel.com/framework/docs/12.x/eloquent-resources) · [CRM authorization lesson](/series/build-crm-laravel-12/chapters/09-authorization-access-control/)

Next, use the [Sanctum lesson](/series/build-crm-laravel-12/chapters/21-api-authentication-sanctum/) to give integrations distinct read and write permissions.
