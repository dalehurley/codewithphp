<?php

declare(strict_types=1);

/**
 * filename: app/Models/User.php
 * Example: Using Spatie Laravel Permission package
 * 
 * Installation: composer require spatie/laravel-permission
 * Publish migrations: php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
 * Run migrations: php artisan migrate
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $fillable = ['name', 'email', 'password'];
}

// Usage examples:

// Assign role to user
$user = User::find(1);
$user->assignRole('admin');

// Assign permission directly to user
$user->givePermissionTo('edit posts');

// Check if user has permission
if ($user->can('edit posts')) {
    // User can edit posts
}

// Check if user has role
if ($user->hasRole('admin')) {
    // User is admin
}

// Create role with permissions
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::create(['name' => 'editor']);
$permission = Permission::create(['name' => 'edit posts']);
$role->givePermissionTo($permission);

// Assign role to user
$user->assignRole('editor');

// Check multiple permissions
if ($user->hasAnyPermission(['edit posts', 'delete posts'])) {
    // User has at least one permission
}

if ($user->hasAllPermissions(['edit posts', 'delete posts'])) {
    // User has all permissions
}

