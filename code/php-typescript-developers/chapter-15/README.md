# Chapter 15: Full-Stack - Inertia.js (React/Vue + Laravel)

Code examples for building full-stack applications with Inertia.js, Laravel, and React/Vue.

## Overview

Inertia.js lets you build SPAs using server-side routing and controllers (Laravel) with modern frontend frameworks (React/Vue) without building an API.

**Think of it as:** Next.js for Laravel developers

## Prerequisites

- PHP 8.1+
- Node.js 18+
- Composer
- Laravel 10+

## Quick Start

### 1. Create Laravel + Inertia Project

```bash
# Create Laravel project
composer create-project laravel/laravel inertia-app
cd inertia-app

# Install Inertia server-side
composer require inertiajs/inertia-laravel

# Install Inertia middleware
php artisan inertia:middleware

# Install frontend dependencies (React)
npm install @inertiajs/react react react-dom
npm install --save-dev @vitejs/plugin-react

# Or for Vue
# npm install @inertiajs/vue3 vue@next
```

### 2. Configure Middleware

Add to `app/Http/Kernel.php`:

```php
'web' => [
    // ...
    \App\Http\Middleware\HandleInertiaRequests::class,
],
```

### 3. Setup Frontend

**resources/js/app.jsx** (React):
```javascript
import './bootstrap';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
    return pages[`./Pages/${name}.jsx`];
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
});
```

**vite.config.js**:
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [
    laravel(['resources/js/app.jsx']),
    react(),
  ],
});
```

### 4. Start Development

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

## Examples

### 1. Setup Guide (`01-setup.md`)
Complete setup instructions.

### 2. Basic Example (`02-basic-example/`)
Simple CRUD with Inertia.

### 3. Forms & Validation (`03-forms/`)
Handle forms with client-side validation.

### 4. Shared Data (`04-shared-data.php`)
Share data across all pages.

### 5. TypeScript Setup (`05-typescript/`)
Use TypeScript with Inertia.

## Key Concepts

### Server-Side Routing

```php
// routes/web.php
Route::get('/users', [UserController::class, 'index']);

// app/Http/Controllers/UserController.php
public function index() {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
}
```

### React Component

```tsx
// resources/js/Pages/Users/Index.tsx
import { Link } from '@inertiajs/react';

interface User {
  id: number;
  name: string;
}

export default function Index({ users }: { users: User[] }) {
  return (
    <div>
      <h1>Users</h1>
      {users.map(user => (
        <div key={user.id}>
          <Link href={`/users/${user.id}`}>{user.name}</Link>
        </div>
      ))}
    </div>
  );
}
```

### Forms

```tsx
import { useForm } from '@inertiajs/react';

export default function Create() {
  const { data, setData, post, errors } = useForm({
    name: '',
    email: '',
  });

  function submit(e) {
    e.preventDefault();
    post('/users');
  }

  return (
    <form onSubmit={submit}>
      <input
        value={data.name}
        onChange={e => setData('name', e.target.value)}
      />
      {errors.name && <div>{errors.name}</div>}

      <button type="submit">Create</button>
    </form>
  );
}
```

## Architecture

```
┌─────────────────┐
│  React/Vue      │  Frontend (SPA experience)
│  Components     │
└────────┬────────┘
         │ Inertia.js (JSON responses)
         ↓
┌─────────────────┐
│  Laravel        │  Backend (routing, validation, ORM)
│  Controllers    │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│  Database       │
└─────────────────┘
```

## Benefits

✅ **No API needed** - Controllers return Inertia responses
✅ **Server-side routing** - Laravel handles routes
✅ **SPA experience** - No full page reloads
✅ **Use Laravel features** - Auth, validation, ORM, etc.
✅ **TypeScript support** - Full type safety
✅ **SSR available** - Server-side rendering optional

## Comparison

### Traditional SPA
```
React → API Endpoints → Laravel
(Two separate apps)
```

### Next.js
```
Next.js (API Routes + React) → Database
(Node.js only)
```

### Inertia.js
```
React/Vue → Inertia → Laravel → Database
(Single codebase)
```

## Resources

- [Inertia.js Documentation](https://inertiajs.com/)
- [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze-and-inertia) - Starter kit with Inertia
- [Ziggy](https://github.com/tighten/ziggy) - Use Laravel routes in JavaScript
- [Inertia UI](https://github.com/inertiajs/ui) - Pre-built components
