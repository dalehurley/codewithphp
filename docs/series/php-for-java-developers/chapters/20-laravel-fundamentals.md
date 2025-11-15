---
title: "20: Laravel Fundamentals"
description: "Eloquent ORM, Blade templates, artisan CLI, ecosystem"
series: "php-for-java-developers"
chapter: 20
order: 20
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/19-framework-comparison"
---

# Chapter 20: Laravel Fundamentals

<Badge type="warning">Intermediate</Badge>

## Overview

Laravel is PHP's most popular framework, comparable to Spring Boot for rapid application development.

**Topics:** Eloquent ORM, Routing, Blade templates, Artisan, Middleware, Service container

## Section 1: Getting Started

```bash
composer create-project laravel/laravel myapp
php artisan serve
```

## Section 2: Routing

```php
<?php
// routes/web.php
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
```

## Section 3: Eloquent ORM

```php
<?php
// Model
class User extends Model {
    protected $fillable = ['name', 'email'];
}

// Queries
$users = User::where('active', true)->get();
$user = User::find($id);
$user = User::create($data);
```

## Section 4: Blade Templates

```php
@extends('layouts.app')

@section('content')
    <h1>{{ $title }}</h1>
    @foreach($users as $user)
        <p>{{ $user->name }}</p>
    @endforeach
@endsection
```

## Section 5: Artisan CLI

```bash
php artisan make:controller UserController
php artisan make:model User
php artisan migrate
php artisan tinker
```

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/19-framework-comparison">← Chapter 19</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/21-symfony-components">Chapter 21 →</a></div>
</div>
